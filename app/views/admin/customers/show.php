ï»¿<?php $pageTitle = 'Customer Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-user me-2"></i>Customer Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">users</a></li>
                    <li class="breadcrumb-item active"><?= $customer['name'] ?? 'Customer' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/users/<?= $customer['id'] ?? 0 ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($customer)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-user-slash fa-4x d-block mb-3"></i><h5>Customer not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body py-4">
                    <div class="avatar-lg mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center style-6167"><?= strtoupper(substr($customer['name'], 0, 1)) ?></div>
                    <h5 class="mb-1"><?= $customer['name'] ?></h5>
                    <p class="text-muted mb-2"><?= $customer['email'] ?? 'No email' ?></p>
                    <span class="badge bg-<?= ($customer['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($customer['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($customer['status'] ?? 'Active') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Phone</div><div class="col-sm-8"><strong><?= $customer['phone'] ?? '-' ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Alt. Phone</div><div class="col-sm-8"><?= $customer['alt_phone'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Email</div><div class="col-sm-8"><?= $customer['email'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Address</div><div class="col-sm-8"><?= $customer['address'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">City / State</div><div class="col-sm-8"><?= ($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '') ?></div></div>
                    <div class="row"><div class="col-sm-4 text-muted">Pincode</div><div class="col-sm-8"><?= $customer['pincode'] ?? '-' ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
