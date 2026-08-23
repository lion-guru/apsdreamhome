<?php
$page_title = $page_title ?? 'CSP Violations';
$page_heading = $page_heading ?? 'Content-Security-Policy Violations';
$violations = $rows ?? [];
$stats = $stats ?? ['total' => 0, 'directives' => 0, 'unique_ips' => 0];
$limit = $limit ?? 50;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($page_heading ?? '') ?></h1>
            <p class="text-muted mb-0">Browser-reported CSP violations from the last 30 days.</p>
        </div>
        <div class="btn-group">
            <a href="?limit=50" class="btn btn-sm btn-outline-secondary <?= $limit == 50 ? 'active' : '' ?>">50</a>
            <a href="?limit=200" class="btn btn-sm btn-outline-secondary <?= $limit == 200 ? 'active' : '' ?>">200</a>
            <a href="?limit=500" class="btn btn-sm btn-outline-secondary <?= $limit == 500 ? 'active' : '' ?>">500</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Total violations</div>
                    <div class="h2 mb-0"><?= number_format((int)$stats['total']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Unique directives</div>
                    <div class="h2 mb-0"><?= number_format((int)$stats['directives']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Unique source IPs</div>
                    <div class="h2 mb-0"><?= number_format((int)$stats['unique_ips']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="style-35962">Received</th>
                        <th>Document</th>
                        <th>Directive</th>
                        <th>Blocked URI</th>
                        <th>Source</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($violations)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No CSP violations recorded yet. Violations will appear here as browsers report them.</td></tr>
                <?php else: foreach ($violations as $v): ?>
                    <tr>
                        <td><small class="text-muted"><?= htmlspecialchars($v['received_at'] ?? '') ?></small></td>
                        <td><small><code class="text-truncate d-inline-block style-1873"><?= htmlspecialchars($v['document_uri'] ?? '') ?></code></small></td>
                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($v['violated_directive'] ?? '') ?></span></td>
                        <td><small><code><?= htmlspecialchars($v['blocked_uri'] ?? '') ?></code></small></td>
                        <td><small class="text-muted"><?= htmlspecialchars(($v['source_file'] ?? '') . ($v['line_number'] ? ':' . $v['line_number'] : '')) ?></small></td>
                        <td><small class="text-muted"><?= htmlspecialchars($v['ip'] ?? '') ?></small></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
