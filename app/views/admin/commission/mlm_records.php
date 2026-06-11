<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-database"></i> MLM Commission Records</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Associate</th><th>Customer</th><th>Booking Amt</th><th>Total Comm.</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($records ?? [] as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['associate_name'] ?? 'N/A') ?></td>
                        <td>#<?= (int)$r['customer_id'] ?></td>
                        <td>&#8377;<?= number_format((float)$r['booking_amount']) ?></td>
                        <td><strong>&#8377;<?= number_format((float)$r['total_commission'],2) ?></strong></td>
                        <td>
                            <?php $c = $r['status']=='paid'?'success':($r['status']=='approved'?'primary':($r['status']=='cancelled'?'danger':'warning')); ?>
                            <span class="badge bg-<?= $c ?>"><?= $r['status'] ?></span>
                        </td>
                        <td><?= date('d-m-Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['status'] == 'calculated'): ?>
                            <a href="/admin/commission/mlm/records/status/<?= $r['id'] ?>/approved" class="btn btn-sm btn-primary" onclick="return confirm('Approve record #<?= $r['id'] ?>?')"><i class="fas fa-check"></i></a>
                            <?php elseif ($r['status'] == 'approved'): ?>
                            <a href="/admin/commission/mlm/records/status/<?= $r['id'] ?>/paid" class="btn btn-sm btn-success" onclick="return confirm('Mark #<?= $r['id'] ?> as paid?')"><i class="fas fa-money-bill"></i></a>
                            <?php endif; ?>
                            <?php if ($r['commission_details']): ?>
                            <button class="btn btn-sm btn-info" onclick="alert('<?= htmlspecialchars(substr($r['commission_details'],0,200)) ?>')"><i class="fas fa-eye"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
