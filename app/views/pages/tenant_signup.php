<?php
/**
 * SaaS Tenant Signup Page — Public tenant registration
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Signup — APS Dream Home SaaS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
            --dark: #0f0f23;
            --card-bg: rgba(255,255,255,0.05);
            --glass: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, #1a1a3e 50%, #0d0d2b 100%);
            color: #e0e0e0;
            min-height: 100vh;
            margin: 0;
        }
        .signup-header {
            text-align: center;
            padding: 80px 20px 40px;
        }
        .signup-header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }
        .signup-header p {
            font-size: 1.2rem;
            color: #a0a0c0;
            max-width: 600px;
            margin: 0 auto;
        }
        .signup-form-container {
            max-width: 480px;
            margin: 0 auto 80px;
            padding: 0 20px;
        }
        .signup-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 48px;
            backdrop-filter: blur(20px);
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #e0e0e0;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        .form-group input::placeholder {
            color: #666;
        }
        .btn-submit {
            width: 100%;
            padding: 18px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
        }
        .login-link {
            text-align: center;
            margin-top: 24px;
            color: #a0a0c0;
        }
        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .plan-info {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .plan-info h4 {
            margin: 0 0 12px;
            color: var(--accent);
        }
        .plan-info ul {
            margin: 0;
            padding-left: 20px;
        }
        .plan-info li {
            margin-bottom: 8px;
            color: #c0c0d0;
        }
        @media (max-width: 640px) {
            .signup-header h1 { font-size: 2.2rem; }
            .signup-card { padding: 32px 24px; }
        }
    </style>
</head>
<body>
    <header class="signup-header">
        <h1>Create Your Tenant Account</h1>
        <p>Start your real estate platform with APS Dream Home SaaS</p>
    </header>

    <div class="signup-form-container">
        <div class="signup-card">
            <div class="plan-info">
                <h4><i class="fas fa-info-circle me-2"></i>What you get</h4>
                <ul>
                    <li>Full white-label real estate ERP</li>
                    <li>MLM commission engine with 7 ranks</li>
                    <li>Property & colony management</li>
                    <li>Customer & associate portals</li>
                    <li>Mobile app (Flutter) included</li>
                </ul>
            </div>

            <form method="POST" action="<?= $base ?>/tenant-signup" id="signupForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Your Real Estate Company" required>
                </div>

                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" placeholder="admin@yourcompany.com" required>
                </div>

                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" placeholder="Min 8 characters" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="subdomain">Subdomain</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="subdomain" name="subdomain" placeholder="yourcompany" required style="flex: 1;">
                        <span style="display: flex; align-items: center; color: #a0a0c0; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 0 16px;">.apsdreamhome.com</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="plan_id">Select Plan</label>
                    <select id="plan_id" name="plan_id" required>
                        <option value="">Choose a plan</option>
                        <option value="1">Starter - ₹29,999/mo</option>
                        <option value="2">Professional - ₹59,999/mo</option>
                        <option value="3">Enterprise - ₹1,49,999/mo</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-rocket me-2"></i>Create My Platform
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="<?= $base ?>/login">Login here</a>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
    });

    // Auto-generate subdomain from company name
    document.getElementById('company_name').addEventListener('input', function() {
        const subdomain = this.value.toLowerCase().replace(/[^a-z0-9]/g, '').substring(0, 30);
        document.getElementById('subdomain').value = subdomain;
    });
    </script>
</body>
</html>