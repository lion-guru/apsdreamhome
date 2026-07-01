<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-calculator"></i> Associate Commission Calculations</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Associate</th><th>Property</th><th>Value</th><th>Level</th><th>%</th><th>Amount</th><th>Type</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($calculations ?? [] as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['associate_name'] ?? 'N/A') ?></td>
                        <td><?= (int)$c['property_id'] ?></td>
                        <td>&#8377;<?= number_format((float)$c['property_value']) ?></td>
                        <td><?= (int)$c['commission_level'] ?></td>
                        <td><?= (float)$c['commission_percentage'] ?>%</td>
                        <td><strong>&#8377;<?= number_format((float)$c['commission_amount'],2) ?></strong></td>
                        <td><span class="badge bg-info"><?= $c['commission_type'] ?></span></td>
                        <td>
                            <?php $cls = $c['status']=='paid'?'success':($c['status']=='confirmed'?'primary':'warning'); ?>
                            <span class="badge bg-<?= $cls ?>"><?= $c['status'] ?></span>
                        </td>
                        <td><?= date('d-m-Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <?php if ($c['status'] == 'pending'): ?>
                            <a href="/admin/commission/associate/calculations/status/<?= $c['id'] ?>/confirmed" class="btn btn-sm btn-primary" onclick="return confirm('Confirm calculation #<?= $c['id'] ?>?')"><i class="fas fa-check"></i></a>
                            <?php elseif ($c['status'] == 'confirmed'): ?>
                            <a href="/admin/commission/associate/calculations/status/<?= $c['id'] ?>/paid" class="btn btn-sm btn-success" onclick="return confirm('Mark #<?= $c['id'] ?> as paid?')"><i class="fas fa-money-bill"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
