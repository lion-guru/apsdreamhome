<?php
/** @var array $clawbacks */
$clawbacks = $clawbacks ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'pending'  => 'bg-warning text-dark',
        'partial'  => 'bg-info',
        'recovered'=> 'bg-success',
        'waived'   => 'bg-secondary',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-undo-alt me-2"></i>Clawback Log</h5>
        <form method="POST" action="<?= $base ?>/admin/mlm/clawbacks/process" class="d-inline" data-aps-confirm="Process clawbacks for all 30+ day overdue installments? This will debit associate wallets.">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-play me-1"></i>Process Clawbacks Now</button>
        </form>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive"><table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Beneficiary</th>
                    <th>Source</th>
                    <th>EMI Installment</th>
                    <th class="text-end">Original</th>
                    <th class="text-end">Clawback</th>
                    <th class="text-end">Recovered</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clawbacks)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-3">No clawbacks yet</td></tr>
                <?php else: foreach ($clawbacks as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($c['created_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($c['beneficiary_name'] ?? '#'.($c['beneficiary_user_id'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars((string)($c['source_name'] ?? '#'.($c['source_user_id'] ?? ''))) ?></td>
                        <td>#<?= (int)($c['emi_installment_id'] ?? 0) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($c['original_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($c['clawback_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">&#8377;<?= number_format((float)($c['recovered_amount'] ?? 0), 2) ?></td>
                        <td><span class="badge <?= $statusBadge($c['status'] ?? '') ?>"><?= htmlspecialchars((string)($c['status'] ?? '')) ?></span></td>
                        <td><small class="text-muted"><?= htmlspecialchars((string)($c['reason'] ?? '')) ?></small></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </div>
</div>
