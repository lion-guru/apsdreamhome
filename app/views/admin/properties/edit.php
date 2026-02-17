<?php
// RBAC Protection - Only Super Admin and Manager can edit properties
$currentRole = $_SESSION['admin_role'] ?? '';
if ($currentRole !== 'superadmin' && $currentRole !== 'manager') {
    // If not authorized, redirect
    header("Location: /admin/dashboard?error=unauthorized");
    exit;
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Edit Property')); ?></h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/properties"><?php echo h($mlSupport->translate('Properties')); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo h($mlSupport->translate('Edit Property')); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="/admin/properties/update/<?php echo h($property['id']); ?>" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Property Title')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="<?php echo h($property['title']); ?>" required>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a property title.')); ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Property Category')); ?></label>
                                <select name="type" class="form-select">
                                    <option value="apartment" <?php echo h($property['type']) == 'apartment' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Apartment')); ?></option>
                                    <option value="house" <?php echo h($property['type']) == 'house' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('House')); ?></option>
                                    <option value="land" <?php echo h($property['type']) == 'land' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Land/Plot')); ?></option>
                                    <option value="commercial" <?php echo h($property['type']) == 'commercial' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Commercial')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Property Type (Sub-type)')); ?></label>
                                <select name="property_type_id" class="form-select">
                                    <?php foreach ($propertyTypes as $type): ?>
                                        <option value="<?php echo h($type['id']); ?>" <?php echo h($property['property_type_id']) == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo h($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Status')); ?></label>
                                <select name="status" class="form-select">
                                    <option value="available" <?php echo h($property['status']) == 'available' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Available')); ?></option>
                                    <option value="booked" <?php echo h($property['status']) == 'booked' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Booked')); ?></option>
                                    <option value="sold" <?php echo h($property['status']) == 'sold' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sold')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Price (₹)')); ?> <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" step="0.01" value="<?php echo h($property['price']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Area')); ?></label>
                                <input type="number" name="area" class="form-control" step="0.01" value="<?php echo h($property['area']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Area Unit')); ?></label>
                                <select name="area_unit" class="form-select">
                                    <option value="sqft" <?php echo h($property['area_unit']) == 'sqft' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sq. Ft.')); ?></option>
                                    <option value="sqyd" <?php echo h($property['area_unit']) == 'sqyd' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sq. Yd.')); ?></option>
                                    <option value="acre" <?php echo h($property['area_unit']) == 'acre' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Acre')); ?></option>
                                    <option value="bigha" <?php echo h($property['area_unit']) == 'bigha' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Bigha')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Bedrooms')); ?></label>
                                <input type="number" name="bedrooms" class="form-control" value="<?php echo h($property['bedrooms']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Bathrooms')); ?></label>
                                <input type="number" name="bathrooms" class="form-control" value="<?php echo h($property['bathrooms']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Pincode')); ?></label>
                                <input type="text" name="pincode" class="form-control" value="<?php echo h($property['pincode']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Location')); ?></label>
                                <input type="text" name="location" class="form-control" value="<?php echo h($property['location']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('City')); ?></label>
                                <input type="text" name="city" class="form-control" value="<?php echo h($property['city']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('State')); ?></label>
                                <input type="text" name="state" class="form-control" value="<?php echo h($property['state']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Description')); ?></label>
                            <textarea name="description" class="form-control" rows="4"><?php echo h($property['description']); ?></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured" id="isFeatured" <?php echo $property['featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="isFeatured"><?php echo h($mlSupport->translate('Mark as Featured')); ?></label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5"><?php echo h($mlSupport->translate('Update Property')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>