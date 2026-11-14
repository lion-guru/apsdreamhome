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
                        <th scope="col">ID</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Status</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Response</th>
                        <th scope="col">Created</th>
                        <th scope="col">Action</th>
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
