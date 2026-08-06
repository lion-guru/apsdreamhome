<?php
$page_title = 'Farmer Login - APS Dream Home';
$GLOBALS['_html_doc_started'] = true;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Login - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; }
        .login-header { background: linear-gradient(135deg, #16a34a, #15803d); padding: 2rem; text-align: center; }
        .login-header h3 { color: #fff; font-weight: 700; }
        .login-header p { color: rgba(255,255,255,0.85); font-size: 0.9rem; }
        .login-body { padding: 2.5rem; }
        .form-control { border-radius: 12px; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; }
        .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,0.15); }
        .btn-login { background: linear-gradient(135deg, #16a34a, #15803d); border: none; border-radius: 12px; padding: 0.75rem; font-weight: 600; color: #fff; width: 100%; }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(22,163,74,0.3); }
        .icon-leaf { color: #16a34a; font-size: 3rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="login-header">
                        <i class="fas fa-seedling icon-leaf mb-2" style="color: #fff; font-size: 2.5rem;"></i>
                        <h3>Farmer Portal</h3>
                        <p class="mb-0">APS Dream Home - Land Acquisition</p>
                    </div>
                    <div class="login-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo BASE_URL; ?>/farmer/login">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Registered Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2" style="border-radius:12px 0 0 12px;">
                                        <i class="fas fa-phone text-success"></i>
                                    </span>
                                    <input type="tel" name="phone" class="form-control" placeholder="Enter your 10-digit phone number" pattern="[0-9]{10}" maxlength="10" required style="border-radius:0 12px 12px 0;">
                                </div>
                                <small class="text-muted">Enter the phone number registered with us</small>
                            </div>

                            <button type="submit" class="btn btn-login mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>

                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>" class="text-muted small">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
