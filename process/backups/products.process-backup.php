<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("suppliers.process.php");


class Process extends Database {

    public function LoadProductNumber() {
        echo 'PROD00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function LoadCostingNumber() {
        return 'COST00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function LoadPriceNumber() {
        return 'PRI00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function LoadProducts($data) {
        
        $products = [];
        $productsloaded = "PRODUCTS_LOADED";

        $query = "SELECT id, productno, barcode, sku, shelf, generalname, brandname, lotno, expirationdate, category, description, status, dateencoded, remarks FROM tbl_products";
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

    public function LoadProduct($data) {
        
        $product = [];
        $costs = [];
        $prices = [];
        $sanitize = new Sanitize();
        $productno = $sanitize->sanitizeForString($data["productno"]);
        $productloaded = "PRODUCT_LOADED";

        $query = "SELECT productno, barcode, sku, shelf, generalname, brandname, lotno, expirationdate, category, description, status, dateencoded, remarks FROM tbl_products WHERE productno = ? LIMIT 1;";
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
                $query = "SELECT id, costingno, supplierno, supplier, supplieruom, suppliersup, supplierstandardprice, supplierstp, supplierdiscountedprice FROM tbl_product_costings WHERE productno = ?;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $productno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $costs[] = $row;
                }
            } catch (\Throwable $th) {}

            try {
                $query = "SELECT id, priceno, pricelistsuom, cost, quantity, markup, productstandardprice, productdiscountedprice FROM tbl_product_pricelists WHERE productno = ?;";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('s', $productno);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

            
                while($row = $result->fetch_assoc()) {
                    $prices[] = $row;
                }
            } catch (\Throwable $th) {}
            

            echo json_encode(array(
                "MESSAGE" => $productloaded,
                "PRODUCT" => $product,
                "COSTS" => $costs,
                "PRICES" => $prices
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
        $costings = json_decode($data['costings']);
        $prices = json_decode($data['prices']);
        
        $dateencoded = date('m/d/Y');
        $status = 'ACTIVE';
        $lotno = $productno . $barcode . $sku;

        $sql = "SELECT productno FROM tbl_products WHERE productno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $productno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Product Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_products (productno, barcode, sku, shelf, generalname, brandname, lotno, category, description, status, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssss", $productno, $barcode, $sku, $shelf, $generalname, $brandname, $lotno, $category, $description, $status, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();

                if ($this->SubmitProductCosting($costings, $productno, $dateencoded) == 'COST_SUCCESS' && $this->SubmitProductPricing($prices, $productno, $dateencoded) == 'PRICE_SUCCESS') {
                    echo 'SUCCESS';
                } else {
                    'SOMETHING DID NOT GO RIGHT.';
                }
            }
        }
    }

    public function SubmitProductCosting($costing, $productno, $dateencoded) {
        
        $supplier = new Supplier();

        $insert = 'INSERT INTO tbl_product_costings (costingno, productno, supplierno, supplier, supplieruom, suppliersup, supplierstandardprice, supplierstp, supplierdiscountedprice, dateencoded)';
        $costingno = $this->LoadCostingNumber();
        $values = '';
        $first = true;
        foreach ($costing as $key => $value) {
            $supplierno = $supplier->GetSupplierNumberByBusinessName($value->supplier);

            if (!$first) {
                $values .= ',';
            }

            $values .= "('" . $costingno . "', '" . $productno . "', '" . $supplierno . "', '" . $value->supplier . "', '" . $value->supplieruom . "', '" . $value->suppliersup . "', '" . $value->supplierstandardprice . "', '" . $value->supplierstp . "', '" . $value->supplierdiscountedprice . "', '" . $dateencoded . "')";
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

    public function SubmitProductPricing($pricing, $productno, $dateencoded) {
        $insert = 'INSERT INTO tbl_product_pricelists (priceno, productno, pricelistsuom, cost, quantity, markup, productstandardprice, productdiscountedprice, dateencoded)';
        $priceno = $this->LoadPriceNumber();
        $values = '';
        $first = true;
        foreach ($pricing as $key => $value) {

            if (!$first) {
                $values .= ',';
            }

            $values .= "('" . $priceno . "', '" . $productno . "', '" . $value->pricelistsuom . "', '" . $value->cost . "', '" . $value->quantity . "', '" . $value->markup . "', '" . $value->productstandardprice . "', '" . $value->productdiscountedprice . "', '" . $dateencoded . "')";
            $first = false;
        }
        $query = $insert . ' VALUES ' . $values;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        if ($result > 0) {
            return "PRICE_SUCCESS";
        } else {
            return "PRICE_ERROR";
        }
    }

    public function SubmitUpdateProductCosting($costing) {
        
        $supplier = new Supplier();
        $update = 'UPDATE tbl_product_costings SET supplierno = ?, supplier = ?, supplieruom = ?, suppliersup = ?, supplierstandardprice = ?, supplierstp = ?, supplierdiscountedprice = ? WHERE id = ?';
        $passed = true;
        foreach ($costing as $key => $value) {
            $supplierno = $supplier->GetSupplierNumberByBusinessName($value->supplier);
            $stmt = $this->conn->prepare($update);
            $stmt->bind_param("ssssssss", $supplierno, $value->supplier, $value->supplieruom, $value->suppliersup, $value->supplierstandardprice, $value->supplierstp, $value->supplierdiscountedprice, $value->id);
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

    public function SubmitUpdateProductPricing($pricing) {
        $update = 'UPDATE tbl_product_pricelists SET pricelistsuom = ?, cost = ?, quantity = ?, markup = ?, productstandardprice = ?, productdiscountedprice = ? WHERE id = ? ';
        $passed = true;
        foreach ($pricing as $key => $value) {
            $stmt = $this->conn->prepare($update);
            $stmt->bind_param("sssssss", $value->pricelistsuom, $value->cost, $value->quantity, $value->markup, $value->productstandardprice, $value->productdiscountedprice, $value->id);
            if (!$stmt->execute()) {
                $stmt->close();
                $passed = false;
                printf($stmt->error);
            }
            $stmt->close();
        }
        

        if ($passed) {
            return "PRICE_SUCCESS";
        } else {
            return "PRICE_ERROR";
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
        $costings = json_decode($data['costings']);
        $prices = json_decode($data['prices']);
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        
        $sql = "UPDATE tbl_products SET barcode = ?, sku = ?, shelf = ?, description = ?, generalname = ?, brandname = ?, category = ?, remarks = ? WHERE productno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssss", $barcode, $sku, $shelf, $description, $generalname, $brandname, $category, $remarks, $productno);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();

            if ($this->SubmitUpdateProductCosting($costings) == 'COST_SUCCESS' && $this->SubmitUpdateProductPricing($prices) == 'PRICE_SUCCESS') {
                echo 'SUCCESS';
            } else {
                'SOMETHING DID NOT GO RIGHT.';
            }
        }
    }
}
?>