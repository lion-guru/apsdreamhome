<?php
$pageTitle = $page_title ?? 'Import History';
$history = $history ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Import History</h1>
        <a href="/admin/plots/import" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Import
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Import Activities</h5>
        </div>
        <div class="card-body">
            <?php if (empty($history)): ?>
                <div class="alert alert-info mb-0">No import history found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $entry): ?>
                                <tr>
                                    <td><?= $entry['id'] ?></td>
                                    <td><?= $entry['user_id'] ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($entry['action']) ?></span></td>
                                    <td>
                                        <?php
                                        $context = json_decode($entry['context'] ?? '{}', true);
                                        if ($context):
                                        ?>
                                            <small>
                                                Imported: <?= $context['imported'] ?? 0 ?>,
                                                Errors: <?= $context['errors'] ?? 0 ?>,
                                                Total: <?= $context['total'] ?? 0 ?>
                                                <?php if (!empty($context['colony_id'])): ?>
                                                    (Colony: <?= $context['colony_id'] ?>)
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($entry['ip_address'] ?? '-') ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($entry['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
