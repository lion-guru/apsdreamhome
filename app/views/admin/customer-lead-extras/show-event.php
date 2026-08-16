<?php
// Session started by controller
$page_title = 'Lead Event Details';
$page_description = 'Detailed view of lead event';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Lead Event Details</h1>
            <p class="text-muted">Detailed view of lead event</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/events" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <?php if ($event): ?>
        <!-- Lead Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Lead Information</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?= BASE_URL ?>/assets/images/user/default-avatar.jpg" alt="Avatar" class="img-fluid rounded-circle" />
                    </div>
                    <div class="col-md-9">
                        <h4><?php echo htmlspecialchars($event['lead_name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($event['lead_email'] ?? ''); ?>
                        </p>
                        <?php if (!empty($event['lead_phone'])): ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($event['lead_phone']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($event['lead_company'])): ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-building me-2"></i>
                                <?php echo htmlspecialchars($event['lead_company']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Event Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Event Type:</strong> 
                            <span class="badge bg-<?php 
                                switch ($event['event_type']) {
                                    case 'form_submit':
                                    case 'booking':
                                    case 'site_visit':
                                        echo 'success';
                                        break;
                                    case 'call':
                                    case 'whatsapp':
                                    case 'email':
                                        echo 'info';
                                        break;
                                    case 'view':
                                    case 'click':
                                        echo 'primary';
                                        break;
                                    case 'form_start':
                                        echo 'warning';
                                        break;
                                    default:
                                        echo 'secondary';
                                }
                            ?>">
                                <?php echo ucfirst($event['event_type'] ?? 'Unknown'); ?>
                            </span>
                        </p>
                        <p><strong>Source Page:</strong> <?php echo htmlspecialchars($event['source_page'] ?? 'Direct'); ?></p>
                        <p><strong>Event Time:</strong> <?php echo date('M d, Y H:i:s', strtotime($event['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>IP Address:</strong> <?php echo htmlspecialchars($event['ip_address'] ?? 'Unknown'); ?></p>
                        <p><strong>User Agent:</strong> 
                            <small class="text-muted"><?php echo htmlspecialchars($event['user_agent'] ?? 'Unknown'); ?></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Data -->
        <?php if (!empty($event['event_data'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Event Data</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars($event['event_data']); ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <!-- Metadata -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Event Metadata</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($event['created_at'])); ?></p>
                        <p><strong>Updated At:</strong> <?php echo date('M d, Y H:i', strtotime($event['updated_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <!-- If there's any additional metadata, it would go here -->
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Event Not Found</h4>
            <p>The requested lead event could not be found.</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/events" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    <?php endif; ?>
</div>