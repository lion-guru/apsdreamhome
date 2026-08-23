<?php
/**
 * Core Login — Unified login for all roles (Premium Glassmorphism)
 * @var string $csrf_token
 * @var string|null $error
 * @var string|null $success
 */
$base = BASE_URL;
$supportedRoles = [
    'Customer / Buyer' => 'Browse properties, book plots, track applications',
    'Associate' => 'MLM network, team building, commissions',
    'Agent' => 'Property sales, client management, flat commission',
    'Employee / Telecaller' => 'Internal dashboard, leads, tasks, attendance',
    'Admin / Manager' => 'Full admin panel, ERP, all modules',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <title>Login - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        *{margin:0;padding:0;box-sizing:border-box}
        html{overflow-x:hidden;max-width:100vw}body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e293b 40%,#0d9488 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow-x:hidden;overflow-y:auto;padding:2rem 1rem;max-width:100vw}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(13,148,136,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(245,158,11,.2) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .login-wrapper{position:relative;z-index:1;width:100%;max-width:1100px;display:flex;gap:2rem;align-items:center;justify-content:center}

        /* Benefits Panel */
        .benefits-panel{flex:0 0 420px;padding:2rem;background:rgba(255,255,255,.95);border-radius:20px;backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);box-shadow:0 25px 60px rgba(0,0,0,.15);position:relative;overflow:hidden}
        .benefits-panel::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0d9488,#14b8a6,#5eead4,#14b8a6,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .benefits-title{font-size:1.2rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
        .benefits-subtitle{font-size:.85rem;color:#64748b;margin-bottom:1.25rem;line-height:1.5}

        .benefit-item{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;padding:.85rem;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;transition:all .3s ease}
        .benefit-item:hover{transform:translateX(5px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .benefit-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;background:#f0fdfa;color:#0d9488}
        .benefit-text h4{font-size:.85rem;font-weight:700;color:#1e293b;margin-bottom:.15rem}
        .benefit-text p{font-size:.75rem;color:#64748b;line-height:1.4;margin:0}

        .stats-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e2e8f0}
        .stat-item{text-align:center}
        .stat-value{font-size:1.4rem;font-weight:800;color:#0d9488;display:block}
        .stat-label{font-size:.65rem;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600}

        /* Login Card */
        .login-card{background:rgba(255,255,255,.98);border-radius:20px;padding:0;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden;width:100%;max-width:440px;transition:all .4s ease}
        .login-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#0d9488,#14b8a6,#5eead4,#14b8a6,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        .login-card:hover{transform:translateY(-5px);box-shadow:0 35px 70px rgba(0,0,0,.2)}

        .card-header-custom{padding:1.75rem 2rem 0;text-align:center}
        .brand-icon{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#0d9488,#0f766e);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 8px 24px rgba(13,148,136,.3)}
        .brand-icon i{font-size:1.8rem;color:#fff}
        .card-header-custom h2,.card-header-custom h1{font-size:1.35rem;font-weight:800;color:#1e293b;margin-bottom:.25rem}
        .card-header-custom p{font-size:.85rem;color:#64748b}

        .card-body-custom{padding:1.5rem 2rem 2rem}

        /* Form */
        .form-group{margin-bottom:1.1rem}
        .form-group label{display:block;color:#475569;font-size:.78rem;font-weight:600;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px}
        .input-wrap{position:relative}
        .input-wrap i.field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;z-index:2;pointer-events:none;transition:color .3s}
        .input-wrap input{width:100%;padding:13px 16px 13px 42px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;color:#1e293b;font-size:.9rem;font-family:inherit;outline:none;transition:all .3s;height:48px}
        .input-wrap input::placeholder{color:#94a3b8}
        .input-wrap input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.1);background:#fff}
        .input-wrap input:focus ~ i.field-icon{color:#0d9488}
        .pwd-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:15px;z-index:3;padding:4px}
        .pwd-toggle:hover{color:#1e293b}

        .form-extras{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}
        .remember-row{display:flex;align-items:center;gap:8px}
        .remember-row input[type="checkbox"]{accent-color:#0d9488;width:16px;height:16px}
        .remember-row label{color:#64748b;font-size:.82rem;cursor:pointer}
        .forgot-link a{color:#0d9488;font-size:.82rem;text-decoration:none;font-weight:600}
        .forgot-link a:hover{text-decoration:underline}

        .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;border-radius:12px;color:#fff;font-size:.95rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(13,148,136,.4)}
        .btn-submit:active{transform:translateY(0)}

        .alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#dc2626;padding:10px 14px;border-radius:10px;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
        .alert-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#16a34a;padding:10px 14px;border-radius:10px;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}

        .divider{text-align:center;margin:1.25rem 0;position:relative}
        .divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:#e2e8f0}
        .divider span{position:relative;background:rgba(255,255,255,.98);padding:0 12px;color:#94a3b8;font-size:.78rem}

        .register-link{text-align:center;color:#64748b;font-size:.85rem}
        .register-link a{color:#0d9488;text-decoration:none;font-weight:600}
        .register-link a:hover{text-decoration:underline}

        .otp-link{text-align:center;margin-top:10px}
        .otp-link a{color:#94a3b8;font-size:.78rem;text-decoration:none}
        .otp-link a:hover{color:#0d9488}

        /* Role quick links */
        .role-quick{margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #e2e8f0}
        .role-quick-title{color:#94a3b8;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-bottom:.75rem;text-align:center}
        .role-links{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
        .role-link{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 6px;text-align:center;text-decoration:none;transition:all .2s;display:block}
        .role-link:hover{border-color:#0d9488;background:#f0fdfa;transform:translateY(-1px)}
        .role-link i{color:#0d9488;font-size:16px;margin-bottom:4px;display:block}
        .role-link strong{color:#1e293b;font-size:.7rem;display:block}
        .role-link span{color:#94a3b8;font-size:.6rem;line-height:1.2}

        /* Social login - wired to Air Login */
        .social-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:1rem}
        .social-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer;font-size:.8rem;color:#475569;font-weight:500;font-family:inherit;text-decoration:none;transition:all .2s}
        .social-btn:hover{border-color:#0d9488;background:#f0fdfa;color:#0d9488}

         @media(max-width:900px){
            .login-wrapper{flex-direction:column}
            .benefits-panel{flex:none;width:100%;max-width:440px}
        }
        @media(max-width:480px){
            .card-body-custom{padding:1.25rem 1.5rem 1.5rem}
            .card-header-custom{padding:1.25rem 1.5rem 0}
            .role-links{grid-template-columns:1fr}
            .social-row{grid-template-columns:1fr}
            .form-group label{font-size:.72rem}
            .input-wrap input{padding:12px 16px 12px 42px;font-size:16px}
            .btn-submit{padding:13px;font-size:.9rem}
        }
        input[type="email"] {font-size:16px !important;}
        input[type="tel"] {font-size:16px !important;}
        input[type="text"] {font-size:16px !important;}
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="login-wrapper">
        <!-- Benefits Panel -->
        <div class="benefits-panel d-none d-lg-block">
            <div class="benefits-title"><i class="fas fa-shield-halved style-5793"></i> Why APS Dream Home?</div>
            <div class="benefits-subtitle">Trusted by 5000+ families across Uttar Pradesh for premium real estate investments.</div>

            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-vector-square"></i></div>
                <div class="benefit-text">
                    <h4>Premium Plots & Properties</h4>
                    <p>Verified plots, flats, villas across Gorakhpur, Lucknow & UP</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="benefit-text">
                    <h4>Flexible EMI Options</h4>
                    <p>Interest-free installment plans up to 36 months</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-shield-halved"></i></div>
                <div class="benefit-text">
                    <h4>100% Legal Verified</h4>
                    <p>RERA compliant with complete documentation</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-headset"></i></div>
                <div class="benefit-text">
                    <h4>24/7 Support</h4>
                    <p>Dedicated relationship manager for every customer</p>
                </div>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-value">5000+</span>
                    <span class="stat-label">Plots Sold</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">4</span>
                    <span class="stat-label">Colonies</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">12+</span>
                    <span class="stat-label">Years</span>
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <div class="card-header-custom">
                <div class="brand-icon"><i class="fas fa-home"></i></div>
                <h1>Welcome Back</h1>
                <p>Sign in to your APS Dream Home account</p>
            </div>
            <div class="card-body-custom">
                <?php if ($error): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error ?? '') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success ?? '') ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= $base ?>/auth/login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                    <div class="form-group">
                        <label>Email or Phone</label>
                        <div class="input-wrap">
                            <input type="text" name="identity" placeholder="Enter email or phone number" required autofocus>
                            <i class="fas fa-user field-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrap">
                            <input type="password" name="password" placeholder="Enter your password" required>
                            <i class="fas fa-lock field-icon"></i>
                            <button type="button" class="pwd-toggle" onclick="togglePwd()" tabindex="-1">
                                <i class="fas fa-eye" id="pwdIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-extras">
                        <div class="remember-row">
                            <input type="checkbox" name="remember" id="remember" value="1">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="forgot-link">
                            <a href="<?= $base ?>/forgot-password">Forgot?</a>
                        </div>
                    </div>

                    
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn-submit">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="divider"><span>or</span></div>

                <div class="social-row">
                    <a href="<?= $base ?>/auth/air-login?method=email" class="social-btn">
                        <i class="fab fa-google style-85143"></i> Google
                    </a>
                    <a href="<?= $base ?>/auth/air-login?method=phone" class="social-btn">
                        <i class="fas fa-phone style-85531"></i> Phone
                    </a>
                </div>

                <div class="register-link style-47816">
                    Don't have an account? <a href="<?= $base ?>/auth/register">Register</a>
                </div>
                <div class="otp-link">
                    <a href="<?= $base ?>/register/smart"><i class="fas fa-mobile-alt me-1"></i>Register with Phone (OTP)</a>
                </div>
                <div class="otp-link">
                    <a href="<?= $base ?>/auth/air-login"><i class="fas fa-plug me-1"></i>Air Login (OTP without password)</a>
                </div>

                <!-- Role Quick Links -->
                <div class="role-quick">
                    <div class="role-quick-title">Or login as</div>
                    <div class="role-links">
                        <a href="<?= $base ?>/associate/login" class="role-link">
                            <i class="fas fa-handshake"></i>
                            <strong>Associate</strong>
                            <span>MLM & Commissions</span>
                        </a>
                        <a href="<?= $base ?>/agent/login" class="role-link">
                            <i class="fas fa-briefcase"></i>
                            <strong>Agent</strong>
                            <span>Sales & Clients</span>
                        </a>
                        <a href="<?= $base ?>/admin/login" class="role-link">
                            <i class="fas fa-user-shield"></i>
                            <strong>Admin</strong>
                            <span>Full Panel</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    function togglePwd() {
        const field = document.querySelector('input[name="password"]');
        const icon = document.getElementById('pwdIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    </script>
</body>
</html>
