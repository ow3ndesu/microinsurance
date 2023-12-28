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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Product CSS -->
    <link href="../assets/css/products.css" rel="stylesheet">
    <style>
        .modal-body#livepreviewmodalbody::-webkit-scrollbar {
            display: none !important;
            width: 0 !important
        }

        #topbtn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 99;
            border: none;
            outline: none;
            cursor: pointer;
            border-radius: 4px;
            color: #fff;
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

        <button type="button" id="topbtn" class="btn btn-danger m-1" title="Go To Top" onclick="window.location.href='#top'"><i class="mdi mdi-arrow-up"></i></button>

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
                                        <button type="button" class="btn btn-secondary m-1" title="Reload" onclick="window.location.reload(true)"><i class="mdi mdi-reload"></i></button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(() => {
            console.log('loading contents...');
            LoadEverything().then(() => {
                setTimeout(() => {
                    $(".preloader").fadeOut();
                }, 2000);
            });

            window.onscroll = function() {
                scrollFunction();
            };
        });

        // ON LOAD FUNCTIONS

        async function LoadEverything() {
            LoadNavigationBar();
            LoadHome();
            LoadProducts();
        }

        // <!-- Go To Top --> //
        function scrollFunction() {
            const mybutton = document.getElementById("topbtn");
            (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) ? mybutton.style.display = "block" :  mybutton.style.display = "none";
        }

        // <!-- Navigation Bar --> //
        async function LoadNavigationBar() {
            await $.ajax({
                url: "../../routes/contents.route.php",
                type: "POST",
                data: {
                    action: "LoadNavigationBar"
                },
                dataType: "JSON",
                beforeSend: function() {
                    console.log('loading navigation bar...');
                },
                success: function(response) {
                    const title = response.TITLE;
                    const isProductsInNavigationBar = (response.ISPRODUCTSINNAVTIGATION) ? '<label class="badge bg-success">Showing</label>' : '<label class="badge bg-danger">Not showing</label>';
                    const isProductsInFeatured = (response.ISPRODUCTSINFEATURED) ? '<label class="badge bg-success">Showing</label>' : '<label class="badge bg-danger">Not showing</label>';

                    $('#navigationbartitle').text(title);
                    $('#viewnavigationbartitlecontent').val(title);

                    $('.change-bool').find(':input[value="isProductsInNavigationBar"]').siblings().last().remove()
                    $('.change-bool').find(':input[value="isProductsInNavigationBar"]').after(isProductsInNavigationBar);

                    $('.change-bool').find(':input[value="isProductsInFeatured"]').siblings().last().remove()
                    $('.change-bool').find(':input[value="isProductsInFeatured"]').after(isProductsInFeatured);
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        // <!-- Home Banner --> //
        async function LoadHome() {
            await $.ajax({
                url: "../../routes/contents.route.php",
                type: "POST",
                data: {
                    action: "LoadHome"
                },
                dataType: "JSON",
                beforeSend: function() {
                    console.log('loading home...');
                },
                success: function(response) {
                    const title = response.TITLE;
                    const titleselect = response.TITLESELECT;
                    const titlehighlightedtext = response.TITLEHIGHLIGHTEDTEXT;
                    const text = response.TEXT;
                    const textselect = response.TEXTSELECT;
                    const texthighlightedtext = response.TEXTHIGHLIGHTEDTEXT;
                    const button = response.BUTTON;
                    const target = response.BUTTONTARGET;

                    $('#homebannertitle').text(title);
                    $('#viewbannertexttitlecontent').val(title);

                    $('#viewbannertexttitlehighlightedtextcontent').empty();
                    titleselect.forEach(element => {
                        $('#viewbannertexttitlehighlightedtextcontent').append(`<option value="${ element }">${ element }</option>`);
                    });
                    $('#viewbannertexttitlehighlightedtextcontent').val(titlehighlightedtext);

                    $('#homebannertextcontents').text(text);
                    $('#viewbannertextcontentscontent').val(text);

                    $('#viewbannerhighlightedcontents').empty();
                    textselect.forEach(element => {
                        $('#viewbannerhighlightedcontents').append(`<option value="${ element }">${ element }</option>`);
                    });
                    $('#viewbannerhighlightedcontents').val(texthighlightedtext);

                    $('#viewbannerhighlightedcontents').select2({
                        theme: "bootstrap-5",
                        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                        placeholder: $(this).data('placeholder'),
                        closeOnSelect: false,
                    });

                    $('#homebannerbuttontitle').text(button);
                    $('#viewbannerbuttontitle').val(button);
                    $('#homebannerbuttontarget').text(target);
                    $('#viewbannerbuttontarget').val(target);
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        // <!-- Products --> //
        async function LoadProducts() {
            await $.ajax({
                url: "../../routes/contents.route.php",
                type: "POST",
                data: {
                    action: "LoadProducts"
                },
                dataType: "JSON",
                beforeSend: function() {
                    console.log('loading products...');
                },
                success: function(response) {
                    const subtitle = response.FEATUREDSUBTITLE;

                    $('#productsubtitletitle').text(subtitle);
                    $('#viewproductsubtitlecontent').val(subtitle);
                },
                error: function(err) {
                    console.log(err);
                }
            });
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
                        }).then(() => {
                            window.location.reload(true)
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

        // <!-- Change Product Featured --> //
        $('.change-featured').unbind('click').click(function(e) {
            const id = e.target.parentElement.children[0].value;
            Swal.fire({
                title: 'Set As Featured?',
                text: "This will set this product as featured. Proceed?",
                icon: 'question',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonColor: '#435ebe',
                confirmButtonText: 'Yes, proceed!',
                allowOutsideClick: false,
                preConfirm: (e) => {
                    return $.ajax({
                        url: "../../routes/products.route.php",
                        type: "POST",
                        data: {
                            action: "SetAsFeatured",
                            id: id
                        },
                        beforeSend: function() {
                            console.log('setting product as featured...')
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
                            title: "Error Setting Featured.",
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

        // <!-- Change Product Featured --> //
        $('.remove-featured').unbind('click').click(function(e) {
            const id = e.target.parentElement.children[0].value;
            Swal.fire({
                title: 'Remove As Featured?',
                text: "This will remove this product as featured. Proceed?",
                icon: 'question',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonColor: '#435ebe',
                confirmButtonText: 'Yes, proceed!',
                allowOutsideClick: false,
                preConfirm: (e) => {
                    return $.ajax({
                        url: "../../routes/products.route.php",
                        type: "POST",
                        data: {
                            action: "RemoveAsFeatured",
                            id: id
                        },
                        beforeSend: function() {
                            console.log('removing product as featured...')
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
                            text: `Successfuly Removed!`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error Removing Featured.",
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

        // <!-- About Us Subtitle --> //
        $('#edit-aboutussubtitle').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updateaboutussubtitleform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updateaboutussubtitleform').parsley().reset();
                    $('#updateaboutussubtitleform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-aboutussubtitle';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- About Us Left Image --> //
        $('#edit-aboutusleftimage').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updateaboutusleftimageform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updateaboutusleftimageform').parsley().reset();
                    $('#updateaboutusleftimageform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-aboutusleftimage';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Contact Us Title --> //
        $('#edit-contactustitleandhighlightedtexts').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatecontactustitleandhighlightedtextsform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatecontactustitleandhighlightedtextsform').parsley().reset();
                    $('#updatecontactustitleandhighlightedtextsform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-contactustitleandhighlightedtexts';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Contact Us Text Contents --> //
        $('#edit-contactustextcontents').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatecontactustextcontentsform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatecontactustextcontentsform').parsley().reset();
                    $('#updatecontactustextcontentsform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-contactustextcontents';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- About Us Left Image --> //
        $('#edit-footerimage').unbind('click').click(function(e) {
            const update = e.target;
            const cancel = e.target.parentNode.children[1];
            if ($('#updatefooterimageform *').find(':input').prop('disabled', false)) {
                setTimeout(() => {
                    update.type = 'submit', update.textContent = 'Submit', update.id = 'update-item';
                }, 10);
                $(cancel).removeAttr('data-bs-dismiss'), cancel.textContent = 'Cancel', cancel.id = 'cancel-update';

                $(cancel).click(function(e) {
                    $('#updatefooterimageform').parsley().reset();
                    $('#updatefooterimageform *').find(':input').prop('disabled', true);
                    update.type = 'button', update.textContent = 'Update', update.id = 'edit-footerimage';
                    $(cancel).attr('data-bs-dismiss', 'modal').removeAttr('id'), cancel.textContent = 'Close';
                });
            }
        });

        // <!-- Banner Text Contents & Highlighted Text --> //
        $("#viewbannertextcontentscontent").on('input', function() {
            const textselect = (this.value).split(' ');

            $('#viewbannerhighlightedcontents').empty();
            textselect.forEach(element => {
                $('#viewbannerhighlightedcontents').append(`<option value="${ element }">${ element }</option>`);
            });
        });

        // ====================================================================================

        // SUBMIT

        // <!-- Navigation Bar Title --> //
        $('#updatenavigationbartitleform').unbind('submit').submit(function() {

            let formdata = new FormData();

            if ($('#updatenavigationbartitleform').parsley().isValid()) {

                formdata.append('action', 'UpdateNavigationBarTitle');
                $('#updatenavigationbartitleform *').find(':input:not(:button)').each((index, element) => {
                    formdata.append((element.id).slice(4), element.value);
                });

                Swal.fire({
                    title: 'Proceed Submission?',
                    text: `This action will update Navigation Bar Title. Proceed?`,
                    icon: 'question',
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Yes, proceed!',
                    focusConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: (e) => {
                        return $.ajax({
                            url: "../../routes/contents.route.php",
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            cache: false,
                            beforeSend: function() {
                                console.log(`updating pending navigation bar title...`)
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
                                text: `Navigation Bar Title Successfuly Updated!`,
                            }).then(() => {
                                window.location.reload(true)
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error Updating Navigation Bar Title.",
                                text: result.value,
                            })
                        }
                    } else {
                        Swal.fire({
                            icon: "info",
                            text: "You've Cancelled Update.",
                        })
                    }
                });
            }
        });

        // <!-- Banner Text Title --> //
        $('#updatebannertexttitleform').unbind('submit').submit(function() {

            let formdata = new FormData();

            if ($('#updatebannertexttitleform').parsley().isValid()) {

                formdata.append('action', 'UpdateHomeBannerTitle');
                $('#updatebannertexttitleform *').find(':input:not(:button)').each((index, element) => {
                    formdata.append((element.id).slice(4), element.value);
                });

                Swal.fire({
                    title: 'Proceed Submission?',
                    text: `This action will update Home Banner Title. Proceed?`,
                    icon: 'question',
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Yes, proceed!',
                    focusConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: (e) => {
                        return $.ajax({
                            url: "../../routes/contents.route.php",
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            cache: false,
                            beforeSend: function() {
                                console.log(`updating pending home banner title...`)
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
                                text: `Home Banner Title Successfuly Updated!`,
                            }).then(() => {
                                window.location.reload(true)
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error Updating Home Banner Title.",
                                text: result.value,
                            })
                        }
                    } else {
                        Swal.fire({
                            icon: "info",
                            text: "You've Cancelled Update.",
                        })
                    }
                });
            }
        });

        // <!-- Banner Text Title Highlighted Text --> //
        $('#updatebannertexttitlehighlightedtextform').unbind('submit').submit(function() {

            let formdata = new FormData();

            if ($('#updatebannertexttitlehighlightedtextform').parsley().isValid()) {

                formdata.append('action', 'UpdateHomeBannerTitleHighlightedText');
                $('#updatebannertexttitlehighlightedtextform *').find(':input:not(:button)').each((index, element) => {
                    formdata.append((element.id).slice(4), element.value);
                });

                Swal.fire({
                    title: 'Proceed Submission?',
                    text: `This action will update Home Banner Title Highlighted Text. Proceed?`,
                    icon: 'question',
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Yes, proceed!',
                    focusConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: (e) => {
                        return $.ajax({
                            url: "../../routes/contents.route.php",
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            cache: false,
                            beforeSend: function() {
                                console.log(`updating pending home banner title highlighted text...`)
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
                                text: `Home Banner Title Highlighted Text Successfuly Updated!`,
                            }).then(() => {
                                window.location.reload(true)
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error Updating Home Banner Title Highlighted Text.",
                                text: result.value,
                            })
                        }
                    } else {
                        Swal.fire({
                            icon: "info",
                            text: "You've Cancelled Update.",
                        })
                    }
                });
            }
        });

        // <!-- Banner Text Contents & Highlighted Text --> //
        $('#updatebannertextcontentsandhighlightedtextsform').unbind('submit').submit(function() {

            let formdata = new FormData();

            if ($('#updatebannertextcontentsandhighlightedtextsform').parsley().isValid()) {

                formdata.append('action', 'UpdateHomeBannerTextContentsHighlightedText');
                $('#updatebannertextcontentsandhighlightedtextsform *').find(':input:not(:button)').each((index, element) => {
                    formdata.append((element.id).slice(4), element.value);
                });

                formdata.set('bannerhighlightedcontents', ($('#viewbannerhighlightedcontents').val()).join(', '));

                Swal.fire({
                    title: 'Proceed Submission?',
                    text: `This action will update Home Banner Text Contents & Highlighted Text. Proceed?`,
                    icon: 'question',
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Yes, proceed!',
                    focusConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: (e) => {
                        return $.ajax({
                            url: "../../routes/contents.route.php",
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            cache: false,
                            beforeSend: function() {
                                console.log(`updating pending home banner text & highlighted text...`)
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
                                text: `Home Banner Text Contents & Highlighted Text Successfuly Updated!`,
                            }).then(() => {
                                window.location.reload(true)
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error Updating Home Banner Text Contents & Highlighted Text.",
                                text: result.value,
                            })
                        }
                    } else {
                        Swal.fire({
                            icon: "info",
                            text: "You've Cancelled Update.",
                        })
                    }
                });
            }
        });

        // <!-- Banner Button --> //
        $('#updatebannerbuttonform').unbind('submit').submit(function() {

            let formdata = new FormData();

            if ($('#updatebannerbuttonform').parsley().isValid()) {

                formdata.append('action', 'UpdateHomeBannerButton');
                $('#updatebannerbuttonform *').find(':input:not(:button)').each((index, element) => {
                    formdata.append((element.id).slice(4), element.value);
                });

                Swal.fire({
                    title: 'Proceed Submission?',
                    text: `This action will update Home Banner Button. Proceed?`,
                    icon: 'question',
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Yes, proceed!',
                    focusConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: (e) => {
                        return $.ajax({
                            url: "../../routes/contents.route.php",
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            cache: false,
                            beforeSend: function() {
                                console.log(`updating pending home banner button...`)
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
                                text: `Home Banner Button Successfuly Updated!`,
                            }).then(() => {
                                window.location.reload(true)
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error Updating Home Banner Button.",
                                text: result.value,
                            })
                        }
                    } else {
                        Swal.fire({
                            icon: "info",
                            text: "You've Cancelled Update.",
                        })
                    }
                });
            }
        });

        // <!-- Banner Image --> //
        $("#updatebannerimageform").unbind("submit").submit(function() {

            let formdata = new FormData();
            const viewbannerimage = $("#viewbannerimage")[0].files;

            if ($('#updatebannerimageform').parsley().isValid()) {

                formdata.append('action', 'UpdateHomeBannerImage');
                formdata.append('bannerimage', viewbannerimage[0]);
                Swal.fire({
                    title: 'Proceed Submission?',
                    text: `This action will update Home Banner Image. Proceed?`,
                    icon: 'question',
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Yes, proceed!',
                    focusConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: (e) => {
                        return $.ajax({
                            url: "../../routes/contents.route.php",
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            cache: false,
                            beforeSend: function() {
                                console.log(`updating pending home banner image...`)
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
                                text: `Home Banner Image Successfuly Updated!`,
                            }).then(() => {
                                window.location.reload(true)
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error Updating Home Banner Image.",
                                text: result.value,
                            })
                        }
                    } else {
                        Swal.fire({
                            icon: "info",
                            text: "You've Cancelled Update.",
                        })
                    }
                });
            }

        });

        // ====================================================================================
    </script>
</body>

</html>