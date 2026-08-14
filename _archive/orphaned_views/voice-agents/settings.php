<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-cog me-2"></i> Voice Agent Settings</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-users/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Agent Cards -->
        <div class="col-md-7 mb-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-robot me-1"></i> AI Calling users</h5>

            <?php if (empty($users)): ?>
            <div class="text-center py-4">
                <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                <p class="text-muted">No users configured</p>
            </div>
            <?php else: ?>
            <?php foreach ($users as $agent): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-<?= ($agent['status'] ?? '') === 'active' ? 'success' : (($agent['status'] ?? '') === 'busy' ? 'warning' : 'secondary') ?> text-white d-flex align-items-center justify-content-center me-3" class="style-65746">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($agent['agent_name'] ?? 'Unknown') ?></h6>
                                    <small class="text-muted">ID: <?= htmlspecialchars($agent['agent_id'] ?? '-') ?></small>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 small text-muted">
                                <span><i class="fas fa-language me-1"></i>
                                    <?php
                                    $langs = json_decode($agent['languages'] ?? '[]', true);
                                    echo !empty($langs) ? implode(', ', $langs) : 'EN';
                                    ?>
                                </span>
                                <span><i class="fas fa-phone me-1"></i> <?= (int)($agent['total_calls_made'] ?? 0) ?> calls</span>
                                <span><i class="fas fa-check-circle text-success me-1"></i> <?= (int)($agent['successful_calls'] ?? 0) ?> success</span>
                                <span><i class="fas fa-clock me-1"></i> <?= gmdate('i:s', (int)($agent['avg_call_duration'] ?? 0)) ?> avg</span>
                                <span><i class="fas fa-percentage me-1"></i> <?= $agent['conversion_rate'] ?? 0 ?>% conv</span>
                            </div>
                        </div>

                        <div class="col-auto">
                            <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/settings" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="agent_id" value="<?= htmlspecialchars($agent['agent_id']) ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small"><?= (int)($agent['current_calls'] ?? 0) ?>/<?= (int)($agent['max_concurrent_calls'] ?? 5) ?></span>
                                    <div class="progress" class="style-27818">
                                        <?php $pct = ($agent['max_concurrent_calls'] ?? 5) > 0 ? min(100, round((($agent['current_calls'] ?? 0) / $agent['max_concurrent_calls']) * 100)) : 0; ?>
                                        <div class="progress-bar bg-<?= $pct > 80 ? 'danger' : ($pct > 50 ? 'warning' : 'success') ?>" class="style-21859"></div>
                                    </div>
                                    <select name="agent_status" class="form-select form-select-sm" class="style-30246" onchange="this.form.submit()">
                                        <option value="active" <?= ($agent['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="busy" <?= ($agent['status'] ?? '') === 'busy' ? 'selected' : '' ?>>Busy</option>
                                        <option value="paused" <?= ($agent['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                                        <option value="offline" <?= ($agent['status'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Config Forms -->
        <div class="col-md-5 mb-4">
            <!-- Voice Provider Config -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-microphone me-1"></i> Voice Provider Configuration</h6>
                </div>
                <div class="card-body">
                    <?php
                    $vSettings = [];
                    if (!empty($voice_settings['settings'])) {
                        $vSettings = json_decode($voice_settings['settings'], true) ?? [];
                    }
                    ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/settings">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Provider</label>
                            <select name="voice_provider" class="form-select">
                                <option value="twilio" <?= ($vSettings['provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                                <option value="vapi" <?= ($vSettings['provider'] ?? '') === 'vapi' ? 'selected' : '' ?>>Vapi</option>
                                <option value="plivo" <?= ($vSettings['provider'] ?? '') === 'plivo' ? 'selected' : '' ?>>Plivo</option>
                                <option value="exotel" <?= ($vSettings['provider'] ?? '') === 'exotel' ? 'selected' : '' ?>>Exotel</option>
                                <option value="knowlarity" <?= ($vSettings['provider'] ?? '') === 'knowlarity' ? 'selected' : '' ?>>Knowlarity</option>
                                <option value="custom" <?= ($vSettings['provider'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom SIP</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">API Key / Auth Token</label>
                            <input type="password" name="api_key" class="form-control" value="<?= htmlspecialchars($voice_settings['api_key'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Default Script Template</label>
                            <select name="default_script" class="form-select">
                                <option value="">Select default script</option>
                                <?php
                                try {
                                    $allScripts = \App\Core\Database\Database::getInstance()->fetchAll("SELECT script_code, script_name FROM ai_call_scripts WHERE is_active = 1");
                                    foreach ($allScripts as $s) {
                                        $sel = ($vSettings['default_script'] ?? '') === $s['script_code'] ? 'selected' : '';
                                        echo "<option value=\"{$s['script_code']}\" $sel>" . htmlspecialchars($s['script_name']) . "</option>";
                                    }
                                } catch (\Exception $e) { error_log('voice-users/settings scripts dropdown: ' . $e->getMessage()); }
                                ?>
                            </select>
                        </div>
                        <?php echo \App\Helpers\SimpleCaptcha::renderField(); ?>
<button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Save Provider Settings</button>
                    </form>
                </div>
            </div>

            <!-- Schedule Settings -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-clock me-1"></i> Schedule Settings</h6>
                </div>
                <div class="card-body">
                    <?php
                    $sSettings = [];
                    if (!empty($schedule_settings['settings'])) {
                        $sSettings = json_decode($schedule_settings['settings'], true) ?? [];
                    }
                    ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/settings">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Calling Hours Start</label>
                                <input type="time" name="calling_hours_start" class="form-control" value="<?= htmlspecialchars($sSettings['calling_hours_start'] ?? '09:00') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Calling Hours End</label>
                                <input type="time" name="calling_hours_end" class="form-control" value="<?= htmlspecialchars($sSettings['calling_hours_end'] ?? '20:00') ?>">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Max Attempts per Lead</label>
                                <input type="number" name="max_attempts" class="form-control" value="<?= (int)($sSettings['max_attempts'] ?? 3) ?>" min="1" max="10">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Retry Interval (Hours)</label>
                                <input type="number" name="retry_interval" class="form-control" value="<?= (int)($sSettings['retry_interval'] ?? 24) ?>" min="1" max="168">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Save Schedule Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
