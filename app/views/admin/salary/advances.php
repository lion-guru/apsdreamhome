<?php
/**
 * Salary Advances Inbox (admin)
 * Variables: $advances, $users, $summary, $filter_status
 */

$page_title = $page_title ?? 'Salary Advances';
$advances = $advances ?? [];
$users = $users ?? [];
$summary = $summary ?? [];
$filter_status = $filter_status ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-hand-holding-usd me-2"></i><?= htmlspecialchars($page_title) ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdvanceModal">
            <i class="fas fa-plus me-1"></i> New Advance
        </button>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white"><div class="card-body text-center">
                <h3><?= (int)($summary['total'] ?? 0) ?></h3><small>Total Advances</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark"><div class="card-body text-center">
                <h3>₹<?= number_format($summary['pending_amount'] ?? 0, 0) ?></h3><small>Pending Approval</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white"><div class="card-body text-center">
                <h3>₹<?= number_format($summary['active_amount'] ?? 0, 0) ?></h3><small>Active (Recovering)</small>
            </div></div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    <?php foreach (['pending','active','approved','rejected','closed'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filter_status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div></div>

    <!-- Table -->
    <div class="card"><div class="card-body p-0">
        <?php if (empty($advances)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-hand-holding-usd fa-3x mb-3"></i>
            <h5>No Advances Found</h5>
        </div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>Advance No</th><th>Employee</th><th class="text-end">Amount</th>
                <th class="text-center">EMI/mo</th><th>Tenure</th><th>Start</th>
                <th class="text-center">Status</th><th>Requested</th><th class="text-center">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($advances as $a): ?>
            <tr>
                <td><strong><?= htmlspecialchars($a['advance_no'] ?? ('ADV-' . $a['id'])) ?></strong></td>
                <td><strong><?= htmlspecialchars($a['employee_name'] ?? '-') ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($a['employee_code'] ?? '') ?></small></td>
                <td class="text-end">₹<?= number_format($a['amount'] ?? 0, 2) ?></td>
                <td class="text-center">₹<?= number_format($a['emi_amount'] ?? 0, 2) ?></td>
                <td><?= (int)($a['repay_months'] ?? 0) ?> mo<?= ((float)($a['interest_rate'] ?? 0) > 0) ? ' @ ' . $a['interest_rate'] . '%' : '' ?></td>
                <td><?= !empty($a['start_month']) ? date('M Y', mktime(0,0,0,$a['start_month'],1,$a['start_year'] ?? date('Y'))) : '-' ?></td>
                <td class="text-center">
                    <?php $st = $a['status'] ?? 'pending'; ?>
                    <span class="badge bg-<?= $st === 'active' ? 'info' : ($st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'closed' ? 'secondary' : 'warning'))) ?>"><?= ucfirst($st) ?></span>
                </td>
                <td><small><?= !empty($a['created_at']) ? date('d M Y', strtotime($a['created_at'])) : '-' ?></small><br>
                    <small class="text-muted"><?= htmlspecialchars(substr($a['reason'] ?? '', 0, 60)) ?></small></td>
                <td class="text-center">
                    <?php if ($st === 'pending'): ?>
                    <div class="btn-group btn-group-sm">
                        <form method="POST" action="<?= BASE_URL ?>/admin/salary/advances/approve/<?= (int)$a['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this advance? Recovery starts from its start month.');"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="btn btn-outline-success" title="Approve"><i class="fas fa-check"></i></button></form>
                        <button type="button" class="btn btn-outline-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal<?= (int)$a['id'] ?>"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal fade" id="rejectModal<?= (int)$a['id'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>/admin/salary/advances/reject/<?= (int)$a['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="modal-header"><h5 class="modal-title">Reject Advance <?= htmlspecialchars($a['advance_no'] ?? '') ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body"><label class="form-label">Reason (optional)</label>
                                <textarea name="rejection_reason" class="form-control" rows="2"></textarea></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </form>
                        </div></div>
                    </div>
                    <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div></div>
</div>

<!-- Create Advance Modal -->
<div class="modal fade" id="createAdvanceModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>/admin/salary/advances/create">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>New Salary Advance</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select Employee</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['employee_code'] ?? '') ?>)</option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Repay Months <span class="text-danger">*</span></label>
                        <input type="number" min="1" max="60" name="repay_months" class="form-control" value="6" required></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Interest % (annual, 0 = interest-free)</label>
                        <input type="number" step="0.01" min="0" name="interest_rate" class="form-control" value="0"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Start Month</label>
                        <input type="number" min="1" max="12" name="start_month" class="form-control" value="<?= date('n') ?>"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Start Year</label>
                        <input type="number" min="2020" max="2100" name="start_year" class="form-control" value="<?= date('Y') ?>"></div>
                </div>
                <div class="mb-3"><label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Purpose of advance..."></textarea></div>
                <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-1"></i>EMI is auto-computed. Recovery starts from the start month via payroll (logged per month, idempotent).</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Advance</button>
            </div>
        </form>
    </div></div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
</style>
