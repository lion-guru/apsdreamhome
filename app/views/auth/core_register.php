<?php
/**
 * Core Register — Unified registration with role selection cards
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 * @var string|null $success
 * @var string $ref
 * @var string $selectedRole
 */
$base = BASE_URL;
$roleOptions = [
    'customer' => [
        'label' => 'Customer / Buyer',
        'icon' => 'fas fa-user',
        'desc' => 'Browse properties, book plots, track applications',
        'color' => '#0d9488',
    ],
    'associate' => [
        'label' => 'Associate',
        'icon' => 'fas fa-handshake',
        'desc' => 'MLM network, team building, commissions',
        'color' => '#f59e0b',
    ],
    'agent' => [
        'label' => 'Agent',
        'icon' => 'fas fa-star',
        'desc' => 'Property sales, client management, flat commission',
        'color' => '#2563eb',
    ],
];
$selectedRole = $selectedRole ?? 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">
    <title>Register - APS Dream Home</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
        }
        .register-container { width: 100%; max-width: 560px; }
        .brand { text-align: center; margin-bottom: 24px; }
        .brand h1 { color: #f59e0b; font-size: 28px; font-weight: 700; }
        .brand h1 i { color: #0d9488; margin-right: 8px; }
        .brand p { color: #94a3b8; font-size: 14px; margin-top: 4px; }
        .card { background: #1e293b; border-radius: 16px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        
        /* Role Selection Cards */
        .role-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .role-card { 
            border: 2px solid #334155; border-radius: 12px; padding: 16px 12px; cursor: pointer; 
            background: #0f172a; color: #94a3b8; transition: all 0.3s ease;
            position: relative;
        }
        .role-card:hover { border-color: #475569; color: #e2e8f0; }
        .role-card.selected { 
            border-color: currentColor; background: rgba(currentColor, 0.1); color: currentColor; 
        }
        .role-card .role-icon { font-size: 28px; display: block; margin-bottom: 8px; }
        .role-card .role-label { font-size: 13px; font-weight: 600; display: block; }
        .role-card .role-desc { font-size: 10px; margin-top: 4px; opacity: 0.8; }
        .role-card.customer { color: #0d9488; }
        .role-card.associate { color: #f59e0b; }
        .role-card.agent { color: #2563eb; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .input-wrap { position: relative; }
        .input-wrap i.field-icon { 
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%); 
            color: #64748b; font-size: 16px; z-index: 2; pointer-events: none; 
            transition: color 0.3s; 
        }
        .input-wrap input { 
            width: 100%; padding: 12px 16px 12px 48px; 
            background: #0f172a; border: 1px solid #334155; 
            border-radius: 10px; color: #e2e8f0; font-size: 14px; 
            outline: none; transition: all 0.3s; height: 52px; 
        }
        .input-wrap input::placeholder { color: #475569; }
        .input-wrap input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.15); background: #111827; }
        .input-wrap input:focus ~ i.field-icon { color: #f59e0b; }
        .input-wrap .pwd-toggle { 
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%); 
            background: none; border: none; color: #64748b; cursor: pointer; 
            font-size: 16px; z-index: 3; padding: 4px; 
        }
        .input-wrap .pwd-toggle:hover { color: #e2e8f0; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        /* Password Strength Meter */
        .password-strength { margin-top: 8px; height: 6px; background: #334155; border-radius: 3px; overflow: hidden; }
        .password-strength-bar { height: 100%; width: 0%; transition: all 0.3s; border-radius: 3px; }
        .password-strength-text { font-size: 11px; color: #64748b; margin-top: 4px; }
        
        .btn-submit { 
            width: 100%; padding: 16px; 
            background: linear-gradient(135deg, #f59e0b, #d97706); 
            border: none; border-radius: 12px; 
            color: #fff; font-size: 16px; font-weight: 700; 
            cursor: pointer; transition: all 0.3s; margin-top: 4px; 
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(245,158,11,0.4); }
        .btn-submit:active { transform: translateY(0); }
        
        .error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 8px; }
        .error li { margin-left: 16px; list-style: disc; }
        .success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #86efac; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .ref-info { background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 10px 12px; margin-bottom: 20px; color: #fbbf24; font-size: 13px; display: flex; align-items: center; gap: 8px; }
        .password-hint { color: #64748b; font-size: 12px; margin-top: 4px; }
        
        .login-link { text-align: center; margin-top: 24px; color: #64748b; font-size: 14px; }
        .login-link a { color: #f59e0b; text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }
        
        .terms-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }
        .terms-row input[type="checkbox"] { accent-color: #f59e0b; width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; }
        .terms-row label { color: #94a3b8; font-size: 13px; line-height: 1.5; cursor: pointer; }
        .terms-row a { color: #f59e0b; text-decoration: none; }
        .terms-row a:hover { text-decoration: underline; }
        
        @media (max-width: 480px) { 
            .card { padding: 24px; } 
            .form-row { grid-template-columns: 1fr; }
            .role-selector { gap: 8px; }
            .role-card { padding: 12px 8px; }
            .role-card .role-icon { font-size: 24px; }
            .role-card .role-label { font-size: 12px; }
            .role-card .role-desc { font-size: 9px; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <h1><i class="fas fa-home"></i> APS Dream Home</h1>
            <p>Create your account — choose your role</p>
        </div>
        <div class="card">
            <?php if (!empty($errors)): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i>
                    <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($ref)): ?>
                <div class="ref-info"><i class="fas fa-gift"></i> Referral code applied: <strong><?= htmlspecialchars($ref) ?></strong></div>
            <?php endif; ?>

            <form method="POST" action="<?= $base ?>/auth/register" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="role" id="selectedRole" value="<?= htmlspecialchars($selectedRole) ?>">
                <input type="hidden" name="referral_code" value="<?= htmlspecialchars($ref) ?>">

                <!-- Role Selection Cards -->
                <div class="role-selector">
                    <?php foreach ($roleOptions as $roleKey => $roleData): ?>
                    <div class="role-card <?= $roleKey ?> <?= $selectedRole === $roleKey ? 'selected' : '' ?>" 
                         data-role="<?= $roleKey ?>" onclick="selectRole(this, '<?= $roleKey ?>')">
                        <i class="<?= $roleData['icon'] ?> role-icon"></i>
                        <span class="role-label"><?= $roleData['label'] ?></span>
                        <span class="role-desc"><?= $roleData['desc'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <div class="input-wrap">
                        <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Enter your full name" required autofocus>
                        <i class="fas fa-user field-icon"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <div class="input-wrap">
                            <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="your@email.com" required>
                            <i class="fas fa-envelope field-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <div class="input-wrap">
                            <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" placeholder="10-digit number" pattern="[0-9]{10}" required>
                            <i class="fas fa-phone field-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="password" placeholder="Min 6 characters" minlength="6" required autocomplete="new-password">
                        <i class="fas fa-lock field-icon"></i>
                        <button type="button" class="pwd-toggle" onclick="togglePwd('password')" tabindex="-1">
                            <i class="fas fa-eye" id="pwdIcon"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="pwdStrength"><div class="password-strength-bar" id="pwdStrengthBar"></div></div>
                    <div class="password-strength-text" id="pwdStrengthText">Enter at least 6 characters</div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Re-enter password" required autocomplete="new-password">
                        <i class="fas fa-lock field-icon"></i>
                        <button type="button" class="pwd-toggle" onclick="togglePwd('confirmPassword')" tabindex="-1">
                            <i class="fas fa-eye" id="confirmPwdIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="terms-row">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">I agree to the <a href="<?= BASE_URL ?>/terms">Terms of Service</a> and <a href="<?= BASE_URL ?>/privacy">Privacy Policy</a> *</label>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="<?= $base ?>/auth/login">Login</a>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Role Selection
        function selectRole(el, role) {
            document.querySelectorAll('.role-card').forEach(t => t.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('selectedRole').value = role;
        }

        // Password Toggle
        function togglePwd(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId === 'password' ? 'pwdIcon' : 'confirmPwdIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Password Strength Meter
        const passwordField = document.getElementById('password');
        const strengthBar = document.getElementById('pwdStrengthBar');
        const strengthText = document.getElementById('pwdStrengthText');
        
        passwordField.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;
            let label = '';
            let color = '';
            
            if (val.length >= 6) strength = 1;
            if (val.length >= 8) strength = 2;
            if (/[A-Z]/.test(val)) strength = Math.max(strength, 2);
            if (/[a-z]/.test(val)) strength = Math.max(strength, 2);
            if (/[0-9]/.test(val)) strength = Math.max(strength, 2);
            if (/[^A-Za-z0-9]/.test(val)) strength = Math.max(strength, 3);
            
            const colors = ['', '#ef4444', '#f59e0b', '#22c55e'];
            const labels = ['', 'Weak', 'Fair', 'Strong'];
            
            if (val.length === 0) {
                strength = 0;
                label = 'Enter at least 6 characters';
                color = '#64748b';
            } else {
                color = colors[strength];
                label = labels[strength];
            }
            
            strengthBar.style.width = (strength / 3 * 100) + '%';
            strengthBar.style.background = color;
            strengthText.textContent = label;
            strengthText.style.color = color;
        });

        // Form Validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const pwd = document.getElementById('password').value;
            const confirm = document.getElementById('confirmPassword').value;
            const terms = document.getElementById('terms').checked;
            
            if (pwd !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            if (!terms) {
                e.preventDefault();
                alert('Please accept the Terms of Service and Privacy Policy');
                return false;
            }
            
            // Show loading state
            const btn = this.querySelector('.btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        });
    </script>
</body>
</html>