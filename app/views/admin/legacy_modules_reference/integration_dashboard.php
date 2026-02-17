<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/includes/integration_helpers.php';
require_once __DIR__ . '/../includes/notification/webhook_manager.php';

$page_title = "Integration Dashboard";
$db = \App\Core\App::database();
$webhookManager = new WebhookManager();
$userId = $_SESSION['user_id'] ?? 0;

$success_msg = '';
$error_msg = '';

// Handle Webhook Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        if ($_POST['action'] === 'add_webhook') {
            try {
                $name = $_POST['name'] ?? '';
                $url = $_POST['url'] ?? '';
                $secret = $_POST['secret'] ?: null;
                $events = $_POST['events'] ?? [];

                $webhookManager->createWebhook($name, $url, $events, $secret);
                logAdminActivity($userId, 'add_webhook', 'Added new webhook: ' . $name);
                $success_msg = $mlSupport->translate('Webhook added successfully!');
            } catch (Exception $e) {
                $error_msg = $mlSupport->translate('Error adding webhook: ') . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete_webhook') {
            $id = $_POST['id'] ?? '';
            if ($webhookManager->deleteWebhook($id)) {
                logAdminActivity($userId, 'delete_webhook', 'Deleted webhook ID: ' . $id);
                $success_msg = $mlSupport->translate('Webhook deleted successfully!');
            } else {
                $error_msg = $mlSupport->translate('Error deleting webhook.');
            }
        }
    } else {
        $error_msg = $mlSupport->translate('Invalid CSRF token.');
    }
}

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $sql = "UPDATE integration_settings SET 
            whatsapp_api = :whatsapp_api, google_drive_client_id = :google_drive_client_id, 
            google_drive_client_secret = :google_drive_client_secret, 
            google_drive_refresh_token = :google_drive_refresh_token, google_sheets_key = :google_sheets_key, 
            email_host = :email_host, email_user = :email_user, email_pass = :email_pass, 
            sms_api = :sms_api, crm_api = :crm_api, slack_webhook_url = :slack_webhook_url, 
            telegram_bot_token = :telegram_bot_token, telegram_chat_id = :telegram_chat_id 
            WHERE id = 1";

        $params = [
            'whatsapp_api' => $_POST['whatsapp_api'],
            'google_drive_client_id' => $_POST['google_drive_client_id'],
            'google_drive_client_secret' => $_POST['google_drive_client_secret'],
            'google_drive_refresh_token' => $_POST['google_drive_refresh_token'],
            'google_sheets_key' => $_POST['google_sheets_key'],
            'email_host' => $_POST['email_host'],
            'email_user' => $_POST['email_user'],
            'email_pass' => $_POST['email_pass'],
            'sms_api' => $_POST['sms_api'],
            'crm_api' => $_POST['crm_api'],
            'slack_webhook_url' => $_POST['slack_webhook_url'],
            'telegram_bot_token' => $_POST['telegram_bot_token'],
            'telegram_chat_id' => $_POST['telegram_chat_id']
        ];

        if ($db->execute($sql, $params)) {
            logAdminActivity($userId, 'update_integration_settings', 'Updated integration API settings');
            $success_msg = $mlSupport->translate('Settings updated successfully!');
        } else {
            $error_msg = $mlSupport->translate('Error updating settings');
        }
    } else {
        $error_msg = $mlSupport->translate('Invalid CSRF token.');
    }
}

// Fetch current settings
$settings = get_integration_settings();

// Pagination settings
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Check if table exists first
$has_logs_table = !empty($db->fetchAll("SHOW TABLES LIKE 'integration_activity_logs'"));

// Fetch summary stats
$stats = [];
if ($has_logs_table) {
    $stats = $db->fetchAll("
        SELECT 
            integration_type, 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN status = 'failure' THEN 1 ELSE 0 END) as failure,
            SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error
        FROM integration_activity_logs 
        GROUP BY integration_type
    ");
}

// Fetch recent logs
$logs = [];
$total_logs = 0;
if ($has_logs_table) {
    $db = \App\Core\App::database();
    $logs = $db->fetchAll("SELECT * FROM integration_activity_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset", [
        'limit' => (int)$limit,
        'offset' => (int)$offset
    ]);
    $total_logs = $db->fetch("SELECT COUNT(*) as count FROM integration_activity_logs")['count'] ?? 0;
}
$total_pages = ceil($total_logs / $limit);

// Fetch Webhooks
$webhooks = $webhookManager->listWebhooks();

require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($mlSupport->translate('Integrations')) ?></li>
                        </ol>
                    </nav>
                    <h3 class="page-title fw-bold text-primary"><?= h($mlSupport->translate('Integration & API Monitoring')) ?></h3>
                    <p class="text-muted small mb-0"><?= h($mlSupport->translate('Manage and monitor external service integrations and webhooks')) ?></p>
                </div>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= h($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= h($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs nav-tabs-solid mb-4 shadow-sm rounded p-1 bg-light border-0">
            <li class="nav-item flex-fill text-center"><a class="nav-link active rounded border-0 fw-medium" href="#activity-tab" data-bs-toggle="tab"><?= h($mlSupport->translate('Activity Logs')) ?></a></li>
            <li class="nav-item flex-fill text-center"><a class="nav-link rounded border-0 fw-medium" href="#settings-tab" data-bs-toggle="tab"><?= h($mlSupport->translate('API Settings')) ?></a></li>
            <li class="nav-item flex-fill text-center"><a class="nav-link rounded border-0 fw-medium" href="#webhooks-tab" data-bs-toggle="tab"><?= h($mlSupport->translate('Webhooks')) ?></a></li>
        </ul>

        <div class="tab-content">
            <!-- Activity Logs Tab -->
            <div class="tab-pane show active" id="activity-tab">
                <!-- Summary Stats -->
                <div class="row">
                    <?php if (empty($stats)): ?>
                        <div class="col-12">
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                                <i class="fas fa-info-circle me-3 fa-lg"></i>
                                <?= h($mlSupport->translate('No integration activity recorded yet.')) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($stats as $s): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-uppercase fw-bold text-muted small mb-3"><?= h(str_replace('_', ' ', $s['integration_type'])) ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small"><?= h($mlSupport->translate('Total')) ?>:</span>
                                        <span class="fw-bold"><?= h($s['total']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small"><?= h($mlSupport->translate('Success')) ?>:</span>
                                        <span class="text-success fw-bold"><?= h($s['success']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small"><?= h($mlSupport->translate('Failures')) ?>:</span>
                                        <span class="text-danger fw-bold"><?= h($s['failure']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small"><?= h($mlSupport->translate('Errors')) ?>:</span>
                                        <span class="text-warning fw-bold"><?= h($s['error']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Logs Table -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3">
                                <h4 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Recent Integration Activity')) ?></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th><?= h($mlSupport->translate('Timestamp')) ?></th>
                                                <th><?= h($mlSupport->translate('Integration')) ?></th>
                                                <th><?= h($mlSupport->translate('Status')) ?></th>
                                                <th><?= h($mlSupport->translate('Details')) ?></th>
                                                <th><?= h($mlSupport->translate('Actions')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td><?= h(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?></td>
                                                    <td>
                                                        <span class="badge bg-info text-white"><?= h(strtoupper($log['integration_type'])) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['status'] == 'success'): ?>
                                                            <span class="badge bg-success"><?= h($mlSupport->translate('SUCCESS')) ?></span>
                                                        <?php elseif ($log['status'] == 'failure'): ?>
                                                            <span class="badge bg-danger"><?= h($mlSupport->translate('FAILURE')) ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning"><?= h($mlSupport->translate('ERROR')) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-truncate" style="max-width: 300px;">
                                                        <?= h($log['error_message'] ?: $mlSupport->translate('Processed successfully')) ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 shadow-sm"
                                                            onclick='showLogDetail(<?= json_encode($log) ?>)'>
                                                            <i class="fas fa-eye me-1"></i> <?= h($mlSupport->translate('View')) ?>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($logs)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted"><?= h($mlSupport->translate('No activity logs found.')) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                    <div class="mt-4">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination justify-content-center">
                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                        <a class="page-link border-0 shadow-sm mx-1 rounded" href="?page=<?= $i ?>"><?= h($i) ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div class="tab-pane" id="settings-tab">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Configure API Credentials')) ?></h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?= getCsrfField() ?>
                            <input type="hidden" name="update_settings" value="1">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3 border-bottom pb-2 fw-bold text-primary"><i class="fab fa-google me-2"></i><?= h($mlSupport->translate('Google Cloud (Drive/Sheets)')) ?></h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Client ID')) ?></label>
                                        <input type="text" name="google_drive_client_id" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['google_drive_client_id'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Client Secret')) ?></label>
                                        <input type="password" name="google_drive_client_secret" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['google_drive_client_secret'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Refresh Token')) ?></label>
                                        <textarea name="google_drive_refresh_token" class="form-control shadow-sm border-0 bg-light" rows="2"><?= h($settings['google_drive_refresh_token'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Default Spreadsheet ID')) ?></label>
                                        <input type="text" name="google_sheets_key" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['google_sheets_key'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3 border-bottom pb-2 fw-bold text-success"><i class="fas fa-comment-alt me-2"></i><?= h($mlSupport->translate('Messaging & Notifications')) ?></h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('WhatsApp API Key')) ?></label>
                                        <input type="text" name="whatsapp_api" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['whatsapp_api'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Slack Webhook URL')) ?></label>
                                        <input type="text" name="slack_webhook_url" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['slack_webhook_url'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Telegram Bot Token')) ?></label>
                                        <input type="text" name="telegram_bot_token" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['telegram_bot_token'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Telegram Chat ID')) ?></label>
                                        <input type="text" name="telegram_chat_id" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['telegram_chat_id'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3 border-bottom pb-2 fw-bold text-warning"><i class="fas fa-envelope me-2"></i><?= h($mlSupport->translate('Email (SMTP)')) ?></h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('SMTP Host')) ?></label>
                                        <input type="text" name="email_host" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['email_host'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('SMTP User')) ?></label>
                                        <input type="text" name="email_user" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['email_user'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('SMTP Password')) ?></label>
                                        <input type="password" name="email_pass" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['email_pass'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3 border-bottom pb-2 fw-bold text-info"><i class="fas fa-plug me-2"></i><?= h($mlSupport->translate('Other Integrations')) ?></h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('SMS API Key')) ?></label>
                                        <input type="text" name="sms_api" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['sms_api'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium"><?= h($mlSupport->translate('External CRM API Key')) ?></label>
                                        <input type="text" name="crm_api" class="form-control shadow-sm border-0 bg-light" value="<?= h($settings['crm_api'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm border-0 fw-bold"><?= h($mlSupport->translate('Save Settings')) ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Webhooks Tab -->
            <div class="tab-pane" id="webhooks-tab">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Outgoing Webhooks')) ?></h4>
                        <button type="button" class="btn btn-primary shadow-sm border-0 fw-bold" data-bs-toggle="modal" data-bs-target="#addWebhookModal">
                            <i class="fas fa-plus me-1"></i> <?= h($mlSupport->translate('Add Webhook')) ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th><?= h($mlSupport->translate('Name')) ?></th>
                                        <th><?= h($mlSupport->translate('Target URL')) ?></th>
                                        <th><?= h($mlSupport->translate('Events')) ?></th>
                                        <th><?= h($mlSupport->translate('Status')) ?></th>
                                        <th><?= h($mlSupport->translate('Actions')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($webhooks as $webhook): ?>
                                        <tr>
                                            <td class="fw-bold"><?= h($webhook['name']) ?></td>
                                            <td class="text-muted small"><?= h($webhook['url']) ?></td>
                                            <td>
                                                <?php
                                                $events = is_array($webhook['events']) ? $webhook['events'] : json_decode($webhook['events'], true);
                                                if ($events):
                                                    foreach ($events as $event): ?>
                                                        <span class="badge bg-light text-dark border me-1 mb-1"><?= h($event) ?></span>
                                                <?php endforeach;
                                                endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($webhook['enabled']): ?>
                                                    <span class="badge bg-success-light text-success"><?= h($mlSupport->translate('Active')) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-light text-danger"><?= h($mlSupport->translate('Inactive')) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <form method="POST" onsubmit="return confirm('<?= h($mlSupport->translate('Are you sure you want to delete this webhook?')) ?>');">
                                                        <?= getCsrfField() ?>
                                                        <input type="hidden" name="action" value="delete_webhook">
                                                        <input type="hidden" name="id" value="<?= h($webhook['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 shadow-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($webhooks)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-plug fa-2x mb-3 d-block"></i>
                                                <?= h($mlSupport->translate('No webhooks configured yet.')) ?>
                                            </td>
                                        </tr>
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

</div>

<!-- Add Webhook Modal -->
<div class="modal fade" id="addWebhookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><?= h($mlSupport->translate('Add New Webhook')) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add_webhook">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Webhook Name')) ?></label>
                        <input type="text" name="name" class="form-control shadow-sm border-0 bg-light" required placeholder="<?= h($mlSupport->translate('e.g. Zapier Integration')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Target URL')) ?></label>
                        <input type="url" name="url" class="form-control shadow-sm border-0 bg-light" required placeholder="https://hooks.zapier.com/...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Secret Key (Optional)')) ?></label>
                        <input type="text" name="secret" class="form-control shadow-sm border-0 bg-light" placeholder="<?= h($mlSupport->translate('Leave blank to auto-generate')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Trigger Events')) ?></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="events[]" value="security.incident" id="ev1" checked>
                            <label class="form-check-label" for="ev1"><?= h($mlSupport->translate('Security Incident')) ?></label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="events[]" value="backup.complete" id="ev2">
                            <label class="form-check-label" for="ev2"><?= h($mlSupport->translate('Backup Complete')) ?></label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="events[]" value="system.alert" id="ev3">
                            <label class="form-check-label" for="ev3"><?= h($mlSupport->translate('System Alert')) ?></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light shadow-sm border-0" data-bs-dismiss="modal"><?= h($mlSupport->translate('Cancel')) ?></button>
                    <button type="submit" class="btn btn-primary shadow-sm border-0 px-4 fw-bold"><?= h($mlSupport->translate('Create Webhook')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for details -->
<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title fw-bold"><?= h($mlSupport->translate('Activity Detail')) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-muted mb-3 text-uppercase small"><?= h($mlSupport->translate('Payload Sent')) ?>:</h6>
                <pre class="bg-light p-3 border-0 rounded shadow-sm mb-4"><code id="modalPayload" class="small"></code></pre>

                <h6 class="fw-bold text-muted mb-3 text-uppercase small"><?= h($mlSupport->translate('Response Received')) ?>:</h6>
                <pre class="bg-light p-3 border-0 rounded shadow-sm mb-4"><code id="modalResponse" class="small"></code></pre>

                <div id="modalErrorContainer" class="mt-4" style="display:none;">
                    <h6 class="fw-bold text-danger mb-3 text-uppercase small"><?= h($mlSupport->translate('Error Message')) ?>:</h6>
                    <div class="alert alert-danger border-0 shadow-sm" id="modalError"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';

    function h(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showLogDetail(log) {
        document.getElementById('modalPayload').textContent = JSON.stringify(JSON.parse(log.payload), null, 2);
        document.getElementById('modalResponse').textContent = log.response ? JSON.stringify(JSON.parse(log.response), null, 2) : 'No response recorded';

        if (log.error_message) {
            document.getElementById('modalError').textContent = log.error_message;
            document.getElementById('modalErrorContainer').style.display = 'block';
        } else {
            document.getElementById('modalErrorContainer').style.display = 'none';
        }

        var modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
        modal.show();
    }
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>