<?php
$page_title = 'Call Recordings';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Call Recordings</h1>
    </div>
    <div class="row mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Total</strong><div><?= (int)$stats['total'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Completed</strong><div><?= (int)$stats['completed'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Leads</strong><div><?= (int)$stats['leads'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Seconds</strong><div><?= (int)$stats['total_seconds'] ?></div></div></div></div>
    </div>
    <form method="get" action="/admin/crm/call-recordings" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3"><input type="number" name="lead_id" class="form-control" placeholder="Lead ID" value="<?= htmlspecialchars($_GET['lead_id'] ?? '') ?>"></div>
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary">Filter</button></div>
        </div>
    </form>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lead</th>
                        <th>Agent</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Duration</th>
                        <th>Response</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= (int)$item['id'] ?></td>
                        <td><?= htmlspecialchars($item['lead_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['agent_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['status'] ?? '') ?></td>
                        <td><?= (int)($item['duration_seconds'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($item['customer_response'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['created_at'] ?? '') ?></td>
                        <td><a href="/admin/crm/call-recordings/show/<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">Play</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
