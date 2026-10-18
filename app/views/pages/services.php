<?php
$page_title = $page_title ?? 'Our Services - APS Dream Home';
$page_description = $page_description ?? 'Professional real estate services by APS Dream Home — Plot Selling, Legal & Documentation, Construction, EMI Plans, and Resale Assistance.';
$current_page = 'services';
?>
<div class="container-fluid px-4">
    <div class="svc-hero">
        <div class="container">
            <h1 class="fw-bold mb-3"><?= htmlspecialchars($page_title) ?></h1>
            <p class="lead text-light mb-4" style="max-width: 640px; margin: 0 auto 24px;">
                Professional real estate services by APS Dream Home — Plot Selling, Legal & Documentation, Construction, EMI Plans, and Resale Assistance.
            </p>
            <div class="badge-row d-flex justify-content-center gap-3 flex-wrap mt-2">
                <span class="b-item"><strong>Plot Selling</strong> & Legal Docs</span>
                <span class="b-item"><strong>Construction</strong> & Project Mgmt</span>
                <span class="b-item"><strong>EMI Plans</strong> & Resale Help</span>
            </div>
        </div>
    </div>

    <section class="svc-section py-5">
        <div class="container">
            <div class="section-head text-center mb-5">
                <h2 class="fw-bold" style="font-size: 1.8rem; color: #2c3e50;">Our Services</h2>
                <p class="text-muted" style="max-width: 560px; margin: 0 auto;">
                    Comprehensive real estate solutions tailored for buyers, sellers, and investors across Uttar Pradesh.
                </p>
            </div>

            <div class="svc-grid row g-4 justify-content-center" style="max-width: 1200px; margin: 0 auto;">
                <!-- Service Card 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="svc-card card h-100 border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-body p-4">
                            <div class="icon-wrap d-inline-flex align-items-center justify-content-center mb-3 rounded-3" style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #2980b9, #2c3e50); font-size: 1.6rem; color: #fff;">
                                <i class="fas fa-home"></i>
                            </div>
                            <h3 class="fw-bold" style="font-size: 1.2rem; color: #2c3e50;">Plot Selling & Buying</h3>
                            <p class="desc text-muted" style="font-size: 0.95rem;">End-to-end plot transaction support — from search to registry.</p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success me-2"></i> Plot search & site visits</li>
                                <li><i class="fas fa-check text-success me-2"></i> Price negotiation & token booking</li>
                                <li><i class="fas fa-check text-success me-2"></i> Agreement & registry support</li>
                            </ul>
                            <a href="<?= BASE_URL ?>/properties" class="card-link d-inline-flex align-items-center gap-2" style="color: #2980b9; font-weight: 600;">
                                Explore <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="svc-card card h-100 border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-body p-4">
                            <div class="icon-wrap d-inline-flex align-items-center justify-content-center mb-3 rounded-3" style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #27ae60, #2ecc71); font-size: 1.6rem; color: #fff;">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <h3 class="fw-bold" style="font-size: 1.2rem; color: #2c3e50;">Legal & Documentation</h3>
                            <p class="desc text-muted" style="font-size: 0.95rem;">Complete legal support — from title verification to registry deeds.</p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success me-2"></i> Title search & verification</li>
                                <li><i class="fas fa-check text-success me-2"></i> Sale agreement & deed drafting</li>
                                <li><i class="fas fa-check text-success me-2"></i> Stamp duty & registry assistance</li>
                            </ul>
                            <a href="<?= BASE_URL ?>/user/construction-approvals" class="card-link d-inline-flex align-items-center gap-2" style="color: #2980b9; font-weight: 600;">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="svc-card card h-100 border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-body p-4">
                            <div class="icon-wrap d-inline-flex align-items-center justify-content-center mb-3 rounded-3" style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #f39c12, #f1c40f); font-size: 1.6rem; color: #fff;">
                                <i class="fas fa-hammer"></i>
                            </div>
                            <h3 class="fw-bold" style="font-size: 1.2rem; color: #2c3e50;">Construction & Project Management</h3>
                            <p class="desc text-muted" style="font-size: 0.95rem;">End-to-end construction supervision — from foundation to finish.</p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success me-2"></i> Planning, approvals & permits</li>
                                <li><i class="fas fa-check text-success me-2"></i> Quality supervision & material audit</li>
                                <li><i class="fas fa-check text-success me-2"></i> Handover & defect liability</li>
                            </ul>
                            <a href="<?= BASE_URL ?>/admin/construction/colony-progress" class="card-link d-inline-flex align-items-center gap-2" style="color: #2980b9; font-weight: 600;">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="svc-card card h-100 border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-body p-4">
                            <div class="icon-wrap d-inline-flex align-items-center justify-content-center mb-3 rounded-3" style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #e74c3c, #c0392b); font-size: 1.6rem; color: #fff;">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h3 class="fw-bold" style="font-size: 1.2rem; color: #2c3e50;">EMI Plans & Financing</h3>
                            <p class="desc text-muted" style="font-size: 0.95rem;">Flexible payment plans & bank loan assistance for your dream plot.</p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success me-2"></i> Zero-cost EMI on select plots</li>
                                <li><i class="fas fa-check text-success me-2"></i> Bank loan tie-ups & documentation</li>
                                <li><i class="fas fa-check text-success me-2"></i> Foreclosure & prepayment options</li>
                            </ul>
                            <a href="<?= BASE_URL ?>/user/emi-tracker" class="card-link d-inline-flex align-items-center gap-2" style="color: #2980b9; font-weight: 600;">
                                Calculate EMI <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="svc-card card h-100 border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-body p-4">
                            <div class="icon-wrap d-inline-flex align-items-center justify-content-center mb-3 rounded-3" style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #8e44ad, #9b59b6); font-size: 1.6rem; color: #fff;">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <h3 class="fw-bold" style="font-size: 1.2rem; color: #2c3e50;">Resale & Transfer Assistance</h3>
                            <p class="desc text-muted" style="font-size: 0.95rem;">Hassle-free resale, transfer & NOC processing for your plot.</p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success me-2"></i> Buyer verification & KYC</li>
                                <li><i class="fas fa-check text-success me-2"></i> NOC & transfer deed execution</li>
                                <li><i class="fas fa-check text-success me-2"></i> Mutation & registry update</li>
                            </ul>
                            <a href="<?= BASE_URL ?>/user/plot-transfers" class="card-link d-inline-flex align-items-center gap-2" style="color: #2980b9; font-weight: 600;">
                                Start Transfer <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Card 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="svc-card card h-100 border-0 shadow-sm" style="border-radius: 14px; border: 1px solid #e2e8f0;">
                        <div class="card-body p-4">
                            <div class="icon-wrap d-inline-flex align-items-center justify-content-center mb-3 rounded-3" style="width: 64px; height: 64px; border-radius: 14px; background: linear-gradient(135deg, #17a2b8, #138496); font-size: 1.6rem; color: #fff;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="fw-bold" style="font-size: 1.2rem; color: #2c3e50;">After-Sales & Support</h3>
                            <p class="desc text-muted" style="font-size: 0.95rem;">Dedicated support for maintenance, grievances & community services.</p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success me-2"></i> Maintenance dues & billing</li>
                                <li><i class="fas fa-check text-success me-2"></i> Grievance redressal & township services</li>
                                <li><i class="fas fa-check text-success me-2"></i> Community events & engagement</li>
                            </ul>
                            <a href="<?= BASE_URL ?>/user/support" class="card-link d-inline-flex align-items-center gap-2" style="color: #2980b9; font-weight: 600;">
                                Get Support <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="fw-bold mb-3" style="color: #2c3e50;">Ready to Start Your Property Journey?</h2>
            <p class="text-muted mb-4" style="max-width: 600px; margin: 0 auto;">Our expert team is ready to guide you through every step — from plot selection to registry.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= BASE_URL ?>/properties" class="btn btn-primary btn-lg px-4 py-2">
                    <i class="fas fa-search me-2"></i>Browse Properties
                </a>
                <a href="<?= BASE_URL ?>/list-property" class="btn btn-outline-primary btn-lg px-4 py-2">
                    <i class="fas fa-plus me-2"></i>List Your Property
                </a>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary btn-lg px-4 py-2">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Service-specific styles -->
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    .svc-hero {
        background: linear-gradient(135deg, #2c3e50 0%, #2980b9 100%);
        color: #fff;
        padding: 60px 20px 50px;
        text-align: center;
        margin-bottom: 0;
    }
    .svc-hero h1 { font-size: clamp(2rem, 5vw, 2.8rem); font-weight: 700; margin-bottom: 12px; }
    .svc-hero p { font-size: 1.1rem; color: #cbd5e1; max-width: 640px; margin: 0 auto 24px; }
    .badge-row { display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; margin-top: 8px; }
    .badge-row .b-item { font-size: 0.85rem; color: #94a3b8; }
    .badge-row .b-item strong { color: #fff; }

    .svc-section { padding: 56px 20px; }
    .section-head { text-align: center; margin-bottom: 40px; }
    .section-head h2 { font-size: 1.8rem; color: #2c3e50; font-weight: 700; margin-bottom: 8px; }
    .section-head p { color: #64748b; max-width: 560px; margin: 0 auto; }

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
        background: #2980b9;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    .svc-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(41,128,185,0.15); }
    .svc-card:hover::before { transform: scaleX(1); }

    .icon-wrap {
        width: 64px; height: 64px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }
    .svc-card:hover .icon-wrap { transform: scale(1.08); }

    .svc-card h3 { font-size: 1.2rem; color: #2c3e50; font-weight: 700; margin: 0 0 10px; }
    .desc { color: #64748b; font-size: 0.95rem; margin-bottom: 18px; }
    .svc-card ul { list-style: none; padding: 0; margin: 0 0 22px; }
    .svc-card ul li { font-size: 0.9rem; color: #475569; padding: 4px 0; }
    .svc-card ul li::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; color: #10b981; margin-right: 10px; font-size: 0.8rem; }
    .card-link { display: inline-flex; align-items: center; gap: 6px; color: #2980b9; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: color 0.2s; }
    .card-link:hover { color: #2c3e50; }
</style>