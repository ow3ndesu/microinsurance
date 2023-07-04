<?php
include_once("../process/configurations.process.php");
$process = new Process();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadConfigCounts"){
    $process->LoadConfigCounts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadDashboardCounts"){
    $process->LoadDashboardCounts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadMaintenanceLists"){
    $process->LoadMaintenanceLists();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadMaintenance"){
    $process->LoadMaintenance($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadDeliveryReceipt"){
    $process->LoadDeliveryReceipt();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPaymentReceipt"){
    $process->LoadPaymentReceipt();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSalesInvoices"){
    $process->LoadSalesInvoices();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSalesInvoice"){
    $process->LoadSalesInvoice($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateDeliveryReceipt"){
    $process->SubmitUpdateDeliveryReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdatePaymentReceipt"){
    $process->SubmitUpdatePaymentReceipt($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitSI"){
    $process->SubmitSI($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateSalesInvoice"){
    $process->SubmitUpdateSalesInvoice($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadShelves"){
    $process->LoadShelves();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPurposes"){
    $process->LoadPurposes();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadTerms"){
    $process->LoadTerms();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadModeOfPayments"){
    $process->LoadModeOfPayments();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPaymentMethod"){
    $process->LoadPaymentMethod();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCategories"){
    $process->LoadCategories();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadUnits"){
    $process->LoadUnits();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadDescriptions"){
    $process->LoadDescriptions();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadItem"){
    $process->LoadItem($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitItem"){
    $process->SubmitItem($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateItem"){
    $process->SubmitUpdateItem($_POST);
}