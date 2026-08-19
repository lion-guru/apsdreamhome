<?php
/** @var array $booking */
/** @var array $eligibility */
$booking      = $booking ?? [];
$eligibility  = $eligibility ?? ['eligible' => false, 'reasons' => [], 'overdue_count' => 0, 'pending_amount' => 0, 'penalty_amount' => 0];
$csrf_token   = $csrf_token ?? '';
$base         = defined('BASE_URL') ? BASE_URL : '';
$isEligible   = $eligibility['eligible'] ?? false;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Registry / NOC Check</h4>
        <p class="text-muted mb-0">Booking #<?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></p>
    </div>
    <a href="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Booking
    </a>
</div>

<!-- Booking Summary Card -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-file-contract me-2"></i>Booking Details</span>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block">Customer</small>
                <strong><?= htmlspecialchars((string)($booking['customer_name'] ?? '—')) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Plot</small>
                <strong><?= htmlspecialchars((string)($booking['plot_number'] ?? '—')) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Colony</small>
                <strong><?= htmlspecialchars((string)($booking['colony_name'] ?? '—')) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Status</small>
                <span class="badge bg-<?= $isEligible ? 'success' : 'warning' ?>">
                    <?= htmlspecialchars((string)($booking['status'] ?? '')) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Eligibility Status Card -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-clipboard-check me-2"></i>Eligibility Status</span>
    </div>
    <div class="aps-cp-card-body">
        <?php if ($isEligible): ?>
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" class="style-22918"></i>
                </div>
                <h5 class="text-success">Eligible for Registry / NOC</h5>
                <p class="text-muted">All financial obligations have been met. You may proceed with NOC generation.</p>
                <button
                    id="btn-generate-noc"
                    class="btn btn-success btn-lg mt-2"
                    data-booking-id="<?= (int)($booking['id'] ?? 0) ?>"
                    data-csrf="<?= htmlspecialchars((string)$csrf_token) ?>"
                >
                    <i class="fas fa-file-signature me-2"></i>Generate NOC
                </button>
                <div id="noc-result" class="mt-3" class="style-54390"></div>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-times-circle text-danger" class="style-22918"></i>
                </div>
                <h5 class="text-danger">Not Eligible</h5>
                <p class="text-muted">This booking has unresolved financial obligations that block NOC / Registry generation.</p>
            </div>

            <!-- Blocking Reasons -->
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="style-69407">#</th>
                            <th>Blocking Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($eligibility['reasons'] ?? [])): ?>
                        <tr>
                            <td colspan="2" class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-muted mb-3" class="style-82835"></i>
                                <h5 class="text-muted">No blocking reasons</h5>
                                <p class="text-muted mb-3">There are no outstanding issues preventing registry or NOC generation.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach (($eligibility['reasons'] ?? []) as $i => $reason): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    <?= htmlspecialchars((string)$reason) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Stats -->
            <div class="row g-3 mt-3">
                <?php if (($eligibility['overdue_count'] ?? 0) > 0): ?>
                    <div class="col-md-4">
                        <div class="alert alert-danger mb-0">
                            <strong><?= (int)$eligibility['overdue_count'] ?></strong> Overdue Installment(s)
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (($eligibility['pending_amount'] ?? 0) > 0): ?>
                    <div class="col-md-4">
                        <div class="alert alert-warning mb-0">
                            <strong>₹<?= number_format($eligibility['pending_amount'], 2) ?></strong> Pending Amount
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (($eligibility['penalty_amount'] ?? 0) > 0): ?>
                    <div class="col-md-4">
                        <div class="alert alert-danger mb-0">
                            <strong>₹<?= number_format($eligibility['penalty_amount'], 2) ?></strong> Accrued Penalties
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isEligible): ?>
<script>
document.getElementById('btn-generate-noc')?.addEventListener('click', function() {
    const btn = this;
    const bookingId = btn.dataset.bookingId;
    const csrf = btn.dataset.csrf;
    const resultDiv = document.getElementById('noc-result');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';

    fetch('<?= $base ?>/admin/sales/bookings/' + bookingId + '/generate-noc', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + encodeURIComponent(csrf)
    })
    .then(r => r.json())
    .then(data => {
    .catch(err => console.error('Request failed:', err));
        if (data.success) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML =
                '<div class="alert alert-success">' +
                '<i class="fas fa-check-circle me-2"></i>' +
                '<strong>NOC Generated Successfully!</strong><br>' +
                'NOC Number: <strong>' + data.noc_number + '</strong><br>' +
                'Generated at: ' + data.generated_at +
                '</div>';
            btn.innerHTML = '<i class="fas fa-check me-2"></i>NOC Generated';
            btn.classList.replace('btn-success', 'btn-secondary');
        } else {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML =
                '<div class="alert alert-danger">' +
                '<i class="fas fa-times-circle me-2"></i>' +
                '<strong>Generation Failed</strong><br>' + (data.error || 'Unknown error') +
                '</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-signature me-2"></i>Generate NOC';
        }
    })
    .catch(() => {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-signature me-2"></i>Generate NOC';
    });
});
</script>
<?php endif; ?>
