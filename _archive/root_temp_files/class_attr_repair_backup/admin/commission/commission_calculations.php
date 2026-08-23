ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-file-invoice"></i> Commission Calculations (Resell users)</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Agent</th><th>Type</th><th>Rate</th><th>Base</th><th>Amount</th><th>Bonus</th><th>Final</th><th>Status</th><th>Paid</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if (empty($calculations ?? [])): ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3" class="style-82835"></i>
                            <h5 class="text-muted">No commission calculations found</h5>
                            <p class="text-muted mb-3">Calculations will appear here once commissions are generated from sales.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($calculations ?? [] as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['agent_name'] ?? 'N/A') ?></td>
                        <td><span class="badge bg-info"><?= $c['commission_type'] ?? '-' ?></span></td>
                        <td><?= (float)($c['commission_rate'] ?? 0) ?>%</td>
                        <td>&#8377;<?= number_format((float)($c['base_amount'] ?? 0)) ?></td>
                        <td>&#8377;<?= number_format((float)($c['commission_amount'] ?? 0),2) ?></td>
                        <td>&#8377;<?= number_format((float)($c['bonus_amount'] ?? 0),2) ?></td>
                        <td><strong>&#8377;<?= number_format((float)($c['final_commission'] ?? $c['commission_amount'] ?? 0),2) ?></strong></td>
                        <td>
                            <?php $s = $c['payment_status'] ?? 'pending'; ?>
                            <span class="badge bg-<?= $s=='paid'?'success':($s=='pending'?'warning':'secondary') ?>"><?= $s ?></span>
                        </td>
                        <td>&#8377;<?= number_format((float)($c['paid_amount'] ?? 0),2) ?></td>
                        <td><?= $c['created_at'] ? date('d-m-Y', strtotime($c['created_at'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
