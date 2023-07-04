<?php
include_once("../process/returns.process.php");
$process = new Process();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadPRNumber"){
    $process->LoadPRNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPendingPurchaseReturns"){
    $process->LoadPendingPurchaseReturns();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPurchaseReturns"){
    $process->LoadPurchaseReturns();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPendingPurchaseReturn"){
    $process->LoadPendingPurchaseReturn($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPurchaseReturn"){
    $process->LoadPurchaseReturn($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadReceivedPurchaseOrder"){
    $process->LoadReceivedPurchaseOrder($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "UpdateTotalPrice"){
    $process->UpdateTotalPrice($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitPurchaseReturn"){
    $process->SubmitPurchaseReturn($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "DeletePurchaseReturn"){
    $process->DeletePurchaseReturn($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitPendingPurchaseReturn"){
    $process->SubmitPendingPurchaseReturn($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitAPendingPurchaseReturn"){
    $process->SubmitAPendingPurchaseReturn($_POST);
}