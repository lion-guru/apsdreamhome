<?php
$history = $history ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Configuration Audit History</h1>
        <a href="<?= BASE_URL . '/admin/service-configs' ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Configuration</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($history)): ?>
                <div class="text-center text-muted py-5"><i class="fas fa-history fa-3x mb-3"></i><p>No configuration changes recorded yet.</p></div>
            <?php else: ?>
            <table class="table mb-0">
                <thead><tr><th>Service</th><th>Key</th><th>Old Value</th><th>New Value</th><th>Notes</th><th>When</th></tr></thead>
                <tbody>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($h['service_name']) ?></span></td>
                        <td><code><?= htmlspecialchars($h['config_key']) ?></code></td>
                        <td><?php if ($h['is_secret']): ?><span class="text-muted">••••••</span><?php else: ?><?= htmlspecialchars($h['old_value'] ?? '') ?><?php endif; ?></td>
                        <td><?php if ($h['is_secret']): ?><span class="text-muted">••••••</span><?php else: ?><?= htmlspecialchars($h['new_value'] ?? '') ?><?php endif; ?></td>
                        <td><?= htmlspecialchars($h['notes'] ?? '') ?></td>
                        <td><?= $h['created_at'] ?? '' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
