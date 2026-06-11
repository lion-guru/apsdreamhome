<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-users me-2"></i>Agent Performance Report</h1>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Agent Rankings</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
                <a href="<?= BASE_URL ?>/admin/reports/export/agent-performance" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export</a>
            </div>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr>
                            <th>#</th><th>Agent Name</th><th>Listings</th><th>Inquiries</th><th>Deals Closed</th><th>Conversion</th><th>Revenue</th><th>Rating</th>
                        </tr></thead>
                        <tbody>
                            <?php $i=1; foreach ($users as $a): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($a['name'] ?? 'N/A') ?></strong><br><small class="text-muted"><?= htmlspecialchars($a['email'] ?? '') ?></small></td>
                                <td><span class="badge bg-info"><?= (int)($a['listings'] ?? 0) ?></span></td>
                                <td><span class="badge bg-warning"><?= (int)($a['inquiries_received'] ?? 0) ?></span></td>
                                <td><span class="badge bg-success"><?= (int)($a['deals_closed'] ?? 0) ?></span></td>
                                <td><?= ($a['conversion_rate'] ?? 0) ?>%</td>
                                <td>₹<?= number_format((float)($a['revenue'] ?? 0)) ?></td>
                                <td>
                                    <?php $rate = min(5, round(($a['deals_closed'] ?? 0) / max(1, ($a['listings'] ?? 1)) * 5, 1)); ?>
                                    <?php for ($s=1; $s<=5; $s++): ?>
                                        <i class="fas fa-star text-<?= $s <= $rate ? 'warning' : 'secondary' ?>"></i>
                                    <?php endfor; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No agent data available yet</h5>
                    <p class="text-muted">Agent performance will appear here once users start working on leads and deals.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
