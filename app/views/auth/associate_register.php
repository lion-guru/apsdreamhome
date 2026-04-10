<?php
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
    <title>Associate Registration - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e65100 0%, #ff9800 35%, #ffb74d 65%, #ff8f00 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            position: relative;
            overflow-y: auto;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 15% 85%, rgba(255, 255, 255, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(255, 255, 255, 0.06) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .register-card {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 0 8px 20px rgba(0, 0, 0, 0.12);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .card-header-area {
            background: linear-gradient(135deg, #bf360c 0%, #e65100 40%, #f57c00 100%);
            padding: 35px 30px 30px;
            text-align: center;
            position: relative;
        }

        .card-header-area::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 20px;
            background: #ffffff;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .brand-icon {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(4px);
        }

        .brand-icon i {
            font-size: 30px;
            color: #ffffff;
        }

        .brand-name {
            color: #ffffff;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.82rem;
            font-weight: 400;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .card-body {
            padding: 30px 35px 25px;
        }

        .tagline-box {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            border-left: 4px solid #e65100;
            border-radius: 0 10px 10px 0;
            padding: 14px 16px;
            margin-bottom: 24px;
        }

        .tagline-box p {
            margin: 0;
            font-size: 0.88rem;
            color: #bf360c;
            font-weight: 500;
            line-height: 1.5;
        }

        .tagline-box i {
            margin-right: 6px;
            color: #e65100;
        }

        .form-label {
            font-weight: 600;
            color: #424242;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .form-label .required {
            color: #e65100;
            margin-left: 2px;
        }

        .input-group-text {
            background: #fff3e0;
            border-color: #ffcc80;
            color: #e65100;
            font-size: 0.9rem;
        }

        .form-control {
            border-color: #ffcc80;
            font-size: 0.92rem;
            padding: 10px 14px;
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .form-control:focus {
            border-color: #e65100;
            box-shadow: 0 0 0 0.2rem rgba(230, 81, 0, 0.15);
        }

        .form-control::placeholder {
            color: #bdbdbd;
            font-size: 0.85rem;
        }

        .optional-label {
            font-size: 0.72rem;
            color: #9e9e9e;
            font-weight: 400;
            font-style: italic;
        }

        .referral-note {
            font-size: 0.78rem;
            color: #757575;
            margin-top: 4px;
            line-height: 1.4;
        }

        .referral-note i {
            color: #ff9800;
            margin-right: 3px;
        }

        .error-box {
            background: #fce4ec;
            border: 1px solid #ef9a9a;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }

        .error-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .error-box li {
            color: #c62828;
            font-size: 0.85rem;
            margin-bottom: 3px;
        }

        .error-box li:last-child {
            margin-bottom: 0;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #bf360c 0%, #e65100 50%, #f57c00 100%);
            border: none;
            border-radius: 10px;
            letter-spacing: 0.8px;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-transform: uppercase;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 81, 0, 0.4);
            color: #ffffff;
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 22px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 14px;
            color: #9e9e9e;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .login-link {
            text-align: center;
            font-size: 0.88rem;
            color: #616161;
        }

        .login-link a {
            color: #e65100;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: #bf360c;
            text-decoration: underline;
        }

        .card-footer-area {
            background: #fafafa;
            border-top: 1px solid #f0f0f0;
            padding: 18px 35px;
            text-align: center;
        }

        .card-footer-area small {
            color: #9e9e9e;
            font-size: 0.75rem;
        }

        .card-footer-area small a {
            color: #757575;
            text-decoration: none;
        }

        .card-footer-area small a:hover {
            color: #e65100;
        }

        @media (max-width: 576px) {
            .card-header-area {
                padding: 28px 20px 25px;
            }

            .brand-icon {
                width: 60px;
                height: 60px;
            }

            .brand-icon i {
                font-size: 24px;
            }

            .brand-name {
                font-size: 1.35rem;
            }

            .card-body {
                padding: 24px 20px 20px;
            }

            .card-footer-area {
                padding: 16px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="register-card">
        <div class="card-header-area">
            <div class="brand-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="brand-name">APS Dream Home</div>
            <div class="brand-subtitle">Associate Partner Portal</div>
        </div>

        <div class="card-body">
            <div class="tagline-box">
                <p><i class="fas fa-chart-line"></i> Join our associate network and earn commissions by referring customers</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo $base; ?>/associate/register" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create password" required>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="sponsor_code" class="form-label">Sponsor / Referral Code <span class="required-label">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                        <input type="text" class="form-control" id="sponsor_code" name="sponsor_code" placeholder="Enter sponsor code" required value="<?php echo htmlspecialchars($old['sponsor_code'] ?? ''); ?>">
                    </div>
                    <div class="referral-note">
                        <i class="fas fa-exclamation-circle"></i> Sponsor code is required to join the network.
                    </div>
                </div>

                <button type="submit" class="btn btn-register mt-1">
                    <i class="fas fa-user-plus me-2"></i>Create Associate Account
                </button>
            </form>

            <div class="divider">
                <span>Already registered?</span>
            </div>

            <p class="login-link">
                <i class="fas fa-sign-in-alt me-1"></i> <a href="<?php echo $base; ?>/associate/login">Sign in to your Associate Account</a>
            </p>
        </div>

        <div class="card-footer-area">
            <small>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.<br>
                <a href="<?php echo $base; ?>">Back to Main Site</a></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            var phone = document.getElementById('phone').value;
            if (phone.length !== 10) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number.');
                document.getElementById('phone').focus();
                return false;
            }

            var pw = document.getElementById('password').value;
            var cpw = document.getElementById('confirm_password').value;
            if (pw !== cpw) {
                e.preventDefault();
                alert('Passwords do not match.');
                document.getElementById('confirm_password').focus();
                return false;
            }
        });
    </script>

</body>

</html>