<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Title Protection', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Title Protection</h1>
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

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-3 text-primary">What is Title Protection?</h4>
                        <p class="text-muted mb-4">Title Protection ensures that the property you are purchasing has a clear and marketable title. Our legal team conducts a thorough verification of property ownership history, encumbrances, liens, and legal disputes before you finalize your purchase.</p>

                        <div class="row g-4 mt-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-success"><i class="fas fa-check-circle me-2"></i>What's Covered</h6>
                                <ul class="text-muted">
                                    <li>Ownership chain verification (30+ years)</li>
                                    <li>Encumbrance certificate check</li>
                                    <li>Court case and litigation search</li>
                                    <li>Government acquisition check</li>
                                    <li>Land revenue payment verification</li>
                                    <li>Succession and inheritance chain</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>What's Not Covered</h6>
                                <ul class="text-muted">
                                    <li>Fraudulent documents created after verification</li>
                                    <li>Disputes arising from agreements post-verification</li>
                                    <li>Government policy changes after verification date</li>
                                    <li>Third-party claims not registered in public records</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-4">Protection Plans</h4>
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4">
                            <h5 class="fw-bold text-primary">Basic</h5>
                            <div class="display-6 fw-bold text-primary my-3">₹5,000</div>
                            <p class="text-muted small">Standard title search for properties within city limits</p>
                            <ul class="text-muted small text-start">
                                <li>15-year ownership history</li>
                                <li>Basic encumbrance check</li>
                                <li>Online court records search</li>
                            </ul>
                            <a href="tel:+919277121112" class="btn btn-outline-primary rounded-pill mt-auto">Contact Us</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 border-primary border-2">
                            <span class="badge bg-primary mb-2">Most Popular</span>
                            <h5 class="fw-bold text-primary">Standard</h5>
                            <div class="display-6 fw-bold text-primary my-3">₹10,000</div>
                            <p class="text-muted small">Comprehensive title verification with legal opinion</p>
                            <ul class="text-muted small text-start">
                                <li>30-year ownership history</li>
                                <li>Full encumbrance certificate</li>
                                <li>Physical document verification</li>
                                <li>Legal opinion letter</li>
                            </ul>
                            <a href="tel:+919277121112" class="btn btn-primary rounded-pill mt-auto">Contact Us</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4">
                            <h5 class="fw-bold text-primary">Premium</h5>
                            <div class="display-6 fw-bold text-primary my-3">₹20,000</div>
                            <p class="text-muted small">Complete title protection with insurance coverage</p>
                            <ul class="text-muted small text-start">
                                <li>50+ year ownership history</li>
                                <li>All Basic + Standard features</li>
                                <li>Title insurance coverage</li>
                                <li>Legal representation if disputes arise</li>
                            </ul>
                            <a href="tel:+919277121112" class="btn btn-outline-primary rounded-pill mt-auto">Contact Us</a>
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
