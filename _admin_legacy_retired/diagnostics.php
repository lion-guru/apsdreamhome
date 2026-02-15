<?php
require_once '../includes/db_connection.php';
require_once '../includes/auth_admin.php';

// Ensure only admins can access
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$diagnostics = [
    'database' => testDatabaseConnection(),
    'php_version' => PHP_VERSION,
    'server_info' => $_SERVER['SERVER_SOFTWARE'],
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'extensions' => get_loaded_extensions(),
    'disk_free_space' => formatBytes(disk_free_space('/')),
    'disk_total_space' => formatBytes(disk_total_space('/'))
];

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Include admin header
include '../templates/admin/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>System Diagnostics</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Component</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Database Connection -->
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Database Connection</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= $diagnostics['database']['connection_status'] ? 'success' : 'danger' ?>">
                                            <?= $diagnostics['database']['connection_status'] ? 'Connected' : 'Failed' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            Connection Time: <?= $diagnostics['database']['connection_time'] ?>s
                                        </p>
                                        <?php if (!empty($diagnostics['database']['errors'])): ?>
                                            <p class="text-xs text-danger mb-0">
                                                Errors: <?= implode(', ', $diagnostics['database']['errors']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- PHP Version -->
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">PHP Version</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-info">
                                            <?= $diagnostics['php_version'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <?= version_compare($diagnostics['php_version'], '7.4.0') >= 0 ? 'Compatible' : 'Upgrade Recommended' ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <!-- Server Info -->
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Web Server</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-info">
                                            <?= explode('/', $diagnostics['server_info'])[0] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <?= $diagnostics['server_info'] ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <!-- Disk Space -->
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Disk Space</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= (disk_free_space('/') / disk_total_space('/')) > 0.2 ? 'success' : 'warning' ?>">
                                            <?= $diagnostics['disk_free_space'] ?> Free
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            Total: <?= $diagnostics['disk_total_space'] ?>
                                        </p>
                                    </td>
                                </tr>
                                <!-- PHP Extensions -->
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">PHP Extensions</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= array_sum(array_column($diagnostics['extensions_check'], 'installed')) == count($diagnostics['extensions_check']) ? 'success' : 'warning' ?>">
                                            <?= array_sum(array_column($diagnostics['extensions_check'], 'installed')) ?>/<?= count($diagnostics['extensions_check']) ?> Installed
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-xs font-weight-bold mb-0">
                                            <?php foreach ($diagnostics['extensions_check'] as $ext => $status): ?>
                                                <div class="mb-1">
                                                    <?= $ext ?>: 
                                                    <span class="<?= $status['installed'] ? 'text-success' : 'text-danger' ?>">
                                                        <?= $status['installed'] ? 'Installed' : 'Missing' ?>
                                                    </span>
                                                    <small class="text-muted">(<?= $status['purpose'] ?>)</small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/admin/footer.php'; ?>

$requiredExtensions = [
    'mysqli' => 'Database connectivity',
    'gd' => 'Image processing',
    'curl' => 'API connectivity',
    'json' => 'Data formatting',
    'mbstring' => 'Multi-byte string handling',
    'zip' => 'File compression'
];

$extensionStatus = [];
foreach ($requiredExtensions as $ext => $purpose) {
    $extensionStatus[$ext] = [
        'installed' => extension_loaded($ext),
        'purpose' => $purpose
    ];
}

$diagnostics['extensions_check'] = $extensionStatus;