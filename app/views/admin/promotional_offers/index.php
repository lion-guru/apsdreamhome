<?php
$page_title = $page_title ?? 'Customer Festival & Promotional Offers';
$offers = $offers ?? [];
$colonies = $colonies ?? [];
$stats = $stats ?? ['active_offers' => 0, 'total_redeemed' => 0, 'total_saved_est' => 0.0, 'unique_codes' => 0];
$today = date('Y-m-d');
?>
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1 text-primary"><i class="fas fa-gift me-2"></i><?= htmlspecialchars($page_title) ?></h4>
            <p class="text-muted small mb-0">त्योहारी व प्रमोशनल ऑफर्स — कस्टमर के लिए बुकिंग डिस्काउंट, प्रोमो कोड्स व गिफ्ट वाउचर्स।</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/associate-offers" class="btn btn-outline-info">
                <i class="fas fa-medal me-1"></i>Associate Festival Desk
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#promoModal">
                <i class="fas fa-plus-circle me-1"></i>New Customer Offer
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 text-uppercase fw-semibold">Active Offers</small>
                            <h3 class="mb-0 fw-bold"><?= (int)$stats['active_offers'] ?></h3>
                        </div>
                        <i class="fas fa-fire fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 text-uppercase fw-semibold">Total Redemptions</small>
                            <h3 class="mb-0 fw-bold"><?= (int)$stats['total_redeemed'] ?></h3>
                        </div>
                        <i class="fas fa-ticket-alt fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-dark-50 text-uppercase fw-semibold">Customer Savings Est.</small>
                            <h3 class="mb-0 fw-bold">&#8377;<?= number_format((float)$stats['total_saved_est']) ?></h3>
                        </div>
                        <i class="fas fa-coins fa-2x text-dark-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 text-uppercase fw-semibold">Live Promo Codes</small>
                            <h3 class="mb-0 fw-bold"><?= (int)$stats['unique_codes'] ?></h3>
                        </div>
                        <i class="fas fa-tags fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offers Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2  class="fs-6 fw-semibold mb-0 fw-bold text-dark"><i class="fas fa-list me-2"></i>Active &amp; Archived Promotional Campaigns</h2>
            <span class="badge bg-light text-dark border"><?= count($offers) ?> Total Offers</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Campaign &amp; Festival</th>
                            <th>Discount / Value</th>
                            <th>Promo Code</th>
                            <th>Colony Scope</th>
                            <th class="text-center">Validity Window</th>
                            <th class="text-center">Redemptions</th>
                            <th>Status</th>
                            <th style="width:160px" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($offers)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No promotional offers created yet. Click "New Customer Offer" to launch one.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($offers as $o): ?>
                        <?php
                            $isExpired = (!empty($o['valid_until']) && $o['valid_until'] < $today);
                            $isActive = ($o['status'] ?? '') === 'active' && !$isExpired;
                            $discountType = $o['discount_type'] ?? 'percentage';
                            $val = (float)($o['discount_value'] ?? 0);
                            $pct = (float)($o['discount_percentage'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2 p-2 bg-light rounded text-primary">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($o['title'] ?? '') ?></span>
                                        <?php if (!empty($o['festival_name'])): ?>
                                            <span class="badge bg-warning text-dark ms-1"><i class="fas fa-sparkles me-1"></i><?= htmlspecialchars($o['festival_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($o['description'])): ?>
                                            <div class="text-muted small" style="max-width:320px;"><?= htmlspecialchars(mb_substr($o['description'], 0, 90)) ?>...</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($discountType === 'percentage'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success fs-6"><?= $pct ?>% Concession</span>
                                <?php elseif ($discountType === 'flat_amount'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary fs-6">&#8377;<?= number_format($val) ?> Flat Off</span>
                                <?php elseif ($discountType === 'per_sqft'): ?>
                                    <span class="badge bg-info-subtle text-info border border-info fs-6">&#8377;<?= number_format($val) ?> / sq.ft.</span>
                                <?php elseif ($discountType === 'gift_item'): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning fs-6"><i class="fas fa-gift me-1"></i>Gift Hamper</span>
                                <?php elseif ($discountType === 'cashback_wallet'): ?>
                                    <span class="badge bg-purple-subtle text-purple border border-purple fs-6">&#8377;<?= number_format($val) ?> Cashback</span>
                                <?php endif; ?>
                                <?php if (!empty($o['min_booking_amount']) && (float)$o['min_booking_amount'] > 0): ?>
                                    <div class="text-muted small mt-1">Min. Booking: &#8377;<?= number_format((float)$o['min_booking_amount']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($o['promo_code'])): ?>
                                    <code class="px-2 py-1 bg-light border rounded text-danger fw-bold fs-6"><?= htmlspecialchars($o['promo_code']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted small">Auto-applied</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($o['colony_name'])): ?>
                                    <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($o['colony_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-primary border"><i class="fas fa-globe me-1"></i>All Townships</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small">
                                <div><i class="far fa-calendar-alt text-muted me-1"></i><?= !empty($o['valid_from']) ? date('d M Y', strtotime($o['valid_from'])) : 'Immediate' ?></div>
                                <div class="text-muted">&darr; to</div>
                                <div class="<?= $isExpired ? 'text-danger fw-bold' : 'text-dark' ?>"><?= !empty($o['valid_until']) ? date('d M Y', strtotime($o['valid_until'])) : 'Open' ?></div>
                            </td>
                            <td class="text-center">
                                <strong><?= (int)($o['times_redeemed'] ?? 0) ?></strong>
                                <?php if (!empty($o['max_redemptions']) && (int)$o['max_redemptions'] > 0): ?>
                                    <span class="text-muted small">/ <?= (int)$o['max_redemptions'] ?></span>
                                <?php else: ?>
                                    <div class="text-muted small">Unlimited</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isExpired): ?>
                                    <span class="badge bg-danger">Expired</span>
                                <?php elseif (($o['status'] ?? '') === 'active'): ?>
                                    <span class="badge bg-success">Live &amp; Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($o['status'] ?? 'inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?= BASE_URL ?>/admin/promotional-offers/status" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                    <input type="hidden" name="status" value="<?= ($o['status'] ?? '') === 'active' ? 'inactive' : 'active' ?>">
                                    <button class="btn btn-sm btn-outline-secondary" title="<?= ($o['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas fa-<?= ($o['status'] ?? '') === 'active' ? 'pause' : 'play' ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?= BASE_URL ?>/admin/promotional-offers/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this offer?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Offer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Customer Promotional Offer -->
<div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/promotional-offers/store">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="promoModalLabel"><i class="fas fa-gift me-2"></i>Create New Customer Festival / Promotional Offer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Offer Campaign Title <span class="text-danger">*</span></label>
                            <input name="title" class="form-control" maxlength="255" required placeholder="e.g. Navratri Shubh Labh Concession">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Festival / Tag</label>
                            <select name="festival_name" class="form-select">
                                <option value="Festival Special">Festival Special</option>
                                <option value="Diwali Dhamaka">Diwali Dhamaka</option>
                                <option value="Navratri Utsav">Navratri Utsav</option>
                                <option value="New Year Bumper">New Year Bumper</option>
                                <option value="Holi Mahotsav">Holi Mahotsav</option>
                                <option value="Eid Mubarak">Eid Mubarak</option>
                                <option value="Independence Day">Independence Day</option>
                                <option value="Monsoon Bonanza">Monsoon Bonanza</option>
                                <option value="Exclusive Launch">Exclusive Launch</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Promo Code (कस्टमर कूपन कोड)</label>
                            <input name="promo_code" class="form-control text-uppercase" maxlength="50" placeholder="e.g. FESTIVE21, SUBHLABH5">
                            <small class="text-muted">Unique alphanumeric code entered during plot booking.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Colony / Township Scope</label>
                            <select name="colony_id" class="form-select">
                                <option value="">All Townships &amp; Colonies (Universal)</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Discount Concession Type <span class="text-danger">*</span></label>
                            <select name="discount_type" id="modal_discount_type" class="form-select" onchange="toggleDiscountFields()">
                                <option value="percentage">Percentage Off (% of Plot Value)</option>
                                <option value="flat_amount" selected>Flat Cash Concession (₹ Off Token / Agreement)</option>
                                <option value="per_sqft">Per Sq.ft Concession (₹ per Sqft Discount)</option>
                                <option value="cashback_wallet">Wallet Cashback (Credited into Wallet)</option>
                                <option value="gift_item">Free Gift Hamper (Gold Coin / Electronics)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" id="label_discount_value">Concession Value (₹)</label>
                            <input name="discount_value" id="modal_discount_value" type="number" step="0.01" class="form-control" value="21000" placeholder="Amount in ₹">
                            <div class="d-none" id="div_discount_percentage">
                                <label class="form-label fw-bold mt-2">Discount Percentage (%)</label>
                                <input name="discount_percentage" id="modal_discount_pct" type="number" step="0.01" min="0" max="100" class="form-control" value="5">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Minimum Plot Booking Value (₹)</label>
                            <input name="min_booking_amount" type="number" step="0.01" class="form-control" value="100000" placeholder="e.g. 100000">
                            <small class="text-muted">Minimum booking token or deal price required to apply this offer.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Max Redemptions Limit</label>
                            <input name="max_redemptions" type="number" class="form-control" value="100" placeholder="0 for Unlimited">
                            <small class="text-muted">Cap on total customer bookings using this promo code.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Valid Starting From</label>
                            <input name="valid_from" type="date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Valid Until (Expiry Date)</label>
                            <input name="valid_until" type="date" class="form-control" value="<?= date('Y-m-d', strtotime('+45 days')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Offer Description &amp; Customer Terms</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. Flat ₹21,000 instant festive concession on any plot booking token across all townships."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i>Publish Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDiscountFields() {
    var type = document.getElementById('modal_discount_type').value;
    var labelVal = document.getElementById('label_discount_value');
    var divPct = document.getElementById('div_discount_percentage');

    if (type === 'percentage') {
        labelVal.innerText = 'Discount Percentage (%)';
        divPct.classList.remove('d-none');
    } else if (type === 'per_sqft') {
        labelVal.innerText = 'Concession Per Sq.ft (₹ / sq.ft)';
        divPct.classList.add('d-none');
    } else {
        labelVal.innerText = 'Concession Value (₹)';
        divPct.classList.add('d-none');
    }
}
</script>
