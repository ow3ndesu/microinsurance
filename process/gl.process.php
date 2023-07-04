<?php 
include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database 
{
    public function PostGL($data){
        set_time_limit(0);
        // //$this->COMPACCTCONNECT();

        $cdate = date("m/d/Y",strtotime($data["date"]));

        $stmt = $this->conn->prepare("SELECT * FROM tbl_snapshot WHERE cdate = ?");
        $stmt->bind_param("s",$cdate);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows <= 0){
            $stmt = $this->conn->prepare("INSERT INTO tbl_postingdate SET cdate = ?");
            $stmt->bind_param("s",$cdate);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE tbl_cashreceipt SET postingstat = 'YES' WHERE STR_TO_DATE(datepaid,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y')");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE tbl_cashdisbursement SET postingstat = 'YES' WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y')");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE tbl_journalvouchers SET postingstat = 'YES' WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y')");
            $stmt->execute();
            $stmt->close();

            $this->PostTransaction($data["date"]);
            $this->PostGeneralLedger($data["date"]);
            $this->PostSubsidiaryLedger($data["date"]);
            $this->CreateTables();
            $this->ComputeBeginningBalance("tbl_perfundbalance",$data["date"]);
            $this->ComputeTB("tbl_perfundbalance","tbl_trialbalance",$data["date"]);
            $this->PreviousFSBalance("tbl_glcashflowprev",date("m/d/Y",strtotime("-1 month", strtotime($data["date"]))));
            $this->PopulateFS("tbl_incomestatement","tbl_balancesheet","tbl_glcashflow","tbl_perfundbalance",$data["date"]);
            $this->PerformFSComputation("tbl_incomestatement","tbl_balancesheet","tbl_glcashflow","tbl_perfundbalance","tbl_glcashflowprev",$data["date"]);
            $this->PopulatePerFundFS("tbl_incomestatement","tbl_balancesheet","tbl_glcashflow","tbl_perfundbalance",$data["date"]);
            $this->PerformPerFundFSComputation("tbl_incomestatement","tbl_balancesheet","tbl_glcashflow","tbl_perfundbalance","tbl_glcashflowprev",$data["date"]);
            $this->SummaryFS("tbl_summarizedincome", "tbl_summarizedbalance", "tbl_incomestatement", "tbl_balancesheet",$data["date"]);
            $this->ComputeVariance("tbl_variance","CURRENT",$data["date"]);
            // $this->PopulatePESO($data["date"]);
            // $this->GetRating($data["date"]);
            $this->ComputeBeginBalanceSnapshot("tbl_beginperfundbalance",$data["date"]);
            $this->ComputeBeginBalanceGL("tbl_beginperfundbalancegl","CURRENT",$data["date"]);
            $this->ComputeBeginBalanceSchedule("tbl_beginperfundbalancesched",$data["date"]);
            // $this->BackupData();

            $status = "SUCCESS";
        }else{
            $status = "POSTED";
        }

        echo $status;
    }

    private function PostTransaction($cdate){
        $branch = "CAB";

        $stmt = $this->conn->prepare("SELECT fundname FROM tbl_glbanks");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $fund = $row["fundname"];

            // CASH RECEIPT
            $stmt = $this->conn->prepare("SELECT clientname, orno, fund, crbpage, glno, accounttitle, program, product, principal, cbu, ef, mbaprem, interest, servicefee, penalty, totalpaid, chandcr, cbankdr, sundrydr, sundrycr, explanation, crbpage FROM tbl_cashreceipt WHERE STR_TO_DATE(datepaid,'%m/%d/%Y') = STR_TO_DATE(?,'%m/%d/%Y') AND fund = ?");
            $stmt->bind_param("ss",$cdate,$fund);
            $stmt->execute();
            $resultcr = $stmt->get_result();
            $stmt->close();

            while ($rowcr = $resultcr->fetch_assoc()) {
                if(floatval($rowcr["totalpaid"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '1112', accttitle = 'CASH ON HAND', debit = '" . $rowcr["totalpaid"] . "', credit = 0, particulars = '" . $rowcr["clientname"] . "', normalbal = 'DEBIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["chandcr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '1112', accttitle = 'CASH ON HAND', debit = 0, credit = '" . $rowcr["chandcr"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["cbankdr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '1113', accttitle = 'CASH IN BANK', debit = '" . $rowcr["cbankdr"] . "', credit = 0, particulars = '" . $rowcr["clientname"] . "', normalbal = 'DEBIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["principal"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '1121', accttitle = 'LOAN RECEIVABLE - CLIENT', debit = 0, credit = '" . $rowcr["principal"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["cbu"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '2151', accttitle = 'CBU-BENEFICIARIES', debit = 0, credit = '" . $rowcr["cbu"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["mbaprem"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '2159', accttitle = 'MBA PERMIUM CONTRIBUTIONS PAYABLE', debit = 0, credit = '" . $rowcr["mbaprem"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["interest"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '4201', accttitle = 'INTEREST INCOME ON LOANS', debit = 0, credit = '" . $rowcr["interest"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["penalty"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '4202', accttitle = 'PENALTY FEE', debit = 0, credit = '" . $rowcr["penalty"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["servicefee"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = '" . $rowcr["orno"] . "', acctno = '4203', accttitle = 'SERVICE FEE', debit = 0, credit = '" . $rowcr["servicefee"] . "', particulars = '" . $rowcr["clientname"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                // $stmt = $this->conn->prepare("SELECT normalbal FROM tbl_accountcodes WHERE acctcodes = ?");
                // $stmt->bind_param("s",$rowcr["glno"]);
                // $stmt->execute();
                // $resultac = $stmt->get_result();
                // $rowac = $resultac->fetch_assoc();
                // $stmt->close();

                if(floatval($rowcr["sundrydr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '" . $rowcr["glno"] . "', accttitle = '" . $rowcr["accounttitle"] . "', debit = '" . $rowcr["sundrydr"] . "', credit = 0, particulars = '" . $rowcr["explanation"] . "', normalbal = 'DEBIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcr["sundrycr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcr["fund"] . "', reference = 'OR-" . $rowcr["orno"] . "', acctno = '" . $rowcr["glno"] . "', accttitle = '" . $rowcr["accounttitle"] . "', debit = 0, credit = '" . $rowcr["sundrycr"] . "', particulars = '" . $rowcr["explanation"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcr["crbpage"] . "', program = '" . $rowcr["program"] . "', product = '" . $rowcr["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // CASH DISBURSEMENT
            $stmt = $this->conn->prepare("SELECT cdate, txtname, branch, fund, cvno, accttitle, glno, particulars, cibcr, cibdr, chanddr, chandcr, lrclientdr, cbuwithdr, efwithdr, sundrydr, sundrycr, product, program, cdbdaypage FROM tbl_cashdisbursement WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE(?,'%m/%d/%Y') AND fund = ?");
            $stmt->bind_param("ss",$cdate,$fund);
            $stmt->execute();
            $resultcd = $stmt->get_result();
            $stmt->close();
            while($rowcd = $resultcd->fetch_assoc()){
                if(floatval($rowcd["cibcr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '1113'" . ", accttitle = 'CASH IN BANK', debit = 0, credit = '" . $rowcd["cibcr"] . "', particulars = '" . $rowcd["particulars"] . "', normalbal = 'CREDIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcd["cibdr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '1113'" . ", accttitle = 'CASH IN BANK', debit = '" . $rowcd["cibcr"] . "', credit = 0, particulars = '" . $rowcd["particulars"] . "', normalbal = 'DEBIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();             
                }

                if(floatval($rowcd["chanddr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '1112'" . ", accttitle = 'CASH ON HAND', debit = '" . $rowcd["chanddr"] . "', credit = 0, particulars = '" . $rowcd["particulars"] . "', normalbal = 'DEBIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();             
                }

                if(floatval($rowcd["chandcr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '1112'" . ", accttitle = 'CASH ON HAND', debit = 0, credit = '" . $rowcd["chandcr"] . "', particulars = '" . $rowcd["particulars"] . "', normalbal = 'CREDIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcd["lrclientdr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '1121'" . ", accttitle = 'LOAN RECEIVABLE - CLIENT', debit = '" . $rowcd["lrclientdr"] . "', credit = 0, particulars = '" . $rowcd["particulars"] . "', normalbal = 'DEBIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcd["cbuwithdr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '2151'" . ", accttitle = 'CBU-BENEFICIARIES', debit = '" . $rowcd["cbuwithdr"] . "', credit = 0, particulars = '" . $rowcd["particulars"] . "', normalbal = 'DEBIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcd["efwithdr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $rowcd["cdate"] . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '2154'" . ", accttitle = 'EF-BENEFICIARIES', debit = '" . $rowcd["efwithdr"] . "', credit = 0, particulars = '" . $rowcd["particulars"] . "', normalbal = 'DEBIT', booktype = 'CDB', sourceref = '" . $rowcd["cdbdaypage"] . "', product = '" . $rowcd["product"] . "', program = '" . $rowcd["program"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                // $stmt = $this->conn->prepare("SELECT normalbal FROM tbl_accountcodes WHERE acctcodes = ?");
                // $stmt->bind_param("s",$rowcd["glno"]);
                // $stmt->execute();
                // $resultac = $stmt->get_result();
                // $rowac = $resultac->fetch_assoc();
                // $stmt->close();

                if(floatval($rowcd["sundrydr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '" . $rowcd["glno"] . "', accttitle = '" . $rowcd["accttitle"] . "', debit = '" . $rowcd["sundrydr"] . "', credit = 0, particulars = '" . $rowcd["particulars"] . "', normalbal = 'DEBIT', booktype = 'CRB', sourceref = '" . $rowcd["cdbdaypage"] . "', program = '" . $rowcd["program"] . "', product = '" . $rowcd["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }

                if(floatval($rowcd["sundrycr"]) != 0){
                    $stmt = $this->conn->prepare("INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowcd["fund"] . "', reference = 'CV-" . $rowcd["cvno"] . "', acctno = '" . $rowcd["glno"] . "', accttitle = '" . $rowcd["accttitle"] . "', debit = 0, credit = '" . $rowcd["sundrycr"] . "', particulars = '" . $rowcd["particulars"] . "', normalbal = 'CREDIT', booktype = 'CRB', sourceref = '" . $rowcd["cdbdaypage"] . "', program = '" . $rowcd["program"] . "', product = '" . $rowcd["product"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // JOURNAL VOUCHER
            $stmt = $this->conn->prepare("SELECT fund, jvno, acctno, accttitle, nature, drother, crother, product, program FROM tbl_journalvouchers WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE(?,'%m/%d/%Y') AND (drother <> 0 OR crother <> 0) AND fund = ?");
            $stmt->bind_param("ss",$cdate,$fund);
            $stmt->execute();
            $resultjv = $stmt->get_result();
            $stmt->close();
            while ($rowjv = $resultjv->fetch_assoc()) {
                $jvquery = "";
                if($rowjv["drother"]){
                    $jvquery = "INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowjv["fund"] . "', reference = 'JV-" . $rowjv["jvno"] . "', acctno = '" . $rowjv["acctno"] . "', accttitle = '" . $rowjv["accttitle"] . "', debit = '" . $rowjv["drother"] . "', credit = '0', particulars = '" . $rowjv["nature"] . "', normalbal = 'DEBIT', booktype = 'GJ', product = '" . $rowjv["product"] . "', program = '" . $rowjv["program"] . "'";
                }else{
                    $jvquery = "INSERT INTO tbl_snapshot SET cdate = '" . $cdate . "', branch = '" . $branch . "', fund = '" . $rowjv["fund"] . "', reference = 'JV-" . $rowjv["jvno"] . "', acctno = '" . $rowjv["acctno"] . "', accttitle = '" . $rowjv["accttitle"] . "', debit = 0, credit = '" . $rowjv["crother"] . "', particulars = '" . $rowjv["nature"] . "', normalbal = 'CREDIT', booktype = 'GJ', product = '" . $rowjv["product"] . "', program = '" . $rowjv["program"] . "'";
                }

                $stmt = $this->conn->prepare($jvquery);
                $stmt->execute();
                $stmt->close();

            }
        }

        $stmt = $this->conn->prepare("UPDATE tbl_snapshot SET acctno = '2170' WHERE acctno = '2180' AND STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y')");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE tbl_snapshot SET acctno = '1201' WHERE acctno = '1209' AND STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y')");
        $stmt->execute();
        $stmt->close();
    }

    private function PostGeneralLedger($cdate){
        $branch = "CAB";
        $stmt = $this->conn->prepare("SELECT cdate, fund, SUM(debit) AS fordebit, SUM(credit) AS forcredit, acctno, accttitle, booktype, sourceref FROM tbl_snapshot WHERE booktype <> 'GJ' AND cdate = '" . $cdate . "' GROUP BY booktype, fund, acctno, normalbal ORDER BY cdate, fund, acctno, sourceref");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $stmt = $this->conn->prepare("INSERT INTO tbl_glentries SET cdate = '" . $row["cdate"] . "', branch = '" . $branch . "', fund = '" . $row["fund"] . "', reference = '" . $row["sourceref"] . "', acctno = '" . $row["acctno"] . "', accttitle = '" . $row["accttitle"] . "', debit = '" . $row["fordebit"] . "', credit = '" . $row["forcredit"] . "', particulars = 'TOTAL " . $row["booktype"] . " FOR THE DAY'");
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $this->conn->prepare("INSERT INTO tbl_glentries(cdate, branch, fund, reference, acctno, accttitle, debit, credit, particulars) SELECT cdate, branch, fund, reference, acctno, accttitle, debit, credit, particulars FROM tbl_snapshot WHERE booktype = 'GJ' AND cdate = '" . $cdate . "'");
        $stmt->execute();
        $stmt->close();
    }

    private function PostSubsidiaryLedger($cdate){
        
    }

    private function ComputeBeginningBalance($TableBalance,$cdate){
        // //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_snapshotyear");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("CREATE TABLE tbl_snapshotyear LIKE tbl_snapshot");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("INSERT INTO tbl_snapshotyear SELECT * FROM tbl_snapshot WHERE YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('".$cdate."','%m/%d/%Y'))");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $forFund = $row["COLUMN_NAME"];

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS fortotal, acctno, accttitle FROM tbl_gladjustments WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = " . floatval(date("Y",strtotime($cdate))) - 1 . " GROUP BY acctno");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
            $stmt->execute();
            $stmt->close();

            $strFunds = $this->StringFund(DB1,"" . $TableBalance . "");

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " SET consolidated = ROUND(" . $strFunds . ",2)");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function ComputeTB($TableBalance,$TableTB,$cdate){
        // //$this->COMPACCTCONNECT();
        $strFunds = $this->StringZeroFund($TableTB);

        $stmt = $this->conn->prepare("UPDATE " . $TableTB . " SET cdate = '" . $cdate . "', consolidated = 0, ". $strFunds);
        $stmt->execute();
        $stmt->close();

        $strFunds = $this->StringFundTB();

        $stmt = $this->conn->prepare("UPDATE " . $TableTB . " tb INNER JOIN " . $TableBalance . " pb ON tb.acctno = pb.acctno SET tb.consolidated = ROUND(pb.consolidated,2), " . $strFunds);
        $stmt->execute();
        $stmt->close();

        $strFunds = $this->StringFundTBTotal();

        $stmt = $this->conn->prepare("UPDATE " . $TableTB . " SET consolidated = (SELECT ROUND(SUM(consolidated),2) FROM " . $TableBalance . "), " . $strFunds . " WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_temptrialbalance");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("CREATE TABLE tbl_temptrialbalance LIKE " . $TableTB . "");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("INSERT INTO tbl_temptrialbalance SELECT * FROM " . $TableTB . "");
        $stmt->execute();
        $stmt->close();
    }

    private function PreviousFSBalance($TableBalance,$cfdate){
        // //$this->COMPACCTCONNECT();
        $cfyear = date("Y",strtotime($cfdate));
        if($cfyear != date("Y")){
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monthlyfs' AND TABLE_NAME = 'tbl_" . floatval($cfyear) - 1 . "balance'");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if(floatval($row["count"]) > 0){
                $stmt = $this->conn->prepare("DROP TABLE IF EXISTS " . $TableBalance . "");
                $stmt->execute();
                $stmt->close();

                $stmt = $this->conn->prepare("CREATE TABLE " . $TableBalance . " LIKE monthlyfs.tbl_".floatval($cfyear-1)."balance");
                $stmt->execute();
                $stmt->close();

                $stmt = $this->conn->prepare("INSERT INTO " . $TableBalance . " SELECT * FROM monthlyfs.tbl_".floatval($cfyear-1)."balance");
                $stmt->execute();
                $stmt->close();
            }
        }else{
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE " . $TableBalance . " LIKE tbl_beginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO " . $TableBalance . " SELECT * FROM tbl_beginningbalance");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function PopulateFS($TableIS,$TableBS,$TableCF,$TableBalance,$cdate){
        // //$this->COMPACCTCONNECT();
        $branch = "CAB";
        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " SET cdate = '" . $cdate . "', fund = 'CONSOLIDATED', branch = '" . $branch . "', thisday = 0, thismonth = 0, thisyear = 0");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " SET cdate = '" . $cdate . "', fund = 'CONSOLIDATED', branch = '" . $branch . "', amount1 = 0, amount2 = 0, amount3 = 0");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " SET cdate = '" . $cdate . "', fund = 'CONSOLIDATED', branch = '" . $branch . "', amount = 0");
        $stmt->execute();
        $stmt->close();

        // This Day

        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thisday, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y') GROUP BY acctno");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
        $stmt->execute();
        $stmt->close();

        // This Month

        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thismonth, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND MONTH(STR_TO_DATE(cdate,'%m/%d/%Y')) = MONTH(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . "  beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . "  beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
        $stmt->execute();
        $stmt->close();

        // This Year

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
        $stmt->execute();
        $stmt->close();

        // BS

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT1'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT2'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT3'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT1'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT2'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT3'");
        $stmt->execute();
        $stmt->close();
    }

    private function PerformFSComputation($TableIS,$TableBS,$TableCF,$TableBalance,$TableCFPrev,$cdate){
        // //$this->COMPACCTCONNECT();
        // INCOME STATEMENT
         
        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS . " WHERE SUBSTRING(acctno,1,1) = '4' AND acctno <> '4101' ) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT forrevenues.thisday - forexpenses.thisday AS forday, forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . " WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM " . $TableIS . " WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT forexcess.thisday - (fortaxes.thisday + forincometax.thisday) AS forday, forexcess.thismonth - (fortaxes.thismonth + forincometax.thismonth) AS formonth, forexcess.thisyear - (fortaxes.thisyear + forincometax.thisyear) AS foryear FROM (SELECT * FROM " . $TableIS . " WHERE acctno = 'EXCESS OF REVENUE AFTER') AS forexcess, (SELECT * FROM " . $TableIS . " WHERE acctno = '6002') AS fortaxes, (SELECT * FROM " . $TableIS . " WHERE acctno = '6003') AS forincometax) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'AFTER TAX'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT fortotal.thisday + forgrants.thisday AS forday, fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . " WHERE acctno = 'AFTER TAX') AS fortotal, (SELECT * FROM " . $TableIS . " WHERE acctno = '4101') AS forgrants) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE tbl_excessrevenue a, (SELECT fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . " WHERE acctno = 'AFTER TAX') AS fortotal, (SELECT * FROM " . $TableIS . " WHERE acctno = '4101') AS forgrants) b SET a.amount = ROUND(b.foryear,2) WHERE fund = 'CONSOLIDATED'");
        $stmt->execute();
        $stmt->close();

        // BALANCE SHEET

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT forduefrom.amount3 - fordueto.amount3 AS foroffset FROM (SELECT * FROM " . $TableBS . " WHERE acctno = '1401') AS forduefrom, (SELECT * FROM " . $TableBS . " WHERE acctno = '2401') AS fordueto) b SET a.amount3 = ROUND(b.foroffset,2) WHERE acctno = '1401'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " SET amount3 = 0 WHERE acctno = '2401'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT * FROM tbl_excessrevenue WHERE fund = 'CONSOLIDATED') b SET a.amount2 = ROUND(b.amount,2) WHERE acctno = 'ADD EXCESS'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '111') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1114'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '112') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1129'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '113' OR acctno = '1146' OR acctno = '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1139'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '114' AND acctno <> '1146' AND acctno <> '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1145'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '11') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1603' OR acctno = '1703') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1703'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1604' OR acctno = '1704') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1704'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1605' OR acctno = '1705') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1705'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1606' OR acctno = '1706') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1706'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1607' OR acctno = '1707') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1707'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1608' OR acctno = '1708') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1708'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1610' OR acctno = '1710') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1710'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount, SUM(amount3) AS forland FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '16' OR SUBSTRING(acctno,1,2) = '17') b SET a.amount3 = ROUND(b.foramount + b.forland,2) WHERE acctno = 'TOTAL PROPERTY'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '19') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1909'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE acctno <> '1601' AND acctno <> '1602' AND (acctno = 'TOTAL PROPERTY' OR SUBSTRING(acctno,1,1) = '1')) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL ASSET'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2151') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2152') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2152'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2154') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2156') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2156'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '21' AND acctno <> '2170') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENTL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,1) = '2') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE acctno = '3001' OR acctno = 'ADD EXCESS') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = 'ACCUMULATED'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE acctno = '3002' OR acctno = '3003' OR acctno = '3004' OR acctno = '3005' OR acctno = 'ACCUMULATED') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'FUND END'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE acctno = 'TOTAL LIABILITIES' OR acctno = 'FUND END') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL FUND'");
        $stmt->execute();
        $stmt->close();

        // CASH FLOW

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'CASH AND CASH EQUIVALENTS') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH EQUIVALENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'FUND BALANCE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'FUND BALANCE') c, (SELECT thismonth FROM " . $TableIS . " WHERE acctno = 'TOTAL') d SET a.amount = ((b.foramount - c.foramount) * -1) + d.thismonth WHERE indicatorno = 'FUND BALANCE'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'LOANS RECEIVABLE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'LOANS RECEIVABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'OTHER RECEIVABLES') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'OTHER RECEIVABLES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER RECEIVABLES'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PREPAID'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'CURRENT LIABILITIES') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'CURRENT LIABILITIES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'CURRENT LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'FUND HELD IN TRUST') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'FUND HELD IN TRUST') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'FUND HELD'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'INVESTMENTS') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'INVESTMENTS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'INVESTMENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'RECEIVABLES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PROPERTY'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'OTHER ASSETS') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'OTHER ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER ASSET'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PAYABLES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'LOANS PAYABLE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'LOANS PAYABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LOAN PAYABLES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance . " WHERE category = 'LONG-TERM DEBT') b, (SELECT SUM(consolidated) AS foramount FROM " . $TableCFPrev . " WHERE category = 'LONG-TERM DEBT') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LONG TERM'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . " WHERE indicatorno = 'FUND BALANCE' OR indicatorno = 'LR' OR indicatorno = 'OTHER RECEIVABLES' OR indicatorno = 'PREPAID' OR indicatorno = 'CURRENT LIABILITIES' OR indicatorno = 'FUND HELD') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET OPERATION'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . " WHERE indicatorno = 'INVESTMENT' OR indicatorno = 'RECEIVABLES' OR indicatorno = 'PROPERTY' OR indicatorno = 'OTHER ASSET') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET INVESTMENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . " WHERE indicatorno = 'PAYABLES' OR indicatorno = 'LOAN PAYABLES' OR indicatorno = 'LONG TERM') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET FINANCING'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . " WHERE indicatorno = 'CASH EQUIVALENT' OR indicatorno = 'NET OPERATION' OR indicatorno = 'NET INVESTMENT' OR indicatorno = 'NET FINANCING') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH ENDING'");
        $stmt->execute();
        $stmt->close();
    }

    private function PopulatePerFundFS($TableIS,$TableBS,$TableCF,$TableBalance,$cdate){
        // //$this->COMPACCTCONNECT();
        $branch = "CAB";
        // $stmt = $this->conn->prepare("");
        // $stmt->execute();
        // $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $row["COLUMN_NAME"] . " SET cdate = '" . $cdate . "', fund = '" . strtoupper($row["COLUMN_NAME"]) . "', branch = '" . $branch . "', thisday = 0, thismonth = 0, thisyear = 0");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $row["COLUMN_NAME"] . " SET cdate = '" . $cdate . "', fund = '" . strtoupper($row["COLUMN_NAME"]) . "', branch = '" . $branch . "', amount1 = 0, amount2 = 0, amount3 = 0");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . "" . $row["COLUMN_NAME"] . " SET cdate = '" . $cdate . "', fund = '" . strtoupper($row["COLUMN_NAME"]) . "', branch = '" . $branch . "', amount = 0");
            $stmt->execute();
            $stmt->close();

            // This Day

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thisday, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y') GROUP BY acctno");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
            $stmt->execute();
            $stmt->close();

            // This Month

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thismonth, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND MONTH(STR_TO_DATE(cdate,'%m/%d/%Y')) = MONTH(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "  beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "  beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
            $stmt->execute();
            $stmt->close();

            // This Year

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
            $stmt->execute();
            $stmt->close();

            // BS

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT1'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT2'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT3'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT1'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT2'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT3'");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function PerformPerFundFSComputation($TableIS,$TableBS,$TableCF,$TableBalance,$TableCFPrev,$cdate){
        // //$this->COMPACCTCONNECT();
        $branch = "CAB";

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {

            // Income Statement
            $stmt = $this->conn->prepare("UPDATE " . $TableIS . $row["COLUMN_NAME"] . " a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,1) = '4' AND acctno <> '4101' ) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . $row["COLUMN_NAME"] . " a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . $row["COLUMN_NAME"] . " a, (SELECT forrevenues.thisday - forexpenses.thisday AS forday, forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . $row["COLUMN_NAME"] . " a, (SELECT forexcess.thisday - (fortaxes.thisday + forincometax.thisday) AS forday, forexcess.thismonth - (fortaxes.thismonth + forincometax.thismonth) AS formonth, forexcess.thisyear - (fortaxes.thisyear + forincometax.thisyear) AS foryear FROM (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'EXCESS OF REVENUE AFTER') AS forexcess, (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = '6002') AS fortaxes, (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = '6003') AS forincometax) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'AFTER TAX'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . $row["COLUMN_NAME"] . " a, (SELECT fortotal.thisday + forgrants.thisday AS forday, fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'AFTER TAX') AS fortotal, (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = '4101') AS forgrants) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE tbl_excessrevenue a, (SELECT fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'AFTER TAX') AS fortotal, (SELECT * FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = '4101') AS forgrants) b SET a.amount = ROUND(b.foryear,2) WHERE REPLACE(REPLACE(fund,'-',''),' ','') = '" . $row["COLUMN_NAME"] . "'");
            $stmt->execute();
            $stmt->close();

            // Balance Sheet
            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT * FROM tbl_excessrevenue WHERE REPLACE(REPLACE(fund,'-',''),' ','') = '" . $row["COLUMN_NAME"] . "') b SET a.amount2 = ROUND(b.amount) WHERE acctno = 'ADD EXCESS'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,3) = '111') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1114'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,3) = '112') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1129'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,3) = '113' OR acctno = '1146' OR acctno = '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1139'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,3) = '114' AND acctno <> '1146' AND acctno <> '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1145'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,2) = '11') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENT'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1603' OR acctno = '1703') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1703'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1604' OR acctno = '1704') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1704'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1605' OR acctno = '1705') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1705'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1606' OR acctno = '1706') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1706'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1607' OR acctno = '1707') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1707'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1608' OR acctno = '1708') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1708'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '1610' OR acctno = '1710') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1710'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount, SUM(amount3) AS forland FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,2) = '16' OR SUBSTRING(acctno,1,2) = '17') b SET a.amount3 = ROUND(b.foramount + b.forland,2) WHERE acctno = 'TOTAL PROPERTY'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,2) = '19') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1909'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno <> '1601' AND acctno <> '1602' AND (acctno = 'TOTAL PROPERTY' OR SUBSTRING(acctno,1,1) = '1')) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL ASSET'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '2151') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '2152') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2152'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '2154') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '2156') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2156'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,2) = '21' AND acctno <> '2170') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENTL'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE SUBSTRING(acctno,1,1) = '2') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL LIABILITIES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '3001' OR acctno = 'ADD EXCESS') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = 'ACCUMULATED'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = '3002' OR acctno = '3003' OR acctno = '3004' OR acctno = '3005' OR acctno = 'ACCUMULATED') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'FUND END'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . $row["COLUMN_NAME"] . " WHERE acctno = 'TOTAL LIABILITIES' OR acctno = 'FUND END') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL FUND'");
            $stmt->execute();
            $stmt->close();

            // Cash Flow
            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'CASH AND CASH EQUIVALENTS') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH EQUIVALENT'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'FUND BALANCE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'FUND BALANCE') c, (SELECT thismonth FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'TOTAL') d SET a.amount = ((b.foramount - c.foramount) * -1) + d.thismonth WHERE indicatorno = 'FUND BALANCE'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'LOANS RECEIVABLE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'LOANS RECEIVABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LR'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'OTHER RECEIVABLES') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'OTHER RECEIVABLES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER RECEIVABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PREPAID'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'CURRENT LIABILITIES') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'CURRENT LIABILITIES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'CURRENT LIABILITIES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'FUND HELD IN TRUST') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'FUND HELD IN TRUST') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'FUND HELD'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'INVESTMENTS') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'INVESTMENTS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'INVESTMENT'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'RECEIVABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PROPERTY'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'OTHER ASSETS') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'OTHER ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER ASSET'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PAYABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'LOANS PAYABLE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'LOANS PAYABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LOAN PAYABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'LONG-TERM DEBT') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableCFPrev . " WHERE category = 'LONG-TERM DEBT') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LONG TERM'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . $row["COLUMN_NAME"] . " WHERE indicatorno = 'FUND BALANCE' OR indicatorno = 'LR' OR indicatorno = 'OTHER RECEIVABLES' OR indicatorno = 'PREPAID' OR indicatorno = 'CURRENT LIABILITIES' OR indicatorno = 'FUND HELD') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET OPERATION'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . $row["COLUMN_NAME"] . " WHERE indicatorno = 'INVESTMENT' OR indicatorno = 'RECEIVABLES' OR indicatorno = 'PROPERTY' OR indicatorno = 'OTHER ASSET') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET INVESTMENT'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . $row["COLUMN_NAME"] . " WHERE indicatorno = 'PAYABLES' OR indicatorno = 'LOAN PAYABLES' OR indicatorno = 'LONG TERM') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET FINANCING'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(amount) AS foramount FROM " . $TableCF . $row["COLUMN_NAME"] . " WHERE indicatorno = 'CASH EQUIVALENT' OR indicatorno = 'NET OPERATION' OR indicatorno = 'NET INVESTMENT' OR indicatorno = 'NET FINANCING') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH ENDING'");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function SummaryFS($TableSummIS,$TableSummBS,$TableIS,$TableBS,$cdate){
        // //$this->COMPACCTCONNECT();
        $branch = "CAB";
        $forTotalFund = 0;
        // $TableIS = "tbl_incomestatement";
        // $TableBS = "tbl_balancesheet";
        // $TableSummIS = "tbl_summarizedincome";
        // $TableSummBS = "tbl_summarizedbalance";

        // Income Statement Summary
        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " SET cdate = '" . $cdate . "', branch = '" . $branch . "', thismonth = 0, thisyear = 0");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4201') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4201'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4205') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4205'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4208') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4208'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4299') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4299'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableSummIS . " WHERE SUBSTRING(acctno,1,2) = '42') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5100') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5100'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5200') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5200'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5300') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5300'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5504') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5504'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5400') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5400'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5500') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5500'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableSummIS . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM " . $TableSummIS . " WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM " . $TableSummIS . " WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4101') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4101'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableSummIS . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummIS . " a, (SELECT fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear - forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableSummIS . " WHERE acctno = 'EXCESS OF REVENUE AFTER') AS fortotal, (SELECT * FROM " . $TableSummIS . " WHERE acctno = '4101') AS forgrants) b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        // Balance Sheet Summary
        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " SET cdate = '" . $cdate . "', branch = '" . $branch . "', amount = 0");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " SET amount = '" . $forTotalFund . "' WHERE acctno = '3001'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1110') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1110'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1114') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1114'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1120') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1120'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1140') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1140'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE SUBSTRING(acctno,1,2) = '11') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-CURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1200') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1200'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1600') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1600'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1900') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1900'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE acctno = '1200' OR acctno = '1600' OR acctno = '1900') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-NONCURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE acctno = 'TOTAL-CURRENT' OR acctno = 'TOTAL-NONCURRENT') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-ASSETS'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = 'ACCOUNTS-PAYABLE') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2100'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '2150') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2150'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '2180') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2180'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE SUBSTRING(acctno,1,2) = '21') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-CURLIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '2200') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2200'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE acctno = 'TOTAL-CURLIABILITIES' OR acctno = '2200') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE acctno = 'ACCUMULATED') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '3001'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '3002') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '3002'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE SUBSTRING(acctno,1,1) = '3' OR acctno = 'UNREALIZED') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-FUNDBALANCE'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableSummBS . " a, (SELECT SUM(amount) AS foramount FROM " . $TableSummBS . " WHERE acctno = 'TOTAL-LIABILITIES' OR acctno = 'TOTAL-FUNDBALANCE') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();
    }

    private function ComputeVariance($TableV,$VStat,$cdate){
        //$this->COMPACCTCONNECT();
        $branch = "CAB";
        // $TableV = "tbl_variance";
        // $VStat = "CURRENT";

        // $stmt = $this->conn->prepare("");
        // $stmt->execute();
        // $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " SET cdate = '" . $cdate . "', branch = '" . $branch . "', yearbudget = '0', yearactual = '0', yearvariance = '0', monthbudget = '0', monthactual = '0', monthvariance = '0', lastyear = '0', lastcyyear = '0', yearlastdiff = '0', cyyearlastdiff = '0', percentvar = '0'");
        $stmt->execute();
        $stmt->close();

        $forStrMonth = "";
        $forMonths = explode(",","01, 02, 03, 04, 05, 06, 07, 08, 09, 10, 11, 12");
        $forMonthNow = strtolower(date("F",strtotime($cdate)));

        for ($i=0; $i < count($forMonths); $i++) { 
            if(floatval($forMonths[$i]) <= floatval(date("m"))){
                $forMonthName = strtolower(date("F",strtotime($forMonths[$i]."01/2023")));
                if($i == 0){
                    $forStrMonth = "bud." . $forMonthName;
                }else{
                    $forStrMonth = $forStrMonth . " + " . "bud." . $forMonthName;
                }
            }else{
                break;
            }
        }

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " var INNER JOIN tbl_budget bud ON var.acctno = bud.acctno SET var.monthbudget = bud." . $forMonthNow . "");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " var INNER JOIN tbl_budget bud ON var.acctno = bud.acctno SET var.yearbudget = " . $forStrMonth . "");
        $stmt->execute();
        $stmt->close();

        // Get IS Data

        if($VStat == "CURRENT"){
            $stmt = $this->conn->prepare("UPDATE " . $TableV . " var INNER JOIN tbl_incomestatement inc ON var.acctno = inc.acctno SET var.yearactual = inc.thisyear, var.monthactual = inc.thismonth");
            $stmt->execute();
            $stmt->close();
        }else{
            $stmt = $this->conn->prepare("SELECT DISTINCT cdate FROM tbl_snapshot WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y')");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if($result->num_rows > 0){
                $this->PreviousFSBalance("tbl_beginperfundbalancefs",$cdate);
                $this->PreviousFS("tbl_beginperfundbalancefs","tbl_glisprevious","tbl_glbsprevious",$cdate);

                $stmt = $this->conn->prepare("UPDATE " . $TableV . " var INNER JOIN tbl_glisprevious inc ON var.acctno = inc.acctno SET var.yearactual = inc.thisyear, var.monthactual = inc.thismonth WHERE var.category <> 'TITLE'");
                $stmt->execute();
                $stmt->close();
            }
        }

        // Get Last Year

        $forLastDate = date("m/d/Y",strtotime("-2 year",strtotime($cdate)));
        $this->PreviousFSBalance("tbl_beginperfundbalancefs",$forLastDate);
        $this->PreviousFS("tbl_beginperfundbalancefs","tbl_glisprevious","tbl_glbsprevious",$forLastDate);

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " var INNER JOIN tbl_glisprevious inc ON var.acctno = inc.acctno SET var.lastyear = inc.thisyear WHERE var.category <> 'TITLE'");
        $stmt->execute();
        $stmt->close();

        // Get Last CY Year

        $forCYDate = date("m/d/Y",strtotime("-1 year",strtotime($cdate)));
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monthlyfs' AND TABLE_NAME = 'tbl_" . date("Y",strtotime($forCYDate)) . "endingbalance'");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if(floatval($row["count"]) > 0){
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_incomestatementtemp");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_incomestatementtemp LIKE tbl_incomestatement");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_incomestatementtemp SELECT * FROM tbl_incomestatement");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("SELECT acctno, a.thisyear FROM (SELECT acctno, ROUND(SUM(consolidated),2) AS thisyear FROM monthlyfs.tbl_" . date("Y",strtotime($forCYDate)) . "endingbalance GROUP BY acctno) a GROUP BY acctno");
            $stmt->execute();
            $result1 = $stmt->get_result();
            $stmt->close();
            while ($row1 = $result1->fetch_assoc()) {
                $forThisYear = $row1["thisyear"];
                $stmt = $this->conn->prepare("SELECT * FROM tbl_accountcodes WHERE acctcodes = '" . $row1["acctno"] . "'");
                $stmt->execute();
                $result2 = $stmt->get_result();
                $stmt->close();
                while ($row2 = $result2->fetch_assoc()) {
                    if($row2["normalbal"] == "DEBIT"){
                        if(floatval($forThisYear) < 0){
                            $forThisYear = $forThisYear;
                        }else{
                            $forThisYear = $forThisYear;
                        }
                    }else{
                        if(floatval($forThisYear) < 0){
                            $forThisYear = ($forThisYear * -1);
                        }else{
                            $forThisYear = ($forThisYear * -1);
                        }
                    }

                    $stmt = $this->conn->prepare("UPDATE tbl_incomestatementtemp SET thisyear = '" . $forThisYear . "' WHERE acctno = '" . $row1["acctno"] . "'");
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        
        $stmt = $this->conn->prepare("UPDATE tbl_incomestatementtemp a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM tbl_incomestatementtemp WHERE SUBSTRING(acctno,1,1) = '4' AND acctno <> '4101' ) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE tbl_incomestatementtemp a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM tbl_incomestatementtemp WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE tbl_incomestatementtemp a, (SELECT forrevenues.thisday - forexpenses.thisday AS forday, forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM tbl_incomestatementtemp WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM tbl_incomestatementtemp WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE tbl_incomestatementtemp a, (SELECT fortotal.thisday + forgrants.thisday AS forday, fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear - forgrants.thisyear AS foryear FROM (SELECT * FROM tbl_incomestatementtemp WHERE acctno = 'EXCESS OF REVENUE AFTER') AS fortotal, (SELECT * FROM tbl_incomestatementtemp WHERE acctno = '4101') AS forgrants) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " var INNER JOIN tbl_incomestatementtemp inc ON var.acctno = inc.acctno SET var.lastcyyear = inc.thisyear WHERE var.category <> 'TITLE'");
        $stmt->execute();
        $stmt->close();

        // Compute Variance

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " SET yearvariance = ROUND(yearbudget - yearactual,2), monthvariance = ROUND(monthbudget - monthactual,2) WHERE category <> 'TITLE'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " SET yearlastdiff = ROUND(yearactual - lastyear,2) WHERE category <> 'TITLE'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " SET percentvar = ROUND(yearvariance/yearactual,2) WHERE yearactual <> '0' AND yearactual <> '0.00'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableV . " SET cyyearlastdiff = ROUND(yearactual - lastcyyear,2) WHERE category <> 'TITLE'");
        $stmt->execute();
        $stmt->close();
    }

    private function PopulatePESO($cdate){
        // //$this->COMPACCTCONNECT();
        $branch = "CAB";
        $TablePESO = "tbl_pesodata";
        $TableRating = "tbl_pesorating";
        $TableAging = "";
        $TableIS = "";
        $TableBS = "";

        // $stmt = $this->conn->prepare("");
        // $stmt->execute();
        // $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " SET cdate = '" . $cdate . "', branch = '" . $branch . "'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " SET amount1 = 0, amount2 = 0, amount3 = 0, amount4 = 0, ratio = 0 WHERE encode <> 'YES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableRating . " SET cdate = '" . $cdate . "', branch = '" . $branch . "'");
        $stmt->execute();
        $stmt->close();

        if($cdate != date("m/d/Y")){

            $forChecking = 0;

            $forAgingDate = date("m/d/Y",strtotime("-1 year",strtotime($cdate)));
            $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'archive' AND TABLE_NAME = 'tbl_" . date("m",strtotime($forAgingDate)) . date("Y",strtotime($forAgingDate)) . "'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if($result->num_rows == 0){
                $forChecking = 1;
            }

            $forFSDate = date("m/d/Y",strtotime("-1 year",strtotime($cdate)));
            $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monthlyfs' AND TABLE_NAME = 'tbl_" . (date("Y",strtotime($forFSDate)) - 1) . "balance'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if($result->num_rows == 0){
                $forChecking = floatval($forChecking) + 1;
            }

            switch ($forChecking) {
                case 2:
                    goto Skip;
                    break;
                case 1:
                    goto Skip;
                    break;
                case 0:
                    $this->PreviousFSBalance("tbl_beginperfundbalancefs",$forFSDate);
                    $this->PreviousFS("tbl_beginperfundbalancefs","tbl_glispeso","tbl_glbspeso",$forFSDate);

                    $TableAging = "archive.tbl".date("m",strtotime($cdate)) . date("Y",strtotime($cdate));
                    $TableIS = "tbl_glispeso";
                    $TableBS = "tbl_glbspeso";
                default:
                    # code...
                    break;
            }
        }else{
            $TableAging = "tbl_aging";
            $TableIS = "tbl_incomestatement";
            $TableBS = "tbl_balancesheet";
        }

        // PORTFOLIO AT RISK

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(balance),0) AS forpar130 FROM " . $TableAging . " WHERE 1_30 > 0 AND 31_60 = 0) b SET a.amount3 = ROUND(b.forpar130,2) WHERE indicatorno = 'PAR1'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(balance),0) AS forpar3160 FROM " . $TableAging . " WHERE 31_60 > 0 AND 61_90 = 0) b SET a.amount3 = ROUND(b.forpar3160,2) WHERE indicatorno = 'PAR31'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(balance),0) AS forpar6190 FROM " . $TableAging . " WHERE 61_90 > 0 AND 91_120 = 0) b SET a.amount3 = ROUND(b.forpar6190,2) WHERE indicatorno = 'PAR61'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(CASE WHEN 91_120 > 0 AND 121_150 = 0 THEN balance END),0) AS for91120, COALESCE(SUM(CASE WHEN 121_150 > 0 AND 151_180 = 0 THEN balance END),0) AS for121150, COALESCE(SUM(CASE WHEN 151_180 > 0 AND over180 = 0 THEN balance END),0) AS for151180, COALESCE(SUM(CASE WHEN over180 > 0 THEN balance END),0) AS forover180 FROM " . $TableAging . ") b SET a.amount3 = ROUND(b.for91120 + b.for121150 + b.for151180 + b.forover180,2) WHERE indicatorno = 'PAR91'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(amount3),0) AS forcurrent FROM " . $TablePESO . " WHERE indicatorno = 'PAR1' OR indicatorno = 'PAR31' OR indicatorno = 'PAR61' OR indicatorno = 'PAR91' ) b SET a.amount4 = ROUND(b.forcurrent,2) WHERE indicatorno = 'PARLOAN'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(amount3),0) AS forcurrent FROM " . $TablePESO . " WHERE indicatorno = 'PAR1' OR indicatorno = 'PAR31' OR indicatorno = 'PAR61' OR indicatorno = 'PAR91' ) b SET a.amount3 = ROUND(b.forcurrent,2) WHERE indicatorno = 'NONCURRENTLR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(balance),0) AS forportfolio FROM " . $TableAging . " WHERE par = 0) b SET a.amount3 = ROUND(b.forportfolio,2) WHERE indicatorno = 'CURRENTLR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(amount3),0) AS forloansout FROM " . $TablePESO . " WHERE indicatorno = 'CURRENTLR' OR indicatorno = 'NONCURRENTLR') b SET a.amount4 = ROUND(b.forloansout,2) WHERE indicatorno = 'LR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(amount3),0) AS forloansout FROM " . $TablePESO . " WHERE indicatorno = 'CURRENTLR' OR indicatorno = 'NONCURRENTLR') b SET a.amount3 = ROUND(b.forloansout,2) WHERE indicatorno = 'GROSSLRCURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(amount3),0) AS forloansout FROM " . $TablePESO . " WHERE indicatorno = 'CURRENTLR' OR indicatorno = 'NONCURRENTLR') b SET a.amount3 = ROUND(b.forloansout,2) WHERE indicatorno = 'TOTALLR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE(SUM(amount3),0) AS forloansout FROM " . $TablePESO . " WHERE indicatorno = 'CURRENTLR' OR indicatorno = 'NONCURRENTLR') b SET a.amount3 = ROUND(b.forloansout,2) WHERE indicatorno = 'ENDINGLR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TablePESO . " a, (SELECT COALESCE((forcurrent.amount4 / forportfolio.amount4) * 100,0) AS forresult FROM (SELECT amount4 FROM " . $TablePESO . " WHERE indicatorno = 'PARLOAN') AS forcurrent, (SELECT amount4 FROM " . $TablePESO . " WHERE indicatorno = 'LR') AS forportfolio) b SET a.ratio = ROUND(b.forresult,2) WHERE indicatorno = 'PAR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableRating . " a, (SELECT COALESCE((forcurrent.amount4 / forportfolio.amount4) * 100,0) AS forresult FROM (SELECT amount4 FROM " . $TablePESO . " WHERE indicatorno = 'PARLOAN') AS forcurrent, (SELECT amount4 FROM " . $TablePESO . " WHERE indicatorno = 'LR') AS forportfolio) b SET a.ratio = ROUND(b.forresult,2) WHERE indicatorno = 'PAR'");
        $stmt->execute();
        $stmt->close();

        // LOAN LOSS RESERVE RATIO

        // $stmt = $this->conn->prepare("");
        // $stmt->execute();
        // $stmt->close();


        Skip:
    }

    private function ComputeBeginBalanceSnapshot($TableBalance,$cdate){
        // //$this->COMPACCTCONNECT();
        // $TableBalance = "tbl_beginperfundbalance";
        $this->DropCreateSnapshotYear($cdate);

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $forFund = $row["COLUMN_NAME"];

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS fortotal, acctno, accttitle FROM tbl_gladjustments WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = " . floatval(date("Y",strtotime($cdate))) - 1 . " GROUP BY acctno");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
            $stmt->execute();
            $stmt->close();

            $strFunds = $this->StringFund(DB1,$TableBalance);

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " SET consolidated = ROUND(" . $strFunds . ",2)");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function ComputeBeginBalanceGL($TableBalance,$forStatus,$cdate){
        // //$this->COMPACCTCONNECT();
        // $TableBalance = "tbl_beginperfundbalancegl";
        // $forStatus = "CURRENT";

        $this->DropCreateSnapshotYear($cdate);

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $forFund = $row["COLUMN_NAME"];

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS fortotal, acctno, accttitle FROM tbl_gladjustments WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = " . floatval(date("Y",strtotime($cdate))) - 1 . " GROUP BY acctno");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
            $stmt->execute();
            $stmt->close();

            $strFunds = $this->StringFund(DB1,$TableBalance);

            if($forStatus == "CURRENT"){
                $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND MONTH(STR_TO_DATE(cdate,'%m/%d/%Y')) < MONTH(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " SET consolidated = ROUND(" . $strFunds . ",2)");
                $stmt->execute();
                $stmt->close();
            }else{
                $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
                $stmt->execute();
                $stmt->close();
    
                $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " SET consolidated = ROUND(" . $strFunds . ",2)");
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    private function ComputeBeginBalanceSchedule($TableBalance,$cdate){
        // //$this->COMPACCTCONNECT();
        // $TableBalance = "tbl_beginperfundbalancesched";

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $forFund = $row["COLUMN_NAME"];

            $strFunds = $this->StringFund(DB1,$TableBalance);

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbeginningbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbeginningbalance LIKE " . $TableBalance . "");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbeginningbalance(" . $forFund . ", acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS fortotal, acctno, accttitle FROM tbl_gladjustments WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
            $stmt->execute();
            $stmt->close(); 

            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " beg INNER JOIN tbl_tempbeginningbalance end ON beg.acctno = end.acctno SET beg." . $forFund . " = ROUND(beg." . $forFund . " + end." . $forFund . ",2)");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBalance . " SET consolidated = ROUND(" . $strFunds . ",2)");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function PreviousFS($TableBalance,$TableIS,$TableBS,$cdate){
        // //$this->COMPACCTCONNECT();
        $branch = "CAB";
        $this->DropCreateSnapshotYear($cdate);

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " SET cdate = '" . $cdate . "', fund = 'CONSOLIDATED', branch = '" . $branch . "', thisday = 0, thismonth = 0, thisyear = 0");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " SET cdate = '" . $cdate . "', fund = 'CONSOLIDATED', branch = '" . $branch . "', amount1 = 0, amount2 = 0, amount3 = 0");
        $stmt->execute();
        $stmt->close();

        // This Day

        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thisday, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y') GROUP BY acctno");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
        $stmt->execute();
        $stmt->close();

        // This Month

        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thismonth, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND MONTH(STR_TO_DATE(cdate,'%m/%d/%Y')) = MONTH(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
        $stmt->execute();
        $stmt->close();

        // This Year

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
        $stmt->execute();
        $stmt->close();

        // BS

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT1'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT2'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT3'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT1'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT2'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT3'");
        $stmt->execute();
        $stmt->close();

        // IS

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS . " WHERE SUBSTRING(acctno,1,1) = '4' AND acctno <> '4101' ) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT forrevenues.thisday - forexpenses.thisday AS forday, forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . " WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM " . $TableIS . " WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS . " a, (SELECT fortotal.thisday + forgrants.thisday AS forday, fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS . " WHERE acctno = 'EXCESS OF REVENUE AFTER') AS fortotal, (SELECT * FROM " . $TableIS . " WHERE acctno = '4101') AS forgrants) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        // TB

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT * FROM " . $TableIS . " WHERE acctno = 'TOTAL') b SET a.amount2 = ROUND(b.thisyear) WHERE a.acctno = 'ADD EXCESS'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '111') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1114'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '112') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1129'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '113' OR acctno = '1146' OR acctno = '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1139'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,3) = '114' AND acctno <> '1146' AND acctno <> '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1145'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '11') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1603' OR acctno = '1703') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1703'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1604' OR acctno = '1704') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1704'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1605' OR acctno = '1705') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1705'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1606' OR acctno = '1706') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1706'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1607' OR acctno = '1707') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1707'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1608' OR acctno = '1708') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1708'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS . " WHERE acctno = '1610' OR acctno = '1710') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1710'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '16' OR SUBSTRING(acctno,1,2) = '17') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL PROPERTY'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '19') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1909'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE acctno = 'TOTAL PROPERTY' OR SUBSTRING(acctno,1,1) = '1') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL ASSET'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2151') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2152') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2152'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2154') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS . " WHERE acctno = '2156') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2156'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,2) = '21') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENTL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE SUBSTRING(acctno,1,1) = '2') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE acctno = '3001' OR acctno = 'ADD EXCESS') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = 'ACCUMULATED'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE acctno = '3002' OR acctno = 'ACCUMULATED') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'FUND END'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE acctno = 'TOTAL LIABILITIES' OR acctno = 'FUND END') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL FUND'");
        $stmt->execute();
        $stmt->close();
    }

    private function DropCreateSnapshotYear($cdate){
        // //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_snapshotyear");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("CREATE TABLE tbl_snapshotyear LIKE tbl_snapshot");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("INSERT INTO tbl_snapshotyear SELECT * FROM tbl_snapshot WHERE YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('".$cdate."','%m/%d/%Y'))");
        $stmt->execute();
        $stmt->close();
    }

    private function CreateTables(){
        // //$this->COMPACCTCONNECT();
        $query = "DROP TABLE IF EXISTS tbl_perfundbalance ; DROP TABLE IF EXISTS tbl_beginperfundbalance ; DROP TABLE IF EXISTS tbl_beginperfundbalancegl ; DROP TABLE IF EXISTS tbl_beginperfundbalancesched ; DROP TABLE IF EXISTS tbl_beginperfundbalancesl ; CREATE TABLE tbl_perfundbalance LIKE tbl_beginningbalance ; CREATE TABLE tbl_beginperfundbalance LIKE tbl_beginningbalance ; CREATE TABLE tbl_beginperfundbalancegl LIKE tbl_beginningbalance ; CREATE TABLE tbl_beginperfundbalancesched LIKE tbl_beginningbalance ; CREATE TABLE tbl_beginperfundbalancesl LIKE tbl_slbeginningbalance ; INSERT INTO tbl_perfundbalance SELECT * FROM tbl_beginningbalance ; INSERT INTO tbl_beginperfundbalance SELECT * FROM tbl_beginningbalance ; INSERT INTO tbl_beginperfundbalancegl SELECT * FROM tbl_beginningbalance ; INSERT INTO tbl_beginperfundbalancesched SELECT * FROM tbl_beginningbalance ; INSERT INTO tbl_beginperfundbalancesl SELECT * FROM tbl_slbeginningbalance";
        mysqli_multi_query($this->conn,$query);
    }

    private function StringFund($database,$table){
        // //$this->COMPACCTCONNECT();
        $str = "";
        $count = 0;
        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $database . "' AND TABLE_NAME = '" . $table . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            if($count == 0){
                $str = $str . "" . $row["COLUMN_NAME"];
            }else{
                $str = $str . " + " . $row["COLUMN_NAME"];
            }
            $count++;
        }
        return $str;
    }

    private function StringZeroFund($table){
        // //$this->COMPACCTCONNECT();
        $str = "";
        $count = 0;
        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $table . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            if(($result->num_rows - 1) == $count){
                $str = $str . "" . $row["COLUMN_NAME"] . " = 0 ";
            }else{
                $str = $str . "" . $row["COLUMN_NAME"] . " = 0, ";
            }
            $count++;
        }
        return $str;
    }

    private function StringFundTB(){
        // //$this->COMPACCTCONNECT();
        $str = "";
        $count = 0;
        $stmt = $this->conn->prepare("SELECT * FROM tbl_glbanks");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $string = $this->removeSpecialCharacters($row["fundname"]);
            if(($result->num_rows - 1) == $count){
                $str = $str . "tb." . $string . " = ROUND(pb." . $string . ",2)";
            }else{
                $str = $str . "tb." . $string . " = ROUND(pb." . $string . ",2),";
            }
            $count++;
        }
        return $str;
    }

    private function StringFundTBTotal(){
        // //$this->COMPACCTCONNECT();
        $str = "";
        $count = 0;
        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = 'tbl_perfundbalance' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            if(($result->num_rows -1) == $count){
                $str = $str . "" . $row["COLUMN_NAME"] . " = (SELECT ROUND(SUM(" . $row["COLUMN_NAME"] . "),2) FROM tbl_perfundbalance)";
            }else{
                $str = $str . "" . $row["COLUMN_NAME"] . " = (SELECT ROUND(SUM(" . $row["COLUMN_NAME"] . "),2) FROM tbl_perfundbalance),";
            }
            $count++;
        }
        return $str;
    }

    private function removeSpecialCharacters($string) {
        $pattern = '/[^a-zA-Z0-9]/';
        $replacement = '';
        $cleanString = strtolower(preg_replace($pattern, $replacement, $string));
        return $cleanString;
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