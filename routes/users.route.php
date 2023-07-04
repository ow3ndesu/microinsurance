<?php
include_once("../process/users.process.php");
$process = new Process();

if(isset($_POST["action"]) && $_POST["action"] == "LoadUserID"){
    $process->LoadUserID();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadUsers"){
    $process->LoadUsers();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadUser"){
    $process->LoadUser($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "VerifyPassword"){
    $process->VerifyPassword($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ChangeUserPassword"){
    $process->ChangeUserPassword($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUser"){
    $process->SubmitUser($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SubmitUpdateUser"){
    $process->SubmitUpdateUser($_POST);
}