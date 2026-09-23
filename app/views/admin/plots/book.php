<?php
$plot = $plot ?? [];
$customers = $customers ?? [];
$paymentPlans = $paymentPlans ?? ['Full Payment', 'Installment (12 months)', 'Installment (24 months)', 'Installment (36 months)', 'Construction Linked'];
$totalPrice = floatval($plot['total_price'] ?? 0);
$negotiatedPrice = floatval($plot['negotiated_price'] ?? $totalPrice);
$tokenAmount = 51000;
$twentyFivePercent = $negotiatedPrice * 0.25;
$balanceDue15Days = max(0, $twentyFivePercent - $tokenAmount);
$remainingSeventyFive = max(0, $negotiatedPrice - $twentyFivePercent);
?>

<div class="container-fluid py-4">
    <!-- Header with Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/plots" class="text-decoration-none">Plots Inventory</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/plots/<?= (int)($plot['id'] ?? 0) ?>" class="text-decoration-none">Plot #<?= htmlspecialchars((string)($plot['plot_number'] ?? 'N/A')) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Booking Engine</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-file-signature text-primary me-2"></i>Executive Plot Booking Engine
            </h3>
            <small class="text-muted">Master Deed (Tripartite V8) Standardized Booking &amp; Statutory Consideration Workflow</small>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="<?= BASE_URL ?>/admin/plots/<?= (int)($plot['id'] ?? 0) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Plot
            </a>
            <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-outline-primary">
                <i class="fas fa-th me-1"></i> Inventory Grid
            </a>
        </div>
    </div>

    <!-- 5-Stage Systematic Stepper Bar -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <div class="row text-center g-2">
                <div class="col">
                    <div class="p-2 rounded bg-white border border-primary shadow-xs">
                        <span class="badge bg-primary rounded-pill mb-1">Step 1</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-cube me-1 text-primary"></i>Plot &amp; Demarcation</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border">
                        <span class="badge bg-secondary rounded-pill mb-1">Step 2</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-user-check me-1 text-secondary"></i>Customer KYC</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border">
                        <span class="badge bg-secondary rounded-pill mb-1">Step 3</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-calculator me-1 text-secondary"></i>Price &amp; 15-Day 25%</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border">
                        <span class="badge bg-secondary rounded-pill mb-1">Step 4</span>
                        <div class="fw-bold small text-dark"><i class="fas fa-calendar-alt me-1 text-secondary"></i>EMI Plan (36M)</div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 rounded bg-white border border-danger">
                        <span class="badge bg-danger rounded-pill mb-1">Step 5</span>
                        <div class="fw-bold small text-danger"><i class="fas fa-gavel me-1 text-danger"></i>Master Deed Consent</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/plots/<?= (int)($plot['id'] ?? 0) ?>/book" id="adminPlotBookingForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <div class="row g-4">
            <!-- Left Column: Plot Info & Financial Calculator -->
            <div class="col-lg-5">
                <!-- Plot Demarcation Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-map-marked-alt text-warning me-2"></i>Plot Demarcation &amp; Specs</h6>
                        <span class="badge bg-<?= ($plot['status'] ?? '') === 'available' ? 'success' : 'warning text-dark' ?>">
                            <?= strtoupper(htmlspecialchars((string)($plot['status'] ?? 'AVAILABLE'))) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <tr>
                                    <th class="text-muted" style="width: 40%;">Plot Number</th>
                                    <td><strong class="fs-5 text-primary"><?= htmlspecialchars((string)($plot['plot_number'] ?? 'N/A')) ?></strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Colony / Township</th>
                                    <td><strong><?= htmlspecialchars((string)($plot['colony_name'] ?? 'N/A')) ?></strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Block &amp; Sector</th>
                                    <td><?= htmlspecialchars((string)($plot['block'] ?? '')) ?> <?= !empty($plot['sector']) ? '/ Sector ' . htmlspecialchars((string)($plot['sector'] ?? '')) : '' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Type &amp; Facing</th>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= ucfirst(htmlspecialchars((string)($plot['plot_type'] ?? 'residential'))) ?></span>
                                        <span class="badge bg-light text-dark border ms-1"><i class="fas fa-compass text-info me-1"></i><?= ucfirst(htmlspecialchars((string)($plot['facing'] ?? 'N/A'))) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Dimensions</th>
                                    <td><?= !empty($plot['dimension_label']) ? htmlspecialchars((string)($plot['dimension_label'] ?? '')) : number_format((float)($plot['width_ft'] ?? 0)) . ' × ' . number_format((float)($plot['length_ft'] ?? 0)) . ' ft' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Super / Carpet Area</th>
                                    <td><strong><?= number_format((float)($plot['area_sqft'] ?? 0)) ?></strong> sq.ft.</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Standard Rate</th>
                                    <td>₹<?= number_format((float)($plot['price_per_sqft'] ?? 0), 2) ?> / sq.ft.</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Total List Price</th>
                                    <td><span class="fs-6 fw-bold text-dark">₹<?= number_format((float)$totalPrice) ?></span></td>
                                </tr>
                            </table>
                        </div>

                        <!-- 4 Pillars Demarcation Statutory Badge -->
                        <div class="p-3 bg-light border border-info rounded-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-monument fa-2x text-info me-3 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">Master Deed Demarcation Covenant</h6>
                                    <p class="small text-muted mb-0">
                                        As per Section 2.15 of the Tripartite Deed, the boundary of Plot #<strong><?= htmlspecialchars((string)($plot['plot_number'] ?? '')) ?></strong> shall be physically demarcated with <strong>4 concrete corner pillars</strong> at company expense prior to registry.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($plot['features'])): ?>
                            <div class="mt-3">
                                <label class="small fw-bold text-muted text-uppercase">Amenities &amp; Features</label>
                                <p class="small text-secondary mb-0 bg-white p-2 border rounded"><?= nl2br(htmlspecialchars((string)($plot['features'] ?? ''))) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Live Financial Summary Card -->
                <div class="card shadow-sm border-0 border-top border-3 border-success mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calculator text-success me-2"></i>Live Payment Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Agreed Deal Value:</span>
                            <span class="fw-bold fs-6" id="summaryDealPrice">₹<?= number_format((float)$negotiatedPrice) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-danger bg-opacity-10 rounded">
                            <div>
                                <span class="fw-bold text-danger">Initial Token Due Now:</span><br>
                                <small class="text-danger">Non-Refundable / गैर-वापसी योग्य</small>
                            </div>
                            <span class="fw-bold text-danger fs-5" id="summaryTokenPrice">₹51,000</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-warning bg-opacity-10 rounded">
                            <div>
                                <span class="fw-bold text-dark">Mandatory 25% (in 15 Days):</span><br>
                                <small class="text-muted">Total 25% Less Token</small>
                            </div>
                            <span class="fw-bold text-dark" id="summaryBalance15Days">₹<?= number_format((float)$balanceDue15Days) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="text-muted">Remaining Balance (75%):</span>
                            <span class="fw-bold text-primary" id="summaryRemaining75">₹<?= number_format((float)$remainingSeventyFive) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Booking Configuration Form -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Customer &amp; Commercial Terms</h6>
                        <span class="badge bg-light text-primary">Official Booking Engine</span>
                    </div>
                    <div class="card-body p-4">

                        <!-- Section A: Customer Assignment -->
                        <h6 class="text-primary fw-bold text-uppercase border-bottom pb-2 mb-3" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                            <i class="fas fa-user-circle me-1"></i> Section A: Customer Selection &amp; KYC
                        </h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Registered Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select form-select-lg" required>
                                <option value="">-- Choose Registered Customer --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= (int)($c['id'] ?? 0) ?>">
                                        <?= htmlspecialchars((string)($c['name'] ?? '')) ?> &mdash; [<?= htmlspecialchars((string)($c['phone'] ?? '')) ?> | <?= htmlspecialchars((string)($c['email'] ?? '')) ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted">Search customer by name, mobile, or email address.</small>
                                <a href="<?= BASE_URL ?>/admin/users/create" target="_blank" class="small fw-bold text-decoration-none">
                                    <i class="fas fa-user-plus me-1"></i>+ Register New Customer
                                </a>
                            </div>
                        </div>

                        <!-- Section B: Consideration & Token Advance -->
                        <h6 class="text-primary fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                            <i class="fas fa-rupee-sign me-1"></i> Section B: Agreed Commercials &amp; Statutory Token
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Negotiated / Final Deal Price (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                    <input type="number" name="negotiated_price" id="negotiated_price" class="form-control fw-bold fs-6" 
                                           min="1000" step="1" value="<?= (float)$negotiatedPrice ?>" required oninput="recalculateFinancials()">
                                </div>
                                <small class="text-muted">Base list price: ₹<?= number_format((float)$totalPrice) ?></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Token Booking Consideration (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white fw-bold">₹</span>
                                    <input type="number" name="token_amount" id="token_amount" class="form-control fw-bold fs-6 text-danger" 
                                           min="51000" step="1" value="51000" required oninput="recalculateFinancials()">
                                </div>
                                <small class="text-danger fw-bold"><i class="fas fa-lock me-1"></i>Non-Refundable Statutory Standard (₹51,000)</small>
                            </div>
                        </div>

                        <!-- Section C: Payment Schedule & Tenure -->
                        <h6 class="text-primary fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                            <i class="fas fa-calendar-check me-1"></i> Section C: Payment Schedule &amp; Tenure Plan
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Plan <span class="text-danger">*</span></label>
                                <select name="payment_plan" id="payment_plan" class="form-select" required onchange="recalculateFinancials()">
                                    <?php foreach ($paymentPlans as $pp): ?>
                                        <option value="<?= htmlspecialchars((string)$pp) ?>" <?= str_contains($pp, '36') ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$pp) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Simulated Monthly EMI (Approx)</label>
                                <div class="form-control bg-light fw-bold text-primary" id="simulatedEmiBox">
                                    ₹<?= number_format($remainingSeventyFive / 36, 2) ?> / month
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Booking</label>
                                <input type="date" name="booking_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expected Registry / Possession</label>
                                <input type="date" name="possession_date" class="form-control" value="<?= date('Y-m-d', strtotime('+36 months')) ?>">
                            </div>
                        </div>

                        <!-- Section D: Statutory Master Deed Covenants -->
                        <h6 class="text-danger fw-bold text-uppercase border-bottom pb-2 mb-3 mt-4" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                            <i class="fas fa-balance-scale me-1"></i> Section D: Statutory Master Deed (V8) Legal Compliance
                        </h6>
                        <div class="p-3 rounded-3 border border-danger mb-3" style="background-color: #fff9f9;">
                            <div class="form-check mb-2">
                                <input class="form-check-input border-danger" type="checkbox" id="consentNonRefundable" required checked>
                                <label class="form-check-label fw-bold text-danger" for="consentNonRefundable">
                                    मास्टर डीड (धारा 2.1 व 2.9) वैधानिक स्वीकृति: टोकन राशि ₹51,000 पूर्णतः गैर-वापसी योग्य (100% Non-Refundable) है।
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input border-danger" type="checkbox" id="consent15Days" required checked>
                                <label class="form-check-label small text-dark" for="consent15Days">
                                    बुकिंग तिथि से <strong>15 कैलेंडर दिवसों के भीतर कुल मूल्य का 25%</strong> जमा करना अनिवार्य है, अन्यथा टोकन राशि बिना किसी पूर्व सूचना के जब्त (Forfeited) कर ली जाएगी।
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input border-danger" type="checkbox" id="consentNoCash" required checked>
                                <label class="form-check-label small text-dark" for="consentNoCash">
                                    कंपनी की अधिकृत कॉर्पोरेट बैंकिंग प्रणाली के अतिरिक्त किसी एसोसिएट/एजेंट के निजी खाते, नकद अथवा व्यक्तिगत UPI पर भुगतान पूर्णतः प्रतिबंधित है।
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Executive Remarks &amp; Special Conditions</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Record any special approvals, associate codes, or client requirements..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="<?= BASE_URL ?>/admin/plots/<?= (int)($plot['id'] ?? 0) ?>" class="btn btn-outline-secondary px-3">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                                <i class="fas fa-check-double me-2"></i> Execute &amp; Confirm Booking
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function recalculateFinancials() {
    const dealPrice = parseFloat(document.getElementById('negotiated_price').value) || 0;
    const tokenAmount = parseFloat(document.getElementById('token_amount').value) || 51000;
    const plan = document.getElementById('payment_plan').value;

    const twentyFivePct = dealPrice * 0.25;
    const bal15Days = Math.max(0, twentyFivePct - tokenAmount);
    const rem75 = Math.max(0, dealPrice - twentyFivePct);

    document.getElementById('summaryDealPrice').innerText = '₹' + dealPrice.toLocaleString('en-IN');
    document.getElementById('summaryTokenPrice').innerText = '₹' + tokenAmount.toLocaleString('en-IN');
    document.getElementById('summaryBalance15Days').innerText = '₹' + bal15Days.toLocaleString('en-IN', {maximumFractionDigits: 0});
    document.getElementById('summaryRemaining75').innerText = '₹' + rem75.toLocaleString('en-IN', {maximumFractionDigits: 0});

    let months = 36;
    if (plan.includes('12')) months = 12;
    else if (plan.includes('24')) months = 24;
    else if (plan.includes('Full')) months = 1;

    let emiBox = document.getElementById('simulatedEmiBox');
    if (plan.includes('Full')) {
        emiBox.innerText = 'Lump Sum (Single Payment)';
    } else {
        const emi = rem75 / months;
        emiBox.innerText = '₹' + emi.toLocaleString('en-IN', {maximumFractionDigits: 2}) + ' / month (' + months + ' Mo)';
    }
}
</script>
