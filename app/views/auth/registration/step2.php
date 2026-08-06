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
    <title>Step 2: Profile | Register | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        body { background: linear-gradient(135deg, #0d9488, #0f766e); min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { max-width: 560px; margin: auto; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .wizard-header { background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; border-radius: 16px 16px 0 0; padding: 1.5rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="wizard-card">
        <div class="wizard-header text-center">
            <h3 class="fw-bold mb-0">Profile Details</h3>
            <small class="opacity-75">Step 2 of 4 &middot; Tell us about yourself</small>
        </div>
        <div class="card-body p-4">
            <?php include __DIR__ . '/progress.php'; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register/step2" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="mb-3">
                    <label class="form-label">City *</label>
                    <input type="text" name="city" class="form-control" required
                           value="<?= htmlspecialchars($old['city'] ?? ($state['form_data']['city'] ?? '')) ?>" placeholder="e.g. Gorakhpur">
                </div>
                <div class="mb-3">
                    <label class="form-label">Occupation *</label>
                    <select name="occupation" class="form-select" required>
                        <?php $occ = $old['occupation'] ?? ($state['form_data']['occupation'] ?? ''); ?>
                        <option value="">-- Select --</option>
                        <?php foreach (['Salaried','Self-Employed','Business Owner','Professional','Student','Homemaker','Retired','Other'] as $o): ?>
                            <option value="<?= $o ?>" <?= $occ === $o ? 'selected' : '' ?>><?= $o ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Annual Income Range *</label>
                    <select name="income_range" class="form-select" required>
                        <?php $inc = $old['income_range'] ?? ($state['form_data']['income_range'] ?? ''); ?>
                        <option value="">-- Select --</option>
                        <?php foreach (['Under 3 Lakh','3-6 Lakh','6-10 Lakh','10-20 Lakh','20-50 Lakh','Above 50 Lakh'] as $i): ?>
                            <option value="<?= $i ?>" <?= $inc === $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= BASE_URL ?>/register/step1" class="btn btn-outline-secondary">
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
