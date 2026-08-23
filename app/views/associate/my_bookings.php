<?php
/**
 * Associate My Bookings Page
 */
$page_title = $page_title ?? __('assoc_book_title', [], 'My Bookings');
$current_page = 'my-bookings';
$bookings = $bookings ?? [];
$stats = $stats ?? ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'total_value' => 0];
?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm style-19672">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold"><?= $stats['total'] ?></div>
                <div class="small opacity-75"><?= __('assoc_book_total', [], 'Total Bookings') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-success"><?= $stats['confirmed'] ?></div>
                <div class="small text-muted"><?= __('assoc_book_confirmed', [], 'Confirmed') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-warning"><?= $stats['pending'] ?></div>
                <div class="small text-muted"><?= __('assoc_book_pending', [], 'Pending') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-primary">₹<?= number_format($stats['total_value']) ?></div>
                <div class="small text-muted"><?= __('assoc_book_total_value', [], 'Total Value') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Bookings List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i><?= __('assoc_book_my_bookings', [], 'My Bookings') ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-contract fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted"><?= __('assoc_book_empty', [], 'No bookings yet') ?></h5>
                <p class="text-muted"><?= __('assoc_book_empty_desc', [], 'Your bookings will appear here once you make a sale.') ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('assoc_book_th_booking', [], 'Booking #') ?></th>
                            <th><?= __('assoc_book_th_property', [], 'Property') ?></th>
                            <th><?= __('assoc_book_th_customer', [], 'Customer') ?></th>
                            <th><?= __('assoc_book_th_amount', [], 'Amount') ?></th>
                            <th><?= __('assoc_book_th_paid', [], 'Paid') ?></th>
                            <th><?= __('assoc_book_th_status', [], 'Status') ?></th>
                            <th><?= __('assoc_book_th_date', [], 'Date') ?></th>
                            <th><?= __('assoc_book_th_action', [], 'Action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['id'] ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($b['property_title'] ?? 'N/A') ?>
                                    <?php if (!empty($b['city'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($b['city'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?>
                                    <?php if (!empty($b['customer_phone'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($b['customer_phone'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>₹<?= number_format($b['property_price'] ?? $b['total_amount'] ?? 0) ?></td>
                                <td>
                                    <?php $paid = $b['total_paid'] ?? 0; ?>
                                    <span class="<?= $paid > 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($paid) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match(strtolower($b['status'] ?? '')) {
                                        'confirmed', 'completed' => 'success',
                                        'pending', 'reserved' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($b['status'] ?? 'N/A') ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($b['created_at'] ?? '')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/associate/booking/<?= $b['id'] ?>/receipt" class="btn btn-outline-primary btn-sm" title="<?= __('assoc_book_view_receipt', [], 'View Receipt') ?>">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
