<?php
$driverLabels = [
    'aws_s3' => 'AWS S3',
    'gcs' => 'Google Cloud Storage',
    'local_fs' => 'Local Filesystem',
    'backblaze_b2' => 'Backblaze B2',
    'cloudflare_r2' => 'Cloudflare R2',
    'azure_blob' => 'Azure Blob Storage',
];

$driver = $driver ?? 'Unknown';
$provider = $provider ?? '';
$config = $config ?? [];
$isConfigured = $isConfigured ?? false;
$fieldDefs = [
    'aws_s3' => [
        ['key' => 'access_key', 'label' => 'Access Key ID', 'type' => 'password', 'required' => true],
        ['key' => 'secret_key', 'label' => 'Secret Access Key', 'type' => 'password', 'required' => true],
        ['key' => 'region', 'label' => 'Region', 'type' => 'text', 'default' => 'ap-south-1'],
        ['key' => 'bucket', 'label' => 'Bucket Name', 'type' => 'text', 'required' => true],
        ['key' => 'endpoint', 'label' => 'Custom Endpoint (optional)', 'type' => 'text'],
        ['key' => 'test_mode', 'label' => 'Test Mode', 'type' => 'boolean'],
    ],
    'gcs' => [
        ['key' => 'project_id', 'label' => 'Project ID', 'type' => 'text', 'required' => true],
        ['key' => 'bucket', 'label' => 'Bucket Name', 'type' => 'text', 'required' => true],
        ['key' => 'key_file', 'label' => 'Service Account Key File Path', 'type' => 'text'],
        ['key' => 'key_json', 'label' => 'Service Account JSON (paste JSON)', 'type' => 'textarea', 'help' => 'Paste the entire service account JSON here, or use key_file above'],
        ['key' => 'test_mode', 'label' => 'Test Mode', 'type' => 'boolean'],
    ],
    'local_fs' => [
        ['key' => 'root_path', 'label' => 'Storage Root Path', 'type' => 'text', 'required' => true, 'default' => 'C:/xampp/htdocs/apsdreamhome/storage/files'],
        ['key' => 'base_url', 'label' => 'Public Base URL', 'type' => 'text', 'placeholder' => 'https://files.example.com'],
        ['key' => 'public_path', 'label' => 'Public Path', 'type' => 'text', 'default' => 'storage/files'],
    ],
    'backblaze_b2' => [
        ['key' => 'access_key', 'label' => 'Key ID', 'type' => 'password', 'required' => true],
        ['key' => 'secret_key', 'label' => 'Application Key', 'type' => 'password', 'required' => true],
        ['key' => 'region', 'label' => 'Region', 'type' => 'text', 'default' => 'us-west-002'],
        ['key' => 'bucket', 'label' => 'Bucket Name', 'type' => 'text', 'required' => true],
        ['key' => 'endpoint', 'label' => 'S3 Endpoint', 'type' => 'text', 'default' => 'https://s3.us-west-002.backblazeb2.com'],
    ],
    'cloudflare_r2' => [
        ['key' => 'access_key', 'label' => 'Access Key ID', 'type' => 'password', 'required' => true],
        ['key' => 'secret_key', 'label' => 'Secret Access Key', 'type' => 'password', 'required' => true],
        ['key' => 'region', 'label' => 'Region', 'type' => 'text', 'default' => 'auto'],
        ['key' => 'bucket', 'label' => 'Bucket Name', 'type' => 'text', 'required' => true],
        ['key' => 'endpoint', 'label' => 'Endpoint URL', 'type' => 'text', 'placeholder' => 'https://<account_id>.r2.cloudflarestorage.com'],
    ],
    'azure_blob' => [
        ['key' => 'connection_string', 'label' => 'Connection String', 'type' => 'password', 'required' => true],
        ['key' => 'container', 'label' => 'Container Name', 'type' => 'text', 'required' => true],
    ],
];
$fields = $fieldDefs[$provider] ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Configure <?= $driver ?></h1>
        <a href="<?= BASE_URL . '/admin/multi-storage' ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if ($saved = $_GET['saved'] ?? null): ?><div class="alert alert-success"><?= htmlspecialchars($saved) ?></div><?php endif; ?>
    <?php if ($error = $_GET['error'] ?? null): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header fw-bold">
            <i class="fas fa-cog me-2"></i><?= $driver ?> Configuration
            <span class="badge bg-<?= $isConfigured ? 'success' : 'warning' ?> ms-2">
                <?= $isConfigured ? 'Configured' : 'Not Configured' ?>
            </span>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL . '/admin/service-configs/update' ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="configs[<?= $provider ?>]" value="1">

                <?php foreach ($fields as $f): ?>
                    <?php
                    $key = $f['key'];
                    $val = htmlspecialchars($config[$key] ?? ($f['default'] ?? ''));
                    $placeholder = $f['placeholder'] ?? '';
                    $help = $f['help'] ?? '';
                    ?>
                    <div class="mb-3">
                        <label class="form-label"><?= $f['label'] ?> <?= $f['required'] ? '<span class="text-danger">*</span>' : '' ?></label>
                        <?php if ($f['type'] === 'password'): ?>
                            <div class="input-group">
                                <input type="password" name="configs[<?= $provider ?>][<?= $f['key'] ?>]" class="form-control" value="<?= htmlspecialchars($config[$key] ?? '') ?>" placeholder="<?= htmlspecialchars($placeholder) ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                            </div>
                        <?php elseif ($f['type'] === 'textarea'): ?>
                            <textarea name="configs[<?= $provider ?>][<?= $f['key'] ?>]" class="form-control" rows="6" placeholder="<?= htmlspecialchars($placeholder) ?>"><?= htmlspecialchars($config[$key] ?? '') ?></textarea>
                            <?php if ($help): ?><div class="form-text"><?= $help ?></div><?php endif; ?>
                        <?php elseif ($f['type'] === 'boolean'): ?>
                            <div class="form-check form-switch">
                                <input type="hidden" name="configs[<?= $provider ?>][<?= $f['key'] ?>]" value="0">
                                <input type="checkbox" name="configs[<?= $provider ?>][<?= $f['key'] ?>]" class="form-check-input" value="1" <?= !empty($config[$key]) && $config[$key] !== '0' ? 'checked' : '' ?>>
                                <label class="form-check-label">Enabled</label>
                            </div>
                        <?php else: ?>
                            <input type="text" name="configs[<?= $provider ?>][<?= $f['key'] ?>]" class="form-control" value="<?= htmlspecialchars($config[$key] ?? '') ?>" placeholder="<?= htmlspecialchars($placeholder) ?>">
                        <?php endif; ?>
                        <?php if ($help): ?><div class="form-text"><?= $help ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Configuration</button>
                    <a href="<?= BASE_URL . '/admin/multi-storage' ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}
</script>