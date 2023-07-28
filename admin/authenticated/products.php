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

    <!-- Product CSS -->
    <link href="../assets/css/products.css" rel="stylesheet">
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
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full" data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
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
                    <div class="col-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 d-flex align-items-center">
                                <li class="breadcrumb-item"><a href="index" class="link"><i class="mdi mdi-home-outline fs-4"></i></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Products</li>
                            </ol>
                        </nav>
                        <h1 class="mb-0 fw-bold">Products</h1>
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
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="container">
                    <div class="d-flex justify-content-end">
                        <div class="buttons">
                            <a href="#add" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-product">Add Product</a>
                            <button type="button" class="btn btn-secondary m-1" title="Reload" onclick="window.location.reload(true)"><i class="mdi mdi-reload"></i></button>
                        </div>
                    </div>
                    <div class="row product">
                        <div class="col-md-4 ">
                            <div class="card productcontainer">
                                <div class="ccc">
                                    <p class="text-center"><img src="https://raw.githubusercontent.com/rxhack/productImage/main/1.jpg" class="imw"></p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-center">Apple Watch Series 3</h5>
                                    <p class="text-center">Sample Product Details</p>
                                    <p class="text-center">
                                        <input type="button" name="viewproduct" value="View" class="view">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card productcontainer">
                                <div class="ccc">
                                    <p class="text-center"><img src="https://raw.githubusercontent.com/rxhack/productImage/main/2.jpg" class="imw"></p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-center">Beat Solo3 Wearless</h5>
                                    <p class="text-center">Sample Product Details</p>
                                    <p class="text-center">
                                        <input type="button" name="viewproduct" value="View" class="view">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card productcontainer">
                                <div class="ccc">
                                    <p class="text-center"><img src="https://raw.githubusercontent.com/rxhack/productImage/main/3.jpg" class="imw"></p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-center">Apple MacBook</h5>
                                    <p class="text-center">Sample Product Details</p>
                                    <p class="text-center">
                                        <input type="button" name="viewproduct" value="View" class="view">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row product">
                        <div class="col-md-4 ">
                            <div class="card productcontainer">
                                <div class="ccc">
                                    <p class="text-center"><img src="https://raw.githubusercontent.com/rxhack/productImage/main/4.jpg" class="imw"></p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-center">Apple imac</h5>
                                    <p class="text-center">Sample Product Details</p>
                                    <p class="text-center">
                                        <input type="button" name="viewproduct" value="View" class="view">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card productcontainer">
                                <div class="ccc">
                                    <p class="text-center"><img src="https://raw.githubusercontent.com/rxhack/productImage/main/6.jpg" class="imw"></p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-center">Apple ipad Air</h5>
                                    <p class="text-center">Sample Product Details</p>
                                    <p class="text-center">
                                        <input type="button" name="viewproduct" value="View" class="view">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card productcontainer">
                                <div class="ccc">
                                    <p class="text-center"><img src="https://raw.githubusercontent.com/rxhack/productImage/main/7.jpg" class="imw"></p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-center">Apple iphone X</h5>
                                    <p class="text-center">Sample Product Details</p>
                                    <p class="text-center">
                                        <input type="button" name="viewproduct" value="View" class="view">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CREATE PRODUCT -->
                <div class="modal fade text-left w-100" id="add-product" tabindex="-1" role="dialog" aria-labelledby="myModalLabel20" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel20">
                                    Add Product
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addproductform" action="javascript:void(0)" method="post" data-parsley-validate>
                                    <div class="table-cotainer">
                                        <div class="container-fluid requiredfields" id="requiredfields">
                                            
                                        </div>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
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