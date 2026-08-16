<?php
// Session started by controller
$page_title = 'Customer Journey Details';
$page_description = 'Detailed view of customer journey';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Customer Journey Details</h1>
            <p class="text-muted">Detailed view of customer journey</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/journeys" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <?php if ($journey): ?>
        <!-- Customer Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?= BASE_URL ?>/assets/images/user/default-avatar.jpg" alt="Avatar" class="img-fluid rounded-circle" />
                    </div>
                    <div class="col-md-9">
                        <h4><?php echo htmlspecialchars($journey['customer_name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($journey['customer_email'] ?? ''); ?>
                        </p>
                        <?php if (!empty($journey['customer_phone'])): ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($journey['customer_phone']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journey Overview -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Journey Overview</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Journey Type:</strong> <?php echo htmlspecialchars($journey['journey'] ?? 'Unknown'); ?></p>
                        <p><strong>Started At:</strong> <?php echo date('M d, Y H:i', strtotime($journey['started_at'])); ?></p>
                        <p><strong>Last Touch At:</strong> <?php echo date('M d, Y H:i', strtotime($journey['last_touch_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $started = new DateTime($journey['started_at']);
                        $lastTouch = new DateTime($journey['last_touch_at']);
                        $duration = $started->diff($lastTouch);
                        $durationDays = $duration->days;
                        $durationHours = $duration->h;
                        ?>
                        <p><strong>Duration:</strong> <?php echo $durationDays; ?> days, <?php echo $durationHours; ?> hours</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php echo strpos(strtolower($journey['journey']), 'completed') !== false || 
                                                                strpos(strtolower($journey['journey']), 'converted') !== false ? 
                                                                'success' : (strpos(strtolower($journey['journey']), 'active') !== false ? 'primary' : 'secondary'); ?>">
                                <?php echo ucfirst($journey['journey'] ?? 'Unknown'); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Journey Timeline</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <!-- This would typically be populated with actual timeline events from a separate table -->
                <!-- For now, we'll show a placeholder -->
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Journey Started</h6>
                            <p class="timeline-text text-muted">Customer first interacted with the platform</p>
                            <small class="timeline-date"><?php echo date('M d, Y H:i', strtotime($journey['started_at'])); ?></small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Last Activity</h6>
                            <p class="timeline-text text-muted">Most recent customer interaction</p>
                            <small class="timeline-date"><?php echo date('M d, Y H:i', strtotime($journey['last_touch_at'])); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Journey Metadata</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($journey['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Updated At:</strong> <?php echo date('M d, Y H:i', strtotime($journey['updated_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Journey Not Found</h4>
            <p>The requested customer journey could not be found.</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/journeys" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
    border-left: 2px solid #e9ecef;
}

.timeline-item {
    margin-bottom: 2rem;
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -8px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 1rem;
}

.timeline-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.timeline-text {
    margin-bottom: 0.5rem;
}

.timeline-date {
    font-size: 0.875rem;
    color: #6c757d;
}
</style>