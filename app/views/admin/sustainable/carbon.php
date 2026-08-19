<?php
$entries = $entries ?? [];
$summary = $summary ?? ['total_credits' => 0, 'total_value' => 0];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-globe me-2 text-warning"></i>Carbon Credit Ledger</h2>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addCredit"><i class="fas fa-plus me-1"></i> Add Entry</button>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card bg-warning text-white">
            <div class="card-body"><h6 class="text-uppercase small">Total Credits (t CO₂e)</h6><h3><?= number_format($summary['total_credits'] ?? 0, 1) ?></h3></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body"><h6 class="text-uppercase small">Portfolio Value</h6><h3>₹<?= number_format($summary['total_value'] ?? 0, 2) ?></h3></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($entries)): ?>
            <p class="text-muted text-center py-4">No carbon credit entries yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Type</th><th>Reference</th><th>Date</th><th>Credits (t)</th><th>Rate</th><th>Value</th><th>Verified</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($e['credit_type'] ?? '') ?></strong></td>
                            <td><?= ucfirst($e['reference_type'] ?? '') ?> #<?= $e['reference_id'] ?? '—' ?></td>
                            <td><?= htmlspecialchars($e['credit_date'] ?? '') ?></td>
                            <td><?= number_format($e['credits_earned'] ?? 0, 1) ?></td>
                            <td>₹<?= number_format($e['value_per_credit'] ?? 0, 2) ?></td>
                            <td>₹<?= number_format($e['total_value'] ?? 0, 2) ?></td>
                            <td><span class="badge bg-<?= ($e['verified'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($e['verified'] ?? 0) ? 'Yes' : 'No' ?></span></td>
                            <td class="text-end">
                                <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/carbon/delete/<?= $e['id'] ?>" class="d-inline" onsubmit="return confirm('Delete entry?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addCredit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/carbon/save">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <div class="modal-header"><h5 class="modal-title">Add Carbon Credit Entry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Credit Type</label><input type="text" name="credit_type" class="form-control" placeholder="e.g. renewable_energy" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Reference Type</label><select name="reference_type" class="form-select"><option value="project">Project</option><option value="plot">Plot</option><option value="audit">Audit</option></select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Reference ID</label><input type="number" name="reference_id" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Credits Earned (t)</label><input type="number" step="0.01" name="credits_earned" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Value / Credit (₹)</label><input type="number" step="0.01" name="value_per_credit" class="form-control" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Credit Date</label><input type="date" name="credit_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="verified" id="v"><label class="form-check-label" for="v">Verified</label></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-warning">Save</button></div>
            </form>
        </div>
    </div>
</div>
