<?php
/**
 * Reimbursement Claims Inbox (admin)
 * Variables: $claims, $summary, $filter_status
 * Flow: pending -> approved -> paid (paid manually with reference; reimbursements settle outside payroll)
 */

$page_title = $page_title ?? 'Reimbursement Claims';
$claims = $claims ?? [];
$summary = $summary ?? [];
$filter_status = $filter_status ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i><?= htmlspecialchars($page_title) ?></h1>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white"><div class="card-body text-center">
                <h3><?= (int)($summary['total'] ?? 0) ?></h3><small>Total Claims</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark"><div class="card-body text-center">
                <h3>₹<?= number_format($summary['pending_amount'] ?? 0, 0) ?></h3><small>Pending Approval</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white"><div class="card-body text-center">
                <h3>₹<?= number_format($summary['paid_amount'] ?? 0, 0) ?></h3><small>Paid Out</small>
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
                    <?php foreach (['pending','approved','rejected','paid'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filter_status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div></div>

    <!-- Table -->
    <div class="card"><div class="card-body p-0">
        <?php if (empty($claims)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-receipt fa-3x mb-3"></i>
            <h5>No Claims Found</h5>
        </div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>Employee</th><th>Type</th><th>Expense Date</th><th class="text-end">Amount</th>
                <th>Description</th><th>Receipt</th><th class="text-center">Status</th><th>Submitted</th><th class="text-center">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($claims as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['employee_name'] ?? '-') ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($c['employee_code'] ?? '') ?></small></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($c['claim_type']) ?></span></td>
                <td><?= !empty($c['expense_date']) ? date('d M Y', strtotime($c['expense_date'])) : '-' ?></td>
                <td class="text-end fw-bold">₹<?= number_format($c['amount'] ?? 0, 2) ?></td>
                <td><small><?= htmlspecialchars(substr($c['description'] ?? '', 0, 80)) ?></small></td>
                <td class="text-center">
                    <?php if (!empty($c['receipt_path'])): ?>
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($c['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View Receipt"><i class="fas fa-file-alt"></i></a>
                    <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                </td>
                <td class="text-center">
                    <?php $st = $c['status'] ?? 'pending'; ?>
                    <span class="badge bg-<?= $st === 'paid' ? 'success' : ($st === 'approved' ? 'info' : ($st === 'rejected' ? 'danger' : 'warning')) ?>"><?= ucfirst($st) ?></span>
                    <?php if ($st === 'rejected' && !empty($c['rejection_reason'])): ?>
                    <br><small class="text-danger"><?= htmlspecialchars(substr($c['rejection_reason'], 0, 60)) ?></small>
                    <?php endif; ?>
                    <?php if ($st === 'paid' && !empty($c['payment_reference'])): ?>
                    <br><small class="text-muted"><?= htmlspecialchars($c['payment_reference']) ?></small>
                    <?php endif; ?>
                </td>
                <td><small><?= !empty($c['created_at']) ? date('d M Y', strtotime($c['created_at'])) : '-' ?></small></td>
                <td class="text-center">
                    <?php if ($st === 'pending'): ?>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= BASE_URL ?>/admin/salary/reimbursements/approve/<?= (int)$c['id'] ?>" class="btn btn-outline-success" title="Approve" onclick="return confirm('Approve this claim?');"><i class="fas fa-check"></i></a>
                        <button type="button" class="btn btn-outline-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectClaim<?= (int)$c['id'] ?>"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal fade" id="rejectClaim<?= (int)$c['id'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>/admin/salary/reimbursements/reject/<?= (int)$c['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="modal-header"><h5 class="modal-title">Reject Claim #<?= (int)$c['id'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body"><label class="form-label">Reason</label>
                                <textarea name="rejection_reason" class="form-control" rows="2" required></textarea></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </form>
                        </div></div>
                    </div>
                    <?php elseif ($st === 'approved'): ?>
                    <button type="button" class="btn btn-sm btn-outline-success" title="Mark Paid" data-bs-toggle="modal" data-bs-target="#payClaim<?= (int)$c['id'] ?>"><i class="fas fa-money-bill-wave"></i> Pay</button>
                    <div class="modal fade" id="payClaim<?= (int)$c['id'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>/admin/salary/reimbursements/pay/<?= (int)$c['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="modal-header"><h5 class="modal-title">Mark Claim #<?= (int)$c['id'] ?> Paid</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body"><label class="form-label">Payment Reference</label>
                                <input type="text" name="payment_reference" class="form-control" value="PAY-<?= date('YmdHis') ?>" required></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Mark Paid</button>
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

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
</style>
