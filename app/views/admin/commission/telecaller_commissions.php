ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-phone-alt"></i> Telecaller Commissions</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-6"><div class="card bg-warning text-dark text-center p-2"><h5>&#8377;<?= number_format((float)($summary['pending']??0),2) ?></h5><small>Pending</small></div></div>
        <div class="col-md-6"><div class="card bg-success text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['total']??0),2) ?></h5><small>Total</small></div></div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Telecaller</th><th>Lead</th><th>Rule</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if (empty($commissions ?? [])): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-phone-alt fa-3x text-muted mb-3" class="style-82835"></i>
                            <h5 class="text-muted">No telecaller commissions yet</h5>
                            <p class="text-muted mb-3">Commissions are calculated automatically when telecallers complete qualifying calls or convert leads.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($commissions as $c): ?>
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
                            <form method="POST" action="<?= BASE_URL ?>/admin/commission/telecaller/commissions/approve/<?= $c['id'] ?>" class="style-71727" onsubmit="return confirm('Approve commission #<?= $c['id'] ?>?')">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i></button>
                            </form>
                            <?php elseif ($c['status'] == 'approved'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/commission/telecaller/commissions/pay/<?= $c['id'] ?>" class="style-71727" onsubmit="return confirm('Pay commission #<?= $c['id'] ?>?')">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-money-bill"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
