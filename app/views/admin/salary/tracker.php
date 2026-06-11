<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-tachometer-alt me-2"></i>Salary Tracker</h1>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="employee_id" class="form-select">
                        <option value="">All users</option>
                        <?php foreach ($users ?? [] as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= ($filter_employee ?? 0) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= ($filter_month ?? 0) == $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        <?php for ($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
                        <option value="<?= $y ?>" <?= ($filter_year ?? 0) == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-outline-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Employee</th><th>Month</th><th>Year</th><th>Earnings</th><th>Deductions</th><th>Net Pay</th><th>Paid</th><th>Due</th><th>Date</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($tracker ?? [])): ?>
                            <tr><td colspan="12" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No tracker records</td></tr>
                        <?php else: ?>
                            <?php foreach ($tracker as $t): ?>
                            <tr>
                                <td><?= $t['id'] ?></td>
                                <td><strong><?= htmlspecialchars($t['employee_name'] ?? '') ?></strong></td>
                                <td><?= date('F', mktime(0,0,0,$t['month'] ?? 1,1)) ?></td>
                                <td><?= $t['year'] ?? '' ?></td>
                                <td>₹<?= number_format($t['total_earnings'] ?? 0, 2) ?></td>
                                <td class="text-danger">₹<?= number_format($t['total_deductions'] ?? 0, 2) ?></td>
                                <td><strong>₹<?= number_format($t['net_pay'] ?? 0, 2) ?></strong></td>
                                <td class="text-success">₹<?= number_format($t['paid_amount'] ?? 0, 2) ?></td>
                                <td class="text-warning">₹<?= number_format($t['due_amount'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($t['payment_date'] ?? '-') ?></td>
                                <td><span class="badge bg-<?= match($t['payment_status']??'pending') { 'paid'=>'success', 'partial'=>'warning', 'overdue'=>'danger', 'pending'=>'secondary', default=>'secondary' } ?>"><?= ucfirst($t['payment_status'] ?? 'pending') ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal<?= $t['id'] ?>"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <div class="modal fade" id="updateModal<?= $t['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="<?= BASE_URL ?>/admin/salary/tracker/update/<?= $t['id'] ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Update Tracker</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <p><strong><?= htmlspecialchars($t['employee_name'] ?? '') ?></strong> - <?= date('F', mktime(0,0,0,$t['month']??1,1)) ?> <?= $t['year'] ?? '' ?></p>
                                                <div class="mb-3"><label class="form-label">Net Pay</label><input type="text" class="form-control" value="₹<?= number_format($t['net_pay'] ?? 0, 2) ?>" disabled></div>
                                                <div class="mb-3"><label class="form-label">Paid Amount (₹)</label><input type="number" step="0.01" name="paid_amount" class="form-control" value="<?= $t['paid_amount'] ?? 0 ?>"></div>
                                                <div class="mb-3"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" value="<?= $t['payment_date'] ?? date('Y-m-d') ?>"></div>
                                                <div class="mb-3"><label class="form-label">Status</label>
                                                    <select name="payment_status" class="form-select">
                                                        <option value="pending" <?= ($t['payment_status']??'')==='pending'?'selected':'' ?>>Pending</option>
                                                        <option value="paid" <?= ($t['payment_status']??'')==='paid'?'selected':'' ?>>Paid</option>
                                                        <option value="partial" <?= ($t['payment_status']??'')==='partial'?'selected':'' ?>>Partial</option>
                                                        <option value="overdue" <?= ($t['payment_status']??'')==='overdue'?'selected':'' ?>>Overdue</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
