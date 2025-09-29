<?php
require_once '../includes/auth/auth_session.php';
require_once '../includes/db_settings.php';
require_once '../includes/classes/SmsNotifier.php';

$conn = get_db_connection();

// Get systems using prepared statement
$systems_stmt = $conn->prepare("SELECT DISTINCT system FROM system_alerts ORDER BY system");
$systems_stmt->execute();
$systems_result = $systems_stmt->get_result();
$systems_stmt->close();

$subscriptions = [];

if (isset($_POST['subscriptions'])) {
    foreach ($_POST['subscriptions'] as $system => $levels) {
        $subscriptions[$system] = $levels;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Preferences - APS Dream Home</title>
    <?php include '../includes/templates/header_links.php'; ?>
</head>
<body class="admin-dashboard">
    <?php include '../includes/templates/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/templates/admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Notification Preferences</h1>
                </div>

                <?php
                $user_id = $_SESSION['user_id'];
                $success_message = '';
                $error_message = '';

                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    try {
                        $conn = get_db_connection();

                        // Update user preferences
                        $phone = !empty($_POST['phone']) ? SmsNotifier::validatePhoneNumber($_POST['phone']) : null;
                        $sms_enabled = isset($_POST['sms_enabled']) ? 1 : 0;

                        if (!empty($_POST['phone']) && !$phone) {
                            throw new Exception('Invalid phone number format');
                        }

                        $query = "UPDATE users 
                                 SET phone = ?,
                                     sms_enabled = ?
                                 WHERE id = ?";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('sii', $phone, $sms_enabled, $user_id);
                        $stmt->execute();

                        // Update alert subscriptions using prepared statement
                        $delete_stmt = $conn->prepare("DELETE FROM alert_subscriptions WHERE user_id = ?");
                        $delete_stmt->bind_param("i", $user_id);
                        $delete_stmt->execute();
                        $delete_stmt->close();

                        if (!empty($_POST['subscriptions'])) {
                            $insert_stmt = $conn->prepare("INSERT INTO alert_subscriptions (user_id, system, level, email_enabled) VALUES (?, ?, ?, 1)");
                            foreach ($_POST['subscriptions'] as $system => $levels) {
                                foreach ($levels as $level) {
                                    $insert_stmt->bind_param("iss", $user_id, $system, $level);
                                    $insert_stmt->execute();
                                }
                            }
                            $insert_stmt->close();
                        }

                        $success_message = 'Preferences updated successfully';

                    } catch (Exception $e) {
                        $error_message = 'Error updating preferences: ' . $e->getMessage();
                    }
                }

                // Get current preferences using prepared statement
                $conn = get_db_connection();
                $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user = $user_result->fetch_assoc();
                $user_stmt->close();
                
                $subscriptions_stmt = $conn->prepare("SELECT system, level FROM alert_subscriptions WHERE user_id = ?");
                $subscriptions_stmt->bind_param("i", $user_id);
                $subscriptions_stmt->execute();
                $subscriptions_result = $subscriptions_stmt->get_result();
                $subscriptions_stmt->close();

                $subscriptions = [];
                while ($row = $subscriptions_result->fetch_assoc()) {
                    $subscriptions[$row['system']][] = $row['level'];
                }

                // Get available systems using prepared statement
                $systems_query_stmt = $conn->prepare("SELECT DISTINCT system FROM system_alerts ORDER BY system");
                $systems_query_stmt->execute();
                $systems_result = $systems_query_stmt->get_result();
                $systems_query_stmt->close();
                ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <!-- SMS Preferences -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">SMS Notifications</h5>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="sms_enabled" 
                                           name="sms_enabled" <?php echo $user['sms_enabled'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="sms_enabled">Enable SMS notifications</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="Enter your phone number">
                                <div class="form-text">Format: International number (e.g., +1234567890)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Subscriptions -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Alert Subscriptions</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>System</th>
                                            <th>Critical</th>
                                            <th>Warning</th>
                                            <th>Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($system = $systems_result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($system['system']); ?></td>
                                                <?php foreach (['critical', 'warning', 'info'] as $level) { ?>
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                   name="subscriptions[<?php echo $system['system']; ?>][]"
                                                                   value="<?php echo $level; ?>"
                                                                   <?php echo in_array($level, $subscriptions[$system['system']] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </div>
                </form>

                <!-- Test Notification -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Test Notifications</h5>
                        <p>Send a test notification to verify your settings.</p>
                        <button type="button" class="btn btn-secondary" onclick="sendTestNotification()">
                            Send Test Notification
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../includes/templates/admin_footer.php'; ?>
    <script>
        function sendTestNotification() {
            fetch('/apsdreamhomefinal/api/test_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Test notification sent successfully!');
                } else {
                    alert('Error sending test notification: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error sending test notification');
                console.error('Error:', error);
            });
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });

        // Toggle phone input based on SMS enabled
        document.getElementById('sms_enabled').addEventListener('change', function() {
            const phoneInput = document.getElementById('phone');
            phoneInput.required = this.checked;
            if (!this.checked) {
                phoneInput.value = '';
            }
        });
    </script>
    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>
