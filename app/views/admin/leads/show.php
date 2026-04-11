<?php
$page_title = $page_title ?? 'Lead Details';
$lead = $lead ?? [];
$activities = $activities ?? [];
$notes = $notes ?? [];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Lead Details</h1>
            <p class="text-muted mb-0">View and manage lead information</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Leads
            </a>
            <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Lead
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

    <div class="row">
        <!-- Lead Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Lead Information</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-3"><?= htmlspecialchars($lead['name'] ?? 'N/A') ?></h4>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong><?= htmlspecialchars($lead['email'] ?? 'N/A') ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong><?= htmlspecialchars($lead['phone'] ?? 'N/A') ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Company</small>
                        <strong><?= htmlspecialchars($lead['company'] ?? 'N/A') ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-<?= ($lead['status'] === 'new') ? 'danger' : (($lead['status'] === 'contacted') ? 'info' : (($lead['status'] === 'qualified') ? 'success' : 'secondary')) ?>">
                            <?= ucfirst($lead['status'] ?? 'Unknown') ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Source</small>
                        <strong><?= htmlspecialchars($lead['source_name'] ?? $lead['source'] ?? 'N/A') ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Assigned To</small>
                        <strong><?= htmlspecialchars($lead['assigned_to_name'] ?? 'Unassigned') ?></strong>
                    </div>
                    
                    <div class="mb-0">
                        <small class="text-muted d-block">Created</small>
                        <strong><?= isset($lead['created_at']) ? date('d M Y H:i', strtotime($lead['created_at'])) : 'N/A' ?></strong>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $lead['phone'] ?? '') ?>" target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </a>
                        <a href="mailto:<?= $lead['email'] ?? '' ?>" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Send Email
                        </a>
                        <button class="btn btn-outline-primary" onclick="addNote()">
                            <i class="fas fa-sticky-note me-2"></i>Add Note
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Activity Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Activity Timeline</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($activities)): ?>
                        <div class="timeline">
                            <?php foreach ($activities as $activity): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-<?= ($activity['activity_type'] === 'call') ? 'primary' : (($activity['activity_type'] === 'email') ? 'info' : (($activity['activity_type'] === 'meeting') ? 'success' : 'secondary')) ?>">
                                            <i class="fas fa-<?= ($activity['activity_type'] === 'call') ? 'phone' : (($activity['activity_type'] === 'email') ? 'envelope' : (($activity['activity_type'] === 'meeting') ? 'handshake' : 'circle')) ?>"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= ucfirst($activity['activity_type'] ?? 'Activity') ?></h6>
                                        <p class="mb-1"><?= htmlspecialchars($activity['description'] ?? '') ?></p>
                                        <small class="text-muted">
                                            by <?= htmlspecialchars($activity['created_by_name'] ?? 'System') ?> 
                                            on <?= isset($activity['created_at']) ? date('d M Y H:i', strtotime($activity['created_at'])) : '' ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No activities recorded yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                    <button class="btn btn-sm btn-primary" onclick="addNote()">
                        <i class="fas fa-plus"></i> Add Note
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($notes)): ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <p class="mb-1"><?= nl2br(htmlspecialchars($note['note'] ?? '')) ?></p>
                                <small class="text-muted">
                                    by <?= htmlspecialchars($note['created_by_name'] ?? 'System') ?> 
                                    on <?= isset($note['created_at']) ? date('d M Y H:i', strtotime($note['created_at'])) : '' ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No notes added yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/note">
                <div class="modal-body">
                    <textarea class="form-control" name="note" rows="4" placeholder="Enter your note..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addNote() {
    new bootstrap.Modal(document.getElementById('noteModal')).show();
}
</script>
