<?php 
include_once("../database/connection.php");
include_once("sanitize.process.php");
include_once("../reports/crb.reports.php");

class Process extends Database 
{
    public function Initialize(){
        // //$this->COMPACCTCONNECT();

        $stmt = $this->conn->prepare("SELECT acctcodes,acctitles,normalbal FROM tbl_accountcodes ORDER BY acctitles");
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

        $stmt = $this->conn->prepare("SELECT DISTINCT bank,fund,id FROM tbl_banksetup ORDER BY bank");
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

        echo json_encode(array(
            "AccountCodes" => $AccountCodes,
            "FundSetup" => $FundSetup,
            "ORNames" => $ORNames,
            "ARNames" => $ARNames,
            "BankSetup" => $BankSetup,
            "SLType" => $SLType,
            "SLName" => $SLName,
            "SpecialAcctCodes" => $SpecialAcctCodes,
            // "SpecialAccts" => $SpecialAccts
        ));
    }

    public function SelectSLType($data){
        // //$this->COMPACCTCONNECT(); 
        $stmt = $this->conn->prepare($data["query"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = $this->FillRow($result);
        $stmt->close();
        echo json_encode($list);
    }

    public function SelectSLName($data){
        // //$this->COMPACCTCONNECT(); 
        $stmt = $this->conn->prepare($data["query"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = $this->FillRow($result);
        $stmt->close();
        echo json_encode($list);
    }

    public function SelectOR($data,$returntype){
        // //$this->COMPACCTCONNECT();
        $message = "";
        $ornofinal = "";
        $orleft = "";
        $orno = "";

        $stmt = $this->conn->prepare("SELECT pending,nextor,orleft,ortype FROM tbl_orseries WHERE name = ?");
        $stmt->bind_param("s",$data["val"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if($data["val"] == "CASHIER"){
            if($result->num_rows > 0){
                if($row["pending"] == "NO"){
                    $branchcode = "CAB";
                    $morno = "";
                    $zeroes = 12 - strlen($row["nextor"]);
                    for ($i=0; $i < $zeroes; $i++) { 
                        $morno = "0".$morno;
                    }

                    $orno = $row["nextor"];
                    $ornotxt = $branchcode . "" . $morno . "" . $orno;
                    $ornofinal = $ornotxt;

                    $orleft = $row["orleft"];
                    $ortype = $row["ortype"];
                    $yearnow = date("Y");

                    $stmt = $this->conn->prepare("SELECT DISTINCT orno, ortype FROM tbl_cashreceipt USE INDEX(forORNo) WHERE orno = ? AND ortype = ? AND SUBSTRING(datepaid,7,4) = ?");
                    $stmt->bind_param("sss",$orno,$ortype,$yearnow);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $stmt->close();

                    if($result->num_rows > 0){
                        $message = "OR Number already used. Try again";
                        $row = $result->fetch_assoc();

                        $adjust = floatval($orno) + 1;
                        $left = floatval($orleft) - 1;

                        $stmt = $this->conn->prepare("UPDATE tbl_orseries SET nextor = ?, orleft = ? WHERE name = 'CASHIER'");
                        $stmt->bind_param("ss",$adjust,$left);
                        $stmt->execute();
                        $result = $stmt->affected_rows;
                        $stmt->close();
                    }
                }else{
                    $message = "PENDING";
                }
            }else{
                $message = "No ORs Set for CASHIER. Please configure OR Settings";
                $stmt = $this->conn->prepare("UPDATE tbl_orseries SET pending='NO' WHERE name = 'CASHIER'");
                $stmt->execute();
                $stmt->close();
            }
        }else{
            if($result->num_rows > 0){
                $orno = $row["nextor"];
                $orleft = $row["orleft"];
                $ortype = $row["ortype"];
                $yearnow = date("Y");
                $ornofinal = $orno;

                $stmt = $this->conn->prepare("SELECT DISTINCT orno, ortype FROM tbl_cashreceipt USE INDEX(forORNo) WHERE orno = ? AND ortype = ? AND SUBSTRING(datepaid,7,4) = ?");
                $stmt->bind_param("sss",$orno,$ortype,$yearnow);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

                if($result->num_rows > 0){
                    $message = "OR Number already used.";
                }
            }else{
                $message = "NO ORs set for this PO. Please configure OR Settings";
            }
        }

        if($returntype == "RETURN"){
            return array(
                "ORNO" => $ornofinal,
                "ORLEFT" => $orleft,
                "ACTUALOR" => $orno,
                "MESSAGE" => $message
            );
        }else{
            echo json_encode(array(
                "ORNO" => $ornofinal,
                "ORLEFT" => $orleft,
                "ACTUALOR" => $orno,
                "MESSAGE" => $message
            ));
        }
    }

    public function SelectAR($data,$returntype){
        // //$this->COMPACCTCONNECT();
        $arno = "";
        $arleft = "";
        $message = "";

        $stmt = $this->conn->prepare("SELECT nextar, arleft FROM tbl_arseries WHERE name = ?");
        $stmt->bind_param("s",$data["val"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();

            $arno = $row["nextar"];
            $arleft = $row["arleft"];

            $stmt = $this->conn->prepare("SELECT orno FROM tbl_cashreceipt USE INDEX(forORNo) WHERE orno = ? AND ortype = 'AR' AND SUBSTRING(datepaid,7,4) = ?");
            $stmt->bind_param("ss",$arno,$yearnow);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if($result->num_rows > 0){
                $message = "AR Number already used.";
            }
        }

        if($returntype == "RETURN"){
            return array(
                "ARNO" => $arno,
                "ARLEFT" => $arleft,
                "MESSAGE" => $message
            );
        }else{
            echo json_encode(array(
                "ARNO" => $arno,
                "ARLEFT" => $arleft,
                "MESSAGE" => $message
            ));
        }
    }

    public function SaveEntry($data){
        $bookpage = $this->GetBookPage("CRB",$data["fund"],$data["date"]);
        $entry = $data["data"];
        $query = "";
        $status = "";
        $orno = "";
        $orleft = "";
        $isprint = "NO";

        if(filter_var($data["archecked"], FILTER_VALIDATE_BOOLEAN) == true){
            $ardata = $this->SelectAR(array("val" => $data["orfrom"]),"RETURN");
            $this->AdjustARNO($ardata["ARNO"],$ardata["ARLEFT"],$data["orfrom"]);
            $orno = $ardata["ARNO"];
            $orleft = $ardata["ARLEFT"];
        }else{
            $ordata = $this->SelectOR(array("val" => $data["orfrom"]),"RETURN");
            $this->AdjustORNO($ordata["ACTUALOR"],$ordata["ORLEFT"],$data["orfrom"]);
            $orno = $ordata["ORNO"];
            $orleft = $ordata["ORLEFT"];
        }

        for ($i=0; $i < count($entry); $i++) { 

            $accttitle = (substr($entry[$i][0],0,1) == "&") ? "     ".str_replace("&ensp;","",$entry[$i][0]) : $entry[$i][0];
            $acctno = ($entry[$i][1] == "") ? "" : $entry[$i][1];
            $sldrcr = ($entry[$i][2] == "") ? 0 : $entry[$i][2];
            $dr = ($entry[$i][3] == "") ? 0 : $entry[$i][3];
            $cr = ($entry[$i][4] == "") ? 0 : $entry[$i][4];
            $glno = ($entry[$i][5] == "") ? "-" : $entry[$i][5];
            $loanid = ($entry[$i][6] == "") ? "-" : $entry[$i][6];

            if(substr($accttitle,0,1) != " "){
                if($entry[$i][0] == "CASH ON HAND"){
                    $query = "INSERT INTO tbl_cashreceipttest(CLIENTNAME,DATEPAID,ORNO,FUND,USER,TYPE,ACCOUNTTITLE,GLNO,SLDRCR,SUNDRYDR,SUNDRYCR,CHECKNO,BANKNAME,BANKBRANCH,STATUS,CATEGORY,TOTALPAID,BRANCH,EXPLANATION,ORTYPE,CRBPAGE,BANK,ORSERIESNAME) VALUES('".$data["name"]."','".$data["date"]."','".$orno."','".$data["fund"]."','".$_SESSION["username"]."','".$data["type"]."','".$accttitle."','".$acctno."','".$sldrcr."','0','".$cr."','".$data["checkno"]."','".$data["bankname"]."','".$data["bankbranch"]."','UNDEPOSITED','OTHER','".$dr."','','".$data["part"]."','".$data["ortype"]."','".$bookpage."','".$data["bank"]."','".$data["orfrom"]."')";
                }else{
                    $query = "INSERT INTO tbl_cashreceipttest(CLIENTNAME,DATEPAID,ORNO,FUND,USER,TYPE,ACCOUNTTITLE,GLNO,SLDRCR,SUNDRYDR,SUNDRYCR,CHECKNO,BANKNAME,BANKBRANCH,STATUS,CATEGORY,BRANCH,EXPLANATION,ORTYPE,CRBPAGE,BANK,ORSERIESNAME) VALUES('".$data["name"]."','".$data["date"]."','".$orno."','".$data["fund"]."','".$_SESSION["username"]."','".$data["type"]."','".$accttitle."','".$acctno."','".$sldrcr."','0','".$cr."','".$data["checkno"]."','".$data["bankname"]."','".$data["bankbranch"]."','UNDEPOSITED','OTHER','','".$data["part"]."','".$data["ortype"]."','".$bookpage."','".$data["bank"]."','".$data["orfrom"]."')";
                }
            }else{
                $query = "INSERT INTO tbl_cashreceipttest(CLIENTNAME,DATEPAID,ORNO,FUND,USER,TYPE,ACCOUNTTITLE,SLNO,SLDRCR,SUNDRYDR,SUNDRYCR,CHECKNO,BANKNAME,BANKBRANCH,STATUS,CATEGORY,BRANCH,EXPLANATION,ORTYPE,CRBPAGE,BANK,GLNO,LOANID,ORSERIESNAME) VALUES('".$data["name"]."','".$data["date"]."','".$orno."','".$data["fund"]."','".$_SESSION["username"]."','".$data["type"]."','".$accttitle."','".$acctno."','".$sldrcr."','".$dr."','".$cr."','".$data["checkno"]."','".$data["bankname"]."','".$data["bankbranch"]."','UNDEPOSITED','OTHER','','".$data["part"]."','".$data["ortype"]."','".$bookpage."','".$data["bank"]."','".$glno."','".$loanid."','".$data["orfrom"]."')";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stmt->close();
        }

        // if(filter_var($data["archecked"], FILTER_VALIDATE_BOOLEAN) == true){
        //     $this->AdjustARNO($data["orno"],$data["orleft"],$data["orfrom"]);
        // }else{
        //     $this->AdjustORNO($data["actualorno"],$data["orleft"],$data["orfrom"]);
        // }
        

        if($data["orfrom"] == "CASHIER"){
            $_SESSION["orno"] = $orno;
            $_SESSION["ordate"] = $data["date"];
            $_SESSION["fund"] = $data["fund"];
            $status = "SUCCESS";
            $isprint = "YES";
        }

        if(filter_var($data["issac"], FILTER_VALIDATE_BOOLEAN)){
            $this->SaveToSPUpdate($orno,$data["issac"]);
        }else{
            $this->SaveToPartialUpdate($orno);
        }

        echo json_encode(array(
            "STATUS" => $status,
            "ISPRINT" => $isprint
        ));
    }

    private function GetBookPage($type,$fund,$date){
        // //$this->COMPACCTCONNECT();
        $lastbookpage = "";
        $newbookpage = "";
        if($type == "CRB"){
            $stmt = $this->conn->prepare("SELECT DISTINCT FUND,DATEPAID,CRBPAGE FROM TBL_CASHRECEIPT USE INDEX(forCRBPage) WHERE FUND=? AND CRBPAGE <> '-' ORDER BY CAST(REPLACE(CRBPAGE,'".$type."-','') AS UNSIGNED) DESC LIMIT 1");
            $stmt->bind_param("s",$fund);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if($result->num_rows > 0){
                while ($row = $result->fetch_assoc()) {
                    $lastbookpage = trim(str_replace($type."-","",$row["CRBPAGE"]));
                    $ddate = date("m/d/Y",strtotime($row["DATEPAID"]));
                    $newbookpage = ($ddate == $date) ? $type."-".$lastbookpage : $type."-".(floatval($lastbookpage) + 1);
                }
            }else{
                $newbookpage = $type."-1";
            }
        }else if($type == "CDB"){
            $stmt = $this->conn->prepare("SELECT DISTINCT FUND,DATEPREPARED,CDBDAYPAGE FROM TBL_CASHDISBURSEMENT USE INDEX(forCDBPage) WHERE DATEPREPARED <> '-' AND DATEPREPARED <> '  /  /    ' AND FUND=? AND CDBDAYPAGE <> '-' ORDER BY CAST(REPLACE(CDBDAYPAGE,'".$type."-','') AS UNSIGNED) DESC LIMIT 1");
            $stmt->bind_param("s",$fund);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if($result->num_rows > 0){
                while ($row = $result->fetch_assoc()) {
                    $lastbookpage = trim(str_replace($type."-","",$row["CDBDAYPAGE"]));
                    $ddate = date("m/d/Y",strtotime($row["DATEPREPARED"]));
                    $newbookpage = ($ddate == $date) ? $type."-".$lastbookpage : $type."-".(floatval($lastbookpage) + 1);
                }
            }else{
                $newbookpage = $type."-1";
            }
        }else if($type == "GJ"){
            $stmt = $this->conn->prepare("SELECT DISTINCT FUND,CDATE,GJDAYPAGE FROM TBL_JOURNALVOUCHERS USE INDEX(forGJPage) WHERE FUND=? AND GJDAYPAGE <> '-' ORDER BY CAST(REPLACE(GJDAYPAGE,'".$type."-','') AS UNSIGNED) DESC LIMIT 1");
            $stmt->bind_param("s",$fund);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if($result->num_rows > 0){
                while ($row = $result->fetch_assoc()) {
                    $lastbookpage = trim(str_replace($type."-","",$row["GJDAYPAGE"]));
                    $ddate = date("m/d/Y",strtotime($row["CDATE"]));
                    $newbookpage = ($ddate == $date) ? $type."-".$lastbookpage : $type."-".(floatval($lastbookpage) + 1);
                }
            }else{
                $newbookpage = $type."-1";
            }
        }

        return $newbookpage;
    }

    private function AdjustORNO($orno,$orleft,$orfrom){
        $orno = floatval($orno) + 1;
        $orleft = floatval($orleft) - 1;

        $stmt = $this->conn->prepare("UPDATE TBL_ORSERIES SET NEXTOR=?,ORLEFT=? WHERE NAME=?");
        $stmt->bind_param("sss",$orno,$orleft,$orfrom);
        $stmt->execute();
        $stmt->close();
    }

    private function AdjustARNO($arno,$arleft,$arfrom){
        $arno = floatval($arno) + 1;
        $arleft = floatval($arleft) - 1;

        $stmt = $this->conn->prepare("UPDATE TBL_ARSERIES SET NEXTAR=?,ARLEFT=? WHERE NAME=?");
        $stmt->bind_param("sss",$arno,$arleft,$arfrom);
        $stmt->execute();
        $stmt->close();
    }

    private function SaveToSPUpdate($orno,$issac){
        // //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("SELECT * FROM tbl_specialacctcodes");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $accountcode = $row["AccountCode"];
            $orno = $orno;
            $currdate = date("m/d/Y");

            $stmt = $this->conn->prepare("SELECT SLNO,LOANID FROM TBL_CASHRECEIPT WHERE ORNO=? AND DATEPAID=? AND SLNO <> '-' AND GLNO=?");
            $stmt->bind_param("sss",$orno,$currdate,$accountcode);
            $stmt->execute();
            $result1 = $stmt->get_result();
            $stmt->close();

            while ($row1 = $result1->fetch_assoc()) {
                $stmt = $this->conn->prepare("INSERT INTO tbl_updatedspaccounts(ClientNo,LoanID,AccountCode) VALUES(?,?,?)");
                $stmt->bind_param("sss",$row1["SLNO"],$row1["LOANID"], $accountcode);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    private function SaveToPartialUpdate($orno){
        // //$this->COMPACCTCONNECT();
        $currdate = date("m/d/Y");
        $stmt = $this->conn->prepare("INSERT INTO tbl_updatedaccounts(ClientNo,LoanID) SELECT SLNo,LoanID FROM tbl_cashreceipt WHERE orno = ? AND DatePaid = ? AND SLNo <> '-'");
        $stmt->bind_param("ss",$orno,$currdate);
        $stmt->execute();
        $stmt->close();
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