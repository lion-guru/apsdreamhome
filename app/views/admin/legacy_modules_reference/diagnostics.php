<?php
require_once __DIR__ . '/core/init.php';

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
    'disk_total_space' => formatBytes(disk_total_space('/')),
    'env_file' => file_exists(dirname(__DIR__) . '/.env'),
    'session_status' => session_status() === PHP_SESSION_ACTIVE,
    'https_status' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
    'writable_dirs' => [
        'logs' => is_writable(dirname(__DIR__) . '/logs') || is_writable(dirname(__DIR__)),
        'uploads' => is_writable(__DIR__ . '/upload') || is_writable(__DIR__ . '/user'),
        'assets' => is_writable(dirname(__DIR__) . '/assets')
    ]
];

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

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Include admin header
include 'admin_header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid py-4">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo $mlSupport->translate('System Diagnostics'); ?></h1>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title mb-0 fw-bold text-primary">
                                    <i class="fas fa-stethoscope me-2"></i><?php echo $mlSupport->translate('System Health Check'); ?>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4"><?php echo $mlSupport->translate('Component'); ?></th>
                                                <th class="border-0"><?php echo $mlSupport->translate('Status'); ?></th>
                                                <th class="border-0"><?php echo $mlSupport->translate('Details'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Database Connection -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                                            <i class="fas fa-database"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('Database Connection'); ?></h6>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill bg-soft-<?= $diagnostics['database']['connection_status'] ? 'success' : 'danger' ?> text-<?= $diagnostics['database']['connection_status'] ? 'success' : 'danger' ?>">
                                                        <i class="fas fa-<?= $diagnostics['database']['connection_status'] ? 'check' : 'times' ?>-circle me-1"></i>
                                                        <?= $diagnostics['database']['connection_status'] ? $mlSupport->translate('Connected') : $mlSupport->translate('Failed') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0">
                                                        <span class="text-muted"><?php echo $mlSupport->translate('Connection Time'); ?>:</span> <?= h($diagnostics['database']['connection_time']) ?>s
                                                    </p>
                                                    <?php if (!empty($diagnostics['database']['errors'])): ?>
                                                        <p class="text-xs text-danger mb-0 mt-1">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            <?= h(implode(', ', $diagnostics['database']['errors'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            
                                            <!-- PHP Version -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-info bg-opacity-10 text-info rounded p-2 me-3">
                                                            <i class="fab fa-php"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('PHP Version'); ?></h6>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill bg-soft-info text-info">
                                                        <?= h($diagnostics['php_version']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0">
                                                        <i class="fas fa-info-circle me-1 text-info"></i>
                                                        <?= version_compare($diagnostics['php_version'], '7.4.0') >= 0 ? $mlSupport->translate('Compatible') : $mlSupport->translate('Upgrade Recommended') ?>
                                                    </p>
                                                </td>
                                            </tr>
                                            
                                            <!-- Server Info -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-secondary bg-opacity-10 text-secondary rounded p-2 me-3">
                                                            <i class="fas fa-server"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('Web Server'); ?></h6>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill bg-soft-secondary text-secondary">
                                                        <?= h(explode('/', $diagnostics['server_info'])[0]) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0 text-muted">
                                                        <?= h($diagnostics['server_info']) ?>
                                                    </p>
                                                </td>
                                            </tr>
                                            
                                            <!-- Disk Space -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-warning bg-opacity-10 text-warning rounded p-2 me-3">
                                                            <i class="fas fa-hdd"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('Disk Space'); ?></h6>
                                                    </div>
                                                </td>
                                                <?php 
                                                    $disk_ratio = (disk_free_space('/') / disk_total_space('/'));
                                                    $disk_class = $disk_ratio > 0.2 ? 'success' : ($disk_ratio > 0.1 ? 'warning' : 'danger');
                                                ?>
                                                <td>
                                                    <span class="badge rounded-pill bg-soft-<?= $disk_class ?> text-<?= $disk_class ?>">
                                                        <?= h($diagnostics['disk_free_space']) ?> <?php echo $mlSupport->translate('Free'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0 text-muted">
                                                        <?php echo $mlSupport->translate('Total'); ?>: <?= h($diagnostics['disk_total_space']) ?>
                                                    </p>
                                                </td>
                                            </tr>

                                            <!-- Environment & Sessions -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-dark bg-opacity-10 text-dark rounded p-2 me-3">
                                                            <i class="fas fa-cogs"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('System Environment'); ?></h6>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge rounded-pill bg-soft-<?= $diagnostics['env_file'] ? 'success' : 'danger' ?> text-<?= $diagnostics['env_file'] ? 'success' : 'danger' ?>">
                                                            .env: <?= $diagnostics['env_file'] ? $mlSupport->translate('Found') : $mlSupport->translate('Missing') ?>
                                                        </span>
                                                        <span class="badge rounded-pill bg-soft-<?= $diagnostics['session_status'] ? 'success' : 'warning' ?> text-<?= $diagnostics['session_status'] ? 'success' : 'warning' ?>">
                                                            <?php echo $mlSupport->translate('Session'); ?>: <?= $diagnostics['session_status'] ? $mlSupport->translate('Active') : $mlSupport->translate('Inactive') ?>
                                                        </span>
                                                        <span class="badge rounded-pill bg-soft-<?= $diagnostics['https_status'] ? 'success' : 'warning' ?> text-<?= $diagnostics['https_status'] ? 'success' : 'warning' ?>">
                                                            SSL: <?= $diagnostics['https_status'] ? $mlSupport->translate('Enabled') : $mlSupport->translate('Disabled') ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0 text-muted">
                                                        <?php echo $mlSupport->translate('Environment configuration and session state'); ?>
                                                    </p>
                                                </td>
                                            </tr>

                                            <!-- Directory Permissions -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                                            <i class="fas fa-folder-open"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('Directory Permissions'); ?></h6>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php foreach ($diagnostics['writable_dirs'] as $dir => $writable): ?>
                                                            <span class="badge rounded-pill bg-soft-<?= $writable ? 'success' : 'danger' ?> text-<?= $writable ? 'success' : 'danger' ?>">
                                                                <?= ucfirst($dir) ?>: <?= $writable ? $mlSupport->translate('Writable') : $mlSupport->translate('Locked') ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0 text-muted">
                                                        <?php echo $mlSupport->translate('Check for write access to critical folders'); ?>
                                                    </p>
                                                </td>
                                            </tr>

                                            <!-- PHP Extensions -->
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-3">
                                                            <i class="fas fa-puzzle-piece"></i>
                                                        </div>
                                                        <h6 class="mb-0 fw-bold"><?php echo $mlSupport->translate('PHP Extensions'); ?></h6>
                                                    </div>
                                                </td>
                                                <?php 
                                                    $ext_count = array_sum(array_column($diagnostics['extensions_check'], 'installed'));
                                                    $ext_total = count($diagnostics['extensions_check']);
                                                    $ext_class = $ext_count == $ext_total ? 'success' : 'warning';
                                                ?>
                                                <td>
                                                    <span class="badge rounded-pill bg-soft-<?= $ext_class ?> text-<?= $ext_class ?>">
                                                        <?= $ext_count ?>/<?= $ext_total ?> <?php echo $mlSupport->translate('Installed'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-sm">
                                                        <?php foreach ($diagnostics['extensions_check'] as $ext => $status): ?>
                                                            <div class="mb-1">
                                                                <span class="fw-bold"><?= h($ext) ?>:</span> 
                                                                <span class="<?= $status['installed'] ? 'text-success' : 'text-danger' ?>">
                                                                    <i class="fas fa-<?= $status['installed'] ? 'check' : 'times' ?>-circle me-1"></i>
                                                                    <?= $status['installed'] ? $mlSupport->translate('Installed') : $mlSupport->translate('Missing') ?>
                                                                </span>
                                                                <span class="text-muted small ms-1">(<?= h($status['purpose']) ?>)</span>
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
            </main>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

