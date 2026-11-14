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
$serviceIcons = [
    'aws_s3' => 'fab fa-aws',
    'google_calendar' => 'fab fa-google',
    'smtp' => 'fas fa-envelope',
    'twilio' => 'fas fa-phone-alt',
    'razorpay' => 'fas fa-credit-card',
    'exchange_rate' => 'fas fa-exchange-alt',
    'gstn' => 'fas fa-file-invoice',
    'tin' => 'fas fa-id-card',
    'leegality' => 'fas fa-file-signature',
    'general' => 'fas fa-cog',
];
$serviceColors = [
    'aws_s3' => '#ff9900',
    'google_calendar' => '#4285f4',
    'smtp' => '#ea4335',
    'twilio' => '#f22f46',
    'razorpay' => '#3395ff',
    'exchange_rate' => '#00b4d8',
    'gstn' => '#138808',
    'tin' => '#000080',
    'leegality' => '#6f42c1',
    'general' => '#6c757d',
];
?>

<style>
.svc-card { border: 1px solid #dee2e6; border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem; background: #fff; }
.svc-header { padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #dee2e6; background: linear-gradient(135deg, #f8f9fa, #fff); }
.svc-header .svc-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; margin-right: 0.75rem; }
.svc-header .svc-title { font-weight: 700; font-size: 1.05rem; margin: 0; }
.svc-header .svc-subtitle { font-size: 0.78rem; color: #6c757d; margin: 0; }
.svc-header .svc-status { display: flex; align-items: center; gap: 0.5rem; }
.svc-status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
.svc-status-dot.configured { background: #28a745; box-shadow: 0 0 6px rgba(40,167,69,0.5); }
.svc-status-dot.not-configured { background: #dc3545; box-shadow: 0 0 6px rgba(220,53,69,0.5); }
.svc-body { padding: 1rem 1.25rem; }
.svc-field { margin-bottom: 0.75rem; }
.svc-field label { font-size: 0.8rem; font-weight: 600; color: #495057; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem; }
.svc-field .form-control { border-radius: 6px; font-size: 0.85rem; }
.svc-field .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.15); }
.svc-field .form-check-input { margin-top: 0.15rem; }
.svc-footer { padding: 0.75rem 1.25rem; background: #f8f9fa; border-top: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
.svc-footer .svc-note { font-size: 0.75rem; color: #6c757d; }
.svc-footer .svc-actions { display: flex; gap: 0.5rem; }
.svc-badge { font-size: 0.65rem; padding: 0.2rem 0.5rem; border-radius: 4px; }
.svc-alert { border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; }
.svc-section-title { font-size: 1.25rem; font-weight: 700; color: #212529; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
.svc-section-title i { color: #0d6efd; }
.svc-empty { text-align: center; padding: 2rem; color: #6c757d; }
.svc-empty i { font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.4; }
.svc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
.svc-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
@media (max-width: 768px) { .svc-field-row { grid-template-columns: 1fr; } }
.svc-test-result { margin-top: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.8rem; display: none; }
.svc-test-result.show { display: block; }
.svc-test-result.success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
.svc-test-result.error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
.svc-test-result.loading { background: #cff4fc; color: #055160; border: 1px solid #b6effb; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0"><i class="fas fa-cogs me-2 text-primary"></i>Service Configuration</h1>
            <small class="text-muted">Manage API keys, credentials, and service settings from the dashboard.</small>
        </div>
        <a href="<?= BASE_URL . '/admin/service-configs/history' ?>" class="btn btn-outline-secondary">
            <i class="fas fa-history me-2"></i>Audit History
        </a>
    </div>

    <?php if ($saved): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($saved) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL . '/admin/service-configs/update' ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <?php foreach ($groups as $groupName => $groupServices): ?>
        <?php
            $firstSvc = $groupServices[0] ?? [];
            $svcName = $firstSvc['service_name'] ?? $groupName;
            $svcLabel = $serviceLabels[$svcName] ?? ucfirst(str_replace('_', ' ', $svcName));
            $svcIcon = $serviceIcons[$svcName] ?? 'fas fa-cog';
            $svcColor = $serviceColors[$svcName] ?? '#6c757d';
            // Check if configured (any non-empty value)
            $isConfigured = false;
            foreach ($groupServices as $s) {
                if (!empty($s['config_value']) && $s['config_value'] !== 'Enter value') {
                    $isConfigured = true;
                    break;
                }
            }
        ?>
        <div class="svc-card" id="svc-<?= htmlspecialchars($svcName) ?>">
            <div class="svc-header">
                <div class="d-flex align-items-center">
                    <div class="svc-icon" style="background: <?= $svcColor ?>;">
                        <i class="<?= $svcIcon ?>"></i>
                    </div>
                    <div>
                        <h5 class="svc-title"><?= htmlspecialchars($svcLabel) ?></h5>
                        <p class="svc-subtitle"><?= htmlspecialchars($groupName) ?> group • <?= count($groupServices) ?> config(s)</p>
                    </div>
                </div>
                <div class="svc-status">
                    <span class="svc-status-dot <?= $isConfigured ? 'configured' : 'not-configured' ?>"></span>
                    <span class="small <?= $isConfigured ? 'text-success' : 'text-danger' ?>">
                        <?= $isConfigured ? 'Configured' : 'Not Configured' ?>
                    </span>
                    <div class="btn-group btn-group-sm ms-2">
                        <button type="button" class="btn btn-outline-info" onclick="testService('<?= htmlspecialchars($svcName) ?>')" title="Test connection">
                            <i class="fas fa-plug"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="resetService('<?= htmlspecialchars($svcName) ?>')" title="Reset to defaults">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="svc-body">
                <?php if (count($groupServices) === 1): ?>
                <!-- Single config: full width -->
                <?php $s = $groupServices[0]; $sName = htmlspecialchars($s['service_name']); $sKey = htmlspecialchars($s['config_key']); $sVal = htmlspecialchars((string)($s['config_value'] ?? '')); $sType = htmlspecialchars($s['config_type']); ?>
                <div class="svc-field">
                    <label for="cfg-<?= $sName ?>-<?= $sKey ?>">
                        <?= htmlspecialchars($s['config_key']) ?>
                        <?php if ($s['is_secret']): ?><span class="badge bg-danger svc-badge">secret</span><?php endif; ?>
                        <?php if (!empty($s['description'])): ?>
                        <i class="fas fa-info-circle text-muted" title="<?= htmlspecialchars($s['description']) ?>"></i>
                        <?php endif; ?>
                    </label>
                    <?php if ($s['config_type'] === 'password' || $s['is_secret']): ?>
                        <?php $sDefault = $defaults[$s['service_name']][$s['config_key']]['placeholder'] ?? ''; ?>
                        <input type="password" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-control" value="<?= $sVal ?>" placeholder="<?= $sDefault ? 'Default: ' . htmlspecialchars($sDefault) : 'Enter value' ?>">
                    <?php elseif ($s['config_type'] === 'boolean'): ?>
                        <div class="form-check form-switch mt-1">
                            <input type="hidden" name="configs[<?= $sName ?>][<?= $sKey ?>]" value="0">
                            <input type="checkbox" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-check-input" value="1" <?= ($s['config_value'] === '1' || $s['config_value'] === 'true') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cfg-<?= $sName ?>-<?= $sKey ?>"><?= htmlspecialchars($s['description'] ?? 'Enable') ?></label>
                        </div>
                    <?php else: ?>
                        <?php $sDefault = $defaults[$s['service_name']][$s['config_key']]['value'] ?? ''; ?>
                        <input type="text" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-control" value="<?= $sVal ?>" placeholder="<?= $sDefault ? 'Default: ' . htmlspecialchars($sDefault) : 'Enter value' ?>">
                    <?php endif; ?>
                    <?php if (!empty($s['description'])): ?>
                    <small class="text-muted"><?= htmlspecialchars($s['description']) ?></small>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <!-- Multiple configs: grid layout -->
                <div class="svc-grid">
                    <?php foreach ($groupServices as $s): ?>
                    <?php $sName = htmlspecialchars($s['service_name']); $sKey = htmlspecialchars($s['config_key']); $sVal = htmlspecialchars((string)($s['config_value'] ?? '')); ?>
                    <div class="svc-field">
                        <label for="cfg-<?= $sName ?>-<?= $sKey ?>">
                            <?= htmlspecialchars($s['config_key']) ?>
                            <?php if ($s['is_secret']): ?><span class="badge bg-danger svc-badge">secret</span><?php endif; ?>
                        </label>
                        <?php if ($s['config_type'] === 'password' || $s['is_secret']): ?>
                            <?php $sDefault = $defaults[$s['service_name']][$s['config_key']]['placeholder'] ?? ''; ?>
                            <input type="password" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-control" value="<?= $sVal ?>" placeholder="<?= $sDefault ? 'Default: ' . htmlspecialchars($sDefault) : 'Enter value' ?>">
                        <?php elseif ($s['config_type'] === 'boolean'): ?>
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="configs[<?= $sName ?>][<?= $sKey ?>]" value="0">
                                <input type="checkbox" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-check-input" value="1" <?= ($s['config_value'] === '1' || $s['config_value'] === 'true') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cfg-<?= $sName ?>-<?= $sKey ?>"><?= htmlspecialchars($s['description'] ?? 'Enable') ?></label>
                            </div>
                        <?php else: ?>
                            <?php $sDefault = $defaults[$s['service_name']][$s['config_key']]['value'] ?? ''; ?>
                            <input type="text" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-control" value="<?= $sVal ?>" placeholder="<?= $sDefault ? 'Default: ' . htmlspecialchars($sDefault) : 'Enter value' ?>">
                        <?php endif; ?>
                        <?php if (!empty($s['description'])): ?>
                        <small class="text-muted"><?= htmlspecialchars($s['description']) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div id="test-result-<?= htmlspecialchars($svcName) ?>" class="svc-test-result"></div>
            </div>
            <div class="svc-footer">
                <small class="text-muted"><?= htmlspecialchars($serviceLabels[$svcName] ?? $svcName) ?> — <?= count($groupServices) ?> configuration(s)</small>
                <div class="svc-actions">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="testService('<?= htmlspecialchars($svcName) ?>')" title="Test connection">
                        <i class="fas fa-plug me-1"></i>Test
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetService('<?= htmlspecialchars($svcName) ?>')" title="Reset to defaults">
                        <i class="fas fa-undo me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save All Changes</button>
            <a href="<?= BASE_URL . '/admin/service-configs' ?>" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
function testService(service) {
    const resultEl = document.getElementById('test-result-' + service);
    if (resultEl) {
        resultEl.className = 'svc-test-result loading show';
        resultEl.textContent = 'Testing ' + service + '...';
    }
    fetch('<?= BASE_URL . '/admin/service-configs/test/' ?>' + service, {
        method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''}
    }).then(r => r.json()).then(d => {
        if (resultEl) {
            resultEl.className = 'svc-test-result ' + (d.success ? 'success' : 'error') + ' show';
            resultEl.textContent = d.service.toUpperCase() + ': ' + d.message;
        }
    }).catch(() => {
        if (resultEl) {
            resultEl.className = 'svc-test-result error show';
            resultEl.textContent = service.toUpperCase() + ': Test failed (network error)';
        }
    });
}
function resetService(service) {
    if (!confirm('Reset ' + service + ' to default values? This will clear current credentials.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL . '/admin/service-configs/reset/' ?>' + service;
    const token = document.createElement('input');
    token.type = 'hidden'; token.name = 'csrf_token';
    token.value = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>';
    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
</script>
