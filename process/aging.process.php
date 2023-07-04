<?php 
include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database 
{
    public function Initialize(){
        $stmt = $this->conn->prepare("SELECT * FROM tbl_aging");
        $stmt->execute();
        $result = $stmt->get_result();
        $Aging = $this->FillRow($result);
        $stmt->close();

        echo json_encode(array(
            "AGING" => $Aging,
        ));
    }

    public function UpdateAging($data){
        // //$this->COMPACCTCONNECT();

        $error = 0;
        $message = "";

        $lastupdate = "";
        $stmt = $this->conn->prepare("SELECT lastupdate FROM tbl_aging LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $lastupdate = $row["lastupdate"];
        }

        $lastclosingdate = "";
        $holidayenabled = "";
        $stmt = $this->conn->prepare("SELECT itemname FROM tbl_maintenance_lists WHERE itemtype = 'LASTCLOSINGDATE'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $lastclosingdate = $row["itemname"];
        }

        $stmt = $this->conn->prepare("SELECT itemname FROM tbl_maintenance_lists WHERE itemtype = 'HOLIDAYENABLED'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($row = $result->fetch_assoc()){
            $holidayenabled = $row["itemname"];
        }

        if(date("m/d/Y",strtotime($data["asof"])) > date("m/d/Y",strtotime($lastupdate))){
            if(date("m/d/Y",strtotime($lastupdate)) <> date("m/d/Y",strtotime($lastclosingdate))){
                $message = "Previous transaction date was not yet close.";
                $error = 1;
            }
        }

        if($error == 0){
            $stmt = $this->conn->prepare("SELECT * FROM tbl_holidays");
            $stmt->execute();
            $HolidaysTB = $stmt->get_result();
            $stmt->close();

            $stmt = $this->conn->prepare("SELECT * FROM tbl_aging WHERE lastupdate = '-' OR STR_TO_DATE(lastupdate,'%m/%d/%Y') < STR_TO_DATE('".$data["asof"]."','%m/%d/%Y')");
            $stmt->execute();
            $AgingTb = $stmt->get_result();
            $stmt->close();

            if($AgingTb->num_rows > 0){
                while ($rowAging = $AgingTb->fetch_assoc()) {
                    $customerno = $rowAging["customerno"];
                    $loanid = $rowAging["loanid"];
                    
                    $terms = $rowAging["terms"];
                    $mode = $rowAging["mode"];
                    $daterelease = $rowAging["transactiondate"];
                    $transactionno = $rowAging["transactionno"];
                    $drno = $rowAging["drno"];
                    $sino = $rowAging["sino"];
    
                    $totalamount = $rowAging["totalamount"];
                    $totalpaid = $rowAging["totalpaid"];
                    $interest = $rowAging["interest"];
                    $balance = floatval($totalamount) - floatval($totalpaid);
                    $penalty = $rowAging["penalty"];
                    $principal = $rowAging["principal"];
    
                    $principalpaid = 0;
                    $payments = "";
                    $interval = "";
                    $intervalcount = 0;
                    $divisor = 0;
                    $multiterm = 0;
                    $principaldue = 0;
    
                    $v130 = 0;
                    $v3160 = 0;
                    $v6190 = 0;
                    $v91120 = 0;
                    $v121150 = 0;
                    $v151180 = 0;
                    $vOver120 = 0;
                    $vOver180 = 0;
                    $arrears = 0;
                    $arrearsaccu = 0;
                    $daysinarrears = 0;
                    $daysoldestarrears = 0;
                    $unpaiddues = 0;
                    $advances = 0;
    
                    // Get Payments in CRB
                    $stmt = $this->conn->prepare("SELECT SUM(ABS(SLDrCr)) as AmountPaid FROM tbl_cashreceipt WHERE slno = ? and loanid = ?");
                    $stmt->bind_param("ss",$customerno,$loanid);
                    $stmt->execute();
                    $PaymentsResult = $stmt->get_result();
                    $stmt->close();
                    while ($PaymentRow = $PaymentsResult->fetch_assoc()) {
                        $principalpaid = $PaymentRow["AmountPaid"];
                    }

                    switch ($mode) {
                        case 'DAILY':
                            $divisor = 22;
                            $payments = floatval($terms) * $divisor;
                            $interval = "days";
                            $intervalcount = 1;
                            $amortization = $totalamount / $divisor;
                            break;
                        case 'WEEKLY':
                            $divisor = 4;
                            $payments = floatval($terms) * $divisor;
                            $interval = "days";
                            $intervalcount = 7;
                            $amortization = ($totalamount / 22) * 7;
                            break;
                        case 'MONTHLY':
                            $divisor = 1;
                            $payments = floatval($terms) * $divisor;
                            $interval = "months";
                            $intervalcount = 1;
                            $amortization = $totalamount / $terms;
                            break;
                        case 'SEMI-MONTHLY':
                            $divisor = 2;
                            $payments = floatval($terms) * $divisor;
                            $interval = "days";
                            $intervalcount = 15;
                            $amortization = ($totalamount / $terms) / 2;
                            break;
                        case 'ANNUAL':
                            $divisor = 1 / 12;
                            $payments = floatval($terms) / 12;
                            $interval = "years";
                            $intervalcount = 1;
                            $amortization = ($totalamount / $terms) * 12;
                            break;
                        case 'SEMI-ANNUAL':
                            $divisor = 1 / 6;
                            $payments = floatval($terms) / 6;
                            $interval = "days";
                            $intervalcount = 180;
                            $amortization = (($totalamount / $terms) * 12) / 2;
                            break;
                        case 'QUARTERLY':
                            $divisor = 1 / 3;
                            $payments = floatval($terms) / 3;
                            $interval = "days";
                            $intervalcount = 90;
                            $amortization = (($totalamount / $terms) * 12) / 4;
                            break;
                        case 'LUMPSUM':
                            $divisor = 1;
                            $payments = 1;
                            $interval = "days";
                            $intervalcount = floatval($terms) * 30;
                            $multiterm = $terms;
                            $amortization = $totalamount * 1;
                            break;
                        default:
                            # code...
                            break;
                    }
    
                    $monthlyprincipal = round(floatval($totalamount) / floatval($terms));
                    $yearlyprincipal = $monthlyprincipal * 12;
                    $duedate = "";
                    $originalduedate = "";
                    $forcheckday = false;
                    $duedateforaging = "";
                    $duedates = [];
                    $duedatecount = 0;
                    $datesdue = 0;
                    $lastduedatepaid = "";
                    $lastduedate = $daterelease;
    
                    while (strtotime($lastduedate) <= strtotime(date("m/d/Y",strtotime($data["asof"])))) {
                        $duedate = date("m/d/Y",strtotime($lastduedate . "+" . $intervalcount . " " . $interval));    
                        $originalduedate = $duedate;
    
                        if($holidayenabled == "YES"){
                            Jump:
                            while ($row = $HolidaysTB->fetch_assoc()) {
                                if(date("m/d/Y",strtotime($row["Date"])) == $duedate){
                                    if($mode == "DAILY"){
                                        $duedate = date("m/d/Y",strtotime($duedate . "+1 days"));
                                        $originalduedate = $duedate;
                                        Goto Jump;
                                    }
                                }
                            }
                        }
    
                        if(strtotime($duedate) <= strtotime(date("m/d/Y",strtotime($data["asof"])))){
                            CheckDay:
                            if(date("w",strtotime($duedate)) == 0 || date("w",strtotime($duedate)) == 6){
                                $duedate = date("m/d/Y",strtotime($duedate . "+1 days"));
                                if($mode == "DAILY"){
                                    $originalduedate = $duedate;
                                }
    
                                $forcheckday = true;
                                Goto CheckDay;
                            }else{
                                if($forcheckday == true){
                                    $forcheckday = false;
                                    if($mode == "DAILY"){
                                        Goto Jump;
                                    }
                                }
                            }
    
                            $duedateforaging = $duedate;
                            $duedates[] = $duedate;
                            $duedatecount += 1;
    
                            if(date("d",strtotime($duedate)) <= date("d",strtotime($data["asof"]))){
                                $datesdue += 1;

                                $principaldue = (floatval($principalpaid - $totalamount) < floatval($amortization)) ? (floatval($principaldue) + floatval($principalpaid - $totalamount)) : (floatval($principaldue) + floatval($amortization));
    
                                if(floatval($principalpaid) >= floatval($principaldue)){
                                    $lastduedatepaid = $duedate;
                                }else{
                                    $date1 = new DateTime($duedate);
                                    $date2 = new DateTime($data["asof"]);
                                    $interva1 = $date1->diff($date2);
                                    $arrears = floatval($principaldue) - floatval($principalpaid);
                                    $daysinarrears = floatval($interva1->days) + 1;
                                    $daysoldestarrears = ($daysinarrears > $daysoldestarrears) ? $daysinarrears : $daysoldestarrears;
                                    $arrears = floatval($principaldue - $principalpaid) - $arrearsaccu;
                                    $arrearsaccu = $arrearsaccu + $arrears;
                                    $daysinarrears = floatval($interva1->days);
                                    if($daysinarrears >= 1 && $daysinarrears <= 30){
                                        $v130 += $arrears;
                                    }else if($daysinarrears >= 31 && $daysinarrears <= 60){
                                        $v3160 += $arrears;
                                    }else if($daysinarrears >= 61 && $daysinarrears <= 90){
                                        $v6190 += $arrears;
                                    }else if($daysinarrears >= 91 && $daysinarrears <= 120){
                                        $v91120 += $arrears;
                                    }else if($daysinarrears >= 121 && $daysinarrears <= 150){
                                        $v121150 += $arrears;
                                    }else if($daysinarrears >= 151 && $daysinarrears <= 180){
                                        $v151180 += $arrears;
                                    }else{
                                        $vOver180 += $arrears;
                                    }
                                }
                            }
                        }

                        $lastduedate = $originalduedate;
                    }
    
                    $unpaiddues = 0;
                    $vOver120 = floatval($v121150 + $v151180 + $vOver180);
                    
                    $principaldue = ($principaldue > $totalamount) ? $totalamount : $principaldue;
                    $principaldue = ($principalpaid > 0) ? ($principaldue - $principalpaid) : $principaldue;

                    $arrears = ($arrears >= 0) ? ($principaldue - $principalpaid) : ($principaldue + $principalpaid);
    
                    if($arrears < 0){
                        $arrears = 0;
                        $advances = floatval($principaldue - $principalpaid);
                    }else if($arrears == 0){
                        $arrears = 0;
                    }else{
                        $advances = 0;
                        $datesdue = ($datesdue > $payments) ? $payments : $datesdue-1;
                        if(strtotime($duedates[$datesdue]) == strtotime(date("m/d/Y",strtotime($data["asof"])))){
                            $arrears = $this->RoundNumber(floatval($arrears) - floatval($principalpaid));
                            if($arrears < 0){
                                $arrears = 0;
                                $advances = floatval($principaldue - $principalpaid);
                            }else if($arrears == 0){
                                $advances = 0;
                            }
                        }
                        $duedates = [];
                    }
    
                    $balance = (($totalamount - $principalpaid) > 0) ? $this->RoundNumber(floatval($totalamount - $principalpaid)) : 0;
                    $principaldue = ($principaldue > 0) ? $this->RoundNumber(floatval($principaldue)) : 0;
                    
                    $stmt = $this->conn->prepare("UPDATE tbl_aging SET totalpaid = '".$principalpaid."', balance = '".$balance."', principal = '".$principaldue."', 1_30 = '".$v130."', 31_60 = '".$v3160."', 61_90 = '".$v6190."', 91_120 = '".$v91120."', 121_150 = '".$v121150."', asof = '".date("m/d/Y",strtotime($data["asof"]))."', lastupdate = '".date("m/d/Y",strtotime($data["asof"]))."' WHERE customerno = '".$customerno."' AND loanid = '".$loanid."'");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("UPDATE tbl_pos_receipts SET balancedue = '".$principaldue."', duedate = '".$duedate."' WHERE transactionno = '".$transactionno."' AND drno = '".$drno."' AND sinumber = '".$sino."'");
                    $stmt->execute();
                    $stmt->close();

                    $updateQuery = "";
                    $stmt = $this->conn->prepare("SELECT COUNT(*) as countexists FROM tbl_pos_transactions_today WHERE transactionno = '".$transactionno."' AND drno = '".$drno."' AND sinumber = '".$sino."'");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();
                    if($row["countexists"] > 0){
                        $updateQuery = "UPDATE tbl_pos_transactions_today SET balancedue = '".$principaldue."', duedate = '".$duedate."' WHERE transactionno = '".$transactionno."' AND drno = '".$drno."' AND sinumber = '".$sino."'";
                    }else{
                        $updateQuery = "UPDATE tbl_pos_transactions_main SET balancedue = '".$principaldue."', duedate = '".$duedate."' WHERE transactionno = '".$transactionno."' AND drno = '".$drno."' AND sinumber = '".$sino."'";
                    }

                    $stmt = $this->conn->prepare($updateQuery);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $this->conn->prepare("UPDATE tbl_maintenance_lists SET itemname = '".date("m/d/Y",strtotime($data["asof"]))."' WHERE itemtype = 'LASTCLOSINGDATE'");
                    $stmt->execute();
                    $stmt->close();
    
                    $message = "Current Aging is updated to date.";
                }
            }else{
                $message = "Current Aging is empty. Nothing to update.";
            }
        }

        echo json_encode(array(
            "STATUS" => ($error == 0) ? "SUCCESS" : "ERROR",
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

    private function RoundNumber($number){
        $decimals = floor($number * 100) % 100;
        $formattedNumber = number_format($number, 2, '.', '');
        $formattedNumberWithoutRounding = floor($formattedNumber) + $decimals / 100;
        return $formattedNumberWithoutRounding;
    }
}
?>