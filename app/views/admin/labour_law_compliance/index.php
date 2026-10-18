<?php
$page_title = 'Labour Law Compliance';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Labour Law Compliance</h1>
        <a href="/admin/labour-law-compliance/create" class="btn btn-primary">Add Record</a>
    </div>
    <div class="row mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Total</strong><div><?= (int)$stats['total'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Pending</strong><div><?= (int)$stats['pending'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Filed</strong><div><?= (int)$stats['filed'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Overdue</strong><div><?= (int)$stats['overdue'] ?></div></div></div></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Law</th>
                        <th>Type</th>
                        <th>Registration</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['law_name']) ?></td>
                        <td><?= htmlspecialchars($item['compliance_type']) ?></td>
                        <td><?= htmlspecialchars($item['registration_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['due_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['status']) ?></td>
                        <td><a href="/admin/labour-law-compliance/show/<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
