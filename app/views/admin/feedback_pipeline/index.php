<?php
$page_title = 'Customer Feedback Pipeline';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Customer Feedback Pipeline</h1>
        <a href="/admin/feedback-pipeline/create" class="btn btn-primary">Dispatch Survey</a>
    </div>
    <div class="row mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Total</strong><div><?= (int)$stats['total'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Sent</strong><div><?= (int)$stats['sent'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Responded</strong><div><?= (int)$stats['responded'] ?></div></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Avg Rating</strong><div><?= htmlspecialchars((string)$stats['avg_rating']) ?></div></div></div></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Source</th>
                        <th>Customer</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Sent</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= (int)$item['id'] ?></td>
                        <td><?= htmlspecialchars($item['source_type'] ?? '') ?> #<?= (int)($item['source_id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($item['user_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['channel'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['status'] ?? '') ?></td>
                        <td><?= htmlspecialchars((string)($item['rating'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($item['sent_at'] ?? '') ?></td>
                        <td><a href="/admin/feedback-pipeline/show/<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
