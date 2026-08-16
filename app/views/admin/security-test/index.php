<?php
$overallScore = $overall_score ?? 0;
$results = $results ?? null;
$lastRun = $last_run ?? null;
$recommendations = $recommendations ?? [];
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$base = defined('BASE_URL') ? BASE_URL : '';

$scoreColor = $overallScore >= 80 ? '#22c55e' : ($overallScore >= 50 ? '#eab308' : '#ef4444');
$scoreBg = $overallScore >= 80 ? 'success' : ($overallScore >= 50 ? 'warning' : 'danger');

$testLabels = [
    'https'             => ['icon' => 'fas fa-lock', 'label' => 'HTTPS Configuration'],
    'security_headers'  => ['icon' => 'fas fa-shield-alt', 'label' => 'Security Headers'],
    'session_security'  => ['icon' => 'fas fa-cookie-bite', 'label' => 'Session Security'],
    'csrf_protection'   => ['icon' => 'fas fa-key', 'label' => 'CSRF Protection'],
    'input_validation'  => ['icon' => 'fas fa-filter', 'label' => 'Input Validation'],
    'file_upload'       => ['icon' => 'fas fa-file-upload', 'label' => 'File Upload Security'],
    'auth_strength'     => ['icon' => 'fas fa-user-shield', 'label' => 'Authentication Strength'],
    'rate_limiting'     => ['icon' => 'fas fa-tachometer-alt', 'label' => 'Rate Limiting'],
    'error_handling'    => ['icon' => 'fas fa-bug', 'label' => 'Error Handling'],
    'database_security' => ['icon' => 'fas fa-database', 'label' => 'Database Security'],
];
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-shield-alt me-2 text-primary"></i>Security Test Suite</h4>
            <p class="text-muted small mb-0">Automated security posture validation — 10 checks across your stack.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/security-test/report" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-pdf me-1"></i>Export Report
            </a>
            <form method="POST" action="<?= $base ?>/admin/security-test/run" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-play me-1"></i>Run Tests Again
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body text-center py-5">
                    <div class="style-20273">
                        <span class="style-43086"><?= $overallScore ?></span>
                    </div>
                    <h5 class="fw-bold mb-1">Overall Security Score</h5>
                    <span class="badge bg-<?= $scoreBg ?> fs-6"><?= $overallScore >= 80 ? 'Secure' : ($overallScore >= 50 ? 'Needs Improvement' : 'Critical') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body py-5">
                    <h6 class="text-muted text-uppercase small mb-3">Test Summary</h6>
                    <?php
                    $passCount = 0;
                    $failCount = 0;
                    $warnCount = 0;
                    if ($results) {
                        foreach ($results as $r) {
                            if ($r['status'] === 'pass') $passCount++;
                            elseif ($r['status'] === 'fail') $failCount++;
                            else $warnCount++;
                        }
                    }
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2 fs-5"></i>
                        <span class="fw-semibold">Passed</span>
                        <span class="badge bg-success ms-auto"><?= $passCount ?>/10</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exclamation-triangle text-warning me-2 fs-5"></i>
                        <span class="fw-semibold">Warnings</span>
                        <span class="badge bg-warning ms-auto"><?= $warnCount ?>/10</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle text-danger me-2 fs-5"></i>
                        <span class="fw-semibold">Failed</span>
                        <span class="badge bg-danger ms-auto"><?= $failCount ?>/10</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body py-5">
                    <h6 class="text-muted text-uppercase small mb-3">Last Run</h6>
                    <?php if ($lastRun): ?>
                        <p class="fw-semibold mb-1"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($lastRun) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-1">No tests run yet</p>
                    <?php endif; ?>
                    <h6 class="text-muted text-uppercase small mt-3 mb-2">Quick Actions</h6>
                    <a href="<?= $base ?>/admin/security-test/report" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-file-alt me-1"></i>View Full Report
                    </a>
                    <a href="<?= $base ?>/admin/cache" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-bolt me-1"></i>Cache Management
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($results): ?>
    <h5 class="fw-bold mb-3"><i class="fas fa-list-check me-2"></i>Test Results</h5>
    <div class="row g-3 mb-4">
        <?php foreach ($results as $key => $result):
            $meta = $testLabels[$key] ?? ['icon' => 'fas fa-check-circle', 'label' => $key];
            $statusCls = $result['status'] === 'pass' ? 'success' : ($result['status'] === 'warning' ? 'warning' : 'danger');
            $statusIcon = $result['status'] === 'pass' ? 'fas fa-check-circle' : ($result['status'] === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-times-circle');
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <i class="<?= $meta['icon'] ?> text-<?= $statusCls ?> me-2 fs-5"></i>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($meta['label']) ?></h6>
                        </div>
                        <span class="badge bg-<?= $statusCls ?>"><?= strtoupper($result['status']) ?></span>
                    </div>
                    <div class="progress mb-2" class="style-51910">
                        <div class="progress-bar bg-<?= $statusCls ?>" class="style-52052"></div>
                    </div>
                    <small class="text-muted d-block mb-1"><?= htmlspecialchars($result['details']) ?></small>
                    <?php if (!empty($result['recommendation'])): ?>
                        <small class="text-<?= $statusCls ?> d-block"><i class="fas fa-lightbulb me-1"></i><?= htmlspecialchars($result['recommendation']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
            <h5>No Test Results Yet</h5>
            <p class="text-muted mb-3">Run the security test suite to validate your application's security posture.</p>
            <form method="POST" action="<?= $base ?>/admin/security-test/run">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play me-1"></i>Run Security Tests
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($recommendations)): ?>
    <h5 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i>Recommendations</h5>
    <div class="row g-3 mb-4">
        <?php foreach ($recommendations as $rec):
            $priorityCls = $rec['priority'] === 'critical' ? 'danger' : ($rec['priority'] === 'high' ? 'warning' : 'info');
        ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-4 border-<?= $priorityCls ?>">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-<?= $priorityCls ?> mb-1"><?= strtoupper($rec['priority']) ?></span>
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($rec['test']) ?></h6>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($rec['recommendation']) ?></p>
                        </div>
                        <span class="badge bg-secondary"><?= $rec['score'] ?>/100</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
