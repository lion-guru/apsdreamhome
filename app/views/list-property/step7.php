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
    <title>Step 7: Review | List Property | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>List Your Property</h2>
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/progress.php'; ?></div>
        <div class="col-lg-9">
            <div class="wizard-card card">
                <div class="card-body p-4">
                    <h4 class="mb-1">Step 7: Review</h4>
                    <p class="text-muted">Please review your listing before publishing.</p>

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Title</dt><dd class="col-sm-9"><?= htmlspecialchars($d['title'] ?? '-') ?></dd>
                        <dt class="col-sm-3">Type</dt><dd class="col-sm-9"><?= htmlspecialchars(ucfirst($d['propertyType'] ?? '-')) ?> for <?= htmlspecialchars($d['listingType'] ?? '-') ?></dd>
                        <dt class="col-sm-3">Location</dt><dd class="col-sm-9"><?= htmlspecialchars($d['address'] ?? '-') ?>, <?= htmlspecialchars($d['city'] ?? '-') ?><?= !empty($d['district']) ? ', ' . htmlspecialchars($d['district']) : '' ?><?= !empty($d['state']) ? ', ' . htmlspecialchars($d['state']) : '' ?><?= !empty($d['pincode']) ? ' - ' . htmlspecialchars($d['pincode']) : '' ?></dd>
                        <dt class="col-sm-3">Area</dt><dd class="col-sm-9"><?= htmlspecialchars($d['area'] ?? '-') ?> sqft<?= !empty($d['facing']) ? ' &middot; Facing ' . htmlspecialchars($d['facing']) : '' ?></dd>
                        <dt class="col-sm-3">Price</dt><dd class="col-sm-9">&#8377;<?= htmlspecialchars(number_format((float)($d['price'] ?? 0))) ?> <?= htmlspecialchars($d['priceType'] ?? 'lakh') ?><?= !empty($d['negotiable']) ? ' (Negotiable)' : '' ?><?= !empty($d['emiAvailable']) ? ' &middot; EMI' : '' ?></dd>
                        <dt class="col-sm-3">Amenities</dt><dd class="col-sm-9"><?= !empty($d['amenities']) ? htmlspecialchars(implode(', ', array_map(fn($a) => ucwords(str_replace('_', ' ', $a)), $d['amenities']))) : '-' ?></dd>
                        <dt class="col-sm-3">Images</dt><dd class="col-sm-9"><?= count($d['images'] ?? []) ?> uploaded</dd>
                    </dl>

                    <form method="POST" action="<?= BASE_URL ?>/list-property/step7">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/list-property/step6" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                            <button type="submit" class="btn btn-primary px-4">Continue <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
