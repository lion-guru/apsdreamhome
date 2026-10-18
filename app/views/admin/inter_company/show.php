<?php
$txn = $txn ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Transaction #<?= htmlspecialchars($txn['transaction_number'] ?? '') ?></h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/inter-company" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
            <?php if ($txn['status']==='draft'): ?>
                <a href="<?= BASE_URL ?>/admin/inter-company/create?edit=<?= (int)$txn['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-edit me-2"></i>Edit</a>
                <button class="btn btn-sm btn-outline-success" onclick="fetch('<?= BASE_URL ?>/admin/inter-company/post/<?= (int)$txn['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'))"><i class="fas fa-check me-2"></i>Post</button>
                <button class="btn btn-sm btn-outline-danger" onclick="fetch('<?= BASE_URL ?>/admin/inter-company/delete/<?= (int)$txn['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'))"><i class="fas fa-trash me-2"></i>Delete</button>
            <?php elseif ($txn['status']==='posted'): ?>
                <button class="btn btn-sm btn-outline-success" onclick="fetch('<?= BASE_URL ?>/admin/inter-company/post/<?= (int)$txn['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'))"><i class="fas fa-check me-2"></i>Post</button>
                <button class="btn btn-sm btn-outline-danger" onclick="fetch('<?= BASE_URL ?>/admin/inter-company/cancel/<?= (int)$txn['id'] ?>',{method:'POST'}).then(r=>r.json()).then(d=>d.success?location.reload():alert(d.error||'Failed'))"><i class="fas fa-ban me-2"></i>Cancel</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= htmlspecialchars($txn['transaction_number']) ?></h3><small>Transaction Number</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3><?= htmlspecialchars($txn['transaction_date']) ?></h3><small>Transaction Date</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= number_format((float)$txn['amount'], 2) ?></h3><small>Amount (<?= htmlspecialchars($txn['currency']) ?>)</small></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h3><?= number_format((float)$txn['amount_inr'], 2) ?></h3><small>Amount (INR)</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Transaction Details</h5></div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr><th style="width:30%">From Entity</th><td><?= htmlspecialchars($txn['from_entity_name']) ?> (<?= htmlspecialchars($txn['entity_code'] ?? '') ?>)</td></tr>
                <tr><th>To Entity</th><td><?= htmlspecialchars($txn['to_entity_name']) ?></td></tr>
                <tr><th>Transaction Type</th><td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$txn['transaction_type']))) ?></td></tr>
                <tr><th>Currency</th><td><?= htmlspecialchars($txn['currency']) ?></td></tr>
                <tr><th>Exchange Rate</th><td><?= number_format((float)$txn['exchange_rate'], 4) ?></td></tr>
                <tr><th>Amount</th><td><?= number_format((float)$txn['amount'], 2) ?></td></tr>
                <tr><th>Amount (INR)</th><td><?= number_format((float)$txn['amount_inr'], 2) ?></td></tr>
                <tr><th>Reference Document</th><td><?= htmlspecialchars($txn['reference_document'] ?? '-') ?></td></tr>
                <tr><th>Description</th><td><?= htmlspecialchars($txn['description'] ?? '-') ?></td></tr>
                <tr><th>Notes</th><td><?= htmlspecialchars($txn['notes'] ?? '-') ?></td></tr>
                <tr><th>Status</th><td><span class="badge bg-<?= $txn['status']==='posted'?'success':($txn['status']==='draft'?'warning':($txn['status']==='reconciled'?'info':'danger')) ?>"><?= ucfirst($txn['status']) ?></span></td></tr>
                <tr><th>Created</th><td><?= htmlspecialchars($txn['created_at']) ?></td></tr>
                <?php if ($txn['posted_at']): ?><tr><th>Posted At</th><td><?= htmlspecialchars($txn['posted_at']) ?></td></tr><?php endif; ?>
            </table>
        </div>
    </div>
</div>
