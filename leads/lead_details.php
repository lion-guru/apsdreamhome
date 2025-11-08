<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Verify user has access
if (!in_array($_SESSION['user_role'], ['admin', 'lead_manager', 'sales_agent'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied');
}

$leadId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$leadId) {
    header('Location: dashboard.php');
    exit;
}

try {
    // Get lead details
    $stmt = $conn->prepare("
        SELECT l.*, u.name as assigned_to_name, u.email as agent_email 
        FROM contact_inquiries l 
        LEFT JOIN users u ON l.assigned_to = u.id 
        WHERE l.id = ?
    ");
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $lead = $stmt->get_result()->fetch_assoc();
    
    if (!$lead) {
        throw new Exception('Lead not found');
    }
    
    // Verify access (admin/manager can view all, agents only their own)
    $isOwner = ($_SESSION['user_id'] == $lead['assigned_to']);
    $isManager = in_array($_SESSION['user_role'], ['admin', 'lead_manager']);
    
    if (!$isOwner && !$isManager) {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied');
    }
    
    // Get lead activities
    $activities = $conn->query("
        SELECT la.*, u.name as user_name, u.role as user_role 
        FROM lead_activities la 
        JOIN users u ON la.user_id = u.id 
        WHERE la.lead_id = $leadId 
        ORDER BY la.created_at DESC
    ");
    
    // Get sales team for assignment
    $salesTeam = $conn->query("
        SELECT id, name 
        FROM users 
        WHERE role IN ('sales_agent', 'lead_manager') 
        ORDER BY name
    ");
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Helper function to format activity type
function formatActivityType($type) {
    $types = [
        'call' => 'Phone Call',
        'email' => 'Email',
        'meeting' => 'Meeting',
        'note' => 'Note',
        'status_change' => 'Status Update'
    ];
    return $types[$type] ?? ucfirst($type);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lead #<?= $leadId ?> - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .activity-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
        }
        .activity-call { border-color: #198754; }
        .activity-email { border-color: #0dcaf0; }
        .activity-meeting { border-color: #6f42c1; }
        .activity-note { border-color: #fd7e14; }
        .activity-status_change { border-color: #d63384; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Leads</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lead #<?= $leadId ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Lead Information</h4>
                        <span class="badge bg-<?= getStatusBadge($lead['status']) ?>">
                            <?= ucfirst($lead['status']) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>Contact Details</h5>
                                <p>
                                    <strong>Name:</strong> <?= htmlspecialchars($lead['name']) ?><br>
                                    <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($lead['email']) ?>">
                                        <?= htmlspecialchars($lead['email']) ?>
                                    </a><br>
                                    <strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($lead['phone']) ?>">
                                        <?= htmlspecialchars($lead['phone']) ?>
                                    </a><br>
                                    <strong>Registered:</strong> <?= date('M j, Y g:i A', strtotime($lead['created_at'])) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>Lead Details</h5>
                                <p>
                                    <strong>Source:</strong> <?= htmlspecialchars($lead['source'] ?? 'Website') ?><br>
                                    <strong>Status:</strong> 
                                    <select class="form-select d-inline-block w-auto" id="leadStatus">
                                        <?php 
                                        $statuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
                                        foreach ($statuses as $status): 
                                        ?>
                                            <option value="<?= $status ?>" <?= $lead['status'] === $status ? 'selected' : '' ?>>
                                                <?= ucfirst($status) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <br>
                                    <strong>Assigned To:</strong> 
                                    <select class="form-select d-inline-block w-auto" id="leadAssignment">
                                        <option value="">Unassigned</option>
                                        <?php while ($agent = $salesTeam->fetch_assoc()): ?>
                                            <option value="<?= $agent['id'] ?>" 
                                                <?= $agent['id'] == $lead['assigned_to'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($agent['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5>Message</h5>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($lead['message'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Activity Log</h5>
                    </div>
                    <div class="card-body">
                        <form id="addActivityForm" class="mb-4">
                            <input type="hidden" name="lead_id" value="<?= $leadId ?>">
                            <div class="mb-3">
                                <label class="form-label">Add Activity</label>
                                <select name="activity_type" class="form-select mb-2">
                                    <option value="call">Phone Call</option>
                                    <option value="email">Email</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="note">Note</option>
                                </select>
                                <textarea name="details" class="form-control" rows="3" placeholder="Enter activity details..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Log Activity</button>
                        </form>
                        
                        <div id="activityTimeline">
                            <?php while ($activity = $activities->fetch_assoc()): ?>
                                <div class="card activity-card activity-<?= $activity['activity_type'] ?> mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-title">
                                                <?= formatActivityType($activity['activity_type']) ?>
                                                <?php if ($activity['user_id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-secondary">You</span>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?>
                                            </small>
                                        </div>
                                        <p class="card-text"><?= nl2br(htmlspecialchars($activity['activity_details'])) ?></p>
                                        <small class="text-muted">
                                            — <?= htmlspecialchars($activity['user_name']) ?>
                                            <span class="badge bg-secondary"><?= ucfirst($activity['user_role']) ?></span>
                                        </small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <?php if ($activities->num_rows === 0): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-2"></i>
                                    <p>No activities yet. Log your first interaction with this lead.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" id="sendEmailBtn">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </button>
                            <button class="btn btn-outline-success" id="logCallBtn">
                                <i class="fas fa-phone me-2"></i>Log Call
                            </button>
                            <button class="btn btn-outline-info" id="scheduleMeetingBtn">
                                <i class="fas fa-calendar-alt me-2"></i>Schedule Meeting
                            </button>
                            <hr>
                            <button class="btn btn-success" id="convertToClientBtn">
                                <i class="fas fa-user-plus me-2"></i>Convert to Client
                            </button>
                            <button class="btn btn-danger" id="markAsLostBtn">
                                <i class="fas fa-times-circle me-2"></i>Mark as Lost
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lead Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Lead Score</span>
                                <span class="badge bg-primary rounded-pill">85/100</span>
                            </li>
                            <li class="list-group-item">
                                <small class="text-muted">Last Contact</small><br>
                                <?php 
                                $lastContact = $lead['last_contacted_at'] 
                                    ? date('M j, Y g:i A', strtotime($lead['last_contacted_at'])) 
                                    : 'Never';
                                echo $lastContact;
                                ?>
                            </li>
                            <li class="list-group-item">
                                <small class="text-muted">Created</small><br>
                                <?= date('M j, Y', strtotime($lead['created_at'])) ?>
                            </li>
                            <li class="list-group-item">
                                <small class="text-muted">Source</small><br>
                                <?= ucfirst($lead['source'] ?? 'Website') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle status update
        $('#leadStatus').change(function() {
            const newStatus = $(this).val();
            
            $.post('../api/update_lead_status.php', {
                lead_id: <?= $leadId ?>,
                status: newStatus
            }, function(response) {
                if (response.success) {
                    showToast('Status updated successfully', 'success');
                    // Update the status badge
                    $('.card-header .badge')
                        .removeClass('bg-success bg-warning bg-danger bg-info bg-secondary')
                        .addClass('bg-' + getStatusBadgeClass(newStatus))
                        .text(capitalizeFirstLetter(newStatus));
                } else {
                    showToast('Error updating status', 'error');
                    // Revert the select
                    $('#leadStatus').val('<?= $lead['status'] ?>');
                }
            }, 'json');
        });
        
        // Handle lead assignment
        $('#leadAssignment').change(function() {
            const assignTo = $(this).val();
            
            $.post('../api/assign_lead.php', {
                lead_id: <?= $leadId ?>,
                assigned_to: assignTo || null
            }, function(response) {
                if (response.success) {
                    showToast('Lead assignment updated', 'success');
                } else {
                    showToast('Error updating assignment', 'error');
                    // Revert the select
                    $('#leadAssignment').val('<?= $lead['assigned_to'] ?>');
                }
            }, 'json');
        });
        
        // Handle activity form submission
        $('#addActivityForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            
            $.post('../api/log_activity.php', formData, function(response) {
                if (response.success) {
                    // Reload the page to show the new activity
                    location.reload();
                } else {
                    showToast('Error logging activity: ' + (response.error || 'Unknown error'), 'error');
                }
            }, 'json');
        });
        
        // Quick action buttons
        $('#sendEmailBtn').click(function() {
            window.location.href = `mailto:<?= htmlspecialchars($lead['email']) ?>?subject=Regarding your inquiry #<?= $leadId ?>`;
        });
        
        $('#logCallBtn').click(function() {
            $('select[name="activity_type"]').val('call');
            $('textarea[name="details"]').focus();
        });
        
        $('#scheduleMeetingBtn').click(function() {
            $('select[name="activity_type"]').val('meeting');
            $('textarea[name="details"]')
                .val('Scheduled meeting for [date] at [time]\nLocation: [location]\nAgenda: ')
                .focus();
        });
        
        $('#convertToClientBtn').click(function() {
            if (confirm('Are you sure you want to convert this lead to a client?')) {
                $.post('../api/update_lead_status.php', {
                    lead_id: <?= $leadId ?>,
                    status: 'converted'
                }, function(response) {
                    if (response.success) {
                        showToast('Lead converted to client', 'success');
                        // Redirect to client creation page or show client creation form
                        window.location.href = '../clients/create.php?lead_id=<?= $leadId ?>';
                    } else {
                        showToast('Error converting lead: ' + (response.error || 'Unknown error'), 'error');
                    }
                }, 'json');
            }
        });
        
        $('#markAsLostBtn').click(function() {
            const reason = prompt('Please enter the reason for marking this lead as lost:');
            if (reason !== null) {
                $.post('../api/update_lead_status.php', {
                    lead_id: <?= $leadId ?>,
                    status: 'lost',
                    notes: reason
                }, function(response) {
                    if (response.success) {
                        showToast('Lead marked as lost', 'success');
                        location.reload();
                    } else {
                        showToast('Error updating lead status: ' + (response.error || 'Unknown error'), 'error');
                    }
                }, 'json');
            }
        });
        
        // Helper functions
        function showToast(message, type = 'info') {
            // Implement toast notification
            const toast = $(`<div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : 'success'} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`);
            
            $('body').append(toast);
            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        function getStatusBadgeClass(status) {
            const statusMap = {
                'new': 'primary',
                'contacted': 'info',
                'qualified': 'success',
                'converted': 'success',
                'lost': 'danger'
            };
            return statusMap[status] || 'secondary';
        }
        
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
    </script>
</body>
</html>
