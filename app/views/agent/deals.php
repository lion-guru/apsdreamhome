<?php
$deals = $deals ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:#15803d;font-weight:700;"><i class="fas fa-handshake me-2"></i>My Deals</h4>
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
                <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-rupee-sign fa-lg" style="color:#15803d;"></i>
                </div>
                <h3 style="color:#15803d;font-weight:700;">₹<?= number_format($totalValue) ?></h3>
                <p class="text-muted mb-0">Total Deal Value</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div style="width:56px;height:56px;border-radius:50%;background:#dbeafe;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-check-circle fa-lg" style="color:#2563eb;"></i>
                </div>
                <h3 style="color:#2563eb;font-weight:700;"><?= $confirmed ?></h3>
                <p class="text-muted mb-0">Confirmed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div style="width:56px;height:56px;border-radius:50%;background:#fef3c7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-clock fa-lg" style="color:#d97706;"></i>
                </div>
                <h3 style="color:#d97706;font-weight:700;"><?= $pending ?></h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($deals)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-handshake fa-2x" style="color:#15803d;"></i>
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
                <thead style="background:#f0fdf4;">
                    <tr>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Property</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Customer</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Amount</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Status</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deals as $deal): ?>
                    <tr>
                        <td class="px-3">
                            <div class="d-flex align-items-center">
                                <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#15803d,#22c55e);color:#fff;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                    <i class="fas fa-home" style="font-size:.8rem;"></i>
                                </div>
                                <strong><?= htmlspecialchars($deal['property_title'] ?? 'Property #' . ($deal['plot_id'] ?? '')) ?></strong>
                            </div>
                        </td>
                        <td class="px-3"><?= htmlspecialchars($deal['customer_name'] ?? '-') ?></td>
                        <td class="px-3 fw-bold" style="color:#15803d;">₹<?= number_format($deal['total_amount'] ?? $deal['booking_amount'] ?? 0) ?></td>
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
