<?php
$staffList = $staffList ?? [];
$assignedUser = $assignedUser ?? null;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Inquiry Details</h1>
        <p class="text-muted mb-0">View inquiry #<?php echo e($inquiry['id']); ?></p>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/inquiries" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Inquiry Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Name</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($inquiry['name'] ?? ''); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Email</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($inquiry['email'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Phone</label>
                        <div class="fw-semibold">
                            <?php echo htmlspecialchars($inquiry['phone'] ?? ''); ?>
                            <a href="tel:<?php echo htmlspecialchars($inquiry['phone'] ?? ''); ?>" class="btn btn-sm btn-success ms-2">
                                <i class="fas fa-phone"></i>
                            </a>
                            <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $inquiry['phone']); ?>" target="_blank" class="btn btn-sm btn-success">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Type</label>
                        <div class="fw-semibold">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($inquiry['type'] ?? 'General')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Message</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <pre class="mb-0"><?php echo htmlspecialchars($inquiry['message'] ?? ''); ?></pre>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Staff Assignment Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-check me-2 text-primary"></i>Assigned Staff</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3">
                    <label class="text-muted small">Current Handler</label>
                    <?php if (!empty($assignedUser)): ?>
                        <div class="d-flex align-items-center mt-1 p-2 border rounded bg-light">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-weight:600;">
                                <?php echo strtoupper(substr($assignedUser['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($assignedUser['name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars(ucfirst($assignedUser['role'])); ?> &bull; <?php echo htmlspecialchars($assignedUser['phone'] ?? $assignedUser['email'] ?? ''); ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning py-2 px-3 mt-1 mb-0 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>Unassigned &mdash; No staff assigned yet</span>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>/admin/inquiries/assign">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" value="<?php echo e($inquiry['id']); ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign / Reassign Staff</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">-- Select Staff Member --</option>
                            <?php foreach ($staffList as $staff): ?>
                                <option value="<?php echo e($staff['id']); ?>" <?php echo (!empty($inquiry['assigned_to']) && $inquiry['assigned_to'] == $staff['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($staff['name']); ?> (<?php echo htmlspecialchars(ucfirst($staff['role'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-paper-plane me-1"></i>Assign Staff & Notify
                    </button>
                </form>
            </div>
        </div>

        <!-- Status Update -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Update Status</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/inquiries/update-status">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" value="<?php echo e($inquiry['id']); ?>">
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="pending" <?php echo $inquiry['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="contacted" <?php echo $inquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                            <option value="closed" <?php echo $inquiry['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="tel:<?php echo htmlspecialchars($inquiry['phone'] ?? ''); ?>" class="btn btn-success">
                        <i class="fas fa-phone me-2"></i>Call Customer
                    </a>
                    <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $inquiry['phone']); ?>?text=Hi <?php echo urlencode($inquiry['name']); ?>, Thank you for your inquiry from our website." target="_blank" class="btn btn-success">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($inquiry['email'] ?? ''); ?>?subject=Re: APS Dream Home Inquiry" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-2">
                    <label class="text-muted small">Created</label>
                    <div><?php echo date('d M Y, h:i A', strtotime($inquiry['created_at'])); ?></div>
                </div>
                <?php if (!empty($inquiry['updated_at'])): ?>
                <div class="mb-2">
                    <label class="text-muted small">Last Updated</label>
                    <div><?php echo date('d M Y, h:i A', strtotime($inquiry['updated_at'])); ?></div>
                </div>
                <?php endif; ?>
                <hr>
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/inquiries/delete/<?php echo e($inquiry['id']); ?>" data-aps-confirm="Are you sure you want to delete this inquiry?">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="fas fa-trash me-1"></i>Delete Inquiry
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
