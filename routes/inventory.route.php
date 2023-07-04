<?php
include_once("../process/inventory.process.php");
$process = new Product();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadProducts"){
    $process->LoadProducts($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCurrentProducts"){
    $process->LoadCurrentProducts($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCurrentProduct"){
    $process->LoadCurrentProduct($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadBatches"){
    $process->LoadBatches($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadAllCurrentInventorySuppliers"){
    $process->LoadAllCurrentInventorySuppliers();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadAllCurrentInventoryProducts"){
    $process->LoadAllCurrentInventoryProducts();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadAllCurrentInventoryProductsSL"){
    $process->LoadAllCurrentInventoryProductsSL();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductsFilteredBySupplier"){
    $process->LoadProductsFilteredBySupplier($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProduct"){
    $process->LoadProduct($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductLessPending"){
    $process->LoadProductLessPending($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductByBatch"){
    $process->LoadProductByBatch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitProduct"){
    $process->SubmitProduct($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateProduct"){
    $process->SubmitUpdateProduct($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SearchProducts"){
    $process->SearchProducts($_POST);
}