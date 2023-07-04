<?php
include_once("../process/pos.process.php");
$process = new POS();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadTransactionNo"){
    $process->LoadTransactionNo();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPOSReceipts"){
    $process->LoadPOSReceipts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPOSPaymentReceipts"){
    $process->LoadPOSPaymentReceipts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadReturnableReceipts"){
    $process->LoadReturnableReceipts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadDeliveryReceipt"){
    $process->LoadDeliveryReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadReturnableDeliveryReceipt"){
    $process->LoadReturnableDeliveryReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPaymentReceipt"){
    $process->LoadPaymentReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadDRNo"){
    $process->LoadDRNo();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPRNo"){
    $process->LoadPRNo();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSINo"){
    $process->LoadSINo($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadNonSettledCustomers"){
    $process->LoadNonSettledCustomers();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCustomersDR"){
    $process->LoadCustomersDR($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadDRDetails"){
    $process->LoadDRDetails($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPreparedBy"){
    $process->LoadPreparedBy();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductsInTransaction"){
    $process->LoadProductsInTransaction($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "RemoveProductInTransaction"){
    $process->RemoveProductInTransaction($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitAddProductInTransaction"){
    $process->SubmitAddProductInTransaction($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitReceipt"){
    $process->SubmitReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ReturnDeliveryReceipt"){
    $process->ReturnDeliveryReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "CancelDeliveryReceipt"){
    $process->CancelDeliveryReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "EntryPOSPayment"){
    $process->EntryPOSPayment($_POST);
}

