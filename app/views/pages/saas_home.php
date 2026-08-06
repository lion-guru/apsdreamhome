<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'APS CRM — All-in-One Business Platform') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; color: #1a1a2e; background: #fff; line-height: 1.6; }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            color: #fff; padding: 100px 24px 80px; text-align: center; position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(102,126,234,0.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(118,75,162,0.1) 0%, transparent 40%);
            animation: float 20s ease-in-out infinite;
        }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        .hero-content { position: relative; z-index: 1; max-width: 800px; margin: 0 auto; }
        .hero-badge {
            display: inline-block; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 999px; padding: 6px 20px; font-size: 13px; font-weight: 500; letter-spacing: 0.5px;
            margin-bottom: 24px; backdrop-filter: blur(4px);
        }
        .hero h1 { font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 800; line-height: 1.15; margin-bottom: 20px; }
        .hero h1 span { background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 1.2rem; color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto 36px; }
        .hero-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 14px 32px; border-radius: 12px;
            font-size: 16px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; cursor: pointer; border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2); color: #fff;
            box-shadow: 0 4px 20px rgba(102,126,234,0.4);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(102,126,234,0.5); }
        .btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,0.3); }
        .btn-outline:hover { border-color: #fff; background: rgba(255,255,255,0.1); }

        /* Stats Bar */
        .stats-bar {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; max-width: 900px;
            margin: -40px auto 0; padding: 0 24px; position: relative; z-index: 2;
        }
        .stat-card {
            background: #fff; border-radius: 16px; padding: 24px; text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        .stat-card .number { font-size: 2rem; font-weight: 800; color: #667eea; }
        .stat-card .label { font-size: 13px; color: #666; margin-top: 4px; }

        /* Section Shared */
        .section { padding: 80px 24px; }
        .section-title { text-align: center; margin-bottom: 48px; }
        .section-title h2 { font-size: clamp(1.8rem, 3vw, 2.5rem); font-weight: 800; margin-bottom: 12px; }
        .section-title p { font-size: 1.1rem; color: #666; max-width: 600px; margin: 0 auto; }
        .section-title .badge {
            display: inline-block; background: linear-gradient(135deg, #667eea15, #764ba215);
            color: #667eea; font-size: 13px; font-weight: 600; padding: 4px 14px;
            border-radius: 999px; margin-bottom: 12px;
        }

        /* Features Grid */
        .features-bg { background: #f8f9fc; }
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px; max-width: 1100px; margin: 0 auto;
        }
        .feature-card {
            background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #eef0f5;
            transition: all 0.3s ease;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .feature-icon {
            width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center;
            justify-content: center; font-size: 24px; margin-bottom: 20px;
        }
        .feature-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; }
        .feature-card p { font-size: 0.95rem; color: #666; line-height: 1.6; }

        /* Modules */
        .modules-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px; max-width: 1100px; margin: 0 auto;
        }
        .module-card {
            background: #fff; border-radius: 16px; padding: 28px; text-align: center;
            border: 2px solid #f0f0f5; transition: all 0.3s ease; cursor: default;
        }
        .module-card:hover { border-color: #667eea; background: linear-gradient(135deg, #667eea08, #764ba208); }
        .module-card i { font-size: 32px; color: #667eea; margin-bottom: 12px; }
        .module-card h4 { font-weight: 700; margin-bottom: 6px; }
        .module-card p { font-size: 0.85rem; color: #888; }

        /* How It Works */
        .steps-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 32px; max-width: 1000px; margin: 0 auto;
        }
        .step { text-align: center; }
        .step-number {
            width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 800; margin: 0 auto 16px;
        }
        .step h4 { font-weight: 700; margin-bottom: 8px; }
        .step p { font-size: 0.9rem; color: #666; }

        /* Pricing */
        .pricing-bg { background: #f8f9fc; }
        .pricing-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px; max-width: 1100px; margin: 0 auto;
        }
        .price-card {
            background: #fff; border-radius: 20px; padding: 36px; text-align: center;
            border: 2px solid #f0f0f5; transition: all 0.3s ease; position: relative;
        }
        .price-card.popular { border-color: #667eea; transform: scale(1.05); }
        .price-card.popular::before {
            content: 'MOST POPULAR'; position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; font-size: 11px;
            font-weight: 700; padding: 4px 16px; border-radius: 999px; letter-spacing: 0.5px;
        }
        .price-card h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: 4px; }
        .price-card .desc { font-size: 0.85rem; color: #888; margin-bottom: 20px; }
        .price-card .amount { font-size: 2.5rem; font-weight: 800; color: #1a1a2e; }
        .price-card .amount span { font-size: 1rem; font-weight: 400; color: #888; }
        .price-card .period { font-size: 0.85rem; color: #888; margin-bottom: 24px; }
        .price-card ul { list-style: none; text-align: left; margin-bottom: 28px; }
        .price-card li { padding: 8px 0; font-size: 0.9rem; color: #444; display: flex; align-items: center; gap: 10px; }
        .price-card li i { color: #22c55e; font-size: 14px; }
        .btn-full { width: 100%; justify-content: center; }

        /* Testimonial */
        .testimonial-section { background: linear-gradient(135deg, #0f0c29, #302b63); color: #fff; text-align: center; }
        .testimonial-section h2 { font-size: 2rem; font-weight: 800; margin-bottom: 16px; }
        .testimonial-section p { color: rgba(255,255,255,0.7); max-width: 600px; margin: 0 auto 40px; }
        .testimonial-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px; max-width: 1000px; margin: 0 auto;
        }
        .testimonial-card {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px; padding: 28px; backdrop-filter: blur(4px);
        }
        .testimonial-card .stars { color: #fbbf24; margin-bottom: 12px; }
        .testimonial-card .text { font-size: 0.95rem; color: rgba(255,255,255,0.85); margin-bottom: 16px; font-style: italic; }
        .testimonial-card .author { font-weight: 600; }
        .testimonial-card .role { font-size: 0.8rem; color: rgba(255,255,255,0.5); }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, #667eea, #764ba2); color: #fff;
            text-align: center; padding: 80px 24px;
        }
        .cta-section h2 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; margin-bottom: 16px; }
        .cta-section p { font-size: 1.15rem; opacity: 0.9; max-width: 600px; margin: 0 auto 32px; }
        .btn-white { background: #fff; color: #667eea; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.2); }

        /* Footer */
        .footer { background: #0f0c29; color: rgba(255,255,255,0.6); padding: 48px 24px 24px; text-align: center; }
        .footer a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .footer-links { display: flex; gap: 24px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px; }

        @media (max-width: 768px) {
            .stats-bar { grid-template-columns: repeat(2, 1fr); margin-top: -20px; }
            .hero { padding: 60px 16px 50px; }
        }
    </style>
</head>
<body>

<!-- Hero -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge"><i class="fas fa-rocket"></i> India's #1 Real Estate CRM Platform</div>
        <h1>Run Your Entire Business on <span>One Platform</span></h1>
        <p>CRM, ERP, Accounting, Legal, AI — everything you need to manage properties, teams, and customers in one powerful SaaS platform.</p>
        <div class="hero-buttons">
            <a href="/tenant-signup" class="btn btn-primary"><i class="fas fa-play"></i> Start Free Trial</a>
            <a href="/pricing" class="btn btn-outline"><i class="fas fa-tag"></i> View Pricing</a>
        </div>
    </div>
</section>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-card"><div class="number">500+</div><div class="label">Properties Listed</div></div>
    <div class="stat-card"><div class="number">50+</div><div class="label">Teams Onboarded</div></div>
    <div class="stat-card"><div class="number">₹10Cr+</div><div class="label">Revenue Managed</div></div>
    <div class="stat-card"><div class="number">99.9%</div><div class="label">Uptime SLA</div></div>
</div>

<!-- Modules -->
<section class="section">
    <div class="section-title">
        <div class="badge">ALL-IN-ONE</div>
        <h2>Everything Your Business Needs</h2>
        <p>12 integrated modules that replace 10+ separate tools. No more switching between apps.</p>
    </div>
    <div class="modules-grid">
        <div class="module-card"><i class="fas fa-users"></i><h4>CRM</h4><p>Lead pipeline, scoring, auto-assign</p></div>
        <div class="module-card"><i class="fas fa-building"></i><h4>ERP</h4><p>Properties, plots, colonies, inventory</p></div>
        <div class="module-card"><i class="fas fa-hand-holding-dollar"></i><h4>Sales</h4><p>Bookings, payments, EMI, commissions</p></div>
        <div class="module-card"><i class="fas fa-calculator"></i><h4>Accounting</h4><p>Bank, cash, TDS, GST, reconciliation</p></div>
        <div class="module-card"><i class="fas fa-network-wired"></i><h4>MLM</h4><p>Unilevel, binary, commissions, payouts</p></div>
        <div class="module-card"><i class="fas fa-robot"></i><h4>AI Engine</h4><p>Price prediction, lead scoring, chatbot</p></div>
        <div class="module-card"><i class="fas fa-file-contract"></i><h4>Legal</h4><p>Documents, templates, KYC, e-sign</p></div>
        <div class="module-card"><i class="fas fa-bullhorn"></i><h4>Marketing</h4><p>Email, SMS, WhatsApp, campaigns</p></div>
        <div class="module-card"><i class="fas fa-phone-volume"></i><h4>Calling</h4><p>AI voice calls, auto-dialer, recording</p></div>
        <div class="module-card"><i class="fas fa-chart-pie"></i><h4>Reports</h4><p>Analytics, dashboards, exports</p></div>
        <div class="module-card"><i class="fas fa-mobile-screen-button"></i><h4>Mobile App</h4><p>Android app for field teams</p></div>
        <div class="module-card"><i class="fas fa-globe"></i><h4>White Label</h4><p>Your brand, your domain</p></div>
    </div>
</section>

<!-- How It Works -->
<section class="section features-bg">
    <div class="section-title">
        <div class="badge">GET STARTED</div>
        <h2>Up and Running in 5 Minutes</h2>
        <p>No installation. No IT team needed. Sign up and start using immediately.</p>
    </div>
    <div class="steps-grid">
        <div class="step">
            <div class="step-number">1</div>
            <h4>Sign Up</h4>
            <p>Enter your company details and choose a plan</p>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <h4>Configure</h4>
            <p>Set up your team, properties, and preferences</p>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <h4>Import Data</h4>
            <p>Upload your existing leads and properties via CSV</p>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <h4>Go Live</h4>
            <p>Start managing your business immediately</p>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section">
    <div class="section-title">
        <div class="badge">WHY APS CRM</div>
        <h2>Built for Real Estate. Scaled for You.</h2>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background: #667eea15; color: #667eea;"><i class="fas fa-shield-halved"></i></div>
            <h3>Enterprise Security</h3>
            <p>AES-256-GCM encryption, 2FA, session management, RBAC, CSRF protection, audit logs. Your data is bank-grade secure.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: #764ba215; color: #764ba2;"><i class="fas fa-brain"></i></div>
            <h3>AI-Powered</h3>
            <p>Smart lead scoring, price prediction, auto-assignment, conversational chatbot, voice AI — all built-in, no extra cost.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: #22c55e15; color: #22c55e;"><i class="fas fa-puzzle-piece"></i></div>
            <h3>Modular Architecture</h3>
            <p>Use CRM alone, or add ERP, Accounting, Legal, MLM — each module works independently or together seamlessly.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: #f59e0b15; color: #f59e0b;"><i class="fas fa-palette"></i></div>
            <h3>White-Label Ready</h3>
            <p>Your logo, your colors, your domain. Clients never see the APS brand. Full customization of sidebar, dashboard, and reports.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: #ef444415; color: #ef4444;"><i class="fas fa-mobile-screen"></i></div>
            <h3>Mobile First</h3>
            <p>Native Android app for field teams. Leads, properties, bookings, commissions — manage everything on the go.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: #06b6d415; color: #06b6d4;"><i class="fas fa-headset"></i></div>
            <h3>24/7 Support</h3>
            <p>Dedicated onboarding, phone support, video tutorials, and documentation. We're with you every step of the way.</p>
        </div>
    </div>
</section>

<!-- Pricing -->
<section class="section pricing-bg">
    <div class="section-title">
        <div class="badge">PRICING</div>
        <h2>Simple, Transparent Pricing</h2>
        <p>No hidden fees. No surprise charges. Cancel anytime.</p>
    </div>
    <div class="pricing-grid">
        <?php $plans = $plans ?? []; foreach ($plans as $i => $plan): ?>
        <div class="price-card <?= $i === 2 ? 'popular' : '' ?>">
            <h3><?= htmlspecialchars($plan['name'] ?? 'Plan') ?></h3>
            <div class="desc"><?= htmlspecialchars($plan['description'] ?? '') ?></div>
            <div class="amount">₹<?= number_format((float)($plan['price_monthly'] ?? 0)) ?><span>/mo</span></div>
            <div class="period">Billed monthly</div>
            <ul>
                <li><i class="fas fa-check"></i> <?= (int)($plan['max_users'] ?? 0) ?> Users</li>
                <li><i class="fas fa-check"></i> <?= (int)($plan['max_leads'] ?? 0) ?> Leads</li>
                <li><i class="fas fa-check"></i> <?= (int)($plan['max_properties'] ?? 0) ?> Properties</li>
                <li><i class="fas fa-check"></i> <?= (int)($plan['storage_limit_mb'] ?? 0) ?> MB Storage</li>
                <li><i class="fas fa-check"></i> Email & SMS Support</li>
            </ul>
            <a href="/tenant-signup?plan=<?= (int)($plan['id'] ?? 1) ?>" class="btn btn-primary btn-full">Get Started</a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonial-section section">
    <h2>Trusted by Real Estate Professionals</h2>
    <p>See what our customers have to say about APS CRM.</p>
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="text">"APS CRM transformed our business. We went from managing leads in Excel to a full CRM with automated follow-ups in just one week."</div>
            <div class="author">Rajesh Kumar</div>
            <div class="role">Director, Skyline Builders</div>
        </div>
        <div class="testimonial-card">
            <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="text">"The accounting module alone saved us ₹2 lakhs/year in CA fees. Everything from TDS to GST is automated now."</div>
            <div class="author">Priya Sharma</div>
            <div class="role">Finance Head, Green Valley Estates</div>
        </div>
        <div class="testimonial-card">
            <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="text">"The white-label feature let us launch our own branded CRM for clients in 3 days. Best investment we made."</div>
            <div class="author">Amit Patel</div>
            <div class="role">CEO, PropertyPro Solutions</div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2>Ready to Transform Your Business?</h2>
    <p>Start your 14-day free trial. No credit card required. Set up in 5 minutes.</p>
    <a href="/tenant-signup" class="btn btn-white"><i class="fas fa-rocket"></i> Start Free Trial</a>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-links">
        <a href="/pricing">Pricing</a>
        <a href="/tenant-signup">Sign Up</a>
        <a href="/login">Login</a>
        <a href="/privacy">Privacy Policy</a>
        <a href="/contact">Contact Us</a>
    </div>
    <p>&copy; <?= date('Y') ?> APS CRM. All rights reserved. Built with <i class="fas fa-heart" style="color:#ef4444;"></i> in India.</p>
</footer>

</body>
</html>
