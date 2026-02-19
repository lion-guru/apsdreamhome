<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-3">
                        <a href="<?php echo BASE_URL; ?>dashboard" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/profile" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-user me-2"></i> My Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/settings" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <a href="<?php echo BASE_URL; ?>notifications" class="list-group-item list-group-item-action active py-3">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Notifications</h2>
                <?php if (!empty($data['notifications'])): ?>
                    <button class="btn btn-outline-primary btn-sm">Mark all as read</button>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <?php if (empty($data['notifications'])): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">You have no new notifications.</p>
                            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary btn-sm">Browse Properties</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($data['notifications'] as $notification): ?>
                                <div class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($notification['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1 text-secondary"><?php echo htmlspecialchars($notification['message']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
