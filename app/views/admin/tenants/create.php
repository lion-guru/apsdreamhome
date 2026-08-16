<!-- Create Tenant — Super Admin -->
<?php
$plans = $plans ?? [];
$base = BASE_URL ?? '';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-plus-circle mr-2"></i>Create New Tenant</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/tenants">Tenants</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= $base ?>/admin/tenants/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="row">
                    <!-- Basic Info -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Company Name *</label>
                                        <input type="text" name="name" class="form-control" required placeholder="e.g. ABC Real Estate" id="tenantName">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Slug (auto-generated)</label>
                                        <input type="text" name="slug" class="form-control" placeholder="auto-from-name" id="tenantSlug">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Domain</label>
                                        <input type="text" name="domain" class="form-control" placeholder="e.g. abc-realestate.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="trial" selected>Trial (14 days)</option>
                                            <option value="active">Active</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-user me-2"></i>Contact Information</h6></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Contact Name</label>
                                        <input type="text" name="contact_name" class="form-control" placeholder="Full name">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="contact_email" class="form-control" placeholder="email@example.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="text" name="contact_phone" class="form-control" placeholder="+91-XXXXXXXXXX">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Address</label>
                                        <input type="text" name="address" class="form-control" placeholder="Full address">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">City</label>
                                        <input type="text" name="city" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">State</label>
                                        <input type="text" name="state" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan & Limits -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-tags me-2"></i>Subscription Plan</h6></div>
                            <div class="card-body">
                                <select name="plan_id" class="form-select mb-3" id="planSelect">
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan['id'] ?>" data-max-users="<?= $plan['max_users'] ?>" data-max-leads="<?= $plan['max_leads'] ?>" data-max-props="<?= $plan['max_properties'] ?>" data-storage="<?= $plan['storage_limit_mb'] ?>">
                                            <?= htmlspecialchars($plan['name'] ?? '') ?> — ₹<?= number_format($plan['price_monthly']) ?>/mo
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Resource Limits</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Max Users</label>
                                    <input type="number" name="max_users" class="form-control form-control-sm" value="1" min="1" id="maxUsers">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Max Leads</label>
                                    <input type="number" name="max_leads" class="form-control form-control-sm" value="50" min="1" id="maxLeads">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Max Properties</label>
                                    <input type="number" name="max_properties" class="form-control form-control-sm" value="10" min="1" id="maxProps">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Storage (MB)</label>
                                    <input type="number" name="storage_limit_mb" class="form-control form-control-sm" value="100" min="10" id="maxStorage">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-palette me-2"></i>Branding</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Primary Color</label>
                                    <input type="color" name="primary_color" class="form-control form-control-sm form-control-color" value="#667eea">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Secondary Color</label>
                                    <input type="color" name="secondary_color" class="form-control form-control-sm form-control-color" value="#764ba2">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Create Tenant</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('tenantName')?.addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('tenantSlug').placeholder = slug || 'auto-from-name';
});
document.getElementById('planSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('maxUsers').value = opt.dataset.maxUsers || 1;
    document.getElementById('maxLeads').value = opt.dataset.maxLeads || 50;
    document.getElementById('maxProps').value = opt.dataset.maxProps || 10;
    document.getElementById('maxStorage').value = opt.dataset.storage || 100;
});
</script>
