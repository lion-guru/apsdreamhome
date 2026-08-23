<?php
// Customer Register - Standalone
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
$csrf_token = $csrf_token ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('register_page_title') ?> | APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d9488, #0f766e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 20px 0
        }

        .card {
            max-width: 500px;
            width: 100%;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2)
        }

        .card-body {
            padding: 2rem
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>

<body>
    <div class="container">
        <div class="card mx-auto">
            <div class="card-body aps-cp-card-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle style-53013"><i class="fas fa-home text-white fa-lg"></i></div>
                    </div>
                    <h3 class="fw-bold"><?= __('register_title') ?></h3>
                    <p class="text-muted"><?= __('register_subtitle') ?></p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e ?? ''); ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e($base); ?>/register" id="customer-register-form" data-experiment="registration_form_length" data-variant="<?= htmlspecialchars($_SESSION['experiments']['registration_form_length'] ?? 'full', ENT_QUOTES) ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                    <?php
                        // A/B test: registration_form_length — 'minimal' shows only 3 fields, 'full' shows all
                        $formVariant = $_SESSION['experiments']['registration_form_length'] ?? 'full';
                    ?>
                    <div class="mb-3">
                        <label class="form-label"><?= __('register_label_name') ?> *</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>" placeholder="<?= __('register_ph_name') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('register_label_email') ?> *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" placeholder="<?= __('register_ph_email') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('register_label_phone') ?> *</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" placeholder="<?= __('register_ph_phone') ?>" required>
                    </div>
                    <div class="reg-step-2" <?= $formVariant === 'minimal' ? 'class="style-24280"' : '' ?>>
                        <div class="mb-3">
                            <label class="form-label"><?= __('register_label_password') ?> *</label>
                            <input type="password" class="form-control" name="password" placeholder="<?= __('register_ph_password') ?>" <?= $formVariant === 'minimal' ? '' : 'required' ?>>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('register_label_confirm_password') ?> *</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="<?= __('register_ph_confirm_password') ?>" <?= $formVariant === 'minimal' ? '' : 'required' ?>>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('register_label_referral') ?></label>
                            <input type="text" class="form-control" name="referral_code" value="<?php echo htmlspecialchars($old['referral_code'] ?? $ref ?? ''); ?>" placeholder="<?= __('register_ph_referral') ?>">
                            <small class="text-success">
                                <i class="fas fa-gift me-1"></i>
                                <?= __('register_referral_bonus', ['percent' => 5]) ?>
                            </small>
                        </div>
                    </div>
                    <?php if ($formVariant === 'minimal'): ?>
                        <button type="button" class="btn btn-primary w-100 py-2 reg-step-1-btn" id="reg-step-1-continue" class="style-32526">
                            <i class="fas fa-arrow-right me-2"></i><?= __('register_continue') ?>
                        </button>
                        
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn btn-primary w-100 py-2 reg-step-2-btn" id="reg-step-2-submit" class="style-95374">
                            <i class="fas fa-user-plus me-2"></i><?= __('register_button_submit') ?>
                        </button>
                        <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
                        (function(){
                            var btn1 = document.getElementById('reg-step-1-continue');
                            var btn2 = document.getElementById('reg-step-2-submit');
                            var step2 = document.querySelectorAll('.reg-step-2');
                            if (btn1 && btn2 && step2.length) {
                                btn1.addEventListener('click', function(){
                                    step2.forEach(function(s){ s.style.display = ''; });
                                    btn1.style.display = 'none';
                                    btn2.style.display = '';
                                    // Track step transition
                                    if (window.ABTracker) window.ABTracker.track('registration_form_length', '<?= htmlspecialchars($formVariant, ENT_QUOTES) ?>', 'step_continue', {step: 1});
                                });
                            }
                        })();
                        </script>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 py-2 style-32526">
                            <i class="fas fa-user-plus me-2"></i><?= __('register_button_submit') ?>
                        </button>
                    <?php endif; ?>
                </form>
                <div class="text-center mt-3">
                    <p class="text-muted"><?= __('register_have_account') ?> <a href="<?php echo e($base); ?>/login"><?= __('register_link_login') ?></a></p>
                    <a href="<?php echo e($base); ?>/" class="text-muted"><i class="fas fa-arrow-left me-1"></i><?= __('register_link_home') ?></a>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>

</html>