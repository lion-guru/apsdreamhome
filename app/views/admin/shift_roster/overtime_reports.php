<?php
/**
 * Overtime Reports View
*/

$page_title = $page_title ?? 'Overtime Reports';
$summary = $summary ?? ['total_requests' => 0, 'total_hours' => 0, 'by_status' => [], 'by_department' => [], 'details' => []];
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-t');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2"></i>Overtime Reports</h1>
        <a href="<?= BASE_URL ?>/admin/shift-roster/overtime-requests" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Requests
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $summary['total_requests'] ?? 0 ?></h3>
                    <small>Total Requests</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $summary['total_hours'] ?? 0 ?> hrs</h3>
                    <small>Total Overtime Hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <?= $summary['by_status']['pending'] ?? 0 ?>
                    <small>Pending Approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>₹<?= number_format($summary['total_hours'] * 200, 0) ?></h3>
                    <small>Est. Cost (@₹200/hr)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Requests by Status</h5>
                </div>
                <div class="card-body">
                    <?php $statuses = $summary['by_status'] ?? []; ?>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <h4 class="text-warning mb-0"><?= $statuses['pending'] ?? 0 ?></h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h4 class="text-success mb-0"><?= $statuses['approved'] ?? 0 ?></h4>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h4 class="text-danger mb-0"><?= $statuses['rejected'] ?? 0 ?></h4>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Overtime Hours by Department</h5>
                </div>
                <div class="card-body">
                    <?php $depts = $summary['by_department'] ?? []; ?>
                    <?php if (empty($depts)): ?>
                    <p class="text-center text-muted py-4">No department data</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Department</th><th class="text-end">Hours</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($depts as $dept => $hours): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dept) ?></td>
                                    <td class="text-end fw-bold"><?= $hours ?> hrs</td>
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

    <!-- Detailed Requests -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detailed Requests</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($summary['details'])): ?>
            <div class="text-center py-4 text-muted">No detailed data available</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center">Hours</th>
                            <th>Reason</th>
                            <th class="text-center">Status</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summary['details'] as $r): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($r['overtime_date'])) ?></td>
                            <td><?= htmlspecialchars($r['employee_name']) ?></td>
                            <td><?= htmlspecialchars($r['department'] ?? '-') ?></td>
                            <td class="text-center"><?= $r['hours'] ?> hrs</td>
                            <td><small><?= htmlspecialchars(substr($r['reason'], 0, 40)) ?></small></td>
                            <td class="text-center">
                                <span class="badge bg-<?= 
                                    $r['status'] === 'approved' ? 'success' : 
                                    ($r['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                            <td><?= $r['approver_name'] ?? '-' ?></td>
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
</style>