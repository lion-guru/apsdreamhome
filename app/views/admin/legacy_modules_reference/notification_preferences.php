<?php
require_once '../core/init.php';
require_once 'includes/admin_header.php';
require_once '../includes/classes/SmsNotifier.php';

$user_id = $_SESSION['uid'];
$user_role = $_SESSION['role'];

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken();
    try {
        $db = \App\Core\App::database();
        // Update user preferences
        $phone = !empty($_POST['phone']) ? SmsNotifier::validatePhoneNumber($_POST['phone']) : null;
        $sms_enabled = isset($_POST['sms_enabled']) ? 1 : 0;

        if (!empty($_POST['phone']) && !$phone) {
            throw new Exception($mlSupport->translate('Invalid phone number format'));
        }

        if ($user_role === 'admin' || $user_role === 'superadmin') {
            $query = "UPDATE admin SET phone = :phone, sms_enabled = :sms_enabled WHERE id = :user_id";
            $db->execute($query, [
                'phone' => $phone,
                'sms_enabled' => $sms_enabled,
                'user_id' => $user_id
            ]);
        } else {
            $query = "UPDATE user SET uphone = :phone, sms_enabled = :sms_enabled WHERE uid = :user_id";
            $db->execute($query, [
                'phone' => $phone,
                'sms_enabled' => $sms_enabled,
                'user_id' => $user_id
            ]);
        }

        // Update alert subscriptions
        $db->execute("DELETE FROM alert_subscriptions WHERE user_id = :user_id", ['user_id' => $user_id]);

        if (!empty($_POST['subscriptions'])) {
            foreach ($_POST['subscriptions'] as $system => $levels) {
                foreach ($levels as $level) {
                    $db->execute("INSERT INTO alert_subscriptions (user_id, system, level, email_enabled) VALUES (:user_id, :system, :level, 1)", [
                        'user_id' => $user_id,
                        'system' => $system,
                        'level' => $level
                    ]);
                }
            }
        }

        log_admin_activity('notification_preferences_update', "Updated notification preferences");
        $success_message = $mlSupport->translate('Preferences updated successfully');
    } catch (Exception $e) {
        $error_message = $mlSupport->translate('Error updating preferences') . ': ' . $e->getMessage();
    }
}

// Get current preferences
$db = \App\Core\App::database();
if ($user_role === 'admin' || $user_role === 'superadmin') {
    $user = $db->fetch("SELECT phone, sms_enabled FROM admin WHERE id = :user_id", ['user_id' => $user_id]);
} else {
    $user = $db->fetch("SELECT uphone as phone, sms_enabled FROM user WHERE uid = :user_id", ['user_id' => $user_id]);
}

$subscriptions_result = $db->fetchAll("SELECT system, level FROM alert_subscriptions WHERE user_id = :user_id", ['user_id' => $user_id]);

$subscriptions = [];
foreach ($subscriptions_result as $row) {
    $subscriptions[$row['system']][] = $row['level'];
}

// Get available systems
$systems_result = $db->fetchAll("SELECT DISTINCT system FROM system_alerts ORDER BY system");

require_once 'includes/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles mb-4">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor"><?php echo $mlSupport->translate('Notification Preferences'); ?></h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php"><?php echo $mlSupport->translate('Dashboard'); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo $mlSupport->translate('Notification Preferences'); ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <?php echo getCsrfField(); ?>

            <div class="row">
                <div class="col-lg-6">
                    <!-- SMS Preferences -->
                    <div class="card shadow-sm border-0 mb-4 h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fas fa-sms me-2 text-primary"></i>
                                <?php echo $mlSupport->translate('SMS Notifications'); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="form-check form-switch custom-switch">
                                    <input type="checkbox" class="form-check-input" id="sms_enabled"
                                        name="sms_enabled" <?php echo ($user['sms_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="sms_enabled">
                                        <?php echo $mlSupport->translate('Enable SMS notifications'); ?>
                                    </label>
                                </div>
                                <p class="text-muted small mt-1">
                                    <?php echo $mlSupport->translate('Receive critical system alerts via SMS text messages.'); ?>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold"><?php echo $mlSupport->translate('Phone Number'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control border-0 bg-light" id="phone" name="phone"
                                        value="<?php echo h($user['phone'] ?? ''); ?>"
                                        placeholder="+1234567890">
                                </div>
                                <div class="form-text mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo $mlSupport->translate('Format: International number (e.g., +1234567890)'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Test Notification -->
                    <div class="card shadow-sm border-0 mb-4 h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fas fa-vial me-2 text-primary"></i>
                                <?php echo $mlSupport->translate('Test Notifications'); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                <?php echo $mlSupport->translate('Send a test notification to verify your SMS and email settings are working correctly.'); ?>
                            </p>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-primary btn-lg" onclick="sendTestNotification()">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    <?php echo $mlSupport->translate('Send Test Notification'); ?>
                                </button>
                            </div>
                            <div id="test-result" class="mt-3 d-none"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Subscriptions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-bell me-2 text-primary"></i>
                        <?php echo $mlSupport->translate('Alert Subscriptions'); ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0"><?php echo $mlSupport->translate('System Module'); ?></th>
                                    <th class="text-center border-0"><?php echo $mlSupport->translate('Critical'); ?></th>
                                    <th class="text-center border-0"><?php echo $mlSupport->translate('Warning'); ?></th>
                                    <th class="text-center border-0"><?php echo $mlSupport->translate('Info'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($systems_result->num_rows > 0): ?>
                                    <?php while ($system = $systems_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">
                                                <?php echo h(ucfirst($system['system'])); ?>
                                            </td>
                                            <?php foreach (['critical', 'warning', 'info'] as $level): ?>
                                                <td class="text-center">
                                                    <div class="form-check d-inline-block">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="subscriptions[<?php echo h($system['system']); ?>][]"
                                                            value="<?php echo $level; ?>"
                                                            <?php echo in_array($level, $subscriptions[$system['system']] ?? []) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <?php echo $mlSupport->translate('No system modules available for subscription.'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i>
                        <?php echo $mlSupport->translate('Save Preferences'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?php echo generateCSRFToken(); ?>';

    function sendTestNotification() {
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?php echo $mlSupport->translate("Sending..."); ?>';

        const resultDiv = document.getElementById('test-result');
        resultDiv.className = 'mt-3 d-none';

        fetch('../api/test_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    csrf_token: CSRF_TOKEN
                })
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.classList.remove('d-none');
                if (data.status === 'success') {
                    resultDiv.className = 'mt-3 alert alert-success py-2 small';
                    resultDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + (data.message || '<?php echo $mlSupport->translate("Test notification sent successfully!"); ?>');
                } else {
                    resultDiv.className = 'mt-3 alert alert-danger py-2 small';
                    resultDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + (data.message || '<?php echo $mlSupport->translate("Error sending test notification"); ?>');
                }
            })
            .catch(error => {
                resultDiv.classList.remove('d-none');
                resultDiv.className = 'mt-3 alert alert-danger py-2 small';
                resultDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> <?php echo $mlSupport->translate("Network error. Please try again."); ?>';
                console.error('Error:', error);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

    // Form validation
    (function() {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Toggle phone input based on SMS enabled
    const smsSwitch = document.getElementById('sms_enabled');
    const phoneInput = document.getElementById('phone');

    if (smsSwitch && phoneInput) {
        const updatePhoneRequired = () => {
            phoneInput.required = smsSwitch.checked;
        };
        smsSwitch.addEventListener('change', updatePhoneRequired);
        updatePhoneRequired();
    }
</script>

<?php require_once 'includes/admin_footer.php'; ?>