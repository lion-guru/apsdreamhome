<?php
$page_title = 'Lead Call Recordings';
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Lead Call Recordings</h1>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agent</th>
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
                        <td><?= htmlspecialchars($item['agent_name'] ?? '') ?></td>
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
    <a href="/admin/crm/call-recordings" class="btn btn-secondary">Back</a>
</div>
