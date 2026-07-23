<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Property Insurance', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Property Insurance</h1>
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

                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="fas fa-shield-alt text-primary fa-lg"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">Property Shield</h5>
                                </div>
                                <p class="text-muted">Comprehensive coverage against natural calamities, fire, theft, and man-made disasters. Covers the full property value with zero depreciation on structure.</p>
                                <ul class="text-muted small">
                                    <li>Natural disaster coverage (flood, earthquake, cyclone)</li>
                                    <li>Fire and explosion protection</li>
                                    <li>Theft and burglary coverage</li>
                                    <li>Third-party liability</li>
                                </ul>
                                <div class="mt-auto">
                                    <span class="badge bg-primary">Starting ₹3,500/year</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                        <i class="fas fa-hard-hat text-success fa-lg"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">Construction Guard</h5>
                                </div>
                                <p class="text-muted">Specialized insurance for properties under construction. Covers material on-site, worker accidents, and structural defects during the building phase.</p>
                                <ul class="text-muted small">
                                    <li>Building materials on-site</li>
                                    <li>Worker accident coverage</li>
                                    <li>Structural defect protection</li>
                                    <li>Delayed completion coverage</li>
                                </ul>
                                <div class="mt-auto">
                                    <span class="badge bg-success">Starting ₹5,000/year</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                        <i class="fas fa-file-contract text-warning fa-lg"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">Title Protect</h5>
                                </div>
                                <p class="text-muted">Protects against legal disputes over property ownership, encumbrances, and title defects discovered after purchase. Essential for resale properties.</p>
                                <ul class="text-muted small">
                                    <li>Title dispute legal costs</li>
                                    <li>Encumbrance claims</li>
                                    <li>Fraud protection</li>
                                    <li>Legal representation costs</li>
                                </ul>
                                <div class="mt-auto">
                                    <span class="badge bg-warning">Starting ₹2,000/year</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                        <i class="fas fa-mountain text-danger fa-lg"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">Earthquake Cover</h5>
                                </div>
                                <p class="text-muted">Enhanced coverage specifically for seismic events. Covers structural damage, content damage, temporary accommodation, and rebuilding costs.</p>
                                <ul class="text-muted small">
                                    <li>Seismic damage to structure</li>
                                    <li>Contents and furniture</li>
                                    <li>Temporary accommodation costs</li>
                                    <li>Rebuilding and repair costs</li>
                                </ul>
                                <div class="mt-auto">
                                    <span class="badge bg-danger">Starting ₹4,000/year</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                    <div class="card-body p-4 p-md-5 text-center">
                        <h4 class="fw-bold mb-3">Need Insurance Advice?</h4>
                        <p class="mb-4 opacity-75">Our team can help you choose the right insurance plan for your property.</p>
                        <a href="tel:+919277121112" class="btn btn-light rounded-pill px-5 py-2">
                            <i class="fas fa-phone me-2"></i> Call +91 9277121112
                        </a>
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
