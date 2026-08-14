<?php
$page_title = $page_title ?? 'Translations';
$languages = $languages ?? [];
$translation_stats = $translation_stats ?? [];
$supported_languages = $supported_languages ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-1"><i class="fas fa-language me-2 text-primary"></i>Translation Management</h2>
        <a href="<?php echo $base; ?>/admin/settings" class="btn btn-outline-secondary">Back</a>
    </div>

    <!-- Language Stats -->
    <div class="row mb-4">
        <?php foreach ($languages as $code => $lang): ?>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><?php echo htmlspecialchars($lang['name'] ?? $code); ?></h6>
                            <span class="badge bg-<?php echo ($lang['completion'] ?? 0) >= 80 ? 'success' : (($lang['completion'] ?? 0) >= 50 ? 'warning' : 'danger'); ?>">
                                <?php echo round($lang['completion'] ?? 0); ?>%
                            </span>
                        </div>
                        <div class="progress mb-2" class="style-29939">
                            <div class="progress-bar bg-primary" class="style-16029"></div>
                        </div>
                        <small class="text-muted">
                            <?php echo $lang['translated'] ?? 0; ?> / <?php echo $lang['total'] ?? 0; ?> keys
                            <?php if ($code === 'en'): ?> (default)<?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Translation Stats -->
    <?php if (!empty($translation_stats)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Overview</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h4 class="text-primary"><?php echo $translation_stats['total_keys'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">Total Keys</p>
                </div>
                <div class="col-md-3">
                    <h4 class="text-success"><?php echo $translation_stats['translated'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">Translated</p>
                </div>
                <div class="col-md-3">
                    <h4 class="text-warning"><?php echo $translation_stats['missing'] ?? 0; ?></h4>
                    <p class="text-muted mb-0">Missing</p>
                </div>
                <div class="col-md-3">
                    <h4 class="text-info"><?php echo count($supported_languages); ?></h4>
                    <p class="text-muted mb-0">Languages</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
            <h5>Translation Editor</h5>
            <p class="text-muted">Use the i18n panel in the codebase or the API to manage translations.<br>
            File locations: <code>app/views/languages/{lang_code}.php</code></p>
            <p class="text-muted mb-0">Total i18n keys: <strong>815+</strong> across English and Hindi.</p>
        </div>
    </div>
</div>
