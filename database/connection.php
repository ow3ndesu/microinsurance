<?php
session_start();
class Database
{
    public $conn;

    public function __construct()
    {   
        require_once("constants.php");
        $this->conn = new Mysqli(HOST, USERNAME, PASSWORD, DBNAME, PORT); 
        $this->conn->set_charset("utf8");
        if($this->conn) {
            date_default_timezone_set("Asia/Singapore");
            return $this->conn;
        } else {
            return "CANT CONNECT TO DATABASE" . $conn->error;
        }
    }

    public function COMPACCTCONNECT(){
        require_once("constants.php");
        $this->conn = new Mysqli(HOST1, USER1, PASS1, DB1, PORT1); 
        $this->conn->set_charset("utf8");
        if($this->conn) {
            date_default_timezone_set("Asia/Singapore");
            return $this->conn;
        } else {
            return "CANT CONNECT TO DATABASE" . $conn->error;
        }
    }

    public function AMSCONNECT(){
        require_once("constants.php");
        $this->conn = new Mysqli(HOST2, USER2, PASS2, DB2, PORT2); 
        $this->conn->set_charset("utf8");
        if($this->conn) {
            date_default_timezone_set("Asia/Singapore");
            return $this->conn;
        } else {
            return "CANT CONNECT TO DATABASE" . $conn->error;
        }
    }
}
?>