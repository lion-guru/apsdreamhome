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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Register - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1e293b 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(102,126,234,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(118,75,162,.25) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .register-wrapper{position:relative;z-index:1;width:100%;max-width:480px}

        .brand-section{text-align:center;margin-bottom:1.5rem}
        .brand-logo{width:80px;height:80px;background:linear-gradient(135deg,#0d9488,#0f766e);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;box-shadow:0 10px 30px rgba(102,126,234,.4);animation:float 3s ease-in-out infinite}
        .brand-logo i{font-size:2.2rem;color:#fff}
        .brand-section h1{color:#fff;font-size:1.8rem;font-weight:800;margin-bottom:.25rem}
        .brand-section h1 span{background:linear-gradient(135deg,#5eead4,#2dd4bf);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .brand-section p{color:rgba(255,255,255,.55);font-size:.95rem;margin-top:.2rem}
        @keyframes float{0%,100%{transform:translateY(0px)}50%{transform:translateY(-10px)}}

        .register-card{background:rgba(255,255,255,.98);border-radius:24px;padding:2.5rem;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden}
        .register-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#0d9488,#059669,#10b981,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .step-indicator{display:flex;justify-content:center;gap:.5rem;margin-bottom:1.5rem}
        .step-dot{width:12px;height:12px;border-radius:50%;background:#e2e8f0;transition:all .3s}
        .step-dot.active{background:#0d9488;transform:scale(1.2)}
        .step-dot.done{background:#10b981}

        .card-title{font-size:1.3rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;text-align:center}
        .card-subtitle{font-size:.88rem;color:#64748b;text-align:center;margin-bottom:1.5rem}

        .phone-input-group{display:flex;gap:.5rem;margin-bottom:1rem}
        .country-code{width:80px;height:52px;border:2px solid #e2e8f0;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-weight:600;color:#1e293b;font-size:.95rem}
        .phone-input{flex:1;height:52px;border:2px solid #e2e8f0;border-radius:12px;padding:0 1rem;font-size:1rem;transition:all .3s}
        .phone-input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.15);outline:none}

        .email-input{width:100%;height:52px;border:2px solid #e2e8f0;border-radius:12px;padding:0 1rem;font-size:.95rem;transition:all .3s;margin-bottom:1rem}
        .email-input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.15);outline:none}

        .channel-selector{display:flex;gap:.75rem;margin-bottom:1.5rem}
        .channel-option{flex:1;padding:1rem;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:all .3s;text-align:center}
        .channel-option:hover{border-color:#0d9488;background:#f0fdfa}
        .channel-option.selected{border-color:#0d9488;background:#f0fdfa;box-shadow:0 4px 12px rgba(13,148,136,.2)}
        .channel-option i{font-size:1.5rem;color:#0d9488;margin-bottom:.5rem;display:block}
        .channel-option span{font-size:.78rem;font-weight:600;color:#1e293b}
        .channel-option small{font-size:.65rem;color:#64748b;display:block;margin-top:.2rem}

        .btn-send{width:100%;height:52px;border:none;border-radius:14px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s;position:relative;overflow:hidden}
        .btn-send::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s}
        .btn-send:hover::before{left:100%}
        .btn-send:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(13,148,136,.4)}
        .btn-send:active{transform:translateY(0)}
        .btn-send:disabled{opacity:.6;cursor:not-allowed;transform:none}

        .social-divider{display:flex;align-items:center;margin:1.5rem 0}
        .social-divider::before,.social-divider::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,transparent,#e2e8f0,transparent)}
        .social-divider span{padding:0 1rem;font-size:.82rem;color:#94a3b8;font-weight:500}

        .social-buttons{display:flex;gap:.75rem}
        .social-btn{flex:1;height:48px;border:2px solid #e2e8f0;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;gap:.5rem;font-size:.85rem;font-weight:600;color:#1e293b;cursor:pointer;transition:all .2s;text-decoration:none}
        .social-btn:hover{border-color:#0d9488;background:#f8fafc}
        .social-btn.google{color:#ea4335}
        .social-btn.facebook{color:#1877f2}

        .alert{border:none;border-radius:12px;font-size:.875rem;padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;animation:slideInDown .4s ease}
        .alert-danger{background:#fff0f0;color:#d63031}
        .alert-success{background:#f0fdf4;color:#166534}
        @keyframes slideInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}

        .login-link{text-align:center;margin-top:1.25rem;font-size:.88rem;color:#64748b}
        .login-link a{color:#0d9488;text-decoration:none;font-weight:600}
        .login-link a:hover{text-decoration:underline}

        .back-home{display:block;text-align:center;margin-top:.75rem;color:rgba(255,255,255,.35);text-decoration:none;font-size:.82rem;transition:color .2s}
        .back-home:hover{color:rgba(255,255,255,.7)}

        .benefits-bar{display:flex;justify-content:space-around;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border:1px solid #99f6e4;border-radius:12px;padding:.85rem .5rem;margin-bottom:1.5rem}
        .benefit-item{text-align:center}
        .benefit-item i{font-size:1.1rem;color:#0d9488;margin-bottom:.3rem;display:block}
        .benefit-item span{font-size:.65rem;color:#0f766e;font-weight:600;text-transform:uppercase;letter-spacing:.5px}

        @media(max-width:576px){
            body{padding:1rem .5rem;align-items:flex-start;padding-top:1.5rem}
            .register-card{padding:1.75rem 1.25rem}
            .brand-section h1{font-size:1.4rem}
            .channel-selector{flex-direction:column;gap:.5rem}
            .social-buttons{flex-direction:column}
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="brand-section">
            <div class="brand-logo"><i class="fas fa-home"></i></div>
            <h1>Start Your <span>Journey</span></h1>
            <p>Just your phone number — that's all it takes!</p>
        </div>

        <div class="register-card">
            <div class="step-indicator">
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
                <div class="step-dot"></div>
            </div>

            <h2 class="card-title">Quick Registration</h2>
            <p class="card-subtitle">Enter your phone number to get started in seconds</p>

            <div class="benefits-bar">
                <div class="benefit-item">
                    <i class="fas fa-bolt"></i>
                    <span>10 Seconds</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-shield-halved"></i>
                    <span>100% Secure</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-gift"></i>
                    <span>Join 10,000+</span>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error ?? ''); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success ?? ''); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e($base); ?>/register/smart/send-otp" id="phoneForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

                <div class="phone-input-group">
                    <div class="country-code">+91</div>
                    <input type="tel" class="phone-input" name="phone" id="phoneInput" placeholder="Enter 10-digit phone number" pattern="[0-9]{10}" maxlength="10" required autofocus>
                </div>

                <input type="email" class="email-input" name="email" placeholder="Email address (optional, for email OTP)">

                <div class="channel-selector">
                    <div class="channel-option selected" data-channel="whatsapp" onclick="selectChannel('whatsapp')">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                        <small>Instant delivery</small>
                    </div>
                    <div class="channel-option" data-channel="sms" onclick="selectChannel('sms')">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                        <small>Fallback option</small>
                    </div>
                    <div class="channel-option" data-channel="email" onclick="selectChannel('email')">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                        <small>If no WhatsApp</small>
                    </div>
                </div>

                <input type="hidden" name="channel" id="selectedChannel" value="whatsapp">

                
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn-send" id="sendBtn">
                    <i class="fas fa-paper-plane me-2"></i>Send Verification Code
                </button>
            </form>

            <div class="social-divider">
                <span>or continue with</span>
            </div>

            <div class="social-buttons">
                <a href="<?php echo e($base); ?>/auth/google" class="social-btn google">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    Google
                </a>
                <a href="<?php echo e($base); ?>/auth/facebook" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </a>
            </div>
        </div>

        <p class="login-link">Already have an account? <a href="<?php echo e($base); ?>/login">Sign in</a></p>

        <a href="<?php echo e($base); ?>/" class="back-home">
            <i class="fas fa-arrow-left me-1"></i> Back to Home
        </a>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        function selectChannel(channel) {
            document.querySelectorAll('.channel-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.channel-option[data-channel="${channel}"]`).classList.add('selected');
            document.getElementById('selectedChannel').value = channel;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phoneInput');
            const form = document.getElementById('phoneForm');
            const sendBtn = document.getElementById('sendBtn');

            // Only allow digits
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                if (phoneInput.value.length !== 10) {
                    e.preventDefault();
                    phoneInput.style.borderColor = '#dc2626';
                    phoneInput.focus();
                    return;
                }

                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending OTP...';
            });

            // Entrance animation
            const card = document.querySelector('.register-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }

            // Track page view
            trackBehavior('page_view', { page: 'smart_register_phone' });
        });

        function trackBehavior(eventType, eventData) {
            const token = getCookie('smart_reg_token');
            if (!token) return;

            fetch('<?php echo e($base); ?>/api/smart-register/track', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: token,
                    event_type: eventType,
                    event_data: eventData,
                    page_url: window.location.pathname
                })
            }).catch(() => {});
        }

        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
    </script>
</body>
</html>
