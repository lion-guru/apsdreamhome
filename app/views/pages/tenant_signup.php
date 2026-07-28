<?php
/**
 * Tenant Self-Service Signup Page
 * Variables: $plans (array), $selectedPlan (string|null), $error (string|null)
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account — APS Dream Home SaaS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #667eea; --secondary: #764ba2; --dark: #0f0f23; --glass: rgba(255,255,255,0.08); }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, #1a1a3e 50%, #0d0d2b 100%);
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .signup-container {
            width: 100%;
            max-width: 520px;
        }
        .signup-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .signup-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .signup-header p { color: #a0a0c0; font-size: 0.95rem; }
        .signup-card {
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 36px 32px;
            backdrop-filter: blur(10px);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #c0c0d0;
            margin-bottom: 6px;
        }
        .form-group label .required { color: #ff5252; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
        }
        .form-group input::placeholder { color: #555; }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .plan-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .plan-option {
            padding: 12px 8px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .plan-option:hover { border-color: var(--primary); }
        .plan-option.selected { border-color: var(--primary); background: rgba(102,126,234,0.1); }
        .plan-option .name { font-weight: 600; font-size: 0.9rem; color: #fff; }
        .plan-option .price { font-size: 0.8rem; color: #a0a0c0; margin-top: 2px; }
        .btn-signup {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
            margin-top: 24px;
        }
        .btn-signup:hover { opacity: 0.9; }
        .btn-signup:disabled { opacity: 0.5; cursor: not-allowed; }
        .divider {
            text-align: center;
            margin: 20px 0;
            color: #555;
            font-size: 0.85rem;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #a0a0c0;
        }
        .login-link a { color: var(--primary); text-decoration: none; }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-error { background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.3); color: #ff8a80; }
        .alert-success { background: rgba(0,200,83,0.1); border: 1px solid rgba(0,200,83,0.3); color: #69f0ae; }
        .trust-bar {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 24px;
            font-size: 0.8rem;
            color: #666;
        }
        .trust-bar i { margin-right: 4px; }
        .slug-preview {
            font-size: 0.8rem;
            color: #666;
            margin-top: 4px;
        }
        .slug-preview span { color: var(--primary); }
    </style>
</head>
<body>

<div class="signup-container">
    <div class="signup-header">
        <h1>Create Your Account</h1>
        <p>Start your 14-day free trial. No credit card required.</p>
    </div>

    <div class="signup-card">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= $base ?>/tenant-signup">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label>Company Name <span class="required">*</span></label>
                <input type="text" name="name" placeholder="e.g. Sunrise Builders" required
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" id="companyName">
                <div class="slug-preview">Your URL: <span id="slugPreview"><?= $base ?>/t/</span><span id="slugText">your-company</span></div>
            </div>

            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" name="contact_email" placeholder="you@company.com" required
                       value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="contact_name" placeholder="Your name" required
                           value="<?= htmlspecialchars($_POST['contact_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="contact_phone" placeholder="+91 98765 43210"
                           value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" placeholder="Min 8 characters" required minlength="8">
            </div>

            <div class="form-group">
                <label>Choose Your Plan</label>
                <div class="plan-selector">
                    <?php foreach ($plans as $plan):
                        $isSelected = ($selectedPlan === ($plan['slug'] ?? '')) || ($plan['slug'] ?? '') === 'free';
                        $isFree = ($plan['price_monthly'] ?? 0) == 0;
                    ?>
                    <div class="plan-option <?= $isSelected ? 'selected' : '' ?>" onclick="selectPlan(this, '<?= htmlspecialchars($plan['slug']) ?>')">
                        <input type="radio" name="plan_slug" value="<?= htmlspecialchars($plan['slug']) ?>" <?= $isSelected ? 'checked' : '' ?> style="display:none">
                        <div class="name"><?= htmlspecialchars($plan['name']) ?></div>
                        <div class="price"><?= $isFree ? 'Free' : '₹' . number_format($plan['price_monthly'] ?? 0) . '/mo' ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-signup">
                <i class="fas fa-rocket"></i> Create Account
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="<?= $base ?>/admin/login">Log in here</a>
        </div>
    </div>

    <div class="trust-bar">
        <span><i class="fas fa-lock"></i> SSL Encrypted</span>
        <span><i class="fas fa-server"></i> 99.9% Uptime</span>
        <span><i class="fas fa-headset"></i> 24/7 Support</span>
    </div>
</div>

<script>
function selectPlan(el, slug) {
    document.querySelectorAll('.plan-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}
document.getElementById('companyName').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('slugText').textContent = slug || 'your-company';
});
</script>
</body>
</html>
