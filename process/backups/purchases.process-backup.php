<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");
include_once("products.process.php");

class Process extends Database {
    
    public function LoadPONumber() {
        echo 'PO00' . substr(str_shuffle('PUR00' . substr(str_shuffle('0123456789'), 0, 6)), 0, 6);
    }

    public function LoadPurchaseOrders() {
        
        $purchasess = [];
        $purchasessloaded = "PURCHASES_LOADED";

        $query = "SELECT id, purchaseno, podate, supplierno, supplier, supplieropenbalance, terms, modeofpayment, productno, generalname, brandname, uom, unitprice, quantity, totalprice, dateencoded FROM tbl_purchase_orders WHERE status = 'READY';";
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
        $sanitize = new Sanitize();
        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $purchaseloaded = "ORDER_LOADED";

        $query = "SELECT id, purchaseno, podate, supplier, address, supplieropenbalance, terms, modeofpayment, productno, brandname, uom, unitprice, quantity, totalprice, remarks, dateencoded FROM tbl_purchase_orders WHERE purchaseno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $purchaseno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purchaseorder[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $purchaseloaded,
                "ORDER" => $purchaseorder
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function SubmitPurchaseOrder($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();
        $Product = new Product();

        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $podate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["podate"])));
        $supplier = strtoupper($sanitize->sanitizeForString($data["supplier"]));
        $supplierno = $Supplier->GetSupplierNumberByBusinessName($supplier);
        $address = strtoupper($sanitize->sanitizeForString($data["address"]));
        $supplieropenbalance = $sanitize->sanitizeForString($data["supplieropenbalance"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        $productno = strtoupper($sanitize->sanitizeForString($data["productno"]));
        $generalname = $Product->GetProductByProductNo($productno);
        $brandname = strtoupper($sanitize->sanitizeForString($data["brandname"]));
        $uom = strtoupper($sanitize->sanitizeForString($data["uom"]));
        $unitprice = $sanitize->sanitizeForString($data["unitprice"]);
        $quantity = $sanitize->sanitizeForString($data["quantity"]);
        $totalprice = $sanitize->sanitizeForString($data["totalprice"]);
        
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
            $sql = "INSERT INTO tbl_purchase_orders (purchaseno, podate, supplierno, supplier, address, supplieropenbalance, terms, modeofpayment, productno, generalname, brandname, uom, unitprice, quantity, totalprice, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssssssssssss", $purchaseno, $podate, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $productno, $generalname, $brandname, $uom, $unitprice, $quantity, $totalprice, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdatePurchaseOrder($data) {
        $sanitize = new Sanitize();
        $Supplier = new Supplier();
        $Product = new Product();

        $purchaseno = $sanitize->sanitizeForString($data["purchaseno"]);
        $podate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["podate"])));
        $supplier = strtoupper($sanitize->sanitizeForString($data["supplier"]));
        $supplierno = $Supplier->GetSupplierNumberByBusinessName($supplier);
        $address = strtoupper($sanitize->sanitizeForString($data["address"]));
        $supplieropenbalance = $sanitize->sanitizeForString($data["supplieropenbalance"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        $productno = strtoupper($sanitize->sanitizeForString($data["productno"]));
        $generalname = $Product->GetProductByProductNo($productno);
        $brandname = strtoupper($sanitize->sanitizeForString($data["brandname"]));
        $uom = strtoupper($sanitize->sanitizeForString($data["uom"]));
        $unitprice = $sanitize->sanitizeForString($data["unitprice"]);
        $quantity = $sanitize->sanitizeForString($data["quantity"]);
        $totalprice = $sanitize->sanitizeForString($data["totalprice"]);
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        
        $sql = "UPDATE tbl_purchase_orders SET podate = ?, supplierno = ?, supplier = ?, address = ?, supplieropenbalance = ?, terms = ?, modeofpayment = ?, productno = ?, generalname = ?, brandname = ?, uom = ?, unitprice = ?, quantity = ?, totalprice = ?, remarks = ? WHERE purchaseno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssss", $podate, $supplierno, $supplier, $address, $supplieropenbalance, $terms, $modeofpayment, $productno, $generalname, $brandname, $uom, $unitprice, $quantity, $totalprice, $remarks, $purchaseno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }
}
?>