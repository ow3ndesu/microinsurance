<?php
include_once("../process/aging.process.php");
$process = new Process();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "UpdateAging"){
    $process->UpdateAging($_POST);
}
?>