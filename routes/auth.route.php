<?php
include_once("../process/auth.process.php");
$process = new Process();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data["action"]) && $data["action"] == "LoginAuth"){
    $process->LoginAuth($data);
}

if(isset($data["action"]) && $data["action"] == "LogoutAuth"){
    $process->LogoutAuth();
}

// if(isset($_POST["action"]) && $_POST["action"] == "Register"){
//     $process->Register($_POST);
// }
// if(isset($_POST["action"]) && $_POST["action"] == "ForgotPassword"){
//     $process->ForgotPassword($_POST);
// }

// if(isset($_POST["action"]) && $_POST["action"] == "VerifyOTP"){
//     $process->VerifyOTP($_POST);
// }

// if(isset($_POST["action"]) && $_POST["action"] == "ResetPassword"){
//     $process->ResetPassword($_POST);
// }