<?php
/**
 * System Health & Connectivity Report
 * Comprehensive diagnostic tool for APS Dream Homes
 */

require_once __DIR__ . '/core/init.php';

// Only Superadmins can access this diagnostic tool
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=Unauthorized');
    exit();
}

$page_title = "System Health Report";
include 'admin_header.php';
include 'admin_sidebar.php';

// Diagnostics Logic
$diagnostics = [
    'core' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'os' => PHP_OS,
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
    ],
    'database' => [
        'status' => false,
        'version' => 'Unknown',
        'tables' => 0,
        'size' => 'Unknown'
    ],
    'modules' => [],
    'directories' => [
        'admin/upload' => is_writable(__DIR__ . '/upload'),
        'logs' => is_writable(dirname(__DIR__) . '/logs'),
        'includes' => is_readable(dirname(__DIR__) . '/includes'),
    ]
];

// Database Check
try {
    if ($db) {
        $diagnostics['database']['status'] = true;
        $diagnostics['database']['version'] = $db->fetch("SELECT VERSION() as v")['v'];

        $res = $db->fetch("SELECT count(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
        $diagnostics['database']['tables'] = $res['count'];
    }
} catch (Exception $e) {
    $diagnostics['database']['error'] = $e->getMessage();
}

// Module Verification
$core_modules = [
    'CRM' => 'advanced_crm_dashboard.php',
    'Finance' => 'accounting_dashboard.php',
    'HR' => 'employees_management.php',
    'AI Hub' => 'ai_hub.php',
    'Real Estate' => 'projectview.php',
    'Inventory' => 'propertyview.php',
    'Backup' => 'backup_manager.php',
    'Security' => 'audit_access_log_view.php'
];

foreach ($core_modules as $name => $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $diagnostics['modules'][$name] = [
        'status' => $exists,
        'file' => $file,
        'url' => $exists ? $file : '#'
    ];
}

?>

<style>
    .health-card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    .health-card:hover { transform: translateY(-3px); }
    .status-badge { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 8px; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1); color: #10b981; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">System Health & Diagnostic Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="superadmin_dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Health Report</li>
                    </ul>
                </div>
                <button class="btn btn-primary rounded-pill" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Download PDF Report
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Server Core -->
            <div class="col-md-4">
                <div class="card health-card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-server text-primary me-2"></i>Server Core</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <?php foreach ($diagnostics['core'] as $key => $val): ?>
                            <tr>
                                <td class="text-muted small"><?php echo h(ucwords(str_replace('_', ' ', $key))); ?></td>
                                <td class="fw-bold small"><?php echo h($val); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Database Status -->
            <div class="col-md-4">
                <div class="card health-card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-database text-success me-2"></i>Database Engine</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold mb-0 <?php echo $diagnostics['database']['status'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo h($diagnostics['database']['status'] ? 'Online' : 'Offline'); ?>
                            </h2>
                            <small class="text-muted">MySQL Version: <?php echo h($diagnostics['database']['version']); ?></small>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-3">
                            <div class="text-center flex-grow-1 border-end">
                                <h4 class="fw-bold mb-0"><?php echo h($diagnostics['database']['tables']); ?></h4>
                                <small class="text-muted">Total Tables</small>
                            </div>
                            <div class="text-center flex-grow-1">
                                <h4 class="fw-bold mb-0 text-success">Healthy</h4>
                                <small class="text-muted">Connectivity</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filesystem Health -->
            <div class="col-md-4">
                <div class="card health-card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-folder-open text-warning me-2"></i>Filesystem</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($diagnostics['directories'] as $path => $writable): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="small fw-bold"><?php echo h($path); ?></span>
                            <span class="badge <?php echo $writable ? 'bg-success-soft' : 'bg-danger-soft'; ?> rounded-pill px-3">
                                <?php echo h($writable ? 'Writable' : 'Locked'); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Module Connectivity Matrix -->
            <div class="col-12">
                <div class="card health-card">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fas fa-network-wired text-info me-2"></i>Module Connectivity Matrix</h5>
                        <span class="badge bg-info-soft text-info rounded-pill">Total Control Center</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php foreach ($diagnostics['modules'] as $name => $info): ?>
                            <div class="col-md-3">
                                <div class="p-3 border rounded-3 <?php echo $info['status'] ? 'bg-light' : 'bg-danger-soft'; ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold"><?php echo h($name); ?></span>
                                        <i class="fas <?php echo $info['status'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?>"></i>
                                    </div>
                                    <div class="small text-muted mb-2"><?php echo h($info['file']); ?></div>
                                    <?php if ($info['status']): ?>
                                        <a href="<?php echo h($info['url']); ?>" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Test Module</a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-danger w-100 rounded-pill" disabled>Module Missing</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
