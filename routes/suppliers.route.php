<?php
include_once("../process/suppliers.process.php");
$process = new Supplier();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadSupplierNumber"){
    $process->LoadSupplierNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "GetSupplierNumberByBusinessName"){
    $process->GetSupplierNumberByBusinessName($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSuppliers"){
    $process->LoadSuppliers();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSupplier"){
    $process->LoadSupplier($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitSupplier"){
    $process->SubmitSupplier($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateSupplier"){
    $process->SubmitUpdateSupplier($_POST);
}