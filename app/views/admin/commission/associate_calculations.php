ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-calculator"></i> Associate Commission Calculations</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-striped mb-0">
                <thead><tr><th>#</th><th>Associate</th><th>Property</th><th>Value</th><th>Level</th><th>%</th><th>Amount</th><th>Type</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if (empty($calculations ?? [])): ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="fas fa-calculator fa-3x text-muted mb-3" class="style-82835"></i>
                            <h5 class="text-muted">No calculations found</h5>
                            <p class="text-muted mb-3">Associate commission calculations are triggered automatically when bookings are confirmed.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($calculations as $c): ?>
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
                            <form method="POST" action="<?= BASE_URL ?>/admin/commission/associate/calc-status/<?= $c['id'] ?>" class="style-71727" onsubmit="return confirm('Confirm calculation #<?= $c['id'] ?>?')">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-sm btn-primary" aria-label="Confirm"><i class="fas fa-check"></i></button>
                            </form>
                            <?php elseif ($c['status'] == 'confirmed'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/commission/associate/calc-status/<?= $c['id'] ?>" class="style-71727" onsubmit="return confirm('Mark #<?= $c['id'] ?> as paid?')">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="status" value="paid">
                                <button type="submit" class="btn btn-sm btn-success" aria-label="Payment"><i class="fas fa-money-bill"></i></button>
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
