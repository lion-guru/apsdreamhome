<?php
$page_title = $page_title ?? __('assoc_br_title', [], 'Booking Receipt');
$current_page = 'my-bookings';
$booking = $booking ?? null;
$receipts = $receipts ?? [];
?>

<?php if (!$booking): ?>
    <div class="alert alert-danger"><?= __('assoc_br_not_found', [], 'Booking not found.') ?></div>
<?php else: ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i><?= __('assoc_br_booking', ['id' => $booking['id']], 'Booking #%id%') ?></h5>
                <button class="btn btn-light btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i><?= __('assoc_br_print', [], 'Print') ?>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><?= __('assoc_br_property_details', [], 'Property Details') ?></h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" class="style-12616"><?= __('assoc_br_property', [], 'Property') ?></td><td><strong><?= htmlspecialchars($booking['property_title'] ?? __('assoc_br_na', [], 'N/A')) ?></strong></td></tr>
                        <tr><td class="text-muted"><?= __('assoc_br_city', [], 'City') ?></td><td><?= htmlspecialchars($booking['city'] ?? __('assoc_br_na', [], 'N/A')) ?></td></tr>
                        <tr><td class="text-muted"><?= __('assoc_br_area', [], 'Area') ?></td><td><?= number_format($booking['area_sqft'] ?? 0) ?> <?= __('assoc_br_sqft', [], 'sq ft') ?></td></tr>
                        <tr><td class="text-muted"><?= __('assoc_br_price', [], 'Price') ?></td><td><strong class="text-success">₹<?= number_format($booking['property_price'] ?? $booking['total_amount'] ?? 0) ?></strong></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><?= __('assoc_br_customer_details', [], 'Customer Details') ?></h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" class="style-12616"><?= __('assoc_br_name', [], 'Name') ?></td><td><strong><?= htmlspecialchars($booking['customer_name'] ?? __('assoc_br_na', [], 'N/A')) ?></strong></td></tr>
                        <tr><td class="text-muted"><?= __('assoc_br_phone', [], 'Phone') ?></td><td>
                            <?php if (!empty($booking['customer_phone'])): ?>
                                <a href="tel:<?= $booking['customer_phone'] ?>"><?= htmlspecialchars($booking['customer_phone']) ?></a>
                            <?php else: ?><?= __('assoc_br_na', [], 'N/A') ?><?php endif; ?>
                        </td></tr>
                        <tr><td class="text-muted"><?= __('assoc_br_email', [], 'Email') ?></td><td><?= htmlspecialchars($booking['customer_email'] ?? __('assoc_br_na', [], 'N/A')) ?></td></tr>
                        <tr><td class="text-muted"><?= __('assoc_br_date', [], 'Booking Date') ?></td><td><?= date('d M Y', strtotime($booking['created_at'] ?? '')) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i><?= __('assoc_br_receipts', ['count' => count($receipts)], 'Payment Receipts (%count%)') ?></h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($receipts)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0"><?= __('assoc_br_no_receipts', [], 'No payment receipts yet.') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('assoc_br_th_receipt', [], 'Receipt #') ?></th>
                                <th><?= __('assoc_br_th_amount', [], 'Amount') ?></th>
                                <th><?= __('assoc_br_th_mode', [], 'Mode') ?></th>
                                <th><?= __('assoc_br_th_date', [], 'Date') ?></th>
                                <th><?= __('assoc_br_th_status', [], 'Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['receipt_number'] ?? __('assoc_br_na', [], 'N/A')) ?></strong></td>
                                    <td><strong class="text-success">₹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst($r['payment_mode'] ?? __('assoc_br_na', [], 'N/A')) ?></span></td>
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
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? __('assoc_br_na', [], 'N/A')) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>/associate/my-bookings" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i><?= __('assoc_br_back', [], 'Back to Bookings') ?>
        </a>
        <?php if (!empty($booking['customer_phone'])): ?>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $booking['customer_phone']) ?>?text=<?= urlencode(__('assoc_br_whatsapp_text', ['name' => ($booking['customer_name'] ?? ''), 'id' => $booking['id']], 'Hello %name%, here are your payment details for Booking #%id% at APS Dream Home.')) ?>" 
               class="btn btn-success" target="_blank">
                <i class="fab fa-whatsapp me-1"></i><?= __('assoc_br_share_whatsapp', [], 'Share Receipt on WhatsApp') ?>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
