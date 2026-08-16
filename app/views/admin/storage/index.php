<?php
/** @var array $info */
/** @var array $flash */

$base = defined('BASE_URL') ? BASE_URL : '';
$driver = $info['driver'] ?? 'local';
$s3Configured = !empty($info['s3_configured']);
$s3Active = $driver === 's3' || (is_string($driver) && strpos($driver, 's3') !== false);
$localPath = $info['local_path'] ?? '';
$localSize = (int) ($info['local_size_bytes'] ?? 0);
$localCount = (int) ($info['local_count'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Gateways - APS Dream Home</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/admin/css/admin.css">
    <style>
        .storage-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:20px; margin-bottom:16px; }
        .storage-card h3 { margin:0 0 12px 0; font-size:1.15rem; display:flex; align-items:center; gap:8px; }
        .badge { display:inline-block; padding:3px 8px; border-radius:4px; font-size:.75rem; font-weight:600; }
        .badge-green { background:#10b981; color:#fff; }
        .badge-yellow { background:#f59e0b; color:#fff; }
        .badge-gray { background:#6b7280; color:#fff; }
        .badge-red { background:#ef4444; color:#fff; }
        .kv { display:grid; grid-template-columns:200px 1fr; gap:6px 12px; font-size:.92rem; }
        .kv .k { color:#6b7280; }
        .kv .v { font-family:monospace; word-break:break-all; }
        .actions { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; }
        .btn { background:#2563eb; color:#fff; border:0; padding:8px 14px; border-radius:6px; cursor:pointer; font-size:.9rem; }
        .btn:hover { background:#1d4ed8; }
        .btn-secondary { background:#6b7280; }
        .btn-secondary:hover { background:#4b5563; }
        .alert { padding:12px 16px; border-radius:6px; margin-bottom:14px; }
        .alert-success { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
        .alert-error { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
        .alert-warning { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
        .alert-info { background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; }
    </style>
</head>
<body>
<div class="admin-wrap" class="style-32224">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="style-10864">
        <h1 class="style-38351">Storage Gateways</h1>
        <p class="style-55261">Active storage driver + S3 configuration status.</p>

        <?php if (!empty($flash['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash['success'] ?? '') ?></div>
        <?php endif; ?>
        <?php if (!empty($flash['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flash['error'] ?? '') ?></div>
        <?php endif; ?>
        <?php if (!empty($flash['warning'])): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($flash['warning'] ?? '') ?></div>
        <?php endif; ?>

        <div class="storage-card">
            <h3>Active driver
                <?php if ($s3Active): ?>
                    <span class="badge badge-green">S3</span>
                <?php elseif (strpos($driver, 'fallback') !== false): ?>
                    <span class="badge badge-yellow">Local (S3 fallback)</span>
                <?php else: ?>
                    <span class="badge badge-gray">Local</span>
                <?php endif; ?>
            </h3>
            <div class="kv">
                <div class="k">Configured driver (env)</div><div class="v"><?= htmlspecialchars($info['configured_driver'] ?? '') ?></div>
                <div class="k">Resolved driver</div><div class="v"><?= htmlspecialchars($driver ?? '') ?></div>
                <div class="k">Driver switch</div>
                <div class="v">
                    <form method="post" action="<?= $base ?>/admin/storage/switch" class="style-35851">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="driver" value="local">
                        <button class="btn btn-secondary" type="submit" <?= $info['configured_driver'] === 'local' ? 'disabled' : '' ?>>Use Local</button>
                    </form>
                    <form method="post" action="<?= $base ?>/admin/storage/switch" class="style-35851">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="driver" value="s3">
                        <button class="btn btn-secondary" type="submit" <?= $info['configured_driver'] === 's3' ? 'disabled' : '' ?>>Use S3</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="storage-card">
            <h3>AWS S3
                <?php if ($s3Configured): ?>
                    <span class="badge badge-green">Configured</span>
                <?php else: ?>
                    <span class="badge badge-red">Not configured</span>
                <?php endif; ?>
            </h3>
            <?php if ($s3Configured): ?>
                <div class="kv">
                    <div class="k">Bucket</div><div class="v"><?= htmlspecialchars($info['s3_bucket'] ?? '') ?></div>
                    <div class="k">Region</div><div class="v"><?= htmlspecialchars($info['s3_region'] ?? '') ?></div>
                    <div class="k">Endpoint</div><div class="v"><?= htmlspecialchars($info['s3_endpoint'] ?: '(AWS default)') ?></div>
                    <div class="k">Path-style</div><div class="v"><?= !empty($info['s3_path_style']) ? 'yes' : 'no' ?></div>
                    <div class="k">URL expiry (min)</div><div class="v"><?= (int) $info['s3_url_expiry'] ?></div>
                </div>
                <div class="actions">
                    <form method="post" action="<?= $base ?>/admin/storage/test" class="style-35851">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button class="btn" type="submit">Test Connection</button>
                    </form>
                    <a class="btn btn-secondary" href="<?= $base ?>/admin/storage/list?prefix=&limit=10" target="_blank">View Bucket (first 10)</a>
                </div>
            <?php else: ?>
                <p class="style-57887">AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, and AWS_BUCKET must be set in <code>.env</code> and <code>STORAGE_DRIVER=s3</code>.</p>
                <pre class="style-17804">STORAGE_DRIVER=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=apsdreamhome-uploads</pre>
            <?php endif; ?>
        </div>

        <div class="storage-card">
            <h3>Local storage
                <span class="badge badge-gray">Always available</span>
            </h3>
            <div class="kv">
                <div class="k">Path</div><div class="v"><?= htmlspecialchars($localPath ?? '') ?></div>
                <div class="k">URL prefix</div><div class="v"><?= htmlspecialchars($info['local_url'] ?? '') ?></div>
                <div class="k">Files in root</div><div class="v"><?= number_format($localCount) ?></div>
                <div class="k">Total size</div><div class="v"><?= number_format($localSize / (1024*1024), 2) ?> MB</div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
