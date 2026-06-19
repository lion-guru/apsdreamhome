<?php require_once __DIR__ . '/../../Helpers/TranslationHelper.php'; ?>
<style>
.booking-form-section { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 20px; }
.plot-summary-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 12px; padding: 24px; }
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
                    <input type="hidden" name="plot_id" value="<?= $plot['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label"><?= __('plot_book_proceed', [], 'How would you like to proceed?') ?></label>
                        <select class="form-select" name="booking_type">
                            <option value="site_visit"><?= __('plot_book_visit_first', [], 'I want to visit the site first') ?></option>
                            <option value="online_consultation"><?= __('plot_book_direct', [], 'Online consultation / Direct booking') ?></option>
                        </select>
                    </div>

                    <?php if (!empty($plot['negotiated_price']) && $plot['negotiated_price'] != $plot['total_price']): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-tag"></i> <strong><?= __('plot_book_special_deal', [], 'Special Deal Price Available!') ?></strong>
                        <?= __('plot_book_special_price', [], 'This plot has a special negotiated price of') ?> <strong>₹<?= number_format(intval($plot['negotiated_price'])) ?></strong>
                        (<?= __('plot_book_original', [], 'Original') ?>: ₹<?= number_format(intval($plot['total_price'])) ?>)
                        <input type="hidden" name="negotiated_price" value="<?= $plot['negotiated_price'] ?>">
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label"><?= __('plot_book_notes', [], 'Notes / Special Requests (optional)') ?></label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="<?= __('plot_book_notes_ph', [], 'Any specific requirements or questions...') ?>"></textarea>
                    </div>

                    <div class="emi-note mb-3">
                        <i class="fas fa-info-circle"></i> <strong><?= __('plot_book_payment_terms', [], 'Payment Terms:') ?></strong> <?= __('plot_book_token_required', [], '25% token amount') ?> (₹<?= number_format(intval($plot['total_price'] * 0.25)) ?>) <?= __('plot_book_token_within', [], 'required within 15 days of booking confirmation. Balance as per agreed schedule.') ?>
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
                <div class="price">₹<?= number_format(intval($plot['negotiated_price'] ?? $plot['total_price'])) ?></div>
                <p class="detail mb-3">
                    <?= number_format(floatval($plot['area_sqft'] ?? 0)) ?> <?= __('plot_book_sqft', [], 'sqft') ?>
                    <?= !empty($plot['dimension_label']) ? '| ' . htmlspecialchars($plot['dimension_label']) : '' ?>
                    <?= !empty($plot['block']) ? '| ' . __('plot_book_block', [], 'Block') . ' ' . htmlspecialchars($plot['block']) : '' ?>
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
                    <?= htmlspecialchars(($plot['state_name'] ?? '') . ($plot['district_name'] ? ', ' . $plot['district_name'] : '')) ?>
                </div>
            </div>

            <!-- Your Existing Bookings -->
            <?php $userBookings = $userBookings ?? []; if (!empty($userBookings)): ?>
            <div class="booking-form-section mt-3">
                <h6 class="fw-bold"><i class="fas fa-history"></i> <?= __('plot_book_recent', [], 'Your Recent Bookings') ?></h6>
                <?php foreach (array_slice($userBookings, 0, 3) as $ub): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>#<?= $ub['id'] ?> — ₹<?= number_format(intval($ub['total_amount'] ?? $ub['amount'] ?? 0)) ?></span>
                        <span class="badge bg-<?= ($ub['status'] ?? '') === 'confirmed' ? 'success' : (($ub['status'] ?? '') === 'cancelled' ? 'danger' : 'warning') ?>">
                            <?= ucfirst($ub['status'] ?? 'pending') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
