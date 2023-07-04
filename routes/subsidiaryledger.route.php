<?php
include_once("../process/subsidiaryledger.process.php");
include_once("../reports/subsidiaryledger.reports.php");
$process = new Process();
$reports = new Reports();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "SelectCustomers"){
    $process->SelectCustomers($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadBooks"){
    $process->LoadBooks($_POST);
}
?>