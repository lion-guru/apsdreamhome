<?php
$page_title = $page_title ?? 'Digital Passbook';
$current_page = 'passbook';
$bookings = $bookings ?? [];
$activeBookingId = $activeBookingId ?? null;
$ledger = $ledger ?? [];
$stats = $stats ?? [
    'total_investment' => 0,
    'total_paid' => 0,
    'total_outstanding' => 0,
    'next_emi_date' => null,
    'total_installments' => 0,
    'paid_installments' => 0,
];
$paymentSchedules = $paymentSchedules ?? [];

// PHP fallback for Indian currency formatting. The view calls formatINR()
// but only a JS namesake existed, so any customer WITH bookings hit a fatal.
// Defined once (function_exists guard) — additive, no other file touched.
if (!function_exists('formatINR')) {
    function formatINR($amount) {
        $amount = (float)$amount;
        $decimals = ($amount == (int)$amount) ? 0 : 2;
        $num = number_format($amount, $decimals, '.', '');
        $parts = explode('.', $num);
        $int = $parts[0];
        $neg = '';
        if ($int !== '' && $int[0] === '-') {
            $neg = '-';
            $int = substr($int, 1);
        }
        if (strlen($int) > 3) {
            $last3 = substr($int, -3);
            $rest = substr($int, 0, -3);
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $int = $rest . ',' . $last3;
        }
        return '₹' . $neg . $int . (isset($parts[1]) ? '.' . $parts[1] : '');
    }
}

$statusBadgeMap = [
    'paid' => 'success',
    'completed' => 'success',
    'pending' => 'warning',
    'overdue' => 'danger',
    'partial' => 'info',
    'processing' => 'secondary',
    'failed' => 'danger',
];
?>

<style>
    .pb-hero {
        background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%);
        border-radius: 16px;
        padding: 2rem 1.5rem;
        color: #fff;
        margin-bottom: 1.5rem;
    }
    .pb-hero h2 { font-size: 1.35rem; font-weight: 700; margin-bottom: .25rem; }
    .pb-hero p { opacity: .8; font-size: .85rem; margin-bottom: 0; }

    .pb-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .pb-stat-card {
        background: rgba(255,255,255,.1);
        backdrop-filter: blur(8px);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        border: 1px solid rgba(255,255,255,.15);
        transition: transform .2s;
    }
    .pb-stat-card:hover { transform: translateY(-2px); }
    .pb-stat-card .pb-stat-icon {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .85rem; margin-bottom: .5rem;
    }
    .pb-stat-card .pb-stat-value { font-size: 1.15rem; font-weight: 700; line-height: 1.3; }
    .pb-stat-card .pb-stat-label { font-size: .7rem; opacity: .8; text-transform: uppercase; letter-spacing: .5px; }

    .pb-stat-icon.blue   { background: rgba(59,130,246,.3); color: #93c5fd; }
    .pb-stat-icon.green  { background: rgba(34,197,94,.3);  color: #86efac; }
    .pb-stat-icon.amber  { background: rgba(245,158,11,.3); color: #fcd34d; }
    .pb-stat-icon.teal   { background: rgba(20,184,166,.3); color: #5eead4; }

    .pb-progress-wrap {
        background: rgba(255,255,255,.15);
        border-radius: 8px;
        height: 8px;
        overflow: hidden;
    }
    .pb-progress-bar {
        height: 100%;
        border-radius: 8px;
        background: linear-gradient(90deg, #22c55e, #10b981);
        transition: width .6s ease;
    }
    .pb-progress-label {
        font-size: .75rem;
        opacity: .85;
        margin-top: .35rem;
        text-align: right;
    }

    .pb-tabs-wrap {
        display: flex;
        gap: .5rem;
        overflow-x: auto;
        padding-bottom: .5rem;
        margin-bottom: 1.25rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .pb-tabs-wrap::-webkit-scrollbar { height: 4px; }
    .pb-tabs-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .pb-tab {
        flex: 0 0 auto;
        padding: .6rem 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: .8rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: all .2s;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }
    .pb-tab:hover { border-color: #0d9488; color: #0d9488; }
    .pb-tab.active {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(13,148,136,.35);
    }
    .pb-tab .pb-tab-plot { font-weight: 700; }

    .pb-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .pb-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pb-card-header h5 {
        font-size: .95rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .pb-card-body { padding: 1.25rem; }

    .pb-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: .75rem;
    }
    .pb-info-item small { display: block; color: #94a3b8; font-size: .7rem; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 2px; }
    .pb-info-item strong { font-size: .85rem; color: #1e293b; }

    .pb-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    .pb-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        padding: .6rem .75rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .pb-table tbody td {
        padding: .65rem .75rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    .pb-table tbody tr:hover { background: #f8fafc; }
    .pb-table tbody tr:last-child td { border-bottom: none; }

    .pb-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: .68rem;
        font-weight: 600;
        text-transform: capitalize;
        line-height: 1.6;
    }
    .pb-badge-success { background: #dcfce7; color: #166534; }
    .pb-badge-warning { background: #fef3c7; color: #92400e; }
    .pb-badge-danger  { background: #fee2e2; color: #991b1b; }
    .pb-badge-info    { background: #dbeafe; color: #1e40af; }
    .pb-badge-secondary { background: #f1f5f9; color: #64748b; }

    .pb-btn {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: .72rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
    }
    .pb-btn-pay {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        color: #fff;
    }
    .pb-btn-pay:hover { background: linear-gradient(135deg, #0f766e, #115e59); color: #fff; transform: translateY(-1px); box-shadow: 0 2px 6px rgba(13,148,136,.35); }
    .pb-btn-receipt {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .pb-btn-receipt:hover { background: #e2e8f0; color: #1e293b; }

    .pb-empty {
        text-align: center;
        padding: 3rem 1.5rem;
    }
    .pb-empty-icon {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #94a3b8;
        margin-bottom: 1rem;
    }
    .pb-empty h5 { color: #1e293b; font-weight: 700; margin-bottom: .4rem; }
    .pb-empty p { color: #64748b; font-size: .85rem; margin-bottom: 1rem; }

    /* Modal */
    .pb-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        backdrop-filter: blur(4px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .pb-modal-overlay.show { display: flex; }
    .pb-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        overflow: hidden;
        animation: pbModalIn .25s ease;
    }
    @keyframes pbModalIn {
        from { opacity: 0; transform: scale(.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .pb-modal-header {
        background: linear-gradient(135deg, #0a192f, #1e3a5f);
        color: #fff;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pb-modal-header h5 { margin: 0; font-size: .95rem; font-weight: 700; }
    .pb-modal-close {
        background: rgba(255,255,255,.15);
        border: none;
        color: #fff;
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .75rem;
    }
    .pb-modal-close:hover { background: rgba(255,255,255,.25); }
    .pb-modal-body { padding: 1.25rem; text-align: center; }
    .pb-modal-body .pb-emi-info { font-size: .8rem; color: #64748b; margin-bottom: 1rem; }
    .pb-modal-body .pb-emi-info strong { color: #1e293b; font-size: .95rem; }
    .pb-modal-body .pb-emi-amount { font-size: 1.8rem; font-weight: 800; color: #0d9488; margin-bottom: 1rem; }
    .pb-modal-qr {
        width: 180px; height: 180px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        overflow: hidden;
        background: #fff;
    }
    .pb-modal-qr img { width: 100%; height: 100%; object-fit: contain; }
    .pb-modal-qr .pb-qr-loading { color: #94a3b8; font-size: .8rem; }
    .pb-upi-btns { display: flex; gap: .75rem; justify-content: center; margin-top: .75rem; }
    .pb-upi-btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .5rem 1rem;
        border-radius: 10px;
        font-size: .8rem;
        font-weight: 600;
        text-decoration: none;
        color: #fff;
        transition: transform .2s;
    }
    .pb-upi-btn:hover { transform: translateY(-2px); color: #fff; }
    .pb-upi-gpay { background: #4285f4; }
    .pb-upi-phonepe { background: #5f259f; }
    .pb-upi-paytm { background: #00b9f5; }

    .pb-scroll-table { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    @media (max-width: 768px) {
        .pb-hero { padding: 1.25rem 1rem; }
        .pb-hero h2 { font-size: 1.1rem; }
        .pb-stat-grid { grid-template-columns: repeat(2, 1fr); gap: .6rem; }
        .pb-stat-card { padding: .75rem .5rem; }
        .pb-stat-card .pb-stat-value { font-size: .95rem; }
        .pb-stat-card .pb-stat-label { font-size: .62rem; }
        .pb-info-grid { grid-template-columns: repeat(2, 1fr); }
        .pb-card-body { padding: 1rem; }
        .pb-table { font-size: .72rem; }
        .pb-table thead th,
        .pb-table tbody td { padding: .5rem; }
        .pb-modal { max-width: 95vw; margin: 0 .5rem; }
        .pb-upi-btns { flex-direction: column; align-items: center; }
    }
    @media (max-width: 480px) {
        .pb-stat-grid { grid-template-columns: 1fr 1fr; }
        .pb-card-header { padding: .75rem 1rem; }
        .pb-card-header h5 { font-size: .85rem; }
    }
</style>

<?php if (empty($bookings)): ?>
<div class="pb-card">
    <div class="pb-card-body">
        <div class="pb-empty">
            <div class="pb-empty-icon"><i class="fas fa-book-open"></i></div>
            <h5>No bookings yet</h5>
            <p>You don't have any plot bookings. Explore our colonies to find your dream plot.</p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary px-4 py-2">
                <i class="fas fa-search me-2"></i>Explore Properties
            </a>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Hero Summary Card -->
<div class="pb-hero">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2><i class="fas fa-book me-2"></i>Digital Passbook</h2>
            <p>Your complete payment history and booking summary</p>
        </div>
        <div class="text-end d-none d-md-block">
            <small style="opacity:.6;">Last updated</small><br>
            <small style="font-weight:600;"><?= date('d M Y, h:i A') ?></small>
        </div>
    </div>

    <div class="pb-stat-grid">
        <div class="pb-stat-card">
            <div class="pb-stat-icon blue"><i class="fas fa-coins"></i></div>
            <div class="pb-stat-value" id="pb-total-investment"><?= formatINR($stats['total_investment']) ?></div>
            <div class="pb-stat-label">Total Investment</div>
        </div>
        <div class="pb-stat-card">
            <div class="pb-stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="pb-stat-value" id="pb-total-paid"><?= formatINR($stats['total_paid']) ?></div>
            <div class="pb-stat-label">Total Paid</div>
        </div>
        <div class="pb-stat-card">
            <div class="pb-stat-icon amber"><i class="fas fa-hourglass-half"></i></div>
            <div class="pb-stat-value" id="pb-outstanding"><?= formatINR($stats['total_outstanding']) ?></div>
            <div class="pb-stat-label">Outstanding</div>
        </div>
        <div class="pb-stat-card">
            <div class="pb-stat-icon teal"><i class="fas fa-calendar-alt"></i></div>
            <div class="pb-stat-value" id="pb-next-emi">
                <?php if (!empty($stats['next_emi_date'])): ?>
                    <?= date('d M', strtotime($stats['next_emi_date'])) ?>
                <?php else: ?>
                    All Paid
                <?php endif; ?>
            </div>
            <div class="pb-stat-label">Next EMI</div>
        </div>
    </div>

    <?php
    $payPct = $stats['total_investment'] > 0 ? round(($stats['total_paid'] / $stats['total_investment']) * 100, 1) : 0;
    ?>
    <div class="pb-progress-wrap">
        <div class="pb-progress-bar" style="width: <?= min($payPct, 100) ?>%"></div>
    </div>
    <div class="pb-progress-label"><?= $payPct ?>% paid <?= $stats['paid_installments'] ?> of <?= $stats['total_installments'] ?> installments</div>
</div>

<!-- Booking Tabs -->
<?php if (count($bookings) > 1): ?>
<div class="pb-tabs-wrap" id="pb-tabs">
    <?php foreach ($bookings as $b): ?>
        <a class="pb-tab <?= (int)($b['id'] ?? 0) === (int)$activeBookingId ? 'active' : '' ?>"
            href="<?= BASE_URL ?>/customer/passbook?booking_id=<?= (int)($b['id'] ?? 0) ?>">
            <i class="fas fa-map-pin"></i>
            <span class="pb-tab-plot"><?= htmlspecialchars($b['plot_number'] ?? '') ?></span>
            <span><?= htmlspecialchars($b['colony_name'] ?? '') ?></span>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
$activeBooking = null;
foreach ($bookings as $b) {
    if ((int)($b['id'] ?? 0) === (int)$activeBookingId) {
        $activeBooking = $b;
        break;
    }
}
if (!$activeBooking && !empty($bookings)) {
    $activeBooking = $bookings[0];
}
?>

<?php if ($activeBooking): ?>
<!-- Active Booking Details Card -->
<div class="pb-card">
    <div class="pb-card-header">
        <h5><i class="fas fa-map-marker-alt text-primary"></i> Plot Details</h5>
        <span class="pb-badge pb-badge-<?= ($activeBooking['status'] ?? '') === 'fully_paid' ? 'success' : (($activeBooking['status'] ?? '') === 'emi_active' ? 'warning' : 'info') ?>">
            <?= htmlspecialchars(str_replace('_', ' ', $activeBooking['status'] ?? 'active')) ?>
        </span>
    </div>
    <div class="pb-card-body">
        <div class="pb-info-grid">
            <div class="pb-info-item">
                <small>Plot Number</small>
                <strong><?= htmlspecialchars($activeBooking['plot_number'] ?? '-') ?></strong>
            </div>
            <div class="pb-info-item">
                <small>Block</small>
                <strong><?= htmlspecialchars($activeBooking['block'] ?? '-') ?></strong>
            </div>
            <div class="pb-info-item">
                <small>Colony</small>
                <strong><?= htmlspecialchars($activeBooking['colony_name'] ?? '-') ?></strong>
            </div>
            <div class="pb-info-item">
                <small>Dimensions</small>
                <strong><?= htmlspecialchars($activeBooking['width_ft'] ?? '-') ?> x <?= htmlspecialchars($activeBooking['length_ft'] ?? '-') ?> ft</strong>
            </div>
            <div class="pb-info-item">
                <small>Area</small>
                <strong><?= htmlspecialchars($activeBooking['area_sqft'] ?? '-') ?> sqft</strong>
            </div>
            <div class="pb-info-item">
                <small>Facing</small>
                <strong><?= htmlspecialchars($activeBooking['facing'] ?? '-') ?></strong>
            </div>
            <div class="pb-info-item">
                <small>Booking Number</small>
                <strong><?= htmlspecialchars($activeBooking['booking_number'] ?? '-') ?></strong>
            </div>
            <div class="pb-info-item">
                <small>Total Value</small>
                <strong class="text-primary"><?= formatINR($activeBooking['total_plot_value'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="mt-3 d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/customer/registry/<?= (int)($activeBooking['id'] ?? 0) ?>" class="pb-btn pb-btn-pay" style="padding:.55rem 1.1rem;font-size:.8rem;">
                <i class="fas fa-stamp"></i> Track Registry Status
            </a>
        </div>
    </div>
</div>

<!-- Payment Schedule Card -->
<div class="pb-card">
    <div class="pb-card-header">
        <h5><i class="fas fa-calendar-check text-success"></i> Payment Schedule</h5>
        <small class="text-muted"><?= count($paymentSchedules) ?> installments</small>
    </div>
    <div class="pb-card-body" style="padding:.5rem;">
        <div class="pb-scroll-table">
            <table class="pb-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                        <th class="text-end">Paid Amt</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paymentSchedules)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            <i class="fas fa-info-circle me-1"></i> No payment schedule found for this booking.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($paymentSchedules as $i => $sch): ?>
                            <?php
                            $schStatus = strtolower($sch['status'] ?? 'pending');
                            $badgeClass = $statusBadgeMap[$schStatus] ?? 'secondary';
                            $isOverdue = $schStatus === 'pending' && !empty($sch['due_date']) && strtotime($sch['due_date']) < time();
                            if ($isOverdue) { $badgeClass = 'danger'; $schStatus = 'overdue'; }
                            ?>
                            <tr>
                                <td class="fw-bold"><?= (int)($sch['installment_no'] ?? $sch['installment_number'] ?? ($i + 1)) ?></td>
                                <td><?= !empty($sch['due_date']) ? date('d M Y', strtotime($sch['due_date'])) : '-' ?></td>
                                <td class="text-end fw-bold"><?= formatINR($sch['amount'] ?? 0) ?></td>
                                <td><span class="pb-badge pb-badge-<?= $badgeClass ?>"><?= ucfirst($schStatus) ?></span></td>
                                <td><?= !empty($sch['paid_date']) ? date('d M Y', strtotime($sch['paid_date'])) : '-' ?></td>
                                <td class="text-end"><?= !empty($sch['paid_amount']) ? formatINR($sch['paid_amount']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($schStatus === 'pending' || $schStatus === 'overdue'): ?>
                                        <button class="pb-btn pb-btn-pay" onclick="openPayModal(<?= (int)($sch['installment_no'] ?? $sch['installment_number'] ?? ($i + 1)) ?>, <?= (float)($sch['amount'] ?? 0) ?>, <?= (int)($activeBooking['id'] ?? 0) ?>)">
                                            <i class="fas fa-credit-card"></i> Pay Now
                                        </button>
                                    <?php elseif ($schStatus === 'paid'): ?>
                                        <button class="pb-btn pb-btn-receipt" onclick="downloadReceipt(<?= (int)($sch['id'] ?? 0) ?>)">
                                            <i class="fas fa-download"></i> Receipt
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.7rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transaction Ledger Card -->
<div class="pb-card">
    <div class="pb-card-header">
        <h5><i class="fas fa-receipt text-info"></i> Transaction Ledger</h5>
        <small class="text-muted"><?= count($ledger) ?> entries</small>
    </div>
    <div class="pb-card-body" style="padding:.5rem;">
        <div class="pb-scroll-table">
            <table class="pb-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ledger)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="fas fa-info-circle me-1"></i> No transactions recorded yet.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($ledger as $entry): ?>
                            <?php
                            $entryStatus = strtolower($entry['status'] ?? 'completed');
                            $entryBadge = $statusBadgeMap[$entryStatus] ?? 'secondary';
                            ?>
                            <tr>
                                <td><?= !empty($entry['date']) ? date('d M Y', strtotime($entry['date'])) : '-' ?></td>
                                <td><?= htmlspecialchars($entry['description'] ?? '') ?></td>
                                <td class="text-end fw-bold"><?= formatINR($entry['amount'] ?? 0) ?></td>
                                <td>
                                    <span class="pb-badge pb-badge-secondary">
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $entry['payment_mode'] ?? '-'))) ?>
                                    </span>
                                </td>
                                <td><span class="pb-badge pb-badge-<?= $entryBadge ?>"><?= ucfirst($entryStatus) ?></span></td>
                                <td class="text-center">
                                    <?php if (strtolower($entry['status'] ?? '') === 'completed' || strtolower($entry['status'] ?? '') === 'paid'): ?>
                                        <button class="pb-btn pb-btn-receipt" onclick="downloadReceipt(<?= (int)($entry['id'] ?? 0) ?>)">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.7rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>
<?php endif; ?>

<!-- Pay EMI Modal -->
<div class="pb-modal-overlay" id="pbPayModal">
    <div class="pb-modal">
        <div class="pb-modal-header">
            <h5><i class="fas fa-credit-card me-2"></i>Pay EMI Installment</h5>
            <button class="pb-modal-close" onclick="closePayModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="pb-modal-body">
            <div class="pb-emi-info">
                Plot <strong id="pb-modal-plot"></strong> &middot; Installment <strong id="pb-modal-installment">#</strong>
            </div>
            <div class="pb-emi-amount" id="pb-modal-amount">₹0</div>

            <div class="pb-modal-qr" id="pb-qr-container">
                <span class="pb-qr-loading"><i class="fas fa-spinner fa-spin me-1"></i> Loading QR...</span>
            </div>

            <div class="pb-upi-btns">
                <a href="#" class="pb-upi-btn pb-upi-gpay" id="pb-gpay-link" target="_blank" rel="noopener">
                    <i class="fab fa-google-pay fa-lg"></i> GPay
                </a>
                <a href="#" class="pb-upi-btn pb-upi-phonepe" id="pb-phonepe-link" target="_blank" rel="noopener">
                    <i class="fas fa-mobile-alt"></i> PhonePe
                </a>
                <a href="#" class="pb-upi-btn pb-upi-paytm" id="pb-paytm-link" target="_blank" rel="noopener">
                    <i class="fas fa-wallet"></i> Paytm
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    /* Indian currency formatting */
    window.formatINR = function(num) {
        num = parseFloat(num) || 0;
        var s = Math.round(num).toString();
        var lastThree = s.substring(s.length - 3);
        var other = s.substring(0, s.length - 3);
        if (other !== '') {
            lastThree = ',' + lastThree;
        }
        var formatted = other.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;
        return '\u20B9' + formatted;
    };

    var modal = document.getElementById('pbPayModal');
    var currentBookingId = <?= (int)($activeBookingId ?? 0) ?>;
    var csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;

    window.openPayModal = function(instNo, amount, bookingId) {
        document.getElementById('pb-modal-plot').textContent = <?= json_encode(htmlspecialchars($activeBooking['plot_number'] ?? '')) ?>;
        document.getElementById('pb-modal-installment').textContent = '#' + instNo;
        document.getElementById('pb-modal-amount').textContent = formatINR(amount);

        var qrContainer = document.getElementById('pb-qr-container');
        qrContainer.innerHTML = '<span class="pb-qr-loading"><i class="fas fa-spinner fa-spin me-1"></i> Loading QR...</span>';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        /* Fetch UPI QR from API */
        fetch(<?= json_encode(BASE_URL) ?> + '/customer/pay-emi/upi-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: 'booking_id=' + encodeURIComponent(bookingId) +
                  '&installment_no=' + encodeURIComponent(instNo) +
                  '&amount=' + encodeURIComponent(amount) +
                  '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            if (data.success && data.qr_image) {
                qrContainer.innerHTML = '<img src="' + data.qr_image + '" alt="UPI QR Code">';
                if (data.upi_link) {
                    document.getElementById('pb-gpay-link').href = data.upi_link.replace('upi://', 'upi://') + '&pa=' + encodeURIComponent(data.upi_id || '') + '&pn=' + encodeURIComponent('APS Dream Home');
                    document.getElementById('pb-phonepe-link').href = data.upi_link;
                    document.getElementById('pb-paytm-link').href = data.upi_link;
                }
                if (data.upi_id) {
                    var upiBase = 'upi://pay?pa=' + encodeURIComponent(data.upi_id) + '&pn=' + encodeURIComponent('APS Dream Home') + '&am=' + amount + '&cu=INR';
                    document.getElementById('pb-gpay-link').href = 'gpay://upi/' + upiBase.substring(6);
                    document.getElementById('pb-phonepe-link').href = 'phonepe://pay/' + upiBase.substring(6);
                    document.getElementById('pb-paytm-link').href = 'paytmmp://pay?' + upiBase.substring(11);
                }
            } else {
                qrContainer.innerHTML = '<span class="pb-qr-loading text-danger"><i class="fas fa-exclamation-triangle me-1"></i> ' + (data.error || 'Unable to generate QR') + '</span>';
            }
        })
        .catch(function() {
            qrContainer.innerHTML = '<span class="pb-qr-loading text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Network error</span>';
        });
    };

    window.closePayModal = function() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closePayModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) closePayModal();
    });

    window.downloadReceipt = function(receiptId) {
        if (!receiptId) return;
        window.open(<?= json_encode(BASE_URL) ?> + '/customer/receipt/' + encodeURIComponent(receiptId), '_blank');
    };

    /* Animate stat values on load */
    document.querySelectorAll('.pb-stat-value').forEach(function(el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(8px)';
        setTimeout(function() {
            el.style.transition = 'opacity .4s ease, transform .4s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 150);
    });
})();
</script>
