<?php
/**
 * MLM Payout Simulator — Interactive Commission Calculator
 *
 * Simulates Track A (slab differential), Track B (performance rollup),
 * Track C (milestone escrow), royalty pool (2%), and company retention.
 *
 * Uses: aps-cp-card / aps-cp-stat design system.
 */
$rank_slabs = $rank_slabs ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $csrf_token ?? '';
?>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-calculator me-2"></i>Commission Payout Simulator</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
    <div class="aps-cp-card-body">
        <p class="text-muted mb-3">
            Simulate the full commission breakdown for any sale amount. Tracks A/B/C share a 20% global cap.
            The 2% royalty pool contribution is tracked separately.
        </p>

        <form id="simForm" class="row g-3 align-items-end mb-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Sale Amount (&#8377;)</label>
                <input type="number" id="saleAmount" name="sale_amount" class="form-control form-control-lg"
                       placeholder="e.g. 500000" min="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Seller Rank</label>
                <select id="rankSlug" name="rank_slug" class="form-select form-select-lg">
                    <?php foreach ($rank_slabs as $slug => $slab): ?>
                        <option value="<?= htmlspecialchars($slug ?? '') ?>">
                            <?= htmlspecialchars($slab['rank_name'] ?? ucfirst($slug)) ?>
                            (<?= (float)($slab['commission_rate'] ?? 0) ?>%)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-lg w-100" id="simBtn">
                    <i class="fas fa-play me-1"></i>Simulate
                </button>
            </div>
        </form>

        <div id="simLoading" class="text-center py-4" class="style-2248">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Calculating...</p>
        </div>

        <div id="simError" class="alert alert-danger" class="style-2248"></div>

        <div id="simResults" class="style-2248">
            <!-- Summary cards -->
            <div class="row g-3 mb-4" id="summaryCards"></div>

            <!-- Track A breakdown -->
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header bg-primary bg-opacity-10">
                    <h6 class="m-0"><i class="fas fa-layer-group me-2 text-primary"></i>Track A — Slab Differential (15% cap)</h6>
                </div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0" id="trackATable">
                        <thead><tr><th>Beneficiary</th><th>Rate</th><th class="text-end">Amount</th></tr></thead>
                        <tbody></tbody>
                    </table></div>
                </div>
            </div>

            <!-- Track B breakdown -->
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header bg-success bg-opacity-10">
                    <h6 class="m-0"><i class="fas fa-chart-line me-2 text-success"></i>Track B — Performance Rollup (3% cap)</h6>
                </div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0" id="trackBTable">
                        <thead><tr><th>Beneficiary</th><th>Rate</th><th class="text-end">Amount</th></tr></thead>
                        <tbody></tbody>
                    </table></div>
                </div>
            </div>

            <!-- Track C breakdown -->
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header bg-warning bg-opacity-10">
                    <h6 class="m-0"><i class="fas fa-piggy-bank me-2 text-warning"></i>Track C — Milestone Escrow (2% cap)</h6>
                </div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0" id="trackCTable">
                        <thead><tr><th>Description</th><th>Rate</th><th class="text-end">Amount</th></tr></thead>
                        <tbody></tbody>
                    </table></div>
                </div>
            </div>

            <!-- Company Retention -->
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header bg-info bg-opacity-10">
                    <h6 class="m-0"><i class="fas fa-building me-2 text-info"></i>Company Retention</h6>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3" id="retentionCards"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('simForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var amount = parseFloat(document.getElementById('saleAmount').value);
    var rank = document.getElementById('rankSlug').value;
    if (!amount || amount <= 0) return;

    var btn = document.getElementById('simBtn');
    var loading = document.getElementById('simLoading');
    var error = document.getElementById('simError');
    var results = document.getElementById('simResults');

    btn.disabled = true;
    loading.style.display = 'block';
    error.style.display = 'none';
    results.style.display = 'none';

    var fd = new FormData();
    fd.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    fd.append('sale_amount', amount);
    fd.append('rank_slug', rank);

    fetch('<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payout-simulator/simulate', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        loading.style.display = 'none';
        btn.disabled = false;
        if (!d.success) {
            error.textContent = d.error || 'Simulation failed';
            error.style.display = 'block';
            return;
        }
        renderResults(d);
        results.style.display = 'block';
    })
    .catch(function(err) {
        loading.style.display = 'none';
        btn.disabled = false;
        error.textContent = 'Network error: ' + err.message;
        error.style.display = 'block';
    });
});

function fmt(n) { return '&#8377;' + Number(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
function pct(n) { return Number(n).toFixed(2) + '%'; }

function renderResults(d) {
    // Summary cards
    var sc = document.getElementById('summaryCards');
    sc.innerHTML =
        card('Sale Amount', fmt(d.sale_amount), 'primary') +
        card('Total Distributed', fmt(d.total_distributed), 'success') +
        card('Royalty Pool (2%)', fmt(d.royalty_contribution), 'warning') +
        card('Company Retains', fmt(d.company_retains), 'info') +
        card('Overhead %', pct(d.overhead_pct), 'danger') +
        card('Company %', pct(d.company_retains_pct), 'secondary');

    // Track A
    var at = document.getElementById('trackATable').querySelector('tbody');
    at.innerHTML = '';
    (d.track_a_entries || []).forEach(function(e) {
        at.innerHTML += '<tr><td>' + esc(e.label) + '</td><td>' + pct(e.rate) + '</td><td class="text-end fw-bold">' + fmt(e.amount) + '</td></tr>';
    });
    at.innerHTML += '<tr class="table-primary fw-bold"><td>Track A Total</td><td></td><td class="text-end">' + fmt(d.track_a_total) + '</td></tr>';

    // Track B
    var bt = document.getElementById('trackBTable').querySelector('tbody');
    bt.innerHTML = '';
    (d.track_b_entries || []).forEach(function(e) {
        bt.innerHTML += '<tr><td>' + esc(e.label) + '</td><td>' + pct(e.rate) + '</td><td class="text-end fw-bold">' + fmt(e.amount) + '</td></tr>';
    });
    bt.innerHTML += '<tr class="table-success fw-bold"><td>Track B Total</td><td></td><td class="text-end">' + fmt(d.track_b_total) + '</td></tr>';

    // Track C
    var ct = document.getElementById('trackCTable').querySelector('tbody');
    ct.innerHTML = '';
    (d.track_c_entries || []).forEach(function(e) {
        ct.innerHTML += '<tr><td>' + esc(e.label) + '</td><td>' + pct(e.rate) + '</td><td class="text-end fw-bold">' + fmt(e.amount) + '</td></tr>';
    });
    ct.innerHTML += '<tr class="table-warning fw-bold"><td>Track C Total</td><td></td><td class="text-end">' + fmt(d.track_c_total) + '</td></tr>';

    // Retention
    var rc = document.getElementById('retentionCards');
    rc.innerHTML =
        '<div class="col-md-4"><div class="aps-cp-stat bg-primary text-white"><div class="aps-cp-stat-value">' + fmt(d.track_a_total) + '</div><div class="aps-cp-stat-label">Track A</div></div></div>' +
        '<div class="col-md-4"><div class="aps-cp-stat bg-success text-white"><div class="aps-cp-stat-value">' + fmt(d.track_b_total + d.track_c_total) + '</div><div class="aps-cp-stat-label">Track B + C</div></div></div>' +
        '<div class="col-md-4"><div class="aps-cp-stat bg-info text-white"><div class="aps-cp-stat-value">' + fmt(d.company_retains) + '</div><div class="aps-cp-stat-label">Company Keeps</div></div></div>';
}

function card(label, value, color) {
    return '<div class="col-md-4 col-6"><div class="aps-cp-stat bg-' + color + ' bg-opacity-10"><div class="aps-cp-stat-value text-' + color + '">' + value + '</div><div class="aps-cp-stat-label">' + label + '</div></div></div>';
}

function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
</script>
