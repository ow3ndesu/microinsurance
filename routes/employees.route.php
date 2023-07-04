<?php
include_once("../process/employees.process.php");
$process = new Employee();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadEmployeeNumber"){
    $process->LoadEmployeeNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadEmployees"){
    $process->LoadEmployees();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProfile"){
    $process->LoadProfile();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadUnsignedEmployees"){
    $process->LoadUnsignedEmployees();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadNonAgentEmployees"){
    $process->LoadNonAgentEmployees();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadEmployee"){
    $process->LoadEmployee($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitEmployee"){
    $process->SubmitEmployee($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateEmployee"){
    $process->SubmitUpdateEmployee($_POST);
}