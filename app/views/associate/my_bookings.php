<?php
/**
 * Associate My Bookings Page
 */
$page_title = $page_title ?? __('assoc_book_title', [], 'My Bookings');
$current_page = 'my-bookings';
$bookings = $bookings ?? [];
$stats = $stats ?? ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'total_value' => 0];
?>

<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/dashboard"><i class="fas fa-home me-1"></i><?= __('nav_dashboard', [], 'Dashboard') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('assoc_book_title', [], 'My Bookings') ?></li>
        </ol>
    </nav>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 text-center">
                    <div class="fs-2 fw-bold text-dark"><?= $stats['total'] ?></div>
                    <div class="small text-muted"><?= __('assoc_book_total', [], 'Total Bookings') ?></div>
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

    <!-- Associate Legal Compliance Notice -->
    <div class="alert alert-danger py-2 px-3 mb-3 small border-start border-4 border-danger d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #fff8f8;">
        <div>
            <i class="fas fa-shield-alt text-danger me-2"></i>
            <strong>एसोसिएट आचार संहिता एवं मास्टर डीड (धारा 2.1 व 2.9):</strong> सभी ग्राहकों को बुकिंग के समय स्पष्ट सूचित करें कि टोकन बुकिंग राशि (₹51,000) पूर्णतः <strong>नॉन-रिफंडेबल (Non-Refundable / वापस नहीं होगी)</strong> है। किसी भी परिस्थिति में टोकन वापसी का झूठा आश्वासन न दें।
        </div>
        <span class="badge bg-danger">Zero Tolerance Policy</span>
    </div>

    <!-- Associate Legal Deeds & Documents Toolkit -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #0d6efd !important;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <strong class="text-primary"><i class="fas fa-balance-scale me-1"></i> एसोसिएट लीगल टूलकिट (Legal Deeds & Statutory Kit):</strong>
                    <span class="text-muted small ms-1">ग्राहकों के लिए अधिकृत रद्दीकरण समझौता विलेख एवं स्टाम्प पेपर प्रारूप।</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= BASE_URL ?>/documents/cancellation_settlement_deed.html" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-print me-1"></i> रद्दीकरण विलेख (Stamp Paper Mode)
                    </a>
                    <a href="<?= BASE_URL ?>/downloads/cancellation_settlement_deed_hindi.doc" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-file-word me-1"></i> भरणीय विलेख (.DOC Hindi)
                    </a>
                    <a href="<?= BASE_URL ?>/associate-rules" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-gavel me-1"></i> आचार संहिता (Code of Conduct)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i><?= __('assoc_book_my_bookings', [], 'My Bookings') ?></h5>
            <a href="<?= BASE_URL ?>/associate/book-plot" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle me-1"></i> <?= __('assoc_book_new_doorstep', [], 'New Doorstep Booking') ?>
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="aps-cp-empty py-5">
                    <div class="aps-cp-empty-icon"><i class="fas fa-file-contract"></i></div>
                    <h5><?= __('assoc_book_empty', [], 'No bookings yet') ?></h5>
                    <p><?= __('assoc_book_empty_desc', [], 'Your bookings will appear here once you make a sale.') ?></p>
                    <a href="<?= BASE_URL ?>/associate/book-plot" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-plus-circle me-1"></i> Create First Booking Draft
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><?= __('assoc_book_th_booking', [], 'Booking #') ?></th>
                                <th scope="col"><?= __('assoc_book_th_property', [], 'Property') ?></th>
                                <th scope="col"><?= __('assoc_book_th_customer', [], 'Customer') ?></th>
                                <th scope="col"><?= __('assoc_book_th_amount', [], 'Amount') ?></th>
                                <th scope="col"><?= __('assoc_book_th_paid', [], 'Paid') ?></th>
                                <th scope="col"><?= __('assoc_book_th_status', [], 'Status') ?></th>
                                <th scope="col"><?= __('assoc_book_th_date', [], 'Date') ?></th>
                                <th scope="col"><?= __('assoc_book_th_action', [], 'Action') ?></th>
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
                                        <span class="<?= $paid > 0 ? 'text-success fw-bold' : 'text-danger' ?>">₹<?= number_format($paid) ?></span>
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
                                        <?php
                                        $approval = strtolower($b['approval_status'] ?? '');
                                        if ($approval === 'pending'): ?>
                                            <br><span class="badge bg-warning text-dark mt-1" style="font-size:0.7rem;"><i class="fas fa-clock me-1"></i>Review Pending</span>
                                        <?php elseif ($approval === 'approved'): ?>
                                            <br><span class="badge bg-success mt-1" style="font-size:0.7rem;"><i class="fas fa-check-double me-1"></i>Verified</span>
                                        <?php elseif ($approval === 'rejected'): ?>
                                            <br><span class="badge bg-danger mt-1" style="font-size:0.7rem;"><i class="fas fa-times me-1"></i>Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($b['created_at'] ?? '')) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/associate/booking/<?= $b['id'] ?>/receipt" class="btn btn-outline-primary btn-sm" title="<?= __('assoc_book_view_receipt', [], 'View Official Receipt & EMI Schedule') ?>">
                                            <i class="fas fa-receipt me-1"></i>Receipt
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
</div>
