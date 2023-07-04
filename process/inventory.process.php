<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");


class Product extends Database {

    public function LoadProducts($data) {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT id, productno, barcode, sku, shelf, generalname, brandname, category, description, srp, threshold, status, dateencoded, remarks FROM tbl_product_profiles";
        $query .= ($data['category'] == 'all') ? ";" : " WHERE category = '" . $data['category'] . "';";
        $stmt = $this->conn->prepare($query);
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

    public function LoadCurrentProducts($data) {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT * FROM tbl_view_inventory_current t";
        $query .= ($data['category'] == 'all') ? ";" : " WHERE category = '" . $data['category'] . "';";
        $stmt = $this->conn->prepare($query);
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

    public function LoadAllCurrentInventoryProducts() {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT * FROM tbl_view_inventory_current t";
        $stmt = $this->conn->prepare($query);
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

    public function LoadAllCurrentInventorySuppliers() {
        
        $suppliers = [];
        $suppliersloaded = "SUPPLIERS_LOADED";

        $query = "SELECT DISTINCT(supplierno) AS supplierno, supplier FROM tbl_inventory_current t;";
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

    public function LoadAllCurrentInventoryProductsSL() {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT * FROM tbl_product_profiles t";
        $stmt = $this->conn->prepare($query);
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

    public function LoadCurrentProduct($data) {
        
        $product = [];
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $productloaded = "PRODUCT_LOADED";

        $query = "SELECT i.productno, i.barcode, p.sku, p.category, i.generalname, i.brandname, p.shelf, p.description, SUM(i.quantity) AS totalquantity, i.srp, p.threshold
        FROM tbl_inventory_current i
        LEFT JOIN tbl_product_profiles p ON p.productno = i.productno
        WHERE i.productno = ? GROUP BY i.productno;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $productno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $product[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $productloaded,
                "PRODUCT" => $product
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadBatches($data) {
        
        $batches = [];
        $batchesloaded = "BATCHES_LOADED";
        $productno = $data["productno"];

        $query = "SELECT batch, productno FROM tbl_inventory_current WHERE productno = '$productno' ORDER BY batch ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $batches[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $batchesloaded,
                "BATCHES" => $batches
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadProductsFilteredBySupplier($data) {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT DISTINCT(i.productno) AS productno, i.barcode, i.generalname, i.brandname FROM tbl_inventory_current i INNER JOIN tbl_product_costings c ON i.productno = c.productno WHERE i.supplier = '" . $data['supplier'] . "';";
        $stmt = $this->conn->prepare($query);
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

    public function LoadProductByBatch($data) {
        
        $product = [];
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $batch = $sanitize->sanitizeForString($data["batch"]);
        $productloaded = "PRODUCT_LOADED";

        $query = "SELECT batch, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, quantity AS quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE productno = ? AND batch = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $productno, $batch);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $product[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $productloaded,
                "PRODUCT" => $product,
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadProduct($data) {
        
        $product = [];
        $costs = [];
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $productloaded = "PRODUCT_LOADED";

        $query = "SELECT batch, productno, barcode, generalname, brandname, uom, unitprice, customunitprice, SUM(quantity) AS quantity, producttotalprice, expirationdate, lotno, srp, asof FROM tbl_inventory_current WHERE productno = ? GROUP BY productno;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $productno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $product[] = $row;
            }   

            try {
                $query = "SELECT id, costingno, supplierno, supplier, supplieruom, suppliersup, isdefault FROM tbl_product_costings WHERE productno = ?;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $productno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $costs[] = $row;
                }
            } catch (\Throwable $th) {}

            echo json_encode(array(
                "MESSAGE" => $productloaded,
                "PRODUCT" => $product,
                "COSTS" => $costs
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadProductLessPending($data) {
        
        $product = [];
        $srphistory = [];
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $productloaded = "PRODUCT_LOADED";

        $query = "SELECT i.batch, i.productno, i.barcode, i.generalname, i.brandname, i.uom, i.unitprice, i.customunitprice, SUM(i.quantity) AS quantity,
        COALESCE(SUM(i.quantity), 0) - COALESCE(p.quantity, 0) AS available,
        i.producttotalprice, i.expirationdate, i.lotno, i.srp, i.asof
        FROM tbl_inventory_current i
        LEFT JOIN tbl_pos_products_transaction p ON p.barcode = i.barcode
        WHERE i.productno = ?
        GROUP BY i.productno;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $productno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $product[] = $row;
            }

            if ($product[0]['available'] == 0) {
                echo 'No Stock Available!';
                return;
            }

            $query = "SELECT DISTINCT(srp) AS srphistory FROM tbl_pos_receipts WHERE barcode = ? ORDER BY drno DESC;";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $product[0]['barcode']);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();

            try {
                while($row = $res->fetch_assoc()) {
                    $srphistory[] = $row;
                }
            } catch (\Throwable $th) { }

            echo json_encode(array(
                "MESSAGE" => $productloaded,
                "PRODUCT" => $product,
                "SRPHISTORY" => $srphistory
            ));

        } else {
            echo 'No Product Data!';
        }
    }

    public function SubmitProduct($data) {
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $barcode = $sanitize->sanitizeForString($data["barcode"]);
        $sku = $sanitize->sanitizeForString($data["sku"]);
        $category = strtoupper($sanitize->sanitizeForString($data["category"]));
        $generalname = strtoupper($sanitize->sanitizeForString($data["generalname"]));
        $brandname = strtoupper($sanitize->sanitizeForString($data["brandname"]));
        $shelf = $sanitize->sanitizeForString($data["shelf"]);
        $description = $sanitize->sanitizeForString($data["description"]);
        $srp = $sanitize->sanitizeForString($data["srp"]);
        $threshold = $sanitize->sanitizeForString($data["threshold"]);
        $costings = json_decode($data['costings']);
        
        $dateencoded = date('m/d/Y');
        $status = 'ACTIVE';

        $sql = "SELECT productno FROM tbl_product_profiles WHERE productno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $productno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Product Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_product_profiles (productno, barcode, sku, shelf, generalname, brandname, category, description, srp, threshold, status, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssssss", $productno, $barcode, $sku, $shelf, $generalname, $brandname, $category, $description, $srp, $threshold, $status, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();

                if ($this->SubmitProductCosting($costings, $productno, $dateencoded) == 'COST_SUCCESS') {
                    echo 'SUCCESS';
                } else {
                    'SOMETHING DID NOT GO RIGHT.';
                }
            }
        }
    }

    public function SubmitProductCosting($costing, $productno, $dateencoded) {
        
        $supplier = new Supplier();

        $insert = 'INSERT INTO tbl_product_costings (costingno, productno, supplierno, supplier, supplieruom, suppliersup, isdefault, dateencoded)';
        $costingno = $this->LoadCostingNumber();
        $values = '';
        $first = true;
        foreach ($costing as $key => $value) {
            $supplierno = $supplier->GetSupplierNumberByBusinessName($value->supplier);
            $isdefault = ($value->isdefault == 'YES') ? 'YES' : 'NO';

            if (!$first) {
                $values .= ',';
            }

            $values .= "('" . $costingno . "', '" . $productno . "', '" . $supplierno . "', '" . $value->supplier . "', '" . $value->supplieruom . "', '" . $value->suppliersup . "', '" . $isdefault . "', '" .  $dateencoded . "')";
            $first = false;
        }
        $query = $insert . ' VALUES ' . $values;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        if ($result > 0) {
            return "COST_SUCCESS";
        } else {
            return "COST_ERROR";
        }
    }

    public function SubmitUpdateProductCosting($costing) {
        
        $supplier = new Supplier();
        $update = 'UPDATE tbl_product_costings SET supplierno = ?, supplier = ?, supplieruom = ?, suppliersup = ?, isdefault = ? WHERE id = ?';
        $passed = true;
        foreach ($costing as $key => $value) {
            $supplierno = $supplier->GetSupplierNumberByBusinessName($value->supplier);
            $isdefault = ($value->isdefault == 'YES') ? 'YES' : 'NO';
            $stmt = $this->conn->prepare($update);
            $stmt->bind_param("ssssss", $supplierno, $value->supplier, $value->supplieruom, $value->suppliersup, $isdefault, $value->id);
            if (!$stmt->execute()) {
                $stmt->close();
                $passed = false;
                printf($stmt->error);
            }
            $stmt->close();
        }
        

        if ($passed) {
            return "COST_SUCCESS";
        } else {
            return "COST_ERROR";
        }
    }

    public function SubmitUpdateProduct($data) {
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $barcode = $sanitize->sanitizeForString($data["barcode"]);
        $sku = $sanitize->sanitizeForString($data["sku"]);
        $category = strtoupper($sanitize->sanitizeForString($data["category"]));
        $generalname = strtoupper($sanitize->sanitizeForString($data["generalname"]));
        $brandname = strtoupper($sanitize->sanitizeForString($data["brandname"]));
        $shelf = $sanitize->sanitizeForString($data["shelf"]);
        $description = $sanitize->sanitizeForString($data["description"]);
        $srp = $sanitize->sanitizeForString($data["srp"]);
        $threshold = $sanitize->sanitizeForString($data["threshold"]);
        $costings = json_decode($data['costings']);
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        
        $sql = "UPDATE tbl_product_profiles SET barcode = ?, sku = ?, shelf = ?, description = ?, srp = ?, threshold = ?, generalname = ?, brandname = ?, category = ?, remarks = ? WHERE productno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $barcode, $sku, $shelf, $description, $srp, $threshold, $generalname, $brandname, $category, $remarks, $productno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();

            if ($this->SubmitUpdateProductCosting($costings) == 'COST_SUCCESS') {
                echo 'SUCCESS';
            } else {
                'SOMETHING DID NOT GO RIGHT.';
            }
        }
    }

    public function SearchProducts($data) {
        
        $products = [];
        $sanitize = new Sanitize();
        $search = $sanitize->sanitizeForString($data["search"]);
        $productloaded = "PRODUCTS_LOADED";

        $query = "SELECT productno, barcode, generalname, brandname FROM tbl_inventory_current WHERE MATCH( barcode, generalname, lotno ) AGAINST( ? ) GROUP BY barcode;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $productloaded,
                "PRODUCTS" => $products,
            ));

        } else {
            echo 'NO_DATA';
        }
    }
}
?>