<?php
include_once("../process/jv.process.php");
include_once("../reports/jv.reports.php");
$process = new Process();
$reports = new Reports();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSL"){
    $process->LoadSL($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SelectFund"){
    $process->SelectFund($_POST,"");
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadBankSetup"){
    $process->LoadBankSetup();
}

if(isset($_POST["action"]) && $_POST["action"] == "Save"){
    $process->Save($_POST);
}

if(isset($_GET["type"]) && $_GET["type"] == "jv"){
    $reports->JVReport($_SESSION["JVNO"]);
}
?>