<!-- Page Header -->
<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/leads" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>Back to Leads
    </a>
    <h1 class="h3 mt-2 mb-1">Add New Lead</h1>
    <p class="text-muted">Create a new lead entry</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/leads">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="property_id" class="form-label fw-semibold">Interested Property</label>
                            <select class="form-select" id="property_id" name="property_id">
                                <option value="">Select Property</option>
                                <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $property): ?>
                                <option value="<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['title']); ?></option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="interested">Interested</option>
                                <option value="converted">Converted</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="source" class="form-label fw-semibold">Source</label>
                            <select class="form-select" id="source" name="source">
                                <option value="website">Website</option>
                                <option value="phone">Phone</option>
                                <option value="walk-in">Walk-in</option>
                                <option value="referral">Referral</option>
                                <option value="social-media">Social Media</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about this lead..."></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Lead</button>
                        <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
