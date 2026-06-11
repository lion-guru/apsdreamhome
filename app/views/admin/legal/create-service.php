<?php
$page_title = $page_title ?? 'Add Legal Service';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Add Legal Service</h1>
                    <p class="text-muted mb-0">Create a new legal service offering</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/legal/services" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-gavel me-2"></i>Service Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/legal/services/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required placeholder="e.g. Property Registration">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Icon (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control" value="fa-gavel" placeholder="fa-gavel">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Describe this legal service"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price Range</label>
                                <input type="text" name="price_range" class="form-control" placeholder="e.g. ₹5,000 - ₹25,000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g. 3-5 Business Days">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Features (one per line)</label>
                                <textarea name="features" class="form-control" rows="4" placeholder="Feature 1&#10;Feature 2&#10;Feature 3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Service</button>
                                <a href="<?php echo BASE_URL; ?>/admin/legal/services" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Icon Reference</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="small text-muted mb-2">Common Font Awesome icons:</p>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><i class="fas fa-gavel me-2 text-primary"></i>fa-gavel - General Legal</li>
                        <li class="mb-1"><i class="fas fa-file-contract me-2 text-primary"></i>fa-file-contract - Contracts</li>
                        <li class="mb-1"><i class="fas fa-home me-2 text-primary"></i>fa-home - Property</li>
                        <li class="mb-1"><i class="fas fa-handshake me-2 text-primary"></i>fa-handshake - Agreement</li>
                        <li class="mb-1"><i class="fas fa-balance-scale me-2 text-primary"></i>fa-balance-scale - Disputes</li>
                        <li class="mb-1"><i class="fas fa-file-signature me-2 text-primary"></i>fa-file-signature - Registration</li>
                        <li class="mb-1"><i class="fas fa-landmark me-2 text-primary"></i>fa-landmark - Court/Registry</li>
                        <li><i class="fas fa-stamp me-2 text-primary"></i>fa-stamp - Notary/Stamp</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
