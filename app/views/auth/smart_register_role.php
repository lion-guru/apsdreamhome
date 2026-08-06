<?php
/**
 * Smart Registration — Role Selection
 * Shown after OTP verification, before profile completion
 * @var array $session
 * @var string $csrf_token
 */
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">
    <title>Choose Your Role - APS Dream Home</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { width: 100%; max-width: 640px; }
        .brand { text-align: center; margin-bottom: 20px; }
        .brand h1 { color: #f59e0b; font-size: 24px; font-weight: 700; }
        .brand p { color: #94a3b8; font-size: 14px; margin-top: 4px; }
        .phone-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 20px; padding: 4px 14px; color: #fbbf24; font-size: 13px; margin-top: 8px; }
        .card { background: #1e293b; border-radius: 16px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .card h2 { color: #e2e8f0; font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .card p.subtitle { color: #64748b; font-size: 14px; margin-bottom: 24px; }
        .role-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .role-card { background: #0f172a; border: 2px solid #1e293b; border-radius: 14px; padding: 20px 12px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .role-card:hover { border-color: #334155; transform: translateY(-2px); }
        .role-card.selected { border-color: #f59e0b; background: rgba(245,158,11,0.08); }
        .role-card .icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 20px; }
        .role-card .icon.customer { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .role-card .icon.associate { background: rgba(16,185,129,0.15); color: #34d399; }
        .role-card .icon.agent { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .role-card h3 { color: #e2e8f0; font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .role-card p { color: #64748b; font-size: 12px; line-height: 1.4; }
        .role-card .badge { display: inline-block; background: rgba(245,158,11,0.15); color: #fbbf24; font-size: 10px; padding: 2px 8px; border-radius: 10px; margin-top: 8px; font-weight: 500; }
        .btn-continue { width: 100%; padding: 14px; background: linear-gradient(135deg, #f59e0b, #d97706); border: none; border-radius: 10px; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s; }
        .btn-continue:hover { transform: translateY(-1px); }
        .btn-continue:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .skip-link { text-align: center; margin-top: 14px; }
        .skip-link a { color: #64748b; font-size: 13px; text-decoration: none; }
        .skip-link a:hover { color: #94a3b8; }
        @media (max-width: 480px) { .card { padding: 20px; } .role-grid { grid-template-columns: 1fr; gap: 8px; } .role-card { padding: 14px; display: flex; align-items: center; text-align: left; gap: 12px; } .role-card .icon { margin: 0; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <h1><i class="fas fa-home"></i> APS Dream Home</h1>
            <p>Almost there! Choose how you'd like to use the platform</p>
            <div class="phone-badge"><i class="fas fa-check-circle"></i> Verified: <?= htmlspecialchars(substr($session['phone'] ?? '', 0, 2)) ?>****<?= htmlspecialchars(substr($session['phone'] ?? '', -2)) ?></div>
        </div>
        <div class="card">
            <h2>Choose Your Role</h2>
            <p class="subtitle">You can change this later from your profile settings.</p>

            <form method="POST" action="<?= $base ?>/auth/smart/role" id="roleForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                <input type="hidden" name="role" id="selectedRole" value="customer">

                <div class="role-grid">
                    <div class="role-card selected" data-role="customer" onclick="selectRole(this, 'customer')">
                        <div class="icon customer"><i class="fas fa-user"></i></div>
                        <h3>Customer</h3>
                        <p>Browse & buy properties, track bookings, manage EMI</p>
                        <span class="badge">Most Popular</span>
                    </div>
                    <div class="role-card" data-role="associate" onclick="selectRole(this, 'associate')">
                        <div class="icon associate"><i class="fas fa-handshake"></i></div>
                        <h3>Associate</h3>
                        <p>Earn commissions, build team, refer properties</p>
                    </div>
                    <div class="role-card" data-role="agent" onclick="selectRole(this, 'agent')">
                        <div class="icon agent"><i class="fas fa-star"></i></div>
                        <h3>Agent</h3>
                        <p>Professional real estate agent with team features</p>
                    </div>
                </div>

                <button type="submit" class="btn-continue" id="continueBtn"><i class="fas fa-arrow-right"></i> Continue as Customer</button>
            </form>

            <div class="skip-link">
                <a href="<?= $base ?>/register/smart/profile-complete?token=<?= htmlspecialchars($_GET['token'] ?? '') ?>&role=customer"><i class="fas fa-forward"></i> Skip, continue as Customer</a>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        function selectRole(el, role) {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('selectedRole').value = role;
            const names = { customer: 'Customer', associate: 'Associate', agent: 'Agent' };
            document.getElementById('continueBtn').innerHTML = '<i class="fas fa-arrow-right"></i> Continue as ' + (names[role] || role);
        }
    </script>
</body>
</html>
