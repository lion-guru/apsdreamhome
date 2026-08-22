<?php
/**
 * Air Login OTP Verification Page
 * @var string $csrf_token
 * @var string|null $error
 * @var string|null $success
 * @var string $masked
 * @var string $identifier_type
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
    <title>Verify OTP - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e293b 40%,#0d9488 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(13,148,136,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(245,158,11,.2) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .verify-wrapper{position:relative;z-index:1;width:100%;max-width:440px}

        .verify-card{background:rgba(255,255,255,.98);border-radius:20px;padding:0;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden}
        .verify-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#0d9488,#14b8a6,#5eead4,#14b8a6,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .card-header-custom{padding:1.75rem 2rem 0;text-align:center}
        .brand-icon{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#0d9488,#0f766e);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 8px 24px rgba(13,148,136,.3)}
        .brand-icon i{font-size:1.8rem;color:#fff}
        .card-header-custom h2{font-size:1.35rem;font-weight:800;color:#1e293b;margin-bottom:.25rem}
        .card-header-custom p{font-size:.85rem;color:#64748b}

        .card-body-custom{padding:1.5rem 2rem 2rem}

        .alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#dc2626;padding:10px 14px;border-radius:10px;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
        .alert-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#16a34a;padding:10px 14px;border-radius:10px;font-size:.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}

        .otp-info{background:#f0fdfa;border:1px solid #d1fae5;border-radius:12px;padding:14px;margin-bottom:1.25rem;display:flex;align-items:center;gap:10px}
        .otp-info i{color:#0d9488;font-size:1.1rem}
        .otp-info-text{font-size:.85rem;color:#065f46}
        .otp-info-text strong{color:#0f766e}

        .otp-form{display:flex;gap:.5rem;justify-content:center;margin-bottom:1.25rem}
        .otp-input{width:52px;height:52px;border:1.5px solid #e2e8f0;border-radius:10px;text-align:center;font-size:1.4rem;font-weight:700;color:#1e293b;font-family:inherit;transition:all .3s;background:#f8fafc}
        .otp-input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.1);background:#fff;outline:none}
        .otp-input.filled{border-color:#10b981;background:#f0fdfa}

        .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;border-radius:12px;color:#fff;font-size:.95rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(13,148,136,.4)}
        .btn-submit:active{transform:translateY(0)}

        .resend-section{text-align:center;margin-top:1rem}
        .resend-link{color:#0d9488;font-size:.85rem;text-decoration:none;font-weight:600;cursor:pointer}
        .resend-link:hover{text-decoration:underline}
        .resend-disabled{color:#94a3b8;font-size:.82rem;font-weight:600;cursor:not-allowed}

        .divider{text-align:center;margin:1.25rem 0;position:relative}
        .divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:#e2e8f0}
        .divider span{position:relative;background:rgba(255,255,255,.98);padding:0 12px;color:#94a3b8;font-size:.78rem}

        .login-link{text-align:center;color:#64748b;font-size:.85rem}
        .login-link a{color:#0d9488;text-decoration:none;font-weight:600}
        .login-link a:hover{text-decoration:underline}

        .countdown{color:#64748b;font-size:.8rem;text-align:center;margin-bottom:1rem}
    </style>
</head>
<body>
    <div class="verify-wrapper">
        <div class="verify-card">
            <div class="card-header-custom">
                <div class="brand-icon"><i class="fas fa-shield-check"></i></div>
                <h2>Verify OTP</h2>
                <p>Enter the 6-digit code sent to your <?= htmlspecialchars($identifier_type ?? '') === 'email' ? 'email' : 'phone' ?></p>
            </div>
            <div class="card-body-custom">
                <?php if ($error): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error ?? '') ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success ?? '') ?></div>
                <?php endif; ?>

                <div class="otp-info">
                    <i class="fas fa-info-circle"></i>
                    <div class="otp-info-text">OTP sent to <strong><?= htmlspecialchars($masked ?? '') ?></strong></div>
                </div>

                <div class="countdown" id="countdown">Resend code in <span id="timer">5:00</span></div>

                <form id="otpForm" method="POST" action="<?= $base ?>/auth/air-login/verify">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                    <div class="otp-form">
                        <input type="tel" name="otp" id="otp" maxlength="6" pattern="[0-9]{6}" placeholder="•" required autocomplete="one-time-password" autofocus>
                    </div>

                    
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn-submit">
                        <i class="fas fa-sign-in-alt"></i> Verify &amp; Login
                    </button>
                </form>

                <div class="resend-section">
                    <a href="<?= $base ?>/auth/air-login" class="resend-link"><i class="fas fa-arrow-left me-1"></i> Use different email/phone</a>
                </div>

                <div class="divider"><span>or</span></div>

                <div class="login-link">
                    <a href="<?= $base ?>/auth/login">← Back to Password Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format OTP input — allow typing 6 digits into single field
        const otpInput = document.getElementById('otp');

        otpInput.addEventListener('input', function(e) {
            const value = e.target.value.replace(/\D/g, '').slice(0, 6);
            e.target.value = value;
            if (value.length === 6) {
                document.getElementById('otpForm').submit();
            }
        });

        // Countdown timer for resend
        let timeLeft = 300; // 5 minutes
        const timerEl = document.getElementById('timer');
        const countdownEl = document.getElementById('countdown');

        const updateTimer = () => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            if (timerEl) timerEl.textContent = `${minutes}:${seconds.toString().padStart(0, '0')}`;
            timeLeft--;
            if (timeLeft < 0) {
                clearInterval(timerInterval);
                if (countdownEl) countdownEl.innerHTML = '<a href="<?= $base ?>/auth/air-login" class="resend-link">Resend OTP</a>';
            }
        };

        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer();

        // Handle paste of full OTP code
        document.addEventListener('paste', function(e) {
            const pastedData = e.clipboardData.getData('text/plain').replace(/\D/g, '').slice(0, 6);
            if (pastedData.length === 6) {
                e.preventDefault();
                otpInput.value = pastedData;
                document.getElementById('otpForm').submit();
            }
        });
    </script>
</body>
</html>
