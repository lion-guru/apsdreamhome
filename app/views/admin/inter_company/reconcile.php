<?php
$reconciliations = $reconciliations ?? [];
$entities = $entities ?? [];
$status = $status ?? 'open';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Inter-Company Reconciliation</h1>
        <div>
            <form method="GET" class="d-inline">
                <label class="me-2" style="margin-bottom:0;">Status:</label>
                <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                    <option value="open" <?= $status==='open'?'selected':'' ?>>Open</option>
                    <option value="reconciled" <?= $status==='reconciled'?'selected':'' ?>>Reconciled</option>
                    <option value="partial" <?= $status==='partial'?'selected':'' ?>>Partial</option>
                </select>
                <a href="<?= BASE_URL ?>/admin/inter-company" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-2"></i>Back to Transactions</a>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card bg-primary text-white"><div class="card-body"><h3><?= count($reconciliations) ?></h3><small>Reconciliations</small></div></div></div>
        <div class="col-md-4"><div class="card bg-info text-white"><div class="card-body"><h3><?= count(array_filter($reconciliations, fn($r)=>$r['status']==='open')) ?></h3><small>Open</small></div></div></div>
        <div class="col-md-4"><div class="card bg-success text-white"><div class="card-body"><h3><?= count(array_filter($reconciliations, fn($r)=>$r['status']==='reconciled')) ?></h3><small>Reconciled</small></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/inter-company/reconcile/store" id="recForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Reconciliation Number</label>
                        <input type="text" name="reconciliation_number" class="form-control" value="" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reconciliation Date</label>
                        <input type="date" name="reconciliation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Entity</label>
                        <select name="from_entity_id" class="form-select" required>
                            <option value="">Select source entity</option>
                            <?php foreach ($entities as $e): ?><option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['entity_name']) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Entity</label>
                        <select name="to_entity_id" class="form-select" required>
                            <option value="">Select destination entity</option>
                            <?php foreach ($entities as $e): ?><option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['entity_name']) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period Start</label>
                        <input type="date" name="period_start" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period End</label>
                        <input type="date" name="period_end" class="form-control" required>
                    </div>
                    <div class="col-md-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create Reconciliation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Reconciliations</h5>
            <span class="badge bg-secondary"><?= count(array_filter($reconciliations, fn($r)=>$r['status']==='open')) ?> Open</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Number</th><th>Date</th><th>From Entity</th><th>To Entity</th><th>Period</th><th class="text-end">From Balance</th><th class="text-end">To Balance</th><th class="text-end">Difference</th><th>Status</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reconciliations)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No reconciliations found.</td></tr>
                <?php else: ?>
                    <?php foreach ($reconciliations as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['reconciliation_number']) ?></td>
                            <td><?= htmlspecialchars($r['reconciliation_date']) ?></td>
                            <td><?= htmlspecialchars($r['from_entity_name']) ?></td>
                            <td><?= htmlspecialchars($r['to_entity_name']) ?></td>
                            <td><?= htmlspecialchars($r['period_start']) ?> to <?= htmlspecialchars($r['period_end']) ?></td>
                            <td class="text-end"><?= number_format((float)$r['from_entity_balance'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$r['to_entity_balance'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$r['difference'], 2) ?></td>
                            <td><span class="badge bg-<?= $r['status']==='reconciled'?'success':($r['status']==='open'?'warning':'info') ?>"><?= ucfirst($r['status']) ?></span></td>
                            <td class="text-end"></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
