<?php 
$settings = $settings ?? [];
$roles = ['admin', 'manager', 'associate', 'agent', 'telecaller'];
$base = BASE_URL ?? '';
$val = fn($key, $default = '1') => htmlspecialchars($settings[$key] ?? $default);
?>
<div class="container-fluid py-4">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.crm-settings-header { background: linear-gradient(135deg, #7c3aed, #a78bfa); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.setting-card { border: none; border-radius: 12px; transition: transform 0.2s; }
.setting-card:hover { transform: translateY(-2px); }
.setting-section { background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 1px solid #e2e8f0; }
.toggle-switch { width: 50px; height: 26px; border-radius: 13px; background: #cbd5e1; position: relative; cursor: pointer; transition: background 0.3s; }
.toggle-switch.active { background: #10b981; }
.toggle-switch::after { content: ''; position: absolute; width: 22px; height: 22px; border-radius: 50%; background: #fff; top: 2px; left: 2px; transition: transform 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle-switch.active::after { transform: translateX(24px); }
</style>

<div class="crm-settings-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-cogs me-2"></i>CRM Settings & Controls</h4>
            <p class="mb-0 mt-1 style-91394">Configure CRM features, permissions, and automation for your team</p>
        </div>
        <div>
            <span class="badge bg-white text-dark style-51894"><i class="fas fa-shield-alt me-1"></i>SaaS-Ready</span>
        </div>
    </div>
</div>

<form method="POST" action="<?= $base ?>/admin/settings/crm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <!-- Global CRM Toggle -->
    <div class="card setting-card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 style-90386"><i class="fas fa-power-off me-2"></i>Global CRM</h6>
        </div>
        <div class="card-body">
            <div class="setting-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Enable CRM Module</div>
                        <small class="text-muted">Master toggle — disabling hides CRM from all users</small>
                    </div>
                    <div class="toggle-switch <?= $val('crm_enabled') === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('crm_enabled').value = this.classList.contains('active') ? '1' : '0';">
                        <input type="hidden" id="crm_enabled" name="crm_enabled" value="<?= $val('crm_enabled') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Permissions -->
    <div class="card setting-card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 style-90386"><i class="fas fa-user-shield me-2"></i>Lead Permissions</h6>
        </div>
        <div class="card-body">
            <div class="setting-section mb-3">
                <label class="fw-semibold mb-2">Roles that can CREATE leads</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($roles as $role): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="crm_lead_create_roles[]" value="<?= $role ?>" id="create_<?= $role ?>" <?= in_array($role, explode(',', $settings['crm_lead_create_roles'] ?? 'admin,manager,associate,agent')) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="create_<?= $role ?>"><?= ucfirst($role) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="setting-section">
                <label class="fw-semibold mb-2">Roles that can DELETE leads</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($roles as $role): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="crm_lead_delete_roles[]" value="<?= $role ?>" id="delete_<?= $role ?>" <?= in_array($role, explode(',', $settings['crm_lead_delete_roles'] ?? 'admin,manager')) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="delete_<?= $role ?>"><?= ucfirst($role) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Assignment -->
    <div class="card setting-card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 style-90386"><i class="fas fa-random me-2"></i>Auto-Assignment</h6>
        </div>
        <div class="card-body">
            <div class="setting-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">Enable Auto-Assignment</div>
                        <small class="text-muted">Automatically assign new leads to available agents</small>
                    </div>
                    <div class="toggle-switch <?= $val('crm_auto_assign_enabled') === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('crm_auto_assign').value = this.classList.contains('active') ? '1' : '0';">
                        <input type="hidden" id="crm_auto_assign" name="crm_auto_assign_enabled" value="<?= $val('crm_auto_assign_enabled') ?>">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">Require Attendance Clock-in</div>
                        <small class="text-muted">Only assign leads if telecaller is marked present</small>
                    </div>
                    <div class="toggle-switch <?= $val('crm_require_attendance', '0') === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('crm_require_attendance').value = this.classList.contains('active') ? '1' : '0';">
                        <input type="hidden" id="crm_require_attendance" name="crm_require_attendance" value="<?= $val('crm_require_attendance', '0') ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="fw-semibold mb-2">Assignment Method</label>
                    <select name="crm_lead_assignment_strategy" class="form-select">
                        <option value="round_robin" <?= $val('crm_lead_assignment_strategy', 'round_robin') === 'round_robin' ? 'selected' : '' ?>>Round Robin (Equal Distribution)</option>
                        <option value="least_burdened" <?= $val('crm_lead_assignment_strategy') === 'least_burdened' ? 'selected' : '' ?>>Least Loaded (Fewest Active Leads)</option>
                    </select>
                </div>
                
                <div>
                    <label class="fw-semibold mb-2">Daily Lead Cap</label>
                    <input type="number" name="crm_daily_lead_cap" class="form-control" value="<?= $val('crm_daily_lead_cap', '50') ?>" min="1" max="1000">
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Scoring -->
    <div class="card setting-card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 style-90386"><i class="fas fa-star me-2"></i>Lead Scoring</h6>
        </div>
        <div class="card-body">
            <div class="setting-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">Enable Auto-Scoring</div>
                        <small class="text-muted">Automatically score leads based on engagement and profile</small>
                    </div>
                    <div class="toggle-switch <?= $val('crm_scoring_enabled') === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('crm_scoring').value = this.classList.contains('active') ? '1' : '0';">
                        <input type="hidden" id="crm_scoring" name="crm_scoring_enabled" value="<?= $val('crm_scoring_enabled') ?>">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="fw-semibold">Hot Lead Threshold</label>
                        <div class="input-group">
                            <input type="number" name="crm_scoring_hot_threshold" class="form-control" value="<?= $val('crm_scoring_hot_threshold', '70') ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-semibold">Warm Lead Threshold</label>
                        <div class="input-group">
                            <input type="number" name="crm_scoring_warm_threshold" class="form-control" value="<?= $val('crm_scoring_warm_threshold', '40') ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Automation Features -->
    <div class="card setting-card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 style-90386"><i class="fas fa-robot me-2"></i>Automation Features</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $autoFeatures = [
                    ['key' => 'crm_drip_enabled', 'label' => 'Drip Campaigns', 'desc' => 'Automated email/SMS sequences for lead nurturing'],
                    ['key' => 'crm_sla_enabled', 'label' => 'SLA Tracking', 'desc' => 'Response time compliance monitoring'],
                    ['key' => 'crm_kanban_enabled', 'label' => 'Kanban Board', 'desc' => 'Visual drag-drop pipeline board'],
                ];
                foreach ($autoFeatures as $feat):
                ?>
                <div class="col-md-4">
                    <div class="setting-section h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= $feat['label'] ?></div>
                                <small class="text-muted"><?= $feat['desc'] ?></small>
                            </div>
                            <div class="toggle-switch <?= $val($feat['key']) === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('<?= $feat['key'] ?>').value = this.classList.contains('active') ? '1' : '0';">
                                <input type="hidden" id="<?= $feat['key'] ?>" name="<?= $feat['key'] ?>" value="<?= $val($feat['key']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- SLA & Data Settings -->
    <div class="card setting-card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 style-90386"><i class="fas fa-clock me-2"></i>SLA & Data Settings</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="fw-semibold">SLA Response Time (hours)</label>
                    <input type="number" name="crm_sla_response_hours" class="form-control" value="<?= $val('crm_sla_response_hours', '24') ?>" min="1">
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold">Trash Retention (days)</label>
                    <input type="number" name="crm_trash_retention_days" class="form-control" value="<?= $val('crm_trash_retention_days', '30') ?>" min="1">
                    <small class="text-muted">Auto-permanent delete after N days</small>
                </div>
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-semibold mb-0">Export Enabled</label>
                        <div class="toggle-switch <?= $val('crm_export_enabled') === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('crm_export').value = this.classList.contains('active') ? '1' : '0';">
                            <input type="hidden" id="crm_export" name="crm_export_enabled" value="<?= $val('crm_export_enabled') ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-semibold mb-0">Import Enabled</label>
                        <div class="toggle-switch <?= $val('crm_import_enabled') === '1' ? 'active' : '' ?>" onclick="this.classList.toggle('active');document.getElementById('crm_import').value = this.classList.contains('active') ? '1' : '0';">
                            <input type="hidden" id="crm_import" name="crm_import_enabled" value="<?= $val('crm_import_enabled') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="text-end mb-4">
        <a href="<?= $base ?>/admin/leads" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save me-2"></i>Save All Settings
        </button>
    </div>
</form>
</div>
