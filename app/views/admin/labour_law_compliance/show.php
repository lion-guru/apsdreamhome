<?php
$page_title = 'Labour Law Compliance Detail';
$csrf = \App\Helpers\SecurityHelper::generateCSRFToken();
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Labour Law Compliance Detail</h1>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Law:</strong> <?= htmlspecialchars($item['law_name']) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($item['compliance_type']) ?></p>
            <p><strong>Registration:</strong> <?= htmlspecialchars($item['registration_no'] ?? '') ?></p>
            <p><strong>Authority:</strong> <?= htmlspecialchars($item['authority'] ?? '') ?></p>
            <p><strong>Unit:</strong> <?= htmlspecialchars($item['applicable_unit'] ?? '') ?></p>
            <p><strong>Frequency:</strong> <?= htmlspecialchars($item['frequency'] ?? '') ?></p>
            <p><strong>Due Date:</strong> <?= htmlspecialchars($item['due_date'] ?? '') ?></p>
            <p><strong>Last Filed:</strong> <?= htmlspecialchars($item['last_filed_date'] ?? '') ?></p>
            <p><strong>Next Due:</strong> <?= htmlspecialchars($item['next_due_date'] ?? '') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($item['status']) ?></p>
            <p><strong>Penalty:</strong> <?= htmlspecialchars((string)$item['penalty_amount']) ?></p>
            <p><strong>Remarks:</strong> <?= htmlspecialchars($item['remarks'] ?? '') ?></p>
        </div>
    </div>
    <form method="post" action="/admin/labour-law-compliance/update-status/<?= (int)$item['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="filed" <?= $item['status'] === 'filed' ? 'selected' : '' ?>>Filed</option>
                <option value="overdue" <?= $item['status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                <option value="not_applicable" <?= $item['status'] === 'not_applicable' ? 'selected' : '' ?>>Not Applicable</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Last Filed Date</label>
            <input type="date" name="last_filed_date" class="form-control" value="<?= htmlspecialchars($item['last_filed_date'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Next Due Date</label>
            <input type="date" name="next_due_date" class="form-control" value="<?= htmlspecialchars($item['next_due_date'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Penalty Amount</label>
            <input type="number" step="0.01" name="penalty_amount" class="form-control" value="<?= htmlspecialchars((string)$item['penalty_amount']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control"><?= htmlspecialchars($item['remarks'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
    <form method="post" action="/admin/labour-law-compliance/<?= (int)$item['id'] ?>/delete" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
    <a href="/admin/labour-law-compliance" class="btn btn-secondary mt-3">Back</a>
</div>
