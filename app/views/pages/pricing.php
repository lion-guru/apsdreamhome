<?php
/**
 * SaaS Pricing Page — Public plan comparison and signup
 * Variable: $plans (array of subscription_plans rows)
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans — APS Dream Home SaaS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .pricing-header {
            text-align: center;
            padding: 80px 20px 40px;
        }
        .pricing-header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }
        .pricing-header p {
            font-size: 1.2rem;
            color: #a0a0c0;
            max-width: 600px;
            margin: 0 auto;
        }
        .billing-toggle {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin: 40px 0;
        }
        .billing-toggle .label {
            font-size: 1rem;
            color: #a0a0c0;
        }
        .billing-toggle .label.active { color: #fff; font-weight: 600; }
        .toggle-switch {
            width: 56px;
            height: 28px;
            background: var(--glass);
            border-radius: 14px;
            cursor: pointer;
            position: relative;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .toggle-switch .knob {
            width: 22px;
            height: 22px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.3s;
        }
        .toggle-switch.yearly .knob { transform: translateX(28px); }
        .save-badge {
            background: linear-gradient(135deg, #00c853, #00e676);
            color: #000;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }
        .plan-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 36px 28px;
            position: relative;
            backdrop-filter: blur(10px);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .plan-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px rgba(102,126,234,0.15);
        }
        .plan-card.popular {
            border-color: var(--primary);
            box-shadow: 0 0 40px rgba(102,126,234,0.2);
        }
        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 20px;
            border-radius: 20px;
        }
        .plan-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        .plan-desc {
            font-size: 0.9rem;
            color: #a0a0c0;
            margin-bottom: 20px;
        }
        .plan-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }
        .plan-price .currency { font-size: 1.2rem; vertical-align: top; margin-right: 2px; }
        .plan-price .period { font-size: 0.9rem; color: #a0a0c0; font-weight: 400; }
        .plan-annual {
            font-size: 0.85rem;
            color: #a0a0c0;
            margin-bottom: 24px;
        }
        .plan-limits {
            list-style: none;
            padding: 0;
            margin: 0 0 24px;
        }
        .plan-limits li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }
        .plan-limits li .label { color: #a0a0c0; }
        .plan-limits li .value { color: #fff; font-weight: 600; }
        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 28px;
        }
        .plan-features li {
            padding: 6px 0;
            font-size: 0.85rem;
            color: #c0c0d0;
        }
        .plan-features li i {
            margin-right: 8px;
            font-size: 0.8rem;
        }
        .plan-features li i.fa-check { color: #00c853; }
        .plan-features li i.fa-xmark { color: #ff5252; }
        .btn-plan {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        .btn-plan.primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
        }
        .btn-plan.primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-plan.outline {
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-plan.outline:hover { background: var(--glass); }
        .comparison-section {
            max-width: 1000px;
            margin: 0 auto 80px;
            padding: 0 20px;
        }
        .comparison-section h2 {
            text-align: center;
            font-size: 2rem;
            color: #fff;
            margin-bottom: 40px;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
        }
        .comparison-table th, .comparison-table td {
            padding: 14px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.9rem;
        }
        .comparison-table th {
            background: rgba(102,126,234,0.1);
            color: #fff;
            font-weight: 600;
        }
        .comparison-table td:first-child { text-align: left; color: #a0a0c0; }
        .comparison-table tr:hover td { background: rgba(255,255,255,0.02); }
        .cta-section {
            text-align: center;
            padding: 60px 20px 80px;
        }
        .cta-section h2 {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 16px;
        }
        .cta-section p { color: #a0a0c0; margin-bottom: 24px; }
        .footer-note {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 0.85rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        @media (max-width: 768px) {
            .pricing-header h1 { font-size: 2rem; }
            .plans-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="pricing-header">
    <h1>Simple, Transparent Pricing</h1>
    <p>Choose the plan that fits your real estate business. Start free, upgrade when you're ready.</p>
</div>

<div class="billing-toggle">
    <span class="label monthly active" onclick="setBilling(false)">Monthly</span>
    <div class="toggle-switch" id="billingToggle" onclick="toggleBilling()">
        <div class="knob"></div>
    </div>
    <span class="label yearly" onclick="setBilling(true)">Yearly</span>
    <span class="save-badge">Save 20%</span>
</div>

<div class="plans-grid" id="plansGrid">
<?php
$planIndex = 0;
foreach ($plans as $plan):
    $isPopular = ($plan['slug'] ?? '') === 'pro';
    $isFree = ($plan['price_monthly'] ?? 0) == 0;
    $features = json_decode($plan['features_json'] ?? '{}', true) ?: [];
?>
    <div class="plan-card <?= $isPopular ? 'popular' : '' ?>">
        <?php if ($isPopular): ?>
            <div class="popular-badge">MOST POPULAR</div>
        <?php endif; ?>

        <div class="plan-name"><?= htmlspecialchars($plan['name']) ?></div>
        <div class="plan-desc"><?= htmlspecialchars($plan['description'] ?? '') ?></div>

        <div class="plan-price" data-monthly="<?= (int)($plan['price_monthly'] ?? 0) ?>" data-yearly="<?= (int)($plan['price_yearly'] ?? 0) ?>">
            <?php if ($isFree): ?>
                Free
            <?php else: ?>
                <span class="currency">₹</span><span class="amount"><?= number_format($plan['price_monthly'] ?? 0) ?></span>
                <span class="period">/mo</span>
            <?php endif; ?>
        </div>

        <?php if (!$isFree && ($plan['price_yearly'] ?? 0) > 0): ?>
            <div class="plan-annual" data-yearly-price="<?= number_format($plan['price_yearly'] ?? 0) ?>">
                ₹<?= number_format($plan['price_yearly'] ?? 0) ?>/year (save <?= round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) ?>%)
            </div>
        <?php endif; ?>

        <ul class="plan-limits">
            <li><span class="label">Users</span><span class="value"><?= $plan['max_users'] ?? 1 ?></span></li>
            <li><span class="label">Leads</span><span class="value"><?= number_format($plan['max_leads'] ?? 50) ?></span></li>
            <li><span class="label">Properties</span><span class="value"><?= $plan['max_properties'] ?? 10 ?></span></li>
            <li><span class="label">Storage</span><span class="value"><?= $plan['storage_limit_mb'] ?? 100 ?> MB</span></li>
        </ul>

        <ul class="plan-features">
            <li><i class="fas <?= ($plan['api_access'] ?? 0) ? 'fa-check' : 'fa-xmark' ?>"></i> API Access</li>
            <li><i class="fas <?= ($plan['white_label'] ?? 0) ? 'fa-check' : 'fa-xmark' ?>"></i> White Label</li>
            <li><i class="fas <?= ($plan['mlm_engine'] ?? 0) ? 'fa-check' : 'fa-xmark' ?>"></i> MLM Engine</li>
            <li><i class="fas <?= ($plan['ai_features'] ?? 0) ? 'fa-check' : 'fa-xmark' ?>"></i> AI Features</li>
            <li><i class="fas <?= ($plan['mobile_app'] ?? 0) ? 'fa-check' : 'fa-xmark' ?>"></i> Mobile App</li>
            <li><i class="fas <?= ($plan['priority_support'] ?? 0) ? 'fa-check' : 'fa-xmark' ?>"></i> Priority Support</li>
        </ul>

        <?php if ($isFree): ?>
            <a href="<?= $base ?>/tenant-signup?plan=<?= urlencode($plan['slug']) ?>" class="btn-plan outline">Get Started Free</a>
        <?php else: ?>
            <a href="<?= $base ?>/tenant-signup?plan=<?= urlencode($plan['slug']) ?>" class="btn-plan primary">Start 14-Day Trial</a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<div class="comparison-section">
    <h2>Full Feature Comparison</h2>
    <table class="comparison-table">
        <thead>
            <tr>
                <th style="text-align:left">Feature</th>
                <?php foreach ($plans as $p): ?>
                    <th><?= htmlspecialchars($p['name']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Lead Management</td>
                <?php foreach ($plans as $p): ?>
                    <td><?= number_format($p['max_leads'] ?? 0) ?> leads</td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>Properties</td>
                <?php foreach ($plans as $p): ?>
                    <td><?= $p['max_properties'] ?? 0 ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>Team Members</td>
                <?php foreach ($plans as $p): ?>
                    <td><?= $p['max_users'] ?? 1 ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>Storage</td>
                <?php foreach ($plans as $p): ?>
                    <td><?= $p['storage_limit_mb'] ?? 100 ?> MB</td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>CRM Pipeline</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-check" style="color:#00c853"></i></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>Kanban Board</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-<?= ($p['slug'] ?? '') === 'free' ? 'xmark' : 'check' ?>" style="color:<?= ($p['slug'] ?? '') === 'free' ? '#ff5252' : '#00c853' ?>"></i></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>API Access</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-<?= ($p['api_access'] ?? 0) ? 'check' : 'xmark' ?>" style="color:<?= ($p['api_access'] ?? 0) ? '#00c853' : '#ff5252' ?>"></i></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>White Label</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-<?= ($p['white_label'] ?? 0) ? 'check' : 'xmark' ?>" style="color:<?= ($p['white_label'] ?? 0) ? '#00c853' : '#ff5252' ?>"></i></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>MLM Engine</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-<?= ($p['mlm_engine'] ?? 0) ? 'check' : 'xmark' ?>" style="color:<?= ($p['mlm_engine'] ?? 0) ? '#00c853' : '#ff5252' ?>"></i></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>AI Features</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-<?= ($p['ai_features'] ?? 0) ? 'check' : 'xmark' ?>" style="color:<?= ($p['ai_features'] ?? 0) ? '#00c853' : '#ff5252' ?>"></i></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>Priority Support</td>
                <?php foreach ($plans as $p): ?>
                    <td><i class="fas fa-<?= ($p['priority_support'] ?? 0) ? 'check' : 'xmark' ?>" style="color:<?= ($p['priority_support'] ?? 0) ? '#00c853' : '#ff5252' ?>"></i></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>

<div class="cta-section">
    <h2>Ready to Grow Your Real Estate Business?</h2>
    <p>Start your 14-day free trial. No credit card required.</p>
    <a href="<?= $base ?>/tenant-signup" class="btn-plan primary" style="max-width:300px;margin:0 auto">Get Started Now</a>
</div>

<div class="footer-note">
    All prices in INR. GST additional where applicable. You can cancel anytime.
</div>

<script>
function toggleBilling() {
    const toggle = document.getElementById('billingToggle');
    const isYearly = toggle.classList.contains('yearly');
    setBilling(!isYearly);
}
function setBilling(yearly) {
    const toggle = document.getElementById('billingToggle');
    const monthlyLabel = document.querySelector('.billing-toggle .monthly');
    const yearlyLabel = document.querySelector('.billing-toggle .yearly');
    if (yearly) {
        toggle.classList.add('yearly');
        monthlyLabel.classList.remove('active');
        yearlyLabel.classList.add('active');
    } else {
        toggle.classList.remove('yearly');
        monthlyLabel.classList.add('active');
        yearlyLabel.classList.remove('active');
    }
    document.querySelectorAll('.plan-price').forEach(el => {
        const monthly = parseInt(el.dataset.monthly);
        const yearly2 = parseInt(el.dataset.yearly);
        if (monthly === 0) return;
        const amt = yearly ? Math.round(yearly2 / 12) : monthly;
        el.querySelector('.amount').textContent = amt.toLocaleString('en-IN');
        el.querySelector('.period').textContent = yearly ? '/mo (billed yearly)' : '/mo';
    });
}
</script>
</body>
</html>
