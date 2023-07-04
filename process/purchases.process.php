<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");
include_once("products.process.php");
include_once("transactions.process.php");
include_once("pre-function.process.php");

class Purchase extends Database {
    
    public function LoadPONumber() {
        echo 'PO00' . substr(str_shuffle('PUR00' . substr(str_shuffle('0123456789'), 0, 6)), 0, 6);
    }

    public function LoadReceivingNumber() {
        echo 'REC00' . substr(str_shuffle('REC00' . substr(str_shuffle('0123456789'), 0, 6)), 0, 6);
    }

    public function LoadConfigCounts() {
        
        $orders = 0;
        $receiving = 0;
        $returns = 125;
        $countsloaded = "COUNTS_LOADED";

        $query1 = "SELECT COUNT(DISTINCT(purchaseno)) AS orders FROM tbl_purchase_orders WHERE status = 'READY';";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->execute();
        $row1 = ($stmt1->get_result()->fetch_assoc());
        $stmt1->close();

        $query2 = "SELECT COUNT(DISTINCT(purchaseno)) AS receiving FROM tbl_purchase_orders WHERE status = 'READY' OR (status = 'PARTIAL' AND purchaseno NOT IN (SELECT purchaseno FROM tbl_purchase_orders WHERE status = 'RECEIVED'));";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $row2 = ($stmt2->get_result()->fetch_assoc());
        $stmt2->close();

        $query3 = "SELECT COUNT(id) AS returned FROM tbl_purchase_returned;";
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->execute();
        $row3 = ($stmt3->get_result()->fetch_assoc());
        $stmt3->close();

        try {
            $orders = $row1['orders'];
        } catch (\Throwable $th) { }

        try {
            $receiving = $row2['receiving'];
        } catch (\Throwable $th) { }

        try {
            $returns = $row3['returned'];
        } catch (\Throwable $th) { }

        echo json_encode(array(
            "MESSAGE" => $countsloaded,
            "ORDERS" => $orders,
            "RECEIVING" => $receiving,
            "RETURNS" => $returns,
        ));
    }

    public function LoadAllPurchaseOrderSuppliers() {
        
        $suppliers = [];
        $suppliersloaded = "SUPPLIERS_LOADED";

        $query = "SELECT DISTINCT(supplierno) AS supplierno, supplier FROM tbl_purchase_orders t;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $suppliersloaded,
                "SUPPLIERS" => $suppliers
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPurchaseOrders() {
        
        $purchasess = [];
        $purchasessloaded = "PURCHASES_LOADED";

        $query = "SELECT purchaseno, podate, totalprice, status FROM tbl_purchase_orders WHERE status = 'READY' OR (status = 'PARTIAL' AND purchaseno NOT IN (SELECT purchaseno FROM tbl_purchase_orders WHERE status = 'RECEIVED'))  GROUP BY purchaseno;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchasess[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $purchasessloaded,
                "PURCHASES" => $purchasess
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadReceivedPurchaseOrders() {
        
        $purchasess = [];
        $purchasessloaded = "PURCHASES_LOADED";

        $query = "SELECT receivingno, datereceived, totalprice FROM tbl_purchase_received WHERE status = 'RECEIVED' OR status = 'PARTIAL';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchasess[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $purchasessloaded,
                "PURCHASES" => $purchasess
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPurchaseOrder($data) {
        
        $purchaseorder = [];
        $products = [];
        $sanitize = new Sanitize();
        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $status = (isset($data["status"])) ? $sanitize->sanitizeForString($data["status"]) : 'READY';
        $purchaseloaded = "ORDER_LOADED";

        $query = ($status != 'PARTIAL') ? "SELECT id, purchaseno, podate, purpose, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, dateencoded FROM tbl_purchase_orders WHERE purchaseno = ? LIMIT 1"
         : "SELECT id, purchaseno, podate, purpose, receivingno, datereceived, drno, invoiceno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, dateencoded FROM tbl_purchase_received WHERE purchaseno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $purchaseno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchaseorder[] = $row;
            }

            try {
                $query = ($status != 'PARTIAL') ? "SELECT id, purchaseno, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice FROM tbl_purchase_order_products WHERE purchaseno = ? ORDER BY id DESC;" 
                : "SELECT MAX(id) AS id, purchaseno, productno, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, SUM(receivedquantity) as receivedquantity FROM tbl_purchase_order_products WHERE purchaseno = ? GROUP BY productno ORDER BY id DESC;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $purchaseno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }

                if ($status == 'PARTIAL') {
                    $products = array_map(function ($product) {
                        $product['quantity'] = abs($product['quantity'] - $product['receivedquantity']);
                        return $product;
                    }, $products);
                
                    $products = array_filter($products, function ($product) {
                        return $product['quantity'] > 0;
                    });
                
                    $products = array_values($products);
                }

            } catch (\Throwable $th) {}

            echo json_encode(array(
                "MESSAGE" => $purchaseloaded,
                "ORDER" => $purchaseorder,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPurchaseOrderNonFiltered($data) {
        
        $products = [];
        $sanitize = new Sanitize();
        $purchaseno = trim($sanitize->sanitizeForString($data["purchaseno"]));
        $purchaseloaded = "ORDER_LOADED";

        $query = "SELECT totalprice FROM tbl_purchase_orders WHERE purchaseno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $purchaseno);
        $stmt->execute();
        $totalprice = ($stmt->get_result())->fetch_array()['totalprice'];
        $stmt->close();

        $query =  "SELECT
            barcode,
            generalname,
            brandname,
            uom,
            unitprice,
            customunitprice,
            CASE WHEN SUM(receivedquantity) = 0 THEN quantity ELSE SUM(receivedquantity) END AS `quantity`,
            SUM(producttotalprice) AS `producttotalprice`
        FROM
            tbl_purchase_order_products
        WHERE
            purchaseno = ?
        GROUP BY
            barcode
        ORDER BY
            id DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $purchaseno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $purchaseloaded,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadReceivedPurchaseOrder($data) {
        
        $purchaseorder = [];
        $products = [];
        $sanitize = new Sanitize();
        $receivingno = $sanitize->sanitizeForString($data["receivingno"]);
        $purchaseloaded = "ORDER_LOADED";

        $query = "SELECT id, purchaseno, podate, receivingno, datereceived, drno, invoiceno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, dateencoded FROM tbl_purchase_received WHERE receivingno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $receivingno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchaseorder[] = $row;
            }

            $purchaseno = $purchaseorder[0]['purchaseno'];

            try {
                $query = "SELECT id, purchaseno, productno, generalname, brandname, uom, unitprice, customunitprice, receivedquantity, producttotalprice, expirationdate, lotno FROM tbl_purchase_order_products WHERE purchaseno = ? ORDER BY id DESC;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $purchaseno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            } catch (\Throwable $th) {}

            echo json_encode(array(
                "MESSAGE" => $purchaseloaded,
                "ORDER" => $purchaseorder,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadReceivedPurchaseOrderNonFiltered($data) {
        
        $purchaseorder = [];
        $products = [];
        $sanitize = new Sanitize();
        $receivingno = trim($sanitize->sanitizeForString($data["receivingno"]));
        $purchaseloaded = "ORDER_LOADED";

        $query = "SELECT purchaseno FROM tbl_purchase_received WHERE receivingno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $receivingno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchaseorder[] = $row;
            }

            $purchaseno = $purchaseorder[0]['purchaseno'];

            try {
                $query = "SELECT barcode, generalname, brandname, uom, unitprice, customunitprice, receivedquantity AS quantity, producttotalprice FROM tbl_purchase_order_products WHERE purchaseno = ? AND receivingno = ? ORDER BY id DESC;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('ss', $purchaseno, $receivingno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            } catch (\Throwable $th) {}

            echo json_encode(array(
                "MESSAGE" => $purchaseloaded,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function DeleteProducFromPurchaseOrder($data) {
        
        $sanitize = new Sanitize();
        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $id = $sanitize->sanitizeForString($data["id"]);
        $purchaseloaded = "ORDER_LOADED";

        $query = "DELETE FROM tbl_purchase_order_products WHERE purchaseno = ? AND id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $purchaseno, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo 'SUCCESS';
        } else {
            $stmt->close();
            echo 'Nothing Changes!';
        }
    }

    public function UpdateTotalPrice($data) {
        
        $sanitize = new Sanitize();
        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $totalprice = $sanitize->sanitizeForString($data["totalprice"]);
        
        $sql = "UPDATE tbl_purchase_orders SET totalprice = ? WHERE purchaseno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $totalprice, $purchaseno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function SubmitPurchaseOrder($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();

        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $podate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["podate"])));
        $purpose = strtoupper($sanitize->sanitizeForString($data["purpose"]));
        $supplier = strtoupper($sanitize->sanitizeForString($data["supplier"]));
        $supplierno = $Supplier->GetSupplierNumberByBusinessName($supplier);
        $address = strtoupper($sanitize->sanitizeForString($data["address"]));
        $supplieropenbalance = $sanitize->sanitizeForString($data["supplieropenbalance"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        $totalprice = $sanitize->sanitizeForString($data["totalprice"]);

        $products = json_decode($data['products']);
        
        $dateencoded = date('m/d/Y');

        $sql = "SELECT purchaseno FROM tbl_purchase_orders WHERE purchaseno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $purchaseno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Purchases Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_purchase_orders (purchaseno, podate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssss", $purchaseno, $podate, $purpose, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $totalprice, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                if ($this->SubmitProductOrders($products, $purchaseno)) {
                    echo 'SUCCESS';
                } else {
                    'SOMETHING DID NOT GO RIGHT.';
                }
            }
        }
    }

    public function SubmitProductOrders($products, $purchaseno) {
        
        $Product = new Product();

        $insert = 'INSERT INTO tbl_purchase_order_products (purchaseno, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice)';
        $values = '';
        $first = true;
        foreach ($products as $key => $value) {
            $productno = $value->productno;
            $productarr = $Product->GetProductByProductNo($productno);
            $generalname = $productarr['generalname'];
            $barcode = $productarr['barcode'];

            if (!$first) {
                $values .= ',';
            }

            $values .= "('" . $purchaseno . "', '" . $productno . "', '" . $barcode . "', '" . $generalname . "', '" . $value->brandname . "', '" . $value->uom . "', '" . $value->unitprice . "', '" . $value->customunitprice . "', '" . $value->quantity . "', '" . $value->producttotalprice . "')";
            $first = false;
        }
        $query = $insert . ' VALUES ' . $values;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        if ($result > 0) {
            return "PRODUCT_SUCCESS";
        } else {
            return "PRODUCT_ERROR";
        }
    }

    public function SubmitUpdatePurchaseOrder($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();

        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $podate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["podate"])));
        $purpose = strtoupper($sanitize->sanitizeForString($data["purpose"]));
        $supplier = strtoupper($sanitize->sanitizeForString($data["supplier"]));
        $supplierno = $Supplier->GetSupplierNumberByBusinessName($supplier);
        $address = strtoupper($sanitize->sanitizeForString($data["address"]));
        $supplieropenbalance = $sanitize->sanitizeForString($data["supplieropenbalance"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        $totalprice = $sanitize->sanitizeForString($data["totalprice"]);
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));

        $products = json_decode($data['products']);
        
        $sql = "UPDATE tbl_purchase_orders SET podate = ?, purpose = ?, supplierno = ?, supplier = ?, address = ?, supplieropenbalance = ?, terms = ?, modeofpayment = ?, totalprice = ?, remarks = ? WHERE purchaseno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $podate, $purpose, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $totalprice, $remarks, $purchaseno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            if ($this->SubmitUpdateProductOrders($products) == 'PRODUCT_SUCCESS') {
                echo 'SUCCESS';
            } else {
                'SOMETHING DID NOT GO RIGHT.';
            }
        }
    }

    public function SubmitUpdateProductOrders($products) {
        
        $Product = new Product();
        $update = 'UPDATE tbl_purchase_order_products SET productno = ?, barcode = ?, generalname = ?, brandname = ?, uom = ?, unitprice = ?, customunitprice = ?, quantity = ?, producttotalprice = ? WHERE id = ?';
        $passed = true;
        foreach ($products as $key => $value) {
            $productno = $value->productno;
            $productarr = $Product->GetProductByProductNo($productno);
            $generalname = $productarr['generalname'];
            $barcode = $productarr['barcode'];

            $stmt = $this->conn->prepare($update);
            $stmt->bind_param("ssssssssss", $productno, $barcode, $generalname, $value->brandname, $value->uom, $value->unitprice, $value->customunitprice, $value->quantity, $value->producttotalprice, $value->id);
            if (!$stmt->execute()) {
                $stmt->close();
                $passed = false;
                printf($stmt->error);
            }
            $stmt->close();
        }
        

        if ($passed) {
            return "PRODUCT_SUCCESS";
        } else {
            return "PRODUCT_ERROR";
        }
    }

    public function ReceivePurchaseOrder($data) {
        
        $sanitize = new Sanitize();
        $Product = new Product();
        $Transaction = new Transaction();
        $PredefinedProcess = new PredefinedProcess();

        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $receivingno = strtoupper($sanitize->sanitizeForString($data["receivingno"]));
        $datereceived = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datereceived"])));
        $drno = strtoupper($sanitize->sanitizeForString($data["drno"]));
        $invoiceno = strtoupper($sanitize->sanitizeForString($data["invoiceno"]));
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        $initialstatus = strtoupper($sanitize->sanitizeForString($data["initialstatus"]));
        $status = ($remarks == 'RECEIVED COMPLETED') ? 'RECEIVED' : 'PARTIAL';
        
        $expirationdates = json_decode($data['expirationdates']);

        $sql = "UPDATE tbl_purchase_orders SET status = ?, remarks = ? WHERE purchaseno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $status, $remarks, $purchaseno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            $returned = $this->SubmitProductExpiration($expirationdates, $receivingno, $initialstatus);
            if ($returned['message'] == 'PRODUCT_SUCCESS') {

                $expirationdates = $returned['expirationdates'];
                $totalprice = $returned['totalprice'];

                // TRANSFER TO RECEIVED TABLE
                if ($initialstatus != 'PARTIAL') {
                    $transfertoreceived = "INSERT INTO tbl_purchase_received (purchaseno, podate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, dateencoded) SELECT purchaseno, podate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, '$totalprice', remarks, status, dateencoded FROM tbl_purchase_orders WHERE purchaseno = ? ORDER BY id DESC LIMIT 1;";
                    $stmt = $this->conn->prepare($transfertoreceived);
                    $stmt->bind_param("s", $purchaseno);
                    $stmt->execute();
                    $stmt->close();

                    $sql = "UPDATE tbl_purchase_received SET status = ?, receivingno = ?, datereceived = ?, drno = ?, invoiceno = ?, remarks = ? WHERE purchaseno = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("sssssss", $status, $receivingno, $datereceived, $drno, $invoiceno, $remarks, $purchaseno);
                } else {
                    $inserttoreceivedapartial = "INSERT INTO tbl_purchase_received (purchaseno, podate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, receivingno, datereceived, drno, invoiceno, dateencoded) SELECT purchaseno, podate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, '$totalprice', '$remarks', '$status', '$receivingno', '$datereceived', '$drno', '$invoiceno', dateencoded FROM tbl_purchase_orders WHERE purchaseno = ?;";
                    $stmt = $this->conn->prepare($inserttoreceivedapartial);
                    $stmt->bind_param("s", $purchaseno);
                }

                if (!$stmt->execute()) {
                    $stmt->close();
                    printf($stmt->error);
                } else {
                    $stmt->close();

                    // INSERT TO CURRENT INVEMTORY TABLE
                    $asof = date('m/d/Y');
                    $passed = true;

                    $find = "SELECT supplierno, supplier, purpose FROM tbl_purchase_orders WHERE purchaseno = ? ORDER BY id DESC LIMIT 1;";
                    $stmt = $this->conn->prepare($find);
                    $stmt->bind_param("s", $purchaseno);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $stmt->close();
                    $rows = ($res->fetch_assoc());
                    $supplierno = $rows['supplierno'];
                    $supplier = $rows['supplier'];
                    $purpose = $rows['purpose'];

                    $insert = "INSERT INTO tbl_inventory_current (batch, productno, supplierno, supplier, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, purpose) SELECT batch, productno, '$supplierno', '$supplier', barcode, generalname, brandname, uom, unitprice, customunitprice, receivedquantity, producttotalprice, expirationdate, lotno, ?, ?, '$purpose' FROM tbl_purchase_order_products WHERE id = ? AND productno = ?";

                    foreach ($expirationdates as $key => $value) {
                        $productarr = $Product->GetProductByProductNo($value->productno);
                        $srp = $productarr['srp'];

                        $stmt = $this->conn->prepare($insert);
                        $stmt->bind_param("ssss", $srp, $asof, $value->id, $value->productno);
                        if (!$stmt->execute()) {
                            $stmt->close();
                            $passed = false;
                            return;
                            printf($stmt->error);
                        }
                        $stmt->close();

                        if ($Transaction->SaveIncomingTransaction($receivingno, $value->id, $value->productno) != 'SUCCESS') {
                            $passed = false;
                            return;
                            printf($stmt->error);
                        }
                    }

                    if ($passed) {
                        $GJstatus = $PredefinedProcess->GJEntrySupplier($purchaseno, $receivingno);
                        if ($GJstatus != 'SUCCESS') {
                            echo 'JV Entry Failed';
                            return;
                        }

                        $_SESSION['receivingno'] = $receivingno;
                        echo 'SUCCESS';
                        
                    } else {
                        echo 'SOMETHING DID NOT GO RIGHT.';
                    }
                }
            } else {
                'SOMETHING DID NOT GO RIGHT.';
            }
        }
    }

    public function SubmitProductExpiration($expirationdates, $receivingno, $initialstatus) {
        
        $passed = true;
        if ($initialstatus != 'PARTIAL') {
            $update = 'UPDATE tbl_purchase_order_products
            SET batch = ?,
                receivingno = ?,
                expirationdate = ?,
                lotno = ?,
                receivedquantity = ?,
                producttotalprice = 
                    CASE
                        WHEN unitprice = 0 THEN customunitprice * ?
                        ELSE unitprice * ?
                    END
            WHERE id = ? AND productno = ?;';
            foreach ($expirationdates as $key => $value) {
                $productno = $value->productno;
                $expirationdate = date('m/d/Y', strtotime($value->expirationdate));

                $sql = "SELECT (COUNT(productno) + 1) as batch FROM tbl_inventory_transactions WHERE productno = ? AND category = 'INCOMING';";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param('s', $productno);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();
                $batch = ($res->fetch_assoc())['batch'];
                
                $stmt = $this->conn->prepare($update);
                $stmt->bind_param("sssssssss", $batch, $receivingno, $expirationdate, $value->lotno, $value->receivedquantity, $value->receivedquantity, $value->receivedquantity, $value->id, $value->productno);
                if (!$stmt->execute()) {
                    $stmt->close();
                    $passed = false;
                    return;
                    printf($stmt->error);
                }
                $stmt->close();
            }
        } else {
            $insert = "INSERT INTO tbl_purchase_order_products (batch, purchaseno, receivingno, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, receivedquantity)
            SELECT ?, purchaseno, ?, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, 
                CASE
                    WHEN unitprice = 0 THEN customunitprice * ?
                    ELSE unitprice * ?
                END AS producttotalprice, 
                ?, ?, ? 
            FROM tbl_purchase_order_products 
            WHERE id = ? AND productno = ?;";
            
            foreach ($expirationdates as $key => $value) {
                $productno = $value->productno;
                $expirationdate = date('m/d/Y', strtotime($value->expirationdate));

                $sql = "SELECT (COUNT(productno) + 1) as batch FROM tbl_inventory_transactions WHERE productno = ? AND category = 'INCOMING';";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param('s', $productno);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();
                $batch = ($res->fetch_assoc())['batch'];

                $stmt = $this->conn->prepare($insert);
                $stmt->bind_param("sssssssss", $batch, $receivingno, $value->receivedquantity, $value->receivedquantity, $expirationdate, $value->lotno, $value->receivedquantity, $value->id, $productno);
                if (!$stmt->execute()) {
                    $stmt->close();
                    $passed = false;
                    return;
                    printf($stmt->error);
                }
                $stmt->close();
                
                $sql = "SELECT LAST_INSERT_ID() as newid;";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();
                $newid = ($res->fetch_assoc())['newid'];

                $value->id = $newid;
            }
        }

        $sql = "SELECT (SUM(producttotalprice)) as totalprice FROM tbl_purchase_order_products WHERE receivingno = ?;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $receivingno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $totalprice = ($res->fetch_assoc())['totalprice'];

        if ($passed) {
            return array(
                "message" => 'PRODUCT_SUCCESS',
                "expirationdates" => $expirationdates,
                "totalprice" => $totalprice
            );
        } else {
            return array(
                "message" => 'PRODUCT_ERROR',
                "expirationdates" => $expirationdates,
                "totalprice" => $totalprice
            );
        }
    }
}
?>