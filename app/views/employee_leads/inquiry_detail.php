<?php
$inquiry = $inquiry ?? [];
$base = BASE_URL ?? '';
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');

$statusColors = [
    'pending' => 'bg-warning text-dark',
    'new' => 'bg-info text-white',
    'in_progress' => 'bg-primary text-white',
    'completed' => 'bg-success text-white',
    'closed' => 'bg-secondary text-white',
];
$statusKey = strtolower($inquiry['status'] ?? 'pending');
$badgeClass = $statusColors[$statusKey] ?? 'bg-secondary text-white';
$priorityKey = strtolower($inquiry['priority'] ?? 'medium');
$priorityBadge = ($priorityKey === 'high') ? 'badge bg-danger' : (($priorityKey === 'low') ? 'badge bg-secondary' : 'badge bg-info text-dark');
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/employee/dashboard"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/employee/inquiries">Assigned Inquiries</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inquiry #<?= (int)$inquiry['id'] ?></li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="fas fa-user-circle text-primary me-2"></i><?= htmlspecialchars($inquiry['name']) ?>
            </h2>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $statusKey)) ?></span>
                <span class="<?= $priorityBadge ?>"><?= ucfirst($priorityKey) ?> Priority</span>
                <span class="text-muted small"><i class="far fa-clock me-1"></i>Received <?= date('d M Y, h:i A', strtotime($inquiry['created_at'])) ?></span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="tel:<?= htmlspecialchars($inquiry['phone']) ?>" class="btn btn-success">
                <i class="fas fa-phone-alt me-1"></i>Call Customer
            </a>
            <a href="https://wa.me/91<?= preg_replace('/\D/', '', $inquiry['phone']) ?>?text=<?= urlencode('Hello ' . $inquiry['name'] . ', this is regarding your inquiry with APS Dream Home.') ?>" target="_blank" class="btn btn-outline-success">
                <i class="fab fa-whatsapp me-1"></i>WhatsApp
            </a>
            <a href="<?= $base ?>/employee/inquiries" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Column: Customer & Inquiry Details -->
        <div class="col-lg-7">
            <!-- Customer Contact Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-address-card text-primary me-2"></i>Customer Profile</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase fw-semibold d-block">Full Name</label>
                            <span class="fs-6 fw-semibold text-dark"><?= htmlspecialchars($inquiry['name']) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase fw-semibold d-block">Phone Number</label>
                            <a href="tel:<?= htmlspecialchars($inquiry['phone']) ?>" class="fs-6 fw-semibold text-primary text-decoration-none">
                                <i class="fas fa-phone-alt me-1 small"></i><?= htmlspecialchars($inquiry['phone']) ?>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase fw-semibold d-block">Email Address</label>
                            <?php if (!empty($inquiry['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($inquiry['email']) ?>" class="fs-6 text-dark text-decoration-none">
                                    <i class="fas fa-envelope me-1 text-muted small"></i><?= htmlspecialchars($inquiry['email']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Not provided</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase fw-semibold d-block">Inquiry Type</label>
                            <span class="badge bg-light text-dark border"><?= ucfirst($inquiry['type'] ?? 'general') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Interest (if applicable) -->
            <?php if (!empty($inquiry['property_title']) || !empty($inquiry['project_name'])): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-building text-info me-2"></i>Interested Property / Project</h6>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (!empty($inquiry['property_title'])): ?>
                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-2">
                                <div class="bg-primary text-white rounded p-2"><i class="fas fa-home fa-lg"></i></div>
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($inquiry['property_title']) ?></div>
                                    <span class="text-muted small">Property ID: #<?= (int)$inquiry['property_id'] ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($inquiry['project_name'])): ?>
                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                                <div class="bg-info text-white rounded p-2"><i class="fas fa-city fa-lg"></i></div>
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($inquiry['project_name']) ?></div>
                                    <span class="text-muted small">Township / Project ID: #<?= (int)$inquiry['project_id'] ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Customer Message Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-comment-alt text-warning me-2"></i>Inquiry Message</h6>
                    <span class="text-muted small"><?= date('d M Y, h:i A', strtotime($inquiry['created_at'])) ?></span>
                </div>
                <div class="card-body pt-0">
                    <div class="p-3 bg-light rounded-3 text-dark border-start border-4 border-warning" style="white-space: pre-wrap; line-height: 1.6;">
<?= htmlspecialchars($inquiry['message']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status Update & Follow-up Actions -->
        <div class="col-lg-5">
            <!-- Update Status Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sync-alt text-primary me-2"></i>Update Inquiry Status</h6>
                </div>
                <div class="card-body pt-0">
                    <form method="POST" action="<?= $base ?>/employee/inquiries/<?= (int)$inquiry['id'] ?>/status">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label text-muted small fw-semibold">Status Stage</label>
                            <select id="statusSelect" name="status" class="form-select">
                                <option value="pending" <?= $statusKey === 'pending' ? 'selected' : '' ?>>Pending (Needs Action)</option>
                                <option value="in_progress" <?= $statusKey === 'in_progress' ? 'selected' : '' ?>>In Progress (Contacting / Site Visit)</option>
                                <option value="completed" <?= $statusKey === 'completed' ? 'selected' : '' ?>>Completed (Customer Converted / Booked)</option>
                                <option value="closed" <?= $statusKey === 'closed' ? 'selected' : '' ?>>Closed (Resolved / Not Interested)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-check me-1"></i>Save Status Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Follow-up Log Action Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history text-success me-2"></i>Log Follow-up Call / Interaction</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3 p-2 bg-light rounded text-muted small d-flex justify-content-between">
                        <span><i class="fas fa-phone me-1"></i>Total Follow-ups: <strong><?= (int)($inquiry['followup_count'] ?? 0) ?></strong></span>
                        <?php if (!empty($inquiry['last_followup_at'])): ?>
                            <span>Last: <?= date('d M, h:i A', strtotime($inquiry['last_followup_at'])) ?></span>
                        <?php else: ?>
                            <span>No follow-up yet</span>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="<?= $base ?>/employee/inquiries/<?= (int)$inquiry['id'] ?>/followup">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="mb-3">
                            <label for="followupNote" class="form-label text-muted small fw-semibold">Interaction Notes & Feedback</label>
                            <textarea id="followupNote" name="followup_note" class="form-control" rows="3" placeholder="e.g., Called customer, interested in Plot A-12, scheduled site visit for Sunday morning..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-paper-plane me-1"></i>Log Follow-up & Touch Timeline
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Communication Templates -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body pt-0 d-grid gap-2">
                    <a href="tel:<?= htmlspecialchars($inquiry['phone']) ?>" class="btn btn-light text-start border">
                        <i class="fas fa-phone text-success me-2"></i>Call Customer (<?= htmlspecialchars($inquiry['phone']) ?>)
                    </a>
                    <a href="https://wa.me/91<?= preg_replace('/\D/', '', $inquiry['phone']) ?>?text=<?= urlencode('Namaste ' . $inquiry['name'] . ', thank you for contacting APS Dream Home. I am your assigned support representative. How may I assist you today?') ?>" target="_blank" class="btn btn-light text-start border">
                        <i class="fab fa-whatsapp text-success me-2"></i>Send WhatsApp Introduction
                    </a>
                    <?php if (!empty($inquiry['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($inquiry['email']) ?>?subject=<?= urlencode('APS Dream Home — Regarding your Inquiry #' . $inquiry['id']) ?>" class="btn btn-light text-start border">
                            <i class="fas fa-envelope text-primary me-2"></i>Send Email to Customer
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
