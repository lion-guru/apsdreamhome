<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Deal Timeline - <?= htmlspecialchars($deal['deal_number'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/deal-pipeline/<?= $deal['id'] ?>" class="btn btn-secondary">Back to Deal</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <h5>Full History</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($timeline)): ?>
            <ul class="list-group">
                <?php foreach ($timeline as $entry): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted"><?= $entry['created_at'] ?></small>
                    </div>
                    <p class="mb-0 mt-1">
                        <strong><?= ucwords(str_replace('_', ' ', $entry['action'])) ?>:</strong>
                        <?= htmlspecialchars($entry['old_value'] ?? '') ?> &rarr; <?= htmlspecialchars($entry['new_value'] ?? '') ?>
                    </p>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-muted">No history recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
