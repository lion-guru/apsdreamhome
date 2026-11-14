<?php
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
/** @var array<string, mixed> $plot */
$plot = $plot ?? [];
/** @var array<string, mixed> $user */
$user = $user ?? [];
$lead_id = $lead_id ?? 0;
$userBookings = $userBookings ?? [];
?>
<style>
.booking-form-section { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 20px; }
.plot-summary-card { background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border-radius: 12px; padding: 24px; }
.plot-summary-card .price { font-size: 1.8rem; font-weight: 800; }
.plot-summary-card .detail { opacity: 0.9; font-size: 0.9rem; }
.emi-note { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px 16px; }
</style>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('home', [], 'Home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/dashboard"><?= __('plot_book_breadcrumb_dashboard', [], 'Dashboard') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($plot['colony_slug'] ?? '') ?>/plots"><?= htmlspecialchars($plot['colony_name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active"><?= __('plot_book_breadcrumb_book', [], 'Book Plot') ?> <?= htmlspecialchars($plot['plot_number'] ?? '') ?></li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="fas fa-file-contract"></i> <?= __('plot_book_heading', [], 'Book a Plot') ?></h2>

    <div class="row">
        <!-- Left: Booking Form -->
        <div class="col-lg-7">
            <div class="booking-form-section">
                <h5 class="fw-bold mb-3"><i class="fas fa-user"></i> <?= __('plot_book_your_info', [], 'Your Information') ?></h5>
                <div class="row mb-3">
                    <div class="col-md-6"><strong><?= __('plot_book_name', [], 'Name:') ?></strong> <?= htmlspecialchars($user['name'] ?? '') ?></div>
                    <div class="col-md-6"><strong><?= __('plot_book_email', [], 'Email:') ?></strong> <?= htmlspecialchars($user['email'] ?? '') ?></div>
                    <div class="col-md-6 mt-2"><strong><?= __('plot_book_phone', [], 'Phone:') ?></strong> <?= htmlspecialchars($user['phone'] ?? $user['mobile'] ?? '') ?></div>
                </div>
            </div>

            <div class="booking-form-section">
                <h5 class="fw-bold mb-3"><i class="fas fa-file-signature"></i> <?= __('plot_book_details', [], 'Booking Details') ?></h5>
                <form method="POST" action="<?= BASE_URL ?>/plot/book">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="plot_id" value="<?= (int)($plot['id'] ?? 0) ?>">
                    <?php if (!empty($lead_id)): ?>
                    <input type="hidden" name="lead_id" value="<?= (int)$lead_id ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="booking_type"><?= __('plot_book_proceed', [], 'How would you like to proceed?') ?></label>
                        <select aria-label="Booking type" class="form-select" name="booking_type" id="booking_type">
                            <option value="site_visit"><?= __('plot_book_visit_first', [], 'I want to visit the site first') ?></option>
                            <option value="online_consultation"><?= __('plot_book_direct', [], 'Online consultation / Direct booking') ?></option>
                        </select>
                    </div>

                    <?php if (!empty($plot['negotiated_price']) && !empty($plot['total_price']) && $plot['negotiated_price'] != $plot['total_price']): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-tag"></i> <strong><?= __('plot_book_special_deal', [], 'Special Deal Price Available!') ?></strong>
                        <?= __('plot_book_special_price', [], 'This plot has a special negotiated price of') ?> <strong>₹<?= number_format(intval($plot['negotiated_price'])) ?></strong>
                        (<?= __('plot_book_original', [], 'Original') ?>: ₹<?= number_format(intval($plot['total_price'])) ?>)
                        <input type="hidden" name="negotiated_price" value="<?= htmlspecialchars((string)$plot['negotiated_price']) ?>">
                    </div>
                    <?php endif; ?>

                    <!-- Festive Promo Code / Concession -->
                    <div class="mb-3 p-3 rounded border bg-light">
                        <label class="form-label fw-bold mb-1" for="booking_promo_code">
                            <i class="fas fa-gift text-danger me-1"></i> <?= __('festive_promo_label', [], 'Festive Promo Code / Concession (त्योहारी छूट कोड)') ?>
                        </label>
                        <div class="input-group">
                            <input type="text" name="promo_code" id="booking_promo_code" class="form-control text-uppercase fw-bold" placeholder="e.g. FESTIVE21, SUBHLABH5" value="<?= htmlspecialchars($_GET['promo'] ?? '') ?>">
                            <button type="button" class="btn btn-outline-primary" id="btn_apply_customer_promo">
                                <i class="fas fa-check me-1"></i> Apply
                            </button>
                        </div>
                        <input type="hidden" name="discount_amount" id="cust_discount_amount" value="0">
                        <div id="cust_promo_feedback" class="small mt-2 text-muted">
                            <?php if (!empty($_GET['promo'])): ?>
                                <span class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i> Checking offer code...</span>
                            <?php else: ?>
                                Enter promotional or festival discount code (e.g. <code>FESTIVE21</code>). <a href="<?= BASE_URL ?>/offers" target="_blank" class="text-decoration-none">View active festival offers</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="booking_notes"><?= __('plot_book_notes', [], 'Notes / Special Requests (optional)') ?></label>
                        <textarea aria-label="Notes" class="form-control" name="notes" id="booking_notes" rows="3" placeholder="<?= __('plot_book_notes_ph', [], 'Any specific requirements or questions...') ?>"></textarea>
                    </div>

                    <div class="emi-note mb-3" id="plot_payment_terms_box">
                        <i class="fas fa-info-circle"></i> <strong><?= __('plot_book_payment_terms', [], 'Payment Terms:') ?></strong>
                        <span id="terms_token_desc">
                            <?= __('plot_book_token_required', [], '25% token amount') ?> (₹<?= number_format(intval(($plot['negotiated_price'] ?? $plot['total_price'] ?? 0) * 0.25)) ?>) <?= __('plot_book_token_within', [], 'required within 15 days of booking confirmation. Balance as per agreed schedule.') ?>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-check-circle"></i> <?= __('plot_book_confirm', [], 'Confirm Booking Request') ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Plot Summary -->
        <div class="col-lg-5">
            <div class="plot-summary-card">
                <h5 class="fw-bold mb-3"><?= htmlspecialchars($plot['colony_name'] ?? '') ?></h5>
                <h4 class="fw-bold">Plot <?= htmlspecialchars($plot['plot_number'] ?? '') ?></h4>
                <div class="price">₹<?= number_format(intval($plot['negotiated_price'] ?? $plot['total_price'] ?? 0)) ?></div>
                <p class="detail mb-3">
                    <?= number_format(floatval($plot['area_sqft'] ?? 0)) ?> <?= __('plot_book_sqft', [], 'sqft') ?>
                    <?= !empty($plot['dimension_label']) ? '| ' . htmlspecialchars($plot['dimension_label'] ?? '') : '' ?>
                    <?= !empty($plot['block']) ? '| ' . __('plot_book_block', [], 'Block') . ' ' . htmlspecialchars($plot['block'] ?? '') : '' ?>
                </p>
                <hr class="border-light">
                <div class="row detail">
                    <div class="col-6">₹<?= number_format(floatval($plot['price_per_sqft'] ?? 0)) ?>/sqft</div>
                    <div class="col-6">
                        <?php if ($plot['corner_plot'] ?? false): ?><?= __('plot_book_corner', [], 'Corner Plot') ?><?php endif; ?>
                        <?php if ($plot['park_facing'] ?? false): ?><?= ($plot['corner_plot'] ?? false) ? ' | ' : '' ?><?= __('plot_book_park', [], 'Park Facing') ?><?php endif; ?>
                    </div>
                </div>
                <div class="mt-3 detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars(($plot['state_name'] ?? '') . (!empty($plot['district_name']) ? ', ' . $plot['district_name'] : '')) ?>
                </div>
            </div>

            <!-- Your Existing Bookings -->
            <?php if (!empty($userBookings)): ?>
            <div class="booking-form-section mt-3">
                <h6 class="fw-bold"><i class="fas fa-history"></i> <?= __('plot_book_recent', [], 'Your Recent Bookings') ?></h6>
                <?php foreach (array_slice($userBookings, 0, 3) as $ub): ?>
                    <?php
                        $bStatus = $ub['status'] ?? 'pending';
                        $statusClass = 'warning';
                        if ($bStatus === 'confirmed') {
                            $statusClass = 'success';
                        } elseif ($bStatus === 'cancelled') {
                            $statusClass = 'danger';
                        }
                    ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>#<?= (int)($ub['id'] ?? 0) ?> — ₹<?= number_format(intval($ub['total_amount'] ?? $ub['amount'] ?? 0)) ?></span>
                        <span class="badge bg-<?= $statusClass ?>">
                            <?= ucfirst($bStatus) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const promoInput = document.getElementById('booking_promo_code');
    const applyBtn = document.getElementById('btn_apply_customer_promo');
    const feedback = document.getElementById('cust_promo_feedback');
    const discountHidden = document.getElementById('cust_discount_amount');
    const termsDesc = document.getElementById('terms_token_desc');
    const basePrice = <?= (float)($plot['negotiated_price'] ?? $plot['total_price'] ?? 0) ?>;
    const colonyId = <?= (int)($plot['colony_id'] ?? 0) ?>;

    function validatePromo(code) {
        if (!code || code.trim() === '') {
            feedback.innerHTML = '<span class="text-muted">Enter promotional or festival discount code (e.g. <code>FESTIVE21</code>).</span>';
            discountHidden.value = '0';
            return;
        }

        feedback.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i> Validating code...</span>';

        fetch('<?= BASE_URL ?>/api/promo/validate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'promo_code=' + encodeURIComponent(code.trim()) + '&plot_value=' + encodeURIComponent(basePrice) + '&colony_id=' + encodeURIComponent(colonyId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                const discount = parseFloat(data.discount_amount || 0);
                const netPrice = Math.max(0, basePrice - discount);
                const tokenAmt = Math.round(netPrice * 0.25);
                discountHidden.value = discount;

                feedback.innerHTML = '<div class="alert alert-success py-2 px-3 mb-0 mt-2 small">' +
                    '<i class="fas fa-check-circle me-1"></i> <strong>' + (data.title || 'Festive Offer') + ' Applied!</strong><br>' +
                    'Festive Discount: <strong>-₹' + discount.toLocaleString('en-IN') + '</strong> | Net Agreement: <strong>₹' + netPrice.toLocaleString('en-IN') + '</strong>' +
                    '</div>';

                if (termsDesc) {
                    termsDesc.innerHTML = '<strong>Festive Discounted Price: ₹' + netPrice.toLocaleString('en-IN') + '</strong> (Saved ₹' + discount.toLocaleString('en-IN') + '). ' +
                        '25% token amount (<strong>₹' + tokenAmt.toLocaleString('en-IN') + '</strong>) required within 15 days of booking confirmation. Balance as per agreed schedule.';
                }
            } else {
                discountHidden.value = '0';
                feedback.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0 mt-2 small">' +
                    '<i class="fas fa-times-circle me-1"></i> ' + (data.message || 'Invalid or expired promo code') +
                    '</div>';
            }
        })
        .catch(err => {
            feedback.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i> Validation failed. Please try again.</span>';
        });
    }

    if (applyBtn && promoInput) {
        applyBtn.addEventListener('click', function() {
            validatePromo(promoInput.value);
        });

        promoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                validatePromo(promoInput.value);
            }
        });

        // Auto-validate if promo code pre-populated from URL
        if (promoInput.value.trim() !== '') {
            validatePromo(promoInput.value);
        }
    }
});
</script>
