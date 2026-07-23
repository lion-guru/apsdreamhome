<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Refund Policy', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Refund Policy</h1>
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

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-3 text-primary">1. Eligibility for Refund</h4>
                        <p class="text-muted mb-4">Refunds are applicable in the following scenarios: booking cancellation (as per cancellation policy), service non-delivery, duplicate payments, or any payment made in error. All refund requests must be submitted within 30 days of the original transaction.</p>

                        <h4 class="fw-bold mb-3 text-primary">2. Refund Process</h4>
                        <ol class="text-muted mb-4">
                            <li>Submit a refund request via email or in-person</li>
                            <li>Our team will review the request within 3 business days</li>
                            <li>Upon approval, refund will be initiated within 7-10 business days</li>
                            <li>Crediting to your account may take an additional 5-7 business days depending on your bank</li>
                        </ol>

                        <h4 class="fw-bold mb-3 text-primary">3. Non-Refundable Items</h4>
                        <ul class="text-muted mb-4">
                            <li>Administrative processing charges (₹5,000 per booking)</li>
                            <li>Legal verification fees once the process has started</li>
                            <li>Third-party charges (government fees, registration charges) already paid</li>
                            <li>Consultation fees for completed sessions</li>
                        </ul>

                        <h4 class="fw-bold mb-3 text-primary">4. Partial Refunds</h4>
                        <p class="text-muted mb-4">In cases where services have been partially delivered, a proportionate refund will be calculated and processed. The refund amount will be determined based on the portion of service not yet rendered.</p>

                        <h4 class="fw-bold mb-3 text-primary">5. Contact for Refunds</h4>
                        <p class="text-muted mb-0">For any refund-related queries, please reach out to <a href="mailto:accounts@apsdreamhome.com" class="text-primary fw-bold">accounts@apsdreamhome.com</a> or call <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a>. Please keep your booking receipt/payment proof ready when contacting us.</p>
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
