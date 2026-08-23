<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Leadership Salary Tracker</h1>
        <div>
            <span class="badge bg-success fs-6 me-2"><?= $active_count ?? 0 ?> Active</span>
            <span class="badge bg-info fs-6">₹<?= number_format((float)($total_monthly ?? 0), 2) ?>/mo</span>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>User</th><th>Target Volume</th><th>Achieved In</th><th>Monthly Payout</th><th>Duration</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($trackers ?? [])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3" class="style-82835"></i>
                                <h5 class="text-muted">No salary trackers found</h5>
                                <p class="text-muted mb-3">Leadership salary tracking records will appear here once targets are set.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($trackers as $t): ?>
                        <tr>
                            <td>#<?= $t['id'] ?></td>
                            <td><strong><?= htmlspecialchars($t['user_name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($t['email'] ?? '') ?></small></td>
                            <td>₹<?= number_format((float)$t['target_volume'], 2) ?></td>
                            <td><?= $t['achieved_in_days'] ? $t['achieved_in_days'] . ' days' : '-' ?></td>
                            <td><strong>₹<?= number_format((float)$t['monthly_payout'], 2) ?></strong></td>
                            <td><?= $t['duration_months'] ?> months</td>
                            <td><?= htmlspecialchars($t['start_date'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($t['end_date'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $t['status'] === 'active' ? 'success' : ($t['status'] === 'completed' ? 'primary' : 'secondary') ?>"><?= htmlspecialchars($t['status'] ?? '') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>