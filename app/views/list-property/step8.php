<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
$d = $state['form_data'] ?? [];
$published = isset($_GET['published']) ? (int)$_GET['published'] : 0;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 8: Contact | List Property | APS Dream Home</title>
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
                    <h4 class="mb-1">Step 8: Contact &amp; Publish</h4>
                    <p class="text-muted">Tell us how to reach you.</p>

                    <?php if ($published > 0): ?>
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-1"></i> Listing Published!</h5>
                            Your property (ID #<?= $published ?>) is now pending review. We'll notify you once approved.
                            <hr>
                            <a href="<?= BASE_URL ?>/list-property/step1" class="btn btn-success"><i class="fas fa-plus me-1"></i> List Another</a>
                            <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i> Browse Properties</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors) && !$published): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e ?? '') ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>

                    <?php if (!$published): ?>
                    <form method="POST" action="<?= BASE_URL ?>/list-property/step8">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($d['name'] ?? '') ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="tel" name="phone" class="form-control" required pattern="[0-9+\-\s]{7,15}" value="<?= htmlspecialchars($d['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($d['email'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="agree_tc" id="tc" value="1" required>
                            <label class="form-check-label" for="tc">I agree to the <a href="<?= BASE_URL ?>/terms" target="_blank">Terms &amp; Conditions</a> and confirm the information above is accurate. *</label>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/list-property/step7" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-paper-plane me-1"></i> Publish Listing</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
