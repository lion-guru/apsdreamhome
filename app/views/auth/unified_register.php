<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$errors = $errors ?? $_SESSION['errors'] ?? [];
$old = $old ?? $_SESSION['old_input'] ?? [];
$role = $role ?? 'customer';
$ref = $ref ?? '';
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['old_role']);
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #5eead4;
            --accent: #f59e0b;
            --bg-dark: #0a0f1a;
            --bg-card: #111827;
            --glass: rgba(255,255,255,0.04);
            --glass-border: rgba(255,255,255,0.08);
            --text: #f1f5f9;
            --text-dim: #94a3b8;
            --danger: #ef4444;
            --success: #22c55e;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior: smooth; overflow-x: hidden; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            background: var(--bg-dark);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
            width: 100%;
        }
        body::before {
            content: '';
            position: fixed;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(13,148,136,0.15) 0%, transparent 70%);
            top: -300px;
            right: -200px;
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%);
            bottom: -200px;
            left: -150px;
            border-radius: 50%;
            pointer-events: none;
        }

        /* Grid background */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
        }

        .register-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }

        /* Top Brand */
        .brand-top {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 32px rgba(13,148,136,0.4);
            animation: logoFloat 4s ease-in-out infinite;
            position: relative;
        }
        .brand-logo::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 23px;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            z-index: -1;
            opacity: 0.5;
            filter: blur(8px);
        }
        .brand-logo i { font-size: 2rem; color: #fff; }
        .brand-top h1 {
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .brand-top h1 span {
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-top p {
            color: var(--text-dim);
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        @keyframes logoFloat {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(2deg); }
        }

        /* Main Card */
        .reg-card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            position: relative;
        }
        .reg-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent), var(--primary));
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Role Picker */
        .role-picker {
            display: flex;
            padding: 1.25rem 1.25rem 0;
            gap: 0.75rem;
        }
        .role-option {
            flex: 1;
            background: var(--glass);
            border: 2px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.25rem 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            position: relative;
            overflow: hidden;
        }
        .role-option::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--role-color, transparent), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .role-option:hover { border-color: var(--role-color); transform: translateY(-2px); }
        .role-option:hover::before { opacity: 0.1; }
        .role-option.active {
            border-color: var(--role-color);
            background: rgba(255,255,255,0.06);
            box-shadow: 0 4px 20px var(--role-shadow, rgba(13,148,136,0.2));
        }
        .role-option.active::before { opacity: 0.15; }
        .role-option .role-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--role-color);
            background: rgba(255,255,255,0.06);
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .role-option.active .role-icon {
            background: var(--role-color);
            color: #fff;
            transform: scale(1.1);
            box-shadow: 0 4px 12px var(--role-shadow);
        }
        .role-option .role-name {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text);
        }
        .role-option .role-desc {
            display: block;
            font-size: 0.7rem;
            color: var(--text-dim);
            margin-top: 0.15rem;
        }
        .role-option[data-role="customer"] { --role-color: #0d9488; --role-shadow: rgba(13,148,136,0.3); }
        .role-option[data-role="agent"] { --role-color: #059669; --role-shadow: rgba(5,150,105,0.3); }
        .role-option[data-role="associate"] { --role-color: #ea580c; --role-shadow: rgba(234,88,12,0.3); }

        /* Benefits Strip */
        .benefits-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
        }
        .benefits-strip::-webkit-scrollbar { display: none; }
        .benefit-chip {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.75rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            font-size: 0.72rem;
            color: var(--text-dim);
            white-space: nowrap;
            transition: all 0.3s;
        }
        .benefit-chip i { color: var(--role-color, var(--primary)); font-size: 0.7rem; }
        .benefit-chip.highlight {
            background: rgba(13,148,136,0.1);
            border-color: rgba(13,148,136,0.3);
            color: var(--primary-light);
        }

        /* Form Body */
        .form-body {
            padding: 0 1.5rem 1.5rem;
        }

        /* Section Headers */
        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, var(--glass-border), transparent);
        }

        /* Input Groups */
        .field-group {
            position: relative;
            margin-bottom: 1rem;
        }
        .field-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .field-group label .req { color: var(--danger); }
        .field-group label .opt { font-size: 0.65rem; color: var(--text-dim); font-weight: 400; }
        .input-wrap {
            position: relative;
        }
        .input-wrap i.field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
            font-size: 0.85rem;
            z-index: 2;
            transition: color 0.3s;
            pointer-events: none;
        }
        .input-wrap .form-control,
        .input-wrap .form-select {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.7rem 0.9rem 0.7rem 2.5rem;
            font-size: 0.9rem;
            color: var(--text);
            transition: all 0.3s;
            height: 46px;
            font-family: inherit;
        }
        .input-wrap .form-control::placeholder { color: rgba(148,163,184,0.5); }
        .input-wrap .form-control:focus,
        .input-wrap .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13,148,136,0.15);
            background: rgba(255,255,255,0.06);
        }
        .input-wrap .form-control:focus ~ i.field-icon,
        .input-wrap .form-select:focus ~ i.field-icon { color: var(--primary); }
        .input-wrap .form-select { padding-left: 2.5rem; appearance: none; }
        .input-wrap .form-select option { background: #1e293b; color: var(--text); }

        /* Password toggle */
        .pwd-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            font-size: 0.85rem;
            z-index: 3;
            padding: 4px;
            transition: color 0.2s;
        }
        .pwd-toggle:hover { color: var(--text); }

        /* Password Strength */
        .pwd-strength {
            display: flex;
            gap: 4px;
            margin-top: 0.4rem;
        }
        .pwd-strength .bar {
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: var(--glass-border);
            transition: all 0.3s;
        }
        .pwd-strength.weak .bar:nth-child(1) { background: var(--danger); }
        .pwd-strength.fair .bar:nth-child(1),
        .pwd-strength.fair .bar:nth-child(2) { background: #f59e0b; }
        .pwd-strength.strong .bar:nth-child(1),
        .pwd-strength.strong .bar:nth-child(2),
        .pwd-strength.strong .bar:nth-child(3) { background: #22c55e; }
        .pwd-strength.very-strong .bar { background: var(--primary); }
        .pwd-hint {
            font-size: 0.7rem;
            color: var(--text-dim);
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .pwd-hint i { font-size: 0.6rem; }

        /* Validation states */
        .field-group.valid .form-control { border-color: var(--success); }
        .field-group.invalid .form-control { border-color: var(--danger); }
        .field-msg {
            font-size: 0.7rem;
            margin-top: 0.25rem;
            display: none;
            align-items: center;
            gap: 0.3rem;
        }
        .field-msg.error { color: var(--danger); display: flex; }
        .field-msg.success { color: var(--success); display: flex; }

        /* Role-specific extras */
        .role-extras { display: none; animation: slideDown 0.3s ease; }
        .role-extras.active { display: block; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Sponsor Info Box */
        .sponsor-box {
            background: rgba(234,88,12,0.08);
            border: 1px solid rgba(234,88,12,0.2);
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #fb923c;
        }
        .sponsor-box i { margin-top: 2px; font-size: 0.7rem; }

        /* Associate Motivation */
        .associate-motivation {
            background: linear-gradient(135deg, rgba(234,88,12,0.06), rgba(249,115,22,0.03));
            border: 1px solid rgba(234,88,12,0.15);
            border-radius: 14px;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
        }
        .motivation-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.4rem 0;
            font-size: 0.8rem;
            color: var(--text);
        }
        .motivation-item i {
            color: #f97316;
            font-size: 0.75rem;
            width: 20px;
            text-align: center;
        }

        /* Trust Indicators */
        .trust-bar {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--glass-border);
        }
        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.7rem;
            color: var(--text-dim);
        }
        .trust-item i { color: var(--primary); font-size: 0.65rem; }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(13,148,136,0.3);
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }
        .btn-submit:hover::before { left: 100%; }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13,148,136,0.4);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        .btn-submit[data-role="agent"] {
            background: linear-gradient(135deg, #059669, #10b981);
            box-shadow: 0 4px 15px rgba(5,150,105,0.3);
        }
        .btn-submit[data-role="agent"]:hover { box-shadow: 0 8px 25px rgba(5,150,105,0.4); }
        .btn-submit[data-role="associate"] {
            background: linear-gradient(135deg, #ea580c, #f97316);
            box-shadow: 0 4px 15px rgba(234,88,12,0.3);
        }
        .btn-submit[data-role="associate"]:hover { box-shadow: 0 8px 25px rgba(234,88,12,0.4); }

        /* Terms */
        .terms {
            font-size: 0.72rem;
            color: var(--text-dim);
            text-align: center;
            margin-top: 0.75rem;
            line-height: 1.5;
        }
        .terms a { color: var(--primary); text-decoration: none; }
        .terms a:hover { text-decoration: underline; }

        /* Footer */
        .login-footer {
            text-align: center;
            padding: 1.25rem;
            border-top: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.02);
        }
        .login-footer p {
            font-size: 0.88rem;
            color: var(--text-dim);
        }
        .login-footer a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover { text-decoration: underline; }

        /* Error Box */
        .error-box {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            animation: shake 0.4s ease;
        }
        .error-box .error-title {
            color: var(--danger);
            font-weight: 700;
            font-size: 0.82rem;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .error-box ul { margin: 0; padding-left: 1.1rem; }
        .error-box li { color: #fca5a5; font-size: 0.78rem; margin-bottom: 0.1rem; }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Back link */
        .back-home {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.82rem;
            transition: color 0.2s;
        }
        .back-home:hover { color: var(--primary-light); }

        /* Step indicator */
        .step-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 0.75rem 0;
        }
        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--glass-border);
            transition: all 0.3s;
        }
        .step-dot.active { background: var(--primary); transform: scale(1.3); }
        .step-dot.done { background: var(--success); }

        /* Responsive */
        @media (max-width: 576px) {
            body { padding: 0.5rem; align-items: flex-start; padding-top: 1rem; }
            .brand-top h1 { font-size: 1.4rem; }
            .brand-logo { width: 60px; height: 60px; }
            .brand-logo i { font-size: 1.5rem; }
            .role-picker { gap: 0.5rem; }
            .role-option { padding: 1rem 0.3rem; }
            .role-option .role-name { font-size: 0.78rem; }
            .role-option .role-desc { display: none; }
            .role-option .role-icon { width: 40px; height: 40px; font-size: 1rem; }
            .form-body { padding: 0 1.25rem 1.25rem; }
            .trust-bar { gap: 0.75rem; flex-wrap: wrap; }
        }

        /* Smooth form section transitions */
        .form-section { animation: fadeSlide 0.3s ease; }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>

    <div class="register-wrapper">
        <!-- Brand -->
        <div class="brand-top">
            <div class="brand-logo"><i class="fas fa-building"></i></div>
            <h1>Create <span>Account</span></h1>
            <p>Join 5,000+ property owners in India's trusted network</p>
        </div>

        <!-- Main Card -->
        <div class="reg-card">
            <!-- Role Picker -->
            <div class="role-picker">
                <div class="role-option <?php echo $role === 'customer' ? 'active' : ''; ?>" data-role="customer" onclick="pickRole('customer')">
                    <div class="role-icon"><i class="fas fa-home"></i></div>
                    <span class="role-name">Buyer</span>
                    <span class="role-desc">Find Your Dream Home</span>
                </div>
                <div class="role-option <?php echo $role === 'agent' ? 'active' : ''; ?>" data-role="agent" onclick="pickRole('agent')">
                    <div class="role-icon"><i class="fas fa-handshake"></i></div>
                    <span class="role-name">Agent</span>
                    <span class="role-desc">Earn 5% Commission</span>
                </div>
                <div class="role-option <?php echo $role === 'associate' ? 'active' : ''; ?>" data-role="associate" onclick="pickRole('associate')">
                    <div class="role-icon"><i class="fas fa-network-wired"></i></div>
                    <span class="role-name">Associate</span>
                    <span class="role-desc">Up to 20% Commission</span>
                </div>
            </div>

            <!-- Benefits Strip -->
            <div class="benefits-strip" id="benefitsStrip">
                <!-- Populated by JS based on role -->
            </div>

            <!-- Error Box -->
            <?php if (!empty($errors)): ?>
                <div class="style-54467">
                    <div class="error-box">
                        <div class="error-title"><i class="fas fa-exclamation-circle"></i> Please fix these errors</div>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="form-body">
                <form method="POST" action="<?php echo $base; ?>/register/unified" id="regForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="role" id="selectedRole" value="<?php echo htmlspecialchars($role); ?>">

                    <!-- Step 1: Personal Info -->
                    <div class="form-section" id="step1">
                        <div class="section-label"><i class="fas fa-user"></i> Personal Details</div>

                        <div class="field-group" id="fg-name">
                            <label>Full Name <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="text" class="form-control" name="name" id="regName" placeholder="e.g. Rahul Sharma" value="<?php echo htmlspecialchars($old['name'] ?? $old['full_name'] ?? ''); ?>" required autocomplete="name">
                                <i class="fas fa-user field-icon"></i>
                            </div>
                            <div class="field-msg" id="msg-name"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="field-group" id="fg-email">
                                    <label>Email <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <input type="email" class="form-control" name="email" id="regEmail" placeholder="you@example.com" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required autocomplete="email">
                                        <i class="fas fa-envelope field-icon"></i>
                                    </div>
                                    <div class="field-msg" id="msg-email"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group" id="fg-phone">
                                    <label>Phone <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <input type="tel" class="form-control" name="phone" id="regPhone" placeholder="10-digit mobile" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" required autocomplete="tel">
                                        <i class="fas fa-phone field-icon"></i>
                                    </div>
                                    <div class="field-msg" id="msg-phone"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Security -->
                    <div class="form-section" id="step2">
                        <div class="section-label"><i class="fas fa-shield-halved"></i> Security</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="field-group" id="fg-password">
                                    <label>Password <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <input type="password" class="form-control" name="password" id="regPassword" placeholder="Min 6 characters" required minlength="6" autocomplete="new-password">
                                        <i class="fas fa-lock field-icon"></i>
                                        <button type="button" class="pwd-toggle" onclick="togglePwd('regPassword', this)" tabindex="-1"><i class="fas fa-eye"></i></button>
                                    </div>
                                    <div class="pwd-strength" id="pwdStrength">
                                        <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                                    </div>
                                    <div class="pwd-hint" id="pwdHint"><i class="fas fa-info-circle"></i> <span>Enter a password</span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group" id="fg-confirm">
                                    <label>Confirm Password <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <input type="password" class="form-control" name="confirm_password" id="regConfirm" placeholder="Re-enter password" required autocomplete="new-password">
                                        <i class="fas fa-check-double field-icon"></i>
                                        <button type="button" class="pwd-toggle" onclick="togglePwd('regConfirm', this)" tabindex="-1"><i class="fas fa-eye"></i></button>
                                    </div>
                                    <div class="field-msg" id="msg-confirm"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Role-specific -->
                    <div class="form-section" id="step3">
                        <!-- Customer Extra -->
                        <div class="role-extras <?php echo $role === 'customer' ? 'active' : ''; ?>" data-for="customer">
                            <div class="section-label"><i class="fas fa-gift"></i> Referral</div>
                            <div class="field-group">
                                <label>Referral Code <span class="opt">(optional)</span></label>
                                <div class="input-wrap">
                                    <input type="text" class="form-control" name="referral_code" id="refCode" placeholder="Got a code? Enter it here" value="<?php echo htmlspecialchars($ref); ?>" autocomplete="off">
                                    <i class="fas fa-ticket field-icon"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Agent Extra -->
                        <div class="role-extras <?php echo $role === 'agent' ? 'active' : ''; ?>" data-for="agent">
                            <div class="section-label"><i class="fas fa-briefcase"></i> Professional Info</div>
                            <div class="field-group">
                                <label>Experience</label>
                                <div class="input-wrap">
                                    <select class="form-select" name="experience">
                                        <option value="" selected>Select your experience</option>
                                        <option value="fresher">Fresher (0 years)</option>
                                        <option value="1-2">1-2 years</option>
                                        <option value="3-5">3-5 years</option>
                                        <option value="5+">5+ years</option>
                                    </select>
                                    <i class="fas fa-clock field-icon"></i>
                                </div>
                            </div>
                            <div class="field-group" id="fg-ref-agent">
                                <label>Referral Code <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <input type="text" class="form-control" name="referral_code" placeholder="Enter referrer's code" required value="<?php echo htmlspecialchars($ref); ?>" autocomplete="off">
                                    <i class="fas fa-ticket field-icon"></i>
                                </div>
                                <div class="field-msg error" id="msg-ref-agent"><i class="fas fa-info-circle"></i> Required to join as Agent</div>
                            </div>
                        </div>

                        <!-- Associate Extra -->
                        <div class="role-extras <?php echo $role === 'associate' ? 'active' : ''; ?>" data-for="associate">
                            <div class="associate-motivation">
                                <div class="motivation-item">
                                    <i class="fas fa-rocket"></i>
                                    <span>Start earning from Day 1 with zero investment</span>
                                </div>
                                <div class="motivation-item">
                                    <i class="fas fa-users"></i>
                                    <span>Build your team & earn on their sales too</span>
                                </div>
                                <div class="motivation-item">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Unlimited earning potential as you grow</span>
                                </div>
                            </div>

                            <div class="section-label"><i class="fas fa-sitemap"></i> Sponsor Info</div>
                            <div class="field-group" id="fg-sponsor">
                                <label>Sponsor Code <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <input type="text" class="form-control" name="sponsor_code" id="sponsorCode" placeholder="Enter your sponsor's code" required value="<?php echo htmlspecialchars($ref); ?>" autocomplete="off">
                                    <i class="fas fa-link field-icon"></i>
                                </div>
                                <div class="sponsor-box">
                                    <i class="fas fa-circle-info"></i>
                                    <span>Your sponsor code connects you to the network tree for auto-commission tracking.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="terms">
                        By registering, you agree to our <a href="<?php echo $base; ?>/terms">Terms of Service</a> and <a href="<?php echo $base; ?>/privacy">Privacy Policy</a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-submit" id="submitBtn" data-role="<?php echo htmlspecialchars($role); ?>">
                        <i class="fas fa-arrow-right me-2"></i><span id="btnText">Create Account</span>
                    </button>
                </form>
            </div>

            <!-- Trust Bar -->
            <div class="trust-bar">
                <div class="trust-item"><i class="fas fa-shield-halved"></i> 256-bit SSL</div>
                <div class="trust-item"><i class="fas fa-lock"></i> Encrypted</div>
                <div class="trust-item"><i class="fas fa-check-circle"></i> RERA Approved</div>
                <div class="trust-item"><i class="fas fa-users"></i> 5,000+ Members</div>
            </div>

            <!-- Login Footer -->
            <div class="login-footer">
                <p>Already have an account? <a href="<?php echo $base; ?>/login">Sign in</a></p>
            </div>
        </div>

        <a href="<?php echo $base; ?>/" class="back-home">
            <i class="fas fa-arrow-left me-1"></i> Back to homepage
        </a>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    (function() {
        const BASE = <?php echo json_encode($base); ?>;
        const INIT_ROLE = <?php echo json_encode($role); ?>;
        const INIT_REF = <?php echo json_encode($ref); ?>;

        const benefits = {
            customer: [
                { icon: 'fas fa-shield-halved', text: 'RERA Approved', highlight: true },
                { icon: 'fas fa-percent', text: 'EMI from ₹8,333/mo' },
                { icon: 'fas fa-gift', text: 'Refer & Earn Points' },
                { icon: 'fas fa-map-marker-alt', text: '204+ Plots Available' }
            ],
            agent: [
                { icon: 'fas fa-coins', text: 'Up to 5% Commission', highlight: true },
                { icon: 'fas fa-chart-line', text: 'Monthly Bonuses' },
                { icon: 'fas fa-users', text: 'Build Your Team' },
                { icon: 'fas fa-graduation-cap', text: 'Free Training' }
            ],
            associate: [
                { icon: 'fas fa-money-bill-wave', text: 'Up to 20% Commission', highlight: true },
                { icon: 'fas fa-layer-group', text: '4 Revenue Streams' },
                { icon: 'fas fa-sitemap', text: 'Binary Network Tree' },
                { icon: 'fas fa-crown', text: 'Royalty Pool Access' }
            ]
        };

        const btnLabels = {
            customer: 'Create Account',
            agent: 'Apply as Agent',
            associate: 'Join as Associate'
        };

        // --- Role Picker ---
        window.pickRole = function(role) {
            document.getElementById('selectedRole').value = role;

            document.querySelectorAll('.role-option').forEach(el => el.classList.remove('active'));
            document.querySelector('.role-option[data-role="' + role + '"]').classList.add('active');

            document.querySelectorAll('.role-extras').forEach(el => el.classList.remove('active'));
            const extra = document.querySelector('.role-extras[data-for="' + role + '"]');
            if (extra) extra.classList.add('active');

            const btn = document.getElementById('submitBtn');
            btn.setAttribute('data-role', role);
            document.getElementById('btnText').textContent = btnLabels[role];

            renderBenefits(role);
        };

        function renderBenefits(role) {
            const strip = document.getElementById('benefitsStrip');
            strip.innerHTML = benefits[role].map(b =>
                '<div class="benefit-chip' + (b.highlight ? ' highlight' : '') + '">' +
                '<i class="' + b.icon + '"></i> ' + b.text + '</div>'
            ).join('');
        }

        // --- Password Toggle ---
        window.togglePwd = function(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        };

        // --- Password Strength ---
        function checkPwdStrength(pwd) {
            let score = 0;
            if (pwd.length >= 6) score++;
            if (pwd.length >= 10) score++;
            if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;
            if (score <= 1) return { level: 'weak', text: 'Weak — add more characters', color: '#ef4444' };
            if (score <= 2) return { level: 'fair', text: 'Fair — try adding numbers', color: '#f59e0b' };
            if (score <= 3) return { level: 'strong', text: 'Strong password', color: '#22c55e' };
            return { level: 'very-strong', text: 'Excellent password!', color: '#0d9488' };
        }

        // --- Real-time Validation ---
        function validateField(id, condition, msg) {
            const fg = document.getElementById('fg-' + id);
            const fm = document.getElementById('msg-' + id);
            if (!fg) return;
            fg.classList.remove('valid', 'invalid');
            if (fm) { fm.className = 'field-msg'; fm.innerHTML = ''; }
            if (condition === null) return; // not yet validated
            if (condition) {
                fg.classList.add('valid');
                if (fm) { fm.className = 'field-msg success'; fm.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg; }
            } else {
                fg.classList.add('invalid');
                if (fm) { fm.className = 'field-msg error'; fm.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg; }
            }
        }

        const form = document.getElementById('regForm');
        const nameInput = document.getElementById('regName');
        const emailInput = document.getElementById('regEmail');
        const phoneInput = document.getElementById('regPhone');
        const pwdInput = document.getElementById('regPassword');
        const confirmInput = document.getElementById('regConfirm');

        nameInput.addEventListener('blur', function() {
            validateField('name', this.value.trim().length >= 2, 'Looks good!');
        });
        nameInput.addEventListener('input', function() {
            if (this.value.trim().length >= 2) validateField('name', true, 'Looks good!');
        });

        emailInput.addEventListener('blur', function() {
            const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            validateField('email', valid, valid ? 'Valid email' : 'Enter a valid email');
        });
        emailInput.addEventListener('input', function() {
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) validateField('email', true, 'Valid email');
        });

        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
        });
        phoneInput.addEventListener('blur', function() {
            validateField('phone', /^[6-9]\d{9}$/.test(this.value), this.value.length === 10 ? 'Valid phone' : 'Enter 10-digit mobile');
        });

        pwdInput.addEventListener('input', function() {
            const str = checkPwdStrength(this.value);
            const el = document.getElementById('pwdStrength');
            el.className = 'pwd-strength ' + str.level;
            document.getElementById('pwdHint').innerHTML = '<i class="fas fa-info-circle" class="style-50939"></i> <span>' + str.text + '</span>';

            if (confirmInput.value) {
                validateField('confirm', this.value === confirmInput.value, this.value === confirmInput.value ? 'Passwords match' : 'Passwords do not match');
            }
        });

        confirmInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                validateField('confirm', this.value === pwdInput.value, this.value === pwdInput.value ? 'Passwords match' : 'Passwords do not match');
            }
        });
        confirmInput.addEventListener('blur', function() {
            if (this.value) {
                validateField('confirm', this.value === pwdInput.value, this.value === pwdInput.value ? 'Passwords match' : 'Passwords do not match');
            }
        });

        // --- Submit Loading ---
        form.addEventListener('submit', function(e) {
            // Quick client-side check
            let valid = true;
            if (nameInput.value.trim().length < 2) { validateField('name', false, 'Name is required'); valid = false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) { validateField('email', false, 'Valid email required'); valid = false; }
            if (!/^[6-9]\d{9}$/.test(phoneInput.value)) { validateField('phone', false, 'Valid 10-digit phone required'); valid = false; }
            if (pwdInput.value.length < 6) { valid = false; }
            if (confirmInput.value !== pwdInput.value) { validateField('confirm', false, 'Passwords do not match'); valid = false; }

            if (!valid) {
                e.preventDefault();
                return;
            }

            const btn = document.getElementById('submitBtn');
            const txt = document.getElementById('btnText');
            btn.disabled = true;
            btn.style.opacity = '0.7';
            txt.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
        });

        // --- Sponsor Name Resolution ---
        const sponsorInputs = document.querySelectorAll('input[name="referral_code"], input[name="sponsor_code"]');
        let sponsorTimeout = null;

        sponsorInputs.forEach(input => {
            // Create a badge container after the input if it doesn't exist
            let badge = input.parentElement.parentElement.querySelector('.sponsor-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'sponsor-badge mt-2';
                badge.style.display = 'none';
                badge.style.fontSize = '0.8rem';
                input.parentElement.parentElement.appendChild(badge);
            }

            const resolveSponsor = async (val) => {
                if (!val || val.length < 3) {
                    badge.style.display = 'none';
                    return;
                }
                badge.style.display = 'block';
                badge.innerHTML = '<i class="fas fa-spinner fa-spin text-muted"></i> <span class="text-muted">Resolving sponsor...</span>';
                
                try {
                    const res = await fetch('<?php echo BASE_URL; ?>/api/user/resolve-sponsor?ref=' + encodeURIComponent(val));
                    const data = await res.json();
                    
                    if (data.success) {
                        badge.innerHTML = `<div class="p-2 rounded bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="fas fa-check-circle me-1"></i> <strong>Sponsor:</strong> ${data.name} <span class="badge bg-success ms-1">${data.role}</span></div>`;
                    } else {
                        badge.innerHTML = `<div class="p-2 rounded bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="fas fa-times-circle me-1"></i> Invalid or inactive code</div>`;
                    }
                } catch (e) {
                    badge.innerHTML = `<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error resolving sponsor</div>`;
                }
            };

            input.addEventListener('input', (e) => {
                clearTimeout(sponsorTimeout);
                sponsorTimeout = setTimeout(() => resolveSponsor(e.target.value), 600);
            });
            input.addEventListener('blur', (e) => {
                clearTimeout(sponsorTimeout);
                resolveSponsor(e.target.value);
            });

            // Initial check if value exists
            if (input.value) {
                resolveSponsor(input.value);
            }
        });

        // --- Init ---
        pickRole(INIT_ROLE);
        if (INIT_REF) {
            const refInput = document.getElementById('refCode');
            if (refInput) {
                refInput.value = INIT_REF;
                refInput.dispatchEvent(new Event('blur'));
            }
        }

        // --- Card entrance ---
        const card = document.querySelector('.reg-card');
        if (card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            requestAnimationFrame(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4,0,0.2,1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            });
        }
    })();
    </script>
</body>
</html>
