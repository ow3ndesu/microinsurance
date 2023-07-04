<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Employee extends Database {
    
    public function LoadEmployeeNumber() {

        $series = '00001';
        $asquery = "SELECT LPAD(COUNT(*) OVER () + 1, 5, '0') AS series FROM tbl_employees LIMIT 1;";
        $asstmt = $this->conn->prepare($asquery);
        $asstmt->execute();
        $result = $asstmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $series = $row['series'];
        }
        $asstmt->close();

        echo 'EMP' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5) . '-' . $series;
    }

    public function LoadEmployees() {
        
        $employees = [];
        $employeesloaded = "EMPLOYEES_LOADED";

        $query = "SELECT id, employeeno, fullname, firstname, middlename, lastname, extension, province, town, barangay, street, birthdate, age, gender, datehired, mobileno, position, sssno, philhealthno, pagibigno, tinno, email, resignationdate, remarks FROM tbl_employees WHERE employeeno != 'ADMIN/IT';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $employeesloaded,
                "EMPLOYEES" => $employees
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadProfile() {
        
        $profile = [];
        $username = $_SESSION['username'];
        $profileloaded = "PROFILE_LOADED";

        $query = "SELECT employeeno FROM tbl_users WHERE username = '$username' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $employeeno = (($stmt->get_result())->fetch_assoc())['employeeno'];
        $stmt->close();

        $query = "SELECT fullname, firstname, middlename, lastname, extension, province, town, barangay, street, birthdate, age, gender, datehired, mobileno, position, sssno, philhealthno, pagibigno, tinno, email, resignationdate, remarks FROM tbl_employees WHERE employeeno = '$employeeno' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {
            echo "NO DATA";
            return;
        }
        
        while($row = $result->fetch_assoc()) {
            $profile[] = $row;
        }

        $sql = "SELECT CONCAT(initials, '-', sicount) AS sinumber FROM tbl_sinumber WHERE agent = '$employeeno' LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 0) {
            $sinumber = "NONE";
        } else {
            $sinumber = ($res->fetch_assoc())['sinumber'];
        }

        echo json_encode(array(
            "MESSAGE" => $profileloaded,
            "PROFILE" => $profile,
            "SI" => $sinumber
        ));

    }

    public function LoadUnsignedEmployees() {
        
        $employees = [];
        $employeesloaded = "EMPLOYEES_LOADED";

        $query = "SELECT e.id, e.employeeno, e.fullname, e.firstname, e.middlename, e.lastname, e.extension, e.province, e.town, e.barangay, e.street, e.birthdate, e.age, e.gender, e.datehired, e.mobileno, e.position, e.sssno, e.philhealthno, e.pagibigno, e.tinno, e.email, e.resignationdate, e.remarks FROM tbl_employees e WHERE NOT EXISTS (SELECT employeeno FROM tbl_users WHERE employeeno = e.employeeno);";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $employeesloaded,
                "EMPLOYEES" => $employees
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadNonAgentEmployees() {
        
        $employees = [];
        $employeesloaded = "EMPLOYEES_LOADED";

        $query = "SELECT e.id, e.employeeno, e.fullname, e.firstname, e.middlename, e.lastname, e.extension, e.province, e.town, e.barangay, e.street, e.birthdate, e.age, e.gender, e.datehired, e.mobileno, e.position, e.sssno, e.philhealthno, e.pagibigno, e.tinno, e.email, e.resignationdate, e.remarks FROM tbl_employees e WHERE NOT EXISTS (SELECT agent FROM tbl_sinumber WHERE agent = e.employeeno) AND e.employeeno <> 'ADMIN/IT';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $employeesloaded,
                "EMPLOYEES" => $employees
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadEmployee($data) {
        
        $employee = [];
        $sanitize = new Sanitize();
        $employeeno = $sanitize->sanitizeForString($data["employeeno"]);
        $employeeloaded = "EMPLOYEE_LOADED";

        $query = "SELECT id, employeeno, fullname, firstname, middlename, lastname, extension, province, town, barangay, street, birthdate, age, gender, datehired, mobileno, position, sssno, philhealthno, pagibigno, tinno, email, resignationdate, remarks FROM tbl_employees WHERE employeeno = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $employeeno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $employee[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $employeeloaded,
                "EMPLOYEE" => $employee
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function GetEmployeeeNameByEmployeeNo($employeeno) {
        $gathered = is_array($employeeno) ? $employeeno['employeeno'] : $employeeno;
        $stmt = $this->conn->prepare('SELECT fullname FROM tbl_employees WHERE employeeno = ? LIMIT 1;');
        $stmt->bind_param('s', $gathered);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {
            return 'UNAVAILABLE';
        }

        $fullname = ($result->fetch_assoc())['fullname'];
        if (is_array($employeeno)) {
            echo $fullname;
        } else {
           return $fullname;
        }
    }

    public function SubmitEmployee($data) {
        $sanitize = new Sanitize();
        $employeeno = $sanitize->sanitizeForString($data["employeeno"]);
        $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $firstname = strtoupper($sanitize->sanitizeForString($data["firstname"]));
        $middlename = strtoupper($sanitize->sanitizeForString($data["middlename"]));
        $lastname = strtoupper($sanitize->sanitizeForString($data["lastname"]));
        $extension = strtoupper($sanitize->sanitizeForString($data["extension"]));
        $province = strtoupper($sanitize->sanitizeForString($data["province"]));
        $town = strtoupper($sanitize->sanitizeForString($data["town"]));
        $barangay = strtoupper($sanitize->sanitizeForString($data["barangay"]));
        $street = strtoupper($sanitize->sanitizeForString($data["street"]));
        $birthdate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["birthdate"])));
        $age = $sanitize->sanitizeForString($data["age"]);
        $gender = strtoupper($sanitize->sanitizeForString($data["gender"]));
        $datehired = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datehired"])));
        $mobileno = $sanitize->sanitizeForString($data["mobileno"]);
        $position = strtoupper($sanitize->sanitizeForString($data["position"]));
        $sssno = $sanitize->sanitizeForString($data["sssno"]);
        $philhealthno = $sanitize->sanitizeForString($data["philhealthno"]);
        $pagibigno = $sanitize->sanitizeForString($data["pagibigno"]);
        $tinno = $sanitize->sanitizeForString($data["tinno"]);
        $email = $sanitize->sanitizeForEmail($data["email"]);
        $resignationdate = ($data["resignationdate"] != '') ? date('m/d/Y', strtotime($sanitize->sanitizeForString($data["resignationdate"]))) : '';
        $dateencoded = date('m/d/Y');

        $sql = "SELECT employeeno FROM tbl_employees WHERE employeeno = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $employeeno);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Employee Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_employees (employeeno, fullname, firstname, middlename, lastname, extension, province, town, barangay, street, birthdate, age, gender, datehired, mobileno, position, sssno, philhealthno, pagibigno, tinno, email, resignationdate, dateencoded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssssssssssssssss", $employeeno, $fullname, $firstname, $middlename, $lastname, $extension, $province, $town, $barangay, $street, $birthdate, $age, $gender, $datehired, $mobileno, $position, $sssno, $philhealthno, $pagibigno, $tinno, $email, $resignationdate, $dateencoded);

            if (!$stmt->execute()) {
                $stmt->close();
                printf($stmt->error);
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdateEmployee($data) {
        $sanitize = new Sanitize();
        $employeeno = $sanitize->sanitizeForString($data["employeeno"]);
        $fullname = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $firstname = strtoupper($sanitize->sanitizeForString($data["firstname"]));
        $middlename = strtoupper($sanitize->sanitizeForString($data["middlename"]));
        $lastname = strtoupper($sanitize->sanitizeForString($data["lastname"]));
        $extension = strtoupper($sanitize->sanitizeForString($data["extension"]));
        $province = strtoupper($sanitize->sanitizeForString($data["province"]));
        $town = strtoupper($sanitize->sanitizeForString($data["town"]));
        $barangay = strtoupper($sanitize->sanitizeForString($data["barangay"]));
        $street = strtoupper($sanitize->sanitizeForString($data["street"]));
        $birthdate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["birthdate"])));
        $age = $sanitize->sanitizeForString($data["age"]);
        $gender = strtoupper($sanitize->sanitizeForString($data["gender"]));
        $datehired = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datehired"])));
        $mobileno = $sanitize->sanitizeForString($data["mobileno"]);
        $position = strtoupper($sanitize->sanitizeForString($data["position"]));
        $sssno = $sanitize->sanitizeForString($data["sssno"]);
        $philhealthno = $sanitize->sanitizeForString($data["philhealthno"]);
        $pagibigno = $sanitize->sanitizeForString($data["pagibigno"]);
        $tinno = $sanitize->sanitizeForString($data["tinno"]);
        $email = $sanitize->sanitizeForEmail($data["email"]);
        $resignationdate = ($data["resignationdate"] != '') ? date('m/d/Y', strtotime($sanitize->sanitizeForString($data["resignationdate"]))) : '';
        $remarks = strtoupper($sanitize->sanitizeForString($data["remarks"]));
        $dateencoded = date('m/d/Y');
        
        $sql = "UPDATE tbl_employees SET fullname = ?, firstname = ?, middlename = ?, lastname = ?, extension = ?, province = ?, town = ?, barangay = ?, street = ?, birthdate = ?, age = ?, gender = ?, datehired = ?, mobileno = ?, position = ?, sssno = ?, philhealthno = ?, pagibigno = ?, tinno = ?, email = ?, resignationdate = ?, remarks = ? WHERE employeeno = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssss", $fullname, $firstname, $middlename, $lastname, $extension, $province, $town, $barangay, $street, $birthdate, $age, $gender, $datehired, $mobileno, $position, $sssno, $philhealthno, $pagibigno, $tinno, $email, $resignationdate, $remarks, $employeeno);

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