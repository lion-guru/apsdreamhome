<?php
/**
 * Air Login - OTP-based login without password
 * @var string $csrf_token
 * @var string|null $error
 * @var string|null $success
 */
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <title>Air Login - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e293b 40%,#0d9488 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(13,148,136,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(245,158,11,.2) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .login-wrapper{position:relative;z-index:1;width:100%;max-width:1100px;display:flex;gap:2rem;align-items:center;justify-content:center}

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

        .login-card{background:rgba(255,255,255,.98);border-radius:20px;padding:0;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden;width:100%;max-width:440px;transition:all .4s ease}
        .login-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#0d9488,#14b8a6,#5eead4,#14b8a6,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}

        .card-header-custom{padding:1.75rem 2rem 0;text-align:center}
        .brand-icon{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#0d9488,#0f766e);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 8px 24px rgba(13,148,136,.3)}
        .brand-icon i{font-size:1.8rem;color:#fff}
        .card-header-custom h2{font-size:1.35rem;font-weight:800;color:#1e293b;margin-bottom:.25rem}
        .card-header-custom p{font-size:.85rem;color:#64748b}

        .card-body-custom{padding:1.5rem 2rem 2rem}

        .form-group{margin-bottom:1.1rem}
        .form-group label{display:block;color:#475569;font-size:.78rem;font-weight:600;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px}
        .input-wrap{position:relative}
        .input-wrap i.field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;z-index:2;pointer-events:none;transition:color .3s}
        .input-wrap input{width:100%;padding:13px 16px 13px 42px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;color:#1e293b;font-size:.9rem;font-family:inherit;outline:none;transition:all .3s;height:48px}
        .input-wrap input::placeholder{color:#94a3b8}
        .input-wrap input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.1);background:#fff}
        .input-wrap input:focus ~ i.field-icon{color:#0d9488}

        .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;border-radius:12px;color:#fff;font-size:.95rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(13,148,136,.4)}
        .btn-submit:active{transform:translateY(0)}

        .alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#dc2626;padding:10px 14px;border-radius:10px;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
        .alert-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#16a34a;padding:10px 14px;border-radius:10px;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}

        .divider{text-align:center;margin:1.25rem 0;position:relative}
        .divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:#e2e8f0}
        .divider span{position:relative;background:rgba(255,255,255,.98);padding:0 12px;color:#94a3b8;font-size:.78rem}

        .login-link{text-align:center;color:#64748b;font-size:.85rem}
        .login-link a{color:#0d9488;text-decoration:none;font-weight:600}
        .login-link a:hover{text-decoration:underline}

        @media(max-width:900px){
            .login-wrapper{flex-direction:column}
            .benefits-panel{flex:none;width:100%;max-width:440px}
        }
        @media(max-width:480px){
            .card-body-custom{padding:1.25rem 1.5rem 1.5rem}
            .card-header-custom{padding:1.25rem 1.5rem 0}
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="login-wrapper">
        <!-- Benefits Panel -->
        <div class="benefits-panel d-none d-lg-block">
            <div class="benefits-title"><i class="fas fa-wind style-5793"></i> Air Login</div>
            <div class="benefits-subtitle">Login to your APS Dream Home account without a password — just your email or phone number.</div>

            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-shield-check"></i></div>
                <div class="benefit-text">
                    <h4>Instant OTP</h4>
                    <p>Receive a one-time password on your registered email or phone. No password needed.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-lock"></i></div>
                <div class="benefit-text">
                    <h4>Secure Login</h4>
                    <p>OTP is valid for 10 minutes and can only be used once. Your account stays protected.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
                <div class="benefit-text">
                    <h4>Fast Access</h4>
                    <p>Skip password recall. Just enter your OTP and you're in — under 30 seconds.</p>
                </div>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-value">0-1</span>
                    <span class="stat-label">Steps</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">10m</span>
                    <span class="stat-label">OTP Valid</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">3</span>
                    <span class="stat-label">Retries</span>
                </div>
            </div>
        </div>

        <!-- Air Login Card -->
        <div class="login-card">
            <div class="card-header-custom">
                <div class="brand-icon"><i class="fas fa-plug"></i></div>
                <h2>Air Login</h2>
                <p>Enter your email or phone to receive an OTP</p>
            </div>
            <div class="card-body-custom">
                <?php if ($error): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error ?? '') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success ?? '') ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= $base ?>/auth/air-login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                 <?php $method = $_GET['method'] ?? ''; ?>
                 <div class="form-group">
                         <?php
                             if ($method === 'email'):
                                 $label = 'Email';
                                 $placeholder = 'Enter your email address';
                                 $icon = 'fa-envelope';
                             elseif ($method === 'phone'):
                                 $label = 'Phone Number';
                                 $placeholder = 'Enter your phone number';
                                 $icon = 'fa-mobile-alt';
                             else:
                                 $label = 'Email or Phone';
                                 $placeholder = 'Enter email or phone number';
                                 $icon = 'fa-mobile-alt';
                             endif;
                         ?>
                         <label><?= e($label) ?></label>
                         <div class="input-wrap">
                             <input type="text" name="identity" placeholder="<?= e($placeholder) ?>" required autofocus>
                             <i class="fas fa-<?= e($icon) ?> field-icon"></i>
                         </div>
                     </div>

                    
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Send OTP
                    </button>
                </form>

                <div class="divider"><span>or</span></div>

                <div class="login-link">
                    <a href="<?= $base ?>/auth/login">← Back to Password Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
