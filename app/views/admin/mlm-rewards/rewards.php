<?php

$page_title = 'Reward History';
$rewards = $rewards ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-gift me-2"></i>Reward History</h1>
            <p class="text-muted">View all rewards given to users</p>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Reward History</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($rewards)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-gift fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No rewards recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Associate</th>
                                <th>Reward Type</th>
                                <th>Value</th>
                                <th>Date</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewards as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['associate_name'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <span class="badge bg-success"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $r['reward_type'] ?? ''))) ?></span>
                                </td>
                                <td>
                                    <?php if (is_numeric($r['reward_value'] ?? '')): ?>
                                    ₹<?= number_format(floatval($r['reward_value']), 2) ?>
                                    <?php else: ?>
                                    <?= htmlspecialchars($r['reward_value'] ?? 'N/A') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($r['reward_date'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
