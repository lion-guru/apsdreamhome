<?php
$page_title = $page_title ?? 'Edit Lead';
$lead = $lead ?? [];
$statuses = $statuses ?? [];
$sources = $sources ?? [];
$assignees = $assignees ?? [];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Lead</h1>
            <p class="text-muted mb-0">Update lead information</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Leads
            </a>
            <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Lead Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/update">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Full Name *</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= htmlspecialchars($lead['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email *</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlspecialchars($lead['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phone *</label>
                        <input type="tel" class="form-control" name="phone" 
                               value="<?= htmlspecialchars($lead['phone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Company</label>
                        <input type="text" class="form-control" name="company" 
                               value="<?= htmlspecialchars($lead['company'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status['status_name'] ?>" 
                                    <?= ($lead['status'] === $status['status_name']) ? 'selected' : '' ?>>
                                    <?= ucfirst($status['status_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Source</label>
                        <select class="form-select" name="source_id">
                            <option value="">-- Select Source --</option>
                            <?php foreach ($sources as $source): ?>
                                <option value="<?= $source['id'] ?>" 
                                    <?= ($lead['source_id'] == $source['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($source['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Assigned To</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($assignees as $assignee): ?>
                                <option value="<?= $assignee['id'] ?>" 
                                    <?= ($lead['assigned_to'] == $assignee['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($assignee['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Message/Requirements</label>
                        <textarea class="form-control" name="message" rows="4"><?= htmlspecialchars($lead['message'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
