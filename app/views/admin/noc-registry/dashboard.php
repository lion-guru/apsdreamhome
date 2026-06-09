<?php
$page_title = $page_title ?? 'NOC & Registry Pipeline';
ob_start();
$st = $stats ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-contract me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Property registration pipeline — NOC → Registry</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/noc-registry/eligibility" class="btn btn-outline-warning btn-sm"><i class="fas fa-check-double me-1"></i>Eligibility Check</a>
        <a href="<?= BASE_URL ?>/admin/noc-registry/nocs/create" class="btn btn-outline-danger btn-sm"><i class="fas fa-plus me-1"></i>New NOC</a>
        <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus me-1"></i>New Registry</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-clock text-warning"></i></div></div>
                    <div>
                        <div class="text-muted small">NOC Pending</div>
                        <div class="fw-bold fs-5"><?= $st['nocPending'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check text-success"></i></div></div>
                    <div>
                        <div class="text-muted small">NOC Approved</div>
                        <div class="fw-bold fs-5"><?= $st['nocApproved'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-landmark text-primary"></i></div></div>
                    <div>
                        <div class="text-muted small">Registry In Progress</div>
                        <div class="fw-bold fs-5"><?= $st['regPending'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-check-double text-info"></i></div></div>
                    <div>
                        <div class="text-muted small">Registry Completed</div>
                        <div class="fw-bold fs-5"><?= $st['regCompleted'] ?? 0 ?></div>
                        <small class="text-muted">₹<?= number_format($st['regTotalCost'] ?? 0, 0) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline Flow -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Registration Pipeline Flow</h6>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="text-center">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">1</div>
                <div class="small fw-bold mt-2">Booking Fully Paid</div>
            </div>
            <i class="fas fa-arrow-right text-muted"></i>
            <div class="text-center">
                <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">2</div>
                <div class="small fw-bold mt-2">NOC Requested</div>
            </div>
            <i class="fas fa-arrow-right text-muted"></i>
            <div class="text-center">
                <div class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">3</div>
                <div class="small fw-bold mt-2">NOC Approved</div>
            </div>
            <i class="fas fa-arrow-right text-muted"></i>
            <div class="text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">4</div>
                <div class="small fw-bold mt-2">Registry Created</div>
            </div>
            <i class="fas fa-arrow-right text-muted"></i>
            <div class="text-center">
                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">5</div>
                <div class="small fw-bold mt-2">SRO Appointment</div>
            </div>
            <i class="fas fa-arrow-right text-muted"></i>
            <div class="text-center">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">6</div>
                <div class="small fw-bold mt-2">Registration Done</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent NOCs -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between">
                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent NOC Requests</h6>
                <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_nocs)): ?>
                    <div class="p-4 text-center text-muted">No NOC requests yet</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Booking</th><th>Customer</th><th>Purpose</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($recent_nocs as $n): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($n['booking_number']) ?></td>
                                    <td class="small"><?= htmlspecialchars($n['customer_name']) ?></td>
                                    <td class="small text-truncate" style="max-width:150px;"><?= htmlspecialchars($n['purpose']) ?></td>
                                    <td><span class="badge bg-<?= $n['status'] === 'approved' ? 'success' : ($n['status'] === 'rejected' ? 'danger' : ($n['status'] === 'blocked' ? 'dark' : 'warning')) ?>"><?= ucfirst($n['status']) ?></span></td>
                                    <td><a href="<?= BASE_URL ?>/admin/noc-registry/nocs/<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Registries -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between">
                <h6 class="mb-0"><i class="fas fa-landmark me-2"></i>Recent Registries</h6>
                <a href="<?= BASE_URL ?>/admin/noc-registry/registries" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_registries)): ?>
                    <div class="p-4 text-center text-muted">No registries yet</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Booking</th><th>Customer</th><th>Plot</th><th>Cost</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($recent_registries as $r): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($r['booking_number']) ?></td>
                                    <td class="small"><?= htmlspecialchars($r['customer_name']) ?></td>
                                    <td class="small"><?= htmlspecialchars($r['plot_no']) ?>, <?= htmlspecialchars($r['colony_name']) ?></td>
                                    <td class="small">₹<?= number_format($r['total_registry_cost'], 0) ?></td>
                                    <td><span class="badge bg-<?= $r['status'] === 'completed' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : ($r['status'] === 'cancelled' ? 'dark' : 'primary')) ?>"><?= ucfirst(str_replace('_', ' ', $r['status'])) ?></span></td>
                                    <td><a href="<?= BASE_URL ?>/admin/noc-registry/registries/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
