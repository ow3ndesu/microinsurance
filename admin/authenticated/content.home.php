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
                                        <span id="homebannertitle"> ... </span>
                                        <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#bannertexttitlemodal"><i class="mdi mdi-pen"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md d-flex justify-content-between align-items-center">
                                        Highlighted Text
                                        <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#bannertexttitlehighlightedtextmodal"><i class="mdi mdi-pen"></i></button>
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
                                        <span id="homebannertextcontents"> ... </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md d-flex justify-content-between align-items-center">
                                        Two (2) Highlighted Texts
                                        <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#bannertextcontentsandhighlightedtextsmodal"><i class="mdi mdi-pen"></i></button>
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
                                        <span id="homebannerbuttontitle"> ... </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md d-flex justify-content-between align-items-center">
                                        <span id="homebannerbuttontarget"> ... </span>
                                        <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#bannerbuttonmodal"><i class="mdi mdi-pen"></i></button>
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
                            <div class="card-body button" data-bs-toggle="modal" data-bs-target="#bannerimagemodal">
                                <img src="../../assets/images/banner-bg.png" alt="Banner Image" width="100%" height="100%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Banner Text Title Modal -->
<div class="modal fade" id="bannertexttitlemodal" tabindex="-1" role="dialog" aria-labelledby="bannertexttitlemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannertexttitlemodaltitle">Banner Text Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatebannertexttitleform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewbannertexttitlecontent" id="viewbannertexttitlecontent" placeholder="Contents" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewbannertexttitlecontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-bannertexttitle">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Banner Text Title Highlighted Modal -->
<div class="modal fade" id="bannertexttitlehighlightedtextmodal" tabindex="-1" role="dialog" aria-labelledby="bannertexttitlehighlightedtextmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannertexttitlehighlightedtextmodaltitle">Banner Text Title Highlighted</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatebannertexttitlehighlightedtextform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <select name="viewbannertexttitlehighlightedtextcontent" id="viewbannertexttitlehighlightedtextcontent" class="form-control m-1" data-parsley-required="true" disabled required>
                                            <option value=""></option>
                                        </select>
                                        <label for="viewbannertexttitlehighlightedtextcontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-bannertexttitlehighlightedtext">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>


<!-- Banner Text Contents & Highlighted Texts Modal -->
<div class="modal fade" id="bannertextcontentsandhighlightedtextsmodal" tabindex="-1" role="dialog" aria-labelledby="bannertextcontentsandhighlightedtextsmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannertextcontentsandhighlightedtextsmodaltitle">Banner Text Contents & Highlighted Texts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatebannertextcontentsandhighlightedtextsform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <textarea name="viewbannertextcontentscontent" id="viewbannertextcontentscontent" class="form-control m-1" minlength="50" data-parsley-required="true" disabled required>
                                            
                                        </textarea>
                                        <label for="viewbannertextcontentscontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <select name="viewbannerhighlightedcontents" id="viewbannerhighlightedcontents" class="form-control m-1" data-parsley-required="true" data-placeholder="Highlighted Contents" multiple disabled required>
                                            <option value=""></option>
                                        </select>
                                        <!-- <label for="viewbannerhighlightedcontents">Highlighted Contents</label> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-bannertextcontentsandhighlightedtexts">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Banner Button Modal -->
<div class="modal fade" id="bannerbuttonmodal" tabindex="-1" role="dialog" aria-labelledby="bannerbuttonmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannerbuttonmodaltitle">Banner Button</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatebannerbuttonform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewbannerbuttontitle" id="viewbannerbuttontitle" placeholder="Button Title" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewbannerbuttontitle">Button Title</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <select name="viewbannerbuttontarget" id="viewbannerbuttontarget" class="form-control m-1" data-parsley-required="true" disabled required>
                                            <option value="#products">#products</option>
                                            <option value="#testimonials">#testimonials</option>
                                            <option value="#about">#about</option>
                                            <option value="#contact-us">#contact-us</option>
                                        </select>
                                        <label for="viewbannerbuttontarget">Button Target</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-bannerbutton">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Banner Image Modal -->
<div class="modal fade" id="bannerimagemodal" tabindex="-1" role="dialog" aria-labelledby="bannerimagemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannerimagemodaltitle">Banner Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatebannerimageform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <input type="file" name="viewbannerimage" id="viewbannerimage" placeholder="Banner Image" class="form-control m-1" accept="image/png" data-parsley-required="true" disabled required>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-bannerimage">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>