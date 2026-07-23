<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Cancellation Policy', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Cancellation Policy</h1>
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
                        <h4 class="fw-bold mb-3 text-primary">1. Booking Cancellation</h4>
                        <p class="text-muted mb-4">If you wish to cancel a property booking, you must submit a written cancellation request to <a href="mailto:cancel@apsdreamhome.com" class="text-primary">cancel@apsdreamhome.com</a> or visit our office. Cancellation requests are processed within 7-10 business days.</p>

                        <h4 class="fw-bold mb-3 text-primary">2. Cancellation Charges</h4>
                        <ul class="text-muted mb-4">
                            <li><strong>Within 7 days of booking:</strong> Full refund minus administrative charges of ₹5,000</li>
                            <li><strong>8-30 days of booking:</strong> 75% refund of the booking amount</li>
                            <li><strong>After 30 days of booking:</strong> 50% refund of the booking amount</li>
                            <li><strong>After agreement execution:</strong> As per the terms of the sale agreement</li>
                        </ul>

                        <h4 class="fw-bold mb-3 text-primary">3. Service Cancellations</h4>
                        <p class="text-muted mb-4">For services such as site visits, consultations, or legal verification, cancellation must be made at least 24 hours before the scheduled appointment. Late cancellations may attract a fee of ₹500.</p>

                        <h4 class="fw-bold mb-3 text-primary">4. How to Cancel</h4>
                        <p class="text-muted mb-4">To cancel your booking or service, please contact us through any of the following channels:</p>
                        <ul class="text-muted mb-4">
                            <li>Email: <a href="mailto:cancel@apsdreamhome.com" class="text-primary">cancel@apsdreamhome.com</a></li>
                            <li>Phone: <a href="tel:+919277121112" class="text-primary">+91 9277121112</a></li>
                            <li>In-person: Visit any of our offices with original booking receipt</li>
                        </ul>

                        <h4 class="fw-bold mb-3 text-primary">5. Refund Processing</h4>
                        <p class="text-muted mb-0">Refunds will be processed within 15-30 business days from the date of approval. Refunds will be made through the same payment method used for the original transaction, unless otherwise specified.</p>
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
