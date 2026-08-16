<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$error = $error ?? $_SESSION['error'] ?? null;
$success = $success ?? $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('auth_associate_login_title', 'Associate Login'); ?> - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#7c2d12 0%,#c2410c 30%,#ea580c 60%,#f97316 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(234,88,12,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(249,115,22,.25) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .login-wrapper{position:relative;z-index:1;width:100%;max-width:1100px;display:flex;gap:2rem;align-items:center;justify-content:center}

        /* Benefits Panel */
        .benefits-panel{flex:0 0 420px;padding:2rem;background:rgba(255,255,255,.95);border-radius:20px;backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);box-shadow:0 25px 60px rgba(0,0,0,.15);position:relative;overflow:hidden}
        .benefits-panel::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#ea580c,#f97316,#fb923c,#f97316,#ea580c);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .benefits-title{font-size:1.2rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
        .benefits-subtitle{font-size:.85rem;color:#64748b;margin-bottom:1.25rem;line-height:1.5}

        .benefit-item{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;padding:.85rem;background:#f8fafc;border-radius:12px;border:1px solid #fed7aa;transition:all .3s ease}
        .benefit-item:hover{transform:translateX(5px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .benefit-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;background:#fff7ed;color:#ea580c}
        .benefit-text h4{font-size:.85rem;font-weight:700;color:#1e293b;margin-bottom:.15rem}
        .benefit-text p{font-size:.75rem;color:#64748b;line-height:1.4;margin:0}

        .earnings-display{background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:12px;padding:1rem;margin-top:1rem}
        .earnings-display h4{font-size:.85rem;font-weight:700;color:#9a3412;margin-bottom:.5rem}
        .earnings-row{display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;border-bottom:1px solid #fed7aa}
        .earnings-row:last-child{border-bottom:none}
        .earnings-row .label{font-size:.78rem;color:#9a3412}
        .earnings-row .value{font-size:.85rem;font-weight:700;color:#ea580c}

        .stats-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #fed7aa}
        .stat-item{text-align:center}
        .stat-value{font-size:1.4rem;font-weight:800;color:#ea580c;display:block}
        .stat-label{font-size:.65rem;color:#9a3412;text-transform:uppercase;letter-spacing:.5px;font-weight:600}

        /* Login Card */
        .login-card{background:rgba(255,255,255,.98);border-radius:20px;padding:0;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden;width:100%;max-width:440px;transition:all .4s ease}
        .login-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#ea580c,#f97316,#fb923c,#f97316,#ea580c);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        .login-card:hover{transform:translateY(-5px);box-shadow:0 35px 70px rgba(0,0,0,.2)}

        .card-header-custom{padding:2rem 2rem 0;text-align:center}
        .brand-icon{width:80px;height:80px;background:linear-gradient(135deg,#ea580c,#f97316);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 15px 30px rgba(234,88,12,.3);animation:float 3s ease-in-out infinite}
        .brand-icon i{font-size:2.2rem;color:#fff}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

        .brand-title{font-size:1.4rem;font-weight:800;color:#1e293b;margin-bottom:.2rem}
        .brand-subtitle{color:#64748b;font-size:.88rem;margin-bottom:0}

        /* Quick Stats */
        .quick-stats{display:flex;justify-content:space-around;background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:12px;padding:.85rem .5rem;margin:1.25rem 2rem 0}
        .quick-stat{text-align:center}
        .quick-stat-value{display:block;font-size:1.1rem;font-weight:800;color:#ea580c;line-height:1.2}
        .quick-stat-label{font-size:.65rem;color:#9a3412;text-transform:uppercase;letter-spacing:.5px;font-weight:600}

        .card-body-custom{padding:1.25rem 2rem 2rem}

        /* Form Styles */
        .input-icon-wrapper{position:relative;margin-bottom:1rem}
        .input-icon-wrapper .input-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#aaa;z-index:5;font-size:.95rem;pointer-events:none}
        .input-icon-wrapper .form-control{padding-left:2.75rem;height:52px;border:2px solid #e8e8f0;border-radius:12px;font-size:.95rem;transition:border-color .2s,box-shadow .2s}
        .input-icon-wrapper .form-control:focus{border-color:#ea580c;box-shadow:0 0 0 3px rgba(234,88,12,.15);outline:none}
        .input-icon-wrapper .form-control:focus~.input-icon{color:#ea580c}

        .password-wrapper .form-control{padding-right:3rem}
        .toggle-password{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:#aaa;cursor:pointer;z-index:5;padding:4px;transition:color .2s}
        .toggle-password:hover{color:#ea580c}

        .form-options{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;font-size:.85rem}
        .form-check-input:checked{background-color:#ea580c;border-color:#ea580c}
        .forgot-link{color:#ea580c;text-decoration:none;font-weight:500;transition:color .2s}
        .forgot-link:hover{color:#c2410c}

        .btn-login{width:100%;height:52px;border:none;border-radius:12px;background:linear-gradient(135deg,#ea580c,#f97316);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:transform .15s,box-shadow .2s;box-shadow:0 4px 15px rgba(234,88,12,.4);position:relative;overflow:hidden}
        .btn-login::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s ease}
        .btn-login:hover::before{left:100%}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(234,88,12,.5)}
        .btn-login:active{transform:translateY(0)}
        .btn-login.loading{pointer-events:none;opacity:.8}

        .alert-danger{border:none;border-radius:12px;background:#fff0f0;color:#d63031;font-size:.875rem;padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;animation:slideInDown .4s ease}
        .alert-success{border:none;border-radius:12px;background:#f0fdf4;color:#166534;font-size:.875rem;padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;animation:slideInDown .4s ease}
        @keyframes slideInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}

        .social-divider{display:flex;align-items:center;margin:1.5rem 0}
        .social-divider::before,.social-divider::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,transparent,#e0e0e0,transparent)}
        .social-divider span{padding:0 1rem;font-size:.85rem;color:#6c757d;font-weight:500}

        .google-btn{display:flex;align-items:center;justify-content:center;gap:.75rem;width:100%;height:48px;border:2px solid #e5e7eb;border-radius:12px;background:#fff;color:#1f2937;font-size:.95rem;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;margin-bottom:1rem}
        .google-btn:hover{border-color:#d1d5db;background:#f9fafb;color:#1f2937}

        .register-section{text-align:center;padding:1rem 2rem 1.5rem;background:#f8fafc;border-top:1px solid #e2e8f0}
        .register-section p{color:#64748b;font-size:.88rem}
        .register-section a{color:#ea580c;text-decoration:none;font-weight:600}
        .register-section a:hover{text-decoration:underline}

        .back-home{display:block;text-align:center;margin-top:.75rem;color:rgba(255,255,255,.4);text-decoration:none;font-size:.82rem;transition:color .2s}
        .back-home:hover{color:rgba(255,255,255,.7)}

        @media(max-width:992px){
            .login-wrapper{flex-direction:column}
            .benefits-panel{flex:none;max-width:100%}
        }
        @media(max-width:576px){
            body{padding:1rem .5rem;align-items:flex-start;padding-top:1.5rem}
            .card-header-custom{padding:1.5rem 1.25rem 0}
            .card-body-custom{padding:1rem 1.25rem 1.5rem}
            .benefits-panel{padding:1.25rem}
            .brand-title{font-size:1.2rem}
            .brand-icon{width:65px;height:65px}
            .brand-icon i{font-size:1.8rem}
            .quick-stats{margin:1rem 1.25rem 0;padding:.7rem .5rem}
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Benefits Panel -->
        <div class="benefits-panel">
            <div class="benefits-title"><i class="fas fa-gem"></i> Associate Income Potential</div>
            <div class="benefits-subtitle">Join our network of successful associates and start earning passive income</div>

            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="benefit-text">
                    <h4>Up to 20% Commission</h4>
                    <p>Earn based on your rank from 5% to 20% on every sale</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-layer-group"></i></div>
                <div class="benefit-text">
                    <h4>4 Revenue Streams</h4>
                    <p>Plot Sales, Investment Plans, Salary & Incentives, Telecaller Commission</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-sitemap"></i></div>
                <div class="benefit-text">
                    <h4>Binary Network Tree</h4>
                    <p>Build left and right teams with matching bonuses up to 3 generations</p>
                </div>
            </div>

            <div class="earnings-display">
                <h4><i class="fas fa-calculator me-2"></i>Example: ₹1 Lakh Sale</h4>
                <div class="earnings-row">
                    <span class="label">Track A (Direct Sale)</span>
                    <span class="value">₹15,000</span>
                </div>
                <div class="earnings-row">
                    <span class="label">Track B (Performance)</span>
                    <span class="value">₹3,000</span>
                </div>
                <div class="earnings-row">
                    <span class="label">Track C (Milestone)</span>
                    <span class="value">₹2,000</span>
                </div>
                <div class="earnings-row" class="style-24496">
                    <span class="label" class="style-14635">Total Earning</span>
                    <span class="value" class="style-36688">₹20,000</span>
                </div>
            </div>

            <?php
                $s = $stats ?? ['total_paid' => 10560320, 'commission_count' => 311, 'rank_count' => 7, 'max_rate' => 20];
                $formatAmount = function($amt) {
                    if ($amt >= 10000000) return '₹' . round($amt / 10000000, 2) . 'Cr';
                    if ($amt >= 100000) return '₹' . round($amt / 100000, 2) . 'L';
                    return '₹' . number_format($amt);
                };
            ?>
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-value"><?= $formatAmount($s['total_paid']) ?></span>
                    <span class="stat-label">Total Paid</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= number_format($s['commission_count']) ?></span>
                    <span class="stat-label">Commissions</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= (int)$s['max_rate'] ?>%</span>
                    <span class="stat-label">Max Rate</span>
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <div class="card-header-custom">
                <div class="brand-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h1 class="brand-title"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h1>
                <p class="brand-subtitle"><?php echo __('auth_associate_portal_login', 'Associate Portal Login'); ?></p>
            </div>

            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="quick-stat-value"><?= $formatAmount($s['total_paid']) ?></span>
                    <span class="quick-stat-label">Total Paid</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-value"><?= number_format($s['commission_count']) ?></span>
                    <span class="quick-stat-label">Commissions</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-value"><?= (int)$s['rank_count'] ?></span>
                    <span class="quick-stat-label">Rank Levels</span>
                </div>
            </div>

            <div class="card-body-custom">
                <?php if (!empty($error)): ?>
                    <div class="alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo $base; ?>/associate/login" method="POST" id="associateLoginForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="input-icon-wrapper">
                        <input type="text" class="form-control" id="email" name="email" placeholder="<?php echo __('auth_enter_email_phone', 'Email or Phone'); ?>" required autofocus>
                        <i class="fas fa-user input-icon"></i>
                    </div>

                    <div class="input-icon-wrapper password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo __('auth_enter_password', 'Password'); ?>" required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember" class="style-27797"><?php echo __('auth_remember_me', 'Remember me'); ?></label>
                        </div>
                        <a href="<?php echo $base; ?>/associate/forgot-password" class="forgot-link"><?php echo __('auth_forgot_password', 'Forgot Password?'); ?></a>
                    </div>

                    
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn-login" id="submitBtn">
                        <i class="fas fa-sign-in-alt me-2"></i><?php echo __('auth_login', 'Login'); ?>
                    </button>
                </form>

                <div class="social-divider">
                    <span><?php echo __('auth_or_continue_with', 'or continue with'); ?></span>
                </div>

                <a href="<?php echo $base; ?>/auth/google" class="google-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <?php echo __('auth_continue_with_google', 'Continue with Google'); ?>
                </a>
            </div>

            <div class="register-section">
                <p><?php echo __('auth_not_registered_yet', 'Not registered yet?'); ?> <a href="<?php echo $base; ?>/associate/register"><?php echo __('auth_register_as_associate', 'Register as Associate'); ?></a></p>
            </div>
        </div>
    </div>

    <a href="<?php echo $base; ?>/" class="back-home">
        <i class="fas fa-arrow-left me-1"></i> <?php echo __('auth_back_to_home', 'Back to Home'); ?>
    </a>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#associateLoginForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(e) {
                let isValid = true;
                const inputs = form.querySelectorAll('input[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) isValid = false;
                });

                if (!isValid) {
                    e.preventDefault();
                    form.style.animation = 'shake 0.5s';
                    setTimeout(() => { form.style.animation = ''; }, 500);
                    return;
                }

                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Signing in...';
                submitBtn.disabled = true;
            });

            // Entrance animation
            const card = document.querySelector('.login-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }

            const benefits = document.querySelector('.benefits-panel');
            if (benefits) {
                benefits.style.opacity = '0';
                benefits.style.transform = 'translateX(-30px)';
                setTimeout(() => {
                    benefits.style.transition = 'all 0.6s ease';
                    benefits.style.opacity = '1';
                    benefits.style.transform = 'translateX(0)';
                }, 200);
            }
        });
    </script>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>
</html>
