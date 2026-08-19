<?php
$plans = $plans ?? [];
$activePlan = $activePlan ?? null;
$levels = $levels ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-calculator me-2"></i>Commission Calculator</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/commission-plans" class="btn btn-link btn-sm">Back to Plans</a>
    </div>
    <div class="aps-cp-card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Select Plan</label>
                <select id="calcPlan" class="form-select" onchange="loadLevels()">
                    <?php if (empty($plans)): ?>
                        <option value="">No plans available</option>
                    <?php else: ?>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?= $plan['id'] ?>" <?= ($activePlan && $activePlan['id'] == $plan['id']) ? 'selected' : '' ?> data-name="<?= htmlspecialchars($plan['plan_name'] ?? '') ?>">
                                <?= htmlspecialchars($plan['plan_name'] ?? '') ?> (<?= htmlspecialchars($plan['plan_code'] ?? '') ?>) — <?= ucfirst($plan['status']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sale Amount (₹)</label>
                <input type="number" id="saleAmount" class="form-control" value="1500000" step="10000" min="0" oninput="calculateCommissions()">
            </div>
            <div class="col-md-4">
                <label class="form-label">Agent Level</label>
                <select id="agentLevel" class="form-select" onchange="calculateCommissions()">
                    <option value="0">Associate (Entry)</option>
                    <option value="1">Sr. Associate</option>
                    <option value="2">BDM</option>
                    <option value="3">Sr. BDM</option>
                    <option value="4">Vice President</option>
                    <option value="5">President</option>
                    <option value="6">Site Manager</option>
                </select>
            </div>
        </div>

        <?php if (!$activePlan): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>No active commission plan. Activate one first.</div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <div class="text-muted small">Sale Amount</div>
                        <h4 id="dispSale" class="text-primary">₹15,00,000</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <div class="text-muted small">Total Commission</div>
                        <h4 id="dispTotal" class="text-success">₹0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <div class="text-muted small">Commission %</div>
                        <h4 id="dispPct" class="text-warning">0%</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-danger">
                    <div class="card-body">
                        <div class="text-muted small">Net to Company</div>
                        <h4 id="dispNet" class="text-danger">₹0</h4>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="mb-3"><i class="fas fa-layer-group me-1"></i>Commission Breakdown by Upline Level</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm m-0">
                <thead class="table-light">
                    <tr>
                        <th>Level</th>
                        <th>Direct %</th>
                        <th>Team %</th>
                        <th>Level Bonus %</th>
                        <th>Matching %</th>
                        <th>Leadership %</th>
                        <th>Performance %</th>
                        <th>Total %</th>
                        <th>Amount (₹)</th>
                    </tr>
                </thead>
                <tbody id="calcBody">
                    <tr><td colspan="9" class="text-center text-muted">Loading levels...</td></tr>
                </tbody>
                <tfoot id="calcFoot">
                    <tr class="table-light">
                        <td colspan="7"><strong>Grand Total</strong></td>
                        <td id="footPct"><strong>0%</strong></td>
                        <td id="footAmt"><strong>₹0</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="alert alert-secondary small">
            <i class="fas fa-info-circle me-1"></i>
            <strong>Global Cap:</strong> Total commission per payment must not exceed 20% of sale amount.
            The calculator highlights rows exceeding 20% in red. Adjust percentages in the <a href="<?= htmlspecialchars($base ?? '') ?>/admin/commission-plans/edit/1">plan editor</a>.
        </div>
    </div>
</div>

<script>
let calcLevels = [];
const fmt = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });

function loadLevels() {
    const planId = document.getElementById('calcPlan').value;
    if (!planId) return;
    fetch('<?= $base ?>/admin/commission-plans/ajax-levels?plan_id=' + planId)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.levels) {
                calcLevels = data.levels;
                calculateCommissions();
                .catch(err => console.error('Request failed:', err));
            }
        })
        .catch(err => console.error('Failed to load levels:', err));
}

function calculateCommissions() {
    const amount = parseFloat(document.getElementById('saleAmount').value) || 0;
    const agentIdx = parseInt(document.getElementById('agentLevel').value) || 0;
    let totalPct = 0;
    let totalAmt = 0;
    const tbody = document.getElementById('calcBody');
    let html = '';

    calcLevels.forEach((lv, i) => {
        const direct = parseFloat(lv.direct_commission) || 0;
        const team = parseFloat(lv.team_commission) || 0;
        const lvlBonus = parseFloat(lv.level_bonus) || 0;
        const match = parseFloat(lv.matching_bonus) || 0;
        const leader = parseFloat(lv.leadership_bonus) || 0;
        const perf = parseFloat(lv.performance_bonus) || 0;
        const rowTotal = direct + team + lvlBonus + match + leader + perf;
        const rowAmt = amount * rowTotal / 100;
        totalPct += rowTotal;
        totalAmt += rowAmt;

        const rowClass = i >= agentIdx ? '' : 'text-muted';
        html += '<tr class="' + rowClass + '">';
        html += '<td><strong>' + lv.level_name + '</strong></td>';
        html += '<td>' + direct.toFixed(1) + '%</td>';
        html += '<td>' + team.toFixed(1) + '%</td>';
        html += '<td>' + lvlBonus.toFixed(1) + '%</td>';
        html += '<td>' + match.toFixed(1) + '%</td>';
        html += '<td>' + leader.toFixed(1) + '%</td>';
        html += '<td>' + perf.toFixed(1) + '%</td>';
        html += '<td class="' + (rowTotal > 20 ? 'text-danger fw-bold' : '') + '">' + rowTotal.toFixed(1) + '%</td>';
        html += '<td>' + fmt.format(Math.round(rowAmt)) + '</td>';
        html += '</tr>';
    });

    tbody.innerHTML = html || '<tr><td colspan="9" class="text-center text-muted">No levels</td></tr>';
    document.getElementById('footPct').innerHTML = '<strong>' + totalPct.toFixed(1) + '%</strong>';
    document.getElementById('footAmt').innerHTML = '<strong>' + fmt.format(Math.round(totalAmt)) + '</strong>';

    document.getElementById('dispSale').textContent = fmt.format(Math.round(amount));
    document.getElementById('dispTotal').textContent = fmt.format(Math.round(totalAmt));
    document.getElementById('dispPct').textContent = totalPct.toFixed(1) + '%';
    document.getElementById('dispNet').textContent = fmt.format(Math.round(amount - totalAmt));

    const pctEl = document.getElementById('dispPct');
    pctEl.className = totalPct > 20 ? 'text-danger' : 'text-warning';
    const netEl = document.getElementById('dispNet');
    netEl.className = (amount - totalAmt) < 0 ? 'text-danger' : 'text-danger';
}

document.addEventListener('DOMContentLoaded', function() { loadLevels(); });
</script>
