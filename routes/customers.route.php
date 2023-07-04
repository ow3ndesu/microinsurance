<?php
include_once("../process/customers.process.php");
$process = new Customer();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadCustomerNumber"){
    $process->LoadCustomerNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCustomers"){
    $process->LoadCustomers();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadCustomer"){
    $process->LoadCustomer($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitCustomer"){
    $process->SubmitCustomer($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateCustomer"){
    $process->SubmitUpdateCustomer($_POST);
}