<!-- Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/leads">Leads</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($lead['name']) ?>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Lead Details Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="lead-avatar-large mr-4">
                                <?= strtoupper(substr($lead['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h2 class="mb-1">
                                    <?= htmlspecialchars($lead['name']) ?>
                                    <?php if ($lead['company']): ?>
                                        <small class="text-muted">
                                            - <?= htmlspecialchars($lead['company']) ?>
                                        </small>
                                    <?php endif; ?>
                                </h2>
                                <div class="lead-badges mb-2">
                                    <span class="badge bg-<?= $this->getStatusBadgeClass($lead['status']) ?> mr-2">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?= htmlspecialchars($lead['status_name']) ?>
                                    </span>
                                    <span class="badge bg-<?= $this->getPriorityBadgeClass($lead['priority']) ?>">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?= htmlspecialchars($lead['priority_name']) ?>
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
                            <button type="button" class="btn btn-info mt-2" onclick="addNote()">
                                <i class="fas fa-sticky-note mr-2"></i>Add Note
                            </button>
                            <div class="btn-group mt-2">
                                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog mr-2"></i>Action
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="/admin/leads/<?= $lead['id'] ?>/edit">
                                        <i class="fas fa-edit mr-2"></i>Edit
                                    </a>
                                    <a class="dropdown-item" href="mailto:<?= htmlspecialchars($lead['email']) ?>">
                                        <i class="fas fa-envelope mr-2"></i>Send Email
                                    </a>
                                    <a class="dropdown-item" href="tel:<?= htmlspecialchars($lead['phone']) ?>">
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
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle mr-2"></i>Lead Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Full Name</label>
                        <p class="h5"><?= htmlspecialchars($lead['name']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Phone Number</label>
                        <p class="h5">
                            <a href="tel:<?= htmlspecialchars($lead['phone']) ?>"><?= htmlspecialchars($lead['phone']) ?></a>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Email Address</label>
                        <p class="h5">
                            <a href="mailto:<?= htmlspecialchars($lead['email']) ?>"><?= htmlspecialchars($lead['email'] ?: 'N/A') ?></a>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Company</label>
                        <p class="h5"><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Source</label>
                        <p class="h5"><?= htmlspecialchars($lead['source_name'] ?: 'N/A') ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Assigned To</label>
                        <p class="h5"><?= htmlspecialchars($lead['assigned_to_name'] ?: 'Unassigned') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Preferences -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-building mr-2"></i>Property Preferences
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Property Type</label>
                        <p class="h5"><?= htmlspecialchars($lead['property_type'] ?: 'N/A') ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Budget</label>
                        <p class="h5"><?= $lead['budget'] ? '₹' . number_format($lead['budget']) : 'N/A' ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold">Location</label>
                        <p class="h5"><?= htmlspecialchars($lead['location_preference'] ?: 'N/A') ?></p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small text-uppercase font-weight-bold">Notes</label>
                        <p><?= nl2br(htmlspecialchars($lead['notes'] ?: 'No notes')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities & Notes -->
    <div class="col-lg-4">
        <!-- Activities Timeline -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-2"></i>Activities
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="addActivity()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="timeline p-3">
                    <?php if (empty($activities)): ?>
                        <p class="text-center text-muted my-3">No activity found</p>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <small class="text-primary font-weight-bold">
                                        <?= htmlspecialchars($activity['activity_type_name']) ?>
                                    </small>
                                    <small class="text-muted"><?= date('d M', strtotime($activity['created_at'])) ?></small>
                                </div>
                                <p class="mb-0 small"><?= htmlspecialchars($activity['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .lead-avatar-large {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .timeline-item {
        border-left: 2px solid #e3e6f0;
        padding-left: 15px;
        position: relative;
    }

    .timeline-item::before {
        content: '';
        width: 10px;
        height: 10px;
        background: #667eea;
        border-radius: 50%;
        position: absolute;
        left: -6px;
        top: 5px;
    }
</style>

<script>
    function addActivity() {
        // Implement add activity logic
        alert('Add Activity feature coming soon!');
    }

    function addNote() {
        // Implement add note logic
        alert('Add Note feature coming soon!');
    }

    function deleteLead() {
        if (confirm('Are you sure you want to delete this lead?')) {
            fetch('/admin/leads/<?= $lead['id'] ?>/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/admin/leads';
                    } else {
                        alert('Failed to delete lead: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete lead. Please try again.');
                });
        }
    }
</script>