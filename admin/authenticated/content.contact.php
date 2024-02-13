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
                                        More About ASKI MIU
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md d-flex justify-content-between align-items-center">
                                        Highlighted Text
                                        <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#contactustitleandhighlightedtextsmodal"><i class="mdi mdi-pen"></i></button>
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
                            <div class="card-body button" data-bs-toggle="modal" data-bs-target="#contactustextcontentsmodal">
                                Thank you for your interest in our company! We value your feedback, inquiries, and suggestions. If you have any questions regarding our products, services, or any general inquiries, our dedicated customer support team is here to assist you. Feel free to reach out to us through the contact form below, and we'll make sure to respond promptly to provide the information you need.
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card">
                            <div class="card-header">
                                E-Mail Card
                            </div>
                            <div class="card-body button change-bool">
                                <input type="hidden" name="type" value="isEmailCardShowing">
                                <label class="badge bg-success">Showing</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card">
                            <div class="card-header">
                                Footer Image
                            </div>
                            <div class="card-body button" data-bs-toggle="modal" data-bs-target="#footerimagemodal">
                                <img src="../../assets/images/footer-bg.png" alt="Footer Image" width="100%" height="100%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Us Title & Highlighted Texts Modal -->
<div class="modal fade" id="contactustitleandhighlightedtextsmodal" tabindex="-1" role="dialog" aria-labelledby="contactustitleandhighlightedtextsmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactustitleandhighlightedtextsmodaltitle">Contact Us Title & Highlighted Texts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatecontactustitleandhighlightedtextsform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewcontactustitlecontent" id="viewcontactustitlecontent" placeholder="Button Title" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewcontactustitlecontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <select name="viewbannerhighlightedcontents" id="viewbannerhighlightedcontents" class="form-control m-1" data-parsley-required="true" disabled required>
                                            <option value=""></option>
                                        </select>
                                        <label for="viewbannerhighlightedcontents">Highlighted Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-contactustitleandhighlightedtexts">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Contact Us Text Contents Modal -->
<div class="modal fade" id="contactustextcontentsmodal" tabindex="-1" role="dialog" aria-labelledby="contactustextcontentsmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactustextcontentsmodaltitle">Contact Us Text Contents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatecontactustextcontentsform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <textarea name="viewcontactustextcontents" id="viewcontactustextcontents" class="form-control m-1" minlength="50" data-parsley-required="true" disabled required>
                                            
                                        </textarea>
                                        <label for="viewcontactustextcontents">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-contactustextcontents">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer Image Modal -->
<div class="modal fade" id="footerimagemodal" tabindex="-1" role="dialog" aria-labelledby="footerimagemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="footerimagemodaltitle">Footer Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatefooterimageform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <input type="file" name="viewfooterimage" id="viewfooterimage" placeholder="Footer Image" class="form-control m-1" accept="image/png, image/jpeg" data-parsley-required="true" disabled required>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-footerimage">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>