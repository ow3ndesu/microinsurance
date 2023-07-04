<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");


class Product extends Database {

    public function LoadProductNumber() {
        echo 'PROD00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function LoadConfigCounts() {
        
        $profiles = 0;
        $current = 0;
        $countsloaded = "COUNTS_LOADED";

        $query1 = "SELECT COUNT(DISTINCT(productno)) AS profiles FROM tbl_product_profiles WHERE status = 'ACTIVE';";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->execute();
        $row1 = ($stmt1->get_result()->fetch_assoc());
        $stmt1->close();

        $query2 = "SELECT COUNT(DISTINCT(productno)) AS current FROM tbl_inventory_current WHERE quantity <> 0 OR quantity < 0;";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $row2 = ($stmt2->get_result()->fetch_assoc());
        $stmt2->close();

        try {
            $profiles = $row1['profiles'];
        } catch (\Throwable $th) { }

        try {
            $current = $row2['current'];
        } catch (\Throwable $th) { }

        echo json_encode(array(
            "MESSAGE" => $countsloaded,
            "PROFILES" => $profiles,
            "CURRENT" => $current,
        ));
    }

    public function GetProductByProductNo($productno) {
        $gathered = is_array($productno) ? $productno['productno'] : $productno;
        $stmt = $this->conn->prepare('SELECT generalname, barcode, srp FROM tbl_product_profiles WHERE productno = ? LIMIT 1;');
        $stmt->bind_param('s', $gathered);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $row = ($result->fetch_assoc());
        $generalname = $row['generalname'];
        $barcode = $row['barcode'];
        $srp = $row['srp'];
        if (is_array($productno)) {
            echo $generalname;
        } else {
           return array(
            "generalname" => $generalname,
            "barcode" => $barcode,
            "srp" => $srp,
           );
        }
    }

    public function LoadCostingNumber() {
        return 'COST00' . substr(str_shuffle('0123456789'), 0, 6);
    }

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

    public function LoadProductsFilteredBySupplier($data) {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT DISTINCT(p.id), p.productno, p.barcode, p.sku, p.shelf, p.generalname, p.brandname, p.category, p.description, p.srp, p.threshold, p.status, p.dateencoded, p.remarks FROM tbl_product_profiles p INNER JOIN tbl_product_costings c ON p.productno = c.productno WHERE c.supplier = '" . $data['supplier'] . "';";
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

    public function LoadProduct($data) {
        
        $product = [];
        $costs = [];
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $productloaded = "PRODUCT_LOADED";

        $query = "SELECT productno, barcode, sku, shelf, generalname, brandname, category, description, srp, threshold, status, dateencoded, remarks FROM tbl_product_profiles WHERE productno = ? LIMIT 1;";
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
            $sql = "INSERT INTO tbl_product_profiles (productno, barcode, sku, shelf, generalname, brandname, category, description, srp, threshold, status, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssssssss", $productno, $barcode, $sku, $shelf, $generalname, $brandname, $category, $description, $srp, $threshold, $status, $dateencoded);

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
        $costingLegth = count($costing);
        foreach ($costing as $key => $value) {
            $supplierno = $supplier->GetSupplierNumberByBusinessName($value->supplier);
            $isdefault = ($value->isdefault == 'YES' || $costingLegth == 1) ? 'YES' : 'NO';

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
}
?>