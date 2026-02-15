<?php
/**
 * MLM Opportunity View - APS Dream Homes
 */
?>

<!-- Page Header -->
<section class="mlm-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Business Opportunity</h1>
        <p class="lead mb-0">Join APS Dream Homes MLM Program and build your financial future in real estate.</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<div class="container py-5 mt-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
            <h2 class="text-primary fw-bold mb-4">Why Join Us?</h2>
            <p class="lead text-muted mb-4">
                APS Dream Homes offers a unique opportunity to enter the booming real estate market with zero initial investment and a proven system for success.
            </p>
            <ul class="list-unstyled mb-4">
                <li class="mb-3 d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>High commission rates on every property sale.</span>
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Multi-level earning potential through team building.</span>
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Professional training and marketing support.</span>
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Flexible working hours - be your own boss.</span>
                </li>
            </ul>
            <a href="<?= BASE_URL ?>register" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm">
                Become An Associate <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
            <div class="position-relative">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Team Success" class="img-fluid rounded-4 shadow-lg">
                <div class="position-absolute bottom-0 end-0 bg-white p-4 rounded-start-4 shadow mb-4">
                    <h4 class="text-primary fw-bold mb-0">10+ Levels</h4>
                    <p class="small text-muted mb-0">Growth Potential</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MLM Levels Section -->
    <div class="py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="text-primary fw-bold">Career Progression & Commissions</h2>
            <p class="text-muted">Explore our transparent level-based commission structure</p>
        </div>

        <?php if (!empty($mlm_levels)): ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($mlm_levels as $index => $level): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                        <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift overflow-hidden">
                            <div class="card-header bg-primary text-white text-center py-4 border-0">
                                <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-2">Level <?= $level['level_order'] ?></span>
                                <h4 class="fw-bold mb-0"><?= h($level['level_name']) ?></h4>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span class="text-muted">Direct Commission</span>
                                    <span class="fw-bold text-primary h4 mb-0"><?= $level['direct_commission_percentage'] ?>%</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <span class="text-muted">Team Commission</span>
                                    <span class="fw-bold text-success"><?= $level['team_commission_percentage'] ?>%</span>
                                </div>
                                <div class="mb-4">
                                    <h6 class="fw-bold small text-uppercase text-muted mb-3">Requirements</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-users text-primary me-2 small"></i>
                                        <span class="small">Team: <?= $level['team_size_required'] ?>+ members</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-plus text-primary me-2 small"></i>
                                        <span class="small">Directs: <?= $level['direct_referrals_required'] ?>+ referrals</span>
                                    </div>
                                </div>
                                <a href="<?= BASE_URL ?>register" class="btn btn-outline-primary w-100 rounded-pill">Get Started</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center rounded-4 border-0 shadow-sm p-5">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>Commission details are being updated.</h4>
                <p class="mb-0">Please contact our support team for the latest MLM plan details.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container py-4">
        <h2 class="fw-bold mb-3">Ready to Start Earning?</h2>
        <p class="lead opacity-75 mb-4 mx-auto" style="max-width: 700px;">Join APS Dream Homes MLM program and start building your real estate empire today. Free to join, no hidden charges.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>register" class="btn btn-warning btn-lg px-4 py-3 rounded-pill fw-bold shadow">
                <i class="fas fa-rocket me-2"></i>Join Now - Free
            </a>
            <a href="<?= BASE_URL ?>commission-calculator" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-bold">
                <i class="fas fa-calculator me-2"></i>Calculate Earnings
            </a>
        </div>
        <div class="mt-4 opacity-75 small">
            <span class="mx-2">✅ Free to Join</span>
            <span class="mx-2">✅ No Hidden Charges</span>
            <span class="mx-2">✅ Instant Commission Tracking</span>
            <span class="mx-2">✅ 24/7 Support</span>
        </div>
    </div>
</section>

<style>
    .mlm-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .header-desc {
        max-width: 700px;
    }
    .bg-primary-subtle {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
</style>
