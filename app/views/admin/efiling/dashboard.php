<?php
$page_title = $page_title ?? 'E-Filing Dashboard';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-upload me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">FY <?= htmlspecialchars($fy) ?> | Quarter <?= htmlspecialchars($quarter) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/efiling/tds" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-invoice-dollar me-1"></i>TDS Filing</a>
        <a href="<?= BASE_URL ?>/admin/efiling/gst" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-alt me-1"></i>GST Filing</a>
        <a href="<?= BASE_URL ?>/admin/efiling/calendar" class="btn btn-outline-success btn-sm"><i class="fas fa-calendar me-1"></i>Calendar</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-danger bg-opacity-10 p-3"><i class="fas fa-file-invoice-dollar text-danger"></i></div></div>
                    <div>
                        <div class="text-muted small">TDS Pending</div>
                        <div class="fw-bold fs-5"><?= $stats['draft'] + $stats['prepared'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-file-alt text-primary"></i></div></div>
                    <div>
                        <div class="text-muted small">GST Pending</div>
                        <div class="fw-bold fs-5"><?= $gst_pending_count ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-clock text-warning"></i></div></div>
                    <div>
                        <div class="text-muted small">Due This Week</div>
                        <div class="fw-bold fs-5"><?= $deadline_stats['due_this_week'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check-double text-success"></i></div></div>
                    <div>
                        <div class="text-muted small">Filed This Month</div>
                        <div class="fw-bold fs-5"><?= $stats['this_month'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Overdue Deadlines -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Overdue Deadlines</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($overdue_deadlines)): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-check-circle text-success fa-2x mb-2"></i><br>No overdue deadlines!</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Type</th><th>Description</th><th>Due</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($overdue_deadlines as $d):
                                $daysOverdue = (int)((time() - strtotime($d['due_date'])) / 86400);
                            ?>
                                <tr>
                                    <td><span class="badge bg-<?= $d['filing_type'] === 'tds_return' ? 'danger' : ($d['filing_type'] === 'gstr1' ? 'primary' : 'info') ?>"><?= strtoupper(str_replace('_return', '', $d['filing_type'])) ?></span></td>
                                    <td class="small"><?= htmlspecialchars($d['description'] ?? '') ?></td>
                                    <td class="small text-danger"><?= date('d M Y', strtotime($d['due_date'])) ?></td>
                                    <td><span class="badge bg-danger"><?= $daysOverdue ?>d overdue</span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines (14 days) -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Upcoming (14 days)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcoming_deadlines)): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-calendar-check fa-2x mb-2"></i><br>No upcoming deadlines</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Type</th><th>Description</th><th>Due</th><th>Days</th></tr></thead>
                            <tbody>
                            <?php foreach ($upcoming_deadlines as $d):
                                $daysLeft = max(0, (int)((strtotime($d['due_date']) - time()) / 86400));
                            ?>
                                <tr>
                                    <td><span class="badge bg-<?= $d['filing_type'] === 'tds_return' ? 'danger' : ($d['filing_type'] === 'gstr1' ? 'primary' : 'info') ?>"><?= strtoupper(str_replace('_return', '', $d['filing_type'])) ?></span></td>
                                    <td class="small"><?= htmlspecialchars($d['description'] ?? '') ?></td>
                                    <td class="small"><?= date('d M Y', strtotime($d['due_date'])) ?></td>
                                    <td><span class="badge bg-<?= $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success') ?>"><?= $daysLeft ?>d</span></td>
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

<!-- Recent Submissions -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Submissions</h6>
        <a href="<?= BASE_URL ?>/admin/efiling/submissions" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recent_submissions)): ?>
            <div class="p-4 text-center text-muted">No submissions yet. Generate TDS or GST returns to start.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Type</th><th>FY</th><th>Period</th><th>Records</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent_submissions as $s): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= strtoupper($s['submission_type']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($s['financial_year']) ?></td>
                            <td class="small"><?= $s['quarter'] ? "Q{$s['quarter']}" : ($s['period_month'] ? date('M Y', mktime(0,0,0,$s['period_month'],1,$s['period_year'])) : '-') ?></td>
                            <td><?= $s['total_records'] ?></td>
                            <td class="small">₹<?= number_format($s['total_amount'], 0) ?></td>
                            <td><span class="badge bg-<?= $s['status'] === 'accepted' ? 'success' : ($s['status'] === 'rejected' ? 'danger' : ($s['status'] === 'submitted' ? 'primary' : 'secondary')) ?>"><?= ucfirst($s['status']) ?></span></td>
                            <td class="small"><?= date('d M', strtotime($s['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
?>
