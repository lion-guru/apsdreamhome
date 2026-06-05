<?php
// Customer Register - Standalone
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
</head>

<body>
    <div class="container">
        <div class="card mx-auto">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:60px;height:60px;background:linear-gradient(135deg,#667eea,#764ba2)"><i class="fas fa-home text-white fa-lg"></i></div>
                    </div>
                    <h3 class="fw-bold"><?= __('register_title') ?></h3>
                    <p class="text-muted"><?= __('register_subtitle') ?></p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $base; ?>/register" id="customer-register-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
                    <div class="mb-3">
                        <label class="form-label"><?= __('register_label_password') ?> *</label>
                        <input type="password" class="form-control" name="password" placeholder="<?= __('register_ph_password') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('register_label_confirm_password') ?> *</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="<?= __('register_ph_confirm_password') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('register_label_referral') ?></label>
                        <input type="text" class="form-control" name="referral_code" value="<?php echo htmlspecialchars($old['referral_code'] ?? ''); ?>" placeholder="<?= __('register_ph_referral') ?>">
                        <small class="text-success">
                            <i class="fas fa-gift me-1"></i>
                            <?= __('register_referral_bonus', ['percent' => 5]) ?>
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2" style="background:linear-gradient(135deg,#667eea,#764ba2);border:none">
                        <i class="fas fa-user-plus me-2"></i><?= __('register_button_submit') ?>
                    </button>
                </form>
                <div class="text-center mt-3">
                    <p class="text-muted"><?= __('register_have_account') ?> <a href="<?php echo $base; ?>/login"><?= __('register_link_login') ?></a></p>
                    <a href="<?php echo $base; ?>/" class="text-muted"><i class="fas fa-arrow-left me-1"></i><?= __('register_link_home') ?></a>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>

</html>