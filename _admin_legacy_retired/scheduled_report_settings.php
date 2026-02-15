<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit();
}
$config_file = __DIR__ . '/scheduled_report_config.json';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cfg = [
        'admin_email' => $_POST['admin_email'] ?? 'admin@example.com',
        'send_summary' => !empty($_POST['send_summary']),
        'send_audit' => !empty($_POST['send_audit']),
        'frequency' => $_POST['frequency'] ?? 'daily',
        'slack_enabled' => !empty($_POST['slack_enabled']),
        'slack_webhook' => trim($_POST['slack_webhook'] ?? ''),
        'twilio_enabled' => !empty($_POST['twilio_enabled']),
        'twilio_sid' => trim($_POST['twilio_sid'] ?? ''),
        'twilio_token' => trim($_POST['twilio_token'] ?? ''),
        'twilio_from' => trim($_POST['twilio_from'] ?? ''),
        'twilio_to' => trim($_POST['twilio_to'] ?? '')
    ];
    file_put_contents($config_file, json_encode($cfg, JSON_PRETTY_PRINT));
    $msg = 'Settings saved!';
}
$cfg = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [
    'admin_email' => 'admin@example.com',
    'send_summary' => true,
    'send_audit' => true,
    'frequency' => 'daily',
    'slack_enabled' => false,
    'slack_webhook' => '',
    'twilio_enabled' => false,
    'twilio_sid' => '',
    'twilio_token' => '',
    'twilio_from' => '',
    'twilio_to' => ''
];
require_once(__DIR__ . '/../includes/templates/header.php');
?>
<div class="container my-4">
  <h2 class="mb-4"><i class="fa fa-calendar-alt me-2"></i>Scheduled Report Settings</h2>
  <?php if (!empty($msg)): ?><div class="alert alert-success">Settings saved!</div><?php endif; ?>
  <form method="post" class="mb-4">
    <div class="mb-3">
      <label class="form-label fw-bold">Recipient Email</label>
      <input type="email" name="admin_email" class="form-control" value="<?php echo htmlspecialchars($cfg['admin_email']); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label fw-bold">Include in Report</label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="send_summary" id="send_summary" value="1" <?php if($cfg['send_summary']) echo 'checked'; ?>>
        <label class="form-check-label" for="send_summary">Analytics Summary</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="send_audit" id="send_audit" value="1" <?php if($cfg['send_audit']) echo 'checked'; ?>>
        <label class="form-check-label" for="send_audit">Blocklist Audit Log</label>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-bold">Frequency</label>
      <select name="frequency" class="form-select">
        <option value="daily" <?php if($cfg['frequency']==='daily') echo 'selected'; ?>>Daily</option>
        <option value="weekly" <?php if($cfg['frequency']==='weekly') echo 'selected'; ?>>Weekly</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label fw-bold">Slack Notifications</label><br>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="slack_enabled" id="slack_enabled" value="1" <?php if(!empty($cfg['slack_enabled'])) echo 'checked'; ?>>
        <label class="form-check-label" for="slack_enabled">Enable Slack notifications for scheduled reports</label>
      </div>
      <input type="url" name="slack_webhook" class="form-control mt-2" placeholder="Slack Incoming Webhook URL" value="<?php echo htmlspecialchars($cfg['slack_webhook'] ?? ''); ?>">
      <div class="form-text">Paste your Slack Incoming Webhook URL here. <a href="https://api.slack.com/messaging/webhooks" target="_blank">How to create a webhook &rarr;</a></div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-bold">Twilio SMS Alerts</label><br>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="twilio_enabled" id="twilio_enabled" value="1" <?php if(!empty($cfg['twilio_enabled'])) echo 'checked'; ?>>
        <label class="form-check-label" for="twilio_enabled">Enable SMS alerts for critical events</label>
      </div>
      <input type="text" name="twilio_sid" class="form-control mt-2" placeholder="Twilio Account SID" value="<?php echo htmlspecialchars($cfg['twilio_sid'] ?? ''); ?>">
      <input type="text" name="twilio_token" class="form-control mt-2" placeholder="Twilio Auth Token" value="<?php echo htmlspecialchars($cfg['twilio_token'] ?? ''); ?>">
      <input type="text" name="twilio_from" class="form-control mt-2" placeholder="Twilio From Number (e.g. +1234567890)" value="<?php echo htmlspecialchars($cfg['twilio_from'] ?? ''); ?>">
      <input type="text" name="twilio_to" class="form-control mt-2" placeholder="Recipient Mobile Number (e.g. +19876543210)" value="<?php echo htmlspecialchars($cfg['twilio_to'] ?? ''); ?>">
      <div class="form-text">Get your credentials from <a href="https://www.twilio.com/console" target="_blank">Twilio Console &rarr;</a></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save Settings</button>
  </form>
  <div class="alert alert-info small">
    <strong>To enable scheduled reports:</strong><br>
    Use Windows Task Scheduler to run <code>php scheduled_report.php</code> once per day or week, as configured above.<br>
    <a href="https://www.windowscentral.com/how-create-automated-task-using-task-scheduler-windows-10" target="_blank">How to use Task Scheduler &rarr;</a>
  </div>
</div>
<?php require_once(__DIR__ . '/../includes/templates/new_footer.php'); ?>
