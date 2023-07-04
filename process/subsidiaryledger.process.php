<?php 
include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database 
{
    public function Initialize(){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_customers ORDER BY businessname ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $Customers = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "CUSTOMERS" => $Customers,
        ));
    }

    public function SelectCustomers($data){
        $stmt = $this->conn->prepare("SELECT loanid,transactionno,'CURRENT' as loantype FROM tbl_loans WHERE customerno = ?");
        $stmt->bind_param("s",$data["val"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $LoanList = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "LOANS" => $LoanList
        ));
    }

    public function LoadBooks($data){
        // //$this->COMPACCTCONNECT();
        // $stmt = $this->conn->prepare("SELECT * FROM (SELECT * FROM TBL_CASHDISBURSEMENT WHERE CLIENTNO='".$data["customerno"]."' AND LOANID='".$data["loanid"]."') AS myTABLE WHERE LRCLIENTDR <> '0' OR GLNO='1121' OR GLNO='2151' OR GLNO='2159' OR GLNO='2160' OR GLNO='2154' OR GLNO='4201' OR GLNO='4203' OR SLNO='2151' OR SLNO='2154' OR SLNO='4201' OR SLNO='1121'");

        $stmt = $this->conn->prepare("SELECT * FROM tbl_cashreceipt WHERE loanid = ? AND glno <> '2173' AND (clientno = ? OR slno = ?)");
        $stmt->bind_param("sss",$data["loanid"],$data["customerno"],$data["customerno"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $CRBData = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM (SELECT * FROM tbl_cashdisbursement WHERE clientno = ? AND loanid = ?) as MyTable WHERE LRCLIENTDR <> '0' OR GLNO='1121' OR GLNO='2151' OR GLNO='2159' OR GLNO='2160' OR GLNO='2154' OR GLNO='4201' OR GLNO='4203' OR SLNO='2151' OR SLNO='2154' OR SLNO='4201' OR SLNO='1121'");
        $stmt->bind_param("ss",$data["customerno"],$data["loanid"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $CDBData = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_journalvouchers WHERE clientno = ? AND loanid = ? AND slno <> '0' AND slno <> '-' AND slno <> '2163' AND slno <> '2116'");
        $stmt->bind_param("ss",$data["customerno"],$data["loanid"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $JVData = $this->FillRow($result);
        $stmt->close();

        $this->__construct();
        $stmt = $this->conn->prepare("SELECT * FROM tbl_aging WHERE customerno = ? AND loanid = ?");
        $stmt->bind_param("ss",$data["customerno"],$data["loanid"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $AgingData = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "CRB" => $CRBData,
            "CDB" => $CDBData,
            "JV" => $JVData,
            "AGING" => $AgingData
        ));
    }

    private function FillRow($data){
        $arr = [];
        while ($row = $data->fetch_assoc()) {
            $arr[] = $row;
        }
        return $arr;
    }
}
?>