<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-user-plus me-2 text-success"></i>Capture Lead</h4>
    <form method="POST" action="<?= BASE_URL ?>/admin/marketing-automation/capture-lead" class="card shadow-sm p-4">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Source</label>
                <select name="source" class="form-select">
                    <option value="website">Website</option>
                    <option value="referral">Referral</option>
                    <option value="social">Social Media</option>
                    <option value="call">Phone Call</option>
                    <option value="walkin">Walk-in</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Message</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-success mt-3"><i class="fas fa-save me-1"></i>Save Lead</button>
    </form>
</div>