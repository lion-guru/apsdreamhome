<?php
/** @var array $agents */
$agents = $agents ?? [];
$base   = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-user-tie me-2"></i>Salaried Agents — Salary Structures</h4>
        <a href="<?= htmlspecialchars($base) ?>/admin/agents/salaried/create"
           class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Set Salary Structure
        </a>
    </div>

    <?php if (empty($agents)): ?>
        <div class="alert alert-info">
            No salaried agents found. Use the button above to assign a salary structure to an agent.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email / Phone</th>
                                <th class="text-end">Basic Salary</th>
                                <th class="text-end">HRA</th>
                                <th class="text-end">TA/DA</th>
                                <th class="text-end">Allowance</th>
                                <th class="text-center">Incentive</th>
                                <th class="text-center">Effective From</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agents as $a): ?>
                                <?php
                                $grossFixed = ((float)($a['basic_salary'] ?? 0))
                                           + ((float)($a['hra']           ?? 0))
                                           + ((float)($a['ta_da']         ?? 0))
                                           + ((float)($a['other_allowance'] ?? 0));
                                $incLabel = $a['incentive_type'] === 'percentage'
                                    ? number_format((float)$a['incentive_value'], 2) . '% of sale'
                                    : '₹' . number_format((float)$a['incentive_value'], 0) . '/plot';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($a['name'] ?? '') ?></strong><br>
                                        <span class="badge bg-success">Salaried</span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($a['email'] ?? '') ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($a['phone'] ?? '') ?></small>
                                    </td>
                                    <td class="text-end">₹<?= number_format((float)($a['basic_salary'] ?? 0), 0) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($a['hra'] ?? 0), 0) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($a['ta_da'] ?? 0), 0) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($a['other_allowance'] ?? 0), 0) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($incLabel) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($a['effective_from']) ? date('d M Y', strtotime($a['effective_from'])) : '—' ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= htmlspecialchars($base) ?>/admin/agents/salaried/<?= (int)$a['user_id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1"
                                           title="View Payroll">
                                            <i class="fas fa-calculator"></i>
                                        </a>
                                        <a href="<?= htmlspecialchars($base) ?>/admin/agents/salaried/create?user_id=<?= (int)$a['user_id'] ?>"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Revise Salary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
