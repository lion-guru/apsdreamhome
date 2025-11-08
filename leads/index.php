<?php
/**
 * Leads Management System
 * 
 * Main interface for managing leads with a modern, responsive design
 */

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = 'Lead Management';
$activeNav = 'leads';

// Include header and database connection
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/db_config.php';

// Get user information
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// Check for success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!-- Success/Error Alerts -->
<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="d-flex justify-content-between align-items-center px-3 mb-3">
                    <h5>Leads</h5>
                    <button class="btn btn-sm btn-primary" id="addLeadBtn">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="/leads">
                            <i class="bi bi-grid-1x2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="newLeadBtn">
                            <i class="bi bi-plus-circle me-2"></i> New Lead
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#leadsSubmenu">
                            <i class="bi bi-people me-2"></i> Leads
                            <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <div class="collapse show" id="leadsSubmenu">
                            <ul class="nav flex-column ms-4">
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="all">
                                        <i class="bi bi-list-ul me-2"></i> All Leads
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="assigned">
                                        <i class="bi bi-person-check me-2"></i> Assigned to Me
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="new">
                                        <i class="bi bi-star me-2"></i> New
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="contacted">
                                        <i class="bi bi-telephone me-2"></i> Contacted
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="qualified">
                                        <i class="bi bi-check-circle me-2"></i> Qualified
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="lost">
                                        <i class="bi bi-x-circle me-2"></i> Lost
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#reportsSubmenu">
                            <i class="bi bi-graph-up me-2"></i> Reports
                            <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="reportsSubmenu">
                            <ul class="nav flex-column ms-4">
                                <li class="nav-item">
                                    <a class="nav-link" href="#" id="conversionReportBtn">
                                        <i class="bi bi-arrow-left-right me-2"></i> Conversion
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" id="sourceReportBtn">
                                        <i class="bi bi-tag me-2"></i> By Source
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" id="activityReportBtn">
                                        <i class="bi bi-activity me-2"></i> Activity
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item mt-3">
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Quick Filters</span>
                        </h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="today">
                            <i class="bi bi-calendar-day me-2"></i> Today's Follow-ups
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="overdue">
                            <i class="bi bi-exclamation-triangle me-2"></i> Overdue
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="high">
                            <i class="bi bi-arrow-up-circle me-2"></i> High Priority
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h3 mb-0">Lead Dashboard</h1>
                    <p class="text-muted mb-0">Manage and track your leads</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Quick Filters</h6></li>
                            <li><a class="dropdown-item" href="#" data-filter="all">All Leads</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="assigned">Assigned to Me</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="new">New</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="today">Due Today</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="overdue">Overdue</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="exportBtn"><i class="bi bi-download me-2"></i>Export</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4 g-3">
                <div class="col-6 col-md-3">
                    <div class="card border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Total Leads</h6>
                                    <h4 class="mb-0" id="totalLeads">0</h4>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-3">
                                        <i class="bi bi-people fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">New This Month</h6>
                                    <h4 class="mb-0" id="newLeads">0</h4>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-success bg-opacity-10 text-success rounded-3">
                                        <i class="bi bi-plus-circle fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-start border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">In Progress</h6>
                                    <h4 class="mb-0" id="inProgressLeads">0</h4>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-warning bg-opacity-10 text-warning rounded-3">
                                        <i class="bi bi-arrow-repeat fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-start border-danger border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">At Risk</h6>
                                    <h4 class="mb-0" id="atRiskLeads">0</h4>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-danger bg-opacity-10 text-danger rounded-3">
                                        <i class="bi bi-exclamation-triangle fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="card mb-4">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <div class="search-box">
                                <input type="text" class="form-control" id="searchQuery" placeholder="Search leads by name, email, or phone...">
                                <i class="bi bi-search search-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <select class="form-select me-2" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="new">New</option>
                                    <option value="contacted">Contacted</option>
                                    <option value="qualified">Qualified</option>
                                    <option value="proposal">Proposal Sent</option>
                                    <option value="negotiation">Negotiation</option>
                                    <option value="closed_won">Closed Won</option>
                                    <option value="closed_lost">Closed Lost</option>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" id="filterBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-funnel"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3" style="width: 300px;">
                                    <h6 class="dropdown-header mb-2">Advanced Filters</h6>
                                    <div class="mb-3">
                                        <label class="form-label small">Source</label>
                                        <select class="form-select form-select-sm" id="sourceFilter">
                                            <option value="">All Sources</option>
                                            <option value="website">Website</option>
                                            <option value="referral">Referral</option>
                                            <option value="social">Social Media</option>
                                            <option value="email">Email</option>
                                            <option value="phone">Phone</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Assigned To</label>
                                        <select class="form-select form-select-sm" id="assignedToFilter">
                                            <option value="">Everyone</option>
                                            <option value="me">Me</option>
                                            <option value="unassigned">Unassigned</option>
                                            <!-- Will be populated dynamically -->
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Date Range</label>
                                        <select class="form-select form-select-sm" id="dateRangeFilter">
                                            <option value="all">All Time</option>
                                            <option value="today">Today</option>
                                            <option value="yesterday">Yesterday</option>
                                            <option value="this_week">This Week</option>
                                            <option value="last_week">Last Week</option>
                                            <option value="this_month" selected>This Month</option>
                                            <option value="last_month">Last Month</option>
                                            <option value="custom">Custom Range</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFiltersBtn">
                                            Reset Filters
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary" id="applyFiltersBtn">
                                            Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leads Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="leadsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllLeads">
                                        </div>
                                    </th>
                                    <th>Lead</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Last Contact</th>
                                    <th>Next Action</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leadsTableBody">
                                <!-- Loading State -->
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0 text-muted">Loading leads...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Table Footer -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-top">
                        <div class="text-muted small" id="leadsCount">
                            Showing <span id="startCount">0</span> to <span id="endCount">0</span> of <span id="totalCount">0</span> entries
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Leads pagination">
                            <ul class="pagination pagination-sm mb-0" id="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous" id="prevPage">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Next" id="nextPage">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" aria-labelledby="leadDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadDetailsModalLabel">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Will be loaded dynamically -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveLeadBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Lead Modal -->
<div class="modal fade" id="leadFormModal" tabindex="-1" aria-labelledby="leadFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadFormModalLabel">Add New Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leadForm">
                <div class="modal-body">
                    <input type="hidden" id="leadId" name="id">
                    
                    <ul class="nav nav-tabs mb-4" id="leadFormTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab" aria-controls="basic-info" aria-selected="true">Basic Info</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#lead-details" type="button" role="tab" aria-controls="lead-details" aria-selected="false">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#lead-activity" type="button" role="tab" aria-controls="lead-activity" aria-selected="false">Activity</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="leadFormTabsContent">
                        <!-- Basic Info Tab -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="company" class="form-label">Company</label>
                                    <input type="text" class="form-control" id="company" name="company">
                                </div>
                                <div class="col-md-6">
                                    <label for="jobTitle" class="form-label">Job Title</label>
                                    <input type="text" class="form-control" id="jobTitle" name="job_title">
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address">
                                </div>
                                <div class="col-md-4">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city">
                                </div>
                                <div class="col-md-4">
                                    <label for="state" class="form-label">State/Province</label>
                                    <input type="text" class="form-control" id="state" name="state">
                                </div>
                                <div class="col-md-4">
                                    <label for="postalCode" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postalCode" name="postal_code">
                                </div>
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-select" id="country" name="country">
                                        <option value="">Select Country</option>
                                        <option value="United States">United States</option>
                                        <option value="Canada">Canada</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Australia">Australia</option>
                                        <!-- More countries will be added via JavaScript -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="website" class="form-label">Website</label>
                                    <div class="input-group">
                                        <span class="input-group-text">https://</span>
                                        <input type="text" class="form-control" id="website" name="website" placeholder="example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details Tab -->
                        <div class="tab-pane fade" id="lead-details" role="tabpanel" aria-labelledby="details-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="new">New</option>
                                        <option value="contacted">Contacted</option>
                                        <option value="qualified">Qualified</option>
                                        <option value="proposal">Proposal Sent</option>
                                        <option value="negotiation">Negotiation</option>
                                        <option value="closed_won">Closed Won</option>
                                        <option value="closed_lost">Closed Lost</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="source" class="form-label">Source *</label>
                                    <select class="form-select" id="source" name="source" required>
                                        <option value="website">Website</option>
                                        <option value="referral">Referral</option>
                                        <option value="social">Social Media</option>
                                        <option value="email">Email</option>
                                        <option value="phone">Phone</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assignedTo" class="form-label">Assigned To</label>
                                    <select class="form-select" id="assignedTo" name="assigned_to">
                                        <option value="">Unassigned</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="leadScore" class="form-label">Lead Score</label>
                                    <input type="number" class="form-control" id="leadScore" name="lead_score" min="0" max="100">
                                </div>
                                <div class="col-md-6">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="nextFollowUp" class="form-label">Next Follow-up</label>
                                    <input type="datetime-local" class="form-control" id="nextFollowUp" name="next_follow_up">
                                </div>
                                <div class="col-12">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" placeholder="Add tags separated by commas">
                                    <div class="form-text">Separate tags with commas</div>
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="lead-activity" role="tabpanel" aria-labelledby="activity-tab">
                            <div id="leadActivityContent">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading activity...</p>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">Add New Activity</h6>
                            <form id="addActivityForm">
                                <div class="mb-3">
                                    <label for="activityType" class="form-label">Activity Type</label>
                                    <select class="form-select" id="activityType" name="activity_type" required>
                                        <option value="">Select Activity Type</option>
                                        <option value="call">Phone Call</option>
                                        <option value="email">Email</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="task">Task</option>
                                        <option value="note">Note</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="activitySubject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="activitySubject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="activityDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="activityDescription" name="description" rows="3" required></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="activityDueDate" class="form-label">Due Date</label>
                                        <input type="datetime-local" class="form-control" id="activityDueDate" name="due_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="activityStatus" class="form-label">Status</label>
                                        <select class="form-select" id="activityStatus" name="status">
                                            <option value="not_started">Not Started</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Add Activity</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveLeadBtn">Save Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this lead? This action cannot be undone.</p>
                <p class="fw-bold">This will permanently delete the lead and all associated data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Lead</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusChangeModalLabel">Update Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusChangeForm">
                    <input type="hidden" id="statusLeadId" name="lead_id">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">New Status</label>
                        <select class="form-select" id="newStatus" name="status" required>
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="qualified">Qualified</option>
                            <option value="proposal">Proposal Sent</option>
                            <option value="negotiation">Negotiation</option>
                            <option value="closed_won">Closed Won</option>
                            <option value="closed_lost">Closed Lost</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="3" placeholder="Add any notes about this status change"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveStatusBtn">Update Status</button>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<!-- Custom CSS for this page -->
<style>
/* Lead Status Badges */
.badge-status {
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 500;
    text-transform: capitalize;
}

/* Avatar Styles */
.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    color: #fff;
    text-transform: uppercase;
}

.avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 0.65rem;
}

/* Search Box */
.search-box {
    position: relative;
}

.search-box .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-box input {
    padding-left: 40px;
}

/* Lead Table Styles */
.lead-name {
    font-weight: 500;
    color: #333;
    text-decoration: none;
}

.lead-name:hover {
    color: #0d6efd;
    text-decoration: none;
}

/* Status Badge Colors */
.badge-new {
    background-color: #e9ecef;
    color: #495057;
}

.badge-contacted {
    background-color: #cfe2ff;
    color: #084298;
}

.badge-qualified {
    background-color: #cff4fc;
    color: #055160;
}

.badge-proposal {
    background-color: #fff3cd;
    color: #664d03;
}

.badge-negotiation {
    background-color: #e2e3e5;
    color: #41464b;
}

.badge-closed_won {
    background-color: #d1e7dd;
    color: #0a3622;
}

.badge-closed_lost {
    background-color: #f8d7da;
    color: #58151c;
}

/* Responsive Table */
@media (max-width: 991.98px) {
    .table-responsive {
        border: 0;
    }
    
    .table thead {
        display: none;
    }
    
    .table tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .table td {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        text-align: right;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .table td::before {
        content: attr(data-label);
        font-weight: 600;
        margin-right: 1rem;
        color: #6c757d;
    }
    
    .table td:last-child {
        border-bottom: 0;
    }
    
    .table td .dropdown {
        display: flex;
        justify-content: flex-end;
        width: 100%;
    }
}
</style>

<!-- Custom JavaScript for this page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add lead button
    document.getElementById('addLeadBtn')?.addEventListener('click', function() {
        // Open add lead modal
        const modal = new bootstrap.Modal(document.getElementById('leadFormModal'));
        modal.show();
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchQuery');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Implement search functionality
                console.log('Searching for:', searchInput.value);
                // loadLeads(); // You'll need to implement this function
            }, 500);
        });
    }
    
    // Filter functionality
    const filterButtons = document.querySelectorAll('[data-filter]');
    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            applyFilter(filter);
        });
    });
    
    // Function to apply filters
    function applyFilter(filter) {
        console.log('Applying filter:', filter);
        // Update active state
        document.querySelectorAll('[data-filter]').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
        
        // Apply filter and reload leads
        // loadLeads(); // You'll need to implement this function
    }
    
    // Initialize with default filter
    applyFilter('all');
});

// Format phone number
function formatPhoneNumber(phone) {
    if (!phone) return '';
    // Simple formatting - you can enhance this with a library like libphonenumber-js
    return phone.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
}

// Format date
function formatDate(dateString) {
    if (!dateString) return '';
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleString('en-US', options);
}

// Get status badge HTML
function getStatusBadge(status) {
    const statusMap = {
        'new': { class: 'bg-light text-dark', label: 'New' },
        'contacted': { class: 'bg-primary', label: 'Contacted' },
        'qualified': { class: 'bg-info text-dark', label: 'Qualified' },
        'proposal': { class: 'bg-warning text-dark', label: 'Proposal Sent' },
        'negotiation': { class: 'bg-secondary', label: 'Negotiation' },
        'closed_won': { class: 'bg-success', label: 'Closed Won' },
        'closed_lost': { class: 'bg-danger', label: 'Closed Lost' }
    };
    
    const statusInfo = statusMap[status] || { class: 'bg-light text-dark', label: status };
    return `<span class="badge ${statusInfo.class} badge-status">${statusInfo.label}</span>`;
}

// Get source icon
function getSourceIcon(source) {
    const iconMap = {
        'website': 'globe',
        'email': 'envelope',
        'phone': 'telephone',
        'social': 'share',
        'referral': 'person-plus',
        'other': 'question-circle'
    };
    
    const icon = iconMap[source] || 'question-circle';
    return `<i class="bi bi-${icon} me-1"></i>`;
}
</script>
<?php
// Shared footer (closes body/html and loads common JS)
include_once __DIR__ . '/../includes/footer.php';
?>
