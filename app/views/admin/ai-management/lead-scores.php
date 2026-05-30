<?php
$page_title = $page_title ?? 'AI Lead Scores';
$scores = $scores ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">AI Lead Scores</h1>
        <p class="text-muted mb-0">AI-predicted lead qualification scores</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Lead Scoring Results</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lead Name</th>
                        <th>Email</th>
                        <th>AI Score</th>
                        <th>Scored At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($scores)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No lead scores recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($scores as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['lead_name'] ?? 'Unknown') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($row['lead_phone'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['lead_email'] ?? '') ?></td>
                                <td>
                                    <?php $score = (int)($row['score'] ?? 0); ?>
                                    <span class="badge bg-<?= $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : ($score >= 30 ? 'info' : 'secondary')) ?> fs-6 px-3 py-2">
                                        <?= $score ?>/100
                                    </span>
                                </td>
                                <td><?= date('M j, Y H:i', strtotime($row['scored_at'] ?? 'now')) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/leads/show/<?= $row['lead_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
