<?php
$current_page = $current_page ?? 'book-plot';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$csrfToken = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/browse">Browse Plots</a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail">Plot <?= htmlspecialchars($plot['plot_number']) ?></a></li>
            <li class="breadcrumb-item active">Book</li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="fas fa-file-contract me-2"></i>Book a Plot</h2>

    <form method="POST" action="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book" id="bookingForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="row g-4">

            <!-- Left: Booking Form -->
            <div class="col-lg-7">

                <!-- Customer Info -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-user me-2"></i>Your Information</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? $user['mobile'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">User ID</label>
                                <input type="text" class="form-control" value="#<?= (int)($user['id'] ?? 0) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Plan -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-credit-card me-2"></i>Payment Plan</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planFull" value="full">
                                    <label class="form-check-label w-100" for="planFull">
                                        <strong class="d-block">Full Payment</strong>
                                        <small class="text-muted">Pay the entire amount upfront. No EMI.</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_plan" id="planEmi" value="emi" checked>
                                    <label class="form-check-label w-100" for="planEmi">
                                        <strong class="d-block">EMI Plan (12 months)</strong>
                                        <small class="text-muted">Pay 25% token + 12 monthly installments @ 10% p.a.</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded p-3 mt-3">
                            <div class="row text-center">
                                <div class="col">
                                    <small class="text-muted d-block">Token Amount</small>
                                    <strong class="text-primary">₹<?= number_format($tokenAmount) ?></strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Balance</small>
                                    <strong>₹<?= number_format($plot['total_price'] - $tokenAmount) ?></strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Total Price</small>
                                    <strong class="text-primary fs-5">₹<?= number_format($plot['total_price']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-sticky-note me-2"></i>Additional Notes</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <textarea class="form-control" name="notes" rows="3" placeholder="Any special requests or questions (optional)"></textarea>
                    </div>
                </div>

                <!-- Terms -->
                <div class="aps-cp-card mb-4">
                    <div class="aps-cp-card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                I agree to the <a href="<?= $baseUrl ?>/legal/terms-conditions" target="_blank">Terms &amp; Conditions</a>
                                and understand that the 25% token amount is required to confirm this booking.
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                    <i class="fas fa-check-circle me-2"></i>Confirm Booking Request
                </button>
                <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-1"></i>Back to Plot Detail
                </a>
            </div>

            <!-- Right: Plot Summary -->
            <div class="col-lg-5">
                <div class="aps-cp-card mb-4" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border: none;">
                    <div class="aps-cp-card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-building me-1"></i><?= htmlspecialchars($plot['colony_name']) ?>
                        </h5>
                        <h3 class="fw-bold mb-3">Plot <?= htmlspecialchars($plot['plot_number']) ?></h3>
                        <div class="fs-2 fw-bold mb-3">₹<?= number_format($plot['total_price']) ?></div>
                        <hr style="border-color: rgba(255,255,255,0.3);">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="opacity-75">Area</small><br>
                                <strong><?= number_format($plot['area_sqft']) ?> sqft</strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75">Dimensions</small><br>
                                <strong><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75">Block</small><br>
                                <strong><?= htmlspecialchars($plot['block'] ?? '—') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75">Location</small><br>
                                <strong><?= htmlspecialchars($plot['district_name'] ?? '') ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($plot['corner_plot'])): ?>
                            <span class="badge bg-warning"><i class="fas fa-star me-1"></i>Corner Plot</span>
                        <?php endif; ?>
                        <?php if (!empty($plot['park_facing'])): ?>
                            <span class="badge bg-success"><i class="fas fa-tree me-1"></i>Park Facing</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-info-circle me-2"></i>Payment Terms</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                25% token (₹<?= number_format($tokenAmount) ?>) to confirm
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Balance via EMI or full payment
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Estimated stamp duty: ~₹<?= number_format(round($plot['total_price'] * 0.05)) ?>
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Registration at Sub-Registrar office
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if (!document.getElementById('termsCheck').checked) {
        e.preventDefault();
        alert('Please agree to the Terms & Conditions before proceeding.');
        return false;
    }
});
</script>
