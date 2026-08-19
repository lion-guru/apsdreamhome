<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-signature me-2"></i>Salary Contracts</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>New Contract</button>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Employee</th><th>Type</th><th>Start</th><th>End</th><th>Salary</th><th>Bonus</th><th>Status</th><th>Created By</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($contracts ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No contracts found</td></tr>
                        <?php else: ?>
                            <?php foreach ($contracts as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><strong><?= htmlspecialchars($c['employee_name'] ?? '') ?></strong></td>
                                <td><span class="badge bg-<?= match($c['contract_type']??'permanent') { 'permanent'=>'primary', 'probation'=>'info', 'contract'=>'warning', 'intern'=>'secondary', default=>'secondary' } ?>"><?= ucfirst($c['contract_type'] ?? 'permanent') ?></span></td>
                                <td><?= htmlspecialchars($c['start_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['end_date'] ?? '-') ?></td>
                                <td>₹<?= number_format($c['salary_amount'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($c['signing_bonus'] ?? 0, 2) ?></td>
                                <td><span class="badge bg-<?= match($c['status']??'active') { 'active'=>'success', 'expired'=>'secondary', 'terminated'=>'danger', default=>'secondary' } ?>"><?= ucfirst($c['status'] ?? 'active') ?></span></td>
                                <td><?= htmlspecialchars($c['created_by_name'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/salary/contracts/view/<?= $c['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                    <?php if (($c['status'] ?? '') === 'active'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/salary/contracts/terminate/<?= $c['id'] ?>" class="d-inline" data-aps-confirm="Terminate this contract?">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Cancel contract"><i class="fas fa-ban"></i></button>
                                    </form>
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
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/contracts/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-1"></i>New Contract</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Contract Type</label>
                            <select name="contract_type" class="form-select">
                                <option value="permanent">Permanent</option>
                                <option value="probation">Probation</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Salary Amount (₹)</label><input type="number" step="0.01" name="salary_amount" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Signing Bonus (₹)</label><input type="number" step="0.01" name="signing_bonus" class="form-control" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Terms & Conditions</label><textarea name="terms" class="form-control" rows="4"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
