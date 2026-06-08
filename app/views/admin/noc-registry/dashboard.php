<?php
$page_title = $page_title ?? 'NOC & Registry Dashboard';
$page_heading = $page_heading ?? 'NOC & Registry Management';
$stats = $stats ?? [];
$pendingNocs = $pendingNocs ?? [];
$pendingRegistries = $pendingRegistries ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-contract me-2"></i>NOC & Registry</h2>
            <p class="text-muted mb-0">Block registry if EMI/penalty outstanding — NOC gate before registration</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Request NOC</a>
            <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create" class="btn btn-outline-success"><i class="fas fa-landmark me-2"></i>Request Registry</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1">Pending NOCs</p>
                    <h3 class="text-primary"><?= number_format($stats['pending_nocs'] ?? 0) ?></h3>
                    <small class="text-muted"><?= number_format($stats['total_nocs'] ?? 0) ?> total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1">Approved NOCs</p>
                    <h3 class="text-success"><?= number_format($stats['approved_nocs'] ?? 0) ?></h3>
                    <small class="text-muted">Ready for registry</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1">Blocked NOCs</p>
                    <h3 class="text-warning"><?= number_format($stats['blocked_nocs'] ?? 0) ?></h3>
                    <small class="text-muted">EMI/penalty outstanding</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1">Pending Registries</p>
                    <h3 class="text-info"><?= number_format($stats['pending_registries'] ?? 0) ?></h3>
                    <small class="text-muted"><?= number_format($stats['completed_registries'] ?? 0) ?> completed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending NOC Requests</h5>
                    <a href="<?= BASE_URL ?>/admin/noc-registry/nocs?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>#</th><th>Booking</th><th>Customer</th><th>Purpose</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if (empty($pendingNocs)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No pending NOCs</td></tr>
                                <?php else: foreach ($pendingNocs as $n): ?>
                                    <tr>
                                        <td>#<?= $n['id'] ?></td>
                                        <td><?= htmlspecialchars($n['booking_number'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($n['customer_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($n['purpose'] ?? '') ?></td>
                                        <td><a href="<?= BASE_URL ?>/admin/noc-registry/nocs/<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary">Review</a></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-landmark me-2"></i>Pending Registries</h5>
                    <a href="<?= BASE_URL ?>/admin/noc-registry/registries?status=pending" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>#</th><th>Booking</th><th>Customer</th><th>Office</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if (empty($pendingRegistries)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No pending registries</td></tr>
                                <?php else: foreach ($pendingRegistries as $r): ?>
                                    <tr>
                                        <td>#<?= $r['id'] ?></td>
                                        <td><?= htmlspecialchars($r['booking_number'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['customer_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['sub_registrar_office'] ?? '') ?></td>
                                        <td><a href="<?= BASE_URL ?>/admin/noc-registry/registries/<?= $r['id'] ?>" class="btn btn-sm btn-outline-success">Review</a></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5><i class="fas fa-info-circle me-2"></i>How the NOC/Registry Pipe Works</h5>
            <ol class="mb-0 small">
                <li class="mb-1"><strong>Request NOC</strong> — submit for a booking (auto-checks eligibility)</li>
                <li class="mb-1"><strong>Eligibility checks</strong> — EMI status, penalties, RERA compliance, documents, commissions, NOC status</li>
                <li class="mb-1"><strong>If blocked</strong> — view blockers, fix issues, re-process</li>
                <li class="mb-1"><strong>If approved</strong> — can proceed to request registry</li>
                <li class="mb-0"><strong>Registry</strong> — track appointment, documents, completion at sub-registrar office</li>
            </ol>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
