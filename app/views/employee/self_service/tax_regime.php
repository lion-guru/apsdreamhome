<?php
/**
 * Tax Regime Selection
*/

$page_title = $page_title ?? 'Tax Regime Selection';
$current_regime = $current_regime ?? null;
$financial_year = $financial_year ?? date('Y');
$fy_label = $fy_label ?? ($financial_year . '-' . ($financial_year + 1));
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calculator me-2"></i>Tax Regime Selection - FY <?= htmlspecialchars($fy_label) ?></h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Choose Your Tax Regime</h6>
                        <p class="mb-0">Your choice applies for the entire financial year and affects TDS deduction from your salary. You can change it once per financial year before the first salary payment.</p>
                    </div>

                    <!-- Current Regime Display -->
                    <?php if ($current_regime): ?>
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Current Selection: <strong><?= strtoupper($current_regime['regime']) ?> Regime</strong></h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Financial Year:</strong> <?= $financial_year ?>-<?= $financial_year + 1 ?></p>
                            <p class="mb-0"><strong>Selected on:</strong> <?= date('d M Y', strtotime($current_regime['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>No tax regime selected for FY <?= $fy_label ?>. Default is <strong>New Regime</strong>.
                    </div>
                    <?php endif; ?>

                    <!-- Regime Comparison -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">New Tax Regime (Default)</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Lower slab rates</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>No deductions/exemptions (except 80CCD(2), 80JJAA)</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Standard deduction ₹50,000</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Simpler compliance</li>
                                    </ul>
                                    <hr>
                                    <h6>Slabs (FY 2024-25):</h6>
                                    <small class="text-muted">
                                        0-3L: 0% | 3-7L: 5% | 7-10L: 10%<br>
                                        10-12L: 15% | 12-15L: 20% | 15L+: 30%
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">Old Tax Regime</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>All deductions available (80C, 80D, HRA, 24b, etc.)</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Standard deduction ₹50,000</li>
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Higher slab rates</li>
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Complex compliance</li>
                                    </ul>
                                    <hr>
                                    <h6>Slabs (FY 2024-25):</h6>
                                    <small class="text-muted">
                                        0-2.5L: 0% | 2.5-5L: 5% | 5-10L: 20%<br>
                                        10L+: 30% (+ Surcharge if >50L)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selection Form -->
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="financial_year" value="<?= $financial_year ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card regime-option h-100" data-regime="new">
                                    <div class="card-body text-center p-4">
                                        <h4 class="text-primary">New Regime</h4>
                                        <p class="text-muted">Recommended for most employees</p>
                                        <div class="form-check form-check-inline mt-3">
                                            <input class="form-check-input" type="radio" name="regime" value="new" id="regime_new" <?= (!$current_regime || ($current_regime['regime'] ?? '') === 'new') ? 'checked' : '' ?> required>
                                            <label class="form-check-label fw-bold" for="regime_new">Select New Regime</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card regime-option h-100" data-regime="old">
                                    <div class="card-body text-center p-4">
                                        <h4 class="text-warning">Old Regime</h4>
                                        <p class="text-muted">If you have significant deductions</p>
                                        <div class="form-check form-check-inline mt-3">
                                            <input class="form-check-input" type="radio" name="regime" value="old" id="regime_old" <?= ($current_regime['regime'] ?? '') === 'old' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="regime_old">Select Old Regime</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Once selected, the regime applies for the entire FY. Changes are only allowed before the first salary payment of the financial year.
                            </div>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Save Selection
                            </button>
                            <a href="<?= BASE_URL ?>/employee/self-service" class="btn btn-outline-secondary btn-lg px-4 ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.regime-option { border: 2px solid #dee2e6; transition: all 0.2s; cursor: pointer; }
.regime-option:hover { border-color: #0d6efd; box-shadow: 0 4px 12px rgba(13,110,253,0.15); }
.regime-option.selected { border-color: #0d6efd; background: rgba(13,110,253,0.05); }
.card { border-radius: 0.75rem; }
.card-header { border-radius: 0.75rem 0.75rem 0 0 !important; }
</style>

<script>
document.querySelectorAll('.regime-option').forEach(card => {
    card.addEventListener('click', function() {
        const regime = this.dataset.regime;
        document.querySelectorAll('.regime-option').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('regime_' + regime).checked = true;
    });
    const radio = card.querySelector('input[type="radio"]');
    if (radio && radio.checked) card.classList.add('selected');
});
</script>