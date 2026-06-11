<?php
$page_title = $page_title ?? 'Add Property - APS Dream Home';
$page_description = $page_description ?? 'Add a new property listing';
$states = $states ?? [];
?>
<div class="container-fluid px-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Add New Property</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?php echo BASE_URL; ?>/associate/add-property" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Property Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Property Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="property_type" required>
                            <option value="">Select</option>
                            <option value="plot">Plot</option>
                            <option value="house">House</option>
                            <option value="flat">Flat</option>
                            <option value="shop">Shop</option>
                            <option value="farmhouse">Farmhouse</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Listing Type</label>
                        <select class="form-select" name="listing_type">
                            <option value="sell">For Sale</option>
                            <option value="rent">For Rent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Area (sq.ft.)</label>
                        <input type="number" class="form-control" name="area">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <select class="form-select" name="state_id" id="stateSelect">
                            <option value="">Select State</option>
                            <?php foreach ($states as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City/Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Property Images</label>
                        <input type="file" class="form-control" name="property_image" accept="image/*">
                        <small class="text-muted">JPG, PNG, WEBP (max 5MB)</small>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-check me-2"></i>Submit Property</button>
                    <a href="<?php echo BASE_URL; ?>/associate/properties" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
