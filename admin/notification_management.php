<?php
$page_title = "Notification Management - APS Dream Homes Admin";
require_once '../includes/admin_header.php';
require_once '../includes/notification_manager.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Initialize NotificationManager
$notification_manager = new NotificationManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_template':
                $type = $_POST['type'] ?? '';
                $title = $_POST['title_template'] ?? '';
                $message = $_POST['message_template'] ?? '';
                
                try {
                    $stmt = $conn->prepare("UPDATE notification_templates SET title_template = ?, message_template = ? WHERE type = ?");
                    $stmt->bind_param('sss', $title, $message, $type);
                    $stmt->execute();
                    $success_message = "Template updated successfully!";
                } catch (Exception $e) {
                    $error_message = "Error updating template: " . $e->getMessage();
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
                    $success_message = "Test notification sent successfully!";
                } catch (Exception $e) {
                    $error_message = "Error sending test notification: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get notification templates
$templates = [];
$result = $conn->query("SELECT * FROM notification_templates ORDER BY type");
while ($row = $result->fetch_assoc()) {
    $templates[] = $row;
}

// Get notification stats
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM notifications")->fetch_object()->count,
    'unread' => $conn->query("SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'")->fetch_object()->count,
    'today' => $conn->query("SELECT COUNT(*) as count FROM notifications WHERE DATE(created_at) = CURDATE()")->fetch_object()->count
];

// Get recent notifications
$recent_notifications = [];
$result = $conn->query("
    SELECT n.*, u.first_name, u.last_name 
    FROM notifications n 
    JOIN users u ON n.user_id = u.id 
    ORDER BY n.created_at DESC 
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $recent_notifications[] = $row;
}
?>

<!-- Dashboard Header -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Notification Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Notification Management</li>
    </ol>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['total']); ?></h4>
                    <div>Total Notifications</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['unread']); ?></h4>
                    <div>Unread Notifications</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['today']); ?></h4>
                    <div>Today's Notifications</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notification Templates -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-envelope me-1"></i>
            Notification Templates
        </div>
        <div class="card-body">
            <div class="accordion" id="templatesAccordion">
                <?php foreach ($templates as $index => $template): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $template['type'])); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#templatesAccordion">
                            <div class="accordion-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="update_template">
                                    <input type="hidden" name="type" value="<?php echo $template['type']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Title Template</label>
                                        <input type="text" class="form-control" name="title_template" value="<?php echo htmlspecialchars($template['title_template']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Message Template</label>
                                        <textarea class="form-control" name="message_template" rows="3" required><?php echo htmlspecialchars($template['message_template']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Available Variables</label>
                                        <div class="alert alert-info">
                                            {name}, {property_title}, {visit_date}, {visit_time}, {status}
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update Template</button>
                                        <button type="button" class="btn btn-secondary" onclick="sendTestNotification('<?php echo $template['type']; ?>')">Send Test</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Notifications -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-bell me-1"></i>
            Recent Notifications
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Recipient</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_notifications as $notification): ?>
                        <tr>
                            <td><?php echo ucwords(str_replace('_', ' ', $notification['type'])); ?></td>
                            <td><?php echo htmlspecialchars($notification['first_name'] . ' ' . $notification['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($notification['title']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $notification['status'] === 'read' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($notification['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($notification['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                <div class="modal-body">
                    <input type="hidden" name="action" value="send_test">
                    <input type="hidden" name="type" id="testNotificationType">
                    
                    <div class="mb-3">
                        <label class="form-label">Select User</label>
                        <select name="user_id" class="form-control" required>
                            <?php
                            $users = $conn->query("SELECT id, first_name, last_name FROM users ORDER BY first_name, last_name");
                            while ($user = $users->fetch_object()) {
                                echo "<option value='{$user->id}'>{$user->first_name} {$user->last_name}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function sendTestNotification(type) {
    document.getElementById('testNotificationType').value = type;
    new bootstrap.Modal(document.getElementById('testNotificationModal')).show();
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
