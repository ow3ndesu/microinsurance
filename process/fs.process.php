<?php 
include_once("../database/connection.php");
include_once("../reports/fs.reports.php");
include_once("sanitize.process.php");

class Process extends Database 
{
    public function Initialize(){
        //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("SELECT fundname as fund FROM tbl_glbanks");
        $stmt->execute();
        $Fund = $this->FillRow($stmt->get_result());
        $stmt->close();

        echo json_encode(array(
            "FUND" => $Fund
        ));
    }

    public function RetrieveData($data){
        $status = "";
        $message = "";

        //$this->COMPACCTCONNECT();
        $stmt = $this->conn->prepare("SELECT DISTINCT cdate FROM tbl_snapshot WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $data["date"] . "','%m/%d/%Y')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows > 0){
            if(date("Y",strtotime($data["date"])) <> date("Y")){
                $forYearDate = date("m/d/Y",strtotime("-1 year",strtotime($data["date"])));

                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monthlyfs' AND TABLE_NAME = 'tbl_" . date("Y",strtotime($forYearDate)) . "balance'");
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if($row["count"] > 0){
                    $CFDate = date("m/d/Y",strtotime("-1 month",strtotime($data["date"])));

                    $this->ComputeBeginBalance("tbl_beginperfundbalancefs",$data["date"]);
                    $this->PreviousFSBalance("tbl_glcashflowprev",$CFDate);
                    $this->PopulateFS("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_glcashflowtemp","tbl_beginperfundbalancefs", $data["date"]);
                    $this->PerformFSComputation("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_glcashflowtemp","tbl_beginperfundbalancefs","tbl_glcashflowprev");
                    $this->PopulatePerFundFS("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_glcashflowtemp","tbl_beginperfundbalancefs", $data["date"]);
                    $this->PerformPerFundFSComputation("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_beginperfundbalancefs","tbl_glcashflowtemp","tbl_glcashflowprev");
                    $this->SummaryFS("tbl_summarizedincometemp","tbl_summarizedbalancetemp","tbl_incomestatementtemp","tbl_balancesheettemp",$data["date"]);

                    $status = "SUCCESS";
                    $message = "Data has been retrieved";
                }else{
                    $status = "NO_DATA";
                    $message = "No data to retrieve with the date supplied";
                }
            }else{

                $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_beginperfundbalancefs");
                $stmt->execute();
                $stmt->close();

                $stmt = $this->conn->prepare("CREATE TABLE tbl_beginperfundbalancefs LIKE tbl_beginningbalance");
                $stmt->execute();
                $stmt->close();

                $stmt = $this->conn->prepare("INSERT INTO tbl_beginperfundbalancefs SELECT * FROM tbl_beginningbalance");
                $stmt->execute();
                $stmt->close();

                $this->CreateTables();
                $CFDate = date("m/d/Y",strtotime("-1 month",strtotime($data["date"])));

                $this->ComputeBeginBalance("tbl_beginperfundbalancefs",$data["date"]);
                $this->PreviousFSBalance("tbl_glcashflowprev",$CFDate);
                $this->PopulateFS("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_glcashflowtemp","tbl_beginperfundbalancefs", $data["date"]);
                $this->PerformFSComputation("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_glcashflowtemp","tbl_beginperfundbalancefs","tbl_glcashflowprev");
                $this->PopulatePerFundFS("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_glcashflowtemp","tbl_beginperfundbalancefs", $data["date"]);
                $this->PerformPerFundFSComputation("tbl_incomestatementtemp","tbl_balancesheettemp","tbl_beginperfundbalancefs","tbl_glcashflowtemp","tbl_glcashflowprev");
                $this->SummaryFS("tbl_summarizedincometemp","tbl_summarizedbalancetemp","tbl_incomestatementtemp","tbl_balancesheettemp",$data["date"]);

                $status = "SUCCESS";
                $message = "Data has been retrieved";
            }
        }else{
            $status = "NO_DATA";
            $message = "No data to retrieve with the date supplied";
        }

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
        ));
    }

    public function RetrieveDataTB($data){
        $status = "";
        $message = "";

        //$this->COMPACCTCONNECT();
        
        if(date("d",strtotime($data["date"])) == "31" && date("m",strtotime($data["date"]))){
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_beginperfundbalancetb");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_beginperfundbalancetb LIKE monthlyfs.tbl_" . date("Y",strtotime($data["date"])) . "balance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_beginperfundbalancetb SELECT * FROM monthlyfs.tbl_" . date("Y",strtotime($data["date"])) . "balance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_trialbalanceperfund");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("CREATE TABLE tbl_trialbalanceperfund LIKE tbl_trialbalance");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO tbl_trialbalanceperfund SELECT * FROM tbl_trialbalance");
            $stmt->execute();
            $stmt->close();

            $this->ComputeTB("tbl_beginperfundbalancetb","tbl_trialbalanceperfund",$data["date"]);
        }else{
            $stmt = $this->conn->prepare("SELECT DISTINCT cdate FROM tbl_snapshot WHERE STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $data["date"] . "','%m/%d/%Y')");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
    
            if($result->num_rows > 0){
                if(date("Y",strtotime($data["date"])) <> date("Y")){
                    $forYearDate = date("m/d/Y",strtotime("-1 year",strtotime($data["date"])));
    
                    $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monthlyfs' AND TABLE_NAME = 'tbl_" . date("Y",strtotime($forYearDate)) . "balance'");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();
    
                    if($row["count"] > 0){
                        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_beginperfundbalancetb");
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $this->conn->prepare("CREATE TABLE tbl_beginperfundbalancetb LIKE monthlyfs.tbl_" . date("Y",strtotime($forYearDate)) . "balance");
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $this->conn->prepare("INSERT INTO tbl_beginperfundbalancetb SELECT * FROM monthlyfs.tbl_" . date("Y",strtotime($forYearDate)) . "balance");
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_trialbalanceperfund");
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $this->conn->prepare("CREATE TABLE tbl_trialbalanceperfund LIKE tbl_trialbalance");
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $this->conn->prepare("INSERT INTO tbl_trialbalanceperfund SELECT * FROM tbl_trialbalance");
                        $stmt->execute();
                        $stmt->close();

                        $this->ComputeBeginBalance("tbl_beginperfundbalancetb",$data["date"]);
                        $this->ComputeTB("tbl_beginperfundbalancetb","tbl_trialbalanceperfund",$data["date"]);
    
                        $status = "SUCCESS";
                        $message = "Data has been retrieved";
                    }else{
                        $status = "NO_DATA";
                        $message = "No data to retrieve with the date supplied";
                    }
                }else{
    
                    $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_beginperfundbalancetb");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("CREATE TABLE tbl_beginperfundbalancetb LIKE tbl_beginningbalance");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("INSERT INTO tbl_beginperfundbalancetb SELECT * FROM tbl_beginningbalance");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_trialbalanceperfund");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("CREATE TABLE tbl_trialbalanceperfund LIKE tbl_trialbalance");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("INSERT INTO tbl_trialbalanceperfund SELECT * FROM tbl_trialbalance");
                    $stmt->execute();
                    $stmt->close();

                    $this->ComputeBeginBalance("tbl_beginperfundbalancetb",$data["date"]);
                    $this->ComputeTB("tbl_beginperfundbalancetb","tbl_trialbalanceperfund",$data["date"]);
    
                    $status = "SUCCESS";
                    $message = "Data has been retrieved";
                }
            }else{
                $status = "NO_DATA";
                $message = "No data to retrieve with the date supplied";
            }
        }
        

        echo json_encode(array(
            "STATUS" => $status,
            "MESSAGE" => $message
        ));
    }

    public function GenerateReport($data){
        $forFund = (isset($data["fund"])) ? str_replace(array(" ", "-"), "", strtolower($data["fund"])) : "";
        $cdate = date("m/d/Y");
        $reporttype = $data["type"];

        if($reporttype == "IS"){
            $TableIS = ($cdate == $data["date"]) ? "tbl_incomestatement".$forFund : "tbl_incomestatementtemp".$forFund;

            //$this->COMPACCTCONNECT();
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempincomestatement");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempincomestatement LIKE " . $TableIS . "");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempincomestatement SELECT * FROM " . $TableIS . "");
            $stmt->execute();
            $stmt->close();
        }else if($reporttype == "BS"){
            $TableBS = ($cdate == $data["date"]) ? "tbl_balancesheet".$forFund : "tbl_balancesheettemp".$forFund;

            //$this->COMPACCTCONNECT();
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempbalancesheet");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempbalancesheet LIKE " . $TableBS . "");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempbalancesheet SELECT * FROM " . $TableBS . "");
            $stmt->execute();
            $stmt->close();
        }else if($reporttype == "CF"){
            $TableCF = ($cdate == $data["date"]) ? "tbl_glcashflow".$forFund : "tbl_glcashflowtemp".$forFund;

            //$this->COMPACCTCONNECT();
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_gltempcashflow");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("CREATE TABLE tbl_gltempcashflow LIKE " . $TableCF . "");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("INSERT INTO tbl_gltempcashflow SELECT * FROM " . $TableCF . "");
            $stmt->execute();
            $stmt->close();
        }else if($reporttype == "TB"){
            $TableTB = ($cdate == $data["date"]) ? "tbl_trialbalance" : "tbl_trialbalanceperfund";

            //$this->COMPACCTCONNECT();
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
        
        echo json_encode(array(
            "STATUS" => "READY",
            "REPORT_TYPE" => $reporttype,
        ));
    }

    public function GenerateSummReport($data){
        $reporttype = $data["type"];
        $cdate = date("m/d/Y");

        if($reporttype == "SUMMIS"){
            $TableIS = ($cdate == $data["date"]) ? "tbl_summarizedincome" : "tbl_summarizedincometemp";

            //$this->COMPACCTCONNECT();
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempsummarizedincome");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempsummarizedincome LIKE " . $TableIS . "");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempsummarizedincome SELECT * FROM " . $TableIS . "");
            $stmt->execute();
            $stmt->close();
        }else if($reporttype == "SUMMBS"){
            $TableBS = ($cdate == $data["date"]) ? "tbl_summarizedbalance" : "tbl_summarizedbalancetemp";

            //$this->COMPACCTCONNECT();
            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempsummarizedbalance");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempsummarizedbalance LIKE " . $TableBS . "");
            $stmt->execute();
            $stmt->close();
    
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempsummarizedbalance SELECT * FROM " . $TableBS . "");
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(array(
            "STATUS" => "READY",
            "REPORT_TYPE" => $reporttype,
        ));
    }

    private function ComputeBeginBalance($TableBalance,$cdate){
        //$this->COMPACCTCONNECT();
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

    private function PreviousFSBalance($TableBalance,$cfdate){
        //$this->COMPACCTCONNECT();
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

        $this->DropCreateSnapshotYear($cfdate);
        $this->ComputeBeginBalance($TableBalance,$cfdate);
    }

    private function PopulateFS($TableIS,$TableBS,$TableCF,$TableBalance,$cdate){
        //$this->COMPACCTCONNECT();

        $this->DropCreateSnapshotYear($cdate);

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
    
    private function PerformFSComputation($TableIS,$TableBS,$TableCF,$TableBalance,$TablePrev){
        //$this->COMPACCTCONNECT();
        // INCOME STATEMENT
         
        $stmt = $this->conn->prepare("UPDATE " . $TableIS ." a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS ." WHERE SUBSTRING(acctno,1,1) = '4' AND acctno <> '4101' ) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS ." a, (SELECT ROUND(SUM(thisday),2) AS forday, ROUND(SUM(thismonth),2) AS formonth, ROUND(SUM(thisyear),2) AS foryear FROM " . $TableIS ." WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS ." a, (SELECT forrevenues.thisday - forexpenses.thisday AS forday, forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM " . $TableIS ." WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM " . $TableIS ." WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS ." a, (SELECT forexcess.thisday - (fortaxes.thisday + forincometax.thisday) AS forday, forexcess.thismonth - (fortaxes.thismonth + forincometax.thismonth) AS formonth, forexcess.thisyear - (fortaxes.thisyear + forincometax.thisyear) AS foryear FROM (SELECT * FROM " . $TableIS ." WHERE acctno = 'EXCESS OF REVENUE AFTER') AS forexcess, (SELECT * FROM " . $TableIS ." WHERE acctno = '6002') AS fortaxes, (SELECT * FROM " . $TableIS ." WHERE acctno = '6003') AS forincometax) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'AFTER TAX'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableIS ." a, (SELECT fortotal.thisday + forgrants.thisday AS forday, fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS ." WHERE acctno = 'AFTER TAX') AS fortotal, (SELECT * FROM " . $TableIS ." WHERE acctno = '4101') AS forgrants) b SET a.thisday = ROUND(b.forday,2), a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE tbl_excessrevenue a, (SELECT fortotal.thisyear + forgrants.thisyear AS foryear FROM (SELECT * FROM " . $TableIS ." WHERE acctno = 'AFTER TAX') AS fortotal, (SELECT * FROM " . $TableIS ." WHERE acctno = '4101') AS forgrants) b SET a.amount = ROUND(b.foryear,2) WHERE fund = 'CONSOLIDATED'");
        $stmt->execute();
        $stmt->close();

        // BALANCE SHEET

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT forduefrom.amount3 - fordueto.amount3 AS foroffset FROM (SELECT * FROM " . $TableBS ." WHERE acctno = '1401') AS forduefrom, (SELECT * FROM " . $TableBS ." WHERE acctno = '2401') AS fordueto) b SET a.amount3 = ROUND(b.foroffset,2) WHERE acctno = '1401'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." SET amount3 = 0 WHERE acctno = '2401'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT * FROM tbl_excessrevenue WHERE fund = 'CONSOLIDATED') b SET a.amount2 = ROUND(b.amount,2) WHERE acctno = 'ADD EXCESS'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,3) = '111') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1114'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,3) = '112') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1129'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,3) = '113' OR acctno = '1146' OR acctno = '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1139'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,3) = '114' AND acctno <> '1146' AND acctno <> '1147') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1145'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,2) = '11') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1603' OR acctno = '1703') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1703'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1604' OR acctno = '1704') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1704'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1605' OR acctno = '1705') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1705'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1606' OR acctno = '1706') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1706'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1607' OR acctno = '1707') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1707'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1608' OR acctno = '1708') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1708'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount1) AS foramount FROM " . $TableBS ." WHERE acctno = '1610' OR acctno = '1710') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = '1710'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount, SUM(amount3) AS forland FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,2) = '16' OR SUBSTRING(acctno,1,2) = '17') b SET a.amount3 = ROUND(b.foramount + b.forland,2) WHERE acctno = 'TOTAL PROPERTY'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,2) = '19') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '1909'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS ." WHERE acctno <> '1601' AND acctno <> '1602' AND (acctno = 'TOTAL PROPERTY' OR SUBSTRING(acctno,1,1) = '1')) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL ASSET'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS ." WHERE acctno = '2151') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS ." WHERE acctno = '2152') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2152'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT forbeneficiary.amount2 - forremittance.amount2 AS foramount FROM (SELECT amount2 FROM " . $TableBS ." WHERE acctno = '2154') AS forbeneficiary, (SELECT amount2 FROM " . $TableBS ." WHERE acctno = '2156') AS forremittance) b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = '2156'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,2) = '21' AND acctno <> '2170') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL CURRENTL'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS ." WHERE SUBSTRING(acctno,1,1) = '2') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE acctno = '3001' OR acctno = 'ADD EXCESS') b SET a.amount2 = ROUND(b.foramount,2) WHERE acctno = 'ACCUMULATED'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS ." WHERE acctno = '3002' OR acctno = '3003' OR acctno = '3004' OR acctno = '3005' OR acctno = 'ACCUMULATED') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'FUND END'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableBS ." a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS ." WHERE acctno = 'TOTAL LIABILITIES' OR acctno = 'FUND END') b SET a.amount3 = ROUND(b.foramount,2) WHERE acctno = 'TOTAL FUND'");
        $stmt->execute();
        $stmt->close();

        // CASH FLOW

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'CASH AND CASH EQUIVALENTS') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH EQUIVALENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'FUND BALANCE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev . " WHERE category = 'FUND BALANCE') c, (SELECT thismonth FROM " . $TableIS ." WHERE acctno = 'TOTAL') d SET a.amount = ((b.foramount - c.foramount) * -1) + d.thismonth WHERE indicatorno = 'FUND BALANCE'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'LOANS RECEIVABLE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'LOANS RECEIVABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LR'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'OTHER RECEIVABLES') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'OTHER RECEIVABLES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER RECEIVABLES'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PREPAID'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'CURRENT LIABILITIES') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'CURRENT LIABILITIES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'CURRENT LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'FUND HELD IN TRUST') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'FUND HELD IN TRUST') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'FUND HELD'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'INVESTMENTS') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'INVESTMENTS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'INVESTMENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'RECEIVABLES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PROPERTY'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'OTHER ASSETS') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'OTHER ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER ASSET'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PAYABLES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'LOANS PAYABLE') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'LOANS PAYABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LOAN PAYABLES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(consolidated) AS foramount FROM " . $TableBalance ." WHERE category = 'LONG-TERM DEBT') b, (SELECT SUM(consolidated) AS foramount FROM " . $TablePrev ." WHERE category = 'LONG-TERM DEBT') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LONG TERM'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(amount) AS foramount FROM " . $TableCF ." WHERE indicatorno = 'FUND BALANCE' OR indicatorno = 'LR' OR indicatorno = 'OTHER RECEIVABLES' OR indicatorno = 'PREPAID' OR indicatorno = 'CURRENT LIABILITIES' OR indicatorno = 'FUND HELD') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET OPERATION'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(amount) AS foramount FROM " . $TableCF ." WHERE indicatorno = 'INVESTMENT' OR indicatorno = 'RECEIVABLES' OR indicatorno = 'PROPERTY' OR indicatorno = 'OTHER ASSET') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET INVESTMENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(amount) AS foramount FROM " . $TableCF ." WHERE indicatorno = 'PAYABLES' OR indicatorno = 'LOAN PAYABLES' OR indicatorno = 'LONG TERM') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'NET FINANCING'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $TableCF ." a, (SELECT SUM(amount) AS foramount FROM " . $TableCF ." WHERE indicatorno = 'CASH EQUIVALENT' OR indicatorno = 'NET OPERATION' OR indicatorno = 'NET INVESTMENT' OR indicatorno = 'NET FINANCING') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH ENDING'");
        $stmt->execute();
        $stmt->close();
    }

    private function PopulatePerFundFS($TableIS,$TableBS,$TableCF,$TableBalance,$cdate){
        $branch = "CAB";
        // $stmt = $this->conn->prepare("");
        // $stmt->execute();
        // $stmt->close();

        $this->DropCreateSnapshotYear($cdate);

        $stmt = $this->conn->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB1 . "' AND TABLE_NAME = '" . $TableBalance . "' AND (COLUMN_NAME <> 'id' AND COLUMN_NAME <> 'cdate' AND COLUMN_NAME <> 'acctno' AND COLUMN_NAME <> 'accttitle' AND COLUMN_NAME <> 'slno' AND COLUMN_NAME <> 'slname' AND COLUMN_NAME <> 'category' AND COLUMN_NAME <> 'consolidated')");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $forFund = $row["COLUMN_NAME"];

            $qry = "UPDATE " . $TableIS . "" . $forFund . " SET cdate = '" . $cdate . "', fund = '" . strtoupper($row["COLUMN_NAME"]) . "', branch = '" . $branch . "', thisday = 0, thismonth = 0, thisyear = 0";

            $stmt = $this->conn->prepare($qry);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " SET cdate = '" . $cdate . "', fund = '" . strtoupper($row["COLUMN_NAME"]) . "', branch = '" . $branch . "', amount1 = 0, amount2 = 0, amount3 = 0");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . "" . $forFund . " SET cdate = '" . $cdate . "', fund = '" . strtoupper($row["COLUMN_NAME"]) . "', branch = '" . $branch . "', amount = 0");
            $stmt->execute();
            $stmt->close();

            // This Day

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "" . $forFund . "");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thisday, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND STR_TO_DATE(cdate,'%m/%d/%Y') = STR_TO_DATE('" . $cdate . "','%m/%d/%Y') GROUP BY acctno");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thisday = ROUND(end.thisday,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
            $stmt->execute();
            $stmt->close();

            // This Month

            $stmt = $this->conn->prepare("DROP TABLE IF EXISTS tbl_tempis");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("CREATE TABLE tbl_tempis LIKE " . $TableIS . "" . $forFund . "");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("INSERT INTO tbl_tempis(thismonth, acctno, accttitle) SELECT ROUND(SUM(debit-credit),2) AS foramount, acctno, accttitle FROM tbl_snapshotyear WHERE REPLACE(REPLACE(fund,' ',''),'-','') = '" . $forFund . "' AND STR_TO_DATE(cdate,'%m/%d/%Y') <= STR_TO_DATE('" . $cdate . "','%m/%d/%Y') AND MONTH(STR_TO_DATE(cdate,'%m/%d/%Y')) = MONTH(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) AND YEAR(STR_TO_DATE(cdate,'%m/%d/%Y')) = YEAR(STR_TO_DATE('" . $cdate . "','%m/%d/%Y')) GROUP BY acctno");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . "  beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . "  beg INNER JOIN tbl_tempis end ON beg.acctno = end.acctno SET beg.thismonth = ROUND(end.thismonth,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
            $stmt->execute();
            $stmt->close();

            // This Year

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated * -1,2) WHERE SUBSTRING(beg.acctno,1,1) = '4'");
            $stmt->execute();
            $stmt->close();
            
            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '5'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableIS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.thisyear = ROUND(end.consolidated,2) WHERE SUBSTRING(beg.acctno,1,1) = '6'");
            $stmt->execute();
            $stmt->close();

            // BS

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT1'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT2'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated,2) WHERE beg.amtcategory = 'AMOUNT3'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount1 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT1'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount2 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT2'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableBS . "" . $forFund . " beg INNER JOIN " . $TableBalance . " end ON beg.acctno = end.acctno SET beg.amount3 = ROUND(end.consolidated * -1,2) WHERE ((beg.acctno <> '2152' AND beg.acctno <> '2156') AND SUBSTRING(beg.acctno,1,1) = '2' OR SUBSTRING(beg.acctno,1,1) = '3') AND beg.amtcategory = 'AMOUNT3'");
            $stmt->execute();
            $stmt->close();
        }
    }

    private function PerformPerFundFSComputation($TableIS,$TableBS,$TableBalance,$TableCF,$TablePrev){
        //$this->COMPACCTCONNECT();
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
            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'CASH AND CASH EQUIVALENTS') b SET a.amount = ROUND(b.foramount,2) WHERE indicatorno = 'CASH EQUIVALENT'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'FUND BALANCE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'FUND BALANCE') c, (SELECT thismonth FROM " . $TableIS . $row["COLUMN_NAME"] . " WHERE acctno = 'TOTAL') d SET a.amount = ((b.foramount - c.foramount) * -1) + d.thismonth WHERE indicatorno = 'FUND BALANCE'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'LOANS RECEIVABLE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'LOANS RECEIVABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LR'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'OTHER RECEIVABLES') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'OTHER RECEIVABLES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER RECEIVABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'PREPAID EXPENSE AND OTHER CURRENT ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PREPAID'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'CURRENT LIABILITIES') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'CURRENT LIABILITIES') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'CURRENT LIABILITIES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'FUND HELD IN TRUST') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'FUND HELD IN TRUST') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'FUND HELD'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'INVESTMENTS') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'INVESTMENTS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'INVESTMENT'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'RECEIVABLE FROM BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'RECEIVABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'PROPERTY AND EQUIPMENT' OR category = 'ACCUMULATED DEPRECIATION') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PROPERTY'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'OTHER ASSETS') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'OTHER ASSETS') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'OTHER ASSET'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'PAYABLE TO BRANCHES/HEAD OFFICE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'PAYABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'LOANS PAYABLE') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'LOANS PAYABLE') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LOAN PAYABLES'");
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE " . $TableCF . $row["COLUMN_NAME"] . " a, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TableBalance . " WHERE category = 'LONG-TERM DEBT') b, (SELECT SUM(" . $row["COLUMN_NAME"] . ") AS foramount FROM " . $TablePrev . " WHERE category = 'LONG-TERM DEBT') c SET a.amount = ROUND(b.foramount - c.foramount,2) * -1 WHERE indicatorno = 'LONG TERM'");
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

    private function SummaryFS($SummTableIS,$SummTableBS,$TableIS,$TableBS,$cdate){
        //$this->COMPACCTCONNECT();
        $branch = "CAB";
        $forTotalFund = 0;

        // Income Statement Summary
        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " SET cdate = '" . $cdate . "', branch = '" . $branch . "', thismonth = 0, thisyear = 0");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4201') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4201'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4205') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4205'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4208') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4208'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4299') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4299'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $SummTableIS . " WHERE SUBSTRING(acctno,1,2) = '42') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL REVENUES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5100') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5100'");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5200') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5200'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5300') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5300'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5504') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5504'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5400') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5400'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '5500') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '5500'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $SummTableIS . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT forrevenues.thismonth - forexpenses.thismonth AS formonth, forrevenues.thisyear - forexpenses.thisyear AS foryear FROM (SELECT * FROM " . $SummTableIS . " WHERE acctno = 'TOTAL REVENUES') AS forrevenues, (SELECT * FROM " . $SummTableIS . " WHERE acctno = 'TOTAL EXPENSES') AS forexpenses) b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'EXCESS OF REVENUE AFTER'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $TableIS . " WHERE category2 = '4101') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = '4101'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT SUM(thismonth) AS formonth, SUM(thisyear) AS foryear FROM " . $SummTableIS . " WHERE SUBSTRING(acctno,1,1) = '5') b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL EXPENSES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableIS . " a, (SELECT fortotal.thismonth + forgrants.thismonth AS formonth, fortotal.thisyear - forgrants.thisyear AS foryear FROM (SELECT * FROM " . $SummTableIS . " WHERE acctno = 'EXCESS OF REVENUE AFTER') AS fortotal, (SELECT * FROM " . $SummTableIS . " WHERE acctno = '4101') AS forgrants) b SET a.thismonth = ROUND(b.formonth,2), a.thisyear = ROUND(b.foryear,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();

        // Balance Sheet Summary
        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " SET cdate = '" . $cdate . "', branch = '" . $branch . "', amount = 0");
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " SET amount = '" . $forTotalFund . "' WHERE acctno = '3001'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1110') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1110'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1114') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1114'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1120') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1120'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1140') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1140'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE SUBSTRING(acctno,1,2) = '11') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-CURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1200') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1200'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1600') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1600'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '1900') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '1900'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE acctno = '1200' OR acctno = '1600' OR acctno = '1900') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-NONCURRENT'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE acctno = 'TOTAL-CURRENT' OR acctno = 'TOTAL-NONCURRENT') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-ASSETS'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = 'ACCOUNTS-PAYABLE') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2100'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '2150') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2150'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '2180') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2180'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE SUBSTRING(acctno,1,2) = '21') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-CURLIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount3) AS foramount FROM " . $TableBS . " WHERE sumcategory = '2200') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '2200'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE acctno = 'TOTAL-CURLIABILITIES' OR acctno = '2200') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-LIABILITIES'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE acctno = 'ACCUMULATED') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '3001'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount2) AS foramount FROM " . $TableBS . " WHERE sumcategory = '3002') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = '3002'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE SUBSTRING(acctno,1,1) = '3' OR acctno = 'UNREALIZED') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL-FUNDBALANCE'");
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE " . $SummTableBS . " a, (SELECT SUM(amount) AS foramount FROM " . $SummTableBS . " WHERE acctno = 'TOTAL-LIABILITIES' OR acctno = 'TOTAL-FUNDBALANCE') b SET a.amount = ROUND(b.foramount,2) WHERE acctno = 'TOTAL'");
        $stmt->execute();
        $stmt->close();
    }

    private function ComputeTB($TableBalance,$TableTB,$cdate){
        //$this->COMPACCTCONNECT();
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

    private function DropCreateSnapshotYear($cdate){
        //$this->COMPACCTCONNECT();
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
        //$this->COMPACCTCONNECT();
        $query = "DROP TABLE IF EXISTS tbl_incomestatementtemp ; DROP TABLE IF EXISTS tbl_balancesheettemp ; DROP TABLE IF EXISTS tbl_glcashflowtemp ; DROP TABLE IF EXISTS tbl_summarizedincometemp ; DROP TABLE IF EXISTS tbl_summarizedbalancetemp ; CREATE TABLE tbl_incomestatementtemp LIKE tbl_incomestatement ; CREATE TABLE tbl_balancesheettemp LIKE tbl_balancesheet ; CREATE TABLE tbl_glcashflowtemp LIKE tbl_glcashflow ; CREATE TABLE tbl_summarizedbalancetemp LIKE tbl_summarizedbalance ; CREATE TABLE tbl_summarizedincometemp LIKE tbl_summarizedincome ; INSERT INTO tbl_incomestatementtemp SELECT * FROM tbl_incomestatement ; INSERT INTO tbl_balancesheettemp SELECT * FROM tbl_balancesheet ; INSERT INTO tbl_glcashflowtemp SELECT * FROM tbl_glcashflow ; INSERT INTO tbl_summarizedbalancetemp SELECT * FROM tbl_summarizedbalance ; INSERT INTO tbl_summarizedincometemp SELECT * FROM tbl_summarizedincome";
        mysqli_multi_query($this->conn,$query);
        mysqli_close($this->conn);
    }

    private function StringFund($database,$table){
        //$this->COMPACCTCONNECT();
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
        //$this->COMPACCTCONNECT();
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
        //$this->COMPACCTCONNECT();
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
        //$this->COMPACCTCONNECT();
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