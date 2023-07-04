<?php
include_once("../database/connection.php");
include_once("../process/pre-function.process.php");

$process = new PredefinedProcess();

if(isset($_POST["action"]) && $_POST["action"] == "LoadGJ"){
    $process->LoadGJ();
}

if(isset($_POST["action"]) && $_POST["action"] == "ViewGJEntry"){
    $process->ViewGJEntry($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ConfirmEntryGJ"){
    $process->ConfirmEntryGJ($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCRB"){
    $process->LoadCRB();
}

if(isset($_POST["action"]) && $_POST["action"] == "ViewCRBEntry"){
    $process->ViewCRBEntry($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ConfirmEntryCRB"){
    $process->ConfirmEntryCRB($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCD"){
    $process->LoadCD();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadBankSetup"){
    $process->LoadBankSetup();
}

if(isset($_POST["action"]) && $_POST["action"] == "ViewCDEntry"){
    $process->ViewCDEntry($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ConfirmEntryCD"){
    $process->ConfirmEntryCD($_POST);
}
?>