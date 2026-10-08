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
        'label' => 'Customer / Buyer & Seller',
        'icon' => 'fas fa-user',
        'desc' => 'Buy plots, sell property, track applications',
        'color' => '#0d9488',
        'badge' => 'Most Popular',
    ],
    'associate' => [
        'label' => 'Associate',
        'icon' => 'fas fa-handshake',
        'desc' => 'MLM network, team building, commissions up to 20%',
        'color' => '#f59e0b',
        'badge' => 'Earn More',
    ],
    'agent' => [
        'label' => 'Agent',
        'icon' => 'fas fa-star',
        'desc' => 'Property sales, client management, flat 5% commission',
        'color' => '#2563eb',
        'badge' => 'Professional',
    ],
];
$selectedRole = $selectedRole ?? 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>">
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
            border-color: currentColor; color: currentColor; 
        }
.role-card.selected.customer { background: rgba(13,148,136,0.15); }
        .role-card.selected.associate { background: rgba(245,158,11,0.15); }
        .role-card.selected.agent { background: rgba(37,99,235,0.15); }
        .role-card .role-icon { font-size: 28px; display: block; margin-bottom: 8px; }
        .role-card .role-label { font-size: 13px; font-weight: 600; display: block; }
        .role-card .role-badge { 
            display: inline-block; 
            font-size: 8px; 
            font-weight: 700; 
            padding: 2px 6px; 
            border-radius: 999px; 
            margin-left: 6px; 
            text-transform: uppercase;
            border: 1px solid currentColor;
            opacity: 0.9;
            vertical-align: middle;
        }
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

        .alt-methods { margin-top: 18px; padding-top: 16px; border-top: 1px solid #334155; text-align: center; }
        .alt-title { color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .alt-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; font-size: 13px; }
        .alt-links a { color: #38bdf8; text-decoration: none; font-weight: 600; }
        .alt-links a:hover { text-decoration: underline; }
        .alt-sub { font-weight: 400; color: #64748b; }
        .alt-sep { color: #475569; }
        .alt-note { margin-top: 8px; color: #64748b; font-size: 12px; }
        .alt-note a { color: #f59e0b; text-decoration: none; font-weight: 600; }
        .alt-note a:hover { text-decoration: underline; }

        /* CAPTCHA styling */
        .captcha-wrap { display: flex; gap: 8px; align-items: stretch; }
        .captcha-wrap input { flex: 1; }
        .captcha-wrap .input-group-text { 
            background: #1e293b; border: 1px solid #334155; border-radius: 10px; 
            padding: 4px 8px; display: flex; align-items: center; 
        }
        .captcha-wrap .input-group-text img { 
            height: 44px; border-radius: 6px; display: block;
            background: #fff;
        }
        
        .terms-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }
        .terms-row input[type="checkbox"] { accent-color: #f59e0b; width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; }
        .terms-row label { color: #94a3b8; font-size: 13px; line-height: 1.5; cursor: pointer; }
        .terms-row a { color: #f59e0b; text-decoration: none; }
        .terms-row a:hover { text-decoration: underline; }

        .benefits-strip{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px}
        .benefit-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#0f172a;border:1px solid #334155;border-radius:999px;font-size:12px;color:#cbd5e1}
        .benefit-chip i{color:#f59e0b;font-size:11px}
        .benefit-chip.highlight{background:rgba(13,148,136,.12);border-color:rgba(13,148,136,.4);color:#5eead4}
        .benefit-chip.highlight i{color:#5eead4}
        .trust-strip{display:flex;justify-content:center;flex-wrap:wrap;gap:16px;margin-top:18px;padding-top:16px;border-top:1px solid #334155;font-size:12px;color:#64748b}
        .trust-strip i{color:#0d9488;margin-right:4px}

        .social-divider{display:flex;align-items:center;margin:0 0 16px}
        .social-divider::before,.social-divider::after{content:'';flex:1;height:1px;background:#334155}
        .social-divider span{padding:0 12px;font-size:11px;color:#64748b;font-weight:600;letter-spacing:1px}
        .social-buttons{display:flex;gap:10px;margin-bottom:20px}
        .social-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 14px;border:1.5px solid #334155;border-radius:10px;background:#0f172a;cursor:pointer;font-size:.82rem;color:#cbd5e1;font-weight:600;font-family:inherit;text-decoration:none;transition:all .2s}
        .social-btn:hover{border-color:#f59e0b;background:#111827;color:#fff}
        .social-btn.google i{color:#ea4335}
        .social-btn.facebook i{color:#1877f2}

@media (max-width: 480px) { 
            .card { padding: 24px; } 
            .form-row { grid-template-columns: 1fr; }
            .role-selector { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .role-card { padding: 12px 8px; }
            .role-card .role-icon { font-size: 24px; }
            .role-card .role-label { font-size: 12px; }
            .role-card .role-desc { font-size: 9px; }
            .role-card .role-badge { font-size: 7px; padding: 1px 4px; }
        }
        @media (max-width: 360px) {
            .role-selector { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
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
                    <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e ?? '') ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success ?? '') ?></div>
            <?php endif; ?>

            <?php if (!empty($ref)): ?>
                <div class="ref-info"><i class="fas fa-gift"></i> Referral code applied: <strong><?= htmlspecialchars($ref ?? '') ?></strong></div>
            <?php endif; ?>

            <form method="POST" action="<?= $base ?>/register" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="role" id="selectedRole" value="<?= htmlspecialchars($selectedRole ?? '') ?>">

<!-- Role Selection Cards -->
<div class="role-selector">
                    <?php foreach ($roleOptions as $roleKey => $roleData): ?>
                    <button type="button" class="role-card <?= e($roleKey) ?> <?= $selectedRole === $roleKey ? 'selected' : '' ?>" 
                         data-role="<?= e($roleKey) ?>" role="button" tabindex="0"
                         onclick="selectRole(this, '<?= e($roleKey) ?>')"
                         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();selectRole(this, '<?= e($roleKey) ?>');}">
                        <i class="<?= e($roleData['icon']) ?> role-icon"></i>
                        <span class="role-label"><?= e($roleData['label']) ?>
                            <?php if (!empty($roleData['badge'])): ?>
                                <span class="role-badge"><?= e($roleData['badge']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="role-desc"><?= e($roleData['desc']) ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>

                <div class="benefits-strip" id="benefitsStrip"></div>

                <div class="social-buttons">
                    <a href="<?= $base ?>/auth/google" class="social-btn google">
                        <i class="fab fa-google"></i> Google
                    </a>
                    <a href="<?= $base ?>/auth/facebook" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                </div>
                <div class="social-divider">
                    <span>OR CONTINUE WITH EMAIL</span>
                </div>

                <div class="form-group">
                    <label for="reg_name"><i class="fas fa-user"></i> Full Name</label>
                    <div class="input-wrap">
                        <input type="text" name="name" id="reg_name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Enter your full name" required autofocus>
                        <i class="fas fa-user field-icon"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg_email"><i class="fas fa-envelope"></i> Email</label>
                        <div class="input-wrap">
                            <input type="email" name="email" id="reg_email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="your@email.com" required>
                            <i class="fas fa-envelope field-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg_phone"><i class="fas fa-phone"></i> Phone</label>
                        <div class="input-wrap">
                            <input type="tel" name="phone" id="reg_phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" placeholder="10-digit number" pattern="[0-9]{10}" required>
                            <i class="fas fa-phone field-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Location <span class="text-muted" style="font-weight:400">(optional — helps us show nearby properties)</span></label>
                    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr">
                        <div class="input-wrap">
                            <input type="text" name="pincode" id="reg_pincode" value="<?= htmlspecialchars($old['pincode'] ?? '') ?>" placeholder="Pincode" maxlength="6" inputmode="numeric">
                            <i class="fas fa-thumbtack field-icon"></i>
                        </div>
                        <div class="input-wrap">
                            <input type="text" name="city" id="reg_city" value="<?= htmlspecialchars($old['city'] ?? '') ?>" placeholder="City">
                            <i class="fas fa-city field-icon"></i>
                        </div>
                        <div class="input-wrap">
                            <input type="text" name="state" id="reg_state" value="<?= htmlspecialchars($old['state'] ?? '') ?>" placeholder="State">
                            <i class="fas fa-flag field-icon"></i>
                        </div>
                    </div>
                    <div class="input-wrap" style="margin-top:12px">
                        <input type="text" name="address" id="reg_address" value="<?= htmlspecialchars($old['address'] ?? '') ?>" placeholder="House/Street, Area">
                        <i class="fas fa-home field-icon"></i>
                    </div>
                    <div class="password-hint" id="pinHint"></div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="password" placeholder="Min 6 characters" minlength="6" required autocomplete="new-password">
                        <i class="fas fa-lock field-icon"></i>
<button type="button" class="pwd-toggle" onclick="togglePwd('password')" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="pwdIcon"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="pwdStrength"><div class="password-strength-bar" id="pwdStrengthBar"></div></div>
                    <div class="password-strength-text" id="pwdStrengthText">Enter at least 6 characters</div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Re-enter password" required autocomplete="new-password">
                        <i class="fas fa-lock field-icon"></i>
<button type="button" class="pwd-toggle" onclick="togglePwd('confirmPassword')" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="confirmPwdIcon"></i>
                        </button>
                    </div>
                </div>

<div class="form-group" id="referralGroup" style="display:block">
                     <label for="referralCodeInput"><i class="fas fa-gift"></i> Referral Code <span id="referralRequired" class="text-muted">(Optional)</span></label>
                     <div class="input-wrap">
                         <input type="text" name="referral_code" id="referralCodeInput" value="<?= htmlspecialchars($ref ?? '') ?>" placeholder="Enter sponsor's referral code">
                         <i class="fas fa-gift field-icon"></i>
                     </div>
                     <div id="referral_name_display" class="mt-2"></div>
                     <small id="referralNote" class="text-muted">Enter referral code for 5% discount on first booking</small>
                 </div>

<div class="form-group" id="agentTypeGroup" style="display:<?= ($selectedRole === 'agent') ? 'block' : 'none' ?>; margin-bottom: 16px;">
                    <label><i class="fas fa-id-badge"></i> Agent Type / Engagement</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 6px;">
                        <label style="display: flex; align-items: center; gap: 8px; background: #0f172a; border: 1px solid #334155; padding: 10px 12px; border-radius: 10px; cursor: pointer; color: #cbd5e1; font-size: 13px;">
                            <input type="radio" name="agent_type" id="agent_type_freelancer" value="freelancer" checked style="accent-color: #2563eb;">
                            <span><strong>Freelancer Agent</strong><br><small style="color: #94a3b8; font-size: 11px;">Independent & flat commission</small></span>
                        </label>
                    </div>
                    <div class="input-wrap" style="margin-top:10px">
                        <select name="experience" id="reg_experience" style="width:100%;padding:12px 16px 12px 48px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:#e2e8f0;font-size:14px;height:52px;appearance:none">
                            <option value="">Experience (optional)</option>
                            <option value="fresher" <?= ($old['experience'] ?? '') === 'fresher' ? 'selected' : '' ?>>Fresher (0 years)</option>
                            <option value="1-2" <?= ($old['experience'] ?? '') === '1-2' ? 'selected' : '' ?>>1-2 years</option>
                            <option value="3-5" <?= ($old['experience'] ?? '') === '3-5' ? 'selected' : '' ?>>3-5 years</option>
                            <option value="5+" <?= ($old['experience'] ?? '') === '5+' ? 'selected' : '' ?>>5+ years</option>
                        </select>
                        <i class="fas fa-clock field-icon"></i>
                    </div>
                    <p style="margin-top: 8px; font-size: 12px; color: #64748b;">
                        <i class="fas fa-info-circle me-1"></i> Employee Agent roles are hired through HR/Admin. Apply via <a href="<?= $base ?>/careers" style="color: #2563eb;">Careers</a> portal.
                    </p>
                </div>

                <div class="terms-row">
                    <input type="checkbox" name="terms" id="terms" required <?= !empty($old['terms']) ? 'checked' : '' ?>>
                    <label for="terms">I agree to the <a href="<?= BASE_URL ?>/terms">Terms of Service</a> and <a href="<?= BASE_URL ?>/privacy">Privacy Policy</a> *</label>
                </div>

<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="trust-strip">
                <span><i class="fas fa-shield-halved"></i>256-bit SSL</span>
                <span><i class="fas fa-lock"></i>Encrypted</span>
                <span><i class="fas fa-check-circle"></i>RERA Approved</span>
                <span><i class="fas fa-users"></i>5,000+ Members</span>
            </div>

<div class="login-link">
                Already have an account? <a href="<?= $base ?>/auth/login">Login</a>
            </div>
            <div class="alt-methods">
                <div class="alt-title">Other ways to join</div>
                <div class="alt-links">
                    <a href="<?= $base ?>/register/step-by-step">Step-by-step form</a>
                    <span class="alt-sep">·</span>
                    <a href="<?= $base ?>/register/smart">OTP <span class="alt-sub">(no password)</span></a>
                </div>
                <div class="alt-note">Employee / Telecaller accounts are created by HR/Admin — apply via <a href="<?= $base ?>/careers">Careers</a>.</div>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Role Selection
        function selectRole(el, role) {
            document.querySelectorAll('.role-card').forEach(t => t.classList.remove('selected'));
            el.classList.add('selected');
document.getElementById('selectedRole').value = role;
            // Show referral field for all public roles (customer=optional, associate/agent=required)
            var refGroup = document.getElementById('referralGroup');
            var refRequired = document.getElementById('referralRequired');
            var refNote = document.getElementById('referralNote');
            if (refGroup) refGroup.style.display = 'block';
            if (role === 'customer') {
                if (refRequired) { refRequired.textContent = '(Optional)'; refRequired.className = 'text-muted'; }
                if (refNote) refNote.textContent = 'Enter referral code for 5% discount on first booking';
            } else if (role === 'associate' || role === 'agent') {
                if (refRequired) { refRequired.textContent = '*'; refRequired.className = 'text-danger'; }
                if (refNote) refNote.textContent = 'Referral code is required to join as Associate/Agent';
            }
            var agentGroup = document.getElementById('agentTypeGroup');
            if (agentGroup) agentGroup.style.display = (role === 'agent') ? 'block' : 'none';
            renderBenefits(role);
        }

        // Per-role benefit chips (mirrors standalone associate/agent selling points)
        var roleBenefits = {
            customer: [
                { icon: 'fas fa-shield-halved', text: 'RERA Approved', highlight: true },
                { icon: 'fas fa-percent', text: 'EMI from ₹8,333/mo' },
                { icon: 'fas fa-gift', text: 'Refer & Earn Points' },
                { icon: 'fas fa-map-marker-alt', text: '200+ Plots Available' }
            ],
            associate: [
                { icon: 'fas fa-money-bill-wave', text: 'Up to 20% Commission', highlight: true },
                { icon: 'fas fa-layer-group', text: '4 Revenue Streams' },
                { icon: 'fas fa-sitemap', text: 'Hybrid Matrix Plan' },
                { icon: 'fas fa-crown', text: 'Royalty Pool Access' }
            ],
            agent: [
                { icon: 'fas fa-coins', text: 'Up to 5% Commission', highlight: true },
                { icon: 'fas fa-chart-line', text: 'Monthly Bonuses' },
                { icon: 'fas fa-users', text: 'Build Your Team' },
                { icon: 'fas fa-graduation-cap', text: 'Free Training' }
            ]
        };
        function renderBenefits(role) {
            var strip = document.getElementById('benefitsStrip');
            if (!strip || !roleBenefits[role]) return;
            strip.innerHTML = roleBenefits[role].map(function(b) {
                return '<span class="benefit-chip' + (b.highlight ? ' highlight' : '') + '"><i class="' + b.icon + '"></i>' + b.text + '</span>';
            }).join('');
        }

        // Sync referral UI with server-preselected role (?role=... deep links)
        document.addEventListener('DOMContentLoaded', function() {
            var hidden = document.getElementById('selectedRole');
            var role = hidden ? hidden.value : 'customer';
            var card = document.querySelector('.role-card[data-role="' + role + '"]');
            if (card) selectRole(card, role);
            else renderBenefits(role);
        });

        // Pincode → city/state autofill (fills only empty fields so user edits win)
        (function() {
            var pinInput = document.getElementById('reg_pincode');
            var cityInput = document.getElementById('reg_city');
            var stateInput = document.getElementById('reg_state');
            var pinHint = document.getElementById('pinHint');
            if (!pinInput) return;
            var pinTimer = null;
            pinInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
                clearTimeout(pinTimer);
                if (pinHint) pinHint.textContent = '';
                if (this.value.length !== 6) return;
                var pin = this.value;
                pinTimer = setTimeout(function() {
                    fetch('<?= $base ?>/api/locations/pincode/' + encodeURIComponent(pin))
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d && d.found) {
                                if (cityInput && !cityInput.value && d.city) cityInput.value = d.city;
                                if (stateInput && !stateInput.value && d.state) stateInput.value = d.state;
                                if (pinHint) { pinHint.textContent = 'Location detected' + (d.area ? ': ' + d.area : ''); pinHint.style.color = '#22c55e'; }
                            } else if (pinHint) {
                                pinHint.textContent = 'Pincode not found — please enter city/state manually';
                                pinHint.style.color = '#f59e0b';
                            }
                        })
                        .catch(function() {});
                }, 500);
            });
        })();

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
         
         // Referral name resolution
         function resolveReferralName() {
             var referralInput = document.getElementById('referralCodeInput');
             var referralCode = referralInput ? referralInput.value.trim() : '';
             var display = document.getElementById('referral_name_display');
             if (!referralCode) {
                 display.innerHTML = '';
                 return;
             }
             display.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resolving...';
fetch('/apsdreamhome/api/user/resolve-sponsor?code=' + encodeURIComponent(referralCode))
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          var name = data.name || 'Unknown';
                          var role = data.role || '';
                          var displayText = '<strong>' + name + '</strong> (' + role + ')';
                          display.innerHTML = displayText;
                      } else {
                          display.innerHTML = '<span class="text-danger">Invalid referral code</span>';
                      }
                  })
                  .catch(() => {
                      display.innerHTML = '<span class="text-danger">Error validating referral</span>';
                  });
         }
         
         // Attach listener to referral_code input (if exists)
         var referralInput = document.getElementById('referralCodeInput');
         if (referralInput) {
             referralInput.addEventListener('input', resolveReferralName);
             referralInput.addEventListener('blur', resolveReferralName);
         }
         
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