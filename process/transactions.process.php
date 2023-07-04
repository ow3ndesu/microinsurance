<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");

class Transaction extends Database{

    public function SaveIncomingTransaction($receivingno, $id, $productno) {
        
        $referenceno = $receivingno;
        $today = date('m/d/Y');
        $user = $_SESSION['userid'];

        $sql = "SELECT (COUNT(productno) + 1) as batch FROM tbl_inventory_transactions WHERE productno = ? AND category = 'INCOMING';";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $productno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $batch = ($res->fetch_assoc())['batch'];

        $query = "SELECT supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, dateencoded FROM tbl_purchase_received WHERE receivingno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $receivingno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while($row = $result->fetch_assoc()) {
            $purchaseorder[] = $row;
        }

        $insert = "INSERT INTO tbl_inventory_transactions (transactiondate, user, referenceno, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, batch, barcode, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, category, purpose, dateencoded) 
        SELECT '$today', '$user', '$referenceno', r.supplierno, r.supplier, r.address, r.supplieropenbalance, r.terms, r.modeofpayment, r.totalprice, r.remarks, r.status, p.batch, p.barcode, p.productno, p.generalname, p.brandname, p.uom, p.unitprice, p.customunitprice, p.receivedquantity, p.producttotalprice, p.expirationdate, p.lotno, 'INCOMING', r.purpose, '$today'
        FROM tbl_purchase_order_products p
        INNER JOIN tbl_purchase_received r ON r.receivingno = p.receivingno
        WHERE p.id = ? AND p.productno = ? AND p.batch = ?;";
        $stmt = $this->conn->prepare($insert);
        $stmt->bind_param('sss', $id, $productno, $batch);

        if ($stmt->execute()) {
            $stmt->close();
            return 'SUCCESS';
        } else {
            $stmt->close();
            return 'SOMETHING DID NOT GO RIGHT.';
        }
    }

    public function SaveOutgoingTransaction($data, $batch, $barcode, $productno, $generalname, $brandname, $uom, $unitprice, $customunitprice, $quantity, $producttotalprice, $expirationdate, $lotno, $srp, $asof) {

        $Supplier = new Supplier();

        $returnno = $data["returnno"];
        $prdate = date('m/d/Y', strtotime($data["prdate"]));
        $purpose = strtoupper($data["purpose"]);
        $supplier = strtoupper($data["supplier"]);
        $supplierno = $Supplier->GetSupplierNumberByBusinessName($supplier);
        $address = strtoupper($data["address"]);
        $supplieropenbalance = $data["supplieropenbalance"];
        $terms = $data["terms"];
        $modeofpayment = strtoupper($data["modeofpayment"]);
        $totalprice = $data["totalprice"];
        $remarks = (!isset($data["remarks"])) ? 'RETURN COMPLETED' : $data["remarks"];
        
        $referenceno = $returnno;
        $today = date('m/d/Y');
        $user = $_SESSION['userid'];

        $insert = "INSERT INTO tbl_inventory_transactions (transactiondate, user, referenceno, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, batch, barcode, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, category, purpose, dateencoded) VALUES
        ('$prdate', '$user', '$referenceno', '$supplierno', '$supplier', '$address', '$supplieropenbalance', '$terms', '$modeofpayment', '$totalprice', '$remarks', 'RETURNED', '$batch', '$barcode', '$productno', '$generalname', '$brandname', '$uom', '$unitprice', '$customunitprice', '$quantity', '$producttotalprice', '$expirationdate', '$lotno', 'OUTGOING', '$purpose', '$today');";
        $stmt = $this->conn->prepare($insert);

        if ($stmt->execute()) {
            $stmt->close();

            if ($purpose == 'EXPIRED') {
                $insertexp = "INSERT INTO tbl_inventory_expired (batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose) VALUES
                ('$batch', '$supplierno', '$supplier', '$productno',  '$barcode', '$generalname', '$brandname', '$uom', '$unitprice', '$customunitprice', '$quantity', '$producttotalprice', '$expirationdate', '$lotno', '$srp', '$asof', '$purpose');";
                $stmtexp = $this->conn->prepare($insertexp);
                $stmtexp->execute();
                $stmtexp->close();
            }

            return 'SUCCESS';
        } else {
            $stmt->close();
            return 'SOMETHING DID NOT GO RIGHT.';
        }
    }

    public function LoadDates() {
        
        $now = date('m/d/Y');
        $isDisabled = false;
        $sql = "SELECT value AS lastclosingdate FROM tbl_inventory_config WHERE config = 'LASTCLOSINGDATE' LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $lastclosingdate = (($stmt->get_result())->fetch_array())['lastclosingdate'];
        $stmt->close();
        
        if (strtotime($lastclosingdate) == strtotime($now)) {
            $lastclosingdate = 'Today';
            $isDisabled = true;
        }

        if (strtotime($lastclosingdate) > strtotime($now)) {
            $isDisabled = true;
        }

        if (strtotime($lastclosingdate) < strtotime($now)) {
            $isDisabled = false;
        }

        echo json_encode(array(
            "LASTCLOSEDDATE" => $lastclosingdate,
            "ISDISABLED" => $isDisabled
        ));
    }

    public function CloseTransaction() {
        
        $now = date('m/d/Y');
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

        $cleanprevious = "TRUNCATE TABLE tbl_inventory_previous;";
        $stmt = $this->conn->prepare($cleanprevious);
        $stmt->execute();
        $stmt->close();

        $currenttoprevious = "INSERT INTO tbl_inventory_previous (batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose) SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current;";
        $stmt = $this->conn->prepare($currenttoprevious);
        $stmt->execute();
        $stmt->close();

        
        $updateconfig = "UPDATE tbl_inventory_config SET VALUE = '$now' WHERE config = 'LASTCLOSINGDATE';";
        $stmt = $this->conn->prepare($updateconfig);
        $stmt->execute();
        $stmt->close();

        $updatefirstload = "UPDATE tbl_inventory_config SET value = '0' WHERE config = 'FIRSTLOAD';";
        $stmt = $this->conn->prepare($updatefirstload);
        $stmt->execute();
        $stmt->close();

        $todaytomain = "INSERT INTO tbl_pos_transactions_main (transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby) SELECT transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby FROM tbl_pos_transactions_today;";
        $stmt = $this->conn->prepare($todaytomain);
        $stmt->execute();
        $stmt->close();

        $truncatemain = "TRUNCATE TABLE tbl_pos_transactions_today;";
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

        $_SESSION['lockInventory'] = true;
        echo 'SUCCESS';
    }
}
