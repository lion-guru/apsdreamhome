<?php
/**
 * Employee Self-Service Dashboard
 * Main landing page for self-service portal
*/

$page_title = $page_title ?? 'Employee Self-Service Portal';
$tax_regime = $tax_regime ?? null;
$leave_balances = $leave_balances ?? [];
$payslips = $payslips ?? [];
$reimbursements = $reimbursements ?? [];
$attendance_stats = $attendance_stats ?? [];
$current_fy = $current_fy ?? (date('Y') . '-' . (date('Y') + 1));
?>

<div class="container-fluid py-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">Welcome back, <strong><?= htmlspecialchars($_SESSION['employee_name'] ?? 'Employee') ?></strong>!</h2>
                            <p class="mb-0 opacity-75">Employee Self-Service Portal | <?= date('l, F j, Y') ?></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-light text-dark fs-6 px-3 py-2">
                                <?= htmlspecialchars($_SESSION['employee_role'] ?? 'Employee') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="stat-icon bg-primary text-white mx-auto mb-2">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <h3 class="mb-0"><?= $attendance_stats['present'] ?? 0 ?></h3>
                    <small class="text-muted">Days Present This Month</small>
                    <div class="mt-2">
                        <span class="badge bg-success"><?= $attendance_stats['total_hours'] ?? 0 ?> hrs</span>
                        <span class="badge bg-warning text-dark"><?= $attendance_stats['total_overtime'] ?? 0 ?> OT hrs</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="stat-icon bg-success text-white mx-auto mb-2">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h3 class="mb-0">
                        <?php 
                        $totalLeave = 0;
                        foreach ($leave_balances as $lb) $totalLeave += (float)($lb['remaining_days'] ?? 0);
                        echo number_format($totalLeave, 1);
                        ?>
                    </h3>
                    <small class="text-muted">Total Leave Balance (Days)</small>
                    <div class="mt-2">
                        <span class="badge bg-info"><?= count($leave_balances) ?> Types</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="stat-icon bg-warning text-dark mx-auto mb-2">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                    <h3 class="mb-0"><?= count($reimbursements) ?></h3>
                    <small class="text-muted">Pending Reimbursements</small>
                    <div class="mt-2">
                        <?php 
                        $pendingAmt = 0;
                        foreach ($reimbursements as $r) if (($r['status'] ?? '') === 'pending') $pendingAmt += (float)$r['amount'];
                        ?>
                        <span class="badge bg-danger">₹<?= number_format($pendingAmt, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="stat-icon bg-info text-white mx-auto mb-2">
                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <h3 class="mb-0"><?= count($payslips) ?></h3>
                    <small class="text-muted">Recent Payslips</small>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/employee/self-service/payslips" class="btn btn-sm btn-outline-info">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">Quick Actions</h5>
        </div>
        
        <!-- Tax Regime -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-primary text-white mx-auto mb-3">
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                    <h5>Tax Regime Selection</h5>
                    <p class="text-muted small mb-3">
                        Current: <strong><?= $tax_regime ? strtoupper($tax_regime['regime']) : 'Not Set' ?></strong> 
                        for FY <?= $current_fy ?>
                    </p>
                    <a href="<?= BASE_URL ?>/employee/self-service/tax-regime" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Select Regime
                    </a>
                </div>
            </div>
        </div>

        <!-- Investment Declaration -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-success text-white mx-auto mb-3">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h5>Investment Declaration</h5>
                    <p class="text-muted small mb-3">Declare Sec 80C, 80D, HRA, Home Loan & more for FY <?= $current_fy ?></p>
                    <a href="<?= BASE_URL ?>/employee/self-service/investment-declaration" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Declare Now
                    </a>
                </div>
            </div>
        </div>

        <!-- Form 16 -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-info text-white mx-auto mb-3">
                        <i class="fas fa-file-download fa-2x"></i>
                    </div>
                    <h5>Form 16 Download</h5>
                    <p class="text-muted small mb-3">Generate & download TDS certificates</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/form16" class="btn btn-info">
                        <i class="fas fa-download me-1"></i> Download
                    </a>
                </div>
            </div>
        </div>

        <!-- Payslips -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-warning text-dark mx-auto mb-3">
                        <i class="fas fa-money-bill fa-2x"></i>
                    </div>
                    <h5>Payslip History</h5>
                    <p class="text-muted small mb-3">View & download last 24 months payslips</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/payslips" class="btn btn-warning">
                        <i class="fas fa-history me-1"></i> View Payslips
                    </a>
                </div>
            </div>
        </div>

        <!-- Leave Management -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-danger text-white mx-auto mb-3">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <h5>Leave Management</h5>
                    <p class="text-muted small mb-3">Apply leave, check balance, view history</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/leave" class="btn btn-danger">
                        <i class="fas fa-calendar-plus me-1"></i> Manage Leave
                    </a>
                </div>
            </div>
        </div>

        <!-- Reimbursement -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-secondary text-white mx-auto mb-3">
                        <i class="fas fa-credit-card fa-2x"></i>
                    </div>
                    <h5>Reimbursement Claims</h5>
                    <p class="text-muted small mb-3">Medical, LTA, Fuel, Phone, Internet claims</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/reimbursement" class="btn btn-secondary">
                        <i class="fas fa-plus me-1"></i> New Claim
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-dark text-white mx-auto mb-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h5>My Attendance</h5>
                    <p class="text-muted small mb-3">View monthly attendance & hours</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/attendance" class="btn btn-dark">
                        <i class="fas fa-calendar-day me-1"></i> View
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body text-center p-4">
                    <div class="action-icon bg-primary text-white mx-auto mb-3">
                        <i class="fas fa-user-cog fa-2x"></i>
                    </div>
                    <h5>My Profile</h5>
                    <p class="text-muted small mb-3">Update personal info, bank details, password</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/profile" class="btn btn-primary">
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payslips Quick View -->
    <?php if (!empty($payslips)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Payslips</h5>
                    <a href="<?= BASE_URL ?>/employee/self-service/payslips" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Basic</th>
                                    <th>Gross</th>
                                    <th>Deductions</th>
                                    <th class="text-success">Net Pay</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($payslips, 0, 5) as $ps): ?>
                                <tr>
                                    <td><?= date('F Y', mktime(0,0,0, $ps['period_month'], 1, $ps['period_year'])) ?></td>
                                    <td>₹<?= number_format($ps['basic_salary'] ?? 0, 2) ?></td>
                                    <td>₹<?= number_format($ps['basic_salary'] + $ps['hra'] + $ps['allowances'], 2) ?></td>
                                    <td>₹<?= number_format($ps['deductions'] ?? 0, 2) ?></td>
                                    <td class="text-success fw-bold">₹<?= number_format($ps['net_salary'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php $st = $ps['status'] ?? 'draft'; ?>
                                        <span class="badge bg-<?= $st === 'paid' ? 'success' : ($st === 'approved' ? 'info' : 'warning') ?>">
                                            <?= ucfirst($st) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/employee/self-service/payslip/download/<?= $ps['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leave Balance Summary -->
    <?php if (!empty($leave_balances)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Leave Balances (<?= date('Y') ?>)</h5>
                    <a href="<?= BASE_URL ?>/employee/self-service/leave" class="btn btn-sm btn-outline-primary">Manage Leave</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Leave Type</th>
                                    <th class="text-center">Allocated</th>
                                    <th class="text-center">Used</th>
                                    <th class="text-center text-success">Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leave_balances as $lb): ?>
                                <tr>
                                    <td>
                                        <span class="badge me-2" style="background: <?= $lb['color'] ?? '#007bff' ?>"><?= htmlspecialchars($lb['leave_type_code'] ?? '') ?></span>
                                        <?= htmlspecialchars($lb['leave_type_name'] ?? '') ?>
                                    </td>
                                    <td class="text-center"><?= number_format($lb['allocated_days'] ?? 0, 1) ?></td>
                                    <td class="text-center"><?= number_format($lb['used_days'] ?? 0, 1) ?></td>
                                    <td class="text-center text-success fw-bold"><?= number_format($lb['remaining_days'] ?? 0, 1) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.stat-card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,0.12); }
.stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.action-card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.2s; cursor: pointer; }
.action-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
.action-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.card.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
</style>