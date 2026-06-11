<?php if (!empty($recentItems)): ?>
<div class="row g-4">
    <div class="col-12">
        <div class="card aps-cp-card">
            <div class="card-body aps-cp-card-body">
                <h6 class="mb-3"><i class="fas fa-clock me-2"></i>Recent Activity</h6>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentItems as $item): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($item['title'] ?? $item['name'] ?? 'Item'); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($item['description'] ?? $item['email'] ?? ''); ?></small>
                        </div>
                        <span class="badge bg-<?php echo $item['badge_color'] ?? 'info'; ?>"><?php echo htmlspecialchars($item['status'] ?? 'new'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
