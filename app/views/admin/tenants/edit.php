<!-- Edit Tenant — Super Admin -->
<?php
$tenant = $tenant ?? [];
$plans = $plans ?? [];
$base = BASE_URL ?? '';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-edit mr-2"></i>Edit Tenant: <?= htmlspecialchars($tenant['name'] ?? '') ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/tenants">Tenants</a></li>
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?? '' ?>"><?= htmlspecialchars($tenant['name'] ?? '') ?></a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?>/update">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Info -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Company Name *</label>
                                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($tenant['name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Slug</label>
                                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($tenant['slug'] ?? '') ?>" <?= ($tenant['slug'] ?? '') === 'apsdreamhome' ? 'readonly' : '' ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Domain</label>
                                        <input type="text" name="domain" class="form-control" value="<?= htmlspecialchars($tenant['domain'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <?php foreach (['active','trial','suspended','cancelled'] as $s): ?>
                                                <option value="<?= $s ?>" <?= ($tenant['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-user me-2"></i>Contact Information</h6></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Contact Name</label>
                                        <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($tenant['contact_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($tenant['contact_email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($tenant['contact_phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Address</label>
                                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($tenant['address'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">City</label>
                                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($tenant['city'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">State</label>
                                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($tenant['state'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Plan -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-tags me-2"></i>Subscription Plan</h6></div>
                            <div class="card-body">
                                <select name="plan_id" class="form-select mb-3" id="planSelect">
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan['id'] ?>" data-max-users="<?= $plan['max_users'] ?>" data-max-leads="<?= $plan['max_leads'] ?>" data-max-props="<?= $plan['max_properties'] ?>" data-storage="<?= $plan['storage_limit_mb'] ?>" <?= (int)($tenant['plan_id'] ?? 1) === (int)$plan['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($plan['name']) ?> — ₹<?= number_format($plan['price_monthly']) ?>/mo
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Limits -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Resource Limits</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Max Users</label>
                                    <input type="number" name="max_users" class="form-control form-control-sm" value="<?= $tenant['max_users'] ?? 1 ?>" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Max Leads</label>
                                    <input type="number" name="max_leads" class="form-control form-control-sm" value="<?= $tenant['max_leads'] ?? 50 ?>" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Max Properties</label>
                                    <input type="number" name="max_properties" class="form-control form-control-sm" value="<?= $tenant['max_properties'] ?? 10 ?>" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Storage (MB)</label>
                                    <input type="number" name="storage_limit_mb" class="form-control form-control-sm" value="<?= $tenant['storage_limit_mb'] ?? 100 ?>" min="10">
                                </div>
                            </div>
                        </div>

                        <!-- Branding -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-palette me-2"></i>Branding</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Primary Color</label>
                                    <input type="color" name="primary_color" class="form-control form-control-sm form-control-color" value="<?= htmlspecialchars($tenant['primary_color'] ?? '#667eea') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Secondary Color</label>
                                    <input type="color" name="secondary_color" class="form-control form-control-sm form-control-color" value="<?= htmlspecialchars($tenant['secondary_color'] ?? '#764ba2') ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save me-2"></i>Save Changes</button>
                        <a href="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?>" class="btn btn-outline-secondary w-100">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
