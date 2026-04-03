<?php
// Customer Register - Standalone
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$csrf_token = $csrf_token ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body{min-height:100vh;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-family:'Inter',sans-serif;padding:20px 0}
        .card{max-width:500px;width:100%;border:none;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.2)}
        .card-body{padding:2rem}
    </style>
</head>
<body>
<div class="container">
    <div class="card mx-auto">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="mb-3"><div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:60px;height:60px;background:linear-gradient(135deg,#667eea,#764ba2)"><i class="fas fa-home text-white fa-lg"></i></div></div>
                <h3 class="fw-bold">Create Account</h3>
                <p class="text-muted">Join APS Dream Home</p>
            </div>

            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $base; ?>/register">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($old['name']??''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($old['email']??''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone (10 digits) *</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($old['phone']??''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Referral Code (optional)</label>
                    <input type="text" class="form-control" name="referral_code" value="<?php echo htmlspecialchars($old['referral_code']??''); ?>">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2" style="background:linear-gradient(135deg,#667eea,#764ba2);border:none">
                    <i class="fas fa-user-plus me-2"></i>Register
                </button>
            </form>
            <div class="text-center mt-3">
                <p class="text-muted">Already have an account? <a href="<?php echo $base; ?>/login">Login</a></p>
                <a href="<?php echo $base; ?>/" class="text-muted"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
