<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'Investment Plans - APS Dream Home';
$current_page = 'investment';

$stats = $stats ?? ['total' => 0, 'active' => 0, 'principal' => 0, 'current_value' => 0, 'returns' => 0, 'level' => 'Bronze', 'next_level' => 'Silver', 'next_threshold' => 50000, 'progress_pct' => 0];
$plans = $plans ?? [];
$investments = $investments ?? [];

$riskColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
$categoryIcons = ['sip' => 'fa-chart-line', 'lumpsum' => 'fa-coins', 'recurring_deposit' => 'fa-piggy-bank', 'gold' => 'fa-gem', 'real_estate_fund' => 'fa-building', 'crypto' => 'fa-bitcoin-sign'];
$levelColor = match($stats['level']) { 'Diamond' => 'indigo', 'Platinum' => 'purple', 'Gold' => 'orange', 'Silver' => 'secondary', default => 'primary' };

ob_start();
?>
<div class="aps-cp-page-header">
    <h2><i class="fas fa-chart-line"></i> Investment Plans</h2>
    <p>Grow your wealth with our curated investment opportunities.</p>
</div>

<div class="aps-cp-stat-grid">
    <div class="aps-cp-stat aps-cp-stat-blue">
        <div class="aps-cp-stat-icon"><i class="fas fa-coins"></i></div>
        <div class="aps-cp-stat-value" data-countup="<?= (float)$stats['principal'] ?>">0</div>
        <div class="aps-cp-stat-label">Total Invested</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-green">
        <div class="aps-cp-stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="aps-cp-stat-value" data-countup="<?= (float)$stats['current_value'] ?>">0</div>
        <div class="aps-cp-stat-label">Current Value</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-orange">
        <div class="aps-cp-stat-icon"><i class="fas fa-percentage"></i></div>
        <div class="aps-cp-stat-value" data-countup="<?= (float)$stats['returns'] ?>">0</div>
        <div class="aps-cp-stat-label">Returns</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-purple">
        <div class="aps-cp-stat-icon"><i class="fas fa-briefcase"></i></div>
        <div class="aps-cp-stat-value"><?= (int)$stats['active'] ?></div>
        <div class="aps-cp-stat-label">Active Plans</div>
    </div>
</div>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-body">
        <div class="aps-cp-flex-between">
            <div>
                <span class="aps-cp-muted">Investor Level</span>
                <h3 class="mt-0"><i class="fas fa-trophy" style="color:var(--aps-cp-<?= $levelColor ?>)"></i> <span class="aps-cp-badge aps-cp-badge-<?= $levelColor ?> aps-cp-badge-lg"><?= htmlspecialchars($stats['level']) ?></span></h3>
            </div>
            <div style="flex:1; margin-left:24px;">
                <div class="aps-cp-progress">
                    <div class="aps-cp-progress-bar" style="width:<?= (float)$stats['progress_pct'] ?>%; background:linear-gradient(90deg, #4f46e5, #8b5cf6);"></div>
                </div>
                <p class="aps-cp-muted mt-1 mb-0">Invest ₹<?= number_format((float)$stats['next_threshold']) ?> more to reach <strong><?= htmlspecialchars($stats['next_level']) ?></strong></p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($investments)): ?>
<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header"><h3><i class="fas fa-briefcase"></i> Your Investments</h3></div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-table-wrap">
            <table class="aps-cp-table">
                <thead><tr><th>Reference</th><th>Plan</th><th>Principal</th><th>Current Value</th><th>Returns</th><th>Monthly SIP</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($investments as $inv):
                    $cat = $inv['plan_category'] ?? 'sip';
                    $ret = (float)$inv['current_value'] - (float)$inv['principal_amount'];
                    $statusColor = match($inv['status'] ?? 'active') { 'active' => 'success', 'matured' => 'info', 'cancelled' => 'danger', 'paused' => 'warning', default => 'secondary' };
                ?>
                <tr>
                    <td><code><?= htmlspecialchars($inv['investment_ref']) ?></code></td>
                    <td><i class="fas <?= $categoryIcons[$cat] ?? 'fa-chart-line' ?>"></i> <?= htmlspecialchars($inv['plan_name']) ?></td>
                    <td>₹<?= number_format((float)$inv['principal_amount']) ?></td>
                    <td>₹<?= number_format((float)$inv['current_value']) ?></td>
                    <td class="aps-cp-<?= $ret >= 0 ? 'text-success' : 'text-danger' ?>"><?= $ret >= 0 ? '+' : '' ?>₹<?= number_format($ret) ?></td>
                    <td>₹<?= number_format((float)$inv['monthly_amount']) ?></td>
                    <td><span class="aps-cp-badge aps-cp-badge-<?= $statusColor ?>"><?= htmlspecialchars(ucfirst($inv['status'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header"><h3>Available Investment Plans</h3></div>
    <div class="aps-cp-card-body">
        <?php if (empty($plans)): ?>
        <div class="aps-cp-empty"><i class="fas fa-chart-line aps-cp-empty-icon"></i><p>No plans available right now.</p></div>
        <?php else: ?>
        <div class="aps-cp-info-grid">
            <?php foreach ($plans as $plan):
                $cat = $plan['plan_category'] ?? 'sip';
                $icon = $categoryIcons[$cat] ?? 'fa-chart-line';
                $risk = $plan['risk_level'] ?? 'medium';
            ?>
            <div class="aps-cp-info-card" data-plan-id="<?= (int)$plan['id'] ?>">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($plan['plan_name']) ?></h4>
                    <?php if (!empty($plan['is_featured'])): ?><span class="aps-cp-badge aps-cp-badge-primary">Featured</span><?php endif; ?>
                </div>
                <p class="aps-cp-info-card-meta">Min: ₹<?= number_format((float)$plan['min_amount']) ?> | Tenure: <?= (int)($plan['tenure_months'] ?? 0) ?> mo | Risk: <span class="aps-cp-badge aps-cp-badge-<?= $riskColors[$risk] ?? 'warning' ?>"><?= htmlspecialchars(ucfirst($risk)) ?></span></p>
                <?php if (!empty($plan['features'])): ?>
                <ul class="aps-cp-list">
                    <?php foreach ($plan['features'] as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <div class="aps-cp-info-card-foot">
                    <strong>Expected: <?= number_format((float)$plan['expected_return_pct'], 1) ?>% p.a.</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-primary" onclick="openInvestModal(<?= (int)$plan['id'] ?>, '<?= htmlspecialchars(addslashes($plan['plan_name'])) ?>', <?= (float)$plan['min_amount'] ?>)">Invest</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="investModal" class="aps-cp-modal" style="display:none;">
    <div class="aps-cp-modal-overlay" onclick="closeInvestModal()"></div>
    <div class="aps-cp-modal-dialog">
        <div class="aps-cp-modal-head">
            <h3 id="investTitle">Invest</h3>
            <button class="aps-cp-modal-close" onclick="closeInvestModal()">&times;</button>
        </div>
        <form id="investForm" class="aps-cp-form" data-ajax="true" action="<?= BASE_URL ?>/user/investment-plans/invest" method="POST">
            <input type="hidden" name="csrf_token" value="">
            <input type="hidden" name="plan_id" id="investPlanId" value="">
            <div class="aps-cp-modal-body">
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="principal_amount">Principal Amount (₹)</label>
                    <input type="number" name="principal_amount" id="principal_amount" class="aps-cp-input" min="1000" step="100" required>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="sip_date">SIP Date (1-28)</label>
                    <input type="number" name="sip_date" id="sip_date" class="aps-cp-input" min="1" max="28" value="5">
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-form-check">
                        <input type="checkbox" name="auto_invest" value="1" checked> <span>Enable auto-debit for SIP</span>
                    </label>
                </div>
            </div>
            <div class="aps-cp-modal-foot">
                <button type="button" class="aps-cp-btn aps-cp-btn-secondary" onclick="closeInvestModal()">Cancel</button>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" id="investSubmitBtn">Invest Now</button>
            </div>
        </form>
    </div>
</div>

<script>
function openInvestModal(planId, planName, minAmount) {
    document.getElementById('investPlanId').value = planId;
    document.getElementById('investTitle').textContent = 'Invest in ' + planName;
    document.getElementById('principal_amount').value = minAmount;
    document.getElementById('principal_amount').min = minAmount;
    document.getElementById('investModal').style.display = 'flex';
}
function closeInvestModal() { document.getElementById('investModal').style.display = 'none'; }
document.getElementById('investForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('investSubmitBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Investing...';
    try {
        const r = await fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) {
            APS.toast('Investment opened! Ref: ' + data.investment_ref, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            APS.toast(data.error || 'Investment failed', 'error');
            btn.disabled = false; btn.innerHTML = 'Invest Now';
        }
    } catch (err) {
        APS.toast('Network error: ' + err.message, 'error');
        btn.disabled = false; btn.innerHTML = 'Invest Now';
    }
});
</script>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
