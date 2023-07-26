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
                                <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#testimonialssubtitlemodal"><i class="mdi mdi-pen"></i></button>
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
                                <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#testimonialsmodal"><i class="mdi mdi-pen"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Subtitle Modal -->
<div class="modal fade" id="testimonialssubtitlemodal" tabindex="-1" role="dialog" aria-labelledby="testimonialssubtitlemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimonialssubtitlemodaltitle">Testimonials Subtitle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatetestimonialssubtitleform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewtestimonialssubtitlecontent" id="viewtestimonialssubtitlecontent" placeholder="Contents" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewtestimonialssubtitlecontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-testimonialssubtitle">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Testimonials Modal -->
<div class="modal fade" id="testimonialsmodal" tabindex="-1" role="dialog" aria-labelledby="testimonialsmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimonialsmodaltitle">Testimonials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div>Testimonials</div>
                        <div class="buttons">
                            <a href="#add" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-testimonial">Add Testimonials</a>
                        </div>
                    </div>
                    <div class="card-body" id="card-body">
                        <div class="table-cotainer">
                            <div class="table-responsive">
                                <table class="table align-middle text-start bdr" id="testimonialstable">
                                    <thead class="table-head ">
                                        <tr>
                                            <th>Avatar</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Stars</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider" id="testimonialstablebody">
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

<!-- Add Testimonial Modal -->
<div class="modal fade text-left w-100" id="add-testimonial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel20" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel20">
                    Add Testimonial
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addtestimonialform" action="javascript:void(0)" method="post" data-parsley-validate>
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