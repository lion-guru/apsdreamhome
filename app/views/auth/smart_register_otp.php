<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$error = $error ?? $_SESSION['error'] ?? null;
$success = $success ?? $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
$base = BASE_URL;

// Get session data from URL token
$token = $_GET['token'] ?? '';
$phone = $session['phone'] ?? '';
$channel = $session['otp_channel'] ?? 'whatsapp';
$maskedPhone = $phone ? substr($phone, 0, 2) . '****' . substr($phone, -2) : '****';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1e293b 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(102,126,234,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(118,75,162,.25) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .otp-wrapper{position:relative;z-index:1;width:100%;max-width:440px}

        .brand-section{text-align:center;margin-bottom:1.5rem}
        .brand-logo{width:80px;height:80px;background:linear-gradient(135deg,#0d9488,#0f766e);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;box-shadow:0 10px 30px rgba(102,126,234,.4);animation:float 3s ease-in-out infinite}
        .brand-logo i{font-size:2.2rem;color:#fff}
        .brand-section h1{color:#fff;font-size:1.5rem;font-weight:800}
        @keyframes float{0%,100%{transform:translateY(0px)}50%{transform:translateY(-10px)}}

        .otp-card{background:rgba(255,255,255,.98);border-radius:24px;padding:2.5rem;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden}
        .otp-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#0d9488,#059669,#10b981,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .step-indicator{display:flex;justify-content:center;gap:.5rem;margin-bottom:1.5rem}
        .step-dot{width:12px;height:12px;border-radius:50%;background:#e2e8f0;transition:all .3s}
        .step-dot.active{background:#0d9488;transform:scale(1.2)}
        .step-dot.done{background:#10b981}

        .otp-icon{width:80px;height:80px;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;animation:pulse 2s infinite}
        .otp-icon i{font-size:2rem;color:#0d9488}
        @keyframes pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.05);opacity:.8}}

        .card-title{font-size:1.3rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;text-align:center}
        .card-subtitle{font-size:.88rem;color:#64748b;text-align:center;margin-bottom:1.5rem;line-height:1.5}

        .otp-inputs{display:flex;gap:.75rem;justify-content:center;margin-bottom:1.5rem}
        .otp-input{width:52px;height:60px;border:2px solid #e2e8f0;border-radius:12px;text-align:center;font-size:1.5rem;font-weight:700;color:#1e293b;transition:all .3s;background:#f8fafc}
        .otp-input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.15);background:#fff;outline:none}
        .otp-input.filled{border-color:#10b981;background:#f0fdfa}

        .btn-verify{width:100%;height:52px;border:none;border-radius:14px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s;position:relative;overflow:hidden}
        .btn-verify::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s}
        .btn-verify:hover::before{left:100%}
        .btn-verify:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(13,148,136,.4)}
        .btn-verify:disabled{opacity:.6;cursor:not-allowed;transform:none}

        .resend-section{text-align:center;margin-top:1.25rem}
        .resend-text{font-size:.85rem;color:#64748b}
        .resend-btn{background:none;border:none;color:#0d9488;font-weight:600;cursor:pointer;font-size:.85rem;text-decoration:none}
        .resend-btn:hover{text-decoration:underline}
        .resend-btn:disabled{color:#94a3b8;cursor:not-allowed}
        .resend-timer{font-size:.82rem;color:#94a3b8}

        .channel-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .8rem;background:#f0fdfa;border:1px solid #99f6e4;border-radius:20px;font-size:.78rem;color:#0f766e;font-weight:600;margin-bottom:1rem}
        .channel-badge i{font-size:.9rem}

        .alert{border:none;border-radius:12px;font-size:.875rem;padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;animation:slideInDown .4s ease}
        .alert-danger{background:#fff0f0;color:#d63031}
        .alert-success{background:#f0fdf4;color:#166534}
        @keyframes slideInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}

        .help-text{text-align:center;margin-top:1rem;font-size:.82rem;color:#64748b}
        .help-text a{color:#0d9488;text-decoration:none;font-weight:600}

        .back-link{display:block;text-align:center;margin-top:.75rem;color:rgba(255,255,255,.35);text-decoration:none;font-size:.82rem;transition:color .2s}
        .back-link:hover{color:rgba(255,255,255,.7)}

        @media(max-width:576px){
            body{padding:1rem .5rem;align-items:flex-start;padding-top:1.5rem}
            .otp-card{padding:1.75rem 1.25rem}
            .otp-input{width:45px;height:52px;font-size:1.2rem}
        }
    </style>
</head>
<body>
    <div class="otp-wrapper">
        <div class="brand-section">
            <div class="brand-logo"><i class="fas fa-home"></i></div>
            <h1>APS Dream Home</h1>
        </div>

        <div class="otp-card">
            <div class="step-indicator">
                <div class="step-dot done"></div>
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
            </div>

            <div class="otp-icon">
                <i class="fas fa-shield-halved"></i>
            </div>

            <h2 class="card-title">Verify Your Phone</h2>
            <p class="card-subtitle">Enter the 6-digit code sent to your phone</p>

            <div style="text-align:center">
                <?php if ($channel === 'whatsapp'): ?>
                    <span class="channel-badge"><i class="fab fa-whatsapp"></i> Sent via WhatsApp to <?php echo $maskedPhone; ?></span>
                <?php elseif ($channel === 'sms'): ?>
                    <span class="channel-badge"><i class="fas fa-sms"></i> Sent via SMS to <?php echo $maskedPhone; ?></span>
                <?php else: ?>
                    <span class="channel-badge"><i class="fas fa-envelope"></i> Sent via Email</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $base; ?>/register/smart/verify-otp" id="otpForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="otp" id="otpHidden" value="">

                <div class="otp-inputs">
                    <input type="tel" class="otp-input" maxlength="1" data-index="0" autofocus>
                    <input type="tel" class="otp-input" maxlength="1" data-index="1">
                    <input type="tel" class="otp-input" maxlength="1" data-index="2">
                    <input type="tel" class="otp-input" maxlength="1" data-index="3">
                    <input type="tel" class="otp-input" maxlength="1" data-index="4">
                    <input type="tel" class="otp-input" maxlength="1" data-index="5">
                </div>

                <button type="submit" class="btn-verify" id="verifyBtn" disabled>
                    <i class="fas fa-check-circle me-2"></i>Verify & Create Account
                </button>
            </form>

            <div class="resend-section">
                <p class="resend-text" id="resendText">
                    Didn't receive the code?
                    <button class="resend-btn" id="resendBtn" onclick="resendOtp()">
                        Resend OTP
                    </button>
                </p>
                <p class="resend-timer" id="resendTimer" style="display:none">
                    Resend OTP in <span id="countdown">60</span>s
                </p>
            </div>

            <p class="help-text">
                <a href="<?php echo $base; ?>/register/smart">Use a different phone number</a>
            </p>
        </div>

        <a href="<?php echo $base; ?>/" class="back-link">
            <i class="fas fa-arrow-left me-1"></i> Back to Home
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpHidden = document.getElementById('otpHidden');
        const verifyBtn = document.getElementById('verifyBtn');
        const form = document.getElementById('otpForm');
        let resendCountdown = 60;

        // Handle OTP input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Only allow digits
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Move to next input
                if (value && index < 5) {
                    otpInputs[index + 1].focus();
                }

                // Update hidden input
                updateOtpValue();

                // Check if all filled
                if (getOtpValue().length === 6) {
                    verifyBtn.disabled = false;
                    form.submit();
                }
            });

            input.addEventListener('keydown', function(e) {
                // Move to previous input on backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);
                
                for (let i = 0; i < pastedData.length && i < 6; i++) {
                    otpInputs[i].value = pastedData[i];
                    otpInputs[i].classList.add('filled');
                }

                updateOtpValue();

                if (getOtpValue().length === 6) {
                    verifyBtn.disabled = false;
                    form.submit();
                }
            });

            input.addEventListener('focus', function() {
                this.select();
            });
        });

        function getOtpValue() {
            return Array.from(otpInputs).map(i => i.value).join('');
        }

        function updateOtpValue() {
            const otp = getOtpValue();
            otpHidden.value = otp;
            
            otpInputs.forEach((input, i) => {
                if (input.value) {
                    input.classList.add('filled');
                } else {
                    input.classList.remove('filled');
                }
            });
        }

        function resendOtp() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const resendTimer = document.getElementById('resendTimer');
            const countdownEl = document.getElementById('countdown');

            resendBtn.disabled = true;
            resendText.style.display = 'none';
            resendTimer.style.display = 'block';

            // Send AJAX request to resend OTP
            fetch('<?php echo $base; ?>/register/smart/resend-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=<?php echo htmlspecialchars($csrf_token); ?>&token=<?php echo urlencode($token); ?>`
            })
            .then(response => response.text())
            .then(data => {
                // Start countdown
                resendCountdown = 60;
                const timer = setInterval(() => {
                    resendCountdown--;
                    countdownEl.textContent = resendCountdown;

                    if (resendCountdown <= 0) {
                        clearInterval(timer);
                        resendText.style.display = 'block';
                        resendTimer.style.display = 'none';
                        resendBtn.disabled = false;
                    }
                }, 1000);
            })
            .catch(error => {
                console.error('Resend failed:', error);
                resendText.style.display = 'block';
                resendTimer.style.display = 'none';
                resendBtn.disabled = false;
            });
        }

        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            otpInputs[0].focus();

            // Entrance animation
            const card = document.querySelector('.otp-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>
