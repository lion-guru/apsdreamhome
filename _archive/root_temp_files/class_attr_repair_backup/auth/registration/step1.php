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
    <title>Step 1: Account Basics | Register | APS Dream Home</title>
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
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-primary mb-2" class="style-45913">
                <i class="fas fa-user-plus fa-lg"></i>
            </div>
            <h3 class="fw-bold mb-0">Create Your Account</h3>
            <small class="opacity-75">Step 1 of 4 &middot; Account Basics</small>
        </div>
        <div class="card-body p-4">
            <?php include __DIR__ . '/progress.php'; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e ?? '') ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register/step1" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" required minlength="2"
                           value="<?= htmlspecialchars($old['name'] ?? ($state['form_data']['name'] ?? '')) ?>" placeholder="e.g. Amit Kumar">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($old['email'] ?? ($state['form_data']['email'] ?? '')) ?>" placeholder="you@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone *</label>
                    <input type="tel" name="phone" class="form-control" required pattern="[0-9+\-\s]{7,15}"
                           value="<?= htmlspecialchars($old['phone'] ?? ($state['form_data']['phone'] ?? '')) ?>" placeholder="+91 9876543210">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min 6 chars">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm *</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= BASE_URL ?>/login" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Already have an account?
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        Next <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
