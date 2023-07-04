<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Customer extends Database {
    
    public function LoadCustomerNumber() {
        echo 'CUS00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function LoadCustomers() {
        
        $customers = [];
        $customersloaded = "CUSTOMERS_LOADED";

        $query = "SELECT id, customerno, businessname, fdalno, businesstinno, fullname, firstname, middlename, lastname, extension, gender, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, dateencoded, remarks FROM tbl_customers";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $customersloaded,
                "CUSTOMERS" => $customers
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadCustomer($data) {
        
        $customer = [];
        $sanitize = new Sanitize();
        $customerno = $sanitize->sanitizeForString($data["customerno"]);
        $customerloaded = "CUSTOMER_LOADED";

        $query = "SELECT id, customerno, fullname, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, creditlimit, creditbalance, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, remarks FROM tbl_customers WHERE customerno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $customerno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $customer[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $customerloaded,
                "CUSTOMER" => $customer
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadCustomerBusinessNameViaCustomerNo($customerno) {
        
        $customer = [];

        $query = "SELECT customerno, businessname FROM tbl_customers WHERE customerno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $customerno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $customer[] = $row;
            }

            return array(
                "customerno" => $customer[0]['customerno'],
                "businessname" => $customer[0]['businessname']
            );

        } else {
            return array(
                "customerno" => $customerno,
                "businessname" => 'NONE'
            );
        }
    }

    public function SubmitCustomer($data) {
        $sanitize = new Sanitize();
        $customerno = $sanitize->sanitizeForString($data["customerno"]);
        $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
        $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
        $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
        $businessprovince = strtoupper($sanitize->sanitizeForString($data["businessprovince"]));
        $businesstown = strtoupper($sanitize->sanitizeForString($data["businesstown"]));
        $businessbarangay = strtoupper($sanitize->sanitizeForString($data["businessbarangay"]));
        $businessstreet = strtoupper($sanitize->sanitizeForString($data["businessstreet"]));
        $creditlimit = strtoupper($sanitize->sanitizeForString($data["creditlimit"]));
        $creditbalance = strtoupper($sanitize->sanitizeForString($data["creditbalance"]));
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
        $contactperson = strtoupper($sanitize->sanitizeForString($data["contactperson"]));
        $cpmobileno1 = $sanitize->sanitizeForString($data["cpmobileno1"]);
        $cpmobileno2 = $sanitize->sanitizeForString($data["cpmobileno2"]);
        $cpmobileno3 = $sanitize->sanitizeForString($data["cpmobileno3"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        
        $dateencoded = date('m/d/Y');

        $sql = "SELECT customerno FROM tbl_customers WHERE customerno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $customerno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Customer Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_customers (customerno, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, creditlimit, creditbalance, fullname, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssssssssssssssssssssssssss", $customerno, $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $creditlimit, $creditbalance, $fullname, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $contactperson, $cpmobileno1, $cpmobileno2, $cpmobileno3, $terms, $modeofpayment, $dateencoded);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdateCustomer($data) {
        $sanitize = new Sanitize();
        $customerno = $sanitize->sanitizeForString($data["customerno"]);
        $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
        $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
        $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
        $businessprovince = strtoupper($sanitize->sanitizeForString($data["businessprovince"]));
        $businesstown = strtoupper($sanitize->sanitizeForString($data["businesstown"]));
        $businessbarangay = strtoupper($sanitize->sanitizeForString($data["businessbarangay"]));
        $businessstreet = strtoupper($sanitize->sanitizeForString($data["businessstreet"]));
        $creditlimit = strtoupper($sanitize->sanitizeForString($data["creditlimit"]));
        $creditbalance = strtoupper($sanitize->sanitizeForString($data["creditbalance"]));
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
        $contactperson = strtoupper($sanitize->sanitizeForString($data["contactperson"]));
        $cpmobileno1 = $sanitize->sanitizeForString($data["cpmobileno1"]);
        $cpmobileno2 = $sanitize->sanitizeForString($data["cpmobileno2"]);
        $cpmobileno3 = $sanitize->sanitizeForString($data["cpmobileno3"]);
        $terms = $sanitize->sanitizeForString($data["terms"]);
        $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        
        $sql = "UPDATE tbl_customers SET fullname = ?, businessname = ?, fdalno = ?, businesstinno = ?, businessprovince = ?, businesstown = ?, businessbarangay = ?, businessstreet = ?, creditlimit = ?, creditbalance = ?, firstname = ?, middlename = ?, lastname = ?, extension = ?, gender = ?, email = ?, province = ?, town = ?, barangay = ?, street = ?, website = ?, tinno = ?, mobileno1 = ?, mobileno2 = ?, mobileno3 = ?, contactperson = ?, cpmobileno1 = ?, cpmobileno2 = ?, cpmobileno3 = ?, terms = ?, modeofpayment = ?, remarks = ? WHERE customerno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssssssssssssss", $fullname, $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $creditlimit, $creditbalance, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $contactperson, $cpmobileno1, $cpmobileno2, $cpmobileno3, $terms, $modeofpayment, $remarks, $customerno);

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