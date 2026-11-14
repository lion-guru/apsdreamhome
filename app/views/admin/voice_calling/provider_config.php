<?php
/**
 * Voice Provider Configuration — secret-safe
 * Reads via ServiceConfigService (values decrypted server-side; secrets rendered blank)
 * Posts to the shared /admin/service-configs/update endpoint (blank secret = keep current value)
 */
use App\Services\ServiceConfigService;

$page_title = $page_title ?? 'Configure Voice Provider';
$provider = $provider ?? '';

// Load provider + its config rows server-side (no raw DB, no secrets in markup)
$service = ServiceConfigService::getInstance();
$allRows = [];
$testProvider = '';
if ($provider !== '') {
    $all = $service->getAll($provider);
    // getAll(service) returns rows keyed by config_key
    foreach ($all as $row) {
        $allRows[] = $row;
    }
}

// Key-specific hints (where to get it)
$keyHints = [
    'account_sid'   => 'Console dashboard → Account Info → Account SID (starts with AC…)',
    'auth_token'    => 'Console dashboard → Account Info → Auth Token (rotatable)',
    'from_number'   => 'Console → Phone Numbers → your number (+E.164, e.g. +18126684585)',
    'whatsapp_number' => 'Twilio → Messaging → WhatsApp sender (or leave as from_number)',
    'account_sid_2' => 'Backup account SID if you rotate providers',
    'exotel_sid'    => 'Exotel Dashboard → Settings → API → SID (API Credentials)',
    'exotel_token'  => 'Exotel Dashboard → Settings → API → Token',
    'exotel_number' => 'Exotel Dashboard → Virtual Number (…shortlist) / App number',
    'exotel_app_id' => 'Exotel Dashboard → Settings → API → App ID',
    'provider_type' => 'Twilio | Exotel | knowlarity | plivo | kaleyra',
];
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
                <h2 class="fs-6 fw-semibold mb-0"><i class="fas fa-phone me-2"></i>Configure: <strong><?= htmlspecialchars($provider) ?></strong></h2>
            </div>
            <div class="card-body">
                <?php if (empty($provider)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Select a provider from the menu or <a href="/admin/service-configs">go to Service Configs</a> to manage all providers.
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_URL . '/admin/service-configs/update' ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!$allRows): ?>
                            <div class="alert alert-warning">
                                No configuration rows seeded for <strong><?= htmlspecialchars($provider) ?></strong> yet.
                                Create them from <a href="/admin/service-configs">Service Configs</a> first, then return here to set the values.
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <?php foreach ($allRows as $c):
                                $cKey = (string)$c['config_key'];
                                $isSec = (int)$c['is_secret'] === 1;
                                $isBool = $c['config_type'] === 'boolean';
                                $isPass = $c['config_type'] === 'password' || $isSec;
                                $val = (string)$c['config_value'];
                                $placeholder = $isSec ? (trim($val) !== '' ? 'Configured — type to replace' : 'Enter value') : '';
                                $hint = $keyHints[$cKey] ?? (string)($c['description'] ?? '');
                            ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-flex align-items-center gap-2" for="vcfg-<?= htmlspecialchars($cKey) ?>">
                                    <?= htmlspecialchars($cKey) ?>
                                    <?php if ($isSec): ?><span class="badge bg-danger">secret</span><?php endif; ?>
                                    <?php if ($isBool): ?><span class="badge bg-secondary">on/off</span><?php endif; ?>
                                </label>
                                <?php if ($isBool): ?>
                                    <div class="form-check form-switch mt-1">
                                        <input type="hidden" name="configs[<?= htmlspecialchars($provider) ?>][<?= htmlspecialchars($cKey) ?>]" value="0">
                                        <input type="checkbox" id="vcfg-<?= htmlspecialchars($cKey) ?>" name="configs[<?= htmlspecialchars($provider) ?>][<?= htmlspecialchars($cKey) ?>]" class="form-check-input" value="1" <?= ($val === '1' || $val === 'true') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="vcfg-<?= htmlspecialchars($cKey) ?>"><?= htmlspecialchars($c['description'] ?? 'Enable') ?></label>
                                    </div>
                                <?php elseif ($isPass): ?>
                                    <input type="password" id="vcfg-<?= htmlspecialchars($cKey) ?>" class="form-control"
                                           name="configs[<?= htmlspecialchars($provider) ?>][<?= htmlspecialchars($cKey) ?>]"
                                           autocomplete="new-password" placeholder="<?= htmlspecialchars($placeholder) ?>" value="">
                                <?php else: ?>
                                    <input type="text" id="vcfg-<?= htmlspecialchars($cKey) ?>" class="form-control"
                                           name="configs[<?= htmlspecialchars($provider) ?>][<?= htmlspecialchars($cKey) ?>]"
                                           value="<?= htmlspecialchars($val) ?>">
                                <?php endif; ?>
                                <?php if ($hint !== ''): ?>
                                    <div class="form-text small"><?= htmlspecialchars($hint) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-light border small py-2 mb-2">
                            <i class="fas fa-shield-alt me-1 text-success"></i>
                            Secrets are stored AES-256 encrypted (<code>enc:v1:</code>). A blank secret field keeps the current value — the existing value is never printed here.
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Configuration</button>
                        <a href="/admin/voice-calling" class="btn btn-secondary ms-2">Back to Dashboard</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2 class="fs-6 fw-semibold mb-0"><i class="fas fa-info-circle me-1"></i>Provider Guide</h2>
            </div>
            <div class="card-body">
                <h6>Exotel (Recommended for India)</h6>
                <ul class="small">
                    <li>Sign up at exotel.com</li>
                    <li>Get SID, Token, Virtual Number</li>
                    <li>Create App for IVR flows</li>
                    <li>Webhook: <code>/api/voice/exotel</code></li>
                </ul>
                <h6 class="mt-3">Twilio (Global)</h6>
                <ul class="small">
                    <li>Sign up at twilio.com</li>
                    <li>Get Account SID, Auth Token</li>
                    <li>Buy/verify phone number</li>
                    <li>Webhook: <code>/api/voice/twilio</code></li>
                </ul>
                <h6 class="mt-3">Test before going live</h6>
                <p class="small text-muted mb-2">Set <strong>test_mode = 1</strong> first, place a test call, check that the voice_call_logs row appears in <a href="/admin/voice-calling">Voice Dashboard</a>, then flip to 0 when happy.</p>
                <h6 class="mt-2">Ngrok for Local Testing</h6>
                <pre class="small bg-light p-2 rounded"><code>ngrok http 80</code></pre>
                <p class="small text-muted mb-0">Use the HTTPS URL from ngrok as the webhook base in the provider portal.</p>
            </div>
        </div>
    </div>
</div>
