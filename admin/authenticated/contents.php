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

                <?php include_once("content.navigation.php"); ?>

                <?php include_once("content.home.php"); ?>

                <?php include_once("content.products.php"); ?>

                <?php include_once("content.testimonials.php"); ?>

                <?php include_once("content.about.php"); ?>

                <?php include_once("content.contact.php"); ?>

                <!-- Live Preview Modal -->
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
        $(document).ready(() => {
            LoadEverything().then(() => {
                setTimeout(() => {
                    $(".preloader").fadeOut();
                }, 1000);
            });
        });

        // ON LOAD FUNCTIONS

        async function LoadEverything() {
            return await true;
        }

        // ====================================================================================

        // HELPER FUNCTIONS
        // ====================================================================================

        // TRIGGERS

        // <!-- Change Boolean Value --> //
        $('.change-bool').unbind('click').click(function(e) {
            const type = e.target.children[0].value;
            Swal.fire({
                title: 'Change Value?',
                text: "This will change its current value. Proceed?",
                icon: 'question',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonColor: '#435ebe',
                confirmButtonText: 'Yes, proceed!',
                allowOutsideClick: false,
                preConfirm: (e) => {
                    return $.ajax({
                        url: "../../routes/contents.route.php",
                        type: "POST",
                        data: {
                            action: "ChangeBooleanValue",
                            type: type
                        },
                        beforeSend: function() {
                            console.log('changing boolean value...')
                        },
                        success: function(response) {
                            return response;
                        },
                        error: function(err) {
                            console.log(err);
                        }
                    });
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value == 'SUCCESS') {
                        Swal.fire({
                            icon: "success",
                            text: `Successfuly Changed!`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error Changing Value.",
                            text: result.value,
                        })
                    }
                } else {
                    Swal.fire({
                        icon: "info",
                        text: "You've Cancelled Change.",
                    })
                }
            });
        });

        // <!-- Navigation Bar Title --> //
        $('#edit-navigationbartitle').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatenavigationbartitleform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatenavigationbartitleform').parsley().reset();
                    $('#updatenavigationbartitleform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-navigationbartitle';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Banner Text Title --> //
        $('#edit-bannertexttitle').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatebannertexttitleform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatebannertexttitleform').parsley().reset();
                    $('#updatebannertexttitleform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-bannertexttitle';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Banner Text Title Highlighted Text --> //
        $('#edit-bannertexttitlehighlightedtext').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatebannertexttitlehighlightedtextform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatebannertexttitlehighlightedtextform').parsley().reset();
                    $('#updatebannertexttitlehighlightedtextform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-bannertexttitlehighlightedtext';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Banner Text Contents & Highlighted Text --> //
        $('#edit-bannertextcontentsandhighlightedtexts').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatebannertextcontentsandhighlightedtextsform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatebannertextcontentsandhighlightedtextsform').parsley().reset();
                    $('#updatebannertextcontentsandhighlightedtextsform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-bannertextcontentsandhighlightedtexts';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Banner Button --> //
        $('#edit-bannerbutton').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatebannerbuttonform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatebannerbuttonform').parsley().reset();
                    $('#updatebannerbuttonform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-bannerbutton';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Banner Image --> //
        $('#edit-bannerimage').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatebannerimageform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatebannerimageform').parsley().reset();
                    $('#updatebannerimageform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-bannerimage';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Product Subtitle --> //
        $('#edit-productsubtitle').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updateproductsubtitleform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updateproductsubtitleform').parsley().reset();
                    $('#updateproductsubtitleform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-productsubtitle';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // ====================================================================================

        // SUBMIT
        // ====================================================================================
    </script>
</body>

</html>