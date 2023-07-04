<?php 
include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database 
{
    public function Initialize(){
        // //$this->COMPACCTCONNECT();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes ORDER BY acctitles");
        $stmt->execute();
        $result = $stmt->get_result();
        $AccountCodes = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT DISTINCT fund FROM tbl_banksetup WHERE fund <> '-' ORDER BY fund");
        $stmt->execute();
        $result = $stmt->get_result();
        $FundSetup = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT name FROM tbl_orseries ORDER BY name");
        $stmt->execute();
        $result = $stmt->get_result();
        $ORNames = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT name FROM tbl_arseries ORDER BY name");
        $stmt->execute();
        $result = $stmt->get_result();
        $ARNames = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT DISTINCT bank,fund,id,lastcv,slno,nextcheck,type,bankcode FROM tbl_banksetup ORDER BY bank");
        $stmt->execute();
        $result = $stmt->get_result();
        $BankSetup = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT DISTINCT category FROM tbl_subsidiarycodes WHERE category <> '-' ORDER BY category");
        $stmt->execute();
        $result = $stmt->get_result();
        $SLType = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT accountcode,slname FROM tbl_allowedsls WHERE slname <> '' ORDER BY slname");
        $stmt->execute();
        $result = $stmt->get_result();
        $SLName = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT accountcode FROM tbl_specialacctcodes");
        $stmt->execute();
        $result = $stmt->get_result();
        $SpecialAcctCodes = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT DISTINCT cdpname FROM tbl_cdps ORDER BY cdpname");
        $stmt->execute();
        $result = $stmt->get_result();
        $CDPList = $this->FillRow($result);
        $stmt->close();

        // $stmt = $this->conn->prepare("UPDATE tbl_clientinfo set igp=0");
        // $stmt->execute();
        // $stmt->close();

        // $stmt = $this->conn->prepare("UPDATE tbl_clientinfo a INNER JOIN tbl_aging b ON (a.clientno=b.clientno) SET a.igp=b.loanid");
        // $stmt->execute();
        // $stmt->close();

        // $stmt = $this->conn->prepare("SELECT a.CLIENTNAME AS NAME, b.PROGRAM AS NO, b.LOANID AS LOANID FROM tbl_clientinfo a, tbl_loans b WHERE a.CLIENTNO=b.CLIENTNO UNION ALL SELECT CLIENTNAME,CLIENTNO,'-' FROM tbl_clientinfo WHERE igp=0 ORDER BY NAME, CAST(LOANID AS DEC) DESC");
        // $stmt->execute();
        // $result = $stmt->get_result();
        // $SpecialAccts = $this->FillRow($result);
        // $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_natureadjustments ORDER BY NATURE");
        $stmt->execute();
        $result = $stmt->get_result();
        $NatureAdjustment = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "AccountCodes" => $AccountCodes,
            "FundSetup" => $FundSetup,
            "ORNames" => $ORNames,
            "ARNames" => $ARNames,
            "BankSetup" => $BankSetup,
            "SLType" => $SLType,
            "SLName" => $SLName,
            "SpecialAcctCodes" => $SpecialAcctCodes,
            // "SpecialAccts" => $SpecialAccts,
            "CDPList" => $CDPList,
            "NatureAdjustment" => $NatureAdjustment
        ));
    }

    public function LoadSL($data){
        $qry = "";
        if($data["val"] == "CURRENT"){
            $qry = "SELECT CLIENTNO,FULLNAME,PROGRAM,LOANID FROM TBL_AGING ORDER BY FULLNAME,CAST(LOANID AS DEC) DESC";
        }else if($data["val"] == "OLD"){
            $qry = "SELECT CLIENTNO,FULLNAME,PROGRAM,LOANID FROM TBL_AGINGHISTORY ORDER BY FULLNAME,CAST(LOANID AS DEC) DESC";
        }else if($data["val"] == "WRITEOFF"){
            $qry = "SELECT CLIENTNO,FULLNAME,PROGRAM,LOANID FROM TBL_WRITEOFF ORDER BY FULLNAME,CAST(LOANID AS DEC) DESC";
        }else if($data["val"] == "CLIENTINFO"){
            $qry = "SELECT CLIENTNO,CLIENTNAME AS FULLNAME, '-' AS PROGRAM, '-' AS LOANID FROM TBL_CLIENTINFO WHERE IGP = 0 ORDER BY CLIENTNAME";
        }

        //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare($qry);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = $this->FillRow($result);
        echo json_encode($list);
    }

    public function LoadBankSetup(){
        $stmt = $this->conn->prepare("SELECT DISTINCT bank,fund,id,lastcv,slno,nextcheck,type,bankcode FROM tbl_banksetup ORDER BY bank");
        $stmt->execute();
        $result = $stmt->get_result();
        $BankSetup = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "BankSetup" => $BankSetup,
        ));
    }

    public function Save($data){
        //$this->COMPACCTCONNECT();
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

        $jvnodata = $this->SelectFund(array("val" => $data["FUND"]),"RETURN");

        $status = "";
        $message = "";
        $remaining = 0;

        $entries = json_decode($data["DATA"]);
        $values = "";
        $branch = "CAB";
        $prevamount = 0;
        for ($i=0; $i < count($entries); $i++) { 
            $accttitle = (substr($entries[$i][1],0,1) == "&") ? "     ".str_replace("&ensp;","",$entries[$i][1]) : $entries[$i][1];
            
            $sldrcr1 = ($entries[$i][9]=="DEBIT") ? ($entries[$i][4]==0 ? $prevamount : floatval($entries[$i][4])*-1) : ($entries[$i][5]==0 ? $prevamount : $entries[$i][5]);

            $prevamount = ($entries[$i][8] == "YES") ? $sldrcr1 : 0;

            $sldrcr = $entries[$i][3];

            $drother = ($entries[$i][4]=="") ? "0" : $entries[$i][4];
            $crother = ($entries[$i][5]=="") ? "0" : $entries[$i][5];
            $loanid = ($entries[$i][7]=="") ? "-" : $entries[$i][7];
            $slno = ($entries[$i][10]=="") ? "0" : $entries[$i][10];
            $slname = ($entries[$i][11]=="") ? "-" : $entries[$i][11];
            $product = ($entries[$i][12]=="") ? "-" : $entries[$i][12];
            
            $stmt = $this->conn->prepare("SELECT program,product,id FROM tbl_loansetup WHERE product = ?");
            $stmt->bind_param("s",$product);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $program = ($result->num_rows > 0) ? $row["program"] : "-"; 
            $programid = ($result->num_rows > 0) ? "-".$row["id"] : "";
            $product = ($result->num_rows > 0) ? $row["product"] : "-"; 
            $stmt->close();

            $values .= ($i > 0) ? "," : "";
            $values .= "('".$entries[$i]["0"]."','".$data["DATEPREPARED"]."','".$branch."','".$data["FUND"]."','".$jvnodata["JVNO"]."','".$data["VOUCHEREXPLANATION"]."','".$accttitle."','".$entries[$i][2].$programid."','".$sldrcr."','".$sldrcr1."','".$entries[$i][8]."','".$slno."','".$slname."','".$drother."','".$crother."','".$_SESSION["username"]."','".$entries[$i][9]."','".$entries[$i][2]."','".$loanid."','".$data["NATUREADJUSTMENT"]."','".$program."','".$product."')";
        }

        $stmt = $this->conn->prepare("INSERT INTO tbl_journalvouchers(ID, CDate, Branch, Fund, JVNo, Explanation, AcctTitle, AcctNo, SLDrCr, SLDrCr1, SLYesNo, SLNo, SLName, DrOther, CrOther, PreparedBy, CrDr, ClientNo, LoanID, Nature, program, product) VALUES".$values);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        if($result > 0){
            $nextjvno = floatval($data["ACTUALJVNO"]) + 1;
            $stmt = $this->conn->prepare("UPDATE tbl_banksetup SET jvno = ? WHERE fund = ?");
            $stmt->bind_param("ss",$nextjvno,$data["FUND"]);
            $stmt->execute();
            $stmt->close();

            $_SESSION["JVISPRINT"] = "YES";
            $_SESSION["JVNO"] = $data["JVNO"];

            $status = "SUCCESS";
            $message = "JOURNAL VOUCHER HAS BEEN SAVED.";
        }

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
        ));
    }

    public function SelectFund($data,$returntype){
        //$this->COMPACCTCONNECT();
        $finaljvno = "";
        $actualjvno = "";
        $stmt = $this->conn->prepare("SELECT * FROM tbl_banksetup WHERE fund = ? AND bank <> '-'");
        $stmt->bind_param("s",$data["val"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $zeroes = 12 - strlen($row["JVNo"]);
                $counter = 0;
                $jvno = "";
                for ($i=0; $i < $zeroes; $i++) { 
                    $jvno = "0".$jvno;
                }

                $finaljvno = "CAB".$jvno."".$row["JVNo"];
                $actualjvno = $row["JVNo"];
            }
        }

        if($returntype == "RETURN"){
            return array(
                "JVNO" => $finaljvno,
                "ACTUALJVNO" => $actualjvno
            );
        }else{
            echo json_encode(array(
                "JVNO" => $finaljvno,
                "ACTUALJVNO" => $actualjvno
            ));
        }
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