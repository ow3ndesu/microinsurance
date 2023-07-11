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
    <style>
        .modal-body#livepreviewmodalbody::-webkit-scrollbar {
            display: none !important;
            width: 0 !important
        }
    </style>
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
                                <li class="breadcrumb-item active" aria-current="page">Contents</li>
                            </ol>
                        </nav>
                        <h1 class="mb-0 fw-bold">Contents</h1>
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
                <div class="row">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body d-flex justify-content-center">
                                Design your ✨ <label class="badge bg-primary">Landing Page</label> ✨ as you like!
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="row">
                            <div class="col-md">
                                <div class="card">
                                    <div class="card-body d-flex justify-content-center">
                                        <button type="button" class="btn btn-primary m-1" data-bs-toggle="modal" data-bs-target="#livepreviewmodal">Live Preview</button>
                                        <button type="button" class="btn btn-secondary m-1" title="Reload"><i class="mdi mdi-reload"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="navigation">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"><i class="mdi mdi-view-dashboard"></i> Navigation Bar</div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Navigation Bar Title
                                            </div>
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                ASKI MI
                                                <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="row">
                                            <div class="col-md">
                                                <div class="card">
                                                    <div class="card-header">
                                                        All Products in Navigation Bar
                                                    </div>
                                                    <div class="card-body">
                                                        <label class="badge bg-success">Showing</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="card">
                                                    <div class="card-header">
                                                        All Products in Featured
                                                    </div>
                                                    <div class="card-body">
                                                        <label class="badge bg-danger">Not showing</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="home">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"><i class="mdi mdi-home"></i> Home</div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Banner Text Title
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md d-flex justify-content-between align-items-center">
                                                        Welcome to ASKI Micro Insurance
                                                        <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md d-flex justify-content-between align-items-center">
                                                        Highlighted Text
                                                        <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Banner Text Contents
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md">
                                                        At ASKI MI, we offer a comprehensive selection of insurance products designed to provide you with the security you need. Explore our diverse range of offerings and select the perfect insurance solution that suits your requirements. We sincerely look forward to having you join our valued clientele!
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md d-flex justify-content-between align-items-center">
                                                        Two (2) Highlighted Texts
                                                        <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Banner Button
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md">
                                                        KNOW US BETTER
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md d-flex justify-content-between align-items-center">
                                                        #about
                                                        <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Banner Image
                                            </div>
                                            <div class="card-body">
                                                <img src="../../assets/images/banner-bg.png" alt="Banner Image" width="100%" height="100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="products">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"><i class="mdi mdi-server-security"></i> Products</div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Featured Products Subtitle
                                            </div>
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                Look for what is ideal to you here!
                                                <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                All Products
                                            </div>
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                Product and Product Details
                                                <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="testimonials">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"><i class="mdi mdi-comment-multiple-outline"></i> Testimonials</div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Testimonials Subtitle
                                            </div>
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                We are delighted to share that our clients have expressed utmost satisfaction with our services. Now, we eagerly await the opportunity to welcome you into our fold!
                                                <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Testimonials
                                            </div>
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                Four (4) Client Testimonials
                                                <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="about">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"><i class="mdi mdi-account-card-details"></i> About Us</div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                About Us Subtitle
                                            </div>
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                What does ASKI Micro Insurance really do?
                                                <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="row">
                                            <div class="col-md">
                                                <div class="card">
                                                    <div class="card-header">
                                                        About Us
                                                    </div>
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        Three (3) Characteristics
                                                        <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="card">
                                                    <div class="card-header">
                                                        About Us Left Image
                                                    </div>
                                                    <div class="card-body">
                                                        <img src="../../assets/images/left-image.png" alt="About Us Left Image" width="100%" height="100%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="contact">
                    <div class="col-md">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title"><i class="mdi mdi-email-alert"></i> Contact Us</div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Contact Us Title
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md">
                                                        More About ASKI MI
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md d-flex justify-content-between align-items-center">
                                                        Highlighted Text
                                                        <button type="button" class="btn btn-secondary m-1" title="Edit"><i class="mdi mdi-pen"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Contact Us Text Contents
                                            </div>
                                            <div class="card-body">
                                                Thank you for your interest in our company! We value your feedback, inquiries, and suggestions. If you have any questions regarding our products, services, or any general inquiries, our dedicated customer support team is here to assist you. Feel free to reach out to us through the contact form below, and we'll make sure to respond promptly to provide the information you need.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                E-Mail Card
                                            </div>
                                            <div class="card-body">
                                                <label class="badge bg-success">Showing</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="card">
                                            <div class="card-header">
                                                Footer Image
                                            </div>
                                            <div class="card-body">
                                                <img src="../../assets/images/footer-bg.png" alt="Footer Image" width="100%" height="100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="livepreviewmodal" tabindex="-1" role="dialog" aria-labelledby="livepreviewmodaltitle" aria-hidden="true">
                    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="livepreviewmodaltitle">Live Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="livepreviewmodalbody">
                                <div class="container-fluid">
                                    <iframe src="../../index.html" title="index.html" style="height: 95vh; width: 95vw;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
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
        var livepreviewmodal = document.getElementById('livepreviewmodal');

        livepreviewmodal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            let button = event.relatedTarget;
            // Extract info from data-bs-* attributes
            let recipient = button.getAttribute('data-bs-whatever');

            // Use above variables to manipulate the DOM
        });
    </script>



    <!-- Optional: Place to the bottom of scripts -->
    <script>
        const myModal = new bootstrap.Modal(document.getElementById('livepreviewmodal'), options)
    </script>
</body>

</html>