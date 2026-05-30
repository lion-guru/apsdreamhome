<?php
$page_title = $page_title ?? 'AI Context Memory';
$entries = $entries ?? [];

function getImportanceColor($level) {
    $colors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'critical' => 'danger'];
    return $colors[strtolower($level ?? 'low')] ?? 'secondary';
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">AI Context Memory</h1>
        <p class="text-muted mb-0">User context and preferences stored by AI for personalization</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Context Entries</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Context Type</th>
                        <th>Key</th>
                        <th>Value</th>
                        <th>Importance</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No context memory entries yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($entries as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['user_name'] ?? 'Guest') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($row['user_email'] ?? '') ?></small>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['context_type'] ?? 'N/A') ?></span></td>
                                <td><code class="small"><?= htmlspecialchars($row['context_key'] ?? '') ?></code></td>
                                <td><small class="text-muted"><?= htmlspecialchars(mb_substr($row['context_value'] ?? '', 0, 60)) ?><?= mb_strlen($row['context_value'] ?? '') > 60 ? '...' : '' ?></small></td>
                                <td>
                                    <span class="badge bg-<?= getImportanceColor($row['importance_level'] ?? 'low') ?>">
                                        <?= ucfirst($row['importance_level'] ?? 'low') ?>
                                    </span>
                                </td>
                                <td><small><?= date('M j, Y H:i', strtotime($row['created_at'] ?? 'now')) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
