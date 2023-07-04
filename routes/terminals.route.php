<?php
include_once("../process/terminals.process.php");
$process = new Process();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadTerminals"){
    $process->LoadTerminals();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadTerminal"){
    $process->LoadTerminal($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitTerminal"){
    $process->SubmitTerminal($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateTerminal"){
    $process->SubmitUpdateTerminal($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ResetTerminal"){
    $process->ResetTerminal($_POST);
}