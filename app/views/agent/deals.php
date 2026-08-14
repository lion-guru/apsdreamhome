<?php
$deals = $deals ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" class="style-613"><i class="fas fa-handshake me-2"></i>My Deals</h4>
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
                <div class="style-38412">
                    <i class="fas fa-rupee-sign fa-lg" class="style-93945"></i>
                </div>
                <h3 class="style-613">â‚¹<?= number_format($totalValue) ?></h3>
                <p class="text-muted mb-0">Total Deal Value</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="style-20512">
                    <i class="fas fa-check-circle fa-lg" class="style-8693"></i>
                </div>
                <h3 class="style-46545"><?= $confirmed ?></h3>
                <p class="text-muted mb-0">Confirmed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="style-83109">
                    <i class="fas fa-clock fa-lg" class="style-44353"></i>
                </div>
                <h3 class="style-36030"><?= $pending ?></h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($deals)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="style-84169">
            <i class="fas fa-handshake fa-2x" class="style-93945"></i>
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
                <thead class="style-15087">
                    <tr>
                        <th class="px-3 py-3" class="style-83276">Property</th>
                        <th class="px-3 py-3" class="style-83276">Customer</th>
                        <th class="px-3 py-3" class="style-83276">Amount</th>
                        <th class="px-3 py-3" class="style-83276">Status</th>
                        <th class="px-3 py-3" class="style-83276">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deals as $deal): ?>
                    <tr>
                        <td class="px-3">
                            <div class="d-flex align-items-center">
                                <div class="style-37482">
                                    <i class="fas fa-home" class="style-10117"></i>
                                </div>
                                <strong><?= htmlspecialchars($deal['property_title'] ?? 'Property #' . ($deal['plot_id'] ?? '')) ?></strong>
                            </div>
                        </td>
                        <td class="px-3"><?= htmlspecialchars($deal['customer_name'] ?? '-') ?></td>
                        <td class="px-3 fw-bold" class="style-93945">â‚¹<?= number_format($deal['total_amount'] ?? $deal['booking_amount'] ?? 0) ?></td>
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
                            <span class="badge <?= $sClass[$status] ?? 'bg-secondary' ?>"><?= ucfirst($status) ?></span>
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
