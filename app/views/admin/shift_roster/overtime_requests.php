<?php
/**
 * Overtime Requests View
*/

$page_title = $page_title ?? 'Overtime Requests';
$requests = $requests ?? [];
$status = $status ?? 'pending';
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-clock me-2"></i>Overtime Requests</h1>
    </div>

    <!-- Status Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="<?= BASE_URL ?>/admin/shift-roster/overtime-requests?status=pending" class="btn btn-<?= $status === 'pending' ? 'primary' : 'outline-primary' ?>">
                    <i class="fas fa-clock me-1"></i> Pending
                </a>
                <a href="<?= BASE_URL ?>/admin/shift-roster/overtime-requests?status=approved" class="btn btn-<?= $status === 'approved' ? 'primary' : 'outline-primary' ?>">
                    <i class="fas fa-check me-1"></i> Approved
                </a>
                <a href="<?= BASE_URL ?>/admin/shift-roster/overtime-requests?status=rejected" class="btn btn-<?= $status === 'rejected' ? 'primary' : 'outline-primary' ?>">
                    <i class="fas fa-times me-1"></i> Rejected
                </a>
                <a href="<?= BASE_URL ?>/admin/shift-roster/overtime-reports" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-chart-bar me-1"></i> Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($requests)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Overtime Requests</h5>
                <p class="text-muted">No <?= $status ?> overtime requests found</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th class="text-center">Hours</th>
                            <th>Reason</th>
                            <th class="text-center">Status</th>
                            <th>Submitted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>#<?= $r['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['employee_name'] ?? '') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($r['employee_code'] ?? '') ?> | <?= htmlspecialchars($r['designation'] ?? '') ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($r['overtime_date'])) ?></td>
                            <td class="text-center fw-bold"><?= $r['hours'] ?> hrs</td>
                            <td><small><?= htmlspecialchars(substr($r['reason'], 0, 50)) ?></small></td>
                            <td class="text-center">
                                <?php $st = $r['status']; ?>
                                <span class="badge bg-<?= 
                                    $st === 'approved' ? 'success' : 
                                    ($st === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                            <td class="text-center">
                                <?php if ($r['status'] === 'pending'): ?>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="<?= BASE_URL ?>/admin/shift-roster/overtime-requests/<?= $r['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this overtime request?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-outline-success" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/shift-roster/overtime-requests/<?= $r['id'] ?>" class="d-inline" onsubmit="return confirm('Reject this overtime request?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.btn-group-sm .btn { border-radius: 0.375rem !important; }
</style>