<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Agreements & E-Sign', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Agreements & E-Sign</h1>
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

                <p class="text-muted mb-5">Review and sign your property agreements digitally. E-Sign is legally valid under the Information Technology Act, 2000.</p>

                <div class="row g-4 mb-5">
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-primary text-white p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-file-contract me-2"></i>Sale Agreement</h6>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small">The primary agreement between buyer and seller outlining terms of the property sale, payment schedule, and possession timeline.</p>
                                <span class="badge bg-success">Required</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-info text-white p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-hammer me-2"></i>Construction Agreement</h6>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small">Applicable for under-construction properties. Defines construction specifications, material standards, and completion timeline.</p>
                                <span class="badge bg-info">If Applicable</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-success text-white p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-home me-2"></i>Allotment Letter</h6>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small">Official letter allotting the specific property unit to the buyer with details of area, floor, and specifications.</p>
                                <span class="badge bg-success">Required</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-warning text-dark p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-tools me-2"></i>Maintenance Agreement</h6>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small">Terms for ongoing property maintenance, common area upkeep, and society formation after possession.</p>
                                <span class="badge bg-warning text-dark">Post-Possession</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-secondary text-white p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-key me-2"></i>Rental Agreement</h6>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small">For investors renting out their property. Standard rental agreement with security deposit and tenancy terms.</p>
                                <span class="badge bg-secondary">For Investors</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-light">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-3"><i class="fas fa-signature text-primary me-2"></i> How E-Sign Works</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <span class="badge bg-primary rounded-pill me-3 px-3 py-2">1</span>
                                    <div>
                                        <h6 class="fw-bold">Review Document</h6>
                                        <p class="text-muted small mb-0">Read through the agreement carefully on screen</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <span class="badge bg-primary rounded-pill me-3 px-3 py-2">2</span>
                                    <div>
                                        <h6 class="fw-bold">OTP Verification</h6>
                                        <p class="text-muted small mb-0">Verify identity via OTP sent to your registered mobile</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <span class="badge bg-success rounded-pill me-3 px-3 py-2">3</span>
                                    <div>
                                        <h6 class="fw-bold">Sign Digitally</h6>
                                        <p class="text-muted small mb-0">Apply e-signature — legally binding under IT Act 2000</p>
                                    </div>
                                </div>
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
