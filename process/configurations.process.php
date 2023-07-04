<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database {
    public function LoadConfigCounts() {
        
        $maintenances = 0;
        $terminals = 0;
        $users = 0;
        $companystatus = '';
        $countsloaded = "COUNTS_LOADED";

        $query1 = "SELECT COUNT(DISTINCT(itemtype)) AS maintenances FROM tbl_maintenance_lists WHERE itemtype NOT IN ('LASTCLOSINGDATE', 'HOLIDAYENABLED');";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->execute();
        $row1 = ($stmt1->get_result()->fetch_assoc());
        $stmt1->close();

        $query2 = "SELECT COUNT(terminal) AS terminals FROM tbl_terminals;";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $row2 = ($stmt2->get_result()->fetch_assoc());
        $stmt2->close();

        $query3 = "SELECT COUNT(id) AS users FROM tbl_users WHERE type != 'ADMIN/IT';";
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->execute();
        $row3 = ($stmt3->get_result()->fetch_assoc());
        $stmt3->close();

        $query4 = "SELECT IF(STR_TO_DATE(lastupdate, '%m/%d/%Y') < DATE_SUB(NOW(), INTERVAL 1 MONTH), 'PLEASE UPDATE', 'UPDATED') AS companystatus FROM tbl_company LIMIT 1;";
        $stmt4 = $this->conn->prepare($query4);
        $stmt4->execute();
        $row4 = ($stmt4->get_result()->fetch_assoc());
        $stmt4->close();

        try {
            $maintenances = $row1['maintenances'];
        } catch (\Throwable $th) { }

        try {
            $terminals = $row2['terminals'];
        } catch (\Throwable $th) { }

        try {
            $users = $row3['users'];
        } catch (\Throwable $th) { }

        try {
            $companystatus = $row4['companystatus'];
        } catch (\Throwable $th) { }

        echo json_encode(array(
            "MESSAGE" => $countsloaded,
            "MAINTENANCES" => $maintenances,
            "TERMINALS" => $terminals,
            "USERS" => $users,
            "COMPANY" => $companystatus
        ));
    }

    public function LoadDashboardCounts() {
        
        $employees = 0;
        $clients = 0;
        $suppliers = 0;
        $products = 0;
        $countsloaded = "COUNTS_LOADED";

        $query1 = "SELECT COUNT(DISTINCT(employeeno)) AS employees FROM tbl_employees;";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->execute();
        $row1 = ($stmt1->get_result()->fetch_assoc());
        $stmt1->close();

        $query2 = "SELECT COUNT(customerno) AS clients FROM tbl_customers;";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $row2 = ($stmt2->get_result()->fetch_assoc());
        $stmt2->close();

        $query3 = "SELECT COUNT(supplierno) AS suppliers FROM tbl_suppliers;";
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->execute();
        $row3 = ($stmt3->get_result()->fetch_assoc());
        $stmt3->close();

        $query4 = "SELECT COUNT(DISTINCT(barcode)) AS products FROM tbl_inventory_current;";
        $stmt4 = $this->conn->prepare($query4);
        $stmt4->execute();
        $row4 = ($stmt4->get_result()->fetch_assoc());
        $stmt4->close();

        try {
            $employees = $row1['employees'];
        } catch (\Throwable $th) { }

        try {
            $clients = $row2['clients'];
        } catch (\Throwable $th) { }

        try {
            $suppliers = $row3['suppliers'];
        } catch (\Throwable $th) { }

        try {
            $products = $row4['products'];
        } catch (\Throwable $th) { }

        echo json_encode(array(
            "MESSAGE" => $countsloaded,
            "EMPLOYEES" => $employees,
            "CLIENTS" => $clients,
            "SUPPLIERS" => $suppliers,
            "PRODUCTS" => $products
        ));
    }

    public function LoadMaintenanceLists() {
        
        $maintenances = [];
        $maintenancesloaded = "MAINTENANCES_LOADED";

        $query = "SELECT DISTINCT(itemtype) AS maintenances FROM tbl_maintenance_lists WHERE itemtype NOT IN ('LASTCLOSINGDATE', 'HOLIDAYENABLED');";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $maintenances[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $maintenancesloaded,
                "MAINTENANCES" => $maintenances
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadDeliveryReceipt() {
        
        $dr = [];
        $drloaded = "DR_LOADED";

        $query = "SELECT drcount FROM tbl_drnumber LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $dr[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $drloaded,
                "DR" => $dr
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPaymentReceipt() {
        
        $pr = [];
        $prloaded = "DR_LOADED";

        $query = "SELECT prcount FROM tbl_prnumber LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $pr[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $prloaded,
                "PR" => $pr
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadSalesInvoices() {
        
        $si = [];
        $siloaded = "SI_LOADED";

        $query = "SELECT e.fullname, s.agent, s.initials, s.sicount FROM tbl_sinumber s INNER JOIN tbl_employees e ON e.employeeno = s.agent;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $si[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $siloaded,
                "SIS" => $si
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadSalesInvoice($data) {
        
        $si = [];
        $sanitize = new Sanitize();
        $agent = strtoupper($sanitize->sanitizeForString($data["agent"]));
        $siloaded = "SI_LOADED";

        $query = "SELECT e.fullname, s.agent, s.initials, s.sicount FROM tbl_sinumber s INNER JOIN tbl_employees e ON e.employeeno = s.agent WHERE s.agent = '$agent';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $si[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $siloaded,
                "AGENT" => $si
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function SubmitUpdateDeliveryReceipt($data) {
        
        $sanitize = new Sanitize();
        $drcount = strtoupper($sanitize->sanitizeForString($data["drcount"]));
        
        $sql = "UPDATE tbl_drnumber SET drcount = ?;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $drcount);

        if (!$stmt->execute()) {
            printf($stmt->error);
            $stmt->close();
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function SubmitUpdatePaymentReceipt($data) {
        
        $sanitize = new Sanitize();
        $prcount = strtoupper($sanitize->sanitizeForString($data["prcount"]));
        
        $sql = "UPDATE tbl_prnumber SET prcount = ?;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $prcount);

        if (!$stmt->execute()) {
            printf($stmt->error);
            $stmt->close();
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function SubmitUpdateSalesInvoice($data) {
        
        $sanitize = new Sanitize();
        $agent = strtoupper($sanitize->sanitizeForString($data["agent"]));
        $initials = strtoupper($sanitize->sanitizeForString($data["initials"]));
        $sicount = strtoupper($sanitize->sanitizeForString($data["sicount"]));
        
        $sql = "UPDATE tbl_sinumber SET sicount = '$sicount' WHERE agent = '$agent' AND initials = '$initials';";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt->execute()) {
            printf($stmt->error);
            $stmt->close();
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function SubmitSI($data) {
        
        $sanitize = new Sanitize();
        $agent = strtoupper($sanitize->sanitizeForString($data["agent"]));
        $initials = strtoupper($sanitize->sanitizeForString($data["initials"]));
        $sicount = strtoupper($sanitize->sanitizeForString($data["sicount"]));

        $sql = "SELECT agent FROM tbl_sinumber WHERE agent = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $agent);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Employee Already An Agent.';
            return;
        }

        $sql = "SELECT initials FROM tbl_sinumber WHERE initials = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $initials);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Initials In Use.';
            return;
        }
        
        $sql = "INSERT INTO tbl_sinumber (agent, initials, sicount) VALUES ('$agent', '$initials', '$sicount')";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt->execute()) {
            printf($stmt->error);
            $stmt->close();
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function LoadMaintenance($data) {
        
        $sanitize = new Sanitize();
        $maintenance = [];
        $maintenanceloaded = "MAINTENANCE_LOADED";

        $maintenancetype = ($sanitize->sanitizeForString($data['maintenance']));

        $query = "SELECT id, itemtype AS maintenance, itemname AS lists FROM tbl_maintenance_lists WHERE itemtype = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $maintenancetype);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $maintenance[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $maintenanceloaded,
                "MAINTENANCE" => $maintenance
            ));

        } else {
            echo 'NO_DATA';
        }
    }
    
    public function LoadShelves() {
        
        $shelves = 0;
        $shelvesloaded = "SHELVES_LOADED";

        $query = "SELECT itemname AS shelves FROM tbl_maintenance_lists WHERE itemtype = 'SHELVES' LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $shelves = $row['shelves'];

            echo json_encode(array(
                "MESSAGE" => $shelvesloaded,
                "SHELVES" => $shelves
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadTerms() {
        
        $terms = 0;
        $termsloaded = "TERMS_LOADED";

        $query = "SELECT itemname AS terms FROM tbl_maintenance_lists WHERE itemtype = 'TERMS' LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $terms = $row['terms'];

            echo json_encode(array(
                "MESSAGE" => $termsloaded,
                "TERMS" => $terms
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPurposes() {
        
        $purposes = [];
        $purposesloaded = "PURPOSES_LOADED";

        $query = "SELECT itemname AS purposes FROM tbl_maintenance_lists WHERE itemtype = 'PURPOSE';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $purposes[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $purposesloaded,
                "PURPOSES" => $purposes
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadModeOfPayments() {
        
        $modes = [];
        $modesloaded = "MODES_LOADED";

        $query = "SELECT itemname AS modes FROM tbl_maintenance_lists WHERE itemtype = 'MODEOFPAYMENT';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $modes[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $modesloaded,
                "MODES" => $modes
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadPaymentMethod() {
        
        $modes = [];
        $modesloaded = "MODES_LOADED";

        $query = "SELECT itemname AS modes FROM tbl_maintenance_lists WHERE itemtype = 'PAYMENTMETHOD';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $modes[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $modesloaded,
                "MODES" => $modes
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadCategories() {
        
        $categories = [];
        $categoriesloaded = "CATEGORIES_LOADED";

        $query = "SELECT itemname AS categories FROM tbl_maintenance_lists WHERE itemtype = 'CATEGORY';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $categoriesloaded,
                "CATEGORIES" => $categories
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadUnits() {
        
        $units = [];
        $unitsloaded = "UNITS_LOADED";

        $query = "SELECT itemname AS units FROM tbl_maintenance_lists WHERE itemtype = 'UNIT';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $units[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $unitsloaded,
                "UNITS" => $units
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadDescriptions() {
        
        $descriptions = [];
        $descriptionsloaded = "DESCRIPTIONS_LOADED";

        $query = "SELECT itemname AS descriptions FROM tbl_maintenance_lists WHERE itemtype = 'DESCRIPTION';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $descriptions[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $descriptionsloaded,
                "DESCRIPTIONS" => $descriptions
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadItem($data) {
        
        $item = [];
        $itemloaded = "ITEMS_LOADED";
        $id = $data['id'];

        $query = "SELECT id, itemtype, itemname FROM tbl_maintenance_lists WHERE id = ?;";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $item[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $itemloaded,
                "ITEM" => $item
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function SubmitItem($data) {
        $sanitize = new Sanitize();
        $itemtype = strtoupper($sanitize->sanitizeForString($data["itemtype"]));
        $itemname = strtoupper($sanitize->sanitizeForString($data["itemname"]));

        $sql = "SELECT id FROM tbl_maintenance_lists WHERE itemtype = ? AND itemname = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $itemtype, $itemname);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Item Is Already Present In This List.';
        } else {
            $sql = "INSERT INTO tbl_maintenance_lists (itemtype, itemname) VALUES (?, ?);";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $itemtype, $itemname);

            if (!$stmt->execute()) {
                printf($stmt->error);
                $stmt->close();
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdateItem($data) {
        $sanitize = new Sanitize();
        $id = $sanitize->sanitizeForString($data["id"]);
        $itemtype = strtoupper($sanitize->sanitizeForString($data["itemtype"]));
        $itemname = strtoupper($sanitize->sanitizeForString($data["itemname"]));
        
        $sql = "UPDATE tbl_maintenance_lists SET itemtype = ?, itemname = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $itemtype, $itemname, $id);

        if (!$stmt->execute()) {
            printf($stmt->error);
            $stmt->close();
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    // public function SubmitCustomer($data) {
    //     $sanitize = new Sanitize();
    //     $customerno = $sanitize->sanitizeForString($data["customerno"]);
    //     $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
    //     $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
    //     $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
    //     $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
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

    //     $sql = "SELECT customerno FROM tbl_customers WHERE customerno = ? LIMIT 1;";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param('s', $customerno);
    //     $stmt->execute();
    //     $res = $stmt->get_result();
    //     $stmt->close();

    //     if ($res->num_rows == 1) {
    //         echo 'Customer Number In Use.';
    //     } else {
    //         $sql = "INSERT INTO tbl_customers (customerno, businessname, fdalno, businesstinno, fullname, firstname, middlename, lastname, extension, gender, email, province, town, barangay, street, website, tinno, mobileno1, mobileno2, mobileno3, contactperson, cpmobileno1, cpmobileno2, cpmobileno3, terms, modeofpayment, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->bind_param("sssssssssssssssssssssssssss", $customerno, $businessname, $fdalno, $businesstinno, $fullname, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $contactperson, $cpmobileno1, $cpmobileno2, $cpmobileno3, $terms, $modeofpayment, $dateencoded);

    //         if (!$stmt->execute()) {
    //             printf($stmt->error);
    //             $stmt->close();
    //         } else {
    //             $stmt->close();
    //             echo 'SUCCESS';
    //         }
    //     }
    // }

    // public function SubmitUpdateCustomer($data) {
    //     $sanitize = new Sanitize();
    //     $customerno = $sanitize->sanitizeForString($data["customerno"]);
    //     $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
    //     $businessname = strtoupper($sanitize->sanitizeForString($data["businessname"]));
    //     $fdalno = strtoupper($sanitize->sanitizeForString($data["fdalno"]));
    //     $businesstinno = $sanitize->sanitizeForString($data["businesstinno"]);
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
        
    //     $sql = "UPDATE tbl_customers SET fullname = ?, businessname = ?, fdalno = ?, businesstinno = ?, firstname = ?, middlename = ?, lastname = ?, extension = ?, gender = ?, email = ?, province = ?, town = ?, barangay = ?, street = ?, website = ?, tinno = ?, mobileno1 = ?, mobileno2 = ?, mobileno3 = ?, contactperson = ?, cpmobileno1 = ?, cpmobileno2 = ?, cpmobileno3 = ?, terms = ?, modeofpayment = ?, remarks = ? WHERE customerno = ?";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("sssssssssssssssssssssssssss", $fullname, $businessname, $fdalno, $businesstinno, $firstname, $middlename, $lastname, $extension, $gender, $email, $province, $town, $barangay, $street, $website, $tinno, $mobileno1, $mobileno2, $mobileno3, $contactperson, $cpmobileno1, $cpmobileno2, $cpmobileno3, $terms, $modeofpayment, $remarks, $customerno);

    //     if (!$stmt->execute()) {
    //         $stmt->close();
    //         printf($stmt->error);
    //     } else {
    //         $stmt->close();
    //         echo 'SUCCESS';
    //     }
    // }
}
?>