<?php
include_once("../process/backups.process.php");
$process = new BackUp();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "BackupDatabase"){
    $process->BackupDatabase();
}

if(isset($_GET["action"]) && $_GET["action"] == "exportbackup"){
    $process->ExportDatabase($_SESSION['backupFile']);
}