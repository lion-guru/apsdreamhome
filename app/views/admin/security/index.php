<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/security_scanner.php';

// Check admin permissions - Requires superadmin or admin with elevated privileges
if (!in_array(getAuthSubRole(), ['super_admin', 'superadmin'])) {
    setSessionget_flash('error_message', "Access denied. Super admin privileges required.");
    header('Location: ../dashboard.php');
    exit();
}

// Initialize scanner
$scanner = new SecurityScanner();
$scanResults = null;

// Handle scan request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_scan'])) {
    // CSRF Protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('error_message', "Invalid request token");
    } else {
        $scanResults = $scanner->runFullScan();
        setSessionget_flash('success_message', "Security scan completed successfully.");
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Security Scanner - APS Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .security-scan-results { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-critical { color: #dc3545; font-weight: bold; }
        .status-warning { color: #fd7e14; font-weight: bold; }
        .status-secure { color: #28a745; font-weight: bold; }
        .critical-issues { color: #dc3545; }
        .warnings { color: #fd7e14; }
        .info { color: #17a2b8; }
        .recommendations { color: #6f42c1; }
        .scan-summary { background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-shield-alt me-2"></i>Security Scanner</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Security Scanner</li>
            </ol>
        </nav>
    </div>

    <?php
    $success_message = getSessionget_flash('success_message');
    if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo h($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    $error_message = getSessionget_flash('error_message');
    if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-cog me-2"></i>Security Scan Configuration</h5>
        </div>
        <div class="card-body">
            <p>The security scanner will perform comprehensive checks for:</p>
            <ul>
                <li>File permissions and access controls</li>
                <li>Database security vulnerabilities</li>
                <li>Input validation implementation</li>
                <li>CSRF protection status</li>
                <li>Session security configuration</li>
                <li>File upload security</li>
                <li>SQL injection vulnerabilities</li>
                <li>XSS protection status</li>
                <li>Directory traversal vulnerabilities</li>
                <li>Sensitive file exposure</li>
            </ul>

            <form method="post" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" name="run_scan" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Run Security Scan
                </button>
            </form>
        </div>
    </div>

    <?php if ($scanResults): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Scan Results</h5>
            </div>
            <div class="card-body">
                <div class="security-scan-results">
                    <div class="scan-summary">
                        <p><strong>Overall Status:</strong>
                            <span class="status-<?php echo strtolower($scanResults['summary']['overall_status']); ?>">
                                <?php echo $scanResults['summary']['overall_status']; ?>
                            </span>
                        </p>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Critical Issues:</strong>
                                <span class="text-danger"><?php echo $scanResults['summary']['total_critical']; ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Warnings:</strong>
                                <span class="text-warning"><?php echo $scanResults['summary']['total_warnings']; ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Info:</strong>
                                <span class="text-info"><?php echo $scanResults['summary']['total_info']; ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Scan Time:</strong>
                                <span class="text-muted"><?php echo $scanResults['timestamp']; ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (count($scanResults['critical_issues']) > 0): ?>
                        <h3 class="text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Critical Issues</h3>
                        <ul class="critical-issues mb-4">
                            <?php foreach ($scanResults['critical_issues'] as $issue): ?>
                                <li><?php echo h($issue['message']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (count($scanResults['warnings']) > 0): ?>
                        <h3 class="text-warning mb-3"><i class="fas fa-exclamation-circle me-2"></i>Warnings</h3>
                        <ul class="warnings mb-4">
                            <?php foreach ($scanResults['warnings'] as $warning): ?>
                                <li><?php echo h($warning['message']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (count($scanResults['info']) > 0): ?>
                        <h3 class="text-info mb-3"><i class="fas fa-info-circle me-2"></i>Information</h3>
                        <ul class="info mb-4">
                            <?php foreach ($scanResults['info'] as $info): ?>
                                <li><?php echo h($info['message']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (count($scanResults['summary']['recommendations']) > 0): ?>
                        <h3 class="text-purple mb-3"><i class="fas fa-lightbulb me-2"></i>Recommendations</h3>
                        <ul class="recommendations mb-4">
                            <?php foreach ($scanResults['summary']['recommendations'] as $rec): ?>
                                <li><?php echo h($rec); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ($scanResults['summary']['overall_status'] === 'SECURE'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Excellent!</strong> No critical security issues found. Your application appears to be well-secured.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
