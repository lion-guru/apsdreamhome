<?php
$salary = $salary ?? [];
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Salary History</h1>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Basic</th>
                            <th>HRA</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salary)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No salary records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($salary as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['month'] ?? '') ?></td>
                                    <td>&#8377; <?= htmlspecialchars(number_format((float)($s['basic'] ?? 0), 2)) ?></td>
                                    <td>&#8377; <?= htmlspecialchars(number_format((float)($s['hra'] ?? 0), 2)) ?></td>
                                    <td>&#8377; <?= htmlspecialchars(number_format((float)($s['allowances'] ?? 0), 2)) ?></td>
                                    <td>&#8377; <?= htmlspecialchars(number_format((float)($s['deductions'] ?? 0), 2)) ?></td>
                                    <td><strong>&#8377; <?= htmlspecialchars(number_format((float)($s['net_pay'] ?? 0), 2)) ?></strong></td>
                                    <td>
                                        <?php $st = $s['status'] ?? ''; ?>
                                        <?php if ($st === 'Paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif ($st === 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($st) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
