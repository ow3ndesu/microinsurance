<?php
include_once("../process/contents.process.php");
$process = new Content();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadContentNumber"){
    $process->LoadContentNumber();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadContents"){
    $process->LoadContents();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadContent"){
    $process->LoadContent($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadNavigationBar"){
    $process->LoadNavigationBar();
}




if(isset($_POST["action"]) && $_POST["action"] == "ChangeBooleanValue"){
    $process->ChangeBooleanValue($_POST);
}