<?php 
session_start();
if (!isset($_SESSION['MGNSVN03M10Z174U'])) { # authenticated
	echo '<script> window.location.href = \'../index\'; </script>';
	return;
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <?php include_once("../../includes/include.admin.head.php"); ?>
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <!-- ============================================================== -->

        <?php include_once("../../includes/include.admin.header.php"); ?>

        <?php include_once("../../includes/include.admin.sidebar.php"); ?>

        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 d-flex align-items-center">
                                <li class="breadcrumb-item"><a href="index" class="link"><i
                                            class="mdi mdi-home-outline fs-4"></i></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                            </ol>
                        </nav>
                        <h1 class="mb-0 fw-bold">Dashboard</h1>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->

            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-lg-6 col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-md-flex align-items-center">
                                    <div>
                                        <h4 class="card-title">Insurance Summary</h4>
                                        <h6 class="card-subtitle">Previous Year Vs Current Year</h6>
                                    </div>
                                    <div class="ms-auto d-flex no-block align-items-center">
                                        <ul class="list-inline dl d-flex align-items-center m-r-15 m-b-0">
                                            <li class="list-inline-item d-flex align-items-center text-info"><i
                                                    class="fa fa-circle font-10 me-1"></i> Previous Year
                                            </li>
                                            <li class="list-inline-item d-flex align-items-center text-primary"><i
                                                    class="fa fa-circle font-10 me-1"></i> Current Year
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="amp-pxl mt-4" style="height: 350px;">
                                    <div class="chartist-tooltip"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Top Propas</h4>
                                <h6 class="card-subtitle">Average Enrollees</h6>
                                <div class="mt-5 pb-3 d-flex align-items-center">
                                    <span class="btn btn-primary btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature fs-4"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Ben Life</h5>
                                        <span class="text-muted fs-6">Propa #1</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+68 enrollees</span>
                                    </div>
                                </div>
                                <div class="py-3 d-flex align-items-center">
                                    <span class="btn btn-warning btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature fs-4"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Sun Life</h5>
                                        <span class="text-muted fs-6">Propa #2</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+63 enrollees</span>
                                    </div>
                                </div>
                                <div class="py-3 d-flex align-items-center">
                                    <span class="btn btn-success btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature text-white fs-4"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Sun Life</h5>
                                        <span class="text-muted fs-6">Propa #3</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+35 enrollees</span>
                                    </div>
                                </div>
                                <div class="py-3 d-flex align-items-center">
                                    <span class="btn btn-info btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature fs-4 text-white"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Ben Life</h5>
                                        <span class="text-muted fs-6">Headoffice</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+15 enrollees</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Top Branches</h4>
                                <h6 class="card-subtitle">Average Enrollees</h6>
                                <div class="mt-5 pb-3 d-flex align-items-center">
                                    <span class="btn btn-primary btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature fs-4"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Ben Life</h5>
                                        <span class="text-muted fs-6">Branch #1</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+68 enrollees</span>
                                    </div>
                                </div>
                                <div class="py-3 d-flex align-items-center">
                                    <span class="btn btn-warning btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature fs-4"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Sun Life</h5>
                                        <span class="text-muted fs-6">Branch #2</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+63 enrollees</span>
                                    </div>
                                </div>
                                <div class="py-3 d-flex align-items-center">
                                    <span class="btn btn-success btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature text-white fs-4"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Sun Life</h5>
                                        <span class="text-muted fs-6">Branch #3</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+35 enrollees</span>
                                    </div>
                                </div>
                                <div class="py-3 d-flex align-items-center">
                                    <span class="btn btn-info btn-circle d-flex align-items-center">
                                        <i class="mdi mdi-nature fs-4 text-white"></i>
                                    </span>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-bold">Ben Life</h5>
                                        <span class="text-muted fs-6">Headoffice</span>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-light text-muted">+15 enrollees</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Table -->
                <!-- ============================================================== -->
                <div class="row">
                    <!-- column -->
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <!-- title -->
                                <div class="d-md-flex">
                                    <div>
                                        <h4 class="card-title">Top Selling Products</h4>
                                        <h5 class="card-subtitle">Overview of Top Selling Items</h5>
                                    </div>
                                    <div class="ms-auto">
                                        <div class="dl">
                                            <select class="form-select shadow-none">
                                                <option value="0" selected>Propa</option>
                                                <option value="1">Branch</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- title -->
                                <div class="table-responsive">
                                    <table class="table mb-0 table-hover align-middle text-nowrap">
                                        <thead>
                                            <tr>
                                                <th class="border-top-0">Products</th>
                                                <th class="border-top-0">Propa ID</th>
                                                <th class="border-top-0">Propa</th>
                                                <th class="border-top-0">Type</th>
                                                <th class="border-top-0">Enrolees</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="m-r-10"><a
                                                                class="btn btn-circle d-flex btn-info text-white">BL</a>
                                                        </div>
                                                        <div class="">
                                                            <h4 class="m-b-0 font-16">Ben Life</h4>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>PROPAID00128</td>
                                                <td>Juan DC</td>
                                                <td>
                                                    <label class="badge bg-danger">Life</label>
                                                </td>
                                                <td>485</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="m-r-10"><a
                                                                class="btn btn-circle d-flex btn-orange text-white">SL</a>
                                                        </div>
                                                        <div class="">
                                                            <h4 class="m-b-0 font-16">Sun Life</h4>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>PROPAID00129</td>
                                                <td>Jun DC</td>
                                                <td>
                                                    <label class="badge bg-danger">Life</label>
                                                </td>
                                                <td>356</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-md-flex align-items-center">
                                    <div>
                                        <h4 class="card-title">Client Summary</h4>
                                        <h6 class="card-subtitle">MFI Vs Associates</h6>
                                    </div>
                                    <div class="ms-auto d-flex no-block align-items-center">
                                        <ul class="list-inline dl d-flex align-items-center m-r-15 m-b-0">
                                            <li class="list-inline-item d-flex align-items-center text-primary"><i
                                                    class="fa fa-circle font-10 me-1"></i> MFI
                                            </li>
                                            <li class="list-inline-item d-flex align-items-center text-success"><i
                                                    class="fa fa-circle font-10 me-1"></i> Associates
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="ct-chart mt-4" style="height: 350px;">
                                    <div class="piechart-tooltip"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-md-flex align-items-center">
                                    <div>
                                        <h4 class="card-title">Retention Rate</h4>
                                        <h6 class="card-subtitle">Loss Ratio</h6>
                                    </div>
                                    <div class="ms-auto d-flex no-block align-items-center">
                                        <ul class="list-inline dl d-flex align-items-center m-r-15 m-b-0">
                                            <li class="list-inline-item d-flex align-items-center text-info"><i
                                                    class="fa fa-circle font-10 me-1"></i> 2023
                                            </li>
                                            <li class="list-inline-item d-flex align-items-center text-primary"><i
                                                    class="fa fa-circle font-10 me-1"></i> 2024
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="amp-pxl mt-4" id="loss" style="height: 350px;">
                                    <div class="chartist-tooltip"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Table -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            
            <?php include_once("../../includes/include.admin.footer.php"); ?>

        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    
    <?php include_once("../../includes/include.admin.script.php"); ?>
    <script src="../assets/js/pages/dashboards/dashboard1.js"></script>
    <script>
        $(document).ready(() => {
            LoadEverything().then(() => {
                setTimeout(() => {
                    $(".preloader").fadeOut();
                }, 2000);
            });
        });

        // ON LOAD FUNCTIONS

        async function LoadEverything() {
            return await true;
        }

        // ====================================================================================
    </script>
</body>

</html>