<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/ai/InvestmentManager.php';

$page_title = "Investment Plan Manager";
$invManager = new InvestmentManager();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_plan'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $invManager->savePlan($_POST);
        log_admin_activity($_SESSION['user_id'], 'save_investment_plan', 'Saved investment plan: ' . ($_POST['name'] ?? 'Unknown'));
        $success_msg = $mlSupport->translate('Plan saved successfully!');
    } else {
        $error_msg = $mlSupport->translate('Invalid CSRF token.');
    }
}

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    if (verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        $invManager->toggleStatus($_GET['toggle_id'], $_GET['status'], $_SESSION['user_id'] ?? 1);
        log_admin_activity($_SESSION['user_id'], 'toggle_investment_plan_status', 'Toggled status for plan ID: ' . $_GET['toggle_id']);
        header("Location: investment_plans.php?success=" . urlencode($mlSupport->translate('Status updated successfully!')));
        exit();
    } else {
        $error_msg = $mlSupport->translate('Invalid CSRF token.');
    }
}

$plans = \App\Core\App::database()->fetchAll("SELECT * FROM investment_plans ORDER BY created_at DESC");

require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($mlSupport->translate('Investment Plans')) ?></li>
                        </ol>
                    </nav>
                    <h3 class="page-title"><?= h($mlSupport->translate('Investment Plan Manager')) ?></h3>
                    <p class="text-muted"><?= h($mlSupport->translate('Create, Schedule and Monitor Investment Schemes')) ?></p>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                        <i class="fas fa-plus me-2"></i> <?= h($mlSupport->translate('New Investment Plan')) ?>
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($success_msg) || isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= h($success_msg ?? $_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= h($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($plans as $plan): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary"><?= h($plan['name']) ?></h5>
                        <span class="badge rounded-pill bg-<?= $plan['is_active'] ? 'success' : 'danger' ?> px-3">
                            <?= h($mlSupport->translate($plan['is_active'] ? 'Active' : 'Inactive')) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3"><?= h(substr($plan['description'], 0, 100)) ?>...</p>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><?= h($mlSupport->translate('ROI:')) ?></span>
                            <span class="fw-bold text-success"><?= h($plan['expected_roi_percentage']) ?>%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><?= h($mlSupport->translate('Min Investment:')) ?></span>
                            <span class="fw-bold">₹<?= number_format($plan['min_amount']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><?= h($mlSupport->translate('Duration:')) ?></span>
                            <span class="fw-bold"><?= h($plan['duration_months']) ?> <?= h($mlSupport->translate('Months')) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-muted"><?= h($mlSupport->translate('Type:')) ?></span>
                            <span class="badge bg-light text-dark fw-normal"><?= h(strtoupper(str_replace('_', ' ', $plan['plan_type']))) ?></span>
                        </div>
                        
                        <div class="btn-group w-100 shadow-sm">
                            <a href="?toggle_id=<?= h($plan['id']) ?>&status=<?= $plan['is_active'] ? 'inactive' : 'active' ?>&csrf_token=<?= generateCSRFToken() ?>" 
                               class="btn btn-sm btn-<?= $plan['is_active'] ? 'outline-danger' : 'outline-success' ?> py-2">
                                <i class="fas <?= $plan['is_active'] ? 'fa-times-circle' : 'fa-check-circle' ?> me-1"></i>
                                <?= h($mlSupport->translate($plan['is_active'] ? 'Deactivate' : 'Activate')) ?>
                            </a>
                            <button class="btn btn-sm btn-outline-primary py-2">
                                <i class="fas fa-edit me-1"></i> <?= h($mlSupport->translate('Edit')) ?>
                            </button>
                            <button class="btn btn-sm btn-outline-info py-2" onclick="testROI(<?= h($plan['id']) ?>)">
                                <i class="fas fa-calculator me-1"></i> <?= h($mlSupport->translate('ROI Calc')) ?>
                            </button>
                        </div>
                    </div>
                    <?php if ($plan['end_date']): ?>
                    <div class="card-footer bg-light border-0 py-2 small text-center text-muted">
                        <i class="far fa-calendar-alt me-1"></i> <?= h($mlSupport->translate('Expires on:')) ?> <?= h(date('d M Y', strtotime($plan['end_date']))) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content shadow-lg border-0">
            <?= getCsrfField() ?>
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i><?= h($mlSupport->translate('Create New Investment Plan')) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= h($mlSupport->translate('Plan Name')) ?></label>
                    <input type="text" name="name" class="form-control form-control-lg shadow-sm" placeholder="<?= h($mlSupport->translate('Enter plan name')) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><?= h($mlSupport->translate('Min Amount (₹)')) ?></label>
                        <input type="number" name="min_amount" class="form-control shadow-sm" placeholder="50000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><?= h($mlSupport->translate('ROI (%)')) ?></label>
                        <input type="number" step="0.1" name="roi" class="form-control shadow-sm" placeholder="12.5" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><?= h($mlSupport->translate('Duration (Months)')) ?></label>
                        <input type="number" name="duration" class="form-control shadow-sm" placeholder="12" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><?= h($mlSupport->translate('Plan Type')) ?></label>
                        <select name="type" class="form-select shadow-sm">
                            <option value="fixed_return"><?= h($mlSupport->translate('Fixed Return')) ?></option>
                            <option value="plot_appreciation"><?= h($mlSupport->translate('Plot Appreciation')) ?></option>
                            <option value="equity_share"><?= h($mlSupport->translate('Equity Share')) ?></option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= h($mlSupport->translate('Description')) ?></label>
                    <textarea name="description" class="form-control shadow-sm" rows="3" placeholder="<?= h($mlSupport->translate('Describe the plan details...')) ?>"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><?= h($mlSupport->translate('Start Date')) ?></label>
                        <input type="date" name="start_date" class="form-control shadow-sm">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><?= h($mlSupport->translate('End Date')) ?></label>
                        <input type="date" name="end_date" class="form-control shadow-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal"><?= h($mlSupport->translate('Cancel')) ?></button>
                <button type="submit" name="save_plan" class="btn btn-primary px-4 shadow-sm"><?= h($mlSupport->translate('Save Plan')) ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function testROI(planId) {
    let amount = prompt("<?= h($mlSupport->translate('Enter investment amount to test ROI calculation:')) ?>", "100000");
    if (amount) {
        alert("<?= h($mlSupport->translate('ROI Calculation Logic Ready! It will calculate returns based on plan rules.')) ?>");
    }
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>


