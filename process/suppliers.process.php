<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Supplier extends Database {
    
    public function LoadSupplierNumber() {
        echo 'SUP00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function GetSupplierNumberByBusinessName($businessname) {
        $gathered = is_array($businessname) ? $businessname['businessname'] : $businessname;
        $stmt = $this->conn->prepare('SELECT supplierno FROM tbl_suppliers WHERE businessname = ? LIMIT 1;');
        $stmt->bind_param('s', $gathered);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $supplierno = ($result->fetch_assoc())['supplierno'];
        if (is_array($businessname)) {
            echo $supplierno;
        } else {
           return $supplierno;
        }
    }

    public function LoadSuppliers() {
        
        $suppliers = [];
        $suppliersloaded = "SUPPLIERS_LOADED";

        $query = "SELECT id, supplierno, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, fullname, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, dateencoded, remarks FROM tbl_suppliers";
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

    public function LoadSupplier($data) {
        
        $supplier = [];
        $sanitize = new Sanitize();
        $supplierno = $sanitize->sanitizeForString($data["supplierno"]);
        $supplierloaded = "SUPPLIER_LOADED";

        $query = "SELECT id, supplierno, fullname, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, remarks FROM tbl_suppliers WHERE supplierno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $supplierno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $supplier[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $supplierloaded,
                "SUPPLIER" => $supplier
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function SubmitSupplier($data) {
        $sanitize = new Sanitize();
        $supplierno = $sanitize->sanitizeForString($data["supplierno"]);
        $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
        $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
        $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
        $businessprovince = strtoupper($sanitize->sanitizeForString($data["businessprovince"]));
        $businesstown = strtoupper($sanitize->sanitizeForString($data["businesstown"]));
        $businessbarangay = strtoupper($sanitize->sanitizeForString($data["businessbarangay"]));
        $businessstreet = strtoupper($sanitize->sanitizeForString($data["businessstreet"]));
        $firstname = strtoupper($sanitize->sanitizeForString($data["firstname"]));
        $middlename = strtoupper($sanitize->sanitizeForString($data["middlename"]));
        $lastname = strtoupper($sanitize->sanitizeForString($data["lastname"]));
        $extension = strtoupper($sanitize->sanitizeForString($data["extension"]));
        $gender = strtoupper($sanitize->sanitizeForString($data["gender"]));
        $email = $sanitize->sanitizeForString($data["email"]);
        $province = strtoupper($sanitize->sanitizeForString($data["province"]));
        $town = strtoupper($sanitize->sanitizeForString($data["town"]));
        $barangay = strtoupper($sanitize->sanitizeForString($data["barangay"]));
        $street = strtoupper($sanitize->sanitizeForString($data["street"]));
        $website = $sanitize->sanitizeForString($data["website"]);
        $tinno = $sanitize->sanitizeForString($data["tinno"]);
        $mobileno1 = $sanitize->sanitizeForString($data["mobileno1"]);
        $mobileno2 = $sanitize->sanitizeForString($data["mobileno2"]);
        $mobileno3 = $sanitize->sanitizeForString($data["mobileno3"]);
        
        $dateencoded = date('m/d/Y');

        $sql = "SELECT supplierno FROM tbl_suppliers WHERE supplierno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $supplierno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Supplier Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_suppliers (supplierno, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, fullname, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssssssssssssssssss", $supplierno, $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $fullname, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdateSupplier($data) {
        $sanitize = new Sanitize();
        $supplierno = $sanitize->sanitizeForString($data["supplierno"]);
        $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
        $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
        $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
        $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
        $businessprovince = strtoupper($sanitize->sanitizeForString($data["businessprovince"]));
        $businesstown = strtoupper($sanitize->sanitizeForString($data["businesstown"]));
        $businessbarangay = strtoupper($sanitize->sanitizeForString($data["businessbarangay"]));
        $businessstreet = strtoupper($sanitize->sanitizeForString($data["businessstreet"]));
        $firstname = strtoupper($sanitize->sanitizeForString($data["firstname"]));
        $middlename = strtoupper($sanitize->sanitizeForString($data["middlename"]));
        $lastname = strtoupper($sanitize->sanitizeForString($data["lastname"]));
        $extension = strtoupper($sanitize->sanitizeForString($data["extension"]));
        $gender = strtoupper($sanitize->sanitizeForString($data["gender"]));
        $email = $sanitize->sanitizeForString($data["email"]);
        $province = strtoupper($sanitize->sanitizeForString($data["province"]));
        $town = strtoupper($sanitize->sanitizeForString($data["town"]));
        $barangay = strtoupper($sanitize->sanitizeForString($data["barangay"]));
        $street = strtoupper($sanitize->sanitizeForString($data["street"]));
        $website = $sanitize->sanitizeForString($data["website"]);
        $tinno = $sanitize->sanitizeForString($data["tinno"]);
        $mobileno1 = $sanitize->sanitizeForString($data["mobileno1"]);
        $mobileno2 = $sanitize->sanitizeForString($data["mobileno2"]);
        $mobileno3 = $sanitize->sanitizeForString($data["mobileno3"]);
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        
        $sql = "UPDATE tbl_suppliers SET fullname = ?, businessname = ?, fdalno = ?, businesstinno = ?, businessprovince = ?, businesstown = ?, businessbarangay = ?, businessstreet = ?, firstname = ?, middlename = ?, lastname = ?, extension = ?, gender = ?, email = ?, province = ?, town = ?, barangay = ?, street = ?, website = ?, tinno = ?, mobileno1 = ?, mobileno2 = ?, mobileno3 = ?, remarks = ? WHERE supplierno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssssss", $fullname, $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $remarks, $supplierno);

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