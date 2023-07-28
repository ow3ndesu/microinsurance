<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Content extends Database {
    
    public function LoadContentNumber() {
        echo 'CUS00' . substr(str_shuffle('0123456789'), 0, 6);
    }

    public function LoadContents() {
        
        $contents = [];
        $contentsloaded = "CONTENTS_LOADED";

        $query = "SELECT id, contentno, businessname, fdalno, businesstinno, fullname, firstname, middlename, lastname, extension, gender, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, dateencoded, remarks FROM tbl_contents";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $contents[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $contentsloaded,
                "CONTENTS" => $contents
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadContent($data) {
        
        $content = [];
        $sanitize = new Sanitize();
        $contentno = $sanitize->sanitizeForString($data["contentno"]);
        $contentloaded = "CONTENT_LOADED";

        $query = "SELECT id, contentno, fullname, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, creditlimit, creditbalance, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, remarks FROM tbl_contents WHERE contentno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $contentno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $content[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $contentloaded,
                "CONTENT" => $content
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadNavigationBar() {
        
        $content = [];
        $contentsloaded = "CONTENTS_LOADED";

        $query = "SELECT type, value FROM tbl_contents WHERE division = 'NAVIGATION';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows <= 0) {
            echo 'NO_DATA';
            return;
        }

        while($row = $result->fetch_assoc()) {
            $content[] = $row;
        }    

        echo json_encode(array(
            "MESSAGE" => $contentsloaded,
            $content[0]['type'] => $content[0]['value'],
            $content[1]['type'] => ($content[1]['value'] == '1') ? true : false,
            $content[2]['type'] => ($content[2]['value'] == '1') ? true : false
        ));
    }

    public function ChangeBooleanValue($data) {
        $type = $data["type"];
        $sql = '';

        switch ($type) {
            case 'isProductsInNavigationBar':
                $sql .= "UPDATE tbl_contents SET value = NOT value WHERE type = 'ISPRODUCTSINNAVTIGATION';";
                break;

            case 'isProductsInFeatured':
                $sql .= "UPDATE tbl_contents SET value = NOT value WHERE type = 'ISPRODUCTSINFEATURED';";
                break;

            case 'isEmailCardShowing':
                $sql .= "UPDATE tbl_contents SET value = NOT value WHERE type = 'ISEMAILCARDSHOWING';";
                break;
            
            default:
                # code...
                break;
        }
        
        $stmt = $this->conn->prepare($sql);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    // public function SubmitContent($data) {
    //     $sanitize = new Sanitize();
    //     $contentno = $sanitize->sanitizeForString($data["contentno"]);
    //     $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
    //     $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
    //     $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
    //     $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
    //     $businessprovince = strtoupper($sanitize->sanitizeForString($data["businessprovince"]));
    //     $businesstown = strtoupper($sanitize->sanitizeForString($data["businesstown"]));
    //     $businessbarangay = strtoupper($sanitize->sanitizeForString($data["businessbarangay"]));
    //     $businessstreet = strtoupper($sanitize->sanitizeForString($data["businessstreet"]));
    //     $creditlimit = strtoupper($sanitize->sanitizeForString($data["creditlimit"]));
    //     $creditbalance = strtoupper($sanitize->sanitizeForString($data["creditbalance"]));
    //     $firstname = strtoupper($sanitize->sanitizeForString($data["firstname"]));
    //     $middlename = strtoupper($sanitize->sanitizeForString($data["middlename"]));
    //     $lastname = strtoupper($sanitize->sanitizeForString($data["lastname"]));
    //     $extension = strtoupper($sanitize->sanitizeForString($data["extension"]));
    //     $gender = strtoupper($sanitize->sanitizeForString($data["gender"]));
    //     $email = $sanitize->sanitizeForString($data["email"]);
    //     $province = strtoupper($sanitize->sanitizeForString($data["province"]));
    //     $town = strtoupper($sanitize->sanitizeForString($data["town"]));
    //     $barangay = strtoupper($sanitize->sanitizeForString($data["barangay"]));
    //     $street = strtoupper($sanitize->sanitizeForString($data["street"]));
    //     $website = $sanitize->sanitizeForString($data["website"]);
    //     $tinno = $sanitize->sanitizeForString($data["tinno"]);
    //     $mobileno1 = $sanitize->sanitizeForString($data["mobileno1"]);
    //     $mobileno2 = $sanitize->sanitizeForString($data["mobileno2"]);
    //     $mobileno3 = $sanitize->sanitizeForString($data["mobileno3"]);
    //     $contactperson = strtoupper($sanitize->sanitizeForString($data["contactperson"]));
    //     $cpmobileno1 = $sanitize->sanitizeForString($data["cpmobileno1"]);
    //     $cpmobileno2 = $sanitize->sanitizeForString($data["cpmobileno2"]);
    //     $cpmobileno3 = $sanitize->sanitizeForString($data["cpmobileno3"]);
    //     $terms = $sanitize->sanitizeForString($data["terms"]);
    //     $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
        
    //     $dateencoded = date('m/d/Y');

    //     $sql = "SELECT contentno FROM tbl_contents WHERE contentno = ? LIMIT 1;";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param('s', $contentno);
    //     $stmt->execute();
    //     $res = $stmt->get_result();
    //     $stmt->close();

    //     if ($res->num_rows == 1) {
    //         echo 'Content Number In Use.';
    //     } else {
    //         $sql = "INSERT INTO tbl_contents (contentno, businessname, fdalno, businesstinno, businessprovince, businesstown, businessbarangay, businessstreet, creditlimit, creditbalance, fullname, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->bind_param("sssssssssssssssssssssssssssssssss", $contentno, $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $creditlimit, $creditbalance, $fullname, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $contactperson, $cpmobileno1, $cpmobileno2, $cpmobileno3, $terms, $modeofpayment, $dateencoded);

    //         if (!$stmt->execute()) {
    //             printf($stmt->error);
    //             $stmt->close();
    //         } else {
    //             $stmt->close();
    //             echo 'SUCCESS';
    //         }
    //     }
    // }

    // public function SubmitUpdateContent($data) {
    //     $sanitize = new Sanitize();
    //     $contentno = $sanitize->sanitizeForString($data["contentno"]);
    //     $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
    //     $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
    //     $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
    //     $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
    //     $businessprovince = strtoupper($sanitize->sanitizeForString($data["businessprovince"]));
    //     $businesstown = strtoupper($sanitize->sanitizeForString($data["businesstown"]));
    //     $businessbarangay = strtoupper($sanitize->sanitizeForString($data["businessbarangay"]));
    //     $businessstreet = strtoupper($sanitize->sanitizeForString($data["businessstreet"]));
    //     $creditlimit = strtoupper($sanitize->sanitizeForString($data["creditlimit"]));
    //     $creditbalance = strtoupper($sanitize->sanitizeForString($data["creditbalance"]));
    //     $firstname = strtoupper($sanitize->sanitizeForString($data["firstname"]));
    //     $middlename = strtoupper($sanitize->sanitizeForString($data["middlename"]));
    //     $lastname = strtoupper($sanitize->sanitizeForString($data["lastname"]));
    //     $extension = strtoupper($sanitize->sanitizeForString($data["extension"]));
    //     $gender = strtoupper($sanitize->sanitizeForString($data["gender"]));
    //     $email = $sanitize->sanitizeForString($data["email"]);
    //     $province = strtoupper($sanitize->sanitizeForString($data["province"]));
    //     $town = strtoupper($sanitize->sanitizeForString($data["town"]));
    //     $barangay = strtoupper($sanitize->sanitizeForString($data["barangay"]));
    //     $street = strtoupper($sanitize->sanitizeForString($data["street"]));
    //     $website = $sanitize->sanitizeForString($data["website"]);
    //     $tinno = $sanitize->sanitizeForString($data["tinno"]);
    //     $mobileno1 = $sanitize->sanitizeForString($data["mobileno1"]);
    //     $mobileno2 = $sanitize->sanitizeForString($data["mobileno2"]);
    //     $mobileno3 = $sanitize->sanitizeForString($data["mobileno3"]);
    //     $contactperson = strtoupper($sanitize->sanitizeForString($data["contactperson"]));
    //     $cpmobileno1 = $sanitize->sanitizeForString($data["cpmobileno1"]);
    //     $cpmobileno2 = $sanitize->sanitizeForString($data["cpmobileno2"]);
    //     $cpmobileno3 = $sanitize->sanitizeForString($data["cpmobileno3"]);
    //     $terms = $sanitize->sanitizeForString($data["terms"]);
    //     $modeofpayment = strtoupper($sanitize->sanitizeForString($data["modeofpayment"]));
    //     $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        
    //     $sql = "UPDATE tbl_contents SET fullname = ?, businessname = ?, fdalno = ?, businesstinno = ?, businessprovince = ?, businesstown = ?, businessbarangay = ?, businessstreet = ?, creditlimit = ?, creditbalance = ?, firstname = ?, middlename = ?, lastname = ?, extension = ?, gender = ?, email = ?, province = ?, town = ?, barangay = ?, street = ?, website = ?, tinno = ?, mobileno1 = ?, mobileno2 = ?, mobileno3 = ?, contactperson = ?, cpmobileno1 = ?, cpmobileno2 = ?, cpmobileno3 = ?, terms = ?, modeofpayment = ?, remarks = ? WHERE contentno = ?";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("sssssssssssssssssssssssssssssssss", $fullname, $businessname, $fdalno, $businesstinno, $businessprovince, $businesstown, $businessbarangay, $businessstreet, $creditlimit, $creditbalance, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $contactperson, $cpmobileno1, $cpmobileno2, $cpmobileno3, $terms, $modeofpayment, $remarks, $contentno);

    //     if (!$stmt->execute()) {
    //         $stmt->close();
    //         printf($stmt->error);
    //     } else {
    //         $stmt->close();
    //         echo 'SUCCESS';
    //     }
    // }
}
