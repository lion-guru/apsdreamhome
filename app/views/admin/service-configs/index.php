<?php
$groups = $groups ?? [];
$services = $services ?? [];
$serviceLabels = [
    'aws_s3' => 'AWS S3 / Cloud Storage',
    'google_calendar' => 'Google Calendar',
    'smtp' => 'SMTP / Email',
    'twilio' => 'Twilio (Voice/SMS/WhatsApp)',
    'razorpay' => 'Razorpay (Payments)',
    'exchange_rate' => 'Exchange Rate API',
    'gstn' => 'GSTN Portal',
    'tin' => 'TIN Portal',
    'leegality' => 'Leegality (eSign)',
    'general' => 'General',
];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Service Configuration</h1>
            <small class="text-muted">Manage API keys, credentials, and service settings from the dashboard.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL . '/admin/service-configs/history' ?>" class="btn btn-outline-secondary"><i class="fas fa-history me-2"></i>Audit History</a>
        </div>
    </div>

    <?php if ($saved): ?><div class="alert alert-success"><?= htmlspecialchars($saved) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" action="<?= BASE_URL . '/admin/service-configs/update' ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <?php foreach ($groups as $groupName => $groupServices): ?>
        <div class="card mb-4">
            <div class="card-header fw-bold">
                <i class="fas fa-folder me-2"></i><?= htmlspecialchars($groupName) ?> <span class="badge bg-secondary"><?= count($groupServices) ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th style="width:25%">Key</th><th>Value</th><th style="width:80px">Type</th><th style="width:20%">Description</th></tr></thead>
                    <tbody>
                    <?php foreach ($groupServices as $s): ?>
                        <?php $sName = htmlspecialchars($s['service_name']); $sKey = htmlspecialchars($s['config_key']); $sVal = htmlspecialchars((string)($s['config_value'] ?? '')); $sType = htmlspecialchars($s['config_type']); ?>
                        <tr>
                            <td><code><?= $sKey ?></code>
                                <?php if ($s['is_secret']): ?><span class="badge bg-danger ms-1">secret</span><?php endif; ?></td>
                            <td>
                                <?php if ($s['config_type'] === 'password' || $s['is_secret']): ?>
                                    <?php $sDefault = $defaults[$s['service_name']][$s['config_key']]['placeholder'] ?? ''; ?>
                                    <input type="password" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-control form-control-sm" value="<?= $sVal ?>" placeholder="<?= $sDefault ? 'Default: ' . htmlspecialchars($sDefault) : 'Enter value' ?>">
                                <?php elseif ($s['config_type'] === 'boolean'): ?>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="configs[<?= $sName ?>][<?= $sKey ?>]" value="0">
                                        <input type="checkbox" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-check-input" value="1" <?= ($s['config_value'] === '1' || $s['config_value'] === 'true') ? 'checked' : '' ?>>
                                    </div>
                                <?php else: ?>
                                    <?php $sDefault = $defaults[$s['service_name']][$s['config_key']]['value'] ?? ''; ?>
                                    <input type="text" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-control form-control-sm" value="<?= $sVal ?>" placeholder="<?= $sDefault ? 'Default: ' . htmlspecialchars($sDefault) : 'Enter value' ?>">
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= $sType ?></span></td>
                            <td><small class="text-muted"><?= htmlspecialchars($s['description'] ?? '') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted"><?= htmlspecialchars($serviceLabels[$groupServices[0]['service_name']] ?? $groupServices[0]['service_name']) ?></small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="testService('<?= htmlspecialchars($groupServices[0]['service_name']) ?>')"><i class="fas fa-plug me-1"></i>Test</button>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetService('<?= htmlspecialchars($groupServices[0]['service_name']) ?>')"><i class="fas fa-undo me-1"></i>Reset</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save All Changes</button>
            <a href="<?= BASE_URL . '/admin/service-configs' ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function testService(service) {
    fetch('<?= BASE_URL . '/admin/service-configs/test/' ?>' + service, {
        method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(r => r.json()).then(d => {
        alert(d.service.toUpperCase() + ': ' + d.message);
    }).catch(() => alert('Test failed'));
}
function resetService(service) {
    if (!confirm('Reset ' + service + ' to default values? This will clear current credentials.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL . '/admin/service-configs/reset/' ?>' + service;
    const token = document.createElement('input');
    token.type = 'hidden'; token.name = 'csrf_token';
    token.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
</script>
