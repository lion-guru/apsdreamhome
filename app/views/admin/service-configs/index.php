<?php
/**
 * @var array<string, array<int, array>> $groups  group_name => [configs...]
 * @var array<string, array<string, array>> $services  service_name => [key => row]
 * @var string|null $saved  success flash message
 * @var string|null $error  error flash message
 */
$groups  = $groups ?? [];
$services = $services ?? [];
$saved   = $saved ?? null;
$error   = $error ?? null;

$csrfToken = $_SESSION['csrf_token'] ?? '';
$baseUrl   = defined('BASE_URL') ? BASE_URL : '';

// Service icons + labels
$serviceMeta = [
    'general'       => ['icon' => 'fa-cog',           'color' => 'secondary', 'label' => 'General'],
    'integrations'  => ['icon' => 'fa-plug',          'color' => 'info',      'label' => 'Integrations'],
    'payments'      => ['icon' => 'fa-credit-card',   'color' => 'success',   'label' => 'Payments'],
    'tax'           => ['icon' => 'fa-file-invoice',  'color' => 'warning',   'label' => 'Tax & Compliance'],
    'communications'=> ['icon' => 'fa-envelope',      'color' => 'primary',   'label' => 'Communications'],
    'storage'       => ['icon' => 'fa-cloud',         'color' => 'info',      'label' => 'Storage'],
];

// Group icons
$groupIcons = [
    'general'       => ['icon' => 'fa-cog',       'color' => 'secondary'],
    'payments'      => ['icon' => 'fa-credit-card','color' => 'success'],
    'tax'           => ['icon' => 'fa-file-invoice','color' => 'warning'],
    'integrations'  => ['icon' => 'fa-plug',       'color' => 'info'],
    'communications'=> ['icon' => 'fa-envelope',   'color' => 'primary'],
    'storage'       => ['icon' => 'fa-cloud',      'color' => 'info'],
];

$groupLabels = [
    'general'       => 'General',
    'payments'      => 'Payments',
    'tax'           => 'Tax & Compliance',
    'integrations'  => 'Integrations',
    'communications'=> 'Communications',
    'storage'       => 'Storage',
];
?>
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-cogs me-2 text-primary"></i>Service Configuration</h4>
            <p class="text-muted small mb-0">Manage API keys, credentials, and test modes for all integrated services.</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6"><?= count(array_unique(array_column(array_merge(...array_values($groups)), 'service_name'))) ?? 0 ?> Services</span>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if ($saved): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($saved ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i><?= htmlspecialchars($error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="serviceTabs" role="tablist">
        <?php $first = true; foreach ($groups as $groupName => $configs): ?>
            <?php $meta = $groupIcons[$groupName] ?? ['icon' => 'fa-cog', 'color' => 'secondary']; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $first ? 'active' : '' ?>"
                        id="tab-<?= htmlspecialchars($groupName ?? '') ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#panel-<?= htmlspecialchars($groupName ?? '') ?>"
                        type="button" role="tab">
                    <i class="fas <?= $meta['icon'] ?> me-1"></i>
                    <?= htmlspecialchars($groupLabels[$groupName] ?? ucfirst($groupName)) ?>
                    <?php
                    $secretCount = 0;
                    foreach ($configs as $c) { if ($c['is_secret'] && empty($c['config_value'])) $secretCount++; }
                    if ($secretCount > 0): ?>
                        <span class="badge bg-danger ms-1"><?= $secretCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <!-- Tab Content -->
    <form method="POST" action="<?= $baseUrl ?>/admin/service-configs/update">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <div class="tab-content" id="serviceTabContent">
            <?php $first = true; foreach ($groups as $groupName => $configs): ?>
                <div class="tab-pane fade <?= $first ? 'show active' : '' ?>"
                     id="panel-<?= htmlspecialchars($groupName ?? '') ?>" role="tabpanel">

                    <!-- Group header -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <?php $meta = $groupIcons[$groupName] ?? ['icon' => 'fa-cog', 'color' => 'secondary']; ?>
                            <i class="fas <?= $meta['icon'] ?> me-2 text-<?= $meta['color'] ?>"></i>
                            <?= htmlspecialchars($groupLabels[$groupName] ?? ucfirst($groupName)) ?>
                        </h5>
                    </div>

                    <!-- Service cards within this group -->
                    <?php
                    $byService = [];
                    foreach ($configs as $cfg) {
                        $byService[$cfg['service_name']][] = $cfg;
                    }
                    ?>
                    <div class="row g-4 mb-4">
                    <?php foreach ($byService as $svcName => $svcConfigs): ?>
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-capitalize"><?= htmlspecialchars($svcName ?? '') ?></h6>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-label="More options"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item text-info" href="#" onclick="testConnection('<?= htmlspecialchars($svcName ?? '') ?>'); return false;">
                                                    <i class="fas fa-plug me-2"></i>Test Connection
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="<?= $baseUrl ?>/admin/service-configs/reset/<?= htmlspecialchars($svcName ?? '') ?>" data-aps-confirm="Reset all <?= htmlspecialchars($svcName ?? '') ?> configs to defaults?">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-undo me-2"></i>Reset to Defaults
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body aps-cp-card-body">
                                    <?php foreach ($svcConfigs as $cfg): ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small">
                                                <?= htmlspecialchars($cfg['description'] ?: ucfirst(str_replace('_', ' ', $cfg['config_key'] ?? ''))) ?>
                                                <?php if ($cfg['is_secret']): ?>
                                                    <span class="badge bg-danger ms-1">Secret</span>
                                                <?php endif; ?>
                                            </label>

                                            <?php if ($cfg['config_type'] === 'boolean'): ?>
                                                <!-- Toggle switch for booleans -->
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="configs[<?= htmlspecialchars($cfg['service_name'] ?? '') ?>][<?= htmlspecialchars($cfg['config_key'] ?? '') ?>]" value="0">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="configs[<?= htmlspecialchars($cfg['service_name'] ?? '') ?>][<?= htmlspecialchars($cfg['config_key'] ?? '') ?>]"
                                                           value="1"
                                                           id="cfg-<?= htmlspecialchars($cfg['service_name'] ?? '') ?>-<?= htmlspecialchars($cfg['config_key'] ?? '') ?>"
                                                           <?= ($cfg['config_value'] == '1') ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="cfg-<?= htmlspecialchars($cfg['service_name'] ?? '') ?>-<?= htmlspecialchars($cfg['config_key'] ?? '') ?>">
                                                        <?= ($cfg['config_value'] == '1') ? '<span class="text-success fw-semibold">Enabled</span>' : '<span class="text-muted">Disabled</span>' ?>
                                                    </label>
                                                </div>

                                            <?php elseif ($cfg['config_type'] === 'number'): ?>
                                                <input type="number"
                                                       class="form-control form-control-sm"
                                                       name="configs[<?= htmlspecialchars($cfg['service_name'] ?? '') ?>][<?= htmlspecialchars($cfg['config_key'] ?? '') ?>]"
                                                       value="<?= htmlspecialchars($cfg['config_value'] ?? '') ?>">

                                            <?php elseif ($cfg['is_secret']): ?>
                                                <!-- Password field with reveal toggle -->
                                                <div class="input-group input-group-sm">
                                                    <input type="password"
                                                           class="form-control secret-field"
                                                           name="configs[<?= htmlspecialchars($cfg['service_name'] ?? '') ?>][<?= htmlspecialchars($cfg['config_key'] ?? '') ?>]"
                                                           value="<?= htmlspecialchars($cfg['config_value'] ?? '') ?>"
                                                           placeholder="Enter value..."
                                                           id="cfg-<?= htmlspecialchars($cfg['service_name'] ?? '') ?>-<?= htmlspecialchars($cfg['config_key'] ?? '') ?>">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            onclick="toggleSecret('cfg-<?= htmlspecialchars($cfg['service_name'] ?? '') ?>-<?= htmlspecialchars($cfg['config_key'] ?? '') ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>

                                            <?php else: ?>
                                                <input type="text"
                                                       class="form-control form-control-sm"
                                                       name="configs[<?= htmlspecialchars($cfg['service_name'] ?? '') ?>][<?= htmlspecialchars($cfg['config_key'] ?? '') ?>]"
                                                       value="<?= htmlspecialchars($cfg['config_value'] ?? '') ?>"
                                                       placeholder="Enter value...">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php $first = false; endforeach; ?>
        </div>

        <!-- Save button -->
        <div class="sticky-bottom bg-white border-top py-3 px-4 d-flex justify-content-end style-9755">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i>Save Configuration
            </button>
        </div>
    </form>
</div>

<!-- Test connection modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plug me-2"></i>Connection Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="testResult">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Testing...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSecret(id) {
    const el = document.getElementById(id);
    const icon = el.parentElement.querySelector('i');
    if (el.type === 'password') {
        el.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        el.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function testConnection(service) {
    const modal = new bootstrap.Modal(document.getElementById('testModal'));
    document.getElementById('testResult').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div><p class="mt-2">Testing ' + service + '...</p></div>';
    modal.show();

    fetch('<?= $baseUrl ?>/admin/service-configs/test/' + service, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
    .catch(err => console.error('Request failed:', err));
        const statusBadge = data.status === 'ok'
            ? '<span class="badge bg-success fs-6">OK</span>'
            : '<span class="badge bg-danger fs-6">ERROR</span>';

        document.getElementById('testResult').innerHTML = `
            <div class="text-center mb-3">${statusBadge}</div>
            <p><strong>Service:</strong> ${data.service}</p>
            <p><strong>Test Mode:</strong> ${data.test_mode ? 'Yes' : 'No'}</p>
            <p><strong>Has Keys:</strong> ${data.has_keys ? 'Yes' : 'No'}</p>
            <p><strong>Message:</strong> ${data.message}</p>
        `;
    })
    .catch(err => {
        document.getElementById('testResult').innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
    });
}

// Auto-dismiss alerts after 5s
setTimeout(() => {
    document.querySelectorAll('.alert-dismissible').forEach(el => {
        const close = el.querySelector('.btn-close');
        if (close) close.click();
    });
}, 5000);
</script>
