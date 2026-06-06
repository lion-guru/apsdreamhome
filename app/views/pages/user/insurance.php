<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'Insurance - APS Dream Home';
$current_page = 'insurance';

$stats = $stats ?? ['home' => 0, 'health' => 0, 'term_life' => 0, 'vehicle' => 0, 'travel' => 0, 'total' => 0];
$plans = $plans ?? [];
$policies = $policies ?? [];
$activeCount = count(array_filter($policies, fn($p) => ($p['status'] ?? '') === 'active'));

$categoryIcons = ['home' => 'fa-home', 'health' => 'fa-heartbeat', 'term_life' => 'fa-umbrella', 'vehicle' => 'fa-car', 'travel' => 'fa-plane'];
$categoryColors = ['home' => 'blue', 'health' => 'green', 'term_life' => 'orange', 'vehicle' => 'purple', 'travel' => 'indigo'];
$categoryNames = ['home' => 'Home', 'health' => 'Health', 'term_life' => 'Term Life', 'vehicle' => 'Vehicle', 'travel' => 'Travel'];

ob_start();
?>
<div class="aps-cp-page-header">
    <h2><i class="fas fa-shield-alt"></i> Insurance Plans</h2>
    <p>Protect your property, family, and travels with our insurance partners.</p>
</div>

<div class="aps-cp-stat-grid">
    <div class="aps-cp-stat aps-cp-stat-blue">
        <div class="aps-cp-stat-icon"><i class="fas fa-home"></i></div>
        <div class="aps-cp-stat-value"><?= (int)$stats['home'] ?></div>
        <div class="aps-cp-stat-label">Home Insurance</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-green">
        <div class="aps-cp-stat-icon"><i class="fas fa-heartbeat"></i></div>
        <div class="aps-cp-stat-value"><?= (int)$stats['health'] ?></div>
        <div class="aps-cp-stat-label">Health Insurance</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-orange">
        <div class="aps-cp-stat-icon"><i class="fas fa-umbrella"></i></div>
        <div class="aps-cp-stat-value"><?= (int)$stats['term_life'] ?></div>
        <div class="aps-cp-stat-label">Term Life</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-purple">
        <div class="aps-cp-stat-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="aps-cp-stat-value"><?= (int)$stats['total'] ?></div>
        <div class="aps-cp-stat-label">Active Policies</div>
    </div>
</div>

<?php if (!empty($policies)): ?>
<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3><i class="fas fa-file-contract"></i> Your Active Policies</h3>
    </div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-table-wrap">
            <table class="aps-cp-table">
                <thead>
                    <tr><th>Policy #</th><th>Plan</th><th>Category</th><th>Sum Insured</th><th>Premium</th><th>Valid Till</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($policies as $p):
                    $cat = $p['plan_category'] ?? 'home';
                    $icon = $categoryIcons[$cat] ?? 'fa-shield';
                    $statusColor = match($p['status'] ?? 'pending') { 'active' => 'success', 'expired' => 'secondary', 'cancelled' => 'danger', default => 'warning' };
                ?>
                    <tr>
                        <td><code><?= htmlspecialchars($p['policy_number']) ?></code></td>
                        <td><i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($p['plan_name']) ?></td>
                        <td><span class="aps-cp-badge aps-cp-badge-<?= $categoryColors[$cat] ?? 'primary' ?>"><?= htmlspecialchars($categoryNames[$cat] ?? ucfirst($cat)) ?></span></td>
                        <td>₹<?= number_format((float)$p['sum_insured']) ?></td>
                        <td>₹<?= number_format((float)$p['premium_amount']) ?></td>
                        <td><?= htmlspecialchars(date('M j, Y', strtotime($p['end_date']))) ?></td>
                        <td><span class="aps-cp-badge aps-cp-badge-<?= $statusColor ?>"><?= htmlspecialchars(ucfirst($p['status'] ?? 'pending')) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3>Available Plans</h3>
    </div>
    <div class="aps-cp-card-body">
        <?php if (empty($plans)): ?>
        <div class="aps-cp-empty"><i class="fas fa-shield-alt aps-cp-empty-icon"></i><p>No plans available right now.</p></div>
        <?php else: ?>
        <div class="aps-cp-info-grid">
            <?php foreach ($plans as $plan):
                $cat = $plan['plan_category'] ?? 'home';
                $icon = $categoryIcons[$cat] ?? 'fa-shield';
                $color = $categoryColors[$cat] ?? 'primary';
            ?>
            <div class="aps-cp-info-card" data-plan-id="<?= (int)$plan['id'] ?>">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($plan['plan_name']) ?></h4>
                    <?php if (!empty($plan['is_featured'])): ?><span class="aps-cp-badge aps-cp-badge-<?= $color ?>">Featured</span><?php endif; ?>
                </div>
                <p class="aps-cp-info-card-meta"><?= htmlspecialchars($plan['insurer_name'] ?? '') ?> | Coverage: ₹<?= number_format((float)$plan['coverage_amount']) ?></p>
                <?php if (!empty($plan['features'])): ?>
                <ul class="aps-cp-list">
                    <?php foreach ($plan['features'] as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <div class="aps-cp-info-card-foot">
                    <strong>From ₹<?= number_format((float)$plan['premium_monthly']) ?>/mo</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-primary" onclick="openEnrolModal(<?= (int)$plan['id'] ?>, '<?= htmlspecialchars(addslashes($plan['plan_name'])) ?>')">Enrol</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="aps-cp-alert aps-cp-alert-info mt-3">
    <i class="fas fa-info-circle"></i> Premiums shown are indicative. Final policy subject to insurer underwriting and KYC.
</div>

<div id="enrolModal" class="aps-cp-modal" style="display:none;">
    <div class="aps-cp-modal-overlay" onclick="closeEnrolModal()"></div>
    <div class="aps-cp-modal-dialog">
        <div class="aps-cp-modal-head">
            <h3 id="enrolTitle">Enrol in Plan</h3>
            <button class="aps-cp-modal-close" onclick="closeEnrolModal()">&times;</button>
        </div>
        <form id="enrolForm" class="aps-cp-form" data-ajax="true" action="<?= BASE_URL ?>/user/insurance/enrol" method="POST">
            <input type="hidden" name="csrf_token" value="">
            <input type="hidden" name="plan_id" id="enrolPlanId" value="">
            <div class="aps-cp-modal-body">
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="nominee_name">Nominee Name</label>
                    <input type="text" name="nominee_name" id="nominee_name" class="aps-cp-input" required>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="nominee_relation">Nominee Relation</label>
                    <select name="nominee_relation" id="nominee_relation" class="aps-cp-input" required>
                        <option value="">Select</option>
                        <option value="spouse">Spouse</option>
                        <option value="parent">Parent</option>
                        <option value="child">Child</option>
                        <option value="sibling">Sibling</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="aps-cp-form-section">
                    <label class="aps-cp-label" for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="aps-cp-input" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="aps-cp-modal-foot">
                <button type="button" class="aps-cp-btn aps-cp-btn-secondary" onclick="closeEnrolModal()">Cancel</button>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" id="enrolSubmitBtn">Enrol Now</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEnrolModal(planId, planName) {
    document.getElementById('enrolPlanId').value = planId;
    document.getElementById('enrolTitle').textContent = 'Enrol in ' + planName;
    document.getElementById('enrolModal').style.display = 'flex';
}
function closeEnrolModal() {
    document.getElementById('enrolModal').style.display = 'none';
}
document.getElementById('enrolForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('enrolSubmitBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enrolling...';
    try {
        const r = await fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await r.json();
        if (data.success) {
            APS.toast('Enrolled! Policy #: ' + data.policy_number, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            APS.toast(data.error || 'Enrolment failed', 'error');
            btn.disabled = false; btn.innerHTML = 'Enrol Now';
        }
    } catch (err) {
        APS.toast('Network error: ' + err.message, 'error');
        btn.disabled = false; btn.innerHTML = 'Enrol Now';
    }
});
</script>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
