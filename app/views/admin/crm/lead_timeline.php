<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-history me-2 text-info"></i>Lead Timeline</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($timeline)): ?>
                <div class="timeline">
                    <?php foreach ($timeline as $t): ?>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-center" style="width:40px">
                                <i class="fas fa-<?= $t['type'] === 'note' ? 'sticky-note' : ($t['type'] === 'status' ? 'exchange-alt' : 'phone') ?> text-<?= $t['type'] === 'note' ? 'warning' : 'primary' ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted"><?= htmlspecialchars($t['created_at'] ?? $t['activity_date'] ?? '') ?></small>
                                <p class="mb-0"><?= htmlspecialchars($t['description'] ?? $t['content'] ?? $t['notes'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No activity recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>