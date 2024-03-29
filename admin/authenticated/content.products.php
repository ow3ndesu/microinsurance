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
                                <span id="productsubtitletitle"> ... </span>
                                <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#productsubtitlemodal"><i class="mdi mdi-pen"></i></button>
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
                                <button type="button" class="btn btn-primary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#productsmodal"><i class="mdi mdi-pen"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Subtitle Modal -->
<div class="modal fade" id="productsubtitlemodal" tabindex="-1" role="dialog" aria-labelledby="productsubtitlemodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productsubtitlemodaltitle">Product Subtitle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateproductsubtitleform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-floating">
                                        <input type="text" name="viewproductsubtitlecontent" id="viewproductsubtitlecontent" placeholder="Contents" class="form-control m-1" data-parsley-required="true" disabled required>
                                        <label for="viewproductsubtitlecontent">Contents</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit-productsubtitle">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Products Modal -->
<div class="modal fade" id="productsmodal" tabindex="-1" role="dialog" aria-labelledby="productsmodaltitle" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productsmodaltitle">Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateproductsform" action="javascript:void(0)" method="post" data-parsley-validate>
                    <div class="table-cotainer">
                        <div class="container-fluid requiredfields" id="requiredfields">
                            <div class="container">
                                <div class="row product">
                                    <div class="col-md-3 ">
                                        <div class="card productcontainer">
                                            <div class="ccc">
                                                <p class="text-center"><img src="../assets/images/products/benlife-shield.png" class="imw"></p>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="text-center">BENLIFE SHIELD</h5>
                                                <p class="text-center">Single-Pay Micro Personal Accident</p>
                                                <p class="text-center">
                                                    <input type="hidden" name="id" value="product1">
                                                    <input type="button" name="viewproduct" value="View" class="view">
                                                    <input type="button" name="setAsFeatured" value="Set As Featured" class="set change-featured">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card productcontainer">
                                            <div class="ccc">
                                                <p class="text-center"><img src="../assets/images/products/benlife-shield.png" class="imw"></p>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="text-center">B-SHIELD + KONSULTA MD</h5>
                                                <p class="text-center">Single-Pay Micro Personal Accident</p>
                                                <p class="text-center">
                                                    <input type="hidden" name="id" value="product2">
                                                    <input type="button" name="viewproduct" value="View" class="view">
                                                    <input type="button" name="setAsFeatured" value="Set As Featured" class="set change-featured">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card productcontainer">
                                            <div class="ccc">
                                                <p class="text-center"><img src="../assets/images/products/benlife-erx.png" class="imw"></p>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="text-center">BENLIFE ERx</h5>
                                                <p class="text-center">Emergency Room <br>Care</p>
                                                <p class="text-center">
                                                    <input type="hidden" name="id" value="product3">
                                                    <input type="button" name="viewproduct" value="View" class="view">
                                                    <input type="button" name="setAsFeatured" value="Set As Featured" class="set change-featured">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 ">
                                        <div class="card productcontainer">
                                            <div class="ccc">
                                                <p class="text-center"><img src="../assets/images/products/benlife-erx.png" class="imw"></p>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="text-center">BENLIFE NET</h5>
                                                <p class="text-center">Mosquito Borne Disease <br>Cover</p>
                                                <p class="text-center">
                                                    <input type="hidden" name="id" value="product4">
                                                    <input type="button" name="viewproduct" value="View" class="view">
                                                    <input type="button" name="setAsFeatured" value="Set As Featured" class="set change-featured">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>