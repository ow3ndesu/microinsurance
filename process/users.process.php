<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("employees.process.php");

class Process extends Database {

    public function LoadUserID() {
        $series = '00001';
        $asquery = "SELECT LPAD(COUNT(*) OVER () + 1, 5, '0') AS series FROM tbl_users LIMIT 1;";
        $asstmt = $this->conn->prepare($asquery);
        $asstmt->execute();
        $result = $asstmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $series = $row['series'];
        }
        $asstmt->close();

        echo 'USER' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5) . '-' . $series;
    }

    public function LoadUsers() {
        $users = [];
        $usersloaded = "USERS_LOADED";
        $excemptedtype = "ADMIN/IT";

        $query = "SELECT id, userid, employeeno, username, type FROM tbl_users WHERE type != '". $excemptedtype ."'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $usersloaded,
                "USERS" => $users
            ));

        } else {
            echo 'NO_DATA';
        }
    }
    
    public function LoadUser($data) {
        
        $user = [];
        $sanitize = new Sanitize();
        $employee = new Employee();
        $userid = $sanitize->sanitizeForString($data["userid"]);
        $userloaded = "USER_LOADED";

        $query = "SELECT id, userid, employeeno as fullname, type, username, status, terminal FROM tbl_users WHERE userid = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $user[] = $row;
            }

            $employeeno = $user[0]['fullname'];
            $user[0]['fullname'] = $employee->GetEmployeeeNameByEmployeeNo($employeeno);

            echo json_encode(array(
                "MESSAGE" => $userloaded,
                "USER" => $user
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function checkUserID($userid) {
        $stmt = $this->conn->prepare("SELECT id, userid, username, password, type, status FROM tbl_users WHERE userid = ?");
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return array(
            'userid' => $userid,
            'userid' => ($result->num_rows > 0) ? $row['userid'] : null,
            'username' => ($result->num_rows > 0) ? $row['username'] : null,
            'password' => ($result->num_rows > 0) ? $row['password'] : null,
            'type' => ($result->num_rows > 0) ? $row['type'] : null,
            'status' => ($result->num_rows > 0) ? $row['status'] : null,
            'isPresent' => ($result->num_rows > 0) ? 1 : 0,
            'isValidForNewAccount' => ($result->num_rows > 0) ? 0 : 1,
        );
    }

    public function VerifyPassword($data) {
        $sanitize = new Sanitize();
        $employee = new Employee();

        $userid = strtoupper($sanitize->sanitizeForString($data["userid"]));
        $password = $sanitize->sanitizeForString($data["password"]);

        $md5password = md5($password);
        $usernameDetails = $this->checkUserID($userid);

        if ($usernameDetails['isPresent'] == 1) {
            if ($usernameDetails['password'] == $md5password) {
                echo json_encode(array(
                    'STATUS' => 'SUCCESSFUL',
                    'MESSAGE' => 'Password Matched'
                ));
            } else  {
                echo json_encode(array(
                    'STATUS' => 'UNSUCCESSFUL',
                    'MESSAGE' => 'Incorrect Password'
                ));
            }
        } else {
            echo json_encode(array(
                'STATUS' => 'UNSUCCESSFUL',
                'MESSAGE' => 'Account Not Found'
            ));
        }
    }

    public function ChangeUserPassword($data) {
        $sanitize = new Sanitize();
        $userid = strtoupper($sanitize->sanitizeForString($data["userid"]));
        $password = $sanitize->sanitizeForString($data["password"]);
        
        $md5password = md5($password);
        
        $sql = "UPDATE tbl_users SET password = ? WHERE userid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $md5password, $userid);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function SubmitUser($data) {
        $sanitize = new Sanitize();
        $employee = new Employee();

        $userid = strtoupper($sanitize->sanitizeForString($data["userid"]));
        $employeeno = strtoupper($sanitize->sanitizeForString($data["fullname"]));
        $type = strtoupper($sanitize->sanitizeForString($data["type"]));
        $username = $sanitize->sanitizeForString($data["username"]);
        $password = $sanitize->sanitizeForString($data["password"]);
        $terminal = strtoupper($sanitize->sanitizeForString($data["terminal"]));
        $status = "ENABLED";

        // $employeeno = $employee->GetEmployeeeNameByEmployeeNo($employeeno);
        $md5password = md5($password);

        $sql = "SELECT userid FROM tbl_users WHERE userid = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userid);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'User ID In Use.';
        } else {
            $sql = "INSERT INTO tbl_users (userid, employeeno, username, password, type, terminal, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssss", $userid, $employeeno, $username, $md5password, $type, $terminal, $status);

            if (!$stmt->execute()) {
                $stmt->close();
                printf($stmt->error);
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdateUser($data) {
        $sanitize = new Sanitize();
        $userid = strtoupper($sanitize->sanitizeForString($data["userid"]));
        $username = $sanitize->sanitizeForString($data["username"]);
        $status = strtoupper($sanitize->sanitizeForString($data["status"]));
        $terminal = strtoupper($sanitize->sanitizeForString($data["terminal"]));

        $sql = "SELECT username FROM tbl_users WHERE username = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Username In Use.';
        } else {
            $sql = "UPDATE tbl_users SET username = ?, terminal = ?, status = ? WHERE userid = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $terminal, $status, $userid);

            if (!$stmt->execute()) {
                $stmt->close();
                printf($stmt->error);
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }
}
?>