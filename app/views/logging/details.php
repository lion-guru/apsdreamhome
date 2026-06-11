<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Log Details') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/logging/logs" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Logs</a>
    </div>
    <?php if (!empty($log)): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Log Entry</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-borderless table-responsive">
                        <tr><td style="width:140px"><strong>Level</strong></td><td><span class="badge bg-<?= ($log['level'] ?? 'info') === 'error' ? 'danger' : (($log['level'] ?? 'info') === 'warning' ? 'warning' : 'info') ?>"><?= htmlspecialchars($log['level'] ?? 'info') ?></span></td></tr>
                        <tr><td><strong>Message</strong></td><td><?= htmlspecialchars($log['message'] ?? '') ?></td></tr>
                        <tr><td><strong>File</strong></td><td><code><?= htmlspecialchars($log['file'] ?? '-') ?></code></td></tr>
                        <tr><td><strong>Line</strong></td><td><?= (int)($log['line'] ?? 0) ?></td></tr>
                        <tr><td><strong>Function</strong></td><td><?= htmlspecialchars($log['function'] ?? '-') ?></td></tr>
                        <tr><td><strong>IP</strong></td><td><code><?= htmlspecialchars($log['ip'] ?? '-') ?></code></td></tr>
                        <tr><td><strong>User Agent</strong></td><td class="text-truncate" style="max-width:500px"><?= htmlspecialchars($log['user_agent'] ?? '-') ?></td></tr>
                        <tr><td><strong>Timestamp</strong></td><td><?= htmlspecialchars($log['created_at'] ?? '') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Stack Trace</h5></div>
                <div class="card-body p-0">
                    <pre class="mb-0 p-3" style="max-height:400px;overflow:auto;font-size:12px;background:#f8f9fa;border-radius:0 0 0.375rem 0.375rem"><?= htmlspecialchars($log['trace'] ?? 'No stack trace available.') ?></pre>
                </div>
            </div>
            <?php if (!empty($log['context'])): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-code me-2"></i>Context</h5></div>
                <div class="card-body p-0">
                    <pre class="mb-0 p-3" style="max-height:300px;overflow:auto;font-size:12px;background:#f8f9fa;border-radius:0 0 0.375rem 0.375rem"><?= htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT) ?: '{}') ?></pre>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted"><i class="fas fa-question-circle fa-4x d-block mb-3"></i><p>Log entry not found.</p></div>
    </div>
    <?php endif; ?>
</div>
