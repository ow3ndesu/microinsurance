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
                                <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#aboutussubtitlemodal"><i class="mdi mdi-pen"></i></button>
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
                                        <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#aboutusmodal"><i class="mdi mdi-pen"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="card">
                                    <div class="card-header">
                                        About Us Left Image
                                    </div>
                                    <div class="card-body button" data-bs-toggle="modal" data-bs-target="#aboutusleftimagemodal">
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

<!-- About Us Subtitle Modal -->
<div class="modal fade" id="aboutussubtitlemodal" tabindex="-1" role="dialog" aria-labelledby="aboutussubtitlemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aboutussubtitlemodaltitle">About Us Subtitle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateaboutussubtitleform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewaboutussubtitlecontent" id="viewaboutussubtitlecontent" placeholder="Contents" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewaboutussubtitlecontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-aboutussubtitle">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- About Us Modal -->
<div class="modal fade" id="aboutusmodal" tabindex="-1" role="dialog" aria-labelledby="aboutusmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aboutusmodaltitle">About Us</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div>Characteristics</div>
                        <div class="buttons">
                            <a href="#add" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-characteristic">Add Characteristic</a>
                        </div>
                    </div>
                    <div class="card-body" id="card-body">
                        <div class="table-cotainer">
                            <div class="table-responsive">
                                <table class="table align-middle text-start bdr" id="aboutustable">
                                    <thead class="table-head ">
                                        <tr>
                                            <th>Avatar</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Stars</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider" id="aboutustablebody">
                                        <!-- data loads here  -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Characteristic Modal -->
<div class="modal fade text-left w-100" id="add-characteristic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel20" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel20">
                    Add Characteristic
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addcharacteristicform" action="javascript:void(0)" method="post" data-parsley-validate>
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

<!-- About Us Left Image Modal -->
<div class="modal fade" id="aboutusleftimagemodal" tabindex="-1" role="dialog" aria-labelledby="aboutusleftimagemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aboutusleftimagemodaltitle">About Us Left Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateaboutusleftimageform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <input type="file" name="viewaboutusleftimagetitle" id="viewaboutusleftimagetitle" placeholder="About Us Left Image" class="form-control m-1" accept="image/png, image/jpeg" data-parsley-required="true" disabled required>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-aboutusleftimage">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>