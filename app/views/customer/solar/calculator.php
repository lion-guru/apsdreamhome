<?php
/**
 * Customer PM Surya Ghar Green Solar & Subsidy Calculator View
 */
$page_title = $page_title ?? 'PM Surya Ghar Solar Calculator & Subsidy Desk';
$bookings = $bookings ?? [];
$defaultFeasibility = $defaultFeasibility ?? [];
$is_logged_in = $is_logged_in ?? false;
?>

<div class="container py-4">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark text-uppercase px-3 py-2 mb-3 fw-bold">
                        <i class="fas fa-sun me-1"></i> PM Surya Ghar: Muft Bijli Yojana
                    </span>
                    <h2 class="fw-bold mb-3">Green Solar Township Feasibility & Subsidy Engine</h2>
                    <p class="lead opacity-90 mb-4" style="font-size: 1.05rem;">
                        Transform your APS Dream Home plot or villa into a zero-electricity bill home. Get up to <strong>₹78,000 Central Subsidy</strong> plus <strong>₹30,000 UP State Subsidy</strong> with seamless PVVNL net-metering grid connection.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#calculatorSection" class="btn btn-warning text-dark fw-bold px-4 py-2">
                            <i class="fas fa-calculator me-1"></i> Calculate My Subsidy
                        </a>
                        <?php if ($is_logged_in): ?>
                            <a href="/customer/solar-desk" class="btn btn-outline-light px-4 py-2">
                                <i class="fas fa-list-check me-1"></i> Track My Solar Requests
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block">
                    <i class="fas fa-solar-panel fa-8x text-warning opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculator Section -->
    <div class="row g-4 mb-5" id="calculatorSection">
        <!-- Interactive Controls -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">
                    <i class="fas fa-sliders-h text-primary me-2"></i>Rooftop Solar Feasibility Estimator
                </h4>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">
                        Average Monthly Electricity Bill: <span id="billDisplay" class="fw-bold text-primary fs-5">₹3,500</span>
                    </label>
                    <input type="range" class="form-range" id="billRange" min="1000" max="15000" step="500" value="3500" oninput="updateCalculator()">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>₹1,000</span>
                        <span>₹5,000</span>
                        <span>₹10,000</span>
                        <span>₹15,000+</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">
                        Plot / Estimated Rooftop Footprint: <span id="areaDisplay" class="fw-bold text-primary fs-5">1,000 sq.ft</span>
                    </label>
                    <input type="range" class="form-range" id="areaRange" min="400" max="4000" step="100" value="1000" oninput="updateCalculator()">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>400 sq.ft</span>
                        <span>1,000 sq.ft</span>
                        <span>2,500 sq.ft</span>
                        <span>4,000 sq.ft</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">System Capacity Selection</label>
                    <div class="row g-2">
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="kwChoice" id="kw1" value="1" onchange="updateCalculator()">
                            <label class="btn btn-outline-primary w-100 py-2" for="kw1"><strong>1 kW</strong><br><small class="text-muted">Compact</small></label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="kwChoice" id="kw2" value="2" onchange="updateCalculator()">
                            <label class="btn btn-outline-primary w-100 py-2" for="kw2"><strong>2 kW</strong><br><small class="text-muted">Standard</small></label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="kwChoice" id="kw3" value="3" checked onchange="updateCalculator()">
                            <label class="btn btn-outline-primary w-100 py-2" for="kw3"><strong>3 kW</strong><br><small class="text-muted">Most Popular</small></label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="kwChoice" id="kw5" value="5" onchange="updateCalculator()">
                            <label class="btn btn-outline-primary w-100 py-2" for="kw5"><strong>5 kW</strong><br><small class="text-muted">High Power</small></label>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 text-secondary small">
                    <i class="fas fa-info-circle text-primary me-1"></i>
                    <strong>Feasibility Criteria:</strong> Rooftop area requirement is ~100 sq.ft per kW. UP solar insolation provides approximately 120 units per kW/month.
                </div>
            </div>
        </div>

        <!-- Live Financial Summary Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold text-dark mb-0">Financial & Subsidy Breakdown</h4>
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-1 fs-6" id="kwBadge">3.0 kW System</span>
                </div>

                <div class="list-group list-group-flush mb-4">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-secondary">Estimated System Benchmark Cost</span>
                        <strong class="text-dark" id="txtCost">₹1,80,000</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-success bg-opacity-10 rounded px-2">
                        <span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>PM Surya Ghar Central Subsidy</span>
                        <strong class="text-success" id="txtCentralSub">- ₹78,000</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-success bg-opacity-10 rounded px-2 mt-1">
                        <span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>UP State Govt Solar Subsidy</span>
                        <strong class="text-success" id="txtStateSub">- ₹30,000</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-top mt-2">
                        <span class="fw-bold text-dark fs-6">Net Customer Payable Investment</span>
                        <h4 class="fw-bold text-primary mb-0" id="txtNetPayable">₹72,000</h4>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-4">
                        <div class="p-2 border rounded-3 text-center bg-light">
                            <span class="text-muted d-block small">Generation</span>
                            <strong class="text-dark" id="txtMonthlyGen">360 Units/mo</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 text-center bg-light">
                            <span class="text-muted d-block small">Annual Savings</span>
                            <strong class="text-success" id="txtAnnualSavings">₹30,240/yr</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded-3 text-center bg-light">
                            <span class="text-muted d-block small">Payback Time</span>
                            <strong class="text-primary" id="txtPayback">2.4 Years</strong>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                    <i class="fas fa-leaf fa-2x me-3 text-success"></i>
                    <div>
                        <strong class="d-block text-dark">25-Year Clean Energy Return:</strong>
                        <span class="small text-muted" id="txt25YrSavings">Estimated net savings of ₹6,84,000 after system payback!</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Form -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <h4 class="fw-bold text-dark mb-2">
            <i class="fas fa-file-contract text-warning me-2"></i>Apply for Free Feasibility & Subsidy Sanction
        </h4>
        <p class="text-muted mb-4">Our dedicated green energy desk will inspect your rooftop, handle UP-NEDA subsidy paperwork, and facilitate PVVNL net-metering approval.</p>

        <?php if (!$is_logged_in): ?>
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user-lock me-2"></i>
                    Please log in to link this solar request directly with your APS Dream Home plot booking.
                </div>
                <a href="/login?redirect=/customer/solar-calculator" class="btn btn-primary btn-sm">Log In Now</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="/customer/solar/apply">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="system_capacity_kw" id="formKw" value="3.0">
            <input type="hidden" name="monthly_bill" id="formBill" value="3500">
            <input type="hidden" name="plot_sqft" id="formArea" value="1000">

            <div class="row g-3">
                <?php if ($is_logged_in && !empty($bookings)): ?>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Select Your Plot Booking</label>
                        <select name="booking_id" class="form-select">
                            <option value="">-- Associate with Plot Booking (Optional) --</option>
                            <?php foreach ($bookings as $b): ?>
                                <option value="<?= $b['id'] ?>">
                                    <?= htmlspecialchars($b['colony_name']) ?> - Plot #<?= htmlspecialchars($b['plot_number']) ?> (Booking: <?= htmlspecialchars($b['booking_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Electricity Consumer Account (CA / Connection No.)</label>
                    <input type="text" name="consumer_number" class="form-control" placeholder="10-digit PVVNL Consumer Number">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Electricity DISCOM Provider</label>
                    <input type="text" name="discom_provider" class="form-control" value="Purvanchal Vidyut Vitaran Nigam Ltd (PVVNL)" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Special Instructions / Site Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Roof is open concrete slab with stair access.">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-check-circle me-2"></i>Submit PM Surya Ghar Application
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function updateCalculator() {
    const bill = parseFloat(document.getElementById('billRange').value);
    const area = parseFloat(document.getElementById('areaRange').value);
    const selectedRadio = document.querySelector('input[name="kwChoice"]:checked');
    const kw = selectedRadio ? parseFloat(selectedRadio.value) : 3.0;

    document.getElementById('billDisplay').innerText = '₹' + bill.toLocaleString();
    document.getElementById('areaDisplay').innerText = area.toLocaleString() + ' sq.ft';
    document.getElementById('kwBadge').innerText = kw.toFixed(1) + ' kW System';

    // Hidden form inputs
    document.getElementById('formBill').value = bill;
    document.getElementById('formArea').value = area;
    document.getElementById('formKw').value = kw;

    // Subsidy Math
    const cost = kw * 60000;
    let centralSub = 0;
    if (kw <= 1.0) {
        centralSub = 33000 * kw;
    } else if (kw <= 2.0) {
        centralSub = 33000 + ((kw - 1.0) * 27000);
    } else {
        centralSub = 78000;
    }

    const stateSub = (kw <= 1.0) ? 15000 : 30000;
    const net = Math.max(0, cost - (centralSub + stateSub));

    const monthlyUnits = Math.round(kw * 120);
    const annualSavings = Math.round(monthlyUnits * 7.00 * 12);
    const payback = annualSavings > 0 ? (net / annualSavings).toFixed(1) : '0';
    const total25Yr = Math.round((annualSavings * 25) - net);

    // Update DOM
    document.getElementById('txtCost').innerText = '₹' + cost.toLocaleString();
    document.getElementById('txtCentralSub').innerText = '- ₹' + centralSub.toLocaleString();
    document.getElementById('txtStateSub').innerText = '- ₹' + stateSub.toLocaleString();
    document.getElementById('txtNetPayable').innerText = '₹' + net.toLocaleString();
    document.getElementById('txtMonthlyGen').innerText = monthlyUnits + ' Units/mo';
    document.getElementById('txtAnnualSavings').innerText = '₹' + annualSavings.toLocaleString() + '/yr';
    document.getElementById('txtPayback').innerText = payback + ' Years';
    document.getElementById('txt25YrSavings').innerText = 'Estimated net savings of ₹' + total25Yr.toLocaleString() + ' over 25 years lifecycle!';
}
</script>
