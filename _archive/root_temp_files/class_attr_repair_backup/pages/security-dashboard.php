<?php
$page_title = $page_title ?? __('security_dash_title', [], 'Security Dashboard - APS Dream Home');
$page_description = $page_description ?? __('security_dash_desc', [], 'Monitor and manage system security');
$security_stats = $security_stats ?? ['total_events' => 0, 'blocked_attempts' => 0, 'failed_logins' => 0, 'suspicious_activities' => 0, 'security_score' => 100];
$recent_events = $recent_events ?? [];
$vulnerabilities = $vulnerabilities ?? [];
$error = $error ?? null;
$base = $base ?? BASE_URL;
?>

<section class="py-5 bg-gradient-danger text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100 style-17697"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><i class="fas fa-shield-alt me-3"></i><?= __('security_dash_heading', [], 'Security Dashboard') ?></h1>
                <p class="lead mb-0"><?= __('security_dash_subtitle', [], 'Monitor, analyze, and protect your system') ?></p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button class="btn btn-outline-light" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-1"></i> <?= __('security_dash_refresh', [], 'Refresh') ?>
                </button>
            </div>
        </div>
    </div>
</section>

<?php if ($error): ?>
<div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<section class="py-4">
    <div class="container">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-5 text-primary mb-2"><?= htmlspecialchars($security_stats['security_score'] ?? 0) ?><small class="fs-6">%</small></div>
                        <h6 class="text-muted mb-0"><?= __('security_dash_score', [], 'Security Score') ?></h6>
                        <div class="progress mt-2 style-83142">
                            <div class="progress-bar bg-<?= ($security_stats['security_score'] ?? 0) >= 80 ? 'success' : (($security_stats['security_score'] ?? 0) >= 50 ? 'warning' : 'danger') ?>" class="style-81737"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-danger mb-2"><?= htmlspecialchars(number_format($security_stats['total_events'] ?? 0)) ?></div>
                        <h6 class="text-muted mb-0"><?= __('security_dash_total_events', [], 'Total Events (24h)') ?></h6>
                        <small class="text-muted"><?= __('security_dash_all_events', [], 'All security events') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-warning mb-2"><?= htmlspecialchars(number_format($security_stats['failed_logins'] ?? 0)) ?></div>
                        <h6 class="text-muted mb-0"><?= __('security_dash_failed_logins', [], 'Failed Logins') ?></h6>
                        <small class="text-muted"><?= __('security_dash_unsuccessful', [], 'Unsuccessful attempts') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-info mb-2"><?= htmlspecialchars(number_format($security_stats['suspicious_activities'] ?? 0)) ?></div>
                        <h6 class="text-muted mb-0"><?= __('security_dash_suspicious', [], 'Suspicious') ?></h6>
                        <small class="text-muted"><?= __('security_dash_high_severity', [], 'High severity alerts') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i><?= __('security_dash_recent_events', [], 'Recent Security Events') ?></h5>
                        <span class="badge bg-primary"><?= count($recent_events) ?> <?= __('security_dash_events_badge', [], 'events') ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($recent_events)): ?>
                        <div class="table-responsive">
                            <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= __('security_dash_th_action', [], 'Action') ?></th>
                                        <th><?= __('security_dash_th_description', [], 'Description') ?></th>
                                        <th><?= __('security_dash_th_ip', [], 'IP') ?></th>
                                        <th><?= __('security_dash_th_severity', [], 'Severity') ?></th>
                                        <th><?= __('security_dash_th_time', [], 'Time') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_events as $event): ?>
                                    <tr>
                                        <td><span class="badge bg-<?= ($event['action'] ?? '') === 'blocked' ? 'danger' : (($event['action'] ?? '') === 'login_failed' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($event['action'] ?? 'N/A') ?></span></td>
                                        <td class="small"><?= htmlspecialchars($event['description'] ?? 'N/A') ?></td>
                                        <td><code><?= htmlspecialchars($event['ip_address'] ?? 'N/A') ?></code></td>
                                        <td>
                                            <span class="badge bg-<?= ($event['severity'] ?? '') === 'high' ? 'danger' : (($event['severity'] ?? '') === 'medium' ? 'warning' : 'info') ?>">
                                                <?= htmlspecialchars(ucfirst($event['severity'] ?? 'low')) ?>
                                            </span>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars(date('d M H:i', strtotime($event['created_at'] ?? 'now'))) ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table></div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0"><?= __('security_dash_no_events', [], 'No recent security events') ?></p>
                            <small><?= __('security_dash_all_clean', [], 'Everything looks clean!') ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-bug me-2 text-warning"></i><?= __('security_dash_vulnerabilities', [], 'Open Vulnerabilities') ?></h5>
                        <span class="badge bg-warning"><?= count($vulnerabilities) ?> <?= __('security_dash_open_badge', [], 'open') ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($vulnerabilities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($vulnerabilities as $vuln): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-<?= ($vuln['severity'] ?? '') === 'critical' ? 'danger' : (($vuln['severity'] ?? '') === 'high' ? 'warning' : 'info') ?> me-1">
                                            <?= htmlspecialchars(ucfirst($vuln['severity'] ?? 'low')) ?>
                                        </span>
                                        <small class="text-muted"><?= htmlspecialchars($vuln['type'] ?? 'N/A') ?></small>
                                    </div>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($vuln['status'] ?? 'open') ?></span>
                                </div>
                                <p class="small mb-0 mt-1"><?= htmlspecialchars($vuln['description'] ?? __('security_dash_no_description', [], 'No description')) ?></p>
                                <small class="text-muted"><?= htmlspecialchars(date('d M Y', strtotime($vuln['created_at'] ?? 'now'))) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shield-virus fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0"><?= __('security_dash_no_vulns', [], 'No vulnerabilities detected') ?></p>
                            <small><?= __('security_dash_system_secure', [], 'System is secure') ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
