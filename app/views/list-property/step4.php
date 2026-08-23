<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
$d = $state['form_data'] ?? [];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 4: Pricing | List Property | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<div class="container">
    <h2 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>List Your Property</h2>
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/progress.php'; ?></div>
        <div class="col-lg-9">
            <div class="wizard-card card">
                <div class="card-body p-4">
                    <h4 class="mb-1">Step 4: Pricing</h4>
                    <p class="text-muted">Set your asking price.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e ?? '') ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/list-property/step4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price *</label>
                                <input type="number" step="0.01" name="price" class="form-control" required min="1" value="<?= htmlspecialchars($d['price'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit</label>
                                <select name="price_type" class="form-select">
                                    <?php $pt = $d['priceType'] ?? 'lakh'; ?>
                                    <?php foreach (['lakh','crore','thousand','per sqft','per month'] as $u): ?>
                                        <option value="<?= e($u ?? '') ?>" <?= $pt === $u ? 'selected' : '' ?>><?= e($u ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="negotiable" id="neg" value="1" <?= !empty($d['negotiable']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="neg">Price is negotiable</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="emi_available" id="emi" value="1" <?= !empty($d['emiAvailable']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="emi">EMI available</label>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/list-property/step3" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                            <button type="submit" class="btn btn-primary px-4">Next <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
