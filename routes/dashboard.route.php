<?php
include_once("../process/dashboard.process.php");
$process = new InventoryListener();

if(isset($_POST["action"]) && $_POST["action"] == "CreateNotifications"){
    $process->CreateNotifications();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadUnpostedDates"){
    $process->LoadUnpostedDates();
}

if(isset($_POST["action"]) && $_POST["action"] == "isClosed"){
    $process->isClosed();
}

if(isset($_POST["action"]) && $_POST["action"] == "UpdateAsOf"){
    $process->UpdateAsOf();
}

if(isset($_POST["action"]) && $_POST["action"] == "CloseUnclosedDate"){
    $process->CloseUnclosedDate($_POST);
}