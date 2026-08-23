<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../../Helpers/TranslationHelper.php';
$old = $old ?? [];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 3: Preferences | Register | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        body { background: linear-gradient(135deg, #0d9488, #0f766e); min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { max-width: 560px; margin: auto; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .wizard-header { background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; border-radius: 16px 16px 0 0; padding: 1.5rem; }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<div class="container">
    <div class="wizard-card">
        <div class="wizard-header text-center">
            <h3 class="fw-bold mb-0">Property Preferences</h3>
            <small class="opacity-75">Step 3 of 4 &middot; Help us personalise your experience</small>
        </div>
        <div class="card-body p-4">
            <?php include __DIR__ . '/progress.php'; ?>

            <form method="POST" action="<?= BASE_URL ?>/register/step3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="mb-3">
                    <label class="form-label">Property Type Interested In</label>
                    <?php $ptype = $state['form_data']['property_type'] ?? ''; ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (['plot','flat','house','villa','shop','farmhouse','land'] as $t): ?>
                            <input type="radio" class="btn-check" name="property_type" id="ptype-<?= e($t) ?>" value="<?= e($t) ?>" <?= $ptype === $t ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="ptype-<?= e($t) ?>"><?= e(ucfirst($t)) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Budget Range</label>
                    <?php $bud = $state['form_data']['budget_range'] ?? ''; ?>
                    <select name="budget_range" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach (['Under 20 Lakh','20-50 Lakh','50 Lakh - 1 Cr','1-3 Cr','3-5 Cr','Above 5 Cr'] as $b): ?>
                            <option value="<?= e($b) ?>" <?= $bud === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location Preference</label>
                    <input type="text" name="location_preference" class="form-control"
                           value="<?= htmlspecialchars($state['form_data']['location_preference'] ?? '') ?>" placeholder="e.g. Gorakhpur, Lucknow">
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= BASE_URL ?>/register/step2" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <div>
                        <button type="button" class="btn btn-link text-muted" onclick="document.getElementById('skip-form').submit();">Skip</button>
                        <button type="submit" class="btn btn-primary px-4">Next <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
            </form>
            <form id="skip-form" method="POST" action="<?= BASE_URL ?>/register/skip" class="d-none">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            </form>
        </div>
    </div>
</div>
</body>
</html>
