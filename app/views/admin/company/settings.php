<?php

$page_title = 'Company Settings';
$company = $company ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-building me-2"></i>Company Settings</h1>
            <p class="text-muted">Manage your organization details</p>
        </div>
    </div>



    <div class="card aps-cp-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Organization Details</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/company/settings/update">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control"
                               value="<?= htmlspecialchars($company['company_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($company['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control"
                               value="<?= htmlspecialchars($company['address'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
