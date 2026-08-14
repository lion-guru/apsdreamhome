<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Property Verification', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Property Verification Badge</h1>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url']) && $crumb['url']): ?>
                                <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <div class="text-center mb-5">
                    <p class="lead text-muted">APS Dream Home verified properties undergo a rigorous multi-step verification process. Look for the <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span> badge on property listings.</p>
                </div>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4 text-primary">How It Works</h4>
                        <div class="row g-4">
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" class="style-9543">
                                    <i class="fas fa-file-alt fa-lg"></i>
                                </div>
                                <h6 class="fw-bold">Document Check</h6>
                                <p class="text-muted small mb-0">Ownership documents, title deed, encumbrance certificate verified</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" class="style-9543">
                                    <i class="fas fa-map-marked-alt fa-lg"></i>
                                </div>
                                <h6 class="fw-bold">Physical Visit</h6>
                                <p class="text-muted small mb-0">On-site inspection of property condition, boundaries, and surroundings</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" class="style-9543">
                                    <i class="fas fa-gavel fa-lg"></i>
                                </div>
                                <h6 class="fw-bold">Legal Review</h6>
                                <p class="text-muted small mb-0">Legal team confirms clear title, no disputes, RERA compliance</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" class="style-9543">
                                    <i class="fas fa-award fa-lg"></i>
                                </div>
                                <h6 class="fw-bold">Badge Awarded</h6>
                                <p class="text-muted small mb-0">Verified badge added to listing with trust score and report</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-4">Verification Plans</h4>
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4">
                            <span class="badge bg-success mb-2">Free</span>
                            <h5 class="fw-bold">Basic</h5>
                            <p class="text-muted small">Online document verification and basic background check</p>
                            <div class="fw-bold text-success mb-3">â‚¹0</div>
                            <ul class="text-muted small text-start">
                                <li>Online records check</li>
                                <li>RERA status verification</li>
                                <li>Basic badge</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4">
                            <h5 class="fw-bold">Premium</h5>
                            <p class="text-muted small">Full document + physical verification</p>
                            <div class="fw-bold text-primary mb-3">â‚¹999</div>
                            <ul class="text-muted small text-start">
                                <li>All Basic features</li>
                                <li>Physical site visit</li>
                                <li>Premium badge</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 border-primary border-2">
                            <span class="badge bg-primary mb-2">Popular</span>
                            <h5 class="fw-bold">Gold</h5>
                            <p class="text-muted small">Complete verification with legal opinion</p>
                            <div class="fw-bold text-primary mb-3">â‚¹2,499</div>
                            <ul class="text-muted small text-start">
                                <li>All Premium features</li>
                                <li>Legal opinion letter</li>
                                <li>Gold badge</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4">
                            <h5 class="fw-bold">Platinum</h5>
                            <p class="text-muted small">Full verification + title insurance</p>
                            <div class="fw-bold text-primary mb-3">â‚¹4,999</div>
                            <ul class="text-muted small text-start">
                                <li>All Gold features</li>
                                <li>Title insurance</li>
                                <li>Platinum badge</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
                    <div class="card-body p-4 p-md-5 text-center">
                        <h4 class="fw-bold mb-3"><i class="fas fa-shield-alt me-2"></i>Why Choose Verification?</h4>
                        <div class="row g-3 text-center">
                            <div class="col-md-3">
                                <div class="fs-2 fw-bold">Zero</div>
                                <small class="opacity-75">Fraud Risk</small>
                            </div>
                            <div class="col-md-3">
                                <div class="fs-2 fw-bold">100%</div>
                                <small class="opacity-75">Legal Compliance</small>
                            </div>
                            <div class="col-md-3">
                                <div class="fs-2 fw-bold">30+ Yr</div>
                                <small class="opacity-75">Title History</small>
                            </div>
                            <div class="col-md-3">
                                <div class="fs-2 fw-bold">24 Hr</div>
                                <small class="opacity-75">Report Delivery</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
