<?php
$current_page = $current_page ?? 'plot-detail';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$promoQuery = !empty($_GET['promo']) ? '?promo=' . urlencode(trim((string)$_GET['promo'])) : '';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/browse"><?= __('browse_browse_plots') ?></a></li>
            <li class="breadcrumb-item active"><?= __('browse_plot') ?> <?= htmlspecialchars($plot['plot_number'] ?? '') ?></li>
        </ol>
    </nav>

    <div class="row g-4">

        <!-- Left: Plot Detail -->
        <div class="col-lg-8">

            <!-- Header Card -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="fw-bold mb-1"><?= __('browse_plot') ?> <?= htmlspecialchars($plot['plot_number'] ?? '') ?></h3>
                            <p class="text-muted mb-0">
                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($plot['colony_name'] ?? '') ?>
                                <?php if (!empty($plot['block'])): ?>
                                    &middot; <?= __('browse_block') ?> <?= htmlspecialchars($plot['block'] ?? '') ?>
                                <?php endif; ?>
                                &middot; <?= htmlspecialchars($plot['district_name'] ?? '') ?>, <?= htmlspecialchars($plot['state_name'] ?? '') ?>
                            </p>
                        </div>
                        <span class="badge bg-success fs-6"><?= __('browse_available') ?></span>
                    </div>

                    <!-- 360° Quick Actions Bar -->
                    <div class="d-flex flex-wrap gap-2 mt-3" style="border-top: 1px solid #e9ecef; padding-top: 1rem;">
                        <h6 class="fw-bold mb-0 me-3 align-self-center"><i class="fas fa-bolt me-1 text-warning"></i> Quick Actions:</h6>
                        <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book<?= $promoQuery ?>" class="btn btn-sm btn-primary" title="Book This Plot">
                            <i class="fas fa-check-circle me-1"></i> Book Now
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#plotEmiModal" title="Calculate EMI & Financing Plans">
                            <i class="fas fa-calculator me-1"></i> EMI Plans
                        </button>
                        <a href="#locationMapCard" class="btn btn-sm btn-outline-secondary" title="View Location Map">
                            <i class="fas fa-map-marker-alt me-1"></i> Map
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#plotShareModal" title="Share Plot Details">
                            <i class="fas fa-share-alt me-1"></i> Share
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnComparePlot" onclick="comparePlots(<?= $plot['id'] ?>)" title="Compare with Other Plots">
                            <i class="fas fa-balance-scale me-1"></i> <span id="btnComparePlotText">Compare</span>
                        </button>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_area') ?></small>
                                <strong class="fs-5"><?= number_format($plot['area_sqft']) ?></strong>
                                <small class="text-muted"> <?= __('sqft') ?></small>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_dimensions') ?></small>
                                <strong class="fs-5"><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></strong>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_price_sqft') ?></small>
                                <strong class="fs-5">₹<?= number_format($pricePerSqft) ?></strong>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_total_price') ?></small>
                                <strong class="fs-5 text-primary">₹<?= number_format($plot['total_price']) ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($plot['corner_plot']) || !empty($plot['park_facing'])): ?>
                    <div class="mb-2">
                        <?php if (!empty($plot['corner_plot'])): ?>
                            <span class="badge bg-warning-subtle text-warning me-1"><i class="fas fa-star me-1"></i><?= __('browse_corner_plot') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($plot['park_facing'])): ?>
                            <span class="badge bg-success-subtle text-success"><i class="fas fa-tree me-1"></i><?= __('browse_park_facing') ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Map Card -->
            <div class="aps-cp-card mb-4" id="locationMapCard">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-map-marked-alt me-2"></i><?= __('detail_location_map') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center p-4">
                        <div class="text-center text-muted">
                            <i class="fas fa-map fa-3x mb-2 opacity-25"></i>
                            <p class="mb-0 fw-semibold"><?= __('detail_plot_location_map') ?></p>
                            <p class="small text-muted mb-3">
                                <?= htmlspecialchars(($plot['colony_name'] ?? '') . (!empty($plot['district_name']) ? ', ' . $plot['district_name'] : '') . (!empty($plot['state_name']) ? ', ' . $plot['state_name'] : '')) ?>
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <?php if (!empty($plot['map_link'])): ?>
                                <a href="<?= htmlspecialchars($plot['map_link'] ?? '') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i><?= __('detail_open_google_maps') ?>
                                </a>
                                <?php else: ?>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(($plot['colony_name'] ?? '') . ' ' . ($plot['district_name'] ?? '') . ' ' . ($plot['state_name'] ?? '')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-map-marker-alt me-1"></i> Search on Google Maps
                                </a>
                                <?php endif; ?>
                                <a href="<?= $baseUrl ?>/plots/layout" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-th me-1"></i> Interactive Plot Map
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nearby Plots -->
            <?php if (!empty($nearbyPlots)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-th-large me-2"></i><?= __('detail_nearby_plots') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <?php foreach ($nearbyPlots as $np): ?>
                        <div class="col-sm-6 col-lg-3">
                            <a href="<?= $baseUrl ?>/plots/<?= $np['id'] ?>/detail" class="text-decoration-none">
                                <div class="bg-light rounded p-3 text-center h-100">
                                    <strong class="d-block"><?= __('browse_plot') ?> <?= htmlspecialchars($np['plot_number'] ?? '') ?></strong>
                                    <small class="text-muted"><?= number_format($np['area_sqft']) ?> <?= __('sqft') ?></small>
                                    <div class="text-primary fw-bold mt-1">₹<?= number_format($np['total_price']) ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Price Breakdown + CTA -->
        <div class="col-lg-4">

            <!-- Price Breakdown -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-receipt me-2"></i><?= __('detail_price_breakdown') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= __('detail_plot_price') ?></span>
                        <strong>₹<?= number_format($plot['total_price']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= __('detail_token_amount') ?></span>
                        <div class="text-end">
                            <strong>₹<?= number_format($tokenAmount) ?></strong>
                            <div><span class="badge bg-danger" style="font-size:0.68rem;">Non-Refundable / गैर-वापसी योग्य</span></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= __('detail_stamp_duty') ?></span>
                        <span class="text-muted">~₹<?= number_format($stampDuty) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><?= __('detail_balance_after_token') ?></span>
                        <strong>₹<?= number_format($plot['total_price'] - $tokenAmount) ?></strong>
                    </div>
                    <div class="alert alert-danger py-2 px-2 mt-3 mb-0 small text-start border-start border-3 border-danger" style="background-color: #fff8f8; font-size: 0.75rem; line-height: 1.4;">
                        <i class="fas fa-shield-alt text-danger me-1"></i>
                        <strong>Master Deed (धारा 2.1 व 2.9):</strong> टोकन राशि (₹<?= number_format($tokenAmount) ?>) पूर्णतः गैर-वापसी योग्य (वापस नहीं होगी) है।
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body text-center">
                    <h5 class="fw-bold mb-2"><?= __('detail_interested_plot') ?></h5>
                    <p class="text-muted small mb-3"><?= __('detail_book_token_desc') ?></p>
                    <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book<?= $promoQuery ?>" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="fas fa-check-circle me-2"></i><?= __('detail_book_this_plot') ?>
                    </a>
                    <a href="<?= $baseUrl ?>/plots/browse" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-arrow-left me-1"></i><?= __('detail_back_to_browse') ?>
                    </a>
                </div>
            </div>

            <!-- Colony Info -->
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i><?= sprintf(__('detail_about_colony'), htmlspecialchars($plot['colony_name'] ?? '')) ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <p class="small text-muted mb-2"><?= htmlspecialchars($plot['colony_description'] ?? __('detail_colony_default_desc')) ?></p>
                    <?php if (!empty($plot['district_name'])): ?>
                    <div class="small">
                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                        <?= htmlspecialchars($plot['district_name'] ?? '') ?>, <?= htmlspecialchars($plot['state_name'] ?? '') ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$totalPrice = (float)($plot['total_price'] ?? 0);
$tokenAmt = (float)($tokenAmount ?? 0);
$loanPrincipal = max(0.0, $totalPrice - $tokenAmt);

$calcEmiFn = function(float $principal, float $annualRate, int $months): int {
    if ($principal <= 0 || $months <= 0) return 0;
    $r = ($annualRate / 12) / 100;
    return (int)round(($principal * $r * pow(1 + $r, $months)) / (pow(1 + $r, $months) - 1));
};

$emi12 = $calcEmiFn($loanPrincipal, 9.5, 12);
$emi24 = $calcEmiFn($loanPrincipal, 9.5, 24);
$emi36 = $calcEmiFn($loanPrincipal, 10.0, 36);
$emi60 = $calcEmiFn($loanPrincipal, 10.5, 60);

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$shareUrl = $proto . '://' . $host . $baseUrl . '/plots/' . (int)$plot['id'] . '/detail' . $promoQuery;
$shareText = "Check out Plot #" . ($plot['plot_number'] ?? '') . " at " . ($plot['colony_name'] ?? '') . " (" . number_format((float)($plot['area_sqft'] ?? 0)) . " sqft, ₹" . number_format($totalPrice) . ") on APS Dream Home: " . $shareUrl;
$waShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($shareText);
$tgShareUrl = "https://t.me/share/url?url=" . urlencode($shareUrl) . "&text=" . urlencode("Plot #" . ($plot['plot_number'] ?? '') . " in " . ($plot['colony_name'] ?? '') . " - APS Dream Home");
$waAdvisorUrl = "https://api.whatsapp.com/send?phone=919235306634&text=" . urlencode("Hello APS Dream Home, I need assistance with EMI and financing options for Plot #" . ($plot['plot_number'] ?? '') . " in " . ($plot['colony_name'] ?? '') . " (Price: ₹" . number_format($totalPrice) . ").");
?>

<!-- ═══ Plot EMI & Financing Modal ═══ -->
<div class="modal fade" id="plotEmiModal" tabindex="-1" aria-labelledby="plotEmiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold" id="plotEmiModalLabel">
                        <i class="fas fa-calculator text-primary me-2"></i> Plot <?= htmlspecialchars($plot['plot_number'] ?? '') ?> &middot; EMI & Financing Schedule
                    </h5>
                    <small class="text-muted">
                        <?= htmlspecialchars($plot['colony_name'] ?? '') ?> &middot; <?= number_format((float)($plot['area_sqft'] ?? 0)) ?> sqft
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary Metrics -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="bg-light p-3 rounded text-center border">
                            <small class="text-muted d-block">Total Plot Value</small>
                            <strong class="fs-5 text-dark">₹<?= number_format($totalPrice) ?></strong>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="bg-light p-3 rounded text-center border">
                            <small class="text-muted d-block">Token (Non-Refundable)</small>
                            <strong class="fs-5 text-danger">₹<?= number_format($tokenAmt) ?></strong>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="bg-light p-3 rounded text-center border">
                            <small class="text-muted d-block">Est. Loan Principal</small>
                            <strong class="fs-5 text-primary" id="emi_disp_principal">₹<?= number_format($loanPrincipal) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Pre-Calculated Schedules -->
                <h6 class="fw-bold mb-2"><i class="fas fa-calendar-alt text-info me-1"></i> Standard Financing Plans:</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tenure</th>
                                <th>Interest Rate</th>
                                <th>Monthly EMI</th>
                                <th>Total Interest</th>
                                <th>Total Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1 Year</strong> (12 Mo)</td>
                                <td><span class="badge bg-light text-dark border">9.5% p.a.</span></td>
                                <td class="fw-bold text-primary">₹<?= number_format($emi12) ?>/mo</td>
                                <td>₹<?= number_format(max(0, ($emi12 * 12) - (int)$loanPrincipal)) ?></td>
                                <td>₹<?= number_format($emi12 * 12) ?></td>
                            </tr>
                            <tr class="table-info-subtle">
                                <td><strong>2 Years</strong> (24 Mo) <span class="badge bg-success ms-1">Popular</span></td>
                                <td><span class="badge bg-light text-dark border">9.5% p.a.</span></td>
                                <td class="fw-bold text-primary">₹<?= number_format($emi24) ?>/mo</td>
                                <td>₹<?= number_format(max(0, ($emi24 * 24) - (int)$loanPrincipal)) ?></td>
                                <td>₹<?= number_format($emi24 * 24) ?></td>
                            </tr>
                            <tr>
                                <td><strong>3 Years</strong> (36 Mo)</td>
                                <td><span class="badge bg-light text-dark border">10.0% p.a.</span></td>
                                <td class="fw-bold text-primary">₹<?= number_format($emi36) ?>/mo</td>
                                <td>₹<?= number_format(max(0, ($emi36 * 36) - (int)$loanPrincipal)) ?></td>
                                <td>₹<?= number_format($emi36 * 36) ?></td>
                            </tr>
                            <tr>
                                <td><strong>5 Years</strong> (60 Mo)</td>
                                <td><span class="badge bg-light text-dark border">10.5% p.a.</span></td>
                                <td class="fw-bold text-primary">₹<?= number_format($emi60) ?>/mo</td>
                                <td>₹<?= number_format(max(0, ($emi60 * 60) - (int)$loanPrincipal)) ?></td>
                                <td>₹<?= number_format($emi60 * 60) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Custom Simulator -->
                <div class="card bg-light border-0 p-3 mb-3">
                    <h6 class="fw-bold mb-3"><i class="fas fa-sliders-h text-primary me-1"></i> Custom Tenure Simulator:</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="plotEmiTenureSlider">
                                Loan Tenure: <span id="dispEmiTenure" class="text-primary fw-bold">3 Years (36 Months)</span>
                            </label>
                            <input type="range" class="form-range" id="plotEmiTenureSlider" min="1" max="10" step="1" value="3" oninput="updatePlotEmiCalc()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="plotEmiRateSlider">
                                Interest Rate: <span id="dispEmiRate" class="text-primary fw-bold">9.5% p.a.</span>
                            </label>
                            <input type="range" class="form-range" id="plotEmiRateSlider" min="8" max="15" step="0.5" value="9.5" oninput="updatePlotEmiCalc()">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top">
                        <div>
                            <small class="text-muted d-block">Estimated Monthly Installment:</small>
                            <span class="fs-4 fw-bold text-success" id="dynSimEmi">₹<?= number_format($emi36) ?></span> <span class="small text-muted">/month</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Total Amount with Interest:</small>
                            <span class="fw-bold text-dark" id="dynSimTotal">₹<?= number_format($emi36 * 36) ?></span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-danger py-2 px-3 small border-start border-3 border-danger mb-0" style="background:#fff8f8; font-size: 0.78rem;">
                    <i class="fas fa-shield-alt text-danger me-1"></i>
                    <strong>Master Deed Notice:</strong> टोकन राशि (₹<?= number_format($tokenAmt) ?>) पूर्णतः गैर-वापसी योग्य (non-refundable) है। बैंक फाइनेंस व ऋण की स्वीकृति बैंक के नियमों एवं ग्राहक की सिबिल स्कोर / पात्रता पर निर्भर करती है।
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <a href="<?= $waAdvisorUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success">
                    <i class="fab fa-whatsapp me-1"></i> Talk to Loan Expert
                </a>
                <div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book<?= $promoQuery ?>" class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Proceed to Book
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Plot Share Modal ═══ -->
<div class="modal fade" id="plotShareModal" tabindex="-1" aria-labelledby="plotShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="plotShareModalLabel">
                    <i class="fas fa-share-alt text-primary me-2"></i> Share Plot Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Mini Plot Overview -->
                <div class="p-3 rounded bg-light border mb-3 text-center">
                    <h6 class="fw-bold mb-1">Plot <?= htmlspecialchars($plot['plot_number'] ?? '') ?> &middot; <?= htmlspecialchars($plot['colony_name'] ?? '') ?></h6>
                    <span class="badge bg-primary fs-6 me-1">₹<?= number_format($totalPrice) ?></span>
                    <span class="badge bg-secondary"><?= number_format((float)($plot['area_sqft'] ?? 0)) ?> sqft</span>
                </div>

                <!-- 1-Click WhatsApp Share -->
                <a href="<?= $waShareUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success w-100 mb-2 py-2 fw-semibold">
                    <i class="fab fa-whatsapp me-2 fs-5"></i> Share on WhatsApp
                </a>

                <!-- 1-Click Telegram Share -->
                <a href="<?= $tgShareUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info text-white w-100 mb-2 py-2 fw-semibold">
                    <i class="fab fa-telegram-plane me-2 fs-5"></i> Share on Telegram
                </a>

                <!-- Native Web Share API -->
                <button type="button" class="btn btn-dark w-100 mb-3 py-2 fw-semibold" id="btnPlotNativeShare" onclick="sharePlotNative()">
                    <i class="fas fa-share-nodes me-2"></i> Share via Apps / More
                </button>

                <!-- Copy Link Input -->
                <label class="form-label small fw-semibold text-muted mb-1" for="plotShareLinkInput">Direct Plot Link:</label>
                <div class="input-group mb-2">
                    <input type="text" id="plotShareLinkInput" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($shareUrl) ?>" readonly>
                    <button class="btn btn-sm btn-outline-primary" type="button" id="btnCopyShareLink" onclick="copyPlotShareLink()">
                        <i class="fas fa-copy me-1"></i> Copy
                    </button>
                </div>
                <div id="shareLinkFeedback" class="small text-success fw-bold d-none">
                    <i class="fas fa-check-circle me-1"></i> Link copied to clipboard!
                </div>

                <!-- QR Code for On-Site Scanning -->
                <div class="text-center mt-3 pt-3 border-top">
                    <small class="text-muted d-block mb-2"><i class="fas fa-qrcode me-1"></i> Scan QR Code with Phone:</small>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($shareUrl) ?>" alt="Plot QR Code" class="img-fluid rounded border p-1" style="max-width: 140px;" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Compare Notification Container -->
<div id="plotCompareToastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>

<script>
const PLOT_LOAN_PRINCIPAL = <?= (float)$loanPrincipal ?>;
const PLOT_SHARE_URL = <?= json_encode($shareUrl) ?>;
const PLOT_SHARE_TITLE = <?= json_encode("Plot #" . ($plot['plot_number'] ?? '') . " - " . ($plot['colony_name'] ?? '')) ?>;
const PLOT_SHARE_TEXT = <?= json_encode($shareText) ?>;

function updatePlotEmiCalc() {
    const tenureYears = parseInt(document.getElementById('plotEmiTenureSlider').value) || 3;
    const rateAnnual = parseFloat(document.getElementById('plotEmiRateSlider').value) || 9.5;
    
    document.getElementById('dispEmiTenure').textContent = tenureYears + ' Years (' + (tenureYears * 12) + ' Months)';
    document.getElementById('dispEmiRate').textContent = rateAnnual.toFixed(1) + '% p.a.';

    const months = tenureYears * 12;
    if (PLOT_LOAN_PRINCIPAL <= 0 || months <= 0) {
        document.getElementById('dynSimEmi').textContent = '₹0';
        document.getElementById('dynSimTotal').textContent = '₹0';
        return;
    }

    const r = (rateAnnual / 12) / 100;
    const emi = Math.round((PLOT_LOAN_PRINCIPAL * r * Math.pow(1 + r, months)) / (Math.pow(1 + r, months) - 1));
    const total = emi * months;

    document.getElementById('dynSimEmi').textContent = '₹' + emi.toLocaleString('en-IN');
    document.getElementById('dynSimTotal').textContent = '₹' + total.toLocaleString('en-IN');
}

function copyPlotShareLink() {
    const input = document.getElementById('plotShareLinkInput');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const feedback = document.getElementById('shareLinkFeedback');
        const btn = document.getElementById('btnCopyShareLink');
        if (feedback) feedback.classList.remove('d-none');
        if (btn) btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        setTimeout(() => {
            if (feedback) feedback.classList.add('d-none');
            if (btn) btn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy';
        }, 3000);
    }).catch(() => {
        alert('Could not copy link. Please manually copy the URL from the box.');
    });
}

function sharePlotNative() {
    if (navigator.share) {
        navigator.share({
            title: PLOT_SHARE_TITLE,
            text: PLOT_SHARE_TEXT,
            url: PLOT_SHARE_URL
        }).catch(() => {});
    } else {
        copyPlotShareLink();
        alert('Web Share API is not supported on this browser. Link has been copied to your clipboard!');
    }
}

function comparePlots(plotId) {
    const btn = document.getElementById('btnComparePlot');
    const btnText = document.getElementById('btnComparePlotText');

    if (btn && btn.classList.contains('btn-success')) {
        window.location.href = '<?= $baseUrl ?>/compare';
        return;
    }

    if (btnText) btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';

    fetch('<?= $baseUrl ?>/compare/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'plot_id=' + encodeURIComponent(plotId)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (btn) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success', 'text-white');
            }
            if (btnText) btnText.innerHTML = '<i class="fas fa-check me-1"></i> Added (View)';
            showCompareNotification(data.message || 'Plot added to comparison!', '<?= $baseUrl ?>/compare');
        } else {
            alert(data.message || 'Could not add plot to comparison.');
            if (btnText) btnText.textContent = 'Compare';
        }
    })
    .catch(() => {
        alert('Network error. Please try again.');
        if (btnText) btnText.textContent = 'Compare';
    });
}

function showCompareNotification(message, compareUrl) {
    const container = document.getElementById('plotCompareToastContainer');
    if (!container) return;
    const toastHtml = `
        <div class="toast align-items-center text-bg-primary border-0 show shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-1"></i> ${message}
                    <a href="${compareUrl}" class="btn btn-sm btn-light ms-2 fw-semibold text-primary">Compare Now &rarr;</a>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="this.closest('.toast').remove()"></button>
            </div>
        </div>
    `;
    container.innerHTML = toastHtml;
    setTimeout(() => {
        const t = container.querySelector('.toast');
        if (t) t.remove();
    }, 6000);
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const openParam = urlParams.get('open');
    if (openParam === 'emi') {
        const emiModalEl = document.getElementById('plotEmiModal');
        if (emiModalEl && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(emiModalEl).show();
        }
    } else if (openParam === 'share') {
        const shareModalEl = document.getElementById('plotShareModal');
        if (shareModalEl && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(shareModalEl).show();
        }
    }

    if (!navigator.share) {
        const btnNative = document.getElementById('btnPlotNativeShare');
        if (btnNative) btnNative.style.display = 'none';
    }
});
</script>
