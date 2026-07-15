<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-plus-circle me-2 text-warning"></i>Create Campaign</h4>
    <form method="POST" action="<?= BASE_URL ?>/admin/marketing-automation/campaigns/create" class="card shadow-sm p-4">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="social">Social Media</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-warning mt-3"><i class="fas fa-rocket me-1"></i>Create Campaign</button>
    </form>
</div>