<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'NACH / e-Mandate', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">NACH / e-Mandate Setup</h1>
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
                        <h4 class="fw-bold mb-3 text-primary">What is NACH / e-Mandate?</h4>
                        <p class="text-muted mb-4">NACH (National Automated Clearing House) is an electronic payment system that allows automatic debit from your bank account for recurring payments like EMI installments. It's the most convenient and hassle-free way to ensure your property payments are never missed.</p>

                        <h4 class="fw-bold mb-4 text-primary">How to Set Up</h4>
                        <div class="row g-4">
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 style-9543">
                                    <span class="fs-4 fw-bold">1</span>
                                </div>
                                <h6 class="fw-bold">Fill Mandate Form</h6>
                                <p class="text-muted small mb-0">Provide your bank details and authorization for auto-debit</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 style-9543">
                                    <span class="fs-4 fw-bold">2</span>
                                </div>
                                <h6 class="fw-bold">Bank Verification</h6>
                                <p class="text-muted small mb-0">Your bank verifies the mandate (1-2 business days)</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 style-9543">
                                    <span class="fs-4 fw-bold">3</span>
                                </div>
                                <h6 class="fw-bold">Mandate Active</h6>
                                <p class="text-muted small mb-0">Auto-debit starts from the next EMI due date</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3 style-9543">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h6 class="fw-bold">Auto-Pay Active</h6>
                                <p class="text-muted small mb-0">EMIs are debited automatically every month</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-4">Partner Banks</h4>
                <div class="row g-3 mb-5">
                    <?php foreach (['SBI', 'HDFC Bank', 'ICICI Bank', 'Axis Bank', 'PNB', 'Bank of Baroda'] as $bank): ?>
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm rounded-3 text-center p-3">
                            <div class="card-body">
                                <i class="fas fa-university text-primary fa-2x mb-2"></i>
                                <h6 class="fw-bold mb-0"><?= $bank ?></h6>
                                <small class="text-success">e-Mandate Supported</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-question-circle text-primary me-2"></i> Frequently Asked Questions</h5>
                        <div class="accordion" id="nachFaq">
                            <div class="accordion-item border-0 mb-2">
                                <h6 class="accordion-header">
                                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Is NACH safer than manual payment?
                                    </button>
                                </h6>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#nachFaq">
                                    <div class="accordion-body text-muted">Yes, NACH is regulated by NPCI and RBI. Your bank details are encrypted and the mandate can be cancelled at any time.</div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-2">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Can I cancel the mandate anytime?
                                    </button>
                                </h6>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#nachFaq">
                                    <div class="accordion-body text-muted">Yes, you can cancel the e-Mandate at any time through your bank's net banking portal or by submitting a written request to our office.</div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        What happens if there are insufficient funds?
                                    </button>
                                </h6>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#nachFaq">
                                    <div class="accordion-body text-muted">If your account has insufficient funds, the debit will fail and your bank may charge a penalty. We recommend maintaining sufficient balance before the EMI due date.</div>
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
