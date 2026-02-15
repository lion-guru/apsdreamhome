require_once '../core/init.php';

$db = \App\Core\App::database();

require_once 'includes/admin_header.php';

// Get visit details if ID is provided
$visit_id = $_GET['id'] ?? null;
$visit = null;
if ($visit_id) {
    $visit = $db->fetchOne("
        SELECT
            v.*,
            c.name as customer_name,
            c.email as customer_email,
            c.phone as customer_phone,
            p.title as property_title,
            p.location as property_address,
            u.uname as agent_name
        FROM property_visits v
        JOIN customers c ON v.customer_id = c.id
        JOIN properties p ON v.property_id = p.id
        LEFT JOIN user u ON v.associate_id = u.uid
        WHERE v.id = ?
    ", [$visit_id]);

    if ($visit) {
        $visit = (object)$visit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_message = $mlSupport->translate("Invalid CSRF token.");
    } else {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_status':
                    $new_status = $_POST['status'];
                    $v_id = intval($_POST['visit_id']);
                    $feedback = $_POST['feedback'] ?? null;
                    $rating = $_POST['rating'] ?? null;

                    $sql = "UPDATE property_visits SET status = ?, feedback = ?, rating = ?, updated_at = NOW() WHERE id = ?";
                    if ($db->execute($sql, [$new_status, $feedback, $rating, $v_id])) {
                        $success_message = $mlSupport->translate("Visit status updated successfully!");

                        // Log activity
                        log_admin_activity('visit_status_update', "Updated visit #$v_id status to $new_status");

                        // Send notification to agent
                        if ($visit && isset($visit->associate_id)) {
                            $notification = new NotificationManager(null, new EmailService());
                            $notification->send([
                                'type' => 'visit_status_update',
                                'user_id' => $visit->associate_id,
                                'title' => $mlSupport->translate('Visit Status Updated'),
                                'message' => $mlSupport->translate("Visit for") . " " . $visit->property_title . " " . $mlSupport->translate("has been marked as") . " " . ucfirst($new_status),
                                'link' => "/admin/visits.php?id=$v_id"
                            ]);
                        }
                    } else {
                        $error_message = $mlSupport->translate("Error updating visit status.");
                    }
                    break;
            }
        }
    }
}

// Get visits for listing
$where = [];
$params = [];

if (isset($_GET['status']) && $_GET['status'] !== 'all') {
    $where[] = "v.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['date_from']) && $_GET['date_from']) {
    $where[] = "v.visit_date >= ?";
    $params[] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && $_GET['date_to']) {
    $where[] = "v.visit_date <= ?";
    $params[] = $_GET['date_to'];
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT
        v.*,
        c.name as customer_name,
        c.email as customer_email,
        c.phone as customer_phone,
        p.title as property_title,
        u.uname as agent_name
    FROM property_visits v
    JOIN customers c ON v.customer_id = c.id
    JOIN properties p ON v.property_id = p.id
    LEFT JOIN user u ON v.associate_id = u.uid
    $where_clause
    ORDER BY v.visit_date DESC
";

$visits = $db->fetchAll($query, $params);

// Get visit statistics
$stats = [
    'total' => 0,
    'today' => 0,
    'completed' => 0,
    'scheduled' => 0
];

$queries = [
    'total' => "SELECT COUNT(*) as count FROM property_visits",
    'today' => "SELECT COUNT(*) as count FROM property_visits WHERE DATE(visit_date) = CURDATE()",
    'completed' => "SELECT COUNT(*) as count FROM property_visits WHERE status = 'completed'",
    'scheduled' => "SELECT COUNT(*) as count FROM property_visits WHERE status = 'scheduled'"
];

foreach ($queries as $key => $sql) {
    $res = $db->fetchOne($sql);
    $stats[$key] = $res['count'] ?? 0;
}

require_once 'includes/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles mb-4">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor"><?php echo h($mlSupport->translate('Visit Management')); ?></h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Visit Management')); ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo h($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo h($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo h(number_format($stats['total'])); ?></h4>
                                <div class="small"><?php echo h($mlSupport->translate('Total Visits')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-check-circle fa-2x opacity-50"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo h(number_format($stats['completed'])); ?></h4>
                                <div class="small"><?php echo h($mlSupport->translate('Completed Visits')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo h(number_format($stats['scheduled'])); ?></h4>
                                <div class="small"><?php echo h($mlSupport->translate('Scheduled Visits')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo h(number_format($stats['today'])); ?></h4>
                                <div class="small"><?php echo h($mlSupport->translate("Today's Visits")); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($visit): ?>
            <!-- Visit Details -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>
                        <?php echo h($mlSupport->translate('Visit Details')); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><?php echo h($mlSupport->translate('Customer Information')); ?></h6>
                            <p><strong><?php echo h($mlSupport->translate('Name')); ?>:</strong> <?php echo h($visit->customer_name); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Email')); ?>:</strong> <?php echo h($visit->customer_email); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Phone')); ?>:</strong> <?php echo h($visit->customer_phone); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><?php echo h($mlSupport->translate('Property Information')); ?></h6>
                            <p><strong><?php echo h($mlSupport->translate('Title')); ?>:</strong> <?php echo h($visit->property_title); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Address')); ?>:</strong> <?php echo h($visit->property_address); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Agent')); ?>:</strong> <?php echo h($visit->agent_name); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><?php echo h($mlSupport->translate('Visit Information')); ?></h6>
                            <p><strong><?php echo h($mlSupport->translate('Date')); ?>:</strong> <?php echo h(date('F j, Y', strtotime($visit->visit_date))); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Time')); ?>:</strong> <?php echo h(date('g:i A', strtotime($visit->visit_date))); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Status')); ?>:</strong> <span class="badge bg-<?php echo h(getStatusBadgeClass($visit->status)); ?>"><?php echo h($mlSupport->translate(ucfirst($visit->status))); ?></span></p>
                            <?php if ($visit->notes): ?>
                                <p><strong><?php echo h($mlSupport->translate('Notes')); ?>:</strong> <?php echo nl2br(h($visit->notes)); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><?php echo h($mlSupport->translate('Update Status')); ?></h6>
                            <form method="POST" class="needs-validation" novalidate>
                                <?php echo getCsrfField(); ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="visit_id" value="<?php echo h($visit->id); ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo h($mlSupport->translate('Status')); ?></label>
                                    <select name="status" class="form-select border-0 bg-light" required>
                                        <option value="scheduled" <?php echo h($visit->status) === 'scheduled' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Scheduled')); ?></option>
                                        <option value="completed" <?php echo h($visit->status) === 'completed' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Completed')); ?></option>
                                        <option value="cancelled" <?php echo h($visit->status) === 'cancelled' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Cancelled')); ?></option>
                                        <option value="rescheduled" <?php echo h($visit->status) === 'rescheduled' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Rescheduled')); ?></option>
                                        <option value="no_show" <?php echo h($visit->status) === 'no_show' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('No Show')); ?></option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo h($mlSupport->translate('Feedback')); ?></label>
                                    <textarea name="feedback" class="form-control border-0 bg-light" rows="3"><?php echo h($visit->feedback ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo h($mlSupport->translate('Rating')); ?></label>
                                    <select name="rating" class="form-select border-0 bg-light">
                                        <option value=""><?php echo h($mlSupport->translate('Select Rating')); ?></option>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($visit->rating ?? 0) === $i ? 'selected' : ''; ?>><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i><?php echo h($mlSupport->translate('Update Visit')); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end">
                    <a href="visits.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i><?php echo h($mlSupport->translate('Back to List')); ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Visits List -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-calendar me-2 text-primary"></i>
                        <?php echo h($mlSupport->translate('Property Visits')); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded">
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><?php echo h($mlSupport->translate('Status')); ?></label>
                            <select name="status" class="form-select border-0">
                                <option value="all"><?php echo h($mlSupport->translate('All Status')); ?></option>
                                <option value="scheduled" <?php echo ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Scheduled')); ?></option>
                                <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Completed')); ?></option>
                                <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Cancelled')); ?></option>
                                <option value="rescheduled" <?php echo ($_GET['status'] ?? '') === 'rescheduled' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Rescheduled')); ?></option>
                                <option value="no_show" <?php echo ($_GET['status'] ?? '') === 'no_show' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('No Show')); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><?php echo h($mlSupport->translate('From Date')); ?></label>
                            <input type="date" name="date_from" class="form-control border-0" value="<?php echo h($_GET['date_from'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><?php echo h($mlSupport->translate('To Date')); ?></label>
                            <input type="date" name="date_to" class="form-control border-0" value="<?php echo h($_GET['date_to'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="w-100 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-filter me-2"></i><?php echo h($mlSupport->translate('Filter')); ?>
                                </button>
                                <a href="visits.php" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Visits Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0"><?php echo h($mlSupport->translate('Date & Time')); ?></th>
                                    <th class="border-0"><?php echo h($mlSupport->translate('Customer')); ?></th>
                                    <th class="border-0"><?php echo h($mlSupport->translate('Property')); ?></th>
                                    <th class="border-0"><?php echo h($mlSupport->translate('Agent')); ?></th>
                                    <th class="border-0"><?php echo h($mlSupport->translate('Status')); ?></th>
                                    <th class="border-0 text-end"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visits as $v): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo date('M j, Y', strtotime($v['visit_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($v['visit_date'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo h($v['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo h($v['customer_email']); ?></small>
                                        </td>
                                        <td><?php echo h($v['property_title']); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-user-tie me-1 text-muted"></i>
                                                <?php echo h($v['agent_name'] ?? $mlSupport->translate('No Agent')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($v['status']); ?>">
                                                <?php echo $mlSupport->translate(ucfirst($v['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="?id=<?php echo h($v['id']); ?>" class="btn btn-sm btn-info text-white shadow-sm">
                                                <i class="fas fa-eye me-1"></i> <?php echo h($mlSupport->translate('View')); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($visits)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            <?php echo $mlSupport->translate('No visits found.'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
function getStatusBadgeClass($status) {
    return match($status) {
        'scheduled' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rescheduled' => 'warning',
        'no_show' => 'secondary',
        default => 'info'
    };
}

require_once 'includes/admin_footer.php';
?>
