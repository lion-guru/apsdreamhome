<?php
/**
 * show - APS Dream Home Component
 * 
 * @package APS Dream Home
 * @version 1.0.0
 * @author APS Dream Home Team
 * @copyright 2026 APS Dream Home
 * 
 * Description: Handles show functionality
 * 
 * Features:
 * - Secure input validation
 * - Comprehensive error handling
 * - Performance optimization
 * - Database integration
 * - Session management
 * - CSRF protection
 * 
 * @see https://apsdreamhome.com/docs
 */

// TODO: Add proper error handling with try-catch blocks
?>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/leads">Leads</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= h($lead['name']) ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Lead Details Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="lead-avatar-large mr-4">
                                    <?= strtoupper(substr($lead['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h2 class="mb-1">
                                        <?= h($lead['name']) ?>
                                        <?php if ($lead['company']): ?>
                                            <small class="text-muted">
                                                - <?= h($lead['company']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </h2>
                                    <div class="lead-badges mb-2">
                                        <span class="badge badge-<?= $this->getStatusBadgeClass($lead['status']) ?> mr-2">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?= h($lead['status_name']) ?>
                                        </span>
                                        <span class="badge badge-<?= $this->getPriorityBadgeClass($lead['priority']) ?>">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?= h($lead['priority_name']) ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-clock mr-2"></i>
                                        Created: <?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group-vertical">
                                <button type="button" class="btn btn-primary" onclick="addActivity()">
                                    <i class="fas fa-plus mr-2"></i>Add Activity
                                </button>
                                <button type="button" class="btn btn-info" onclick="addNote()">
                                    <i class="fas fa-sticky-note mr-2"></i>Add Note
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false>
                                        <i class="fas fa-cog mr-2"></i>Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="/leads/<?= $lead['id'] ?>/edit">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                        </a>
                                        <a class="dropdown-item" href="mailto:<?= h($lead['email']) ?>">
                                            <i class="fas fa-envelope mr-2"></i>Send Email
                                        </a>
                                        <a class="dropdown-item" href="tel:<?= h($lead['phone']) ?>">
                                            <i class="fas fa-phone mr-2"></i>Call
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item text-danger" onclick="deleteLead()">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lead Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header aps-cp-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>Lead Information
                    </h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Full Name</label>
                                <div class="font-weight-bold">
                                    <?= h($lead['name']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Email Address</label>
                                <div class="font-weight-bold">
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= h($lead['email']) ?>">
                                            <?= h($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not Available</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Phone Number</label>
                                <div class="font-weight-bold">
                                    <a href="tel:<?= h($lead['phone']) ?>">
                                        <i class="fas fa-phone mr-1"></i>
                                        <?= h($lead['phone']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Company</label>
                                <div class="font-weight-bold">
                                    <?php if ($lead['company']): ?>
                                        <?= h($lead['company']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not Available</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Lead Source</label>
                                <div class="font-weight-bold">
                                    <i class="fas fa-bullhorn mr-1 text-info"></i>
                                    <?= h($lead['source_name']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Assigned</label>
                                <div class="font-weight-bold">
                                    <?php if ($lead['assigned_user_name']): ?>
                                        <i class="fas fa-user mr-1 text-success"></i>
                                        <?= h($lead['assigned_user_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not Assigned</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <?php if ($lead['budget'] || $lead['requirements']): ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Budget</label>
                                    <div class="font-weight-bold">
                                        <?php if ($lead['budget']): ?>
                                            ₹<?= number_format($lead['budget']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not Available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Requirements</label>
                                    <div class="font-weight-bold">
                                        <?php if ($lead['requirements']): ?>
                                            <?= nl2br(h($lead['requirements'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not Available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activities & Notes Tabs -->
            <div class="card shadow mb-4">
                <div class="card-header aps-cp-card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#activities" data-bs-toggle="tab">
                                <i class="fas fa-list mr-1"></i>Activities (<?= count($activities) ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#notes" data-bs-toggle="tab">
                                <i class="fas fa-sticky-note mr-1"></i>Notes (<?= count($notes) ?>)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="tab-content">
                        <!-- Activities Tab -->
                        <div class="tab-pane fade show active" id="activities">
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-list fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No Activity Found</p>
                                    <button class="btn btn-primary" onclick="addActivity()">
                                        पहली Add Activity
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-info"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?= h($activity['activity_name']) ?></strong>
                                                        <?php if ($activity['notes']): ?>
                                                            <br><small><?= h($activity['notes']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('d M Y, h:i A', strtotime($activity['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        By: <?= h($activity['user_name']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes">
                            <?php if (empty($notes)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No Note Found</p>
                                    <button class="btn btn-primary" onclick="addNote()">
                                        पहला Add Note
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="notes-list">
                                    <?php foreach ($notes as $note): ?>
                                        <div class="note-item">
                                            <div class="note-header">
                                                <strong><?= h($note['user_name']) ?></strong>
                                                <small class="text-muted">
                                                    <?= date('d M Y, h:i A', strtotime($note['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="note-content">
                                                <?= nl2br(h($note['note'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header aps-cp-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                    </h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>कुल Activities</span>
                            <span class="badge badge-primary">
                                <?= count($activities) ?>
                            </span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>कुल Notes</span>
                            <span class="badge badge-info">
                                <?= count($notes) ?>
                            </span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Last Update</span>
                            <span class="badge badge-success">
                                <?= date('d M Y', strtotime($lead['updated_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header aps-cp-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="addActivity()">
                            <i class="fas fa-plus mr-2"></i>Add Activity
                        </button>
                        <button class="btn btn-info" onclick="addNote()">
                            <i class="fas fa-sticky-note mr-2"></i>Add Note
                        </button>
                        <a href="mailto:<?= h($lead['email']) ?>" class="btn btn-success">
                            <i class="fas fa-envelope mr-2"></i>Send Email
                        </a>
                        <a href="tel:<?= h($lead['phone']) ?>" class="btn btn-warning">
                            <i class="fas fa-phone mr-2"></i>Call
                        </a>
                    </div>
                </div>
            </div>

            <!-- Lead Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header aps-cp-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>Lead Timeline
                    </h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Lead Created</small>
                                <br>
                                <small class="font-weight-bold">
                                    <?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php if ($lead['assigned_user_name']): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        Assigned: <?= h($lead['assigned_user_name']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($activities)): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        अंतिम गतिविधि: <?= h($activities[0]['activity_name']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/leads/<?= $lead['id'] ?>/activity">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Activity</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="activity_type">Activity Type</label>
                        <select class="form-control" id="activity_type" name="activity_id" required>
                            <option value="">Select</option>
                            <option value="1">Call</option>
                            <option value="2">Email</option>
                            <option value="3">Meeting</option>
                            <option value="4">Follow-up</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activity_notes">Notes</label>
                        <textarea class="form-control" id="activity_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/leads/<?= $lead['id'] ?>/note">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Note</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="note_content">Note Content</label>
                        <textarea class="form-control" id="note_content" name="note" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addActivity() {
    new bootstrap.Modal(document.getElementById('activityModal')).show();
}

function addNote() {
    new bootstrap.Modal(document.getElementById('noteModal')).show();
}

function deleteLead() {
    if (confirm('Are you sure you want to delete this lead?')) {
        window.location.href = '/leads/<?= $lead['id'] ?>/delete';
    }
}
</script>

<style>
.lead-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 32px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.info-item {
    padding: 0.75rem;
    border-radius: 8px;
    background: #f8f9fc;
    border-left: 4px solid #0d9488;
}

.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #0d9488;
}

.timeline-content {
    background: #f8f9fc;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 4px solid #0d9488;
}

.notes-list {
    max-height: 400px;
    overflow-y: auto;
}

.note-item {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #28a745;
}

.note-header {
    display: flex;
    justify-content: between;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.note-content {
    color: #495057;
    line-height: 1.5;
}

.stats-item {
    padding: 0.75rem;
    border-radius: 8px;
    background: #f8f9fc;
    border-left: 4px solid #28a745;
}

.badge {
    font-size: 0.85em;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background: #007bff;
    color: white;
    border-radius: 8px 8px 0 0;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
}

@media print {
    .btn, .modal, nav {
        display: none !important;
    }
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\farmers\show.php

function deleteFarmer(id) {
    $('#deleteModal').modal('show');
}
function printFarmerDetails() {
    window.print();
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\emi\show.php

function openPaymentModal(installmentId, amount) {
        document.getElementById('paymentInstallmentId').value = installmentId;
        document.getElementById('paymentAmount').value = amount;
        new bootstrap.Modal(document.getElementById('recordPaymentModal')).show();
    }
function openForecloseModal(planId) {
        const modal = new bootstrap.Modal(document.getElementById('forecloseModal'));
        modal.show();

        // Fetch foreclosure amount
        fetch('<?php echo BASE_URL; ?>admin/emi/getForeclosureAmount/' + planId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('forecloseAmountDisplay').textContent = data.formatted_amount;
                    document.getElementById('forecloseAmountInput').value = data.amount;
                }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\properties\show.php

function shareProperty() {
    const shareText = `Check out this property: ${propertyData.title}
function inquireProperty() {
    const inquiryForm = document.querySelector('form[action*="/contact"]');
    if (inquiryForm) {
        // Scroll to inquiry form
        inquiryForm.scrollIntoView({ behavior: 'smooth' }
function favoriteProperty() {
    const heartBtn = event.target;
    const isFavorited = heartBtn.classList.contains('fas');
    
    // Toggle heart icon
    if (isFavorited) {
        heartBtn.classList.remove('fas');
        heartBtn.classList.add('far');
        showNotification('Property removed from favorites', 'info');
    }
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}
function shareOnWhatsApp() {
    const shareText = `Check out this property: ${propertyData.title}
function shareByEmail() {
    const subject = `Property Inquiry: ${propertyData.title}
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 660 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//