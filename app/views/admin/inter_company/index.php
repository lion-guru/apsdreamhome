<?php
$transactions = $transactions ?? [];
$entities = $entities ?? [];
$stats = $stats ?? ['total_entities'=>0,'total_transactions'=>0,'draft_transactions'=>0,'posted_transactions'=>0,'reconciled_transactions'=>0,'total_amount'=>0,'open_reconciliations'=>0];
$filters = $filters ?? ['status'=>'','from_date'=>'','to_date'=>'','from_entity'=>0,'to_entity'=>0];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Inter-Company Transactions</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/inter-company/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Transaction</a>
            <a href="<?= BASE_URL ?>/admin/inter-company/reconcile" class="btn btn-outline-secondary ms-2"><i class="fas fa-balance-scale me-2"></i>Reconciliation</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total_entities'] ?></h3><small>Entities</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3><?= $stats['total_transactions'] ?></h3><small>Transactions</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= $stats['posted_transactions'] ?></h3><small>Posted</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= number_format((float)$stats['total_amount'], 2) ?></h3><small>Total Amount (INR)</small></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option><option value="draft" <?= $filters['status']==='draft'?'selected':'' ?>>Draft</option><option value="posted" <?= $filters['status']==='posted'?'selected':'' ?>>Posted</option><option value="reconciled" <?= $filters['status']==='reconciled'?'selected':'' ?>>Reconciled</option><option value="cancelled" <?= $filters['status']==='cancelled'?'selected':'' ?>>Cancelled</option></select></div>
                <div class="col-md-2"><label class="form-label">From Date</label><input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($filters['from_date']) ?>"></div>
                <div class="col-md-2"><label class="form-label">To Date</label><input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($filters['to_date']) ?>"></div>
                <div class="col-md-2"><label class="form-label">From Entity</label><select name="from_entity" class="form-select"><option value="0">All</option><?php foreach ($entities as $e): ?><option value="<?= (int)$e['id'] ?>" <?= (int)$filters['from_entity']===(int)$e['id']?'selected':'' ?><?= htmlspecialchars($e['entity_name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">To Entity</label><select name="to_entity" class="form-select"><option value="0">All</option><?php foreach ($entities as $e): ?><option value="<?= (int)$e['id'] ?>" <?= (int)$filters['to_entity']===(int)$e['id']?'selected':'' ?><?= htmlspecialchars($e['entity_name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Transactions</h5></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Number</th><th>Date</th><th>From Entity</th><th>To Entity</th><th>Type</th><th class="text-end">Amount (INR)</th><th>Status</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No inter-company transactions found.</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['transaction_number']) ?></td>
                            <td><?= htmlspecialchars($t['transaction_date']) ?></td>
                            <td><?= htmlspecialchars($t['from_entity_name']) ?></td>
                            <td><?= htmlspecialchars($t['to_entity_name']) ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$t['transaction_type']))) ?></td>
                            <td class="text-end"><?= number_format((float)$t['amount_inr'], 2) ?></td>
                            <td><span class="badge bg-<?= $t['status']==='posted'?'success':($t['status']==='draft'?'warning':($t['status']==='reconciled'?'info':'danger')) ?>"><?= ucfirst($t['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/inter-company/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                <?php if ($t['status']==='draft'): ?>
                                    <a href="<?= BASE_URL ?>/admin/inter-company/create?edit=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete this transaction?'))fetch('<?= BASE_URL ?>/admin/inter-company/delete/<?= (int)$t['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'));"><i class="fas fa-trash"></i></button>
                                <?php elseif ($t['status']==='draft' || $t['status']==='posted'): ?>
                                    <button class="btn btn-sm btn-outline-success" onclick="fetch('<?= BASE_URL ?>/admin/inter-company/post/<?= (int)$t['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'))"><i class="fas fa-check"></i> Post</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="fetch('<?= BASE_URL ?>/admin/inter-company/cancel/<?= (int)$t['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'))"><i class="fas fa-ban"></i> Cancel</button>
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
