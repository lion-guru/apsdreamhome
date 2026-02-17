<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';
require_once __DIR__ . '/../includes/notification_manager.php';

// Initialize NotificationManager
$notification_manager = new NotificationManager();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid CSRF token.";
    } elseif (isset($_POST['action'])) {
        $db = \App\Core\App::database();
        switch ($_POST['action']) {
            case 'update_template':
                $type = $_POST['type'] ?? '';
                $title = $_POST['title_template'] ?? '';
                $message = $_POST['message_template'] ?? '';
                
                try {
                    $sql = "UPDATE notification_templates SET title_template = ?, message_template = ? WHERE type = ?";
                    if ($db->execute($sql, [$title, $message, $type])) {
                        log_admin_activity('update_notification_template', "Updated template for type: $type");
                        $success_message = "Template updated successfully!";
                    } else {
                        $error_message = "Error updating template.";
                    }
                } catch (Exception $e) {
                    $error_message = "Error updating template: " . h($e->getMessage());
                }
                break;
                
            case 'send_test':
                $type = $_POST['type'] ?? '';
                $user_id = $_POST['user_id'] ?? '';
                
                try {
                    $notification_data = [
                        'type' => $type,
                        'user_id' => $user_id,
                        'title' => 'Test Notification',
                        'message' => 'This is a test notification.',
                        'link' => '#'
                    ];
                    $notification_manager->send($notification_data);
                    log_admin_activity('send_test_notification', "Sent test notification of type $type to user ID: $user_id");
                    $success_message = "Test notification sent successfully!";
                } catch (Exception $e) {
                    $error_message = "Error sending test notification: " . h($e->getMessage());
                }
                break;
        }
    }
}

// Get notification templates using singleton
$db = \App\Core\App::database();
try {
    $templates = $db->fetchAll("SELECT * FROM notification_templates ORDER BY type");
} catch (Exception $e) {
    $templates = [];
}

// Get notification stats
$stats = [
    'total' => 0,
    'unread' => 0,
    'today' => 0
];

try {
    $stats['total'] = $db->fetchOne("SELECT COUNT(*) as count FROM notifications")['count'] ?? 0;
    $stats['unread'] = $db->fetchOne("SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'")['count'] ?? 0;
    $stats['today'] = $db->fetchOne("SELECT COUNT(*) as count FROM notifications WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
}

// Get recent notifications
try {
    $recent_notifications = $db->fetchAll("
        SELECT n.*, u.uname 
        FROM notifications n 
        JOIN user u ON n.user_id = u.uid 
        ORDER BY n.created_at DESC 
        LIMIT 10
    ");
} catch (Exception $e) {
    $recent_notifications = [];
}

$page_title = "Notification Management";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Notification Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Notification Management</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo h($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo h($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card bg-primary text-white mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo number_format($stats['total']); ?></h4>
                                <div class="small opacity-75">Total Notifications</div>
                            </div>
                            <div class="fs-1 opacity-25">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card bg-warning text-white mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo number_format($stats['unread']); ?></h4>
                                <div class="small opacity-75">Unread Notifications</div>
                            </div>
                            <div class="fs-1 opacity-25">
                                <i class="fas fa-envelope-open"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card bg-success text-white mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo number_format($stats['today']); ?></h4>
                                <div class="small opacity-75">Today's Notifications</div>
                            </div>
                            <div class="fs-1 opacity-25">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-7">
                <!-- Notification Templates -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope me-2 text-primary"></i>Notification Templates
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion accordion-flush" id="templatesAccordion">
                            <?php foreach ($templates as $index => $template): ?>
                                <div class="accordion-item border-bottom">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                            <?php echo h(ucwords(str_replace('_', ' ', $template['type']))); ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#templatesAccordion">
                                        <div class="accordion-body px-0 py-3">
                                            <form method="POST" class="needs-validation" novalidate>
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="action" value="update_template">
                                                <input type="hidden" name="type" value="<?php echo h($template['type']); ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Title Template</label>
                                                    <input type="text" class="form-control" name="title_template" value="<?php echo h($template['title_template']); ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Message Template</label>
                                                    <textarea class="form-control" name="message_template" rows="3" required><?php echo h($template['message_template']); ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Available Variables</label>
                                                    <div class="alert alert-info py-2 small mb-0">
                                                        <code>{name}, {property_title}, {visit_date}, {visit_time}, {status}</code>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-save me-1"></i> Update Template
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendTestNotification('<?php echo h($template['type']); ?>')">
                                                        <i class="fas fa-paper-plane me-1"></i> Send Test
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <!-- Recent Notifications -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>Recent Notifications
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small fw-bold">Type</th>
                                        <th class="small fw-bold">Recipient</th>
                                        <th class="small fw-bold">Status</th>
                                        <th class="small fw-bold text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_notifications)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No notifications sent yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_notifications as $notification): ?>
                                            <tr>
                                                <td class="small"><?php echo h(ucwords(str_replace('_', ' ', $notification['type']))); ?></td>
                                                <td class="small fw-bold"><?php echo h($notification['uname']); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill bg-<?php echo $notification['status'] === 'read' ? 'success' : 'warning'; ?> small">
                                                        <?php echo h(ucfirst($notification['status'])); ?>
                                                    </span>
                                                </td>
                                                <td class="small text-muted text-end"><?php echo date('M d, H:i', strtotime($notification['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Test Notification Modal -->
<div class="modal fade" id="testNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="action" value="send_test">
                    <input type="hidden" name="type" id="testNotificationType">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Select User</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Choose a user...</option>
                            <?php
                            try {
                                $users = $db->fetchAll("SELECT uid as id, uname as name FROM user ORDER BY uname");
                                foreach ($users as $user) {
                                    echo "<option value='".h($user['id'])."'>".h($user['name'])."</option>";
                                }
                            } catch (Exception $e) {
                                error_log("Error fetching users: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Send Test Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

<script>
function sendTestNotification(type) {
    document.getElementById('testNotificationType').value = type;
    new bootstrap.Modal(document.getElementById('testNotificationModal')).show();
}

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>



