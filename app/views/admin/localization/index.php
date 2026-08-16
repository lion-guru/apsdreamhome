<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Localization Management</h1>
    </div>

    <?php if (!$serviceAvailable): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Localization service is not available. The translation database may not be initialized or required dependencies are missing.
    </div>
    <div class="card aps-cp-card">
        <div class="card-body text-center py-5">
            <i class="fas fa-language fa-4x text-muted mb-3"></i>
            <p class="text-muted">Localization service unavailable. Supported locales fallback: English (en), Hindi (hi).</p>
            <a href="<?= BASE_URL ?>/admin/settings" class="btn btn-outline-primary mt-2">
                <i class="fas fa-cog me-1"></i> Go to Settings
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Current Locale</h5>
                    <h2 class="text-primary"><?= htmlspecialchars($current_locale ?? 'en') ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Supported Locales</h5>
                    <h2 class="text-success"><?= count($supported_locales ?? []) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Translations</h5>
                    <h2 class="text-info"><?= isset($stats['total_translations']) ? (int)$stats['total_translations'] : 0 ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <h5 class="card-title mb-0">Supported Locales</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($supported_locales)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Locale Code</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supported_locales as $code => $name): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($code ?? '') ?></code></td>
                            <td><?= htmlspecialchars($name ?? '') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/localization/editor?locale=<?= urlencode($code) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i> Edit Translations
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">No locales configured.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
