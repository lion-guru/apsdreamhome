<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-history me-2"></i>Salary History</h1>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-auto">
                    <select name="employee_id" class="form-select">
                        <option value="">All users</option>
                        <?php foreach ($users ?? [] as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= ($filter_employee ?? 0) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
                        <?php endforeach; ?>
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
                        <th>#</th><th>Employee</th><th>Field</th><th>Old Value</th><th>New Value</th><th>Changed By</th><th>Changed At</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($history ?? [])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No history records</td></tr>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= $h['id'] ?></td>
                                <td><strong><?= htmlspecialchars($h['employee_name'] ?? '') ?></strong></td>
                                <td><code><?= htmlspecialchars(str_replace('_',' ', $h['field_changed'] ?? '')) ?></code></td>
                                <td class="text-muted"><?= htmlspecialchars($h['old_value'] ?? '-') ?></td>
                                <td class="text-success"><?= htmlspecialchars($h['new_value'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($h['changed_by_name'] ?? 'System') ?></td>
                                <td><?= htmlspecialchars($h['changed_at'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
