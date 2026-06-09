<?php $page_title = 'Support Tickets'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-headset me-2"></i>Support Tickets</h2>
    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= $stats['total'] ?></h3><small class="text-muted">Total</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-warning"><?= $stats['open'] ?></h3><small class="text-muted">Open</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-info"><?= $stats['in_progress'] ?></h3><small class="text-muted">In Progress</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success"><?= $stats['resolved'] ?></h3><small class="text-muted">Resolved</small></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
                <p class="text-muted text-center py-4">No support tickets yet</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Subject</th><th>Category</th><th>Customer</th><th>Assigned</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['ticket_number'] ?? $t['id']) ?></td>
                                <td><?= htmlspecialchars(substr($t['subject'], 0, 40)) ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($t['category'] ?? 'General') ?></span></td>
                                <td><?= htmlspecialchars($t['user_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($t['assignee_name'] ?? 'Unassigned') ?></td>
                                <td><span class="badge bg-<?= ($t['priority'] ?? 'medium')==='high'?'danger':(($t['priority'] ?? 'medium')==='low'?'secondary':'warning') ?>"><?= ucfirst($t['priority'] ?? 'medium') ?></span></td>
                                <td><span class="badge bg-<?= $t['status']==='open'?'warning':($t['status']==='resolved'?'success':($t['status']==='in_progress'?'info':'secondary')) ?>"><?= ucfirst($t['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
