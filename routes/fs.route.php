<?php
include_once("../process/fs.process.php");
include_once("../reports/fs.reports.php");
$process = new Process();
$reports = new Reports();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "RetrieveData"){
    $process->RetrieveData($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "RetrieveDataTB"){
    $process->RetrieveDataTB($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "GenerateReport"){
    $process->GenerateReport($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "GenerateSummReport"){
    $process->GenerateSummReport($_POST);
}

if(isset($_GET["REPORT_TYPE"]) && $_GET["REPORT_TYPE"] == "IS"){
    $reports->DisplayReportIS($_GET["FUND"]);
}

if(isset($_GET["REPORT_TYPE"]) && $_GET["REPORT_TYPE"] == "BS"){
    $reports->DisplayReportBS($_GET["FUND"]);
}

if(isset($_GET["REPORT_TYPE"]) && $_GET["REPORT_TYPE"] == "CF"){
    $reports->DisplayReportCF($_GET["FUND"]);
}

if(isset($_GET["REPORT_TYPE"]) && $_GET["REPORT_TYPE"] == "TB"){
    $reports->DisplayReportTB($_GET["FUND"]);
}


if(isset($_GET["REPORT_TYPE"]) && $_GET["REPORT_TYPE"] == "SUMMIS"){
    $reports->DisplaySummReportIS($_GET["FUND"]);
}

if(isset($_GET["REPORT_TYPE"]) && $_GET["REPORT_TYPE"] == "SUMMBS"){
    $reports->DisplaySummReportBS($_GET["FUND"]);
}
?>