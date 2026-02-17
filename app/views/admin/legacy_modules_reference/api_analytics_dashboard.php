<?php
/**
 * APS Dream Home - API Usage Analytics Dashboard
 * Monitor API key usage, quotas, and developer activity
 */

require_once 'core/init.php';

// Permission check
if (!isset($permission_util)) {
    require_once __DIR__ . '/../includes/functions/permission_util.php';
}
require_permission('view_api_analytics');

$db = \App\Core\App::database();

// Audit Logging
if (function_exists('logAdminActivity')) {
    logAdminActivity("Viewed API Usage Analytics", "Accessed the API usage analytics dashboard");
}

// Performance Manager for caching
require_once __DIR__ . '/../includes/performance_manager.php';
$perfManager = getPerformanceManager();

// Check if api_usage table exists, if not create it
$db->execute("
    CREATE TABLE IF NOT EXISTS api_usage (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dev_name VARCHAR(255),
        api_key VARCHAR(64),
        endpoint VARCHAR(255),
        usage_count INT DEFAULT 1,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_dev (dev_name),
        INDEX idx_endpoint (endpoint)
    )
");

// Fetch API usage data
$usage = $perfManager->executeCachedQuery("
    SELECT * FROM api_usage 
    ORDER BY timestamp DESC 
    LIMIT 50
", 60);

// Fetch summary stats
$summary = $perfManager->executeCachedQuery("
    SELECT 
        COUNT(DISTINCT dev_name) as total_devs,
        SUM(usage_count) as total_calls,
        COUNT(DISTINCT endpoint) as unique_endpoints
    FROM api_usage
", 300);

$stats = $summary[0] ?? ['total_devs' => 0, 'total_calls' => 0, 'unique_endpoints' => 0];

$page_title = $mlSupport->translate('API Usage Analytics');
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('API Usage Analytics')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="analytics.php"><?php echo h($mlSupport->translate('Analytics')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('API Usage')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75"><?php echo h($mlSupport->translate('Total Developers')); ?></h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo h(number_format($stats['total_devs'])); ?></h2>
                            </div>
                            <div class="opacity-25">
                                <i class="fas fa-code fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75"><?php echo h($mlSupport->translate('Total API Calls')); ?></h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo h(number_format($stats['total_calls'])); ?></h2>
                            </div>
                            <div class="opacity-25">
                                <i class="fas fa-exchange-alt fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-info text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75"><?php echo h($mlSupport->translate('Active Endpoints')); ?></h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo h(number_format($stats['unique_endpoints'])); ?></h2>
                            </div>
                            <div class="opacity-25">
                                <i class="fas fa-plug fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i><?php echo h($mlSupport->translate('Recent API Usage Activity')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Developer')); ?></th>
                                        <th><?php echo h($mlSupport->translate('API Key')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Endpoint')); ?></th>
                                        <th class="text-center"><?php echo h($mlSupport->translate('Usage Count')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Timestamp')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usage)): ?>
                                        <?php foreach ($usage as $u): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo h($u['dev_name']); ?></td>
                                                <td><code><?php echo h(substr($u['api_key'] ?? '', 0, 8)) . '...'; ?></code></td>
                                                <td><span class="badge bg-soft-info text-info"><?php echo h($u['endpoint']); ?></span></td>
                                                <td class="text-center">
                                                    <span class="fw-bold"><?php echo h(number_format($u['usage_count'])); ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo h($u['timestamp']); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <?php echo h($mlSupport->translate('No API usage records found.')); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <?php echo h($mlSupport->translate('Monitor API usage, set quotas, and provide analytics to developers. Rate limiting integration ready.')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'admin_footer.php';
?>


