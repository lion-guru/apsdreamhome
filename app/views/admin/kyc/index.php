<?php
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$currentFilter = $currentFilter ?? '';
$nsdlConfigured = $nsdlConfigured ?? false;
$nsdlTestMode = $nsdlTestMode ?? true;
$uidaiConfigured = $uidaiConfigured ?? false;
$uidaiTestMode = $uidaiTestMode ?? true;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-id-card me-2"></i>KYC Requests</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/kyc?status=pending" class="btn btn-outline-warning"><i class="fas fa-clock me-1"></i>Pending (<?= $stats['pending'] ?>)</a>
        </div>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 <?= $nsdlConfigured ? ($nsdlTestMode ? 'border-warning' : 'border-success') : 'border-secondary' ?>">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt <?= $nsdlConfigured ? 'text-success' : 'text-secondary' ?>"></i>
                        <span class="fw-semibold">NSDL PAN Verification</span>
                    </div>
                    <?php if (!$nsdlConfigured): ?>
                        <span class="badge bg-secondary">Not Configured</span>
                    <?php elseif ($nsdlTestMode): ?>
                        <span class="badge bg-warning text-dark">TEST MODE</span>
                    <?php else: ?>
                        <span class="badge bg-success">LIVE</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 <?= $uidaiConfigured ? ($uidaiTestMode ? 'border-warning' : 'border-success') : 'border-secondary' ?>">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt <?= $uidaiConfigured ? 'text-success' : 'text-secondary' ?>"></i>
                        <span class="fw-semibold">UIDAI Aadhaar Verification</span>
                    </div>
                    <?php if (!$uidaiConfigured): ?>
                        <span class="badge bg-secondary">Not Configured</span>
                    <?php elseif ($uidaiTestMode): ?>
                        <span class="badge bg-warning text-dark">TEST MODE</span>
                    <?php else: ?>
                        <span class="badge bg-success">LIVE</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/kyc" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $currentFilter === '' ? 'primary' : 'light' ?>">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-primary"><?= $stats['total'] ?></div>
                        <small class="text-muted">Total Requests</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/kyc?status=pending" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $currentFilter === 'pending' ? 'warning' : 'light' ?>">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-warning"><?= $stats['pending'] ?></div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/kyc?status=approved" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $currentFilter === 'approved' ? 'success' : 'light' ?>">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-success"><?= $stats['approved'] ?></div>
                        <small class="text-muted">Approved</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/kyc?status=rejected" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $currentFilter === 'rejected' ? 'danger' : 'light' ?>">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-danger"><?= $stats['rejected'] ?></div>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Legal Name</th>
                            <th>PAN</th>
                            <th>Aadhaar</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests ?? [])): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-id-card fa-3x text-muted mb-3 d-block"></i>
                                <h5>No KYC Requests</h5>
                                <p class="mb-0">No KYC requests found<?= $currentFilter ? " with status '$currentFilter'" : '' ?>.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td><?= $r['id'] ?? '' ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($r['user_name'] ?? '') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($r['user_email'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($r['legal_name'] ?? '—') ?></td>
                                    <td><code><?= htmlspecialchars($r['pan_number'] ?? '—') ?></code></td>
                                    <td><code><?= htmlspecialchars($r['aadhaar_number'] ?? '—') ?></code></td>
                                    <td>
                                        <span class="badge bg-<?= match($r['status'] ?? 'pending') { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning', default => 'secondary' } ?>">
                                            <?= ucfirst($r['status'] ?? 'Pending') ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/kyc/<?= (int)($r['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
