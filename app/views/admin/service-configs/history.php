<?php
/**
 * Service config change history (read-only audit).
 * @var array $history
 * @var string $service
 */
$history = $history ?? [];
$service = $service ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="cp-card">
    <div class="cp-card-header">
        <h5 class="m-0"><i class="fas fa-history me-2"></i>Configuration History<?= $service !== '' ? ' — ' . htmlspecialchars($service) : '' ?></h5>
        <div class="d-flex gap-2">
            <?php if ($service !== ''): ?>
                <a href="<?= $base ?>/admin/service-configs/history" class="cp-btn cp-btn-outline">All services</a>
            <?php endif; ?>
            <a href="<?= $base ?>/admin/service-configs" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="cp-card-body">
        <?php if (empty($history)): ?>
            <div class="text-center py-4" style="color:#8892b0">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p class="mb-0">No configuration changes recorded yet. Changes made on the settings page appear here with who/when/old/new.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table sim-table">
                    <thead>
                        <tr><th>When</th><th>Service</th><th>Key</th><th>Old</th><th>New</th><th>By</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['created_at'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($h['service_name'] ?? '') ?></span></td>
                                <td><code><?= htmlspecialchars($h['config_key'] ?? '') ?></code></td>
                                <td><?= htmlspecialchars((string)($h['old_value'] ?? '—')) ?></td>
                                <td><strong><?= htmlspecialchars((string)($h['new_value'] ?? '')) ?></strong></td>
                                <td><?= htmlspecialchars($h['changed_by_name'] ?? ('#' . ($h['changed_by'] ?? 0))) ?></td>
                                <td><?= htmlspecialchars($h['ip_address'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
