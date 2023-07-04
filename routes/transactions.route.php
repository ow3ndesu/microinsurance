<?php
include_once("../process/transactions.process.php");
$process = new Transaction();

if(isset($_POST["action"]) && $_POST["action"] == "LoadDates"){
    $process->LoadDates();
}

if(isset($_POST["action"]) && $_POST["action"] == "CloseTransaction"){
    $process->CloseTransaction();
}