<?php
include_once("../process/gl.process.php");
$process = new Process();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "PostGL"){
    $process->PostGL($_POST);
}
?>