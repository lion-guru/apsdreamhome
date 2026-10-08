<?php
$page_title = $page_title ?? 'My Salary - Agent Portal';
$is_salaried = $is_salaried ?? false;
$structure = $structure ?? null;
$history = $history ?? [];
$payroll = $payroll ?? ['success' => false];
?>
<div class="container-fluid px-4">
    <h3 class="mt-3 mb-1"><i class="fas fa-money-check-alt me-2"></i>My Salary</h3>
    <p class="text-muted mb-4">Your fixed salary structure and this month's payroll. Structures are managed by HR.</p>
    <?php if (!$is_salaried || empty($structure)): ?>
    <div class="alert alert-info">You are currently on a commission-only plan. If HR moves you to a fixed salary, the structure and payslips will appear here.</div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card h-100"><div class="card-header fw-bold">Active Structure</div>
                <div class="card-body">
                    <div class="table-responsive"><table class="table table-sm mb-0"><tbody>
                        <tr><td>Basic salary</td><td class="text-end">₹<?= number_format($structure['basic_salary'] ?? 0, 0) ?></td></tr>
                        <tr><td>HRA</td><td class="text-end">₹<?= number_format($structure['hra'] ?? 0, 0) ?></td></tr>
                        <tr><td>TA/DA</td><td class="text-end">₹<?= number_format($structure['ta_da'] ?? 0, 0) ?></td></tr>
                        <tr><td>Other allowance</td><td class="text-end">₹<?= number_format($structure['other_allowance'] ?? 0, 0) ?></td></tr>
                        <tr><td>Incentive</td><td class="text-end"><?= htmlspecialchars($structure['incentive_type'] ?? '') ?> <?= htmlspecialchars($structure['incentive_value'] ?? '') ?></td></tr>
                        <tr><td>Effective from</td><td class="text-end"><?= htmlspecialchars($structure['effective_from'] ?? '') ?></td></tr>
                    </tbody></table></div>
                </div></div>
        </div>
        <div class="col-md-7 mb-4">
            <div class="card h-100"><div class="card-header fw-bold">This Month's Payroll</div>
                <div class="card-body">
                    <?php if (empty($payroll['success'])): ?>
                        <div class="alert alert-warning mb-0"><?= htmlspecialchars($payroll['error'] ?? 'Payroll unavailable') ?></div>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0"><tbody>
                        <tr><td>Fixed gross</td><td class="text-end">₹<?= number_format($payroll['gross_fixed'] ?? 0, 2) ?></td></tr>
                        <tr><td>Plots sold</td><td class="text-end"><?= (int)($payroll['plots_sold'] ?? 0) ?></td></tr>
                        <tr><td>Sale incentive</td><td class="text-end">₹<?= number_format($payroll['total_incentive'] ?? 0, 2) ?></td></tr>
                        <tr><td>TDS deducted</td><td class="text-end">₹<?= number_format($payroll['tds_deducted'] ?? 0, 2) ?></td></tr>
                        <tr class="table-success fw-bold"><td>Net payable</td><td class="text-end">₹<?= number_format($payroll['net_payable'] ?? 0, 2) ?></td></tr>
                    </tbody></table></div>
                    <?php endif; ?>
                </div></div>
        </div>
    </div>
    <?php if (!empty($history)): ?>
    <div class="card mb-4"><div class="card-header fw-bold">Revision History</div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr><th class="text-end">Basic</th><th class="text-end">HRA</th><th class="text-end">TA/DA</th><th class="text-end">Allow.</th><th class="text-center">Effective</th></tr></thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td class="text-end">₹<?= number_format($h['basic_salary'] ?? 0, 0) ?></td>
                    <td class="text-end">₹<?= number_format($h['hra'] ?? 0, 0) ?></td>
                    <td class="text-end">₹<?= number_format($h['ta_da'] ?? 0, 0) ?></td>
                    <td class="text-end">₹<?= number_format($h['other_allowance'] ?? 0, 0) ?></td>
                    <td class="text-center small"><?= htmlspecialchars($h['effective_from'] ?? '') ?> → <?= htmlspecialchars($h['effective_to'] ?? 'present') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div></div></div>
    <?php endif; ?>
    <?php endif; ?>
</div>
