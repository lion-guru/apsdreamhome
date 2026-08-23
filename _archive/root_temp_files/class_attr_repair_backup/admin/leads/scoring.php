<?php $page_title = 'Lead Scoring'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-star me-2"></i>Lead Scoring</h2>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($scored)): ?>
                <p class="text-muted text-center py-4">No scored leads</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Score</th><th>Probability</th><th>Status</th><th>Assigned</th></tr></thead>
                        <tbody>
                        <?php foreach ($scored as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><a href="<?= BASE_URL ?>/admin/leads/show/<?= $s['id'] ?>"><?= htmlspecialchars($s['name'] ?? '') ?></a></td>
                                <td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
                                <td>
                                    <?php $score = $s['lead_score'] ?? 0; ?>
                                    <div class="progress style-48235">
                                        <div class="progress-bar bg-<?= $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger') ?>" class="style-83489"><?= $score ?></div>
                                    </div>
                                </td>
                                <td><?= $s['conversion_probability'] ?? 0 ?>%</td>
                                <td><span class="badge bg-<?= $s['status']==='new'?'primary':($s['status']==='converted'?'success':'secondary') ?>"><?= ucfirst($s['status']) ?></span></td>
                                <td><?= htmlspecialchars($s['assignee_name'] ?? 'Unassigned') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
