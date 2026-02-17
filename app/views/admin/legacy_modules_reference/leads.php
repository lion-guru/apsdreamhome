<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';
require_once __DIR__ . '/../includes/audit_log.php';

$success = $_SESSION['success_msg'] ?? '';
$error = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
// Handle form submission for new lead
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_lead') {
    // CSRF token verification
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $property_interest = trim($_POST['property_interest'] ?? '');
        $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;

        if (empty($name)) {
            $error = "Lead name is required.";
        } else {
            try {
                $db = \App\Core\App::database();
                $params = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'source' => $source,
                    'status' => $status,
                    'notes' => $notes,
                    'budget' => $budget,
                    'property_interest' => $property_interest,
                    'assigned_to' => $assigned_to
                ];

                if ($db->execute("INSERT INTO leads (name, email, phone, address, source, status, notes, budget, property_interest, assigned_to) VALUES (:name, :email, :phone, :address, :source, :status, :notes, :budget, :property_interest, :assigned_to)", $params)) {
                    $lead_id = $db->getLastInsertId();
                    require_once __DIR__ . '/../includes/functions/lead_functions.php';
                    logLeadActivity($lead_id, 'lead_created', "Lead $name was created", null, null, 'New Lead Created');
                    log_admin_activity('add_lead', 'Added lead: ' . $name);
                    logAudit('lead', $lead_id, 'create', 'Created new lead: ' . $name);

                    // Add notification if assigned to someone
                    if ($assigned_to) {
                        require_once __DIR__ . '/../includes/notification_manager.php';
                        $nm = new NotificationManager($db->getConnection());
                        $nm->createTemplatedNotification($assigned_to, 'LEAD_ASSIGNED', [
                            'name' => $name,
                            'source' => $source
                        ]);
                    }

                    $success = "Lead added successfully!";
                } else {
                    $error = "Error adding lead.";
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Handle lead deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_lead') {
    // CSRF token verification
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $lead_id = intval($_POST['lead_id'] ?? 0);

        if ($lead_id <= 0) {
            $error = "Invalid lead ID.";
        } else {
            try {
                $db = \App\Core\App::database();
                // Get lead details for logging
                $lead_data = $db->fetchOne("SELECT name FROM leads WHERE id = :id", ['id' => $lead_id]);

                if (!$lead_data) {
                    $error = "Lead not found.";
                } else {
                    $lead_name = $lead_data['name'];

                    // Delete lead
                    if ($db->execute("DELETE FROM leads WHERE id = :id", ['id' => $lead_id])) {
                        log_admin_activity('delete_lead', 'Deleted lead: ' . $lead_name);
                        logAudit('lead', $lead_id, 'delete', 'Deleted lead: ' . $lead_name);
                        $success = "Lead deleted successfully!";
                    } else {
                        $error = "Failed to delete lead.";
                    }
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch leads
try {
    $db = \App\Core\App::database();
    $leads = $db->fetchAll("SELECT l.*, a.auser as assigned_admin_name FROM leads l LEFT JOIN admin a ON l.assigned_to = a.id ORDER BY l.id DESC");
} catch (Exception $e) {
    $error = "Error fetching leads: " . $e->getMessage();
    $leads = [];
}

// Generate CSRF token for the standardized header
getCsrfToken();

$page_title = "Leads Management";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Leads Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Leads</li>
                    </ul>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <span class="badge bg-primary me-2"><?php echo count($leads); ?> Leads</span>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-primary active" id="list-view-btn"><i class="fas fa-list"></i> List</button>
                        <button type="button" class="btn btn-outline-primary" id="kanban-view-btn"><i class="fas fa-th-large"></i> Kanban</button>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                        <i class="fas fa-plus me-2"></i>Add Lead
                    </button>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo h($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo h($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div id="list-view">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="leadsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leads)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Leads Found</h5>
                                            <p class="text-muted">No leads have been added yet.</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                                                <i class="fas fa-plus me-2"></i>Add First Lead
                                            </button>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($leads as $lead): ?>
                                        <tr>
                                            <td><?php echo h($lead['id']); ?></td>
                                            <td>
                                                <h6 class="mb-0"><?php echo h($lead['name']); ?></h6>
                                                <?php if (!empty($lead['assigned_admin_name'])): ?>
                                                    <small class="text-muted"><i class="fas fa-user-check me-1"></i> Assigned to: <?php echo h($lead['assigned_admin_name']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($lead['email'])): ?>
                                                    <a href="mailto:<?php echo h($lead['email']); ?>" class="text-decoration-none">
                                                        <?php echo h($lead['email']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($lead['phone'])): ?>
                                                    <a href="tel:<?php echo h($lead['phone']); ?>" class="text-decoration-none">
                                                        <?php echo h($lead['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($lead['source'])): ?>
                                                    <span class="badge bg-info"><?php echo h($lead['source']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $lead['status'] ?? 'New';
                                                $status_class = 'bg-secondary';

                                                switch ($status) {
                                                    case 'New':
                                                        $status_class = 'bg-primary';
                                                        break;
                                                    case 'Contacted':
                                                        $status_class = 'bg-info';
                                                        break;
                                                    case 'Qualified':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'Lost':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    case 'Converted':
                                                        $status_class = 'bg-warning';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo h($status_class); ?>">
                                                    <?php echo h($status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info ai-followup-btn"
                                                        data-id="<?php echo h($lead['id']); ?>" title="AI Follow-up">
                                                        <i class="fas fa-robot"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="editLead(<?php echo h($lead['id']); ?>)" title="Edit Lead">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info"
                                                        onclick="viewLead(<?php echo h($lead['id']); ?>)" title="View Timeline">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteLead(<?php echo h($lead['id']); ?>, '<?php echo h(addslashes($lead['name'])); ?>')"
                                                        title="Delete Lead">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="kanban-view" style="display: none;">
            <div class="row kanban-wrapper mx-0" style="overflow-x: auto; white-space: nowrap; display: flex; padding-bottom: 20px;">
                <?php
                $kanban_statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Converted'];
                foreach ($kanban_statuses as $kstatus):
                    $status_class = 'bg-secondary';
                    switch ($kstatus) {
                        case 'New':
                            $status_class = 'bg-primary';
                            break;
                        case 'Contacted':
                            $status_class = 'bg-info';
                            break;
                        case 'Qualified':
                            $status_class = 'bg-success';
                            break;
                        case 'Lost':
                            $status_class = 'bg-danger';
                            break;
                        case 'Converted':
                            $status_class = 'bg-warning';
                            break;
                    }
                ?>
                    <div class="kanban-column px-2" style="min-width: 300px; max-width: 300px;">
                        <div class="bg-light rounded-3 p-3 h-100 shadow-sm border-top border-4 <?php echo h(str_replace('bg-', 'border-', $status_class)); ?>">
                            <h5 class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="badge <?php echo h($status_class); ?>"><?php echo h($kstatus); ?></span>
                                <span class="badge rounded-pill bg-dark">
                                    <?php echo count(array_filter($leads, function ($l) use ($kstatus) {
                                        return ($l['status'] ?? 'New') == $kstatus;
                                    })); ?>
                                </span>
                            </h5>
                            <div class="kanban-items" style="min-height: 400px;">
                                <?php foreach ($leads as $lead): if (($lead['status'] ?? 'New') != $kstatus) continue; ?>
                                    <div class="card mb-2 shadow-sm border-0 kanban-card" onclick="viewLead(<?php echo h($lead['id']); ?>)" style="cursor: pointer; border-left: 4px solid <?php
                                                                                                                                                                                            if ($status_class == 'bg-primary') echo '#0d6efd';
                                                                                                                                                                                            elseif ($status_class == 'bg-info') echo '#0dcaf0';
                                                                                                                                                                                            elseif ($status_class == 'bg-success') echo '#198754';
                                                                                                                                                                                            elseif ($status_class == 'bg-danger') echo '#dc3545';
                                                                                                                                                                                            elseif ($status_class == 'bg-warning') echo '#ffc107';
                                                                                                                                                                                            else echo '#6c757d';
                                                                                                                                                                                            ?>;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <h6 class="mb-0 fw-bold"><?php echo h($lead['name']); ?></h6>
                                                <div class="dropdown">
                                                    <button class="btn btn-link p-0 text-muted" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editLead(<?php echo h($lead['id']); ?>)"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteLead(<?php echo h($lead['id']); ?>, '<?php echo h(addslashes($lead['name'])); ?>')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <?php if (!empty($lead['phone'])): ?>
                                                <div class="small text-muted mb-1"><i class="fas fa-phone-alt me-2"></i><?php echo h($lead['phone']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($lead['source'])): ?>
                                                <div class="small text-muted mb-2"><i class="fas fa-bullhorn me-2"></i><?php echo h($lead['source']); ?></div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="small text-muted"><i class="fas fa-clock me-1"></i><?php echo h(date('d M', strtotime($lead['created_at'] ?? 'now'))); ?></span>
                                                <?php if (!empty($lead['assigned_admin_name'])): ?>
                                                    <div class="avatar avatar-xs" title="Assigned to: <?php echo h($lead['assigned_admin_name']); ?>">
                                                        <span class="avatar-title rounded-circle bg-secondary small"><?php echo h(substr($lead['assigned_admin_name'], 0, 1)); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Edit Lead Modal -->
    <div class="modal fade" id="editLeadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="editLeadModalContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Add Lead Modal -->
    <div class="modal fade" id="addLeadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_lead">
                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Lead</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                                    <label for="name">Name *</label>
                                    <div class="invalid-feedback">Please provide a lead name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone">
                                    <label for="phone">Phone</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="source" name="source">
                                        <option value="Website">Website</option>
                                        <option value="Referral">Referral</option>
                                        <option value="Advertisement">Advertisement</option>
                                        <option value="Social Media">Social Media</option>
                                        <option value="Direct" selected>Direct</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <label for="source">Source</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="status" name="status">
                                        <option value="New" selected>New</option>
                                        <option value="Contacted">Contacted</option>
                                        <option value="Qualified">Qualified</option>
                                        <option value="Lost">Lost</option>
                                        <option value="Converted">Converted</option>
                                    </select>
                                    <label for="status">Status</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="assigned_to" name="assigned_to">
                                        <option value="">Not Assigned</option>
                                        <?php
                                        $sql_admin = "SELECT id, auser as name FROM admin ORDER BY auser";
                                        $admins = $db->fetchAll($sql_admin);
                                        foreach ($admins as $row) {
                                            echo "<option value='" . h($row['id']) . "'>" . h($row['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <label for="assigned_to">Assigned To</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="budget" name="budget" placeholder="Budget">
                                    <label for="budget">Budget (Estimated)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="property_interest" name="property_interest" placeholder="Property Interest">
                                    <label for="property_interest">Property Interest</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Address">
                                    <label for="address">Address</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="notes" name="notes" placeholder="Notes" style="height: 100px;"></textarea>
                                    <label for="notes">Notes</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Save Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-gradient-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Lead</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="deleteForm">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="delete_lead">
                    <input type="hidden" name="lead_id" id="delete_lead_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="delete_lead_name"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- AI Follow-up Modal -->
    <div class="modal fade" id="aiFollowupModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fas fa-robot mr-2"></i> AI Follow-up Generator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="ai-loading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-info mb-3" role="status"></div>
                        <p class="text-muted">Gemini is drafting a personalized message...</p>
                    </div>
                    <div id="ai-result" style="display: none;">
                        <label class="font-weight-bold mb-2">Personalized Message:</label>
                        <textarea id="ai-followup-text" class="form-control" rows="8" readonly></textarea>
                        <div class="mt-3 d-flex justify-content-between">
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard()">
                                <i class="fas fa-copy mr-1"></i> Copy to Clipboard
                            </button>
                            <a href="#" id="whatsapp-link" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp mr-1"></i> Send via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Timeline Modal -->
    <div class="modal fade" id="leadTimelineModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-history mr-2"></i> Lead Activity Timeline: <span id="timeline-lead-name"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="ai-timeline-summary" class="alert alert-info border-0 shadow-sm mb-4" style="display: none;">
                        <h6><i class="fas fa-robot mr-1"></i> AI Journey Insights:</h6>
                        <p class="mb-0 small" id="ai-summary-text"></p>
                    </div>

                    <div class="timeline-container" id="timeline-content">
                        <!-- Timeline items will be injected here -->
                    </div>

                    <div id="timeline-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Fetching lead history...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline-container {
            position: relative;
            padding-left: 30px;
            border-left: 2px solid #e9ecef;
            margin-left: 10px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -36px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4e73df;
            border: 2px solid #fff;
        }

        .timeline-date {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: bold;
        }

        .timeline-desc {
            font-size: 0.85rem;
            margin-top: 3px;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }
    </style>

    <script>
        const csrfToken = '<?= getCsrfToken() ?>';

        function deleteLead(id, name) {
            document.getElementById('delete_lead_id').value = id;
            document.getElementById('delete_lead_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function editLead(id) {
            const modalContent = document.getElementById('editLeadModalContent');
            modalContent.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading lead details...</p></div>';

            const modal = new bootstrap.Modal(document.getElementById('editLeadModal'));
            modal.show();

            fetch(`edit_lead.php?id=${id}`, {
                    headers: {
                        'X-CSRF-Token': csrfToken
                    }
                })
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                })
                .catch(error => {
                    modalContent.innerHTML = `<div class="modal-body text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Error loading lead: ${error.message}</p></div>`;
                });
        }

        // Handle AJAX form submission for the edit modal
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.closest('#editLeadModal') && e.target.tagName === 'FORM') {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': csrfToken
                        },
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        window.location.reload();
                    })
                    .catch(error => {
                        alert('Error updating lead: ' + error.message);
                    });
            }
        });

        // View toggle functionality
        document.getElementById('list-view-btn')?.addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('kanban-view-btn').classList.remove('active');
            document.getElementById('list-view').style.display = 'block';
            document.getElementById('kanban-view').style.display = 'none';
        });

        document.getElementById('kanban-view-btn')?.addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('list-view-btn').classList.remove('active');
            document.getElementById('list-view').style.display = 'none';
            document.getElementById('kanban-view').style.display = 'block';
        });

        // AI Follow-up logic
        document.querySelectorAll('.ai-followup-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const leadId = this.getAttribute('data-id');
                const modal = new bootstrap.Modal(document.getElementById('aiFollowupModal'));
                modal.show();
                document.getElementById('ai-loading').style.display = 'block';
                document.getElementById('ai-result').style.display = 'none';

                fetch('ajax/generate-followup.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-Token': csrfToken
                        },
                        body: 'lead_id=' + leadId
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('ai-loading').style.display = 'none';
                        if (data.success) {
                            document.getElementById('ai-followup-text').value = data.message;
                            document.getElementById('whatsapp-link').href = 'https://wa.me/' + data.phone + '?text=' + encodeURIComponent(data.message);
                            document.getElementById('ai-result').style.display = 'block';
                        } else {
                            alert(data.error || 'Failed to generate follow-up.');
                            modal.hide();
                        }
                    })
                    .catch(error => {
                        document.getElementById('ai-loading').style.display = 'none';
                        alert('Connection error with AI service.');
                        modal.hide();
                    });
            });
        });

        function copyToClipboard() {
            const copyText = document.getElementById("ai-followup-text");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert('Message copied to clipboard!');
        }

        function viewLead(leadId) {
            const modal = new bootstrap.Modal(document.getElementById('leadTimelineModal'));
            modal.show();

            const content = document.getElementById('timeline-content');
            const loading = document.getElementById('timeline-loading');
            const aiSection = document.getElementById('ai-timeline-summary');
            const aiText = document.getElementById('ai-summary-text');
            const leadNameSpan = document.getElementById('timeline-lead-name');

            content.innerHTML = '';
            loading.style.display = 'block';
            aiSection.style.display = 'none';

            fetch('ajax/get-lead-timeline.php?lead_id=' + leadId, {
                    headers: {
                        'X-CSRF-Token': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    if (data.success) {
                        leadNameSpan.textContent = data.lead_name;

                        if (data.ai_summary) {
                            aiText.textContent = data.ai_summary;
                            aiSection.style.display = 'block';
                        }

                        if (data.timeline && data.timeline.length > 0) {
                            data.timeline.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'timeline-item';
                                div.innerHTML = `
                         <div class="timeline-date">${item.activity_date ? new Date(item.activity_date).toLocaleString() : new Date(item.created_at).toLocaleString()}</div>
                         <div class="timeline-desc">
                            ${item.subject ? `<div class="fw-bold text-primary mb-1">${item.subject}</div>` : ''}
                            <strong>${item.activity_type.replace(/_/g, ' ').toUpperCase()}:</strong> ${item.description}
                            ${item.old_value || item.new_value ? `<div class="small mt-1 text-muted">
                                ${item.old_value ? `<span class="badge bg-light text-dark border">${item.old_value}</span> <i class="fas fa-long-arrow-alt-right mx-1"></i>` : ''}
                                <span class="badge bg-light text-dark border">${item.new_value || 'N/A'}</span>
                            </div>` : ''}
                            ${item.admin_name ? `<br><small class="text-muted">By: ${item.admin_name}</small>` : ''}
                        </div>
                     `;
                                content.appendChild(div);
                            });
                        } else {
                            content.innerHTML = '<p class="text-center text-muted py-3">No activity recorded yet.</p>';
                        }
                    } else {
                        alert(data.error || 'Failed to fetch timeline.');
                        modal.hide();
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    alert('Connection error.');
                    modal.hide();
                });
        }

        // Bootstrap form validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

    <?php include 'admin_footer.php'; ?>