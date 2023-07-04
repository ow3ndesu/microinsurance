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
            "CDPList" => $CDPList
        ));
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
        // //$this->COMPACCTCONNECT();
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $stmt = $this->conn->prepare("SELECT remaining FROM tbl_banksetup WHERE bank = ?");
        $stmt->bind_param("s",$data["BANKNAME"]);
        $stmt->execute();
        $resultbs = $stmt->get_result();
        $banksetup = $resultbs->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT count(*) as forid FROM tbl_cashdisbursement");
        $stmt->execute();
        $result = $stmt->get_result();
        $cd =  $result->fetch_assoc();
        $stmt->close();

        $status = "";
        $message = "";
        $remaining = 0;
        if($resultbs->num_rows > 0){
            if($banksetup["remaining"] != "-"){
                $remaining = floatval($banksetup["remaining"]) - 1;
                
                $entries = json_decode($data["DATA"]);
                $branch = "CAB";
                $values = "";
                $prevamount = 0;
                if(count($entries)){
                    for ($i=0; $i < count($entries) ; $i++) { 
                        $id = floatval($entries[$i][0]) + floatval($cd["forid"]);
                        $accttitle = (substr($entries[$i][3],0,1) == "&") ? "     ".str_replace("&ensp;","",$entries[$i][3]) : $entries[$i][3];
                        $sldrcr1 = ($entries[$i][9]=="CREDIT") ? $entries[$i][5] : ($prevamount==0 ? "-".$entries[$i][4] : $prevamount);
                        $prevamount = ($entries[$i][8] == "YES") ? $sldrcr1 : 0 ;
                        $sldrcr = ($entries[$i][8]=="YES") ? ($entries[$i][2]=="" ? 0 : $entries[$i][2]) : ($entries[$i][2]=="" ? 0 : "-".$entries[$i][2]);
                        $drother = ($entries[$i][4]=="") ? "0" : $entries[$i][4];
                        $crother = ($entries[$i][5]=="") ? "0" : $entries[$i][5];
                        $amtwords = ucwords($f->format($data["CHECKAMOUNT"])) . " Pesos";
                        $loanid = ($entries[$i][7]=="") ? "-" : $entries[$i][7];
                        $slno = ($entries[$i][10]=="") ? "0" : $entries[$i][10];
                        $slname = ($entries[$i][11]=="") ? "-" : $entries[$i][11];
                        $values .= ($i > 0) ? "," : "";
                        $values .= "('".$id."','".$data["CHECKDATE"]."','".$branch."','".$data["PAYEE"]."','".$data["PARTICULARS"]."','".$data["CHECKDATE"]."','".$accttitle."','".$entries[$i][1]."','".$sldrcr1."','".$sldrcr."','".$entries[$i][8]."','".$drother."','".$crother."','".$drother."','".$crother."','".$entries[$i][0]."','".$data["DATEPREPARED"]."','".$data["CHECKAMOUNT"]."','".$amtwords."','".$entries[$i][1]."','".$loanid."','".$data["FUND"]."','".$data["BANKNAME"]."','".$data["BANKCODE"]."','".$data["CVNO"]."','".$data["CHECKNO"]."','".$slno."','".$slname."')";
                    }
                }

                $stmt = $this->conn->prepare("INSERT INTO tbl_othercv(id,cdate,branch,payee,particular,checkdate,accttitle,acctno,sldrcr1,sldrcr,slyesno,drother,crother,sundrydr,sundrycr,entryno,dateprepared,amtothercv,amtwords,clientno,loanid,fund,bank,bankcode,cvno,checkno,slno,slname) VALUES".$values);
                $stmt->execute();
                $result = $stmt->affected_rows;
                $stmt->close();
                if($result > 0){
                    $checkno = floatval($data["CHECKNO"])+1;
                    $stmt = $this->conn->prepare("UPDATE tbl_banksetup SET lastcv = ?, nextcheck = ?, remaining = ? WHERE fund = ? AND bank = ?");
                    $stmt->bind_param("sssss",$data["ActualCVNo"],$checkno,$remaining,$data["FUND"],$data["BANKNAME"]);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION["CVISPRINT"] = "YES";
                    $_SESSION["CVNO"] = $data["CVNO"];

                    $status = "SUCCESS";
                    $message = "CASH DISBURSEMENT HAS BEEN SAVED.";
                }else{
                    $status = "ERROR";
                    $message = "Error Inserting Data to Other CV";
                }
            }else{
                $status = "ERROR";
                $message = "Please Encode New Check Series under " . $data["bank"] . ".";
            }
        }else{
            $status = "ERROR";
            $message = "Please Encode New Check Series under " . $data["bank"] . ".";
        }

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
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