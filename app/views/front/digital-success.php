<?php
/** @var array $booking */
$base = BASE_URL ?? '/apsdreamhome';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">
                    <div class="success-animation mb-4">
                        <div class="checkmark-circle">
                            <div class="checkmark-check"></div>
                        </div>
                    </div>
                    
                    <h2 class="mb-3 text-success">Booking Completed Successfully!</h2>
                    <p class="text-muted mb-4">
                        Your booking <strong><?= htmlspecialchars($booking['booking_number'] ?? '') ?></strong> has been digitally signed and confirmed.
                    </p>

                    <div class="alert alert-light border text-start mb-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-file-alt me-2 text-primary"></i>What happens next?</h6>
                        <ul class="mb-0 small">
                            <li class="mb-2">✅ All legal documents have been digitally signed</li>
                            <li class="mb-2">✅ Video consent recorded and stored</li>
                            <li class="mb-2">✅ EMI payment schedule generated</li>
                            <li class="mb-2">✅ Booking status updated to <strong>Agreement Signed</strong></li>
                            <li class="mb-2">📧 Confirmation email sent to your registered email</li>
                            <li class="mb-2">📱 SMS notification sent to your registered mobile</li>
                            <li class="mb-2">📄 Signed PDF agreements available for download</li>
                        </ul>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-bold text-primary">Booking Number</div>
                                <div class="small"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-bold text-success">Plot</div>
                                <div class="small"><?= htmlspecialchars($booking['plot_code'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-bold text-info">Agreement Value</div>
                                <div class="small">₹<?= number_format((float)($booking['agreement_value'] ?? 0), 2) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-bold text-warning">Next EMI Due</div>
                                <div class="small"><?= $booking['next_emi_date'] ?? 'Check schedule' ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-eye me-2"></i>View Booking Details
                        </a>
                        <a href="/user/dashboard" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your documents are legally valid under the Information Technology Act, 2000.
                            Digital signatures are admissible as evidence in court.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkmark-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #28a745;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scaleIn 0.5s ease-out;
}

.checkmark-check {
    width: 40px;
    height: 20px;
    border: solid white;
    border-width: 0 4px 4px 0;
    transform: rotate(45deg);
    animation: checkIn 0.3s ease-out 0.3s both;
}

@keyframes scaleIn {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

@keyframes checkIn {
    from { opacity: 0; transform: rotate(45deg) scale(0); }
    to { opacity: 1; transform: rotate(45deg) scale(1); }
}
</style>