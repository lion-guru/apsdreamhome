<?php
/**
 * Gratuity Detail View (single employee)
 * Variables: $result (GratuityService::calculateGratuity output), $employee (employees + users join)
 */

$page_title = $page_title ?? 'Gratuity Detail';
$result = $result ?? ['success' => false, 'eligible' => false];
$employee = $employee ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-award me-2"></i><?= htmlspecialchars($page_title) ?></h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/gratuity/calculator?employee_id=<?= (int)($employee['id'] ?? 0) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-calculator me-1"></i> Recalculate
            </a>
            <a href="<?= BASE_URL ?>/admin/gratuity/report" class="btn btn-outline-primary ms-2">
                <i class="fas fa-file-alt me-1"></i> Liability Report
            </a>
        </div>
    </div>

    <!-- Employee -->
    <div class="card mb-4">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Employee</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Name:</strong> <?= htmlspecialchars($employee['name'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Code:</strong> <?= htmlspecialchars($employee['employee_code'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Designation:</strong> <?= htmlspecialchars($employee['designation'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Department:</strong> <?= htmlspecialchars($employee['department'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Email:</strong> <?= htmlspecialchars($employee['email'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Phone:</strong> <?= htmlspecialchars($employee['phone'] ?? '-') ?></div>
                <div class="col-md-3"><strong>PAN:</strong> <?= htmlspecialchars($employee['pan_number'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Joining:</strong> <?= !empty($employee['joining_date']) ? date('d M Y', strtotime($employee['joining_date'])) : '-' ?></div>
            </div>
        </div>
    </div>

    <?php if (!($result['success'] ?? false) || !($result['eligible'] ?? false)): ?>
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Not Eligible for Gratuity</h5></div>
        <div class="card-body">
            <p class="mb-1"><?= htmlspecialchars($result['message'] ?? 'Requires 5 years continuous service.') ?></p>
            <p class="text-muted mb-0">Service counted: <?= (int)($result['service_years_for_gratuity'] ?? 0) ?> years (months ≥ 6 round up). Basic considered: ₹<?= number_format($result['basic_salary'] ?? 0, 2) ?></p>
        </div>
    </div>
    <?php else: ?>
    <!-- Amount -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white"><div class="card-body text-center">
                <h2 class="mb-0">₹<?= number_format($result['gratuity_amount'] ?? 0, 2) ?></h2><small>Gratuity Amount</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white"><div class="card-body text-center">
                <h2 class="mb-0"><?= (int)($result['service_years_for_gratuity'] ?? 0) ?></h2><small>Years of Service</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white"><div class="card-body text-center">
                <h2 class="mb-0">₹<?= number_format($result['basic_salary'] ?? 0, 2) ?></h2><small>Monthly Basic</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculation</h5></div>
        <div class="card-body">
            <p class="mb-1"><strong>Formula:</strong> <?= htmlspecialchars($result['formula'] ?? '(15/26) × Basic × Years of Service') ?></p>
            <p class="mb-1"><strong>Per-day basic:</strong> ₹<?= number_format($result['per_day_basic'] ?? 0, 2) ?> (Basic ÷ 26)</p>
            <p class="mb-1"><strong>Tenure:</strong> <?= (int)($result['tenure']['years'] ?? 0) ?>y <?= (int)($result['tenure']['months'] ?? 0) ?>m <?= (int)($result['tenure']['days'] ?? 0) ?>d → counted as <?= (int)($result['service_years_for_gratuity'] ?? 0) ?> years</p>
            <p class="mb-0"><strong>Working:</strong> <?= htmlspecialchars($result['details']['calculation'] ?? '') ?></p>
            <?php if (!empty($result['capped'])): ?>
            <div class="alert alert-warning mt-3 mb-0">Statutory cap applied: maximum gratuity ₹20,00,000 under the Payment of Gratuity Act, 1972.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
</style>
