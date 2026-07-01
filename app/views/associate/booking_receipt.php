<?php
/**
 * Associate Booking Receipt Page
 */
$page_title = $page_title ?? 'Booking Receipt';
$current_page = 'my-bookings';
$booking = $booking ?? null;
$receipts = $receipts ?? [];
?>

<?php if (!$booking): ?>
    <div class="alert alert-danger">Booking not found.</div>
<?php else: ?>
    <!-- Booking Info Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Booking #<?= $booking['id'] ?></h5>
                <button class="btn btn-light btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Property Details</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" style="width: 140px;">Property</td><td><strong><?= htmlspecialchars($booking['property_title'] ?? 'N/A') ?></strong></td></tr>
                        <tr><td class="text-muted">City</td><td><?= htmlspecialchars($booking['city'] ?? 'N/A') ?></td></tr>
                        <tr><td class="text-muted">Area</td><td><?= number_format($booking['area_sqft'] ?? 0) ?> sq ft</td></tr>
                        <tr><td class="text-muted">Price</td><td><strong class="text-success">₹<?= number_format($booking['property_price'] ?? $booking['total_amount'] ?? 0) ?></strong></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Customer Details</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" style="width: 140px;">Name</td><td><strong><?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></strong></td></tr>
                        <tr><td class="text-muted">Phone</td><td>
                            <?php if (!empty($booking['customer_phone'])): ?>
                                <a href="tel:<?= $booking['customer_phone'] ?>"><?= htmlspecialchars($booking['customer_phone']) ?></a>
                            <?php else: ?>N/A<?php endif; ?>
                        </td></tr>
                        <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($booking['customer_email'] ?? 'N/A') ?></td></tr>
                        <tr><td class="text-muted">Booking Date</td><td><?= date('d M Y', strtotime($booking['created_at'] ?? '')) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Receipts -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i>Payment Receipts (<?= count($receipts) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($receipts)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No payment receipts yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt #</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['receipt_number'] ?? 'N/A') ?></strong></td>
                                    <td><strong class="text-success">₹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst($r['payment_mode'] ?? 'N/A') ?></span></td>
                                    <td><?= date('d M Y', strtotime($r['receipt_date'] ?? $r['created_at'] ?? '')) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match(strtolower($r['status'] ?? '')) {
                                            'completed', 'verified' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? 'N/A') ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-4">
        <a href="<?= BASE_URL ?>/associate/my-bookings" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Back to Bookings
        </a>
        <?php if (!empty($booking['customer_phone'])): ?>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $booking['customer_phone']) ?>?text=<?= urlencode('Hello ' . ($booking['customer_name'] ?? '') . ', here are your payment details for Booking #' . $booking['id'] . ' at APS Dream Home.') ?>" 
               class="btn btn-success" target="_blank">
                <i class="fab fa-whatsapp me-1"></i>Share Receipt on WhatsApp
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
