<div class="page-banner" style="background-image: url('<?= get_asset_url("banner/submit-property-banner.jpg", "images") ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Submit Property</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Submit Property</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="submit-property-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="submit-property-form bg-white p-4 rounded shadow-sm">
                    <h3 class="mb-4 border-bottom pb-2">Property Details</h3>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['msg'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                    <?php endif; ?>

                    <?php
                    $is_edit = isset($property);
                    $action_url = $is_edit ? BASE_URL . "submit-property-update?id=" . $property['id'] : BASE_URL . "submit-property";
                    ?>

                    <form action="<?= $action_url ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Property Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Luxury 3BHK Apartment in City Center" value="<?= $is_edit ? h($property['title']) : '' ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Property Description</label>
                                <textarea name="content" class="form-control" rows="5" placeholder="Describe your property in detail..." required><?= $is_edit ? h($property['pcontent']) : '' ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Property Type</label>
                                <select class="form-select" name="ptype" required>
                                    <option value="">Select Type</option>
                                    <?php
                                    $types = ['apartment', 'flat', 'building', 'house', 'villa', 'office'];
                                    foreach($types as $t): ?>
                                        <option value="<?= $t ?>" <?= ($is_edit && $property['type'] == $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Selling Type</label>
                                <select class="form-select" name="stype" required>
                                    <option value="">Select Status</option>
                                    <option value="rent" <?= ($is_edit && $property['stype'] == 'rent') ? 'selected' : '' ?>>Rent</option>
                                    <option value="sale" <?= ($is_edit && $property['stype'] == 'sale') ? 'selected' : '' ?>>Sale</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">BHK</label>
                                <select class="form-select" name="bhk" required>
                                    <option value="">Select BHK</option>
                                    <?php
                                    $bhks = ['1 BHK', '2 BHK', '3 BHK', '4 BHK', '5 BHK', '1,2 BHK', '2,3 BHK', '2,3,4 BHK'];
                                    foreach($bhks as $b): ?>
                                        <option value="<?= $b ?>" <?= ($is_edit && $property['bhk'] == $b) ? 'selected' : '' ?>><?= $b ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bedroom</label>
                                <select class="form-select" name="bed" required>
                                    <option value="">Select Bedroom</option>
                                    <?php for($i=1; $i<=10; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($is_edit && $property['bedroom'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bathroom</label>
                                <select class="form-select" name="bath" required>
                                    <option value="">Select Bathroom</option>
                                    <?php for($i=1; $i<=10; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($is_edit && $property['bathroom'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kitchen</label>
                                <select class="form-select" name="kitc" required>
                                    <option value="">Select Kitchen</option>
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($is_edit && $property['kitchen'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price (₹)</label>
                                <input type="number" name="price" class="form-control" placeholder="Enter Price" value="<?= $is_edit ? h($property['price']) : '' ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">City</label>
                                <input type="text" name="city" class="form-control" placeholder="Enter City" value="<?= $is_edit ? h($property['city']) : '' ?>" required>
                            </div>

                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2">Property Images</h5>
                                <?php if($is_edit): ?>
                                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i> Leave file inputs empty to keep current images.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Featured Image</label>
                                <?php if($is_edit && !empty($property['pimage'])): ?>
                                    <div class="mb-2"><img src="<?= BASE_URL ?>public/uploads/property/<?= $property['pimage'] ?>" height="50" class="rounded border"></div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="aimage" <?= $is_edit ? '' : 'required' ?>>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Gallery Image 1</label>
                                <input type="file" class="form-control form-control-sm" name="aimage1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gallery Image 2</label>
                                <input type="file" class="form-control form-control-sm" name="aimage2">
                            </div>

                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2">Additional Information</h5>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="available" <?= ($is_edit && $property['status'] == 'available') ? 'selected' : '' ?>>Available</option>
                                    <option value="sold out" <?= ($is_edit && $property['status'] == 'sold out') ? 'selected' : '' ?>>Sold Out</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Features / Amenities</label>
                                <textarea class="form-control" name="feature" rows="3" placeholder="e.g. Swimming Pool, Gym, 24/7 Security, Car Parking" required><?= $is_edit ? h($property['feature']) : '' ?></textarea>
                            </div>

                            <div class="col-md-12 mt-4">
                                <button type="submit" name="add" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
                                    <i class="fas <?= $is_edit ? 'fa-save' : 'fa-plus-circle' ?> me-2"></i> <?= $is_edit ? 'Update Property' : 'Submit Property' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="property-guidelines bg-light p-4 rounded border">
                    <h3 class="h4 mb-4 text-primary"><i class="fas fa-info-circle me-2"></i>Submission Guidelines</h3>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span>Provide accurate and detailed information about your property.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span>Upload clear, high-quality images (JPG/PNG).</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span>Mention all unique features and amenities.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span>Provide correct contact information.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span>Your listing will be reviewed before being published.</span>
                        </li>
                    </ul>

                    <div class="mt-5 p-3 bg-white rounded border">
                        <h4 class="h5 mb-3">Need Help?</h4>
                        <p class="small text-muted mb-3">If you need assistance with submitting your property, please contact our support team:</p>
                        <p class="mb-2 fw-bold"><i class="fas fa-phone-alt text-primary me-2"></i> +91 7007444842</p>
                        <p class="mb-0 fw-bold"><i class="fas fa-envelope text-primary me-2"></i> info@apsdreamhomes.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
