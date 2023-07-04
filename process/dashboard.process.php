<?php

include_once("../database/connection.php");
include_once("sanitize.process.php");

class InventoryListener extends Database
{

    public function CreateNotifications()
    {
        $inventoryclosingmessage = "";
        $currentHour = date('H');
        $currentMinute = date('i');

        if (($currentHour == 16 && $currentMinute >= 0) || $currentHour == 17) {
            $inventoryclosingmessage = "Inventory reminder: <b> We're encouraging you on closing our inventory. </b>";
        }

        $products = [];
        $thresholdnotifs = [];

        if ($inventoryclosingmessage != "") {
            $thresholdnotifs[] = $inventoryclosingmessage;
        }
        
        $query = "SELECT
            ic.barcode,
            ic.generalname,
            ic.brandname,
            SUM(ic.quantity) AS quantity,
            p.threshold,
            p.threshold - ic.quantity AS difference
        FROM
            tbl_inventory_current AS ic
        INNER JOIN
            tbl_product_profiles AS p ON ic.barcode = p.barcode
        GROUP BY ic.barcode
        ORDER BY difference DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        foreach ($products as $product) {
            $productName = $product['generalname'];
            $brandName = $product['brandname'];
            $barcode = $product['barcode'];
            $currentQuantity = $product['quantity'];
            $threshold = $product['threshold'];
            $message = "Low stock alert: <b> $productName </b> by $brandName (Barcode: $barcode) has only <b> $currentQuantity </b> left (Threshold: <b> $threshold </b>). Please consider restocking soon.";
            
            if ($currentQuantity < $threshold) {
                $thresholdnotifs[] = $message;
            }
        }

        $agingupdatemessage = "";
        $currentHour = date('H');
        $currentMinute = date('i');

        if (($currentHour == 16 && $currentMinute >= 0) || $currentHour == 17) {
            $agingupdatemessage = "Aging reminder: <b> We're encouraging you to update our aging. </b>";
        }

        $currproducts = [];
        $expirationNotifs = [];

        if ($agingupdatemessage != "") {
            $expirationNotifs[] = $agingupdatemessage;
        }

        $query = "SELECT
            ic.batch,
            ic.barcode,
            ic.generalname,
            ic.brandname,
            ic.quantity,
            ic.expirationdate
        FROM
            tbl_inventory_current AS ic
        ORDER BY STR_TO_DATE(ic.expirationdate, '%m/%d/%Y') DESC;";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $currproducts[] = $row;
            }
        }

        foreach ($currproducts as $currproduct) {
            $batch = $currproduct['batch'];
            $productName = $currproduct['generalname'];
            $brandName = $currproduct['brandname'];
            $barcode = $currproduct['barcode'];
            $expirationDate = $currproduct['expirationdate'];
            
            $message = "Expiration alert: <b>Batch $batch of $productName</b> by $brandName (Barcode: $barcode) is close to expiring. Expires on: <b>$expirationDate</b>. Please consider restocking soon.";

            $now = time();
            $expirationTimestamp = strtotime($expirationDate);
            
            if ($expirationTimestamp < $now) {
                $message = "Expiration alert: <b>Batch $batch of $productName</b> by $brandName (Barcode: $barcode) has already expired on: <b>$expirationDate</b>. Please remove from inventory.";
                $expirationNotifs[] = $message;
            } else if ($expirationTimestamp < strtotime('+1 month')) {
                $expirationNotifs[] = $message;
            }
        }

        echo json_encode(array(
            "LOWSTOCKNOTIFICATIONS" => $thresholdnotifs,
            "EXPIRATIONNOTIFICATIONS" => $expirationNotifs
        ));
    }

    public function LoadUnpostedDates()
    {

        $now = date('m/d/Y');
        $unposted_dates = array();

        $lcdquery = "SELECT value AS lastclosingdate FROM tbl_inventory_config WHERE config = 'LASTCLOSINGDATE' LIMIT 1;";
        $lcdstmt = $this->conn->prepare($lcdquery);
        $lcdstmt->execute();
        $lastclosingdate = (($lcdstmt->get_result())->fetch_array())['lastclosingdate'];
        $lcdstmt->close();

        $start_date = DateTime::createFromFormat('m/d/Y', $lastclosingdate);
        $end_date = DateTime::createFromFormat('m/d/Y', $now);

        $current_date = clone $start_date;
        $current_date->modify('+1 day');

        while ($current_date < $end_date) {
            $unposted_dates[] = $current_date->format('m/d/Y');

            $current_date->modify('+1 day');
        }

        echo array_shift($unposted_dates);
    }

    public function isClosed()
    {

        $isClosed = true;

        $now = date('m/d/Y');
        $lcdquery = "SELECT value AS lastclosingdate FROM tbl_inventory_config WHERE config = 'LASTCLOSINGDATE' LIMIT 1;";
        $lcdstmt = $this->conn->prepare($lcdquery);
        $lcdstmt->execute();
        $lastclosingdate = (($lcdstmt->get_result())->fetch_array())['lastclosingdate'];
        $lcdstmt->close();

        $asquery = "SELECT COALESCE(asof, CURRENT_DATE) AS asof FROM tbl_inventory_current LIMIT 1;";
        $asstmt = $this->conn->prepare($asquery);
        $asstmt->execute();

        $asof = date('m/d/Y'); // Set the default value to current date

        $result = $asstmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $asof = $row['asof'];
        }

        $asstmt->close();

        $_SESSION['lockInventory'] = strtotime($now) == strtotime($lastclosingdate);

        if (strtotime($now) > strtotime($asof)) {
            if (strtotime($asof) != strtotime($lastclosingdate)) {
                $isClosed = false;
                echo $isClosed;
                $_SESSION['isClosed'] = $isClosed;
                return;
            }
        }

        // echo strtotime($now) . ' ' . strtotime($asof) . ' ';
        // echo strtotime($asof) . ' ' .  strtotime($lastclosingdate) . ' ';
        unset($_SESSION['isClosed']);
        echo $isClosed;
    }

    public function UpdateAsOf()
    {

        $now = date('m/d/Y');
        $lcdquery = "SELECT value AS lastclosingdate FROM tbl_inventory_config WHERE config = 'LASTCLOSINGDATE' LIMIT 1;";
        $stmt = $this->conn->prepare($lcdquery);
        $stmt->execute();
        $lastclosingdate = (($stmt->get_result())->fetch_array())['lastclosingdate'];
        $stmt->close();

        $flquery = "SELECT value AS firstload FROM tbl_inventory_config WHERE config = 'FIRSTLOAD' LIMIT 1;";
        $stmt = $this->conn->prepare($flquery);
        $stmt->execute();
        $firstload = (($stmt->get_result())->fetch_array())['firstload'];
        $stmt->close();

        $asquery = "SELECT asof FROM tbl_inventory_current WHERE STR_TO_DATE(asof,'%m/%d/%Y') = STR_TO_DATE('$now','%m/%d/%Y') LIMIT 1;";
        $stmt = $this->conn->prepare($asquery);
        $stmt->execute();
        $asofres = $stmt->get_result();
        $stmt->close();

        // echo ((strtotime($lastclosingdate) . ' ' . strtotime($now) . ' ' . strtotime($lastclosingdate) . ' ' . strtotime($now)) . ' ' . $firstload . ' ' . $asofres->num_rows);

        if (($firstload != 0 && $asofres->num_rows != 0)) {
            echo 'NOT RELOADED';
            return;
        }

        $now = date('m/d/Y');
        $updateasof = "UPDATE tbl_inventory_current SET asof = '$now';";
        $stmt = $this->conn->prepare($updateasof);
        $stmt->execute();
        $stmt->close();

        $updatefirstload = "UPDATE tbl_inventory_config SET value = '1' WHERE config = 'FIRSTLOAD';";
        $stmt = $this->conn->prepare($updatefirstload);
        $stmt->execute();
        $stmt->close();

        echo 'RELOADED';
    }

    public function CloseUnclosedDate($data)
    {

        $now = date('m/d/Y');
        $unposted_dates = array();

        $lcdquery = "SELECT value AS lastclosingdate FROM tbl_inventory_config WHERE config = 'LASTCLOSINGDATE' LIMIT 1;";
        $lcdstmt = $this->conn->prepare($lcdquery);
        $lcdstmt->execute();
        $lastclosingdate = (($lcdstmt->get_result())->fetch_array())['lastclosingdate'];
        $lcdstmt->close();

        $start_date = DateTime::createFromFormat('m/d/Y', $lastclosingdate);
        $end_date = DateTime::createFromFormat('m/d/Y', $now);

        $current_date = clone $start_date;
        $current_date->modify('+1 day');

        while ($current_date < $end_date) {
            $unposted_dates[] = $current_date->format('m/d/Y');

            $current_date->modify('+1 day');
        }

        $uncloseddate = array_shift($unposted_dates);

        if ($data['date'] == $uncloseddate) {

            $sql = "SELECT value AS lastclosingdate FROM tbl_inventory_config WHERE config = 'LASTCLOSINGDATE' LIMIT 1;";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $lastclosingdate = (($stmt->get_result())->fetch_array())['lastclosingdate'];
            $stmt->close();

            if (strtotime($lastclosingdate) == strtotime($now)) {
                echo 'Already Closed.';
                return;
            }

            if (strtotime($lastclosingdate) > strtotime($now)) {
                echo 'Invalid Last Closed Date.';
                return;
            }

            $currenttoprevious = "INSERT INTO tbl_inventory_previous (batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose) SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE asof = '$uncloseddate';";
            $stmt = $this->conn->prepare($currenttoprevious);
            $stmt->execute();
            $stmt->close();


            $updateconfig = "UPDATE tbl_inventory_config SET VALUE = '$uncloseddate' WHERE config = 'LASTCLOSINGDATE';";
            $stmt = $this->conn->prepare($updateconfig);
            $stmt->execute();
            $stmt->close();

            $updateasof = "UPDATE tbl_inventory_current SET asof = '" . (DateTime::createFromFormat('m/d/Y', $uncloseddate))->modify('+1 day')->format('m/d/Y') . "';";
            $stmt = $this->conn->prepare($updateasof);
            $stmt->execute();
            $stmt->close();

            $updatefirstload = "UPDATE tbl_inventory_config SET value = '1' WHERE config = 'FIRSTLOAD';";
            $stmt = $this->conn->prepare($updatefirstload);
            $stmt->execute();
            $stmt->close();

            $todaytomain = "INSERT INTO tbl_pos_transactions_main (transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby) SELECT transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby FROM tbl_pos_transactions_today WHERE transactiondate = '$uncloseddate';";
            $stmt = $this->conn->prepare($todaytomain);
            $stmt->execute();
            $stmt->close();

            $truncatemain = "DELETE FROM tbl_pos_transactions_today WHERE transactiondate = '$uncloseddate';";
            $stmt = $this->conn->prepare($truncatemain);
            $stmt->execute();
            $stmt->close();

            $cleancancellables = "TRUNCATE TABLE tbl_pos_cancellables;";
            $stmt = $this->conn->prepare($cleancancellables);
            $stmt->execute();
            $stmt->close();

            $returnables = "DELETE FROM tbl_pos_returnables WHERE STR_TO_DATE(transactiondate, '%m/%d/%Y') >= STR_TO_DATE('$now', '%m/%d/%Y');";
            $stmt = $this->conn->prepare($returnables);
            $stmt->execute();
            $stmt->close();

            session_destroy();
            echo 'SUCCESS';
            return;
        }

        echo 'Already Posted!';
    }
}