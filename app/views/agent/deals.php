<?php
$deals = $deals ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-handshake me-2"></i>My Deals</h4>
        <p class="text-muted mb-0">Track your bookings and closed deals</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success fs-6"><?= count($deals) ?> Deals</span>
    </div>
</div>

<div class="row mb-4">
    <?php
    $totalValue = 0;
    $confirmed = 0;
    $pending = 0;
    foreach ($deals as $d) {
        $totalValue += $d['total_amount'] ?? $d['booking_amount'] ?? 0;
        $s = $d['status'] ?? 'pending';
        if ($s === 'confirmed' || $s === 'completed') $confirmed++;
        else $pending++;
    }
    ?>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div >
                    <i class="fas fa-rupee-sign fa-lg"></i>
                </div>
                <h3 >₹<?= number_format($totalValue) ?></h3>
                <p class="text-muted mb-0">Total Deal Value</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div >
                    <i class="fas fa-check-circle fa-lg"></i>
                </div>
                <h3 ><?= e($confirmed) ?></h3>
                <p class="text-muted mb-0">Confirmed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div >
                    <i class="fas fa-clock fa-lg"></i>
                </div>
                <h3 ><?= e($pending) ?></h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($deals)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div >
            <i class="fas fa-handshake fa-2x"></i>
        </div>
        <h5 class="text-muted">No deals yet</h5>
        <p class="text-muted mb-0">Your bookings and deals will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead >
                    <tr>
                        <th class="px-3 py-3">Property</th>
                        <th class="px-3 py-3">Customer</th>
                        <th class="px-3 py-3">Amount</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deals as $deal): ?>
                    <tr>
                        <td class="px-3">
                            <div class="d-flex align-items-center">
                                <div >
                                    <i class="fas fa-home"></i>
                                </div>
                                <strong><?= htmlspecialchars($deal['property_title'] ?? 'Property #' . ($deal['plot_id'] ?? '')) ?></strong>
                            </div>
                        </td>
                        <td class="px-3"><?= htmlspecialchars($deal['customer_name'] ?? '-') ?></td>
                        <td class="px-3 fw-bold">₹<?= number_format($deal['total_amount'] ?? $deal['booking_amount'] ?? 0) ?></td>
                        <td class="px-3">
                            <?php
                            $status = $deal['status'] ?? 'pending';
                            $sClass = [
                                'confirmed' => 'bg-success',
                                'completed' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'cancelled' => 'bg-danger',
                            ];
                            ?>
                            <span class="badge <?= e($sClass[$status] ?? 'bg-secondary') ?>"><?= e(ucfirst($status)) ?></span>
                        </td>
                        <td class="px-3"><small class="text-muted"><?= date('d M Y', strtotime($deal['created_at'] ?? 'now')) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
