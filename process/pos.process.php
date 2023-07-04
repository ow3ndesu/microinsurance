<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");
include_once("customers.process.php");
include_once("employees.process.php");
include_once("pre-function.process.php");

class POS extends Database {

    public function LoadTransactionNo() {

        $terminal = $_SESSION['terminal'];
        
        $sql = "SELECT CONCAT(t.terminal, '-', t.nextor) as transactionno
        FROM tbl_terminals t
        INNER JOIN tbl_users u ON t.terminal = u.terminal
        WHERE t.terminal = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $terminal);
        $stmt->execute();
        $row = ($stmt->get_result()->fetch_assoc());
        $stmt->close();

        echo $row['transactionno'];
    }

    public function LoadPOSReceipts() {
        
        $receipts = [];
        $Customer = new Customer();
        $receiptsloaded = "RECEIPTS_LOADED";

        $query = "SELECT
            r.transactionno,
            r.transactiondate,
            r.customerno,
            r.totalamount,
            r.drno,
            CASE WHEN pc.transactionno IS NOT NULL THEN true ELSE false END AS showCancel
        FROM
            tbl_pos_receipts AS r
        LEFT JOIN
            tbl_pos_cancellables AS pc ON r.transactionno = pc.transactionno
            AND r.transactiondate = pc.transactiondate
            AND r.sinumber = pc.sinumber
            AND r.drno = pc.drno
        GROUP BY
            r.transactionno;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $receipts[] = $row;
            }

            for ($i=0; $i < count($receipts); $i++) { 
                $customerno = $receipts[$i]['customerno'];
                $receipts[$i]['customerno'] = $Customer->LoadCustomerBusinessNameViaCustomerNo($customerno)['businessname'];
            }

            echo json_encode(array(
                "MESSAGE" => $receiptsloaded,
                "RECEIPTS" => $receipts
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPOSPaymentReceipts() {
        
        $receipts = [];
        $Customer = new Customer();
        $receiptsloaded = "RECEIPTS_LOADED";

        $query = "SELECT prno, prdate, customerno, amount FROM tbl_pos_payments;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $receipts[] = $row;
            }

            for ($i=0; $i < count($receipts); $i++) { 
                $customerno = $receipts[$i]['customerno'];
                $receipts[$i]['customerno'] = $Customer->LoadCustomerBusinessNameViaCustomerNo($customerno)['businessname'];
            }

            echo json_encode(array(
                "MESSAGE" => $receiptsloaded,
                "RECEIPTS" => $receipts
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadReturnableReceipts() {
        
        $receipts = [];
        $Customer = new Customer();
        $receiptsloaded = "RECEIPTS_LOADED";

        $query = "SELECT
            r.transactionno,
            r.transactiondate,
            r.customerno,
            r.totalamount,
            r.drno
        FROM
            tbl_pos_returnables AS rr
        LEFT JOIN
            tbl_pos_receipts AS r ON rr.transactionno = r.transactionno
            AND rr.transactiondate = r.transactiondate
            AND rr.sinumber = r.sinumber
            AND rr.drno = r.drno
        GROUP BY
            r.transactionno;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $receipts[] = $row;
            }

            for ($i=0; $i < count($receipts); $i++) { 
                $customerno = $receipts[$i]['customerno'];
                $receipts[$i]['customerno'] = $Customer->LoadCustomerBusinessNameViaCustomerNo($customerno)['businessname'];
            }

            echo json_encode(array(
                "MESSAGE" => $receiptsloaded,
                "RECEIPTS" => $receipts
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadDeliveryReceipt($data) {
        
        $dr = [];
        $sanitize = new Sanitize();
        $Employee = new Employee();
        $Customer = new Customer();
        $drno = $sanitize->sanitizeForString($data["drno"]);
        $drloaded = "DR_LOADED";

        $query = "SELECT transactionno, transactiondate, drno, sinumber, paymentmethod, terms, modeofpayment, customerno as customer, billingaddress, preparedby, checkedby, releasedby, receivedby, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, supplier, encodedby FROM tbl_pos_receipts WHERE drno = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $drno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $dr[] = $row;
            }

            $dr[0]['customer'] .= ',' . $Customer->LoadCustomerBusinessNameViaCustomerNo($dr[0]['customer'])['businessname'];
            $dr[0]['preparedby'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['preparedby']);
            $dr[0]['checkedby'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['checkedby']);
            $dr[0]['releasedby'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['releasedby']);

            $transactionno = $dr[0]['transactionno'];
            $query = "SELECT transactionno FROM tbl_pos_cancellables WHERE transactionno = '$transactionno';";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $isCancellable = $result->num_rows > 0;

            $query = "SELECT referenceno FROM tbl_inventory_transactions WHERE referenceno = '$transactionno';";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $isCanceled = $result->num_rows > 0;

            echo json_encode(array(
                "MESSAGE" => $drloaded,
                "DR" => $dr,
                "CANCELLABLE" => $isCancellable,
                "CANCELED" => $isCanceled
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadReturnableDeliveryReceipt($data) {
        
        $dr = [];
        $sanitize = new Sanitize();
        $Employee = new Employee();
        $Customer = new Customer();
        $drno = $sanitize->sanitizeForString($data["drno"]);
        $drloaded = "DR_LOADED";

        $query = "SELECT transactionno, transactiondate, drno, sinumber, paymentmethod, terms, modeofpayment, customerno as customer, billingaddress, preparedby, checkedby, releasedby, receivedby, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, supplier, encodedby FROM tbl_pos_receipts WHERE drno = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $drno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $dr[] = $row;
            }

            $dr[0]['customer'] .= ',' . $Customer->LoadCustomerBusinessNameViaCustomerNo($dr[0]['customer'])['businessname'];
            $dr[0]['preparedby'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['preparedby']);
            $dr[0]['checkedby'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['checkedby']);
            $dr[0]['releasedby'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['releasedby']);

            $transactionno = $dr[0]['transactionno'];
            $transactiondate = $dr[0]['transactiondate'];
            $query = "SELECT transactionno FROM tbl_pos_cancellables WHERE transactionno = '$transactionno';";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $isCancellable = $result->num_rows > 0;

            $query = "SELECT referenceno FROM tbl_inventory_transactions WHERE referenceno = '$transactionno';";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $isCanceled = $result->num_rows > 0;
            $transactionTimestamp = strtotime($transactiondate);
            $currentTimestamp = time();

            // Calculate the difference in seconds between the current date and the transaction date
            $timeDifference = $currentTimestamp - $transactionTimestamp;

            // Calculate the number of days from the time difference
            $daysDifference = floor($timeDifference / (60 * 60 * 24));

            $isExchangable = ($daysDifference <= 7);

            echo json_encode(array(
                "MESSAGE" => $drloaded,
                "DR" => $dr,
                "CANCELLABLE" => $isCancellable,
                "CANCELED" => $isCanceled,
                "EXCHANGABLE" => $isExchangable
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPaymentReceipt($data) {
        
        $pr = [];
        $sanitize = new Sanitize();
        $Employee = new Employee();
        $Customer = new Customer();
        $prno = $sanitize->sanitizeForString($data["prno"]);
        $prloaded = "PR_LOADED";

        $query = "SELECT prno, prdate, customerno AS customer, drno, agent, paymentmethod, bankname, accountname, referenceno, amount, encodedby, receivedby, remittedby FROM tbl_pos_payments WHERE prno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $prno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $pr[] = $row;
            }

            $pr[0]['customer'] .= ',' . $Customer->LoadCustomerBusinessNameViaCustomerNo($pr[0]['customer'])['businessname'];
            $pr[0]['encodedby'] = $Employee->GetEmployeeeNameByEmployeeNo($pr[0]['encodedby']);
            $pr[0]['receivedby'] = $Employee->GetEmployeeeNameByEmployeeNo($pr[0]['receivedby']);

            echo json_encode(array(
                "MESSAGE" => $prloaded,
                "PR" => $pr
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadDRNo() {
        $sql = "SELECT drcount AS drnumber FROM tbl_drnumber LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = ($stmt->get_result()->fetch_assoc());
        $stmt->close();

        echo $row['drnumber'];
    }

    public function LoadPRNo() {
        $sql = "SELECT prcount AS prnumber FROM tbl_prnumber LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = ($stmt->get_result()->fetch_assoc());
        $stmt->close();

        echo $row['prnumber'];
    }

    public function LoadSINo($data) {
        
        $sanitize = new Sanitize();
        $agent = $sanitize->sanitizeForString($data["agent"]);

        $sql = "SELECT CONCAT(initials, '-', sicount) AS sinumber FROM tbl_sinumber WHERE agent = '$agent' LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = ($stmt->get_result()->fetch_assoc());
        $stmt->close();

        echo json_encode(array(
            "SI" => $row['sinumber']
        ));
    }

    public function LoadNonSettledCustomers() {
        
        $customers = [];
        $Customer = new Customer();
        $customersloaded = "CUSTOMERS_LOADED"; 

        $query = "SELECT DISTINCT(customerno) FROM tbl_pos_transactions_today WHERE paymentmethod = 'RECEIVABLE' AND balancedue > 0;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {

            while($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }

            $query2 = "SELECT DISTINCT(customerno) FROM tbl_pos_transactions_main WHERE paymentmethod = 'RECEIVABLE' AND balancedue > 0;";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $stmt2->close();

            if ($result2->num_rows > 0) {
                while($row2 = $result2->fetch_assoc()) {
                    $customers[] = $row2;
                }
            } else {
                echo 'NO_DATA';
                return;
            }

            for ($i=0; $i < count($customers) ; $i++) { 
                $customers[$i]['customerno'] .= ',' . $Customer->LoadCustomerBusinessNameViaCustomerNo($customers[$i]['customerno'])['businessname'];
            }

            echo json_encode(array(
                "MESSAGE" => $customersloaded,
                "CUSTOMERS" => $customers
            ));

            return;
        }

        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }

        $query2 = "SELECT DISTINCT(customerno) FROM tbl_pos_transactions_main WHERE paymentmethod = 'RECEIVABLE' AND balancedue > 0;";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $stmt2->close();

        if ($result2->num_rows > 0) {
            while($row2 = $result2->fetch_assoc()) {
                $customers[] = $row2;
            }
        }

        $uniqueCustomers = [];

        foreach ($customers as $customer) {
            $customerno = $customer['customerno'];
            if (!isset($uniqueCustomers[$customerno])) {
                $uniqueCustomers[$customerno] = $customer;
            }
        }

        $uniqueCustomers = array_values($uniqueCustomers);

        for ($i=0; $i < count($uniqueCustomers) ; $i++) { 
            $uniqueCustomers[$i]['customerno'] .= ',' . $Customer->LoadCustomerBusinessNameViaCustomerNo($uniqueCustomers[$i]['customerno'])['businessname'];
        }

        echo json_encode(array(
            "MESSAGE" => $customersloaded,
            "CUSTOMERS" => $uniqueCustomers
        ));
    }

    public function LoadCustomersDR($data) {
        
        $dr = [];
        $sanitize = new Sanitize();
        $customerno = $sanitize->sanitizeForString($data["customerno"]);
        $drloaded = "DR_LOADED"; 

        $query = "SELECT DISTINCT(drno) FROM tbl_pos_transactions_today WHERE customerno = ? AND paymentmethod = 'RECEIVABLE' AND balancedue > 0;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $customerno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {

            $query2 = "SELECT DISTINCT(drno) FROM tbl_pos_transactions_main WHERE customerno = ? AND paymentmethod = 'RECEIVABLE' AND balancedue > 0;";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param("s", $customerno);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $stmt2->close();

            if ($result2->num_rows > 0) {
                while($row2 = $result2->fetch_assoc()) {
                    $dr[] = $row2;
                }
            } else {
                echo 'NO_DATA';
                return;
            }

            echo json_encode(array(
                "MESSAGE" => $drloaded,
                "DR" => $dr
            ));

            return;
        }

        while($row = $result->fetch_assoc()) {
            $dr[] = $row;
        }

        $query2 = "SELECT DISTINCT(drno) FROM tbl_pos_transactions_main WHERE customerno = ? AND paymentmethod = 'RECEIVABLE' AND balancedue > 0;";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param("s", $customerno);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $stmt2->close();

        if ($result2->num_rows > 0) {
            while($row2 = $result2->fetch_assoc()) {
                $dr[] = $row2;
            }
        }
        
        echo json_encode(array(
            "MESSAGE" => $drloaded,
            "DR" => $dr
        ));
    }

    public function LoadDRDetails($data) {
        
        $dr = [];
        $sanitize = new Sanitize();
        $Employee = new Employee();
        $customerno = $sanitize->sanitizeForString($data["customerno"]);
        $drno = $sanitize->sanitizeForString($data["drno"]);
        $drloaded = "DR_LOADED"; 

        $query = "SELECT customerno, drno, totalamount, balancedue, duedate, agent FROM tbl_pos_transactions_today WHERE customerno = ? AND drno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $customerno, $drno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {

            $query2 = "SELECT customerno, drno, totalamount, balancedue, duedate, agent FROM tbl_pos_transactions_main WHERE customerno = ? AND drno = ? LIMIT 1;";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param("ss", $customerno, $drno);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $stmt2->close();

            if ($result2->num_rows > 0) {
                while($row2 = $result2->fetch_assoc()) {
                    $dr[] = $row2;
                }
            } else {
                echo 'NO_DATA';
                return;
            }

            $dr[0]['agent'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['agent']);

            echo json_encode(array(
                "MESSAGE" => $drloaded,
                "DR" => $dr
            ));
            
            return;
        }

        while($row = $result->fetch_assoc()) {
            $dr[] = $row;
        }

        $dr[0]['agent'] = $Employee->GetEmployeeeNameByEmployeeNo($dr[0]['agent']);

        echo json_encode(array(
            "MESSAGE" => $drloaded,
            "DR" => $dr
        ));
    }

    public function incrementSeries($terminal, $agent) {
        $updatetransactionno = "UPDATE tbl_terminals
        SET orfrom = orfrom + 1,
            orleft = orleft - 1,
            nextor = CONCAT(LEFT(nextor, LENGTH(noofreset)), '-', LPAD(0, LENGTH(orto) - LENGTH(orfrom), '0'), orfrom)
        WHERE terminal = ?;";
        $stmt = $this->conn->prepare($updatetransactionno);
        $stmt->bind_param('s', $terminal);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        $updatedrnumber = "UPDATE tbl_drnumber
        SET drcount = LPAD(
            CASE WHEN LPAD(drcount + 1, LENGTH(drcount), '0') <= drcount
                    THEN drcount + (drcount - LPAD(drcount + 1, LENGTH(drcount), '0') + 1)
                    ELSE drcount + 1
            END, LENGTH(drcount), '0');";
        $stmt = $this->conn->prepare($updatedrnumber);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        $updatesinumber = "UPDATE tbl_sinumber
        SET sicount = LPAD(
            CASE WHEN LPAD(sicount + 1, LENGTH(sicount), '0') <= sicount
                    THEN sicount + (sicount - LPAD(sicount + 1, LENGTH(sicount), '0') + 1)
                    ELSE sicount + 1
            END, LENGTH(sicount), '0') WHERE agent = '$agent';";
        $stmt = $this->conn->prepare($updatesinumber);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
    }

    public function computeReceivables($mode, $term, $start, $totalamount) {
        
        switch ($mode) {
            case "DAILY":
                $payments = (float)$term * 22;
                $interval = "day";
                $intervalCount = 1;
                $divisor = 22;
                $amortization = $totalamount / $divisor;
                break;
            case "WEEKLY":
                $payments = (float)$term * 4;
                $interval = "day";
                $intervalCount = 7;
                $divisor = 4;
                $amortization = ($totalamount / 22) * 7;
                break;
            case "MONTHLY":
                $payments = (float)$term;
                $interval = "month";
                $intervalCount = 1;
                $divisor = 1;
                $amortization = $totalamount / $term;
                break;
            case "SEMI-MONTHLY":
                $payments = (float)$term * 2;
                $interval = "day";
                $intervalCount = 15;
                $divisor = 2;
                $amortization = ($totalamount / $term) / 2;
                break;
            case "ANNUAL":
                $payments = (float)$term / 12;
                $interval = "year";
                $intervalCount = 1;
                $divisor = 1 / 12;
                $amortization = ($totalamount / $term) * 12;
                break;
            case "SEMI-ANNUAL":
                $payments = (float)$term / 6;
                $interval = "day";
                $intervalCount = 180;
                $divisor = 1 / 6;
                $amortization = (($totalamount / $term) * 12) / 2;
                break;
            case "QUARTERLY":
                $payments = (float)$term / 3;
                $interval = "day";
                $intervalCount = 90;
                $divisor = 1 / 3;
                $amortization = (($totalamount / $term) * 12) / 4;
                break;
            case "LUMPSUM":
                $payments = 1;
                $interval = "day";
                $intervalCount = (float)$term * 30;
                $divisor = 1;
                $multi = $term;
                $amortization = $totalamount * 1;
                break;
            default:
                return null;
        }
    
        $due = strtotime("+{$intervalCount} {$interval}", $start);
        $balancedue = $amortization;
    
        return array(
            "duedate" => $due,
            "balancedue" => $balancedue,
        );
    }    

    public function LoadPreparedBy() {

        $userid = $_SESSION['userid'];
        $sql = "SELECT fullname
        FROM tbl_employees
        WHERE employeeno IN (
          SELECT employeeno
          FROM tbl_users
          WHERE userid = ?
        );";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $row = ($stmt->get_result()->fetch_assoc());
        $stmt->close();

        echo $row['fullname'];
    }

    public function LoadProductsInTransaction($data) {
        
        $products = [];
        $sanitize = new Sanitize();
        $transactionno = $sanitize->sanitizeForString($data["transactionno"]);
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT p.transactionno, p.barcode, p.productname, p.cost, p.srp, p.quantity as pendingquantity,
        (SELECT COALESCE(SUM(i.quantity), 0) - COALESCE(p.quantity, 0) FROM tbl_inventory_current i WHERE i.barcode = p.barcode GROUP BY i.barcode) as available,
        amount, discount FROM tbl_pos_products_transaction p
        WHERE p.transactionno = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $transactionno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $productsloaded,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function RemoveProductInTransaction($data) {
        $sanitize = new Sanitize();
        $barcode = $sanitize->sanitizeForString($data["barcode"]);
        $transactionno = $sanitize->sanitizeForString($data["transactionno"]);

        $query = "DELETE FROM tbl_pos_products_transaction WHERE barcode = ? AND transactionno = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $barcode, $transactionno);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo 'SUCCESS';
        } else {
            $stmt->close();
            echo 'Nothing Changes!';
        }
    }

    public function SubmitAddProductInTransaction($data) {
        $sanitize = new Sanitize();
        $transactionno = $sanitize->sanitizeForString($data["transactionno"]);
        $barcode = $sanitize->sanitizeForString($data["barcode"]);
        $productname = strtoupper($sanitize->sanitizeForString($data["generalname"]) . ' - ' . $sanitize->sanitizeForString($data["brandname"]));
        $cost = '-';
        $srp = $sanitize->sanitizeForString($data["srp"]);
        $quantity = $sanitize->sanitizeForString($data["quantity"]);
        $amount = $sanitize->sanitizeForString($data["total"]);
        $discount = 0;

        $sql = "SELECT transactionno FROM tbl_pos_products_transaction WHERE transactionno = ? AND barcode = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $transactionno, $barcode);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Product Already Added In This Transaction.';
        } else {

            $sql = "SELECT COALESCE(p.pendquantity, 0) AS pendquantity,
            COALESCE(i.invquantity, 0) AS invquantity,
            COALESCE(i.invquantity, 0) - COALESCE(p.pendquantity, 0) AS available
            FROM 
            (SELECT COALESCE(SUM(quantity), 0) AS pendquantity, barcode 
            FROM tbl_pos_products_transaction 
            WHERE barcode = ?
            GROUP BY barcode) p
            RIGHT JOIN 
            (SELECT COALESCE(SUM(quantity), 0) AS invquantity, barcode 
            FROM tbl_inventory_current 
            WHERE barcode = ?
            GROUP BY barcode) i ON i.barcode = p.barcode;;";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ss', $barcode, $barcode);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();

            if ($quantity > $res->fetch_assoc()['available']) {
                echo 'Quantity is Unavailable!';
                return;
            }

            $sql = "INSERT INTO tbl_pos_products_transaction (transactionno, barcode, productname, cost, srp, quantity, amount, discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssss", $transactionno, $barcode, $productname, $cost, $srp, $quantity, $amount, $discount);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitReceipt($data) {

        $products = [];
        $sanitize = new Sanitize();
        $Supplier = new Supplier();
        $PredefinedProcess = new PredefinedProcess();
        $transactionno = $sanitize->sanitizeForString($data["transactionno"]);
        $stringdate = $sanitize->sanitizeForString($data["transactiondate"]);
        $transactiondate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["transactiondate"])));
        $drno = strtoupper($sanitize->sanitizeForString($data["drno"]));
        $agent = strtoupper($sanitize->sanitizeForString($data["agent"]));
        $sinumber = strtoupper($sanitize->sanitizeForString($data["sinumber"]));
        $paymentmethod = strtoupper($sanitize->sanitizeForString($data["paymentmethod"]));
        $terms = ($paymentmethod == 'RECEIVABLE') ? $sanitize->sanitizeForString($data["terms"]) : 'NONE';
        $modeofpayment = ($paymentmethod == 'RECEIVABLE') ? $sanitize->sanitizeForString($data["modeofpayment"]) : 'NONE';
        $customerno = strtoupper($sanitize->sanitizeForString($data["customer"]));
        $billingaddress = strtoupper($sanitize->sanitizeForString($data["billingaddress"]));
        $cashier = $_SESSION['userid'];
        $encodedby = $cashier;
        $preparedby = strtoupper($sanitize->sanitizeForString($data["preparedby"]));
        $checkedby = strtoupper($sanitize->sanitizeForString($data["checkedby"]));
        $releasedby = strtoupper($sanitize->sanitizeForString($data["releasedby"]));
        $receivedby = strtoupper($sanitize->sanitizeForString($data["receivedby"]));
        $terminal = $_SESSION['terminal'];
        

        $sql = "SELECT transactionno FROM tbl_pos_transactions_today WHERE transactionno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $transactionno);
        $stmt->execute();
        $transactionnores = $stmt->get_result();
        $stmt->close();

        $sql = "SELECT drno FROM tbl_pos_transactions_today WHERE drno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $drno);
        $stmt->execute();
        $drnores = $stmt->get_result();
        $stmt->close();

        $sql = "SELECT sinumber FROM tbl_pos_transactions_today WHERE sinumber = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $sinumber);
        $stmt->execute();
        $sinumberres = $stmt->get_result();
        $stmt->close();

        if ($transactionnores->num_rows >= 1) {
            echo 'This Transaction Number Is Already Used.';
        } else if ($drnores->num_rows >= 1) {
            echo 'This DR Number Is Already Used.';
        } else if ($sinumberres->num_rows >= 1) {
            echo 'This SI Number Is Already Used.';
        } else {

            $sql = "SELECT transactionno, barcode, productname, cost, srp, quantity, amount, discount FROM tbl_pos_products_transaction WHERE transactionno = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('s', $transactionno);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();

            $totalamount = 0;
            while($row = $res->fetch_assoc()) {
                $products[] = $row;
                $totalamount += $row['amount'];
            }

            $addtotodaytransactions = 'INSERT INTO tbl_pos_transactions_today (transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, lotnos, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            foreach ($products as $key => $pendingvalue) {
                // $supplierno = $supplier->GetSupplierNumberByBusinessName($pendingvalue->supplier);
                $duedate = 'NONE';
                $balancedue = 0;
                $totalcost = 0;
                $netsales = 0;
                $supplier = '';
                $barcode = $pendingvalue['barcode'];

                // CHECK IF QUANTITY IS AVAILABLE
                $quantitychecksql = "SELECT SUM(quantity) AS available FROM tbl_inventory_current WHERE barcode = ?;";
                $quantitycheckstmt = $this->conn->prepare($quantitychecksql);
                $quantitycheckstmt->bind_param('s', $barcode);
                $quantitycheckstmt->execute();
                $quantitycheckres = $quantitycheckstmt->get_result();
                $quantitycheckstmt->close();
                $quantitycheckrow = $quantitycheckres->fetch_assoc();

                if ($pendingvalue['quantity'] > $quantitycheckrow['available']) {
                    echo 'Quantity is Unavailable!';
                    return;
                }

                // LESS QUANTITY IN INVENTORY
                $quantitycheckperbatchsql = "SELECT batch, quantity AS batchquantity, expirationdate, unitprice, customunitprice, supplierno, lotno FROM tbl_inventory_current WHERE barcode = ?;";
                $quantitycheckperbatchstmt = $this->conn->prepare($quantitycheckperbatchsql);
                $quantitycheckperbatchstmt->bind_param('s', $barcode);
                $quantitycheckperbatchstmt->execute();
                $quantitycheckperbatchres = $quantitycheckperbatchstmt->get_result();
                $quantitycheckperbatchstmt->close();
                
                if ($quantitycheckperbatchres->num_rows <= 0) {
                    echo 'Missing Batches!';
                    return;
                }

                $batches = [];
                while($row = $quantitycheckperbatchres->fetch_assoc()) {
                    $batches[] = $row;
                }

                usort($batches, function ($a, $b) {
                    if ($a['expirationdate'] == $b['expirationdate']) {
                        // If expiration dates are equal, sort by batch number in ascending order
                        return $a['batch'] - $b['batch'];
                    }
                    // Sort by expiration date in ascending order
                    return strtotime($a['expirationdate']) - strtotime($b['expirationdate']);
                });

                $lotnos = "";
                $existingLotNos = [];

                $remainingPendingQuantity = $pendingvalue['quantity'];
                $pendingvalue['cost'] = 0;
                $i = 0;

                foreach ($batches as $batchvalue) {
                    $batch = $batchvalue['batch'];
                    $lotno = $batchvalue['lotno'];
                    $batchquantity = $batchvalue['batchquantity'];
                    $cost = ($batchvalue['unitprice'] == 0) ? $batchvalue['customunitprice'] : $batchvalue['unitprice'];
                    
                    if (!in_array($lotno, $existingLotNos)) {
                        $lotnos .= ("/" . $lotno);
                        $existingLotNos[] = $lotno;
                    }

                    if ($remainingPendingQuantity > $batchquantity) {
                        $lesspendingtocurrentsql = "UPDATE tbl_inventory_current SET quantity = quantity - ? WHERE barcode = ? AND batch = ?";
                        $lesspendingtocurrenttmt = $this->conn->prepare($lesspendingtocurrentsql);
                        $lesspendingtocurrenttmt->bind_param('sss', $batchquantity, $barcode, $batch);
                        $lesspendingtocurrenttmt->execute();
                        $lesspendingtocurrenttmt->close();
                        
                        $totalcost += ($cost * $batchquantity);
                        $remainingPendingQuantity -= $batchquantity;
                        $pendingvalue['cost'] += $cost;
                        $supplier = $batchvalue['supplierno'];

                        // ADD DETAILS TO CANCELLABLES
                        $sql = "INSERT INTO tbl_pos_cancellables SELECT NULL, '$transactionno', '$transactiondate', '$sinumber', '$drno', '$batch', supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, '$batchquantity', producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE barcode = '$barcode' AND batch = '$batch' AND lotno = '$lotno'";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute();
                        $stmt->close();

                        // ADD DETAILS TO RETURNABLES
                        $sql = "INSERT INTO tbl_pos_returnables SELECT NULL, '$transactionno', '$transactiondate', '$sinumber', '$drno', '$batch', supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, '$batchquantity', producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE barcode = '$barcode' AND batch = '$batch' AND lotno = '$lotno'";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $lesspendingtocurrentsql = "UPDATE tbl_inventory_current SET quantity = quantity - ? WHERE barcode = ? AND batch = ?";
                        $lesspendingtocurrenttmt = $this->conn->prepare($lesspendingtocurrentsql);
                        $lesspendingtocurrenttmt->bind_param('sss', $remainingPendingQuantity, $barcode, $batch);
                        $lesspendingtocurrenttmt->execute();
                        $lesspendingtocurrenttmt->close();

                        $totalcost += ($cost * $remainingPendingQuantity);
                        $pendingvalue['cost'] += $cost;
                        $supplier = ($i == 0) ? $batchvalue['supplierno'] : (', ' . $batchvalue['supplierno']);

                        // ADD DETAILS TO CANCELLABLES
                        $sql = "INSERT INTO tbl_pos_cancellables SELECT NULL, '$transactionno', '$transactiondate', '$sinumber', '$drno', '$batch', supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, '$remainingPendingQuantity', producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE barcode = '$barcode' AND batch = '$batch' AND lotno = '$lotno'";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute();
                        $stmt->close();

                        // ADD DETAILS TO RETURNABLES
                        $sql = "INSERT INTO tbl_pos_returnables SELECT NULL, '$transactionno', '$transactiondate', '$sinumber', '$drno', '$batch', supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, '$remainingPendingQuantity', producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE barcode = '$barcode' AND batch = '$batch' AND lotno = '$lotno'";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute();
                        $stmt->close();
                        break;
                    }

                    $i++;
                }

                // REMOVE 0s
                $query = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
    
                if ($result->num_rows > 0) {
                    $sql = "INSERT INTO tbl_inventory_deleted SELECT NULL, batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $stmt->close();

                    $sql = "DELETE FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $stmt->close();
                }

                // IDENTIFY RECEIVABLES
                if ($paymentmethod == 'RECEIVABLE') {
                    $receivables = $this->computeReceivables($modeofpayment, $terms, strtotime($stringdate), $totalamount);
                    $duedate = date('m/d/Y', $receivables['duedate']);
                    $balancedue = $receivables['balancedue'];
                }

                // ADD TO TODAY'S POS TRANSACTIONS
                $addtotransactionstmt = $this->conn->prepare($addtotodaytransactions);
                $netsales = $pendingvalue['amount'] - $totalcost;
                $addtotransactionstmt->bind_param("ssssssssssssssssssssssssssssss", $transactionno, $transactiondate, $customerno, $billingaddress, $terms, $modeofpayment, $paymentmethod, $duedate, $balancedue, $cashier, $pendingvalue['barcode'], $pendingvalue['productname'], $pendingvalue['cost'], $pendingvalue['srp'], $pendingvalue['quantity'], $pendingvalue['amount'], $pendingvalue['discount'], $totalcost, $totalamount, $netsales, $lotnos, $agent, $sinumber, $drno, $supplier, $encodedby, $preparedby, $checkedby, $releasedby, $receivedby);
                if (!$addtotransactionstmt->execute()) {
                    $addtotransactionstmt->close();
                    printf($addtotransactionstmt->error);
                    return;
                }
                $addtotransactionstmt->close();
            }

            // REMOVE FROM tbl_pos_products_transaction
            $delete = "DELETE FROM tbl_pos_products_transaction WHERE transactionno = ?;";
            $stmt = $this->conn->prepare($delete);
            $stmt->bind_param('s', $transactionno);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();

            // INCREMENT SERIES
            $this->incrementSeries($terminal, $agent);
            
            $sql = "INSERT INTO tbl_pos_receipts (transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, lotnos, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby) SELECT transactionno, transactiondate, customerno, billingaddress, terms, modeofpayment, paymentmethod, duedate, balancedue, cashier, barcode, productname, cost, srp, quantity, amount, discount, totalcost, totalamount, netsales, lotnos, agent, sinumber, drno, supplier, encodedby, preparedby, checkedby, releasedby, receivedby FROM tbl_pos_transactions_today WHERE transactionno = ? AND transactiondate = ? AND sinumber = ? AND drno = ?;";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $transactionno, $transactiondate, $sinumber, $drno);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                
                $GJstatus = $PredefinedProcess->GJEntryCustomer($transactionno, $sinumber, $drno);
                    if ($GJstatus != 'SUCCESS') {
                        echo 'JV Entry Failed';
                        return;
                    }

                    echo 'SUCCESS';
                    
            }
        }
    }

    public function CancelDeliveryReceipt($data) {
        
        $sanitize = new Sanitize();
        $drno = strtoupper($sanitize->sanitizeForString($data["drno"]));
        $user = $_SESSION['userid'];

        $products = [];
        $productssql = "SELECT barcode, transactionno, transactiondate, sinumber, drno FROM tbl_pos_receipts WHERE drno = ?;";
        $productsstmt = $this->conn->prepare($productssql);
        $productsstmt->bind_param('s', $drno);
        $productsstmt->execute();
        $productsres = $productsstmt->get_result();
        $productsstmt->close();

        if ($productsres->num_rows <= 0) {
            echo 'Missing DR. Couldn\'t be canceled.';
            return;
        }

        while($row = $productsres->fetch_assoc()) {
            $products[] = $row;
        }

        if (strtotime($products[0]['transactiondate']) <> strtotime(date('m/d/Y'))) {
            echo 'Non-cancellable DR. Transaction Date is behind.';
            return;
        }

        foreach ($products as $key => $product) {
            $barcode = $product['barcode'];
            $transactionno = $product['transactionno'];
            $sinumber = $product['sinumber'];
            $transactiondate = $product['transactiondate'];

            $batches = [];
            $batchessql = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_pos_cancellables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode';";
            $batchesstmt = $this->conn->prepare($batchessql);
            $batchesstmt->execute();
            $batchesres = $batchesstmt->get_result();
            $batchesstmt->close();

            if ($batchesres->num_rows <= 0) {
                echo 'Non-cancellable DR. Product not found.';
                return;
            }

            while($row = $batchesres->fetch_assoc()) {
                $batches[] = $row;
            }

            foreach ($batches as $key => $value) {
                $batch = $value['batch'];
                $quantity = $value['quantity'];

                // FIND PRODUCT AND BATCH IN CURRENT INVENTORY
                $invsql = "SELECT batch, barcode FROM tbl_inventory_current WHERE barcode = '$barcode' AND batch = '$batch' LIMIT 1;";
                $invstmt = $this->conn->prepare($invsql);
                $invstmt->execute();
                $invres = $invstmt->get_result();
                $invstmt->close();

                // RETURN PRODUCT TO CURRENT INVENTORY
                $returningtocurrentquery = "UPDATE tbl_inventory_current SET quantity = quantity + $quantity WHERE barcode = '$barcode' AND batch = '$batch';";

                if ($invres->num_rows == 0) {
                    $returningtocurrentquery = "INSERT INTO tbl_inventory_current SELECT NULL, batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_pos_cancellables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode' AND batch = '$batch' LIMIT 1;";
                }

                $returningtocurrentquerystmt = $this->conn->prepare($returningtocurrentquery);
                $returningtocurrentquerystmt->execute();
                $returningtocurrentquerystmt->close();

                // INSERT INTO TRANSACTIONS
                $insert = "INSERT INTO tbl_inventory_transactions (transactiondate, user, referenceno, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, batch, barcode, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, category, purpose, dateencoded) 
                SELECT '$transactiondate', '$user', '$transactionno', supplierno, supplier, '-', '-', '-', '-', producttotalprice, 'CANCEL COMPLETED', 'CANCELED', batch, barcode, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, 'INCOMING', 'CANCELLATION', '$transactiondate' FROM tbl_pos_cancellables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode' AND batch = '$batch' LIMIT 1;";
                $stmt = $this->conn->prepare($insert);
                $stmt->execute();
                $stmt->close();
            }

            // REMOVE FROM RETURNABLES & CANCELABLES
            $removefromreturnablessql = "DELETE FROM tbl_pos_returnables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode';";
            $removefromreturnablesstmt = $this->conn->prepare($removefromreturnablessql);
            $removefromreturnablesstmt->execute();
            $removefromreturnablesstmt->close();

            $removefromcancellablessql = "DELETE FROM tbl_pos_cancellables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode';";
            $removefromcancellablesstmt = $this->conn->prepare($removefromcancellablessql);
            $removefromcancellablesstmt->execute();
            $removefromcancellablesstmt->close();
        }

        echo 'SUCCESS';
    }

    public function ReturnDeliveryReceipt($data) {
        
        $sanitize = new Sanitize();
        $drno = strtoupper($sanitize->sanitizeForString($data["drno"]));
        $user = $_SESSION['userid'];

        $products = [];
        $productssql = "SELECT barcode, transactionno, transactiondate, sinumber, drno FROM tbl_pos_receipts WHERE drno = ?;";
        $productsstmt = $this->conn->prepare($productssql);
        $productsstmt->bind_param('s', $drno);
        $productsstmt->execute();
        $productsres = $productsstmt->get_result();
        $productsstmt->close();

        if ($productsres->num_rows <= 0) {
            echo 'Missing DR. Couldn\'t be returned.';
            return;
        }

        while($row = $productsres->fetch_assoc()) {
            $products[] = $row;
        }

        $transactionTimestamp = strtotime($products[0]['transactiondate']);
        $currentTimestamp = time();
        $timeDifference = $currentTimestamp - $transactionTimestamp;
        $daysDifference = floor($timeDifference / (60 * 60 * 24));

        if ($daysDifference > 14) {
            echo 'Non-returnable DR. Transaction Date is behind.';
            return;
        }

        foreach ($products as $key => $product) {
            $barcode = $product['barcode'];
            $transactionno = $product['transactionno'];
            $sinumber = $product['sinumber'];
            $transactiondate = $product['transactiondate'];

            $batches = [];
            $batchessql = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_pos_returnables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode';";
            $batchesstmt = $this->conn->prepare($batchessql);
            $batchesstmt->execute();
            $batchesres = $batchesstmt->get_result();
            $batchesstmt->close();

            if ($batchesres->num_rows <= 0) {
                echo 'Non-cancellable DR. Product not found.';
                return;
            }

            while($row = $batchesres->fetch_assoc()) {
                $batches[] = $row;
            }

            foreach ($batches as $key => $value) {
                $batch = $value['batch'];
                $quantity = $value['quantity'];

                // FIND PRODUCT AND BATCH IN CURRENT INVENTORY
                $invsql = "SELECT batch, barcode FROM tbl_inventory_current WHERE barcode = '$barcode' AND batch = '$batch' LIMIT 1;";
                $invstmt = $this->conn->prepare($invsql);
                $invstmt->execute();
                $invres = $invstmt->get_result();
                $invstmt->close();

                // RETURN PRODUCT TO CURRENT INVENTORY
                $returningtocurrentquery = "UPDATE tbl_inventory_current SET quantity = quantity + $quantity WHERE barcode = '$barcode' AND batch = '$batch';";

                if ($invres->num_rows == 0) {
                    $returningtocurrentquery = "INSERT INTO tbl_inventory_current SELECT NULL, batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose FROM tbl_pos_returnables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode' AND batch = '$batch' LIMIT 1;";
                }

                $returningtocurrentquerystmt = $this->conn->prepare($returningtocurrentquery);
                $returningtocurrentquerystmt->execute();
                $returningtocurrentquerystmt->close();

                // INSERT INTO TRANSACTIONS
                $insert = "INSERT INTO tbl_inventory_transactions (transactiondate, user, referenceno, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, batch, barcode, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, category, purpose, dateencoded) 
                SELECT '$transactiondate', '$user', '$transactionno', supplierno, supplier, '-', '-', '-', '-', producttotalprice, 'RETURN COMPLETED', 'RETURNED', batch, barcode, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, 'INCOMING', 'RETURNS', '$transactiondate' FROM tbl_pos_returnables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode' AND batch = '$batch' LIMIT 1;";
                $stmt = $this->conn->prepare($insert);
                $stmt->execute();
                $stmt->close();
            }

            // REMOVE FROM RETURNABLES & CANCELABLES
            $removefromreturnablessql = "DELETE FROM tbl_pos_returnables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode';";
            $removefromreturnablesstmt = $this->conn->prepare($removefromreturnablessql);
            $removefromreturnablesstmt->execute();
            $removefromreturnablesstmt->close();

            $removefromcancellablessql = "DELETE FROM tbl_pos_cancellables WHERE transactionno = '$transactionno' AND sinumber = '$sinumber' AND drno = '$drno' AND barcode = '$barcode';";
            $removefromcancellablesstmt = $this->conn->prepare($removefromcancellablessql);
            $removefromcancellablesstmt->execute();
            $removefromcancellablesstmt->close();
        }

        echo 'SUCCESS';
    }

    public function EntryPOSPayment($data) {

        $sanitize = new Sanitize();
        $customer = $sanitize->sanitizeForString($data["customer"]);
        $prno = $sanitize->sanitizeForString($data["prno"]);
        $prdate = date('m/d/Y');
        $drno = $sanitize->sanitizeForString($data["drno"]);
        $agent = $sanitize->sanitizeForString($data["agent"]);
        $paymentmethod = $sanitize->sanitizeForString($data["paymentmethod"]);
        $bankname = ($paymentmethod == 'CHECKS') ? $sanitize->sanitizeForString($data["bankname"]) : 'NONE';
        $accountname = ($paymentmethod == 'E-WALLET' || $paymentmethod == 'CHECKS') ? $sanitize->sanitizeForString($data["accountname"]) : 'NONE';
        $referenceno = ($paymentmethod == 'E-WALLET' || $paymentmethod == 'CHECKS') ? $sanitize->sanitizeForString($data["referenceno"]) : 'NONE';
        $amount = $sanitize->sanitizeForString($data["amount"]);
        $receivedby = $sanitize->sanitizeForString($data["receivedby"]);
        $remittedby = $sanitize->sanitizeForString($data["remittedby"]);
        $cashier = $_SESSION['userid'];
        $encodedby = $cashier;

        $sql = "SELECT prno FROM tbl_pos_payments WHERE prno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $prno);
        $stmt->execute();
        $prnores = $stmt->get_result();
        $stmt->close();

        if ($prnores->num_rows >= 1) {
            echo 'This Payment Receipt Is Already Used.';
            return;
        }

        // INSERT TO PAYMENTS 
        $sql = "INSERT INTO tbl_pos_payments (prno, prdate, customerno, paymentmethod, bankname, accountname, referenceno, cashier, agent, amount, drno, encodedby, remittedby, receivedby) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssssssss", $prno, $prdate, $customer, $paymentmethod, $bankname, $accountname, $referenceno, $cashier, $agent, $amount, $drno, $encodedby, $remittedby, $receivedby);

        if (!$stmt->execute()) {
            printf($stmt->error);
            $stmt->close();
            return;
        }
        
        $stmt->close();

        // LOAD PR SERIES
        $updateprno = "UPDATE tbl_prnumber
        SET prcount = LPAD(
            CASE WHEN LPAD(prcount + 1, LENGTH(prcount), '0') <= prcount
                    THEN prcount + (prcount - LPAD(prcount + 1, LENGTH(prcount), '0') + 1)
                    ELSE prcount + 1
            END, LENGTH(prcount), '0');";
        $stmt = $this->conn->prepare($updateprno);
        $stmt->execute();
        $stmt->close();

        echo 'SUCCESS';

        // echo json_encode(array(
        //     'customer' => $customer,
        //     'prno' => $prno,
        //     'drno' => $drno,
        //     'agent' => $agent,
        //     'paymentmethod' => $paymentmethod,
        //     'bankname' => $bankname,
        //     'accountname' => $accountname,
        //     'referenceno' => $referenceno,
        //     'amount' => $amount,
        //     'receivedby' => $receivedby,
        //     'remittedby' => $remittedby,
        // ));
    }
}
?>