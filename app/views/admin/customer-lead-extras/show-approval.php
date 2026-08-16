<?php
// Session started by controller
$page_title = 'Approval Request Details';
$page_description = 'Detailed view of lead assignment approval request';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Approval Request Details</h1>
            <p class="text-muted">Detailed view of lead assignment approval request</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/approvals" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <?php if ($approval): ?>
        <!-- Lead Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Lead Information</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-4">
                        <img src="<?= BASE_URL ?>/assets/images/user/default-avatar.jpg" alt="Avatar" class="img-fluid rounded-circle" />
                    </div>
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($approval['lead_name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($approval['lead_email'] ?? ''); ?>
                        </p>
                        <?php if (!empty($approval['lead_phone'])): ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($approval['lead_phone']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($approval['lead_company'])): ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-building me-2"></i>
                                <?php echo htmlspecialchars($approval['lead_company']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Request Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Request Type:</strong> 
                            <span class="badge bg-light text-dark border">
                                <?php echo ucfirst($approval['request_type'] ?? 'Unknown'); ?>
                            </span>
                        </p>
                        <p><strong>Priority:</strong> 
                            <span class="badge bg-<?php 
                                switch ($approval['priority']) {
                                    case 'low':
                                        echo 'success';
                                        break;
                                    case 'medium':
                                        echo 'warning';
                                        break;
                                    case 'high':
                                        echo 'danger';
                                        break;
                                    case 'urgent':
                                        echo 'dark';
                                        break;
                                    default:
                                        echo 'secondary';
                                }
                            ?>">
                                <?php echo ucfirst($approval['priority'] ?? 'Unknown'); ?>
                            </span>
                        </p>
                        <p><strong>Requested By:</strong> 
                            <?php echo htmlspecialchars($approval['requested_by_name'] ?? 'Unknown'); ?> 
                            (<?php echo htmlspecialchars($approval['requested_by_email'] ?? 'No email'); ?>)
                        </p>
                        <p><strong>Requested To:</strong> 
                            <?php echo htmlspecialchars($approval['requested_to_name'] ?? 'Unknown'); ?> 
                            (<?php echo htmlspecialchars($approval['requested_to_email'] ?? 'No email'); ?>)
                        </p>
                        <p><strong>Request Date:</strong> <?php echo date('M d, Y H:i', strtotime($approval['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($approval['notes'])): ?>
                            <p><strong>Request Notes:</strong></p>
                            <p class="text-muted fsmall"><?php echo nl2br(htmlspecialchars($approval['notes'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Current Status</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php 
                                switch ($approval['status']) {
                                    case 'pending':
                                        echo 'warning';
                                        break;
                                    case 'approved':
                                        echo 'success';
                                        break;
                                    case 'rejected':
                                        echo 'danger';
                                        break;
                                    default:
                                        echo 'secondary';
                                }
                            ?>">
                                <?php echo ucfirst($approval['status'] ?? 'Unknown'); ?>
                            </span>
                        </p>
                        <?php if ($approval['status'] !== 'pending' && !empty($approval['admin_notes'])): ?>
                            <p><strong>Admin Notes:</strong></p>
                            <p class="text-muted fsmall"><?php echo nl2br(htmlspecialchars($approval['admin_notes'])); ?></p>
                        <?php endif; ?>
                        <?php if ($approval['status'] !== 'pending' && !empty($approval['approved_by_name'])): ?>
                            <p><strong>Reviewed By:</strong> 
                                <?php echo htmlspecialchars($approval['approved_by_name'] ?? 'Unknown'); ?> 
                                (<?php echo htmlspecialchars($approval['approved_by_email'] ?? 'No email'); ?>)
                            </p>
                            <p><strong>Reviewed At:</strong> <?php echo date('M d, Y H:i', strtotime($approval['approved_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <!-- Approval Form (only show if pending) -->
                        <?php if ($approval['status'] === 'pending'): ?>
                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/customer-lead/approvals/update-status/<?php echo $approval['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Update Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="approved">Approve Request</option>
                                        <option value="rejected">Reject Request</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin Notes (Optional)</label>
                                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add any notes about this decision..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-2"></i> Update Status
                                </button>
                                <a href="<?php echo BASE_URL; ?>/admin/customer-lead/approvals" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Request Metadata</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($approval['created_at'])); ?></p>
                        <p><strong>Updated At:</strong> <?php echo date('M d, Y H:i', strtotime($approval['updated_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Request ID:</strong> #<?php echo $approval['id']; ?></p>
                        <?php if (!empty($approval['assignment_id'])): ?>
                            <p><strong>Assignment ID:</strong> #<?php echo $approval['assignment_id']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Approval Request Not Found</h4>
            <p>The requested approval request could not be found.</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/approvals" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    <?php endif; ?>
</div>