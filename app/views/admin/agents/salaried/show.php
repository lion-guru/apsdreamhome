<?php
/** @var array $agent */ /** @var array|null $structure */ /** @var array $history */ /** @var array $payroll */
$agent = $agent ?? [];
$structure = $structure ?? null;
$history = $history ?? [];
$payroll = $payroll ?? ['success' => false, 'error' => 'No payroll data'];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-calculator me-2"></i><?= htmlspecialchars($page_title ?? 'Agent Payroll') ?></h4>
        <div>
            <a href="<?= htmlspecialchars($base) ?>/admin/agents/salaried/create?user_id=<?= (int)($userId ?? 0) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit me-1"></i>Revise Salary</a>
            <a href="<?= htmlspecialchars($base) ?>/admin/agents/salaried" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm"><div class="card-header bg-white fw-bold">Agent</div>
                <div class="card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($agent['name'] ?? '—') ?></strong></p>
                    <p class="mb-1 text-muted small"><?= htmlspecialchars($agent['email'] ?? '') ?></p>
                    <p class="mb-0"><span class="badge bg-success"><?= htmlspecialchars($agent['agent_type'] ?? 'salaried') ?></span></p>
                </div></div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm"><div class="card-header bg-white fw-bold">Current Month Payroll</div>
                <div class="card-body">
                    <?php if (empty($payroll['success'])): ?>
                        <div class="alert alert-warning mb-0"><?= htmlspecialchars($payroll['error'] ?? 'No active salary structure') ?></div>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <tbody>
                            <tr><td>Basic + HRA + TA/DA + Allowance</td><td class="text-end">₹<?= number_format($payroll['gross_fixed'] ?? 0, 2) ?></td></tr>
                            <tr><td>Plots sold (<?= (int)($payroll['month'] ?? 0) ?>/<?= (int)($payroll['year'] ?? 0) ?>)</td><td class="text-end"><?= (int)($payroll['plots_sold'] ?? 0) ?> (₹<?= number_format($payroll['total_sale_value'] ?? 0, 0) ?>)</td></tr>
                            <tr><td>Incentive (<?= htmlspecialchars($payroll['incentive_type'] ?? '') ?>)</td><td class="text-end">₹<?= number_format($payroll['total_incentive'] ?? 0, 2) ?></td></tr>
                            <tr><td>Gross total</td><td class="text-end">₹<?= number_format($payroll['gross_total'] ?? 0, 2) ?></td></tr>
                            <tr><td>TDS deducted</td><td class="text-end">₹<?= number_format($payroll['tds_deducted'] ?? 0, 2) ?></td></tr>
                            <tr class="table-success fw-bold"><td>Net payable</td><td class="text-end">₹<?= number_format($payroll['net_payable'] ?? 0, 2) ?></td></tr>
                        </tbody>
                    </table></div>
                    <?php endif; ?>
                </div></div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Structure History</div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr><th class="text-end">Basic</th><th class="text-end">HRA</th><th class="text-end">TA/DA</th><th class="text-end">Allow.</th><th class="text-center">Incentive</th><th class="text-center">Effective</th><th>Set By</th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?><tr><td colspan="7" class="text-center text-muted py-3">No structures yet</td></tr><?php endif; ?>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td class="text-end">₹<?= number_format($h['basic_salary'] ?? 0, 0) ?></td>
                    <td class="text-end">₹<?= number_format($h['hra'] ?? 0, 0) ?></td>
                    <td class="text-end">₹<?= number_format($h['ta_da'] ?? 0, 0) ?></td>
                    <td class="text-end">₹<?= number_format($h['other_allowance'] ?? 0, 0) ?></td>
                    <td class="text-center"><?= htmlspecialchars($h['incentive_type'] ?? '') ?> <?= htmlspecialchars($h['incentive_value'] ?? '') ?></td>
                    <td class="text-center small"><?= htmlspecialchars($h['effective_from'] ?? '') ?> → <?= htmlspecialchars($h['effective_to'] ?? 'present') ?></td>
                    <td><?= htmlspecialchars($h['set_by_name'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
