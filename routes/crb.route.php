<?php
include_once("../process/crb.process.php");
include_once("../reports/crb.reports.php");
$process = new Process();
$reports = new Reports();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "SelectSLType"){
    $process->SelectSLType($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SelectSLName"){
    $process->SelectSLName($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SelectOR"){
    $process->SelectOR($_POST,"ECHO");
}

if(isset($_POST["action"]) && $_POST["action"] == "SelectAR"){
    $process->SelectAR($_POST,"ECHO");
}

if(isset($_POST["action"]) && $_POST["action"] == "SaveEntry"){
    $process->SaveEntry($_POST);
}

if(isset($_GET["type"]) && $_GET["type"] == "crb"){
    $reports->CRBReport($_SESSION["orno"],$_SESSION["ordate"],$_SESSION["fund"]);
}
?>