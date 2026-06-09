<?php $page_title = 'Customer Feedback'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-star me-2"></i>Customer Feedback</h2>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-1"><?= number_format($avg_rating['avg_rating'] ?? 0, 1) ?> <i class="fas fa-star"></i></h3>
                    <small class="text-muted"><?= number_format($avg_rating['rated'] ?? 0) ?> rated tickets</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
                <p class="text-muted text-center py-4">No rated tickets yet</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>#</th><th>Subject</th><th>Customer</th><th>Rating</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['ticket_number'] ?? $t['id']) ?></td>
                                <td><?= htmlspecialchars(substr($t['subject'], 0, 40)) ?></td>
                                <td><?= htmlspecialchars($t['user_name'] ?? 'Unknown') ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $t['satisfaction_rating'] ? ' text-warning' : ' text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><span class="badge bg-<?= $t['status']==='resolved'?'success':'secondary' ?>"><?= ucfirst($t['status']) ?></span></td>
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
