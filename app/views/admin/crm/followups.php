<?php $page_title = 'Follow-ups'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-phone-alt me-2"></i>Follow-ups</h2>
    <?php if (!empty($pending)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark"><h2  class="fs-6 fw-semibold mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Due This Week (<?= count($pending) ?>)</h2></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th scope="col">Lead</th><th scope="col">Phone</th><th scope="col">Due Date</th><th scope="col">Assigned</th><th scope="col">Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($pending as $p): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/leads/show/<?= $p['id'] ?>"><?= htmlspecialchars($p['name'] ?? '') ?></a></td>
                                <td><?= htmlspecialchars($p['phone'] ?? '') ?></td>
                                <td><strong class="text-danger"><?= $p['next_activity_date'] ?></strong></td>
                                <td><?= htmlspecialchars($p['assignee_name'] ?? 'Unassigned') ?></td>
                                <td><span class="badge bg-warning text-dark"><?= ucfirst($p['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h2  class="fs-6 fw-semibold mb-0"><i class="fas fa-history me-2"></i>Recent Follow-up Activities</h2></div>
        <div class="card-body p-0">
            <?php if (empty($followups)): ?>
                <p class="text-muted text-center py-4">No follow-up activities recorded</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th scope="col">Date</th><th scope="col">Lead</th><th scope="col">Type</th><th scope="col">Description</th><th scope="col">By</th></tr></thead>
                        <tbody>
                        <?php foreach ($followups as $f): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($f['activity_date'] ?? $f['created_at'])) ?></td>
                                <td><a href="<?= BASE_URL ?>/admin/leads/show/<?= $f['lead_id'] ?>"><?= htmlspecialchars($f['lead_name'] ?? 'Lead #'.$f['lead_id']) ?></a></td>
                                <td><span class="badge bg-<?= $f['activity_type']==='call'?'success':($f['activity_type']==='email'?'info':'primary') ?>"><?= ucfirst($f['activity_type']) ?></span></td>
                                <td><?= htmlspecialchars(substr($f['description'], 0, 60)) ?></td>
                                <td><?= htmlspecialchars($f['assignee_name'] ?? 'System') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
