<?php
$groups = $groups ?? [];
$services = $services ?? [];

// Branded metadata per service_name — where to get each value (admin's guide)
$meta = [
    'aws_s3'        => ['label' => 'AWS S3 — Cloud File Storage', 'sub' => 'Backups, generated PDFs & uploads', 'icon' => 'fab fa-aws', 'color' => '#ff9900', 'brand' => '#fff8ea',
                        'links' => [['AWS Console → S3', 'https://s3.console.aws.amazon.com/s3'], ['AWS Console → IAM (keys)', 'https://console.aws.amazon.com/iam/home#/users']]],
    's3'            => ['label' => 'AWS S3 (Tenant Config)', 'sub' => 'Legacy-credential-set row used by backup jobs', 'icon' => 'fab fa-aws', 'color' => '#ff9900', 'brand' => '#fff8ea',
                        'links' => [['AWS Console → IAM', 'https://console.aws.amazon.com/iam/home#/access_key']]],
    'gcs'           => ['label' => 'Google Cloud Storage', 'sub' => 'Google bucket storage', 'icon' => 'fab fa-google', 'color' => '#4285f4', 'brand' => '#eef4ff',
                        'links' => [['Google Cloud Console → IAM → Service Accounts', 'https://console.cloud.google.com/iam-admin/serviceaccounts']]],
    'azure_blob'    => ['label' => 'Azure Blob Storage', 'sub' => 'Microsoft Azure container storage', 'icon' => 'fab fa-microsoft', 'color' => '#0078d4', 'brand' => '#eef5fd',
                        'links' => [['Azure Portal → Storage Accounts', 'https://portal.azure.com/#view/Microsoft_Azure_Storage']]],
    'local_fs'      => ['label' => 'Local Filesystem (Office Server)', 'sub' => 'Store files on this server / office disk', 'icon' => 'fas fa-hdd', 'color' => '#6f42c1', 'brand' => '#f3edff',
                        'links' => []],
    'smtp'          => ['label' => 'SMTP — Outgoing Email', 'sub' => 'Bookings, statements, alerts & password resets', 'icon' => 'fas fa-envelope', 'color' => '#ea4335', 'brand' => '#fff1f0',
                        'links' => [['Gmail → App Passwords (2-Step must be ON)', 'https://myaccount.google.com/apppasswords'], ['Office 365 SMTP settings', 'https://learn.microsoft.com/en-us/exchange/mail-flow-best-practices/smtp/openrelay']]],
    'razorpay'      => ['label' => 'Razorpay — Online Payments', 'sub' => 'Token / EMI / booking payments', 'icon' => 'fas fa-credit-card', 'color' => '#3395ff', 'brand' => '#eef7ff',
                        'links' => [['Dashboard → Settings → API Keys', 'https://razorpay.com/app/keys'], ['Dashboard → Webhooks', 'https://dashboard.razorpay.com/app/webhooks']]],
    'twilio'        => ['label' => 'Twilio — Voice / SMS / WhatsApp', 'sub' => 'Bulk SMS, OTP & WhatsApp alerts', 'icon' => 'fas fa-phone-alt', 'color' => '#f22f46', 'brand' => '#ffeef0',
                        'links' => [['Twilio Console → Account Info', 'https://console.twilio.com/'], ['Twilio → Messaging → Senders', 'https://console.twilio.com/us1/develop/sms/senders/whatsapp-senders']]],
    'voice_twilio'  => ['label' => 'Twilio (Voice Legacy)', 'sub' => 'Legacy voice-channel backup row', 'icon' => 'fas fa-phone-alt', 'color' => '#f22f46', 'brand' => '#ffeef0',
                        'links' => [['Twilio Console', 'https://console.twilio.com/']]],
    'exotel'        => ['label' => 'Exotel — Voice Calls', 'sub' => 'AI calling / site-visit confirmations', 'icon' => 'fas fa-phone', 'color' => '#ff7a00', 'brand' => '#fff5e8',
                        'links' => [['Exotel Dashboard → Settings → API', 'https://myexotel.com/v1/#/dashboard-settings']]],
    'knowlarity'    => ['label' => 'Knowlarity — Voice Calls', 'sub' => 'Outbound dialer fallback', 'icon' => 'fas fa-phone', 'color' => '#12b76a', 'brand' => '#eafff3',
                        'links' => [['Knowlarity Portal → Settings → API keys', 'https://atnav.knowlarity.com/']]],
    'plivo'         => ['label' => 'Plivo — Voice Calls', 'sub' => 'Voice service fallback', 'icon' => 'fas fa-phone', 'color' => '#5865f2', 'brand' => '#eef0ff',
                        'links' => [['Plivo Dashboard → Credentials', 'https://console.plivo.com/']]],
    'kaleyra'       => ['label' => 'Kaleyra — SMS Gateway', 'sub' => 'Bulk SMS fallback', 'icon' => 'fas fa-paper-plane', 'color' => '#0ea5e9', 'brand' => '#effaff',
                        'links' => [['Kaleyra Portal → API Keys', 'https://kaleyra.io/']]],
    'google_calendar' => ['label' => 'Google Calendar — Site-Visit Sync', 'sub' => 'Auto-invite customers/agents to site visits', 'icon' => 'fab fa-google', 'color' => '#4285f4', 'brand' => '#eef4ff',
                        'links' => [['Google Cloud → APIs & Services → Credentials (OAuth)', 'https://console.cloud.google.com/apis/credentials']]],
    'marketplace'   => ['label' => 'Marketplace Webhook', 'sub' => 'Incoming lead/notification webhooks', 'icon' => 'fas fa-plug', 'color' => '#fd7e14', 'brand' => '#fff4e8',
                        'links' => [['Swagger / Webhook console', 'https://swagger.io/inspector/']]],
    'exchange_rate' => ['label' => 'Exchange Rate API', 'sub' => 'USD/EUR conversion for NRI bookings', 'icon' => 'fas fa-exchange-alt', 'color' => '#00b4d8', 'brand' => '#eefbfd',
                        'links' => [['exchangerate-api.com — open.er-api.com/v1/latest', 'https://exchangerate-api.com/']]],
    'gstn'          => ['label' => 'GSTN Portal (GST E-Filing)', 'sub' => 'GST invoice filing from ERP', 'icon' => 'fas fa-file-invoice', 'color' => '#138808', 'brand' => '#edfaee',
                        'links' => [['GST Portal (login)', 'https://www.gst.gov.in/']]],
    'tin'           => ['label' => 'TIN Portal (TDS E-Filing)', 'sub' => 'TDS returns / Form 26AS from ERP', 'icon' => 'fas fa-id-card', 'color' => '#000080', 'brand' => '#eef0ff',
                        'links' => [['TRACES / TIN Portal', 'https://www.incometax.gov.in/']]],
    'leegality'     => ['label' => 'Leegality — eSign', 'sub' => 'Online signing for allotment agreements', 'icon' => 'fas fa-file-signature', 'color' => '#6f42c1', 'brand' => '#f5efff',
                        'links' => [['Leegality Dashboard → API Keys', 'https://app.leegality.com/']]],
    'booking'       => ['label' => 'Booking Business Rules', 'sub' => 'Token amount %, due days — change with CA/owner approval', 'icon' => 'fas fa-file-contract', 'color' => '#0d6efd', 'brand' => '#eef4ff',
                        'links' => []],
    'referral'      => ['label' => 'Referral Program Rates', 'sub' => 'Customer/associate referral incentives', 'icon' => 'fas fa-share-alt', 'color' => '#20c997', 'brand' => '#eafaf3',
                        'links' => []],
    'dynamic_pricing' => ['label' => 'Dynamic Pricing Engine', 'sub' => 'AI-driven price suggestions vs config guard-rails', 'icon' => 'fas fa-chart-line', 'color' => '#6610f2', 'brand' => '#f3edff',
                        'links' => []],
    'general'       => ['label' => 'Company / General', 'sub' => 'Public-facing company identity, CIN, GSTIN, support contacts', 'icon' => 'fas fa-building', 'color' => '#0dcaf0', 'brand' => '#eefbfd',
                        'links' => []],
];

// Flatten groups for search/filter convenience
$allServices = [];
foreach ($groups as $groupName => $rows) {
    foreach ($rows as $row) {
        $allServices[$row['service_name']] = $groupName;
    }
}
ksort($allServices);

// Human-readable key hints (shown under field)
$keyHints = [
    'aws_s3' => [
        'access_key' => 'IAM → Users → your user → Security credentials → Create access key',
        'secret_key' => 'Shown only at creation — store NOW (cannot be re-shown); keep out of git/e-mail',
        'region'     => 'e.g. ap-south-1 (Mumbai)',
        'bucket'     => 'S3 → Create bucket (e.g. apsdreamhome-prod)',
        'use_path_style' => '0 = virtual-hosted style (recommended)',
    ],
    'smtp' => [
        'host'        => 'smtp.gmail.com (Gmail) · smtp.office365.com (Outlook) · mail.yourdomain.com',
        'port'        => '587 = STARTTLS · 465 = SSL',
        'username'    => 'Your full email address',
        'password'    => 'App password (NOT the login password) — Gmail: 2-Step → App Passwords',
        'from_email'  => 'The address customers see as sender',
        'encryption'  => 'tls or ssl',
    ],
    'razorpay' => [
        'key_id'        => 'RAZORPAY dashboard → Settings → API Keys → Key ID',
        'key_secret'    => 'Reveal once at key creation — keep private',
        'webhook_secret'=> 'Webhook create dialog → “Secret” you type yourself',
    ],
    'twilio' => [
        'account_sid'  => 'Console dashboard → Account Info → AC…',
        'auth_token'   => 'Console dashboard → Account Info → show (rotatable)',
        'from_number'  => 'Console → Phone Numbers → your number (+E.164, e.g. +18126684585)',
    ],
    'exchange_rate' => [
        'primary_api' => 'Open ER-API — no key needed: https://open.er-api.com/v6/latest/INR',
    ],
];

function svcHref(array $links): string {
    $out = '';
    foreach ($links as $i => $l) {
        $out .= '<a href="' . htmlspecialchars($l[1]) . '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary svc-doc-btn"><i class="fas fa-external-link-alt me-1"></i>' . htmlspecialchars($l[0]) . '</a>';
    }
    return $out;
}
?>

<style>
.svc-page { max-width: 1180px; }
.svc-top-bar { position: sticky; top: 52px; z-index: 1020; background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 14px; padding: .6rem .9rem; box-shadow: 0 4px 18px rgba(15,23,42,.08); margin-bottom: 1.25rem; display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
.svc-search { position: relative; flex: 1; min-width: 200px; }
.svc-search input { width: 100%; padding: .45rem .8rem .45rem 2.2rem; border-radius: 10px; border: 1px solid var(--border-color, #e5e7eb); font-size: .9rem; }
.svc-search i { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted, #64748b); }
.svc-stat { font-size: .75rem; color: var(--text-muted, #64748b); background: var(--body-bg, #f8fafc); padding: .3rem .6rem; border-radius: 8px; white-space: nowrap; }
.svc-filter-pills { display: flex; gap: .3rem; flex-wrap: wrap; }
.svc-pill { border: 1px solid var(--border-color, #e5e7eb); background: transparent; border-radius: 20px; padding: .25rem .65rem; font-size: .75rem; cursor: pointer; user-select: none; }
.svc-pill.active { background: #2563eb; border-color: #2563eb; color: #fff; }

.svc-card { border: 1px solid var(--border-color, #e5e7eb); border-radius: 16px; overflow: hidden; margin-bottom: 1.1rem; background: var(--card-bg, #fff); box-shadow: 0 1px 3px rgba(15,23,42,.05); transition: box-shadow .2s; }
.svc-card:hover { box-shadow: 0 6px 22px rgba(15,23,42,.1); }
.svc-head { padding: .7rem 1rem; display: flex; align-items: center; gap: .8rem; border-bottom: 1px solid var(--border-color, #eef0f3); }
.svc-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.svc-name { font-weight: 700; font-size: .95rem; margin: 0; line-height: 1.25; }
.svc-sub { font-size: .7rem; color: var(--text-muted, #64748b); margin: .1rem 0 0; }
.svc-chips { margin-left: auto; display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
.svc-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
.svc-dot.ok { background: #16a34a; box-shadow: 0 0 6px rgba(22,163,74,.5); }
.svc-dot.bad { background: #dc2626; box-shadow: 0 0 6px rgba(220,38,38,.4); }
.svc-tag { font-size: .62rem; padding: .18rem .45rem; border-radius: 6px; background: var(--body-bg, #f1f5f9); color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: .02em; }
.svc-tag.secret { background: #fee2e2; color: #b91c1c; }
.svc-tag.enc { background: #d1fae5; color: #047857; }

.svc-body { padding: .8rem 1rem .4rem; }
.svc-grid-inner { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: .7rem; }
.svc-col { grid-column: 1 / -1; }
.svc-field { display: flex; flex-direction: column; gap: .2rem; }
.svc-field > label { font-size: .78rem; font-weight: 600; display: flex; align-items: center; gap: .4rem; }
.svc-hint { font-size: .68rem; color: var(--text-muted, #64748b); line-height: 1.35; }
.svc-hint b { color: var(--text-body, #334155); font-weight: 600; }
.svc-foot { padding: .55rem 1rem; border-top: 1px solid var(--border-color, #eef0f3); display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; background: var(--body-bg, #fafbfc); }
.svc-foot small { font-size: .7rem; color: var(--text-muted, #64748b); flex: 1; min-width: 200px; }
.svc-doc-btn { font-size: .7rem; }
.svc-testnote { display: none; font-size: .75rem; border-radius: 6px; padding: .35rem .6rem; margin-left: .2rem; }
.svc-testnote.show { display: inline-flex; align-items: center; gap: .35rem; }
.svc-testnote.ok { background: #d1fae5; color: #047857; }
.svc-testnote.err { background: #fee2e2; color: #b91c1c; }

.svc-howto { border: 1px dashed var(--border-color, #cbd5e1); border-radius: 12px; padding: .6rem .85rem; font-size: .78rem; margin-bottom: 1rem; background: var(--card-bg, #fff); }
.svc-empty-ill { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted, #64748b); display: none; }

/* Ensure strong contrast on light cards even under the admin dark theme */
.svc-page .svc-card, .svc-page .svc-top-bar, .svc-page .svc-howto { background: #ffffff; color: #1f2937; }
.svc-page .svc-name { color: #0f172a; }
.svc-page .svc-sub { color: #64748b; }
.svc-page .svc-field > label { color: #334155; }
.svc-page .svc-hint { color: #64748b; }
.svc-page .svc-foot { background: #f8fafc; border-top-color: #e5e7eb; }
.svc-page .svc-foot small { color: #64748b; }
.svc-page .svc-search input { background: #f8fafc; color: #0f172a; border-color: #e2e8f0; }
.svc-page .svc-howto { color: #334155; border-color: #cbd5e1; }
.svc-page .svc-stat { background: #f1f5f9; color: #475569; }
.svc-page .svc-tag { background: #f1f5f9; color: #475569; }
.svc-page .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 .15rem rgba(37,99,235,.18); }
</style>

<div class="svc-page container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
            <h1 class="mb-0 h2"><i class="fas fa-cogs me-2 text-primary"></i>Service Configuration</h1>
            <small class="text-muted">API keys &amp; integrations — stored <b>AES-256 encrypted</b> at rest. Changes go live immediately; test each service before use.</small>
        </div>
        <a href="<?= BASE_URL . '/admin/service-configs/history' ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-history me-1"></i>Audit History</a>
    </div>

    <!-- How-to strip -->
    <div class="svc-howto">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary">1</span> Pick a service below
            <span class="text-muted">→</span>
            <span class="badge bg-primary">2</span> Open the provider link → copy the key
            <span class="text-muted">→</span>
            <span class="badge bg-primary">3</span> Paste &amp; Save → <b>Test</b> → done
            <span class="text-muted ms-1">| Blank secret field = <b>keep existing</b> (current value is never shown here)</span>
        </div>
    </div>

    <?php if ($saved): ?><div class="alert alert-success alert-dismissible fade show py-2"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($saved) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show py-2"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <form method="POST" action="<?= BASE_URL . '/admin/service-configs/update' ?>" id="svcForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <!-- Sticky search & filter -->
        <div class="svc-top-bar">
            <div class="svc-search">
                <i class="fas fa-search"></i>
                <input type="search" id="svcSearch" placeholder="Search service, key or description… (e.g. twilio, s3, webhook)" aria-label="Search service configurations">
            </div>
            <div class="svc-filter-pills" id="svcFilterPills">
                <button type="button" class="svc-pill active" data-filter="all">All</button>
                <button type="button" class="svc-pill" data-filter="secrets">Secrets</button>
                <button type="button" class="svc-pill" data-filter="unconfigured">Not configured</button>
                <button type="button" class="svc-pill" data-filter="configured">Configured</button>
            </div>
            <span class="svc-stat"><i class="fas fa-layer-group me-1"></i><span id="svcCount">—</span> settings</span>
            <span class="svc-stat"><i class="fas fa-shield-alt me-1 text-success"></i>enc:v1</span>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save All</button>
            </div>
        </div>

        <!-- Cards -->
        <?php foreach ($groups as $groupName => $groupServices): ?>
        <?php
            $svcName = $groupServices[0]['service_name'] ?? $groupName;
            $m = $meta[$svcName] ?? ['label' => ucfirst(str_replace('_', ' ', $svcName)), 'sub' => 'Configuration', 'icon' => 'fas fa-cog', 'color' => '#6c757d', 'brand' => '#f8f9fa', 'links' => []];
            $isConfigured = false;
            $secretCount = 0;
            $emptyCount = 0;
            foreach ($groupServices as $s) {
                if (!empty($s['is_secret'])) $secretCount++;
                if ($s['config_value'] === '' || $s['config_value'] === null) $emptyCount++;
                if ($s['config_value'] !== '' && $s['config_value'] !== null) $isConfigured = true;
            }
            $dataText = strtolower($svcName . ' ' . $m['label'] . ' ' . $m['sub'] . ' ' . implode(' ', array_map(fn($s) => $s['config_key'] . ' ' . ($s['description'] ?? ''), $groupServices)));
        ?>
        <div class="svc-card svc-block" id="svc-<?= htmlspecialchars($svcName) ?>" data-text="<?= htmlspecialchars($dataText) ?>" data-secrets="<?= $secretCount ?>" data-empty="<?= $emptyCount ?>" data-configured="<?= $isConfigured ? 1 : 0 ?>">
            <div class="svc-head" style="background: linear-gradient(135deg, <?= $m['brand'] ?>, #ffffff);">
                <div class="svc-icon" style="background: <?= $m['color'] ?>; color: #fff;">
                    <i class="<?= $m['icon'] ?>"></i>
                </div>
                <div class="min-width-0">
                    <h2 class="svc-name"><?= htmlspecialchars($m['label']) ?></h2>
                    <p class="svc-sub"><?= htmlspecialchars($m['sub']) ?></p>
                </div>
                <div class="svc-chips">
                    <?php if ($secretCount > 0): ?><span class="svc-tag secret" title="Secret values are AES-256 encrypted, blank = keep current value"><i class="fas fa-lock me-1"></i><?= $secretCount ?> secret<?= $secretCount > 1 ? 's' : '' ?></span><?php endif; ?>
                    <span class="svc-dot <?= $isConfigured ? 'ok' : 'bad' ?>"></span>
                    <span class="small <?= $isConfigured ? 'text-success' : 'text-danger' ?>"><?= $isConfigured ? 'Configured' : 'Not configured' ?></span>
                </div>
            </div>

            <div class="svc-body">
                <div class="svc-grid-inner">
                    <?php foreach ($groupServices as $s):
                        $sName = htmlspecialchars($s['service_name']);
                        $sKey  = htmlspecialchars($s['config_key']);
                        $isSecret = (int)$s['is_secret'] === 1;
                        $sType = (string)$s['config_type'];
                        $hintHtml = $keyHints[$s['service_name']][$s['config_key']] ?? '';
                        $desc = (string)($s['description'] ?? '');
                    ?>
                    <div class="svc-field <?= $isSecret ? 'svc-field-secret' : '' ?>">
                        <label for="cfg-<?= $sName ?>-<?= $sKey ?>">
                            <?= htmlspecialchars($s['config_key']) ?>
                            <?php if ($isSecret): ?><span class="svc-tag secret">secret</span><span class="svc-tag enc" title="Values are stored AES-256 encrypted">enc:v1</span><?php endif; ?>
                            <?php if ($sType === 'number'): ?><span class="svc-tag">number</span><?php elseif ($sType === 'boolean'): ?><span class="svc-tag">on/off</span><?php endif; ?>
                        </label>
                        <?php
                        $placeholder = $isSecret
                            ? (trim((string)$s['config_value']) !== '' ? 'Configured — type to replace' : 'Enter value')
                            : (trim((string)$s['config_value']) !== '' ? '' : 'Enter value');
                        if ($sType === 'boolean'): ?>
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="configs[<?= $sName ?>][<?= $sKey ?>]" value="0">
                                <input type="checkbox" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" class="form-check-input" value="1" <?= ($s['config_value'] === '1' || $s['config_value'] === 'true') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cfg-<?= $sName ?>-<?= $sKey ?>"><?= htmlspecialchars($desc !== '' ? $desc : 'Enable') ?></label>
                            </div>
                        <?php elseif ($isSecret || $sType === 'password'): ?>
                            <input type="password" class="form-control" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" autocomplete="new-password" placeholder="<?= htmlspecialchars($placeholder) ?>" value="">
                        <?php elseif ($sType === 'number'): ?>
                            <input type="number" class="form-control" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" value="<?= htmlspecialchars((string)$s['config_value']) ?>">
                        <?php else: ?>
                            <input type="text" class="form-control" id="cfg-<?= $sName ?>-<?= $sKey ?>" name="configs[<?= $sName ?>][<?= $sKey ?>]" value="<?= htmlspecialchars((string)$s['config_value']) ?>" placeholder="<?= htmlspecialchars($placeholder) ?>">
                        <?php endif; ?>
                        <?php if ($hintHtml !== ''): ?>
                            <div class="svc-hint"><b>Where to get:</b> <?= htmlspecialchars($hintHtml) ?></div>
                        <?php elseif ($desc !== ''): ?>
                            <div class="svc-hint"><?= htmlspecialchars($desc) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="test-result-<?= htmlspecialchars($svcName) ?>" class="svc-testnote" role="status"></div>
            </div>

            <div class="svc-foot">
                <?= svcHref($m['links']) ?>
                <small>Ask AI: <button type="button" class="btn btn-link btn-sm p-0 align-baseline text-decoration-none" onclick="typeof sendQuickMessage === 'function' ? sendQuickMessage('How to configure <?= htmlspecialchars(addslashes($m['label']), ENT_QUOTES) ?> in APS Dream Home admin panel?') : null">how to set up <?= htmlspecialchars($svcName) ?></button></small>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="testService('<?= htmlspecialchars($svcName) ?>')"><i class="fas fa-plug"></i> Test</button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetService('<?= htmlspecialchars($svcName) ?>')"><i class="fas fa-undo"></i> Reset to seed</button>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex align-items-center gap-2 mt-1 mb-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save All Changes</button>
            <a href="<?= BASE_URL . '/admin/service-configs' ?>" class="btn btn-outline-secondary btn-sm">Reload</a>
        </div>
    </form>

    <div class="svc-empty-ill" id="svcEmpty"><i class="fas fa-search fa-2x mb-2 opacity-50"></i><div>No matches. Try “twilio”, “smtp”, “pricing”, or clear the filter.</div></div>
</div>

<script>
(function() {
    var input = document.getElementById('svcSearch');
    var pills = document.querySelectorAll('#svcFilterPills .svc-pill');
    var blocks = Array.prototype.slice.call(document.querySelectorAll('.svc-block'));
    var emptyEl = document.getElementById('svcEmpty');
    var countEl = document.getElementById('svcCount');
    var activeFilter = 'all';

    function visible(block) {
        var text = block.getAttribute('data-text') || '';
        var q = (input.value || '').trim().toLowerCase();
        if (q && text.indexOf(q) === -1) return false;
        switch (activeFilter) {
            case 'secrets': return parseInt(block.getAttribute('data-secrets') || '0', 10) > 0;
            case 'unconfigured': return parseInt(block.getAttribute('data-configured') || '0', 10) === 0 && parseInt(block.getAttribute('data-empty') || '0', 10) > 0;
            case 'configured': return parseInt(block.getAttribute('data-configured') || '0', 10) === 1;
            default: return true;
        }
    }
    function apply() {
        var shown = 0;
        blocks.forEach(function(b) {
            var match = visible(b);
            b.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        if (emptyEl) emptyEl.style.display = shown === 0 ? 'block' : 'none';
        if (countEl) countEl.textContent = shown + '/' + blocks.length;
    }
    if (input) input.addEventListener('input', apply);
    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            activeFilter = pill.getAttribute('data-filter');
            pills.forEach(function(p) { p.classList.toggle('active', p === pill); });
            apply();
        });
    });
    apply();
})();

function testService(service) {
    var resultEl = document.getElementById('test-result-' + service);
    if (resultEl) {
        resultEl.className = 'svc-testnote show';
        resultEl.textContent = (/[a-z]/.test(service) ? service : 'Service') + ': testing…';
    }
    fetch('<?= BASE_URL . '/admin/service-configs/test/' ?>' + service, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': (document.querySelector('meta[name="csrf-token"]') || {}).content || '' }
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (!resultEl) return;
        resultEl.className = 'svc-testnote show ' + (d.success ? 'ok' : 'err');
        resultEl.innerHTML = (d.success ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>') + ' ' + (d.service ? String(d.service).toUpperCase() : service.toUpperCase()) + ': ' + (d.message || (d.success ? 'OK' : 'Failed'));
    }).catch(function() {
        if (!resultEl) return;
        resultEl.className = 'svc-testnote show err';
        resultEl.innerHTML = '<i class="fas fa-times-circle"></i> ' + service.toUpperCase() + ': Test failed (network error)';
    });
}
function resetService(service) {
    if (!confirm('Reset ' + service + ' to the seeded defaults? It will overwrite all values for this service.')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL . '/admin/service-configs/reset/' ?>' + service;
    var token = document.createElement('input');
    token.type = 'hidden'; token.name = 'csrf_token';
    token.value = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>';
    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
</script>
