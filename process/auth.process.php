<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database{

    public function checkUsername($username) {
        $stmt = $this->conn->prepare("SELECT id, userid, username, password, permissions, status FROM tbl_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return array(
            'username' => $username,
            'userid' => ($result->num_rows > 0) ? $row['userid'] : null,
            'username' => ($result->num_rows > 0) ? $row['username'] : null,
            'password' => ($result->num_rows > 0) ? $row['password'] : null,
            'status' => ($result->num_rows > 0) ? $row['status'] : null,
            'permissions' => ($result->num_rows > 0) ? $row['permissions'] : null,
            'isPresent' => ($result->num_rows > 0) ? 1 : 0,
            'isValidForNewAccount' => ($result->num_rows > 0) ? 0 : 1,
        );
    }

    public function LoginAuth($data) {
        $sanitize = new Sanitize();
        $username = $sanitize->sanitizeForString($data['username']);
        $password = $sanitize->sanitizeForString($data['password']);
        $passwordmd5 = md5($password);

        $usernameDetails = $this->checkUsername($username);

        if ($usernameDetails['isPresent'] <> 1) {
            echo json_encode(array(
                'STATUS' => 'UNSUCCESSFUL',
                'MESSAGE' => 'Account Not Found'
            ));
            return;
        }

        if ($usernameDetails['password'] <> $passwordmd5) {
            echo json_encode(array(
                'STATUS' => 'UNSUCCESSFUL',
                'MESSAGE' => 'Incorrect Password'
            ));
            return;
        }

        if ($usernameDetails['status'] <> 'ENABLED') {
            echo json_encode(array(
                'STATUS' => 'UNSUCCESSFUL',
                'MESSAGE' => 'Account Not Enabled'
            ));
            return;
        }

        $_SESSION['MGNSVN03M10Z174U'] = 1; # authenticated
        $_SESSION['MTTPRZ86T69G097Q'] = $usernameDetails['userid']; # userid
        $_SESSION['LMBSMN77L08E049M'] = $usernameDetails['username']; # username
        $_SESSION['MNCFLL86D47Z830B'] = $usernameDetails['permissions']; # permissions
        
        $url = 'authenticated/dashboard';
        echo json_encode(array(
            'STATUS' => 'SUCCESSFUL',
            'URL' => $url
        ));
    }
    
    public function LogoutAuth() {
        if (session_destroy()) {
            echo "LOGOUT_SUCCESS";
        } else {
            echo "LOGOUT_UNSUCCESSFUL";
        }
    }
}
?>