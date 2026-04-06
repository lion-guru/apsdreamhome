<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-home"></i> Create</h2>
                <div>
                    <a href="/admin/resell-properties" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Resell Properties
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Create Management - Complete Resell Property System
                    </div>
                    <form method="POST" action="/admin/resell-properties/create" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="property_title" class="form-label">Property Title *</label>
                                    <input type="text" class="form-control" id="property_title" name="property_title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="property_type" class="form-label">Property Type *</label>
                                    <select class="form-select" id="property_type" name="property_type" required>
                                        <option value="">Select Type</option>
                                        <option value="residential">Residential</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="industrial">Industrial</option>
                                        <option value="land">Land</option>
                                        <option value="mixed">Mixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="seller_name" class="form-label">Seller Name *</label>
                                    <input type="text" class="form-control" id="seller_name" name="seller_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="seller_email" class="form-label">Seller Email *</label>
                                    <input type="email" class="form-control" id="seller_email" name="seller_email" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="seller_phone" class="form-label">Seller Phone *</label>
                                    <input type="tel" class="form-control" id="seller_phone" name="seller_phone" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="area_sqft" class="form-label">Area (Sq Ft) *</label>
                                    <input type="number" class="form-control" id="area_sqft" name="area_sqft" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="original_price" class="form-label">Original Price (₹) *</label>
                                    <input type="number" class="form-control" id="original_price" name="original_price" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="expected_price" class="form-label">Expected Price (₹) *</label>
                                    <input type="number" class="form-control" id="expected_price" name="expected_price" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="colony_name" class="form-label">Colony Name *</label>
                                    <input type="text" class="form-control" id="colony_name" name="colony_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="district_name" class="form-label">District Name *</label>
                                    <input type="text" class="form-control" id="district_name" name="district_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state_name" class="form-label">State Name *</label>
                                    <input type="text" class="form-control" id="state_name" name="state_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="commission_type" class="form-label">Commission Type *</label>
                                    <select class="form-select" id="commission_type" name="commission_type" required>
                                        <option value="">Select Type</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="tiered">Tiered</option>
                                        <option value="hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="commission_rate" class="form-label">Commission Rate/Amount *</label>
                                    <input type="number" class="form-control" id="commission_rate" name="commission_rate" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                <label class="form-check-label" for="featured">
                                    Featured Property
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="images" class="form-label">Property Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/admin/resell-properties" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../../../layouts/admin_footer.php"; ?>