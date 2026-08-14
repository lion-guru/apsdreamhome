<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '+91 92771 21112'); ?>
<div class="page-banner" class="style-52830"banner/submit-property-banner.jpg", "images") ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title"><?php echo __('submit_property_title', 'Submit Property'); ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?php echo __('nav_home', 'Home'); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo __('submit_property_title', 'Submit Property'); ?></li>
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
                    <h3 class="mb-4 border-bottom pb-2"><?php echo __('property_details', 'Property Details'); ?></h3>

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
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?= csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold"><?php echo __('property_title_label', 'Property Title'); ?></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Luxury 3BHK Apartment in City Center" value="<?= $is_edit ? h($property['title']) : '' ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold"><?php echo __('property_description_label', 'Property Description'); ?></label>
                                <textarea name="content" class="form-control" rows="5" placeholder="Describe your property in detail..." required><?= $is_edit ? h($property['pcontent']) : '' ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_type_label', 'Property Type'); ?></label>
                                <select class="form-select" name="ptype" required>
                                    <option value=""><?php echo __('property_select_type', 'Select Type'); ?></option>
                                    <?php
                                    $types = ['apartment', 'flat', 'building', 'house', 'villa', 'office'];
                                    foreach($types as $t): ?>
                                        <option value="<?= $t ?>" <?= ($is_edit && $property['type'] == $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_selling_type', 'Selling Type'); ?></label>
                                <select class="form-select" name="stype" required>
                                    <option value=""><?php echo __('property_select_status', 'Select Status'); ?></option>
                                    <option value="rent" <?= ($is_edit && $property['stype'] == 'rent') ? 'selected' : '' ?>><?php echo __('property_rent', 'Rent'); ?></option>
                                    <option value="sale" <?= ($is_edit && $property['stype'] == 'sale') ? 'selected' : '' ?>><?php echo __('property_sale', 'Sale'); ?></option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_bhk', 'BHK'); ?></label>
                                <select class="form-select" name="bhk" required>
                                    <option value=""><?php echo __('property_select_bhk', 'Select BHK'); ?></option>
                                    <?php
                                    $bhks = ['1 BHK', '2 BHK', '3 BHK', '4 BHK', '5 BHK', '1,2 BHK', '2,3 BHK', '2,3,4 BHK'];
                                    foreach($bhks as $b): ?>
                                        <option value="<?= $b ?>" <?= ($is_edit && $property['bhk'] == $b) ? 'selected' : '' ?>><?= $b ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_bedroom', 'Bedroom'); ?></label>
                                <select class="form-select" name="bed" required>
                                    <option value=""><?php echo __('property_select_bedroom', 'Select Bedroom'); ?></option>
                                    <?php for($i=1; $i<=10; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($is_edit && $property['bedroom'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_bathroom', 'Bathroom'); ?></label>
                                <select class="form-select" name="bath" required>
                                    <option value=""><?php echo __('property_select_bathroom', 'Select Bathroom'); ?></option>
                                    <?php for($i=1; $i<=10; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($is_edit && $property['bathroom'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_kitchen', 'Kitchen'); ?></label>
                                <select class="form-select" name="kitc" required>
                                    <option value=""><?php echo __('property_select_kitchen', 'Select Kitchen'); ?></option>
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($is_edit && $property['kitchen'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_price', 'Price (â‚¹)'); ?></label>
                                <input type="number" name="price" class="form-control" placeholder="<?php echo __('property_enter_price', 'Enter Price'); ?>" value="<?= $is_edit ? h($property['price']) : '' ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_city', 'City'); ?></label>
                                <input type="text" name="city" class="form-control" placeholder="<?php echo __('property_enter_city', 'Enter City'); ?>" value="<?= $is_edit ? h($property['city']) : '' ?>" required>
                            </div>

                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2"><?php echo __('property_images', 'Property Images'); ?></h5>
                                <?php if($is_edit): ?>
                                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i> <?php echo __('property_leave_empty_images', 'Leave file inputs empty to keep current images.'); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold"><?php echo __('property_featured_image', 'Featured Image'); ?></label>
                                <?php if($is_edit && !empty($property['pimage'])): ?>
                                    <div class="mb-2"><img src="<?= BASE_URL ?>/public/uploads/property/<?= htmlspecialchars($property['pimage']) ?>" height="50" class="rounded border" alt="Property image"></div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="aimage" <?= $is_edit ? '' : 'required' ?>>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><?php echo __('property_gallery_image_1', 'Gallery Image 1'); ?></label>
                                <input type="file" class="form-control form-control-sm" name="aimage1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo __('property_gallery_image_2', 'Gallery Image 2'); ?></label>
                                <input type="file" class="form-control form-control-sm" name="aimage2">
                            </div>

                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2"><?php echo __('property_additional_info', 'Additional Information'); ?></h5>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold"><?php echo __('property_status', 'Status'); ?></label>
                                <select class="form-select" name="status" required>
                                    <option value=""><?php echo __('property_select_status', 'Select Status'); ?></option>
                                    <option value="available" <?= ($is_edit && $property['status'] == 'available') ? 'selected' : '' ?>><?php echo __('property_available', 'Available'); ?></option>
                                    <option value="sold out" <?= ($is_edit && $property['status'] == 'sold out') ? 'selected' : '' ?>><?php echo __('property_sold_out', 'Sold Out'); ?></option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold"><?php echo __('property_features_amenities', 'Features / Amenities'); ?></label>
                                <textarea class="form-control" name="feature" rows="3" placeholder="e.g. Swimming Pool, Gym, 24/7 Security, Car Parking" required><?= $is_edit ? h($property['feature']) : '' ?></textarea>
                            </div>

                            <div class="col-md-12 mt-4">
                                <button type="submit" name="add" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
                                     <i class="fas <?= $is_edit ? 'fa-save' : 'fa-plus-circle' ?> me-2"></i> <?= $is_edit ? __('property_update', 'Update Property') : __('property_submit', 'Submit Property') ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="property-guidelines bg-light p-4 rounded border">
                    <h3 class="h4 mb-4 text-primary"><i class="fas fa-info-circle me-2"></i><?php echo __('submission_guidelines', 'Submission Guidelines'); ?></h3>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span><?php echo __('guideline_accurate_info', 'Provide accurate and detailed information about your property.'); ?></span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span><?php echo __('guideline_quality_images', 'Upload clear, high-quality images (JPG/PNG).'); ?></span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span><?php echo __('guideline_unique_features', 'Mention all unique features and amenities.'); ?></span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span><?php echo __('guideline_correct_contact', 'Provide correct contact information.'); ?></span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                            <span><?php echo __('guideline_reviewed_before', 'Your listing will be reviewed before being published.'); ?></span>
                        </li>
                    </ul>

                    <div class="mt-5 p-3 bg-white rounded border">
                        <h4 class="h5 mb-3"><?php echo __('need_help', 'Need Help?'); ?></h4>
                        <p class="small text-muted mb-3"><?php echo __('need_help_desc', 'If you need assistance with submitting your property, please contact our support team:'); ?></p>
                        <p class="mb-2 fw-bold"><i class="fas fa-phone-alt text-primary me-2"></i> <?= htmlspecialchars($phoneDisplay) ?></p>
                        <p class="mb-0 fw-bold"><i class="fas fa-envelope text-primary me-2"></i> <?= htmlspecialchars($sc('contact_email', 'info@apsdreamhome.com')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
