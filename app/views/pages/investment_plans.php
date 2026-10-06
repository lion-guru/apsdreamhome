<?php
/**
 * Customer Investment Plans Page
 * @var array $plans
 * @var array $userInvestments
 * @var array $userStats
 * @var string $base
 * @var string $csrf_token
 */
$base = BASE_URL;
$page_title = $page_title ?? 'Investment Plans';
?>
<?php include __DIR__ . '/../layouts/customer.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Investment Plans</h4>
        <a href="<?= $base ?>/user/investments" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>My Investments</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($userStats['total_invested'] ?? 0) ?></div>
                    <small>Total Invested</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($userStats['current_value'] ?? 0) ?></div>
                    <small>Current Value</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($userStats['total_returns'] ?? 0) ?></div>
                    <small>Total Returns</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold"><?= count($userInvestments ?? []) ?></div>
                    <small>Active Investments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Plans -->
    <h5 class="mb-3"><i class="fas fa-list me-2"></i>Available Plans</h5>
    <div class="row g-4">
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 <?= $plan['is_featured'] ? 'border-2 border-warning' : '' ?>">
                    <?php if ($plan['is_featured']): ?>
                        <div class="card-header bg-warning text-dark text-center small fw-bold">RECOMMENDED</div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0"><?= htmlspecialchars($plan['plan_name']) ?></h5>
                            <span class="badge bg-<?= $plan['risk_level'] === 'low' ? 'success' : ($plan['risk_level'] === 'medium' ? 'warning' : 'danger') ?>">
                                <?= ucfirst($plan['risk_level']) ?> Risk
                            </span>
                        </div>
                        <p class="text-muted small flex-grow-1"><?= htmlspecialchars($plan['description']) ?></p>
                        
                        <div class="mb-3">
                            <div class="row text-center g-2">
                                <div class="col-6">
                                    <div class="fw-bold text-success fs-5">₹<?= number_format($plan['min_amount']) ?></div>
                                    <small class="text-muted">Min Investment</small>
                                </div>
                                <div class="col-6">
                                    <div class="fw-bold text-primary fs-5"><?= $plan['expected_return_pct'] ?>%</div>
                                    <small class="text-muted">Expected Return</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Tenure: <strong><?= $plan['tenure_months'] ?> months</strong></small>
                            <?php 
                            $features = json_decode($plan['features'] ?? '[]', true);
                            if (is_array($features)): ?>
                                <ul class="list-unstyled mb-0 small">
                                    <?php foreach ($features as $feat): ?>
                                        <li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($feat) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <button class="btn btn-primary w-100" 
                                onclick="openInvestModal(<?= $plan['id'] ?>, '<?= htmlspecialchars($plan['plan_name'], ENT_QUOTES) ?>', <?= $plan['min_amount'] ?>, <?= $plan['max_amount'] ?? 0 ?>)">
                            <i class="fas fa-hand-holding-usd me-1"></i>Invest Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (empty($plans)): ?>
        <div class="text-center py-5">
            <i class="fas fa-seedling fa-3x text-muted mb-3"></i>
            <p class="text-muted">No investment plans available at the moment</p>
        </div>
    <?php endif; ?>
</div>

<!-- Invest Modal -->
<div class="modal fade" id="investModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investModalTitle">Invest in Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $base ?>/user/invest" id="investForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="plan_id" id="investPlanId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" class="form-control" id="investPlanName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Investment Amount (₹)</label>
                        <input type="number" class="form-control" name="amount" id="investAmount" required min="0" step="1000">
                        <div class="form-text">Min: <span id="minAmt">0</span> | Max: <span id="maxAmt">No limit</span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="wallet">Wallet Balance</option>
                            <option value="razorpay">Razorpay (UPI/Card/Net Banking)</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i>Lock-in period applies. Early withdrawal charges may apply.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Investment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openInvestModal(planId, planName, minAmt, maxAmt) {
    document.getElementById('investPlanId').value = planId;
    document.getElementById('investPlanName').value = planName;
    document.getElementById('investAmount').min = minAmt;
    document.getElementById('investAmount').value = minAmt;
    document.getElementById('minAmt').textContent = minAmt.toLocaleString('en-IN');
    document.getElementById('maxAmt').textContent = maxAmt > 0 ? maxAmt.toLocaleString('en-IN') : 'No limit';
    new bootstrap.Modal(document.getElementById('investModal')).show();
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/customer.php'; ?>