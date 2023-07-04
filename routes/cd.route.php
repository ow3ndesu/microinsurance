<?php
include_once("../process/cd.process.php");
include_once("../reports/cd.reports.php");
$process = new Process();
$reports = new Reports();

if(isset($_POST["action"]) && $_POST["action"] == "Initialize"){
    $process->Initialize();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadBankSetup"){
    $process->LoadBankSetup();
}

if(isset($_POST["action"]) && $_POST["action"] == "Save"){
    $process->Save($_POST);
}

if(isset($_GET["type"]) && $_GET["type"] == "cd"){
    $reports->CDReport($_SESSION["CVNO"]);
}

// if(isset($_POST["action"]) && $_POST["action"] == "SelectSLName"){
//     $process->SelectSLName($_POST);
// }

// if(isset($_POST["action"]) && $_POST["action"] == "SelectOR"){
//     $process->SelectOR($_POST);
// }

// if(isset($_POST["action"]) && $_POST["action"] == "SelectAR"){
//     $process->SelectAR($_POST);
// }

// if(isset($_POST["action"]) && $_POST["action"] == "SaveEntry"){
//     $process->SaveEntry($_POST);
// }
?>