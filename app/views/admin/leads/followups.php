<?php $page_title = 'Lead Follow-ups'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-phone-alt me-2"></i>Follow-ups</h2>
    <?php if (!empty($pending)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Due This Week (<?= count($pending) ?>)</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Lead</th><th>Phone</th><th>Due Date</th><th>Assigned</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($pending as $p): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/leads/show/<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></td>
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
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h6></div>
        <div class="card-body p-0">
            <?php if (empty($recent)): ?>
                <p class="text-muted text-center py-4">No recent activities</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date</th><th>Lead</th><th>Type</th><th>Description</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><small><?= date('d M H:i', strtotime($r['activity_date'] ?? $r['created_at'])) ?></small></td>
                                <td><?= htmlspecialchars($r['lead_name'] ?? 'Lead #'.$r['lead_id']) ?></td>
                                <td><span class="badge bg-<?= ($r['activity_type'] ?? '')==='call'?'success':(($r['activity_type'] ?? '')==='email'?'info':'primary') ?>"><?= ucfirst($r['activity_type'] ?? '') ?></span></td>
                                <td><small><?= htmlspecialchars(substr($r['description'] ?? '', 0, 60)) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
