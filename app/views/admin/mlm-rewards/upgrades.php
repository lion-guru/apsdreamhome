<?php

$page_title = 'Rank Upgrades';
$upgrades = $upgrades ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-arrow-up me-2"></i>Rank Upgrades</h1>
            <p class="text-muted">Track associate rank upgrade history</p>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Upgrade History</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($upgrades)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-arrow-up fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No rank upgrades recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Associate</th>
                                <th>Old Rank</th>
                                <th>New Rank</th>
                                <th>Upgrade Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upgrades as $u): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($u['associate_name'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($u['old_rank'] ?? '')) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?= ucfirst(htmlspecialchars($u['new_rank'] ?? '')) ?></span>
                                    <i class="fas fa-arrow-right text-muted mx-1"></i>
                                </td>
                                <td><?= htmlspecialchars($u['upgrade_date'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
