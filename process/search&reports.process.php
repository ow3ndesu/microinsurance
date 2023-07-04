<?php

include_once("../database/connection.php");
require_once("../database/constants.php");
require_once('../assets/tcpdf/tcpdf.php');
include_once("sanitize.process.php");

class SearchReports extends Database
{

    public function LoadDateNow() {
        echo date('Y-m-d');
    }

    public function LoadInventoryQuickSearchType() {

        $types = [];
        $typesloaded = "TYPES_LOADED";

        $types = array(
            0 => (object) ['types' => 'CURRENT INVENTORY'],
            1 => (object) ['types' => 'EXPIRED INVENTORY'],
            2 => (object) ['types' => 'INCOMING INVENTORY'],
            3 => (object) ['types' => 'OUTGOING INVENTORY'],
            4 => (object) ['types' => 'PREVIOUS INVENTORY'],
        );

        echo json_encode(array(
            "MESSAGE" => $typesloaded,
            "TYPES" => $types
        ));
    }

    public function LoadModulesQuickSearchType() {

        $types = [];
        $typesloaded = "TYPES_LOADED";

        $types = array(
            0 => (object) ['types' => 'EMPLOYEES'],
            1 => (object) ['types' => 'CUSTOMERS'],
            2 => (object) ['types' => 'SUPPLIERS'],
            3 => (object) ['types' => 'RECEIPTS'],
            4 => (object) ['types' => 'PAYMENTS'],
        );

        echo json_encode(array(
            "MESSAGE" => $typesloaded,
            "TYPES" => $types
        ));
    }

    public function LoadPOReceivingSearchType() {

        $types = [];
        $typesloaded = "TYPES_LOADED";

        $types = array(
            0 => (object) ['types' => 'PURCHASE ORDER', 'value' => 'PURCHASEORDER'],
            1 => (object) ['types' => 'PURCHASE RECEIVED', 'value' => 'PURCHASERECEIVED'],
        );

        echo json_encode(array(
            "MESSAGE" => $typesloaded,
            "TYPES" => $types
        ));
    }

    public function LoadQuickSearchPurpose() {

        $purposes = [];
        $purposesloaded = "PURPOSES_LOADED";

        $query = "SELECT DISTINCT UPPER(column_name) AS fields FROM information_schema.columns WHERE table_schema = '" . DBNAME . "' AND table_name = 'tbl_inventory_current' ORDER BY ordinal_position;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $purposes[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $purposesloaded,
                "PURPOSES" => $purposes
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadValueSearchFields() {

        $fields = [];
        $fieldsloaded = "FIELDS_LOADED";

        $query = "SELECT DISTINCT UPPER(column_name) AS fields FROM information_schema.columns WHERE table_schema = '" . DBNAME . "' AND table_name = 'tbl_inventory_current' ORDER BY ordinal_position;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $fields[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $fieldsloaded,
                "FIELDS" => $fields
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadModuleValueSearchFields($data) {

        $fields = [];
        $sanitize = new Sanitize();
        $type = strtoupper($sanitize->sanitizeForString($data['type']));
        $fieldsloaded = "FIELDS_LOADED";

        switch ($type) {
            case 'EMPLOYEES':
                $table = "tbl_employees";
                break;

            case 'CUSTOMERS':
                $table = "tbl_customers";
                break;

            case 'SUPPLIERS':
                $table = "tbl_suppliers";
                break;

            case 'RECEIPTS':
                $table = "tbl_pos_receipts";
                break;

            case 'PAYMENTS':
                $table = "tbl_pos_payments";
                break;

            default:
                $table = "tbl_employees";
                break;
        }

        $query = "SELECT DISTINCT UPPER(column_name) AS fields FROM information_schema.columns WHERE table_schema = '" . DBNAME . "' AND table_name = '$table' ORDER BY ordinal_position;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $fields[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $fieldsloaded,
                "FIELDS" => $fields
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadSalesValueSearchFields() {

        $fields = [];
        $sanitize = new Sanitize();
        $fieldsloaded = "FIELDS_LOADED";

        $query = "SELECT DISTINCT UPPER(column_name) AS fields FROM information_schema.columns WHERE table_schema = '" . DBNAME . "' AND table_name = 'tbl_pos_transactions_main' ORDER BY ordinal_position;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $fields[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $fieldsloaded,
                "FIELDS" => $fields
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadFieldValues($data) {

        $fieldvalues = [];
        $sanitize = new Sanitize();
        $field = strtoupper($sanitize->sanitizeForString($data['field']));
        $type = strtoupper($sanitize->sanitizeForString($data['type']));
        $fieldvaluesloaded = "FIELDVALUESS_LOADED";

        $table = '';
        switch ($type) {
            case 'CURRENT INVENTORY':
                $table = "tbl_inventory_current;";
                break;

            case 'EXPIRED INVENTORY':
                $table = "tbl_inventory_expired;";
                break;

            case 'INCOMING INVENTORY':
                $table = "tbl_inventory_current;";
                break;

            case 'OUTGOING INVENTORY':
                $table = "tbl_inventory_current;";
                break;

            case 'PREVIOUS INVENTORY':
                $table = "tbl_inventory_previous;";
                break;

            default:
                $table = "tbl_inventory_current;";
                break;
        }

        $query = "SELECT DISTINCT UPPER($field) AS fieldvalues FROM " . $table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $fieldvalues[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $fieldvaluesloaded,
                "FIELDVALUES" => $fieldvalues
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadModuleFieldValues($data) {

        $fieldvalues = [];
        $sanitize = new Sanitize();
        $field = strtoupper($sanitize->sanitizeForString($data['field']));
        $type = strtoupper($sanitize->sanitizeForString($data['type']));
        $fieldvaluesloaded = "FIELDVALUESS_LOADED";

        switch ($type) {
            case 'EMPLOYEES':
                $table = "tbl_employees";
                break;

            case 'CUSTOMERS':
                $table = "tbl_customers";
                break;

            case 'SUPPLIERS':
                $table = "tbl_suppliers";
                break;

            case 'RECEIPTS':
                $table = "tbl_pos_receipts";
                break;

            case 'PAYMENTS':
                $table = "tbl_pos_payments";
                break;

            default:
                $table = "tbl_employees";
                break;
        }

        $query = "SELECT DISTINCT UPPER($field) AS fieldvalues FROM " . $table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $fieldvalues[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $fieldvaluesloaded,
                "FIELDVALUES" => $fieldvalues
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadSalesFieldValues($data) {

        $fieldvalues = [];
        $sanitize = new Sanitize();
        $field = strtoupper($sanitize->sanitizeForString($data['field']));
        $fieldvaluesloaded = "FIELDVALUESS_LOADED";

        $query = "SELECT DISTINCT UPPER($field) AS fieldvalues FROM tbl_pos_transactions_main;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $fieldvalues[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $fieldvaluesloaded,
                "FIELDVALUES" => $fieldvalues
            ));
        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadValueSearchOperators() {

        $operators = [];
        $operatorsloaded = "OPERATORS_LOADED";

        $operators = array(
            0 => (object) ['operators' => '='],
            1 => (object) ['operators' => '<>'],
            2 => (object) ['operators' => '<'],
            3 => (object) ['operators' => '>'],
            4 => (object) ['operators' => '<='],
            5 => (object) ['operators' => '>='],
            6 => (object) ['operators' => 'LIKE'],
        );

        echo json_encode(array(
            "MESSAGE" => $operatorsloaded,
            "OPERATORS" => $operators
        ));
    }

    public function LoadValueSearchLogicalOperators() {

        $logical = [];
        $logicalloaded = "LOGICAL_LOADED";

        $logical = array(
            0 => (object) ['logical' => 'AND'],
            1 => (object) ['logical' => 'OR'],
        );

        echo json_encode(array(
            "MESSAGE" => $logicalloaded,
            "LOGICAL" => $logical
        ));
    }

    public function QuickSearch($data) {

        $lists = [];
        $sanitize = new Sanitize();
        $type = strtoupper($sanitize->sanitizeForString($data['type']));
        $purpose = strtoupper($sanitize->sanitizeForString($data['purpose']));
        $datefrom = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datefrom"])));
        $dateto = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["dateto"])));
        $listsloaded = 'LISTS_LOADED';

        switch ($type) {
            case 'CURRENT INVENTORY':
                $query = "SELECT barcode, generalname, brandname, quantity, unitprice, customunitprice, producttotalprice, srp FROM tbl_inventory_current";
                $query .=  ($purpose != '') ? " WHERE purpose = '$purpose'" : '';
                $query .=  " ORDER BY generalname";
                break;

            case 'EXPIRED INVENTORY':
                $query = "SELECT barcode, generalname, brandname, quantity, unitprice, customunitprice, producttotalprice, srp FROM tbl_inventory_expired";
                $query .=  "  WHERE STR_TO_DATE(expirationdate,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(expirationdate,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  ($purpose != '') ? " AND purpose = '$purpose'" : '';
                $query .=  " ORDER BY generalname";
                break;

            case 'INCOMING INVENTORY':
                $query = "SELECT barcode, generalname, brandname, quantity, unitprice, customunitprice, producttotalprice, srp FROM tbl_inventory_current";
                $query .=  ($purpose != '') ? " WHERE purpose = '$purpose'" : '';
                $query .=  " ORDER BY generalname";
                break;

            case 'OUTGOING INVENTORY':
                $query = "SELECT barcode, generalname, brandname, quantity, unitprice, customunitprice, producttotalprice, srp FROM tbl_inventory_current";
                $query .=  ($purpose != '') ? " WHERE purpose = '$purpose'" : '';
                $query .=  " ORDER BY generalname";
                break;

            case 'PREVIOUS INVENTORY':
                $query = "SELECT barcode, generalname, brandname, quantity, unitprice, customunitprice, producttotalprice, srp FROM tbl_inventory_previous";
                $query .=  "  WHERE STR_TO_DATE(asof,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(asof,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  ($purpose != '') ? " AND purpose = '$purpose'" : '';
                $query .=  " ORDER BY generalname";
                break;

            default:
                $query = "SELECT barcode, generalname, brandname, quantity, unitprice, customunitprice, producttotalprice, srp FROM tbl_inventory_current";
                $query .=  ($purpose != '') ? " WHERE purpose = '$purpose'" : '';
                $query .=  " ORDER BY generalname";
                break;
        }

        // echo $query;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $lists[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $listsloaded,
                "LISTS" => $lists
            ));
        } else {
            echo json_encode(array(
                "MESSAGE" => 'Nothing There!',
            ));
        }
    }

    public function QuickSalesSearch($data) {

        $lists = [];
        $sanitize = new Sanitize();
        $datefrom = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datefrom"])));
        $dateto = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["dateto"])));
        $listsloaded = 'LISTS_LOADED';

        $query = "SELECT barcode AS `#`, productname AS `Product`, quantity AS `Quantity`, CONCAT('₱', FORMAT(cost, 2)) AS `Cost`, CONCAT('₱', FORMAT(srp, 2)) AS `SRP`, CONCAT('₱', FORMAT(totalamount, 2)) AS `Total` FROM tbl_pos_transactions_today";
        $query .= " WHERE STR_TO_DATE(transactiondate, '%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(transactiondate, '%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
        $query .= " ORDER BY STR_TO_DATE(transactiondate, '%m/%d/%Y');";

        // echo $query;
        // return;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $todayresult = $stmt->get_result();
        $stmt->close();

        if ($todayresult->num_rows == 0) {

            $query = "SELECT barcode AS `#`, productname AS `Product`, quantity AS `Quantity`, CONCAT('₱', FORMAT(cost, 2)) AS `Cost`, CONCAT('₱', FORMAT(srp, 2)) AS `SRP`, CONCAT('₱', FORMAT(totalamount, 2)) AS `Total` FROM tbl_pos_transactions_main";
            $query .= " WHERE STR_TO_DATE(transactiondate, '%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(transactiondate, '%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
            $query .= " ORDER BY STR_TO_DATE(transactiondate, '%m/%d/%Y');";


            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $mainresult = $stmt->get_result();
            $stmt->close();

            if ($mainresult->num_rows == 0) {
                echo json_encode(array(
                    "MESSAGE" => 'Nothing There!',
                ));

                return;
            }

            while ($row = $mainresult->fetch_assoc()) {
                $lists[] = $row;
            }
        }

        while ($row = $todayresult->fetch_assoc()) {
            $lists[] = $row;
        }

        echo json_encode(array(
            "MESSAGE" => $listsloaded,
            "LISTS" => $lists
        ));
    }

    public function QuickModuleSearch($data) {

        $lists = [];
        $sanitize = new Sanitize();
        $type = strtoupper($sanitize->sanitizeForString($data['type']));
        $datefrom = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datefrom"])));
        $dateto = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["dateto"])));
        $listsloaded = 'LISTS_LOADED';

        switch ($type) {
            case 'EMPLOYEES':
                $query = "SELECT employeeno as `#`, fullname as `Fullname`, CONCAT(street, ', ', barangay, ', ', town, ', ', province) as `Address`, datehired as `Date Hired`, position as `Position`, IF(resignationdate = '' OR resignationdate = null OR resignationdate = '-', 'NONE', resignationdate) as `Resignation` FROM tbl_employees";
                $query .=  " WHERE STR_TO_DATE(dateencoded,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(dateencoded,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y') AND employeeno <> 'ADMIN/IT'";
                $query .=  " ORDER BY employeeno";
                break;

            case 'CUSTOMERS':
                $query = "SELECT customerno as `#`, businessname as `Business`, CONCAT(businessstreet, ', ', businessbarangay, ', ', businesstown, ', ', businessprovince) as `Address`, creditlimit as `Credit Limit`, creditbalance as `Credit Balance`, fullname as `Owner` FROM tbl_customers";
                $query .=  " WHERE STR_TO_DATE(dateencoded,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(dateencoded,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  " ORDER BY customerno";
                break;

            case 'SUPPLIERS':
                $query = "SELECT supplierno as `#`, businessname as `Business`, CONCAT(businessstreet, ', ', businessbarangay, ', ', businesstown, ', ', businessprovince) as `Address`, email as `Email`, fullname as `Owner` FROM tbl_suppliers";
                $query .=  " WHERE STR_TO_DATE(dateencoded,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(dateencoded,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  " ORDER BY supplierno";
                break;

            case 'RECEIPTS':
                $query = "SELECT transactionno as `#`, transactiondate as `Transaction Date`, drno as `DR #`, sinumber as `SI #`, customerno as `Customer #`, billingaddress as `Billing Address`, modeofpayment as `Mode`, paymentmethod as `Method`, totalamount as `Amount` FROM tbl_pos_receipts";
                $query .=  " WHERE STR_TO_DATE(transactiondate,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(transactiondate,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  " ORDER BY transactionno";
                break;

            case 'PAYMENTS':
                $query = "SELECT prno as `#`, prdate as `PR Date`, drno as `DR #`, customerno as `Customer #`, paymentmethod as `Method`, referenceno as `Reference`, amount as `Amount` FROM tbl_pos_payments";
                $query .=  "  WHERE STR_TO_DATE(prdate,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(prdate,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  " ORDER BY prno";
                break;

            default:
                $query = "SELECT employeeno as `#`, fullname as `Fullname`, CONCAT(street, ', ', barangay, ', ', town, ', ', province) as `Address`, datehired as `Date Hired`, position as `Position`, IF(resignationdate = '' OR resignationdate = null OR resignationdate = '-', 'NONE', resignationdate) as `Resignation` FROM tbl_employees";
                $query .=  " WHERE STR_TO_DATE(dateencoded,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(dateencoded,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y') AND employeeno <> 'ADMIN/IT'";
                $query .=  " ORDER BY employeeno";
                break;
        }

        // echo $query;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $lists[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $listsloaded,
                "LISTS" => $lists
            ));
        } else {
            echo json_encode(array(
                "MESSAGE" => 'Nothing There!',
            ));
        }
    }

    public function POReceivingSearch($data) {

        $lists = [];
        $sanitize = new Sanitize();
        $type = strtoupper($sanitize->sanitizeForString($data['type']));
        $supplier = strtoupper($sanitize->sanitizeForString($data['supplier']));
        $datefrom = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["datefrom"])));
        $dateto = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["dateto"])));
        $listsloaded = 'LISTS_LOADED';

        switch ($type) {
            case 'PURCHASEORDER':
                $query = "SELECT podate AS `date`, purchaseno AS `referenceno`, totalprice FROM tbl_purchase_orders";
                $query .=  "  WHERE STR_TO_DATE(dateencoded,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(dateencoded,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  ($supplier != 'ALL') ? " AND supplierno = '$supplier'" : '';
                break;

            case 'PURCHASERECEIVED':
                $query = "SELECT datereceived AS `date`, receivingno AS `referenceno`, totalprice FROM tbl_purchase_received";
                $query .=  "  WHERE STR_TO_DATE(datereceived,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(datereceived,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  ($supplier != 'ALL') ? " AND supplierno = '$supplier'" : '';
                break;

            default:
                $query = "SELECT podate AS `date`, purchaseno AS `referenceno`, totalprice FROM tbl_purchase_orders";
                $query .=  "  WHERE STR_TO_DATE(dateencoded,'%m/%d/%Y') >= STR_TO_DATE('$datefrom', '%m/%d/%Y') AND STR_TO_DATE(dateencoded,'%m/%d/%Y') <= STR_TO_DATE('$dateto', '%m/%d/%Y')";
                $query .=  ($supplier != 'ALL') ? " AND supplierno = '$supplier'" : '';
                break;
        }

        // echo $query;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $lists[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $listsloaded,
                "LISTS" => $lists
            ));
        } else {
            echo json_encode(array(
                "MESSAGE" => 'Nothing There!',
            ));
        }
    }

    public function ValueSearch($data) {

        $lists = [];
        $sanitize = new Sanitize();
        $type = strtoupper($data['type']);
        $filter = $data['filter'];
        $fieldstoshow = strtoupper($data['fieldstoshow']);
        $listsloaded = 'LISTS_LOADED';

        switch ($type) {
            case 'CURRENT INVENTORY':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_inventory_current") : "SELECT barcode AS `#`, generalname AS `Product`, brandname AS `Brand`, quantity AS `Quantity`, unitprice AS `Unit Price`, customunitprice AS `Custom UP`, producttotalprice AS `Total`, srp AS `SRP` FROM tbl_inventory_current";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY generalname";
                break;

            case 'EXPIRED INVENTORY':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_inventory_expired") : "SELECT barcode AS `#`, generalname AS `Product`, brandname AS `Brand`, quantity AS `Quantity`, unitprice AS `Unit Price`, customunitprice AS `Custom UP`, producttotalprice AS `Total`, srp AS `SRP` FROM tbl_inventory_expired";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY generalname";
                break;

            case 'INCOMING INVENTORY':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_inventory_current") : "SELECT barcode AS `#`, generalname AS `Product`, brandname AS `Brand`, quantity AS `Quantity`, unitprice AS `Unit Price`, customunitprice AS `Custom UP`, producttotalprice AS `Total`, srp AS `SRP` FROM tbl_inventory_current";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY generalname";
                break;

            case 'OUTGOING INVENTORY':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_inventory_current") : "SELECT barcode AS `#`, generalname AS `Product`, brandname AS `Brand`, quantity AS `Quantity`, unitprice AS `Unit Price`, customunitprice AS `Custom UP`, producttotalprice AS `Total`, srp AS `SRP` FROM tbl_inventory_current";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY generalname";
                break;

            case 'PREVIOUS INVENTORY':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_inventory_previous") : "SELECT barcode AS `#`, generalname AS `Product`, brandname AS `Brand`, quantity AS `Quantity`, unitprice AS `Unit Price`, customunitprice AS `Custom UP`, producttotalprice AS `Total`, srp AS `SRP` FROM tbl_inventory_previous";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY generalname";
                break;

            default:
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_inventory_current") : "SELECT barcode AS `#`, generalname AS `Product`, brandname AS `Brand`, quantity AS `Quantity`, unitprice AS `Unit Price`, customunitprice AS `Custom UP`, producttotalprice AS `Total`, srp AS `SRP` FROM tbl_inventory_current";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY generalname";
                break;
        }

        // echo $query;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $lists[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $listsloaded,
                "LISTS" => $lists
            ));
        } else {
            echo json_encode(array(
                "MESSAGE" => 'Nothing There!',
            ));
        }
    }

    public function ValueSalesSearch($data) {

        $lists = [];
        $filter = $data['filter'];
        $fieldstoshow = strtoupper($data['fieldstoshow']);
        $listsloaded = 'LISTS_LOADED';

        $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_pos_transactions_main") : "SELECT barcode AS `#`, productname AS `Product`, quantity AS `Quantity`, CONCAT('₱', FORMAT(cost, 2)) AS `Cost`, CONCAT('₱', FORMAT(srp, 2)) AS `SRP`, CONCAT('₱', FORMAT(totalamount, 2)) AS `Total` FROM tbl_pos_transactions_main";
        $query .=  " WHERE " . $filter;
        $query .= " ORDER BY STR_TO_DATE(transactiondate, '%m/%d/%Y');";

        // echo $query;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $todayresult = $stmt->get_result();
        $stmt->close();

        if ($todayresult->num_rows == 0) {

            $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_pos_transactions_today") : "SELECT barcode AS `#`, productname AS `Product`, quantity AS `Quantity`, CONCAT('₱', FORMAT(cost, 2)) AS `Cost`, CONCAT('₱', FORMAT(srp, 2)) AS `SRP`, CONCAT('₱', FORMAT(totalamount, 2)) AS `Total` FROM tbl_pos_transactions_today";
            $query .=  " WHERE " . $filter;
            $query .= " ORDER BY STR_TO_DATE(transactiondate, '%m/%d/%Y');";


            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $mainresult = $stmt->get_result();
            $stmt->close();

            if ($mainresult->num_rows == 0) {
                echo json_encode(array(
                    "MESSAGE" => 'Nothing There!',
                ));

                return;
            }

            while ($row = $mainresult->fetch_assoc()) {
                $lists[] = $row;
            }
        }

        while ($row = $todayresult->fetch_assoc()) {
            $lists[] = $row;
        }

        echo json_encode(array(
            "MESSAGE" => $listsloaded,
            "LISTS" => $lists
        ));
    }

    public function ValueModuleSearch($data) {

        $lists = [];
        $sanitize = new Sanitize();
        $type = strtoupper($data['type']);
        $fieldstoshow = strtoupper($data['fieldstoshow']);
        $filter = $data['filter'];
        $listsloaded = 'LISTS_LOADED';

        switch ($type) {
            case 'EMPLOYEES':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_employees") : "SELECT employeeno as `#`, fullname as `Fullname`, CONCAT(street, ', ', barangay, ', ', town, ', ', province) as `Address`, datehired as `Date Hired`, position as `Position`, IF(resignationdate = '' OR resignationdate = null OR resignationdate = '-', 'NONE', resignationdate) as `Resignation` FROM tbl_employees";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY employeeno";
                break;

            case 'CUSTOMERS':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_customers") : "SELECT customerno as `#`, businessname as `Business`, CONCAT(businessstreet, ', ', businessbarangay, ', ', businesstown, ', ', businessprovince) as `Address`, creditlimit as `Credit Limit`, creditbalance as `Credit Balance`, fullname as `Owner` FROM tbl_customers";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY customerno";
                break;

            case 'SUPPLIERS':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_suppliers") : "SELECT supplierno as `#`, businessname as `Business`, CONCAT(businessstreet, ', ', businessbarangay, ', ', businesstown, ', ', businessprovince) as `Address`, email as `Email`, fullname as `Owner` FROM tbl_suppliers";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY supplierno";
                break;

            case 'RECEIPTS':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_pos_receipts") : "SELECT transactionno as `#`, transactiondate as `Transaction Date`, drno as `DR #`, sinumber as `SI #`, customerno as `Customer #`, billingaddress as `Billing Address`, modeofpayment as `Mode`, paymentmethod as `Method`, totalamount as `Amount` FROM tbl_pos_receipts";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY transactionno";
                break;

            case 'PAYMENTS':
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_pos_payments") : "SELECT prno as `#`, prdate as `PR Date`, drno as `DR #`, customerno as `Customer #`, paymentmethod as `Method`, referenceno as `Reference`, amount as `Amount` FROM tbl_pos_payments";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY prno";
                break;

            default:
                $query = ($fieldstoshow != '') ? ("SELECT " . $fieldstoshow . " FROM tbl_employees") : "SELECT employeeno as `#`, fullname as `Fullname`, CONCAT(street, ', ', barangay, ', ', town, ', ', province) as `Address`, datehired as `Date Hired`, position as `Position`, IF(resignationdate = '' OR resignationdate = null OR resignationdate = '-', 'NONE', resignationdate) as `Resignation` FROM tbl_employees";
                $query .=  " WHERE " . $filter;
                $query .=  " ORDER BY employeeno";
                break;
        }

        // echo $query;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $lists[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $listsloaded,
                "LISTS" => $lists
            ));
        } else {
            echo json_encode(array(
                "MESSAGE" => 'Nothing There!',
            ));
        }
    }

    public function ToSession($data) {

        $from = $data['from'];

        if ($from == 'inventory') {
            $products = json_decode($data['products']);
            $details = json_decode($data['details']);
            
            unset($_SESSION['products']);
            unset($_SESSION['details']);
            $_SESSION['products'] = $products;
            $_SESSION['details'] = $details;
        }
        
        if ($from == 'modules') {
            $modules = json_decode($data['modules']);
            $details = json_decode($data['details']);
            
            unset($_SESSION['modules']);
            unset($_SESSION['details']);
            $_SESSION['modules'] = $modules;
            $_SESSION['details'] = $details;
        }

        if ($from == 'productsl') {
            $productsl = json_decode($data['productsl']);
            $details = json_decode($data['details']);
            
            unset($_SESSION['productsl']);
            unset($_SESSION['details']);
            $_SESSION['productsl'] = $productsl;
            $_SESSION['details'] = $details;
        }

        if ($from == 'rpdf') {
            $details = json_decode($data['details']);
            
            unset($_SESSION['details']);
            $_SESSION['details'] = $details;
        }

        if ($from == 'sales') {
            $details = json_decode($data['details']);
            
            unset($_SESSION['details']);
            $_SESSION['details'] = $details;
        }

        echo json_encode(array(
            "MESSAGE" => 'DATA_READY'
        ));
    }

    public function LoadProductSL($data) {
        $productsl = [];
        $transactions = [];
        $sanitize = new Sanitize();
        $productno = strtoupper($sanitize->sanitizeForString($data['productno']));
        $barcode = strtoupper($sanitize->sanitizeForString($data['barcode']));
        $todate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["todate"])));
        $fromdate = date('m/d/Y', strtotime($sanitize->sanitizeForString($data["fromdate"])));
        $supplierno = strtoupper($sanitize->sanitizeForString($data['supplierno']));
        $productslloaded = 'SL_LOADED';
        
        $stmt1 = $this->conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS incomingcount
        FROM tbl_inventory_transactions 
        WHERE category = 'INCOMING'
          AND barcode = '$barcode' 
          AND STR_TO_DATE(transactiondate,'%m/%d/%Y') < STR_TO_DATE('$fromdate','%m/%d/%Y');");
        $stmt1->execute();
        $incomingres = $stmt1->get_result();
        $stmt1->close();
        $incomingcount = ($incomingres->fetch_assoc())['incomingcount'];

        $stmt2 = $this->conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS outgoingcount
        FROM tbl_inventory_transactions 
        WHERE category = 'OUTGOING'
          AND barcode = '$barcode' 
          AND STR_TO_DATE(transactiondate,'%m/%d/%Y') < STR_TO_DATE('$fromdate','%m/%d/%Y');");
        $stmt2->execute();
        $outgoingres = $stmt2->get_result();
        $stmt2->close();
        $outgoingcount = ($outgoingres->fetch_assoc())['outgoingcount'];

        $stmt3 = $this->conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS salescount
        FROM tbl_pos_transactions_main 
        WHERE barcode = '$barcode' 
          AND STR_TO_DATE(transactiondate,'%m/%d/%Y') < STR_TO_DATE('$fromdate','%m/%d/%Y');");
        $stmt3->execute();
        $incomingres = $stmt3->get_result();
        $stmt3->close();
        $salescount = ($incomingres->fetch_assoc())['salescount'];

        $stmtto = $this->conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS salescounttoday
        FROM tbl_pos_transactions_today
        WHERE barcode = '$barcode' 
          AND STR_TO_DATE(transactiondate,'%m/%d/%Y') < STR_TO_DATE('$fromdate','%m/%d/%Y');");
        $stmtto->execute();
        $incomingres = $stmtto->get_result();
        $stmtto->close();
        $salescount += ($incomingres->fetch_assoc())['salescounttoday'];

        $beginning = $incomingcount - ($outgoingcount + $salescount);

        $stmt21 = $this->conn->prepare("SELECT transactiondate AS `Date`, category AS `Type`, purpose AS `Purpose`, referenceno AS `Reference`, quantity AS `Quantity`
        FROM tbl_inventory_transactions 
        WHERE barcode = '$barcode' 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') >= STR_TO_DATE('$fromdate' ,'%m/%d/%Y') 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') <= STR_TO_DATE('$todate','%m/%d/%Y')
            AND purpose <> 'CANCELLATION';");
        $stmt21->execute();
        $invtransactionres = $stmt21->get_result();
        $stmt21->close();

        if ($invtransactionres->num_rows != 0) {
            while ($row = $invtransactionres->fetch_assoc()) {
                $transactions[] = $row;
            }
        }

        $stmt6 = $this->conn->prepare("SELECT transactiondate AS `Date`, 'OUTGOING' AS `Type`, 'SALES' AS `Purpose`, transactionno AS `Reference`, quantity AS `Quantity`
        FROM tbl_pos_transactions_today 
        WHERE barcode = '$barcode' 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') >= STR_TO_DATE('$fromdate' ,'%m/%d/%Y') 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') <= STR_TO_DATE('$todate','%m/%d/%Y');");
        $stmt6->execute();
        $postransactiontodayres = $stmt6->get_result();
        $stmt6->close();

        if ($postransactiontodayres->num_rows != 0) {
            while ($row = $postransactiontodayres->fetch_assoc()) {
                $transactions[] = $row;
            }
        }

        $stmt5 = $this->conn->prepare("SELECT transactiondate AS `Date`, 'OUTGOING' AS `Type`, 'SALES' AS `Purpose`, transactionno AS `Reference`, quantity AS `Quantity`
        FROM tbl_pos_transactions_main 
        WHERE barcode = '$barcode' 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') >= STR_TO_DATE('$fromdate' ,'%m/%d/%Y') 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') <= STR_TO_DATE('$todate','%m/%d/%Y');");
        $stmt5->execute();
        $postransactionres = $stmt5->get_result();
        $stmt5->close();

        if ($postransactionres->num_rows != 0) {
            while ($row = $postransactionres->fetch_assoc()) {
                $transactions[] = $row;
            }
        }

        $stmt4 = $this->conn->prepare("SELECT transactiondate AS `Date`, category AS `Type`, purpose AS `Purpose`, referenceno AS `Reference`, quantity AS `Quantity`
        FROM tbl_inventory_transactions 
        WHERE barcode = '$barcode' 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') >= STR_TO_DATE('$fromdate' ,'%m/%d/%Y') 
            AND STR_TO_DATE(transactiondate,'%m/%d/%Y') <= STR_TO_DATE('$todate','%m/%d/%Y')
            AND purpose = 'CANCELLATION';");
        $stmt4->execute();
        $transactionres = $stmt4->get_result();
        $stmt4->close();

        if ($transactionres->num_rows != 0) {
            while ($row = $transactionres->fetch_assoc()) {
                $transactions[] = $row;
            }
        }

        $transactions[] = usort($transactions, function ($a, $b) {
            return (strtotime($a['Date']) - strtotime($b['Date']));
        });

        echo json_encode(array(
            "MESSAGE" => $productslloaded,
            "TRANSACTIONS" => $transactions,
            "BEGINNING" => $beginning
        ));

    }
}
