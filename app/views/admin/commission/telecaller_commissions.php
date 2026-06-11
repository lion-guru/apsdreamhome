<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-phone-alt"></i> Telecaller Commissions</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-6"><div class="card bg-warning text-dark text-center p-2"><h5>&#8377;<?= number_format((float)($summary['pending']??0),2) ?></h5><small>Pending</small></div></div>
        <div class="col-md-6"><div class="card bg-success text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['total']??0),2) ?></h5><small>Total</small></div></div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Telecaller</th><th>Lead</th><th>Rule</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($commissions ?? [] as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['telecaller_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($c['lead_name'] ?? '#'.$c['lead_id']) ?></td>
                        <td><?= htmlspecialchars($c['rule_name'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $c['commission_type'] ?></span></td>
                        <td><strong>&#8377;<?= number_format((float)$c['commission_amount'],2) ?></strong></td>
                        <td>
                            <?php
                            $cls = $c['status']=='paid'?'success':($c['status']=='approved'?'primary':($c['status']=='rejected'?'danger':'warning'));
                            ?>
                            <span class="badge bg-<?= $cls ?>"><?= $c['status'] ?></span>
                        </td>
                        <td><?= date('d-m-Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <?php if ($c['status'] == 'pending'): ?>
                            <a href="/admin/commission/telecaller/commissions/approve/<?= $c['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Approve commission #<?= $c['id'] ?>?')"><i class="fas fa-check"></i></a>
                            <?php elseif ($c['status'] == 'approved'): ?>
                            <a href="/admin/commission/telecaller/commissions/pay/<?= $c['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Pay commission #<?= $c['id'] ?>?')"><i class="fas fa-money-bill"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
