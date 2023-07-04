<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");
include_once("products.process.php");
include_once("transactions.process.php");

class Process extends Database {
    
    public function LoadPRNumber() {
        echo 'RUT00' . substr(str_shuffle('RUT00' . substr(str_shuffle('0123456789'), 0, 6)), 0, 6);
    }

    public function LoadPendingPurchaseReturns() {
        
        $pendings = [];
        $pendingsloaded = "PENDINGS_LOADED";

        $query = "SELECT returnno, prdate, totalprice, status FROM tbl_purchase_returns WHERE status = 'PENDING';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $pendings[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $pendingsloaded,
                "PENDINGS" => $pendings
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPurchaseReturns() {
        
        $returned = [];
        $returnedloaded = "RETURNED_LOADED";

        $query = "SELECT returnno, prdate, totalprice FROM tbl_purchase_returned WHERE status = 'RETURNED';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $returned[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $returnedloaded,
                "RETURNED" => $returned
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPendingPurchaseReturn($data) {
        
        $purchasereturn = [];
        $products = [];
        $sanitize = new Sanitize();
        $returnno = $sanitize->sanitizeForString($data["returnno"]);
        $returnloaded = "RETURN_LOADED";

        $query = "SELECT id, returnno, prdate, purpose, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, dateencoded FROM tbl_purchase_returns WHERE returnno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $returnno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchasereturn[] = $row;
            }

            try {
                $query = "SELECT id, batch, returnno, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno FROM tbl_purchase_returns_products WHERE returnno = ? ORDER BY id DESC;" ;
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $returnno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }

            } catch (\Throwable $th) {}

            echo json_encode(array(
                "MESSAGE" => $returnloaded,
                "RETURN" => $purchasereturn,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPurchaseReturn($data) {
        
        $purchasereturned = [];
        $products = [];
        $sanitize = new Sanitize();
        $returnno = $sanitize->sanitizeForString($data["returnno"]);
        $returnedloaded = "RETURNED_LOADED";

        $query = "SELECT id, returnno, prdate, purpose, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, remarks, status, dateencoded FROM tbl_purchase_returned WHERE returnno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $returnno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchasereturned[] = $row;
            }

            try {
                $query = "SELECT id, returnno, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno FROM tbl_purchase_returned_products WHERE returnno = ? ORDER BY id DESC;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $returnno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            } catch (\Throwable $th) {}

            echo json_encode(array(
                "MESSAGE" => $returnedloaded,
                "RETURNED" => $purchasereturned,
                "PRODUCTS" => $products
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function DeleteProducFromPurchaseReturn($data) {
        
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

    public function SubmitPendingPurchaseReturn($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();

        $returnno = $sanitize->sanitizeForString($data["returnno"]);
        $prdate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["prdate"])));
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

        $sql = "SELECT returnno FROM tbl_purchase_returns WHERE returnno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $returnno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Purchases Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_purchase_returns (returnno, prdate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssss", $returnno, $prdate, $purpose, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $totalprice, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                if ($this->SubmitPendingProductReturns($products, $returnno)) {
                    echo 'SUCCESS';
                } else {
                    'SOMETHING DID NOT GO RIGHT.';
                }
            }
        }
    }

    public function SubmitPendingProductReturns($products, $returnno) {
        
        $Product = new Product();

        $insert = 'INSERT INTO tbl_purchase_returns_products (batch, returnno, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno)';
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

            $values .= "('" . $value->batch . "', '" . $returnno . "', '" . $productno . "', '" . $barcode . "', '" . $generalname . "', '" . $value->brandname . "', '" . $value->uom . "', '" . $value->unitprice . "', '" . $value->customunitprice . "', '" . $value->quantity . "', '" . $value->producttotalprice . "', '" . $value->expirationdate . "', '" . $value->lotno . "')";
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

    public function SubmitUpdatePendingPurchaseReturn($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();

        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $podate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["podate"])));
        $supplier = strtoupper($sanitize->sanitizeForString($data["supplier"]));
        $supplierno = $Supplier->GetSupplierNumberByBusinessName($supplier);
        $address = strtoupper($sanitize->sanitizeForString($data["address"]));
        $supplieropenbalance = $sanitize->sanitizeForString($data["supplieropenbalance"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        $totalprice = $sanitize->sanitizeForString($data["totalprice"]);
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));

        $products = json_decode($data['products']);
        
        $sql = "UPDATE tbl_purchase_orders SET podate = ?, supplierno = ?, supplier = ?, address = ?, supplieropenbalance = ?, terms = ?, modeofpayment = ?, totalprice = ?, remarks = ? WHERE purchaseno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $podate, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $totalprice, $remarks, $purchaseno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            if ($this->SubmitUpdatePendingProductReturns($products) == 'PRODUCT_SUCCESS') {
                echo 'SUCCESS';
            } else {
                'SOMETHING DID NOT GO RIGHT.';
            }
        }
    }

    public function SubmitUpdatePendingProductReturns($products) {
        
        $Product = new Product();
        $update = 'UPDATE tbl_purchase_order_products SET batch = ?, productno = ?, barcode = ?, generalname = ?, brandname = ?, uom = ?, unitprice = ?, customunitprice = ?, quantity = ?, producttotalprice = ? WHERE id = ?';
        $passed = true;
        foreach ($products as $key => $value) {
            $productno = $value->productno;
            $productarr = $Product->GetProductByProductNo($productno);
            $generalname = $productarr['generalname'];
            $barcode = $productarr['barcode'];

            $stmt = $this->conn->prepare($update);
            $stmt->bind_param("sssssssssss", $value->batch, $productno, $barcode, $generalname, $value->brandname, $value->uom, $value->unitprice, $value->customunitprice, $value->quantity, $value->producttotalprice, $value->id);
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

    public function SubmitPurchaseReturn($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();
        $Transaction = new Transaction();

        $returnno = $sanitize->sanitizeForString($data["returnno"]);
        $prdate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["prdate"])));
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
        $passed = true;

        $sql = "SELECT returnno FROM tbl_purchase_returned WHERE returnno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $returnno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Return Number In Use.';
        } else {

            foreach ($products as $key => $value) {
                $productno = $value->productno;
                $quantity = $value->quantity;
                $batch = $value->batch;
    
                // IDENTIFY THE PRODUCT
    
                $sql = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE productno = ? AND batch = ? LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param('ss', $productno, $batch);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();
                
                // CHECK IF NONE
                if ($res->num_rows < 1) {
                    $passed = 'PRODUCT IS NOT AVAILABLE, PLEASE REMOVE THIS PENDING RETURN';
                    echo $passed;
                    return;   
                }

                $inventoryproducts = ($res->fetch_assoc());

                if ($quantity > $inventoryproducts['quantity']) {
                    $passed = 'QUANTITY IS NOT AVAILABLE, PLEASE REMOVE THIS PENDING RETURN';
                    echo $passed;
                    return;
                }
    
                // LESS THE QUANTITY
                
                $invbatchquantity = $inventoryproducts['quantity'];
                $newquantity = $invbatchquantity - $quantity;
    
                $sql = "UPDATE tbl_inventory_current SET quantity = ? WHERE productno = ? AND batch = ?;";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param('sss', $newquantity, $productno, $batch);
                $stmt->execute();
                $stmt->close();
    
                // REMOVE 0s
    
                $query = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
    
                if ($result->num_rows > 0) {
                    $sql = "INSERT INTO tbl_inventory_deleted SELECT NULL, batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof, '$purpose' FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $stmt->close();

                    $sql = "DELETE FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $stmt->close();
                }
    
                if ($Transaction->SaveOutgoingTransaction($data, $inventoryproducts['batch'], $inventoryproducts['barcode'], $inventoryproducts['productno'], $inventoryproducts['generalname'], $inventoryproducts['brandname'], $inventoryproducts['uom'], $inventoryproducts['unitprice'], $inventoryproducts['customunitprice'], $quantity, $inventoryproducts['producttotalprice'], $inventoryproducts['expirationdate'], $inventoryproducts['lotno'], $inventoryproducts['srp'], $inventoryproducts['asof']) != 'SUCCESS') {
                    $passed = false;
                    return;
                    printf($stmt->error);
                }
            }

            if ($passed) {
                $sql = "INSERT INTO tbl_purchase_returned (returnno, prdate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssssss", $returnno, $prdate, $purpose, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $totalprice, $dateencoded);

                if (!$stmt->execute()) {
                    printf($stmt->error);
                    $stmt->close();
                } else {
                    $stmt->close();
                    if ($this->SubmitProductReturns($products, $returnno)) {
                        echo 'SUCCESS';
                    } else {
                        echo 'SOMETHING DID NOT GO RIGHT.';
                    }
                }
            } else {
                echo 'SOMETHING DID NOT GO RIGHT IN SAVING TRANSACTIONS.';
            }
        }
    }

    public function DeletePurchaseReturn($data) {
        
        $sanitize = new Sanitize();
        $returnno = $sanitize->sanitizeForString($data["returnno"]);

        $query = "DELETE FROM tbl_purchase_returns WHERE returnno = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $returnno);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo 'SUCCESS';
        } else {
            $stmt->close();
            echo 'Nothing Changes!';
        }
    }

    // CHECK IF THE PRODUCT AND AMOUNT IS STILL AVAILABLE

    public function SubmitAPendingPurchaseReturn($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();
        $Transaction = new Transaction();

        $returnno = $sanitize->sanitizeForString($data["returnno"]);
        $prdate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["prdate"])));
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
        $passed = true;

        $sql = "SELECT returnno FROM tbl_purchase_returned WHERE returnno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $returnno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Return Number In Use.';
        } else {

            foreach ($products as $key => $value) {
                $productno = $value->productno;
                $quantity = $value->quantity;
                $batch = $value->batch;
    
                // IDENTIFY THE PRODUCT
    
                $sql = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE productno = ? AND batch = ? LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param('ss', $productno, $batch);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();

                // CHECK IF NONE
                if ($res->num_rows < 0) {
                    $passed = 'PRODUCT IS NOT AVAILABLE, PLEASE REMOVE THIS PENDING RETURN';
                    echo $passed;
                    return;   
                }

                $inventoryproducts = ($res->fetch_assoc());

                if ($quantity > $inventoryproducts['quantity']) {
                    $passed = 'QUANTITY IS NOT AVAILABLE, PLEASE REMOVE THIS PENDING RETURN';
                    echo $passed;
                    return;
                }
    
                // LESS THE QUANTITY
                
                $invbatchquantity = $inventoryproducts['quantity'];
                $newquantity = $invbatchquantity - $quantity;
    
                $sql = "UPDATE tbl_inventory_current SET quantity = ? WHERE productno = ? AND batch = ?;";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param('sss', $newquantity, $productno, $batch);
                $stmt->execute();
                $stmt->close();
    
                // REMOVE 0s
    
                $query = "SELECT batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
    
                if ($result->num_rows > 0) {
                    $sql = "INSERT INTO tbl_inventory_deleted SELECT NULL, batch, supplierno, supplier, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $stmt->close();

                    $sql = "DELETE FROM tbl_inventory_current WHERE quantity = 0 OR quantity < 0;";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $stmt->close();
                }
    
                if ($Transaction->SaveOutgoingTransaction($data, $inventoryproducts['batch'], $inventoryproducts['barcode'], $inventoryproducts['productno'], $inventoryproducts['generalname'], $inventoryproducts['brandname'], $inventoryproducts['uom'], $inventoryproducts['unitprice'], $inventoryproducts['customunitprice'], $quantity, $inventoryproducts['producttotalprice'], $inventoryproducts['expirationdate'], $inventoryproducts['lotno'], $inventoryproducts['srp'], $inventoryproducts['asof']) != 'SUCCESS') {
                    $passed = $stmt->error;
                    echo $passed;
                    return;
                    printf($stmt->error);
                }
            }

            if ($passed) {
                $sql = "INSERT INTO tbl_purchase_returned (returnno, prdate, purpose, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, totalprice, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssssssss", $returnno, $prdate, $purpose, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $totalprice, $dateencoded);

                if (!$stmt->execute()) {
                    printf($stmt->error);
                    $stmt->close();
                } else {
                    $stmt->close();
                    if ($this->SubmitProductReturns($products, $returnno)) {
                        echo 'SUCCESS';
                    } else {
                        echo 'SOMETHING DID NOT GO RIGHT.';
                    }
                }
            } else {
                echo $passed;
            }
        }
    }

    public function SubmitProductReturns($products, $returnno) {
        
        $Product = new Product();

        $insert = 'INSERT INTO tbl_purchase_returned_products (batch, returnno, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity, producttotalprice, expirationdate, lotno)';
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

            $values .= "('" . $value->batch . "', '" . $returnno . "', '" . $productno . "', '" . $barcode . "', '" . $generalname . "', '" . $value->brandname . "', '" . $value->uom . "', '" . $value->unitprice . "', '" . $value->customunitprice . "', '" . $value->quantity . "', '" . $value->producttotalprice . "', '" . $value->expirationdate . "', '" . $value->lotno . "')";
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
}
?>