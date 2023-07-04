<?php
include_once("../process/purchases.process.php");
include_once("../reports/generatebarcode.reports.php");

$process = new Purchase();
$barcode = new Barcode();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadPONumber"){
    $process->LoadPONumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadReceivingNumber"){
    $process->LoadReceivingNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadConfigCounts"){
    $process->LoadConfigCounts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadAllPurchaseOrderSuppliers"){
    $process->LoadAllPurchaseOrderSuppliers();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPurchaseOrders"){
    $process->LoadPurchaseOrders();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadReceivedPurchaseOrders"){
    $process->LoadReceivedPurchaseOrders();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPurchaseOrder"){
    $process->LoadPurchaseOrder($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadReceivedPurchaseOrder"){
    $process->LoadReceivedPurchaseOrder($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "DeleteProducFromPurchaseOrder"){
    $process->DeleteProducFromPurchaseOrder($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "UpdateTotalPrice"){
    $process->UpdateTotalPrice($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitPurchaseOrder"){
    $process->SubmitPurchaseOrder($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdatePurchaseOrder"){
    $process->SubmitUpdatePurchaseOrder($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ReceivePurchaseOrder"){
    $process->ReceivePurchaseOrder($_POST);
}

if(isset($_GET["type"]) && $_GET["type"] == "generatebarcode"){
    if (isset($_SESSION["receivingno"]) == false) {
        echo "<script> close(); </script>";
    }
    $barcode->GenerateProductBarcodes($_SESSION["receivingno"]);
}