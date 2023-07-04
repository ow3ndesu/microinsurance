<?php
include_once("../process/search&reports.process.php");
include_once("../process/purchases.process.php");
include_once("../reports/inventory.reports.php");
include_once("../reports/modules.reports.php");
include_once("../reports/productsl.reports.php");
include_once("../reports/drpdf.reports.php");
include_once("../reports/prpdf.reports.php");
include_once("../reports/popdf.reports.php");
include_once("../reports/salesreportpdf.reports.php");

$process = new SearchReports();
$purchase = new Purchase();
$inventoryrep = new InventoryReports();
$modulerep = new ModuleReports();
$productslrep = new ProductSLReports();
$drpdf = new DRPDF();
$prpdf = new PRPDF();
$popdf = new POPDF();
$srpdf = new SalesReportPDF();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_POST["action"]) && $_POST["action"] == "LoadDateNow"){
    $process->LoadDateNow();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadInventoryQuickSearchType"){
    $process->LoadInventoryQuickSearchType();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadModulesQuickSearchType"){
    $process->LoadModulesQuickSearchType();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadPOReceivingSearchType"){
    $process->LoadPOReceivingSearchType();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadQuickSearchPurpose"){
    $process->LoadQuickSearchPurpose();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadValueSearchFields"){
    $process->LoadValueSearchFields();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadModuleValueSearchFields"){
    $process->LoadModuleValueSearchFields($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSalesValueSearchFields"){
    $process->LoadSalesValueSearchFields();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadFieldValues"){
    $process->LoadFieldValues($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadModuleFieldValues"){
    $process->LoadModuleFieldValues($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadSalesFieldValues"){
    $process->LoadSalesFieldValues($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadValueSearchOperators"){
    $process->LoadValueSearchOperators();
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadValueSearchLogicalOperators"){
    $process->LoadValueSearchLogicalOperators();
}

if(isset($_POST["action"]) && $_POST["action"] == "QuickSearch"){
    $process->QuickSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "QuickSalesSearch"){
    $process->QuickSalesSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "QuickModuleSearch"){
    $process->QuickModuleSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "POReceivingSearch"){
    $process->POReceivingSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ValueSearch"){
    $process->ValueSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ValueSalesSearch"){
    $process->ValueSalesSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ValueModuleSearch"){
    $process->ValueModuleSearch($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "ToSession"){
    $process->ToSession($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "LoadProductSL"){
    $process->LoadProductSL($_POST);
}

if(isset($_POST["action"]) && $_POST["action"] == "SearchPOReceivingByRef"){
    switch ($_POST["type"]) {
        case 'PURCHASEORDER':
            $_POST['purchaseno'] = $_POST['referenceno'];
            $purchase->LoadPurchaseOrderNonFiltered($_POST);
            break;

        case 'PURCHASERECEIVED':
            $_POST['receivingno'] = $_POST['referenceno'];
            $purchase->LoadReceivedPurchaseOrderNonFiltered($_POST);
            break;
        
        default:
            $_POST['purchaseno'] = $_POST['referenceno'];
            $purchase->LoadPurchaseOrderNonFiltered($_POST);
            break;
    }
}

if(isset($_GET["type"]) && $_GET["type"] == "inventorylists"){
    $inventoryrep->ExportProductsAsPDF($_SESSION["details"], $_SESSION["products"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "modulelists"){
    $modulerep->ExportModulesAsPDF($_SESSION["details"], $_SESSION["modules"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "productsllists"){
    $productslrep->ExportModulesAsPDF($_SESSION["details"], $_SESSION["productsl"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "autodrpdf"){
    $drpdf->AutoExportDRToPDF($_GET['details']);
}

if(isset($_GET["type"]) && $_GET["type"] == "drpdf"){
    $drpdf->ExportDRToPDF($_SESSION["details"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "autoprpdf"){
    $prpdf->AutoExportPRToPDF($_GET['details']);
}

if(isset($_GET["type"]) && $_GET["type"] == "prpdf"){
    $prpdf->ExportPRToPDF($_SESSION["details"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "autopopdf"){
    $popdf->AutoExportPOToPDF($_GET["details"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "popdf"){
    $popdf->ExportPOToPDF($_SESSION["details"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "salespdf"){
    $srpdf->ExportSalesAsIsToPDF($_SESSION["details"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "salesreportpdf"){
    $srpdf->ExportSalesReportToPDF($_SESSION["details"]);
}

if(isset($_GET["type"]) && $_GET["type"] == "poandreceivingsummarypdf"){
    $details = $_SESSION["details"];
    switch ($details->type) {
        case 'PURCHASEORDER':
            $popdf->ExportPOSummaryToPDF($details);
            break;

        case 'PURCHASERECEIVED':
            $popdf->ExportPRSummaryToPDF($details);
            break;
        
        default:
            $popdf->ExportPOSummaryToPDF($details);
            break;
    }
}

if(isset($_GET["type"]) && $_GET["type"] == "poandreceivingpdf"){
    $details = $_SESSION["details"];
    switch ($details->type) {
        case 'PURCHASEORDER':
            $details->purchaseno = trim($details->referenceno);
            $popdf->ExportPOToPDF($details);
            break;

        case 'PURCHASERECEIVED':
            $details->receivingno = trim($details->referenceno);
            $popdf->ExportPRToPDF($details);
            break;
        
        default:
            $details->purchaseno = trim($details->referenceno);
            $popdf->ExportPOToPDF($details);
            break;
    }
}