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
                                <button type="button" class="btn btn-secondary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#navigationbartitlemodal"><i class="mdi mdi-pen"></i></button>
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
                                    <div class="card-body button change-bool">
                                        <input type="hidden" name="type" value="isProductsInNavigationBar">
                                        <label class="badge bg-success">Showing</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="card">
                                    <div class="card-header">
                                        All Products in Featured
                                    </div>
                                    <div class="card-body button change-bool">
                                        <input type="hidden" name="type" value="isProductsInFeatured">
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

<!-- Navigation Bar Title Modal -->
<div class="modal fade" id="navigationbartitlemodal" tabindex="-1" role="dialog" aria-labelledby="navigationbartitlemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="navigationbartitlemodaltitle">Navigation Bar Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatenavigationbartitleform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewnavigationbartitlecontent" id="viewnavigationbartitlecontent" placeholder="Contents" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewnavigationbartitlecontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-navigationbartitle">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>