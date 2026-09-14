<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Our Services - APS Dream Home') ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? 'Professional real estate services by APS Dream Home — Plot Selling, Legal & Documentation, Construction, EMI Plans, and Resale Assistance.') ?>">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #2c3e50;
            --trust-blue: #2980b9;
            --navy-light: #34495e;
            --gold: #d4af37;
            --bg: #f4f6f9;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: #1a1a2e; line-height: 1.7; }

        /* ── Hero ── */
        .svc-hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--trust-blue) 100%);
            color: #fff;
            padding: 60px 20px 50px;
            text-align: center;
        }
        .svc-hero h1 { font-size: 2.4rem; font-weight: 700; margin-bottom: 12px; }
        .svc-hero p { font-size: 1.1rem; color: #cbd5e1; max-width: 640px; margin: 0 auto 24px; }
        .svc-hero .badge-row { display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; margin-top: 8px; }
        .svc-hero .badge-row .b-item { font-size: 0.85rem; color: #94a3b8; }
        .svc-hero .badge-row .b-item strong { color: #fff; }

        /* ── Cards Grid ── */
        .svc-section { padding: 56px 20px; }
        .svc-section .section-head { text-align: center; margin-bottom: 40px; }
        .svc-section .section-head h2 { font-size: 1.8rem; color: var(--navy); font-weight: 700; margin-bottom: 8px; }
        .svc-section .section-head p { color: #64748b; max-width: 560px; margin: 0 auto; }

        .svc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 28px; max-width: 1200px; margin: 0 auto; }

        .svc-card {
            background: #fff;
            border-radius: 14px;
            padding: 36px 28px 32px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .svc-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--trust-blue);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .svc-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(41,128,185,0.15); }
        .svc-card:hover::before { transform: scaleX(1); }

        .svc-card .icon-wrap {
            width: 64px; height: 64px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .svc-card:hover .icon-wrap { transform: scale(1.08); }

        .svc-card h3 { font-size: 1.2rem; color: var(--navy); font-weight: 700; margin: 0 0 10px; }
        .svc-card .desc { color: #64748b; font-size: 0.95rem; margin-bottom: 18px; }
        .svc-card ul { list-style: none; padding: 0; margin: 0 0 22px; }
        .svc-card ul li { font-size: 0.9rem; color: #475569; padding: 4px 0; }
        .svc-card ul li::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; color: #10b981; margin-right: 10px; font-size: 0.8rem; }
        .svc-card .card-link { display: inline-flex; align-items: center; gap: 6px; color: var(--trust-blue); font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: color 0.2s; }
        .svc-card .card-link:hover { color: var(--navy); }
        .svc-card .card-link i { transition: transform 0.2s; }
        .svc-card:hover .card-link i { transform: translateX(3px); }

        /* icon colour variants */
        .ic-blue   { background: #dbeafe; color: #2563eb; }
        .ic-green  { background: #d1fae5; color: #059669; }
        .ic-orange { background: #ffedd5; color: #ea580c; }
        .ic-purple { background: #ede9fe; color: #7c3aed; }
        .ic-red    { background: #fee2e2; color: #dc2626; }

        /* ── Process Strip ── */
        .proc-strip { background: #fff; padding: 48px 20px; border-top: 1px solid #e2e8f0; }
        .proc-strip h2 { text-align: center; font-size: 1.6rem; color: var(--navy); margin-bottom: 32px; }
        .proc-steps { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; max-width: 960px; margin: 0 auto; }
        .proc-step { flex: 1; min-width: 180px; text-align: center; position: relative; padding: 20px 12px; }
        .proc-step .num { width: 44px; height: 44px; border-radius: 50%; background: var(--trust-blue); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; margin-bottom: 12px; }
        .proc-step h4 { font-size: 0.95rem; color: var(--navy); margin: 0 0 6px; }
        .proc-step p { font-size: 0.82rem; color: #64748b; margin: 0; }

        /* ── CTA ── */
        .svc-cta {
            background: linear-gradient(135deg, var(--navy) 0%, var(--trust-blue) 100%);
            color: #fff;
            text-align: center;
            padding: 56px 20px;
        }
        .svc-cta h2 { font-size: 1.7rem; margin-bottom: 10px; }
        .svc-cta p { color: #cbd5e1; margin-bottom: 28px; }
        .svc-cta .btn-cta { display: inline-block; padding: 14px 36px; border-radius: 8px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all 0.3s; margin: 0 6px 10px; }
        .svc-cta .btn-primary-custom { background: var(--gold); color: var(--navy); }
        .svc-cta .btn-primary-custom:hover { background: #e6c24a; transform: translateY(-2px); }
        .svc-cta .btn-outline-custom { border: 2px solid rgba(255,255,255,0.4); color: #fff; }
        .svc-cta .btn-outline-custom:hover { border-color: #fff; background: rgba(255,255,255,0.1); }

        @media (max-width: 768px) {
            .svc-hero h1 { font-size: 1.8rem; }
            .svc-grid { grid-template-columns: 1fr; }
            .proc-steps { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════ HERO ═══════════════════ -->
<section class="svc-hero">
    <div class="container">
        <h1><i class="fas fa-handshake me-2"></i> Our Services</h1>
        <p>End-to-end real estate solutions — from finding the right plot to handing over the keys. Backed by <strong>15+ years</strong> of trust in Lucknow.</p>
        <div class="badge-row">
            <span class="b-item"><strong>500+</strong> Properties Sold</span>
            <span class="b-item"><strong>1,000+</strong> Happy Families</span>
            <span class="b-item"><strong>4</strong> Active Colonies</span>
            <span class="b-item"><strong>24/7</strong> Support</span>
        </div>
    </div>
</section>

<!-- ═══════════════════ SERVICES GRID ═══════════════════ -->
<section class="svc-section">
    <div class="section-head">
        <h2>What We Offer</h2>
        <p>Tailored services for buyers, investors, and land partners across Lucknow and Uttar Pradesh.</p>
    </div>

    <div class="svc-grid">

        <!-- 1 · Plot Selling -->
        <div class="svc-card">
            <div class="icon-wrap ic-blue"><i class="fas fa-map-marked-alt"></i></div>
            <h3>Plot Selling &amp; Land Deals</h3>
            <p class="desc">RERA-registered residential and commercial plots in prime Lucknow locations with clear titles and documented approvals.</p>
            <ul>
                <li>GDA &amp; RERA approved layouts</li>
                <li>Clear title &amp; encumbrance-free</li>
                <li>Flexible plot sizes (800 – 5,000 sq ft)</li>
                <li>On-site visit assistance</li>
            </ul>
            <a href="<?= BASE_URL ?>/properties" class="card-link">View Plots <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- 2 · Legal & Documentation -->
        <div class="svc-card">
            <div class="icon-wrap ic-green"><i class="fas fa-file-contract"></i></div>
            <h3>Legal &amp; Documentation</h3>
            <p class="desc">Complete legal support — from title verification and agreement drafting to registry assistance and RERA compliance.</p>
            <ul>
                <li>Title search &amp; verification</li>
                <li>Sale deed &amp; agreement drafting</li>
                <li>Registry &amp; mutation assistance</li>
                <li>RERA &amp; GDA liaison</li>
            </ul>
            <a href="<?= BASE_URL ?>/legal" class="card-link">Legal Services <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- 3 · Construction & Development -->
        <div class="svc-card">
            <div class="icon-wrap ic-orange"><i class="fas fa-hard-hat"></i></div>
            <h3>Construction &amp; Development</h3>
            <p class="desc">End-to-end construction management — from foundation to finishing — with transparent costing and quality oversight.</p>
            <ul>
                <li>Architectural plans &amp; approvals</li>
                <li>Residential &amp; commercial builds</li>
                <li>Interior design &amp; finishing</li>
                <li>Quality supervision at every stage</li>
            </ul>
            <a href="<?= BASE_URL ?>/contact" class="card-link">Get a Quote <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- 4 · Flexible EMI Plans -->
        <div class="svc-card">
            <div class="icon-wrap ic-purple"><i class="fas fa-hand-holding-usd"></i></div>
            <h3>Flexible EMI Plans</h3>
            <p class="desc">Affordable payment plans designed for every budget — interest-free installments, NACH e-mandate, and company loan options.</p>
            <ul>
                <li>Up to 36-month interest-free EMI</li>
                <li>NACH auto-debit convenience</li>
                <li>Easy documentation &amp; instant approval</li>
                <li>Early settlement discounts</li>
            </ul>
            <a href="<?= BASE_URL ?>/legal/emi-plans" class="card-link">Explore Plans <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- 5 · Resale & Secondary Market -->
        <div class="svc-card">
            <div class="icon-wrap ic-red"><i class="fas fa-sync-alt"></i></div>
            <h3>Resale &amp; Secondary Market</h3>
            <p class="desc">Hassle-free resale of your APS plots — fair valuation, verified buyers, and complete transfer documentation handled for you.</p>
            <ul>
                <li>Free market valuation report</li>
                <li>Verified buyer network</li>
                <li>Transfer &amp; NOC assistance</li>
                <li>No hidden brokerage</li>
            </ul>
            <a href="<?= BASE_URL ?>/contact" class="card-link">Sell Your Plot <i class="fas fa-arrow-right"></i></a>
        </div>

    </div>
</section>

<!-- ═══════════════════ PROCESS ═══════════════════ -->
<section class="proc-strip">
    <h2><i class="fas fa-route me-2" style="color:var(--trust-blue)"></i> How It Works</h2>
    <div class="proc-steps">
        <div class="proc-step">
            <div class="num">1</div>
            <h4>Consultation</h4>
            <p>Share your requirements — budget, location, size. Our advisors guide you.</p>
        </div>
        <div class="proc-step">
            <div class="num">2</div>
            <h4>Site Visit</h4>
            <p>Visit shortlisted plots with our team. Inspect layout, roads, and surroundings.</p>
        </div>
        <div class="proc-step">
            <div class="num">3</div>
            <h4>Documentation</h4>
            <p>We handle all legal paperwork — agreements, registry, RERA filings.</p>
        </div>
        <div class="proc-step">
            <div class="num">4</div>
            <h4>Handover</h4>
            <p>Complete possession with all approvals. Welcome to the APS family.</p>
        </div>
    </div>
</section>

<!-- ═══════════════════ CTA ═══════════════════ -->
<section class="svc-cta">
    <h2>Ready to Get Started?</h2>
    <p>Talk to our property advisors today — zero brokerage, complete transparency.</p>
    <a href="<?= BASE_URL ?>/contact" class="btn-cta btn-primary-custom"><i class="fas fa-phone me-2"></i>Contact Us</a>
    <a href="tel:919277121112" class="btn-cta btn-outline-custom"><i class="fas fa-phone-alt me-2"></i>Call: 92771 21112</a>
</section>

</body>
</html>
