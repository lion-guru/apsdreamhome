<?php
$page_title = $page_title ?? 'HR Settings';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-cog me-2"></i>HR Settings</h4>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-tags me-2 text-primary"></i>Leave Types</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Code</th><th>Days/Year</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($leave_types ?? [])): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No leave types configured</td></tr>
                        <?php else: ?>
                            <?php foreach ($leave_types as $lt): ?>
                                <tr>
                                    <td><span class="badge style-37131">&nbsp;&nbsp;</span> <?= htmlspecialchars($lt['name'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars($lt['code'] ?? '') ?></code></td>
                                    <td><?= (int)($lt['days_per_year'] ?? 0) ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($lt['status'] ?? '') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
            <div class="card-footer bg-white">
                <a href="<?= BASE_URL ?>/admin/hr/leave-types" class="btn btn-sm btn-primary">Manage Leave Types</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clock me-2 text-info"></i>Shift Types</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Start</th><th>End</th><th>Duration</th><th>Active</th></tr></thead>
                    <tbody>
                        <?php if (empty($shift_types ?? [])): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No shift types configured</td></tr>
                        <?php else: ?>
                            <?php foreach ($shift_types as $st): ?>
                                <tr>
                                    <td><?= htmlspecialchars($st['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($st['start_time'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($st['end_time'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($st['duration_hours'] ?? '') ?>h</td>
                                    <td><span class="badge bg-<?= ($st['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($st['is_active'] ?? 0) ? 'Yes' : 'No' ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
            <div class="card-footer bg-white">
                <a href="<?= BASE_URL ?>/admin/hr/shifts" class="btn btn-sm btn-primary">Manage Shifts</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-secondary"></i>Quick Links</h6>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row g-2">
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/salary-structure" class="btn btn-outline-primary w-100"><i class="fas fa-money-bill-wave me-2"></i>Salary Structures</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/bonuses" class="btn btn-outline-success w-100"><i class="fas fa-gift me-2"></i>Bonuses</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/performance" class="btn btn-outline-warning w-100"><i class="fas fa-chart-line me-2"></i>Performance</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/kpis" class="btn btn-outline-info w-100"><i class="fas fa-bullseye me-2"></i>KPIs</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/attendance/report?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-file-alt me-2"></i>Attendance Report</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/documents" class="btn btn-outline-info w-100"><i class="fas fa-file me-2"></i>Documents</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/activities" class="btn btn-outline-dark w-100"><i class="fas fa-history me-2"></i>Activity Log</a></div>
                    <div class="col-md-3"><a href="<?= BASE_URL ?>/admin/hr/report" class="btn btn-outline-danger w-100"><i class="fas fa-chart-pie me-2"></i>Employee Report</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
