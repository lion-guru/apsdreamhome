<?php
/**
 * Voice Provider Configuration
 * S347
 */
$page_title = $page_title ?? 'Configure Voice Provider';
$provider = $provider ?? '';
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-0"><i class="fas fa-cog me-2 text-primary"></i>Voice Provider Configuration</h4>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Configure: <strong><?= htmlspecialchars($provider) ?></strong></h5>
            </div>
            <div class="card-body">
                <?php if (empty($provider)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Select a provider from the menu or <a href="/admin/service-configs">go to Service Configs</a> to manage all providers.
                    </div>
                <?php else: ?>
                    <form method="POST" action="/admin/service-configs/save">
                        <input type="hidden" name="group_name" value="ai_voice">
                        <input type="hidden" name="service_name" value="<?= htmlspecialchars($provider) ?>">
                        <?php
                        // Get existing configs
                        require_once 'config/bootstrap.php';
                        $db = \App\Core\Database\Database::getInstance();
                        $cfgs = $db->fetchAll("
                            SELECT * FROM service_configs 
                            WHERE group_name = 'ai_voice' AND service_name = ?
                            ORDER BY config_key
                        ", [$provider]);
                        ?>
                        <div class="row">
                            <?php foreach ($cfgs as $c): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= htmlspecialchars($c['config_key']) ?>
                                    <?php if ($c['config_type'] === 'password'): ?>
                                        <span class="text-muted small">(encrypted)</span>
                                    <?php endif; ?>
                                </label>
                                <input type="<?= $c['config_type'] === 'password' ? 'password' : 'text' ?>" 
                                       class="form-control" 
                                       name="configs[<?= htmlspecialchars($c['config_key']) ?>]" 
                                       value="<?= htmlspecialchars($c['config_value'] ?? '') ?>"
                                       placeholder="Enter <?= htmlspecialchars($c['description']) ?>">
                                <div class="form-text"><?= htmlspecialchars($c['description']) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Configuration
                        </button>
                        <a href="/admin/voice-calling" class="btn btn-secondary ms-2">Back to Dashboard</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i>Provider Guide</h6>
            </div>
            <div class="card-body">
                <h6>Exotel (Recommended for India)</h6>
                <ul class="small">
                    <li>Sign up at exotel.com</li>
                    <li>Get SID, Token, Virtual Number</li>
                    <li>Create App for IVR flows</li>
                    <li>Webhook: <code>https://yourdomain.com/api/voice/exotel</code></li>
                </ul>
                <h6 class="mt-3">Twilio (Global)</h6>
                <ul class="small">
                    <li>Sign up at twilio.com</li>
                    <li>Get Account SID, Auth Token</li>
                    <li>Buy/verify phone number</li>
                    <li>Webhook: <code>https://yourdomain.com/api/voice/twilio</code></li>
                </ul>
                <h6 class="mt-3">Ngrok for Local Testing</h6>
                <pre class="small bg-light p-2 rounded"><code>ngrok http 80</code></pre>
                <p class="small text-muted mb-0">Use the HTTPS URL from ngrok as your webhook base.</p>
            </div>
        </div>
    </div>
</div>