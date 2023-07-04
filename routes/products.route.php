<?php
include_once("../process/products.process.php");
$process = new Product();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductNumber"){
    $process->LoadProductNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadConfigCounts"){
    $process->LoadConfigCounts();
}

if(isset($_POST["action"]) && $_POST["action"] == "GetProductByProductNo"){
    $process->GetProductByProductNo($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProducts"){
    $process->LoadProducts($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductsFilteredBySupplier"){
    $process->LoadProductsFilteredBySupplier($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProduct"){
    $process->LoadProduct($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitProduct"){
    $process->SubmitProduct($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateProduct"){
    $process->SubmitUpdateProduct($_POST);
}