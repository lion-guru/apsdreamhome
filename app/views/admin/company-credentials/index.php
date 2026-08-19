<?php
$stats = $stats ?? ['total' => 0, 'active' => 0, 'expiring' => 0, 'expired' => 0, 'by_type' => []];
$grouped = $grouped ?? [];
$expiring = $expiring ?? [];
$filter_expiring = $filter_expiring ?? false;

$typeLabels = [
    'gst' => 'GST', 'pan' => 'PAN', 'tan' => 'TAN', 'cin' => 'CIN',
    'msme' => 'MSME', 'rera' => 'RERA', 'bank_account' => 'Bank Account', 'digital_signature' => 'Digital Signature',
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
        <h1 class="h3"><?= htmlspecialchars($page_heading ?? 'Company Credentials') ?></h1>
        <a href="<?= BASE_URL ?>/admin/company-credentials/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Credential
        </a>
    </div>

    <?php if (!empty($expiring) && !$filter_expiring): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong><?= count($expiring) ?> credential(s)</strong>&nbsp;expiring within 30 days.
        <a href="<?= BASE_URL ?>/admin/company-credentials/expiring" class="alert-link ms-2">View All</a>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary"><?= (int)$stats['total'] ?></div>
                    <div class="text-muted">Total Credentials</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success"><?= (int)$stats['active'] ?></div>
                    <div class="text-muted">Active</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning"><?= (int)$stats['expiring'] ?></div>
                    <div class="text-muted">Expiring Soon</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-danger"><?= (int)$stats['expired'] ?></div>
                    <div class="text-muted">Expired</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($grouped) && empty($filter_expiring)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-3">No credentials found.</p>
            <a href="<?= BASE_URL ?>/admin/company-credentials/create" class="btn btn-primary">Add First Credential</a>
        </div>
    </div>
    <?php else: ?>
        <?php if (!empty($credentials)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Label</th>
                                <th>Value</th>
                                <th>Issuer</th>
                                <th>Status</th>
                                <th>Expiry</th>
                                <th class="text-center">Primary</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($credentials as $cred): ?>
                            <tr>
                                <td>
                                    <i class="fas <?= $typeIcons[$cred['credential_type']] ?? 'fa-certificate' ?> me-1 text-muted"></i>
                                    <?= htmlspecialchars($typeLabels[$cred['credential_type']] ?? $cred['credential_type']) ?>
                                </td>
                                <td><?= htmlspecialchars($cred['credential_label'] ?? '') ?></td>
                                <td>
                                    <code><?= htmlspecialchars($cred['credential_value'] ?? '') ?></code>
                                </td>
                                <td><?= htmlspecialchars($cred['issuer'] ?? '—') ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusColors[$cred['status']] ?? 'secondary' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $cred['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cred['expiry_date']): ?>
                                        <?php
                                        $exp = strtotime($cred['expiry_date']);
                                        $isPast = $exp < time();
                                        $isSoon = !$isPast && $exp < strtotime('+30 days');
                                        ?>
                                        <span class="<?= $isPast ? 'text-danger' : ($isSoon ? 'text-warning' : '') ?>">
                                            <?= htmlspecialchars($cred['expiry_date'] ?? '') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($cred['is_primary']): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/company-credentials/<?= $cred['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/company-credentials/<?= $cred['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/company-credentials/<?= $cred['id'] ?>/delete" class="d-inline" data-aps-confirm="Delete this credential permanently?">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
