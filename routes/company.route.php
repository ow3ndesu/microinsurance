<?php
include_once("../process/company.process.php");
$process = new Process();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadCompany"){
    $process->LoadCompany();
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateCompany"){
    $process->SubmitUpdateCompany($_POST);
}