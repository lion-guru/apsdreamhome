<?php
$credential = $credential ?? null;
if (!$credential) {
    echo '<div class="container-fluid py-4"><div class="alert alert-danger">Credential not found.</div></div>';
    return;
}

$typeLabels = [
    'gst' => 'GST (GSTIN)', 'pan' => 'PAN Card', 'tan' => 'TAN Number', 'cin' => 'CIN Number',
    'msme' => 'MSME Registration', 'rera' => 'RERA Registration', 'bank_account' => 'Bank Account', 'digital_signature' => 'Digital Signature',
];
$typeIcons = [
    'gst' => 'fa-receipt', 'pan' => 'fa-id-card', 'tan' => 'fa-file-invoice', 'cin' => 'fa-building',
    'msme' => 'fa-industry', 'rera' => 'fa-stamp', 'bank_account' => 'fa-university', 'digital_signature' => 'fa-signature',
];
$statusColors = [
    'active' => 'success', 'expired' => 'danger', 'suspended' => 'warning', 'pending_renewal' => 'info',
];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= htmlspecialchars($page_heading ?? 'Credential Detail') ?></h1>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/company-credentials/<?= $credential['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="<?= BASE_URL ?>/admin/company-credentials" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">
                        <i class="fas <?= $typeIcons[$credential['credential_type']] ?? 'fa-certificate' ?> me-2"></i>
                        <?= htmlspecialchars($typeLabels[$credential['credential_type']] ?? $credential['credential_type']) ?>
                        <?php if ($credential['is_primary']): ?>
                            <span class="badge bg-warning text-dark ms-2"><i class="fas fa-star me-1"></i>Primary</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Label</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($credential['credential_label'] ?? '') ?></dd>

                        <dt class="col-sm-4">Value</dt>
                        <dd class="col-sm-8"><code class="fs-6"><?= htmlspecialchars($credential['credential_value'] ?? '') ?></code></dd>

                        <dt class="col-sm-4">Issuer</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($credential['issuer'] ?? '—') ?></dd>

                        <dt class="col-sm-4">Issue Date</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($credential['issue_date'] ?? '—') ?></dd>

                        <dt class="col-sm-4">Expiry Date</dt>
                        <dd class="col-sm-8">
                            <?php if ($credential['expiry_date']): ?>
                                <?php
                                $exp = strtotime($credential['expiry_date']);
                                $isPast = $exp < time();
                                $isSoon = !$isPast && $exp < strtotime('+30 days');
                                ?>
                                <span class="<?= $isPast ? 'text-danger fw-bold' : ($isSoon ? 'text-warning fw-bold' : '') ?>">
                                    <?= htmlspecialchars($credential['expiry_date'] ?? '') ?>
                                    <?php if ($isPast): ?>
                                        <small>(Expired)</small>
                                    <?php elseif ($isSoon): ?>
                                        <small>(Expiring soon)</small>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No expiry</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= $statusColors[$credential['status']] ?? 'secondary' ?> fs-6">
                                <?= ucfirst(str_replace('_', ' ', $credential['status'])) ?>
                            </span>
                        </dd>

                        <?php if ($credential['document_path']): ?>
                        <dt class="col-sm-4">Document</dt>
                        <dd class="col-sm-8">
                            <a href="<?= BASE_URL . '/' . htmlspecialchars($credential['document_path'] ?? '') ?>" target="_blank" class="text-decoration-none">
                                <i class="fas fa-file-pdf me-1"></i>View Document
                            </a>
                        </dd>
                        <?php endif; ?>

                        <?php if ($credential['notes']): ?>
                        <dt class="col-sm-4">Notes</dt>
                        <dd class="col-sm-8"><?= nl2br(htmlspecialchars($credential['notes'] ?? '')) ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($credential['created_at'] ?? '—') ?></dd>

                        <dt class="col-sm-4">Last Updated</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($credential['updated_at'] ?? '—') ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>/admin/company-credentials/<?= $credential['id'] ?>/edit" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit me-2"></i>Edit Credential
                    </a>
                    <a href="<?= BASE_URL ?>/admin/company-credentials/create" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus me-2"></i>Add New Credential
                    </a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/company-credentials/<?= $credential['id'] ?>/delete" onsubmit="return confirm('Delete this credential permanently?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <button type="submit" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-trash me-2"></i>Delete Credential
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php if ($credential['credential_type'] === 'gst'): ?>
                        <div class="bg-light rounded p-3 mb-2">
                            <i class="fas fa-qrcode fa-3x text-muted"></i>
                        </div>
                        <small class="text-muted">Scan QR for GST verification</small>
                    <?php else: ?>
                        <div class="bg-light rounded p-3 mb-2">
                            <i class="fas <?= $typeIcons[$credential['credential_type']] ?? 'fa-certificate' ?> fa-3x text-muted"></i>
                        </div>
                        <small class="text-muted"><?= htmlspecialchars($typeLabels[$credential['credential_type']] ?? '') ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
