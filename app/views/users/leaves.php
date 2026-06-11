<?php
$leaves = $leaves ?? [];
$leaveBalance = $leaveBalance ?? [
    'cl' => ['used' => 0, 'total' => 12],
    'sl' => ['used' => 0, 'total' => 12],
    'pl' => ['used' => 0, 'total' => 24],
];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Leave Management</h1>
        <a href="/leaves/apply" class="btn btn-primary"><i class="fas fa-plus"></i> Apply Leave</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-white bg-info">
                <div class="card-body text-center">
                    <h5 class="card-title">Casual Leave (CL)</h5>
                    <h2 class="mb-0"><?= (int)($leaveBalance['cl']['used'] ?? 0) ?> / <?= (int)($leaveBalance['cl']['total'] ?? 12) ?></h2>
                    <small><?= max(0, (int)($leaveBalance['cl']['total'] ?? 12) - (int)($leaveBalance['cl']['used'] ?? 0)) ?> remaining</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-white bg-warning">
                <div class="card-body text-center">
                    <h5 class="card-title">Sick Leave (SL)</h5>
                    <h2 class="mb-0"><?= (int)($leaveBalance['sl']['used'] ?? 0) ?> / <?= (int)($leaveBalance['sl']['total'] ?? 12) ?></h2>
                    <small><?= max(0, (int)($leaveBalance['sl']['total'] ?? 12) - (int)($leaveBalance['sl']['used'] ?? 0)) ?> remaining</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-white bg-success">
                <div class="card-body text-center">
                    <h5 class="card-title">Privilege Leave (PL)</h5>
                    <h2 class="mb-0"><?= (int)($leaveBalance['pl']['used'] ?? 0) ?> / <?= (int)($leaveBalance['pl']['total'] ?? 24) ?></h2>
                    <small><?= max(0, (int)($leaveBalance['pl']['total'] ?? 24) - (int)($leaveBalance['pl']['used'] ?? 0)) ?> remaining</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0">Leave History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No leave applications found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leaves as $l): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($l['type'] ?? '') ?></span></td>
                                    <td><?= htmlspecialchars($l['from_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['to_date'] ?? '') ?></td>
                                    <td><?= (int)($l['days'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($l['reason'] ?? '') ?></td>
                                    <td>
                                        <?php $st = $l['status'] ?? ''; ?>
                                        <?php if ($st === 'Approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($st === 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($st === 'Rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($st) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/leaves/<?= $l['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <?php if (($l['status'] ?? '') === 'Pending'): ?>
                                            <a href="/leaves/<?= $l['id'] ?? 0 ?>/cancel" class="btn btn-sm btn-outline-danger" title="Cancel"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
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
