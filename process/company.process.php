<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database {

    public function LoadCompany() {
        
        $company = [];
        $companyloaded = "COMPANY_LOADED";

        $query = "SELECT id, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, lastupdate FROM tbl_company LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $company[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $companyloaded,
                "COMPANY" => $company
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function SubmitUpdateCompany($data) {
        $sanitize = new Sanitize();
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

        $lastupdate = date('m/d/Y');
        
        $sql = "UPDATE tbl_company SET businessname = ?, fdalno = ?, businesstinno = ?, businessprovince = ?, businesstown = ?, businessbarangay = ?, businessstreet = ?, firstname = ?, middlename = ?, lastname = ?, extension = ?, gender = ?, email = ?, province = ?, town = ?, barangay = ?, street = ?, website = ?, tinno = ?, mobileno1 = ?, mobileno2 = ?, mobileno3 = ?, lastupdate = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssss", $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $lastupdate);

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