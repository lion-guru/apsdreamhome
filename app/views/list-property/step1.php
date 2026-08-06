<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
$state_data = $state['form_data'] ?? [];
$old = $old ?? [];
$pt = $old['propertyType'] ?? $state_data['propertyType'] ?? '';
$lt = $old['listingType'] ?? $state_data['listingType'] ?? 'sell';
$t = $old['title'] ?? $state_data['title'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 1: Basics | List Property | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>List Your Property</h2>
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include __DIR__ . '/progress.php'; ?>
        </div>
        <div class="col-lg-9">
            <div class="wizard-card card">
                <div class="card-body p-4">
                    <h4 class="mb-1">Step 1: Basics</h4>
                    <p class="text-muted">Tell us what kind of property you're listing.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/list-property/step1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Property Type *</label>
                            <select name="property_type" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach (['plot','flat','house','shop','farmhouse','land','apartment','villa'] as $t1): ?>
                                    <option value="<?= $t1 ?>" <?= $pt === $t1 ? 'selected' : '' ?>><?= ucfirst($t1) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Listing Type *</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="listing_type" id="lt-sell" value="sell" <?= $lt === 'sell' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="lt-sell"><i class="fas fa-tag me-1"></i> Sell</label>
                                <input type="radio" class="btn-check" name="listing_type" id="lt-rent" value="rent" <?= $lt === 'rent' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="lt-rent"><i class="fas fa-key me-1"></i> Rent</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required minlength="5" maxlength="200"
                                   value="<?= htmlspecialchars($t) ?>" placeholder="e.g. 2 BHK Flat in Gorakhpur">
                        </div>
                        <div class="d-flex justify-content-end mt-4">
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
