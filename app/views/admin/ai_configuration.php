<?php $pageTitle = $pageTitle ?? 'AI Configuration'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-robot me-2"></i>AI Configuration</h4>
        <button class="btn btn-success btn-sm"><i class="fas fa-save me-1"></i>Save Config</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Configuration</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $cfg = $config ?? []; ?>
                    <div class="mb-3">
                        <label class="form-label">AI Provider</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($cfg['provider'] ?? 'OpenAI') ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($cfg['model'] ?? 'gpt-4') ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Temperature</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($cfg['temperature'] ?? '0.7') ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Tokens</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($cfg['max_tokens'] ?? '2048') ?>" readonly>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" <?= ($cfg['enabled'] ?? false) ? 'checked' : '' ?> disabled>
                        <label class="form-check-label">AI Enabled</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Available Models</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light">
                                <tr><th>Model</th><th>Version</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($models)): ?>
                                    <?php foreach ($models as $m): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($m['name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($m['version'] ?? '-') ?></td>
                                            <td><span class="badge bg-<?= ($m['status'] ?? 'inactive') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($m['status'] ?? 'inactive') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No models configured</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
