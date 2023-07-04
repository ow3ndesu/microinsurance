<?php
class PredefinedProcess extends Database {
    public function GJEntrySupplier($purchaseno,$receivingno){
        date_default_timezone_set("Asia/Singapore");
        $cdate = date("m/d/Y");

        $stmt = $this->conn->prepare("INSERT INTO tbl_jvpending(cdate,type,purchaseno,receivingno,supplierno,amount,openbalance) SELECT podate,'SUPPLIER',purchaseno,receivingno,supplierno,totalprice,supplieropenbalance FROM tbl_purchase_received WHERE purchaseno = ? AND receivingno = ?");
        $stmt->bind_param("ss",$purchaseno,$receivingno);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        
        $status = ($result > 0) ? "SUCCESS" : "ERROR";
        return $status;
    }

    public function GJEntryCustomer($transactionno,$sinumber,$drno){
        date_default_timezone_set("Asia/Singapore");
        $cdate = date("m/d/Y");

        $stmt = $this->conn->prepare("INSERT INTO tbl_jvpending(cdate,type,transactionno,sino,drno,customerno,amount) SELECT transactiondate,'CUSTOMER',transactionno,sinumber,drno,customerno,totalamount FROM tbl_pos_receipts WHERE transactionno = ? AND sinumber = ? AND drno = ? GROUP BY transactionno,sinumber,drno");
        $stmt->bind_param("sss",$transactionno,$sinumber,$drno);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_pos_receipts WHERE transactionno = ? AND sinumber = ? AND drno = ?");
        $stmt->bind_param("sss",$transactionno,$sinumber,$drno);
        $stmt->execute();
        $posres = $stmt->get_result();
        $posrow = $posres->fetch_assoc();
        $stmt->close();

        if($posrow["paymentmethod"] == "RECEIVABLE"){

            $stmt = $this->conn->prepare("SELECT loanid FROM tbl_loans WHERE customerno = ? ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("s",$posrow["customerno"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $loanid = 1;
            while ($row = $result->fetch_assoc()) {
                $loanid = floatval($loanid) + floatval($row["loanid"]);
            }
            $stmt->close();

            $stmt = $this->conn->prepare("SELECT * FROM tbl_customers WHERE customerno = ?");
            $stmt->bind_param("s",$posrow["customerno"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $customername = $row["businessname"];
            $customercontactno = $row["mobileno1"];
            $customeraddress = $row["businessstreet"] . " " . $row["businessbarangay"] . ", " . $row["businesstown"] . ", " . $row["businessprovince"];
            $stmt->close();

            $query = "INSERT INTO tbl_loans(customerno, loanid, transactiondate, transactionno, drno, sino, terms, mode, name, contactno, address, totalamount) VALUES('".$posrow["customerno"]."','".$loanid."','".$posrow["transactiondate"]."','".$posrow["transactionno"]."','".$posrow["drno"]."','".$posrow["sinumber"]."','".$posrow["terms"]."','".$posrow["modeofpayment"]."','".$customername."','".$customercontactno."','".$customeraddress."','".$posrow["totalamount"]."')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->affected_rows;
            $stmt->close();

            $query = "INSERT INTO tbl_aging(customerno, loanid, transactiondate, transactionno, drno, sino, terms, mode, name, contactno, address, totalamount) VALUES('".$posrow["customerno"]."','".$loanid."','".$posrow["transactiondate"]."','".$posrow["transactionno"]."','".$posrow["drno"]."','".$posrow["sinumber"]."','".$posrow["terms"]."','".$posrow["modeofpayment"]."','".$customername."','".$customercontactno."','".$customeraddress."','".$posrow["totalamount"]."')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->affected_rows;
            $stmt->close();
        }
        
        $status = ($result > 0) ? "SUCCESS" : "ERROR";
        return $status;
    }

    public function LoadGJ(){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_jvpending WHERE status = 'PENDING'");
        $stmt->execute();
        $result = $stmt->get_result();
        $gjentry = $this->FillRow($result);
        $stmt->close();

        echo json_encode($gjentry);
    }

    public function ViewGJEntry($data){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_jvpending WHERE id = ?");
        $stmt->bind_param("s",$data["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $JV = $result->fetch_assoc();
        $stmt->close();

        $arr = [];

        $query1 = "";
        $query2 = "";
        $query3 = "";
        if($JV["type"] == "SUPPLIER"){
            $query1 = "SELECT * FROM tbl_suppliers WHERE supplierno = '".$JV["supplierno"]."'";
            $query2 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '21310'";
            $query3 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '41210'";
        }else{
            $query1 = "SELECT * FROM tbl_customers WHERE customerno = '".$JV["customerno"]."'";
            $query2 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '11500'";
            $query3 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '41100'";
        }
        
        $stmt = $this->conn->prepare($query1);
        $stmt->execute();
        $result = $stmt->get_result();
        $RES1 = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare($query2);
        $stmt->execute();
        $result = $stmt->get_result();
        $RES2 = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare($query3);
        $stmt->execute();
        $result = $stmt->get_result();
        $RES3 = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "JV" => $JV,
            "RES1" => $RES1,
            "RES2" => $RES2,
            "RES3" => $RES3
        ));
    }

    public function ConfirmEntryGJ($data){
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

        $status = "";
        $message = "";
        $id = $data["id"];

        $stmt = $this->conn->prepare("SELECT * FROM tbl_jvpending WHERE id = ?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $JV = $result->fetch_assoc();
        $stmt->close();

        $query1 = "";
        $query2 = "";
        $query3 = "";
        if($JV["type"] == "SUPPLIER"){
            $query1 = "SELECT * FROM tbl_suppliers WHERE supplierno = '".$JV["supplierno"]."'";
            $query2 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '21310'";
            $query3 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '41210'";
        }else{
            $query1 = "SELECT * FROM tbl_customers WHERE customerno = '".$JV["customerno"]."'";
            $query2 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '11500'";
            $query3 = "SELECT * FROM tbl_accountcodes WHERE acctcodes = '41100'";
        }

        $stmt = $this->conn->prepare($query1);
        $stmt->execute();
        $result = $stmt->get_result();
        $RES1 = $result->fetch_assoc();
        $stmt->close();

        // $this->AMSCONNECT();
        $stmt = $this->conn->prepare($query2);
        $stmt->execute();
        $result = $stmt->get_result();
        $RES2 = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare($query3);
        $stmt->execute();
        $result = $stmt->get_result();
        $RES3 = $result->fetch_assoc();
        $stmt->close();

        $loanid = "-";
        $stmt = $this->conn->prepare("SELECT * FROM tbl_loans WHERE transactionno = ? AND drno = ? AND sino = ?");
        $stmt->bind_param("sss",$JV["transactionno"],$JV["drno"],$JV["sino"]);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $loanid = $row["loanid"];
        }
        $stmt->close();
       
        $CDate = date("m/d/Y");
        $Branch = "CAB";
        $Fund = "IRESCUE";
        $JVNo = $this->GenerateJVNO($Fund);
        $slacctitle = ($JV["type"]=="SUPPLIER") ? "     ".$RES1["businessname"] : "     ".$RES1["fullname"];
        $slacctcode = ($JV["type"]=="SUPPLIER") ? $RES1["supplierno"] : $RES1["customerno"];
        $preparedby = strtoupper($_SESSION["username"]);
        $amount = $JV["amount"];

        $query = "";
        if($JV["type"] == "SUPPLIER"){
            $query = "INSERT INTO tbl_journalvouchers(ID, CDate, Branch, Fund, JVNo, AcctTitle, AcctNo, SLDrCr, SLDrCr1, SLYesNo, SLNo, SLName, DrOther, CrOther, PreparedBy, CrDr, ClientNo, LoanID, Nature) VALUES('1','".$CDate."','".$Branch."','".$Fund."','".$JVNo["JVNO"]."','".$RES3["acctitles"]."','".$RES3["acctcodes"]."','0','".floatval($amount*-1)."','".$RES3["sl"]."','0','-','".$amount."','0','".$preparedby."','".$RES3["normalbal"]."','".$RES3["acctcodes"]."','".$loanid."','TO RECORD ACCOUNTS PAYABLE OF ".$slacctitle."'),('2','".$CDate."','".$Branch."','".$Fund."','".$JVNo["JVNO"]."','".$RES2["acctitles"]."','".$RES2["acctcodes"]."','0','".$amount."','".$RES2["sl"]."','0','-','0','".$amount."','".$preparedby."','".$RES2["normalbal"]."','".$RES2["acctcodes"]."','".$loanid."','TO RECORD ACCOUNTS PAYABLE OF ".$slacctitle."'),('3','".$CDate."','".$Branch."','".$Fund."','".$JVNo["JVNO"]."','".$slacctitle."','".$slacctcode."','".$amount."','".floatval($amount*-1)."','".$RES2["sl"]."','".$RES2["acctcodes"]."','SUPPLIER','0','0','".$preparedby."','".$RES2["normalbal"]."','".$slacctcode."','".$loanid."','TO RECORD ACCOUNTS PAYABLE OF ".$slacctitle."')";
        }else{
            $query = "INSERT INTO tbl_journalvouchers(ID, CDate, Branch, Fund, JVNo, AcctTitle, AcctNo, SLDrCr, SLDrCr1, SLYesNo, SLNo, SLName, DrOther, CrOther, PreparedBy, CrDr, ClientNo, LoanID, Nature) VALUES('1','".$CDate."','".$Branch."','".$Fund."','".$JVNo["JVNO"]."','".$RES2["acctitles"]."','".$RES2["acctcodes"]."','0','".floatval($amount*-1)."','".$RES2["sl"]."','0','-','".$amount."','0','".$preparedby."','".$RES2["normalbal"]."','".$RES2["acctcodes"]."','".$loanid."','TO RECORD ACCOUNTS PAYABLE OF ".$slacctitle."'),('2','".$CDate."','".$Branch."','".$Fund."','".$JVNo["JVNO"]."','".$slacctitle."','".$slacctcode."','".$amount."','".floatval($amount*-1)."','".$RES3["sl"]."','".$RES3["acctcodes"]."','SUPPLIER','0','0','".$preparedby."','".$RES3["normalbal"]."','".$slacctcode."','".$loanid."','TO RECORD ACCOUNTS PAYABLE OF ".$slacctitle."'),('3','".$CDate."','".$Branch."','".$Fund."','".$JVNo["JVNO"]."','".$RES3["acctitles"]."','".$RES3["acctcodes"]."','0','".$amount."','".$RES3["sl"]."','0','-','0','".$amount."','".$preparedby."','".$RES3["normalbal"]."','".$RES3["acctcodes"]."','-','TO RECORD ACCOUNTS PAYABLE OF ".$slacctitle."')";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        if($result > 0){
            $nextjvno = floatval($JVNo["ACTUALJVNO"]) + 1;
            $stmt = $this->conn->prepare("UPDATE tbl_banksetup SET jvno = ? WHERE fund = ?");
            $stmt->bind_param("ss",$nextjvno,$Fund);
            $stmt->execute();
            $stmt->close();

            $this->__construct();
            $stmt = $this->conn->prepare("UPDATE tbl_jvpending SET status = 'DONE', jvno = ? WHERE id = ?");
            $stmt->bind_param("ss",$JVNo["JVNO"],$id);
            $stmt->execute();
            $stmt->close();

            $jvquery = "";
            if($JV["type"] == "SUPPLIER"){
                $jvquery = "INSERT INTO tbl_cdpending(cdate, purchaseno, receivingno, supplierno, amount, openbalance, loanid) VALUES('".$JV["cdate"]."','".$JV["purchaseno"]."','".$JV["receivingno"]."','".$JV["supplierno"]."','".$JV["amount"]."','".$JV["openbalance"]."','".$loanid."')";
            }else if($JV["type"] == "CUSTOMER"){
                $jvquery = "INSERT INTO tbl_crbpending(cdate, purchaseno, receivingno, customerno, amount, openbalance, loanid) VALUES('".$JV["cdate"]."','".$JV["purchaseno"]."','".$JV["receivingno"]."','".$JV["customerno"]."','".$JV["amount"]."','".$JV["openbalance"]."','".$loanid."')";
            }
            
            $stmt = $this->conn->prepare($jvquery);
            $stmt->execute();
            $stmt->close();

            $status = "SUCCESS";
            $message = "JOURNAL VOUCHER HAS BEEN SAVED.";
        }

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
        ));
    }

    public function CRBEntryRaw($customerno,$amount){
        date_default_timezone_set("Asia/Singapore");
        $cdate = date("m/d/Y");
        $stmt = $this->conn->prepare("INSERT INTO tbl_jvpending(cdate, supplierno, amount) VALUES(?,?,?)");
        $stmt->bind_param("sss",$cdate,$supplierno,$amount);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        
        $status = ($result > 0) ? "SUCCESS" : "ERROR";
        return array("STATUS" => $status);
    }

    public function LoadCRB(){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_crbpending WHERE status = 'PENDING'");
        $stmt->execute();
        $result = $stmt->get_result();
        $crbentry = $this->FillRow($result);
        $stmt->close();

        echo json_encode($crbentry);
    }

    public function ViewCRBEntry($data){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_crbpending WHERE id = ?");
        $stmt->bind_param("s",$data["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $CRB = $result->fetch_assoc();
        $stmt->close();
        
        $stmt = $this->conn->prepare("SELECT * FROM tbl_customers WHERE customerno = ?");
        $stmt->bind_param("s",$CRB["customerno"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $SL = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '11500'");
        $stmt->execute();
        $result = $stmt->get_result();
        $AR = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '11130'");
        $stmt->execute();
        $result = $stmt->get_result();
        $CIB = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "CRB" => $CRB,
            "SL" => $SL,
            "AR" => $AR,
            "CIB" => $CIB
        ));
    }

    public function ConfirmEntryCRB($data){
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

        $status = "";
        $message = "";

        $stmt = $this->conn->prepare("SELECT * FROM tbl_crbpending WHERE id = ?");
        $stmt->bind_param("s",$data["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $CRB = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_customers WHERE customerno = ?");
        $stmt->bind_param("s",$CRB["customerno"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $SL = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '11500'");
        $stmt->execute();
        $result = $stmt->get_result();
        $AR = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '11130'");
        $stmt->execute();
        $result = $stmt->get_result();
        $CIB = $result->fetch_assoc();
        $stmt->close();
       
        $entries = json_decode($data["Data"]);
        $CDate = date("m/d/Y");
        $Branch = "CAB";
        $Fund = "LBP";
        $Type = "CASH";
        $ORType = "OR";
        $Bank = "ABC-0471106806 ASKI-GF";
        $ORName = "CASHIER";
        $ORNo = $this->GenerateORNO($ORName);
        $CRBPage = $this->GetBookPage("CRB",$Fund,$CDate);
        $slacctitle = "     ".$SL["fullname"];
        $slacctcode = $SL["customerno"];
        
        $preparedby = strtoupper($_SESSION["username"]);

        $totalslamount = 0;
        $entryquery = "";
        for ($i=0; $i < count($entries); $i++) { 
            $accttitle = (substr($entries[$i][0],0,1) == "&") ? "     ".str_replace("&ensp;","",$entries[$i][0]) : $entries[$i][0];
            $amount = str_replace(',','',$entries[$i][2]);
            $entryquery .= ",('".$SL["fullname"]."','".$CDate."','".$ORNo["ORNO"]."','".$Fund."','".$preparedby."','".$Type."','".$accttitle."','".$entries[$i][1]."','".$amount."','0','0','-','-','-','UNDEPOSITED','OTHER','".$Branch."','TO RECORD ACCOUNTS RECEIVABLE OF ".$slacctitle."','".$ORType."','".$CRBPage."','".$Bank."','".$CIB["acctcodes"]."','-','".$ORName."')";

            $totalslamount = floatval($totalslamount) + floatval(abs($amount));
        }

        $amount = floatval($totalslamount);
        $query = "INSERT INTO tbl_cashreceipt(CLIENTNAME,DATEPAID,ORNO,FUND,USER,TYPE,ACCOUNTTITLE,SLNO,SLDRCR,SUNDRYDR,SUNDRYCR,CHECKNO,BANKNAME,BANKBRANCH,STATUS,CATEGORY,BRANCH,EXPLANATION,ORTYPE,CRBPAGE,BANK,GLNO,LOANID,ORSERIESNAME) VALUES('".$SL["fullname"]."','".$CDate."','".$ORNo["ORNO"]."','".$Fund."','".$preparedby."','".$Type."','".$AR["acctitles"]."','-','0','0','".floatval($amount)."','-','-','-','UNDEPOSITED','OTHER','".$Branch."','TO RECORD ACCOUNTS RECEIVABLE OF ".$slacctitle."','".$ORType."','".$CRBPage."','".$Bank."','".$AR["acctcodes"]."','".$CRB["loanid"]."','".$ORName."'),('".$SL["fullname"]."','".$CDate."','".$ORNo["ACTUALOR"]."','".$Fund."','".$preparedby."','".$Type."','".$slacctitle."','".$slacctcode."','".(floatval($amount)*-1)."','0','0','-','-','-','UNDEPOSITED','OTHER','".$Branch."','TO RECORD ACCOUNTS RECEIVABLE OF ".$slacctitle."','".$ORType."','".$CRBPage."','".$Bank."','".$AR["acctcodes"]."','".$CRB["loanid"]."','".$ORName."'),('".$SL["fullname"]."','".$CDate."','".$ORNo["ORNO"]."','".$Fund."','".$preparedby."','".$Type."','".$CIB["acctitles"]."','-','0','".floatval($amount)."','0','-','-','-','UNDEPOSITED','OTHER','".$Branch."','TO RECORD ACCOUNTS RECEIVABLE OF ".$slacctitle."','".$ORType."','".$CRBPage."','".$Bank."','".$CIB["acctcodes"]."','-','".$ORName."')";
        $query .= $entryquery;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        if($result > 0){
            $this->AdjustORNO($ORNo["ACTUALOR"],$ORNo["ORLEFT"],$ORName);

            $this->__construct();
            $crbstatus = "";
            $crbamount = 0;
            $crbstatus = (floatval($CRB["amount"]) > floatval($totalslamount)) ? "PENDING" : "DONE";
            $crbamount = floatval($CRB["amount"]) - floatval($totalslamount);
            $stmt = $this->conn->prepare("UPDATE tbl_crbpending SET status = ?, amount = ?, orno = ? WHERE id = ?");
            $stmt->bind_param("ssss",$crbstatus,$crbamount,$ORNo["ORNO"],$data["id"]);
            $stmt->execute();
            $stmt->close();

            $status = "SUCCESS";
            $message = "CASH RECEIPTS HAS BEEN SAVED.";
        }

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
        ));
    }

    public function LoadCD(){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_cdpending WHERE status = 'PENDING'");
        $stmt->execute();
        $result = $stmt->get_result();
        $cdentry = $this->FillRow($result);
        $stmt->close();

        echo json_encode($cdentry);
    }

    public function ViewCDEntry($data){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_cdpending WHERE id = ?");
        $stmt->bind_param("s",$data["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $CD = $result->fetch_assoc();
        $stmt->close();
        
        $stmt = $this->conn->prepare("SELECT * FROM tbl_suppliers WHERE supplierno = ?");
        $stmt->bind_param("s",$CD["supplierno"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $SL = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '21310'");
        $stmt->execute();
        $result = $stmt->get_result();
        $AP = $this->FillRow($result);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '11130'");
        $stmt->execute();
        $result = $stmt->get_result();
        $CIB = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "CD" => $CD,
            "SL" => $SL,
            "AP" => $AP,
            "CIB" => $CIB
        ));
    }

    public function ConfirmEntryCD($data){
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

        $stmt = $this->conn->prepare("SELECT * FROM tbl_cdpending WHERE id = ?");
        $stmt->bind_param("s",$data["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $CD = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_suppliers WHERE supplierno = ?");
        $stmt->bind_param("s",$CD["supplierno"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $SL = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '21310'");
        $stmt->execute();
        $result = $stmt->get_result();
        $AP = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '11130'");
        $stmt->execute();
        $result = $stmt->get_result();
        $CIB = $result->fetch_assoc();
        $stmt->close();

        // //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("SELECT count(*) as count FROM tbl_cashdisbursement");
        $stmt->execute();
        $result = $stmt->get_result();
        $ForID =  $result->fetch_assoc();
        $stmt->close();

        $entries = json_decode($data["Data"]);
        $CDate = date("m/d/Y");
        $Branch = "CAB";
        $Payee = $SL["businessname"];
        $Particulars = "THIS IS PREDEFINED PARTICULARS";
        $Fund = "IRESCUE";
        $Bank = "CBS-34120000996-8-1";
        $BankCode = "10017";
        $CheckNo = "400419";

        $CVNo = $this->GenerateCVNO($Bank);
        $slacctitle = "     ".$SL["businessname"];
        $slacctcode = $SL["supplierno"];
        $preparedby = strtoupper($_SESSION["username"]);

        $totalslamount = 0;
        for ($x=0; $x < count($entries); $x++) { 
            $amount = str_replace(',','',$entries[$x][2]);
            $totalslamount = floatval($totalslamount) + floatval(abs($amount));
        }

        $amount = $totalslamount;
        $amtwords = ucwords($f->format(floatval($amount))).' Pesos';
        $forid = floatval($ForID["count"]) + 3;
        $entryquery = "";

        for ($i=0; $i < count($entries); $i++) { 
            $accttitle = (substr($entries[$i][0],0,1) == "&") ? "     ".str_replace("&ensp;","",$entries[$i][0]) : $entries[$i][0];
            $amount1 = str_replace(',','',$entries[$i][2]);
            $entryquery .= ",('".(floatval($forid)+($i+1))."','".$CDate."','".$Branch."','".$Payee."','".$Particulars."','".$CDate."','".$accttitle."','".$entries[$i][1]."','".(floatval($amount1)*-1)."','".floatval($amount1)."','".$CIB["sl"]."','0','0','0','0','".floatval($i + 1 + 3)."','".$CDate."','".floatval($amount)."','".$amtwords."','".$entries[$i][1]."','-','".$Fund."','".$Bank."','".$BankCode."','".$CVNo["CVNO"]."','".$CheckNo."','0','".$CIB["slname"]."')";
        }

        $query = "INSERT INTO tbl_othercv(id,cdate,branch,payee,particular,checkdate,accttitle,acctno,sldrcr1,sldrcr,slyesno,drother,crother,sundrydr,sundrycr,entryno,dateprepared,amtothercv,amtwords,clientno,loanid,fund,bank,bankcode,cvno,checkno,slno,slname) VALUES

        ('".(floatval($ForID["count"])+1)."','".$CDate."','".$Branch."','".$Payee."','".$Particulars."','".$CDate."','".$AP["acctitles"]."','".$AP["acctcodes"]."','".(floatval($amount)*-1)."','0','".$AP["sl"]."','".floatval($amount)."','0','".floatval($amount)."','0','1','".$CDate."','".floatval($amount)."','".$amtwords."','".$AP["acctcodes"]."','".$CD["loanid"]."','".$Fund."','".$Bank."','".$BankCode."','".$CVNo["CVNO"]."','".$CheckNo."','0','-'),

        ('".(floatval($ForID["count"])+2)."','".$CDate."','".$Branch."','".$Payee."','".$Particulars."','".$CDate."','".$slacctitle."','".$slacctcode."','".(floatval($amount)*-1)."','".floatval($amount)."','".$AP["sl"]."','0','0','0','0','2','".$CDate."','".floatval($amount)."','".$amtwords."','".$slacctcode."','".$CD["loanid"]."','".$Fund."','".$Bank."','".$BankCode."','".$CVNo["CVNO"]."','".$CheckNo."','0','".$AP["slname"]."'),
        
        ('".(floatval($ForID["count"])+3)."','".$CDate."','".$Branch."','".$Payee."','".$Particulars."','".$CDate."','".$CIB["acctitles"]."','".$CIB["acctcodes"]."','".floatval($amount)."','0','".$CIB["sl"]."','0','".floatval($amount)."','0','".floatval($amount)."','3','".$CDate."','".floatval($amount)."','".$amtwords."','".$CIB["acctcodes"]."','-','".$Fund."','".$Bank."','".$BankCode."','".$CVNo["CVNO"]."','".$CheckNo."','0','-')";
        $query .= $entryquery;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        $stmt = $this->conn->prepare("INSERT INTO tbl_cashdisbursement(id, cdate, branch, fund, bank, txtname, cvno, checkno, particulars, accttitle, glno, sldrcr, slyesno, slno, sundrydr, clientno, loanid, sundrycr, entryno, dateprepared,product,program,LOANTYPE) SELECT id, cdate, branch, fund, bank, payee, cvno, checkno, particular, accttitle, acctno, sldrcr, slyesno, slno, sundrydr, clientno, loanid, sundrycr, entryno, dateprepared,product,program, LOANTYPE FROM tbl_othercv");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM tbl_othercv");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            if($row["acctno"] == "1113"){
                $stmt = $this->conn->prepare("UPDATE tbl_cashdisbursement SET sundrycr = 0, sundrydr=0, cibcr = '" . $row["crother"] . "', cibdr='" . $row["drother"] . "' WHERE GLNO='" . $row["acctno"] . "' AND CHECKNO='" . $row["checkno"] . "' AND CDATE='" . $CDate . "' AND ENTRYNO='" . $row["entryno"] . "'");
                $stmt->execute();
                $stmt->close();
            }
        }

        $stmt = $this->conn->prepare("TRUNCATE tbl_othercv");
        $stmt->execute();
        $stmt->close();

        $zero = 0;
        $stmt = $this->conn->prepare("UPDATE tbl_cashdisbursement SET cibcr = ?, cibdr = ? WHERE glno = ? AND cvno = ?");
        $stmt->bind_param("ssss",$zero,$amount,$CIB["acctcodes"],$CVNo["CVNO"]);
        $stmt->execute();
        $stmt->close();

        if($result > 0){
            $nextcvno = floatval($CVNo["ACTUALCVNO"]);
            $stmt = $this->conn->prepare("UPDATE tbl_banksetup SET lastcv = ? WHERE bank = ?");
            $stmt->bind_param("ss",$nextcvno,$Bank);
            $stmt->execute();
            $stmt->close();

            $this->__construct();
            $cdstatus = "";
            $cdamount = 0;
            $cdstatus = (floatval($CD["amount"]) > floatval($totalslamount)) ? "PENDING" : "DONE";
            $cdamount = floatval($CD["amount"]) - floatval($totalslamount);
            $stmt = $this->conn->prepare("UPDATE tbl_cdpending SET status = ?, amount = ?, cvno = ? WHERE id = ?");
            $stmt->bind_param("ssss",$cdstatus,$cdamount,$CVNo["CVNO"],$data["id"]);
            $stmt->execute();
            $stmt->close();

            $status = "SUCCESS";
            $message = "CASH DISBURSEMENT HAS BEEN SAVED.";
        }

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
        ));
    }
    
    public function LoadBankSetup(){
        // //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("SELECT DISTINCT bank,fund,id,lastcv,slno,nextcheck,type,bankcode FROM tbl_banksetup ORDER BY bank");
        $stmt->execute();
        $result = $stmt->get_result();
        $BankSetup = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "BankSetup" => $BankSetup,
        ));
    }

    private function GenerateJVNO($fund){
        // //$this->COMPACCTCONNECT();
        $finaljvno = "";
        $actualjvno = "";
        $stmt = $this->conn->prepare("SELECT * FROM tbl_banksetup WHERE fund = ? AND bank <> '-'");
        $stmt->bind_param("s",$fund);
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

        return array(
            "JVNO" => $finaljvno,
            "ACTUALJVNO" => $actualjvno
        );
    }

    private function GenerateCVNO($Bank){
        // //$this->COMPACCTCONNECT();
        $finalcvno = "";
        $actualcvno = "";
        $stmt = $this->conn->prepare("SELECT * FROM tbl_banksetup WHERE bank = ? AND bank <> '-'");
        $stmt->bind_param("s",$Bank);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $cvno = floatval($row["LastCV"]) + 1;
                $buffer = "";
                $maxno = "999999999999";

                for ($x = 0; $x < (strlen($maxno) - strlen($cvno)); $x++) {
                    $buffer = $buffer . "0";
                }

                $finalcvno = "CAB" . $buffer . "" . $cvno;
                $actualcvno = $cvno;
            }
        }

        return array(
            "CVNO" => $finalcvno,
            "ACTUALCVNO" => $actualcvno
        );
    }

    private function GenerateORNO($ORName){
        // //$this->COMPACCTCONNECT();
        $message = "";
        $ornofinal = "";
        $orleft = "";
        $orno = "";

        $stmt = $this->conn->prepare("SELECT pending,nextor,orleft,ortype FROM tbl_orseries WHERE name = ?");
        $stmt->bind_param("s",$ORName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if($ORName == "CASHIER"){
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

        return array(
            "ORNO" => $ornofinal,
            "ORLEFT" => $orleft,
            "ACTUALOR" => $orno,
            "MESSAGE" => $message
        );
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

    private function FillRow($data){
        $arr = [];
        while ($row = $data->fetch_assoc()) {
            $arr[] = $row;
        }
        return $arr;
    }
}
?>