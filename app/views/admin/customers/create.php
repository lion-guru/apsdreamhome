<?php $pageTitle = 'Add Customer'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-user-plus me-2"></i>Add Customer</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">users</a></li>
                    <li class="breadcrumb-item active">Add Customer</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/users">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Phone <span class="text-danger">*</span></label><input type="text" name="phone" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Alternate Phone</label><input type="text" name="alt_phone" class="form-control"></div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <div class="input-group">
                            <textarea name="address" class="form-control" rows="2"></textarea>
                            <button type="button" class="btn btn-outline-secondary" data-action="map-picker" data-target="address" title="Pick on Map">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" data-autofill="city"></div>
                    <div class="col-md-3"><label class="form-label">State</label><input type="text" name="state" class="form-control" data-autofill="state"></div>
                    <div class="col-md-3">
                        <label class="form-label">Pincode</label>
                        <div class="input-group">
                            <input type="text" name="pincode" class="form-control" data-autofill="pincode" maxlength="6" placeholder="Enter pincode">
                            <button type="button" class="btn btn-outline-secondary" data-action="gps" title="Use My Location">
                                <i class="fas fa-location-crosshairs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Customer</button> <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
