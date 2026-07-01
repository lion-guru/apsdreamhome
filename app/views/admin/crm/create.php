<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-user-plus me-2"></i>Add New Lead</h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/leads/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Source</label>
                        <select name="source" class="form-select">
                            <option value="website">Website</option>
                            <option value="referral">Referral</option>
                            <option value="walk_in">Walk-in</option>
                            <option value="phone_call">Phone Call</option>
                            <option value="social_media">Social Media</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Budget Range</label>
                        <input type="text" name="budget" class="form-control" placeholder="e.g. 25-50 Lakh">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Interested Colony</label>
                        <select name="colony_id" class="form-select">
                            <option value="">Select Colony</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Lead</button>
                        <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
