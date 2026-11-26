<?php
$page_title = $page_title ?? 'Booking Receipt & EMI Schedule';
$current_page = 'my-bookings';
$booking = $booking ?? null;
$receipts = $receipts ?? [];
$schedule = $schedule ?? [];
$video_consent = $video_consent ?? null;

$totalPlotValue = (float)($booking['property_price'] ?? $booking['total_plot_value'] ?? $booking['total_amount'] ?? 0);
$tokenPaid = (float)($booking['booking_amount_paid'] ?? $booking['booking_amount'] ?? 0);
$balanceAmount = max(0, $totalPlotValue - $tokenPaid);
$bookingRef = $booking['booking_number'] ?? ('BK-' . date('Ymd', strtotime($booking['created_at'] ?? 'now')) . '-' . str_pad($booking['id'] ?? '1', 4, '0', STR_PAD_LEFT));
?>

<style>
@media print {
    .no-print, header, nav, .sidebar, .navbar, footer, .btn, .breadcrumb, #smartChatWidgetContainer {
        display: none !important;
    }
    body, .main-content, .content-wrapper, .container-fluid {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    .print-receipt-container {
        padding: 10px !important;
    }
    .page-break-inside-avoid {
        page-break-inside: avoid;
    }
}
.receipt-header-box {
    border-bottom: 2px solid #0d6efd;
    padding-bottom: 12px;
    margin-bottom: 16px;
}
.stat-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
}
</style>

<div class="container-fluid px-3 px-md-4 py-3 print-receipt-container">
    <!-- Top Action Bar (No-Print) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 py-1 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/dashboard"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/my-bookings">My Bookings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking #<?= htmlspecialchars($booking['id'] ?? '') ?></li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/associate/my-bookings" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <?php if (!empty($booking['customer_phone'])): 
                $waText = "Namaste " . ($booking['customer_name'] ?? 'Client') . "!%0A" .
                          "APS Dream Home mein aapka plot booking confirm ho chuka hai.%0A" .
                          "📌 Project: " . ($booking['colony_name'] ?? '') . " (Plot #" . ($booking['plot_number'] ?? '') . ")%0A" .
                          "💰 Total Plot Value: ₹" . number_format($totalPlotValue) . "%0A" .
                          "✅ Booking Token: ₹" . number_format($tokenPaid) . "%0A" .
                          "📄 Booking Ref: " . $bookingRef . "%0A%0A" .
                          "Aap apne customer portal par login karke EMI Schedule aur Receipt dekh sakte hain:%0A" .
                          BASE_URL . "/login";
            ?>
                <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $booking['customer_phone']) ?>?text=<?= $waText ?>" 
                   target="_blank" class="btn btn-success btn-sm">
                    <i class="fab fa-whatsapp me-1"></i>Share on WhatsApp
                </a>
            <?php endif; ?>
            <button class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Receipt & Schedule
            </button>
        </div>
    </div>

    <?php if (!$booking): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>Booking record not found or access denied.
        </div>
    <?php else: ?>
        <!-- Official Receipt Document Sheet -->
        <div class="card border shadow-sm mb-4">
            <div class="card-body p-4">
                <!-- Company Header & Registration -->
                <div class="receipt-header-box d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fs-4 fw-bold text-primary"><i class="fas fa-city me-2"></i>APS DREAM HOME</span>
                            <span class="badge bg-primary text-uppercase">Official Allotment Receipt</span>
                        </div>
                        <div class="text-muted small">
                            APS Dream Home Infra Projects Pvt. Ltd. | RERA Reg: <strong>UPRERA-AGT-2024-0012</strong><br>
                            Corporate Office: Near Medical College Road, Gorakhpur, Uttar Pradesh - 273001<br>
                            Helpline: +91 99999 99999 | Email: contact@apsdreamhome.com | Web: www.apsdreamhome.com
                        </div>
                    </div>
                    <div class="text-md-end">
                        <div class="fw-bold fs-5 text-dark"><?= htmlspecialchars($bookingRef) ?></div>
                        <div class="text-muted small">Date: <strong><?= date('d M Y, h:i A', strtotime($booking['created_at'] ?? 'now')) ?></strong></div>
                        <div class="mt-1">
                            <?php
                            $st = strtolower($booking['status'] ?? 'pending');
                            $badgeClass = match($st) {
                                'confirmed', 'completed', 'fully_paid' => 'success',
                                'emi_active', 'active', 'partially_paid' => 'info text-dark',
                                'token_paid', 'reserved', 'pending' => 'warning text-dark',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $badgeClass ?> px-3 py-1 fs-6 text-uppercase"><?= str_replace('_', ' ', $st) ?></span>
                        </div>
                    </div>
                </div>

                <!-- 2-Column Summary: Property & Client -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="stat-pill h-100">
                            <h6 class="text-primary fw-bold mb-2 pb-1 border-bottom"><i class="fas fa-map-marker-alt me-1"></i>Property & Plot Allotment</h6>
                            <table class="table table-sm table-borderless mb-0 small">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Project/Colony:</td>
                                    <td><strong><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Plot Number:</td>
                                    <td><strong class="text-primary fs-6">Plot #<?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Plot Area:</td>
                                    <td>
                                        <?= number_format($booking['area_sqft'] ?? 0) ?> Sq.Ft. 
                                        <span class="text-muted">(~<?= round(($booking['area_sqft'] ?? 0) / 9, 2) ?> Gaj / Sq.Yards)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Location/City:</td>
                                    <td><?= htmlspecialchars($booking['city'] ?? 'Gorakhpur') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-pill h-100">
                            <h6 class="text-primary fw-bold mb-2 pb-1 border-bottom"><i class="fas fa-user-check me-1"></i>Customer Information</h6>
                            <table class="table table-sm table-borderless mb-0 small">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Allottee Name:</td>
                                    <td><strong><?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Contact Mobile:</td>
                                    <td>
                                        <strong><?= htmlspecialchars($booking['customer_phone'] ?? 'N/A') ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email Address:</td>
                                    <td><?= htmlspecialchars($booking['customer_email'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Preference:</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= !empty($schedule) ? 'In-House EMI Plan (' . count($schedule) . ' Months)' : 'One-Time Outright Payment' ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Financial Snapshot Grid -->
                <div class="row g-2 mb-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted small">Total Plot Value</div>
                            <div class="fs-5 fw-bold text-dark">₹<?= number_format($totalPlotValue) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted small">Token Paid (Non-Refundable)</div>
                            <div class="fs-5 fw-bold text-success">₹<?= number_format($tokenPaid) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted small">Balance Due to Pay</div>
                            <div class="fs-5 fw-bold text-danger">₹<?= number_format($balanceAmount) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted small">In-House Interest</div>
                            <div class="fs-5 fw-bold text-primary">0% Subvention*</div>
                        </div>
                    </div>
                </div>

                <!-- Statutory Legal Notice (Master Deed Section 2.1 & 2.9) -->
                <div class="alert alert-danger py-2 px-3 mb-4 small border-start border-4 border-danger d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #fff8f8;">
                    <div>
                        <i class="fas fa-shield-alt text-danger me-2"></i>
                        <strong>वैधानिक घोषणा एवं मास्टर डीड (धारा 2.1 व 2.9):</strong> यह रसीद पुष्टि करती है कि टोकन बुकिंग राशि 
                        (₹<?= number_format($tokenPaid) ?>) पूर्णतः <strong>नॉन-रिफंडेबल (Non-Refundable / वापस नहीं होगी)</strong> है। 
                        ग्राहक द्वारा दिए गए पते/मोबाइल पर यह डिजिटल रसीद एवं ईएमआई अनुसूची भेजी गई है।
                    </div>
                    <span class="badge bg-danger">Non-Refundable Token</span>
                </div>

                <!-- Receipts Table -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-receipt text-success me-1"></i>Recorded Payment Receipts (भुगतान रसीदें)</h6>
                    <?php if (empty($receipts)): ?>
                        <div class="p-3 bg-light rounded text-center small text-muted">
                            No separate receipts recorded yet. Initial booking payment of ₹<?= number_format($tokenPaid) ?> recorded on booking confirmation.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th>Ref / Cheque #</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($receipts as $r): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($r['receipt_number'] ?? ('REC-' . $r['id'])) ?></strong></td>
                                            <td><?= date('d M Y', strtotime($r['receipt_date'] ?? $r['created_at'] ?? 'now')) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= ucfirst($r['payment_mode'] ?? 'Online') ?></span></td>
                                            <td><?= htmlspecialchars($r['transaction_ref'] ?? $r['cheque_number'] ?? '-') ?></td>
                                            <td><strong class="text-success">₹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                            <td>
                                                <span class="badge bg-success">Cleared</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bank-Grade EMI Repayment Schedule (If Applicable) -->
                <?php if (!empty($schedule)): ?>
                    <div class="mb-4 page-break-inside-avoid">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                Bank-Grade In-House EMI Repayment Schedule (मासिक किश्त अनुसूची)
                            </h6>
                            <span class="badge bg-primary"><?= count($schedule) ?> Monthly Installments</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0 small text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest (0%)</th>
                                        <th>Installment</th>
                                        <th>Closing Balance</th>
                                        <th>Status</th>
                                        <th>Paid Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedule as $inst): ?>
                                        <?php
                                        $instSt = strtolower($inst['status'] ?? 'pending');
                                        $isOverdue = ($instSt === 'pending' && strtotime($inst['due_date']) < time());
                                        $rowClass = $instSt === 'paid' ? 'table-success bg-opacity-25' : ($isOverdue ? 'table-warning bg-opacity-25' : '');
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td><strong><?= $inst['installment_no'] ?></strong></td>
                                            <td><?= date('d M Y', strtotime($inst['due_date'])) ?></td>
                                            <td>₹<?= number_format($inst['principal'] ?? $inst['amount'] ?? 0) ?></td>
                                            <td class="text-success fw-bold">₹<?= number_format($inst['interest'] ?? 0) ?> (0%)</td>
                                            <td><strong>₹<?= number_format($inst['amount'] ?? 0) ?></strong></td>
                                            <td>₹<?= number_format($inst['closing_balance'] ?? 0) ?></td>
                                            <td>
                                                <?php if ($instSt === 'paid'): ?>
                                                    <span class="badge bg-success">Paid</span>
                                                <?php elseif ($isOverdue): ?>
                                                    <span class="badge bg-danger">Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Upcoming</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ((float)($inst['paid_amount'] ?? 0) > 0): ?>
                                                    <span class="text-success fw-bold">₹<?= number_format($inst['paid_amount']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Bank-Grade In-House Loan Default & Penalty Rules Undertaking -->
                <div class="card border border-warning bg-light p-3 mb-4 page-break-inside-avoid">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="fas fa-gavel text-warning me-1"></i>
                        इन-हाउस लोन एवं ईएमआई नियम व शर्तें (In-House Loan Legal Clauses & Default Penalty):
                    </h6>
                    <ol class="small text-muted ps-3 mb-0" style="line-height: 1.6;">
                        <li><strong>0% ब्याज छूट (Promotional Subvention):</strong> 36 माह (3 वर्ष) तक की ईएमआई योजना पर कंपनी शून्य प्रतिशत (0%) ब्याज देती है, बशर्ते ग्राहक प्रत्येक किश्त समय पर दे।</li>
                        <li><strong>लगातार 3 ईएमआई डिफ़ॉल्ट नियम (3-Month Consecutive Default Clause):</strong> यदि ग्राहक लगातार 3 माह तक किश्त जमा करने में विफल रहता है, तो शून्य प्रतिशत (0%) ब्याज की छूट स्वतः समाप्त हो जाएगी तथा कुल बकाया राशि पर 12% वार्षिक वाणिज्यिक ब्याज लागू होगा। 90 दिन पश्चात कंपनी को आवंटन निरस्त करने का विधिक अधिकार होगा।</li>
                        <li><strong>चेक बाउंस एवं विलंब शुल्क (Cheque Bounce & Overdue Penalty):</strong> चेक / NACH बाउंस होने पर प्रति बाउंस ₹500 का शुल्क एवं मासिक 2% का विलंब अधिभार देय होगा।</li>
                        <li><strong>पजेशन एवं रजिस्ट्री (Possession & Registry):</strong> कुल प्लॉट मूल्य का 25% भुगतान होने पर समझौता विलेख तथा पूर्ण भुगतान (100%) होने पर ही बैनामा (रजिस्ट्री) निष्पादित किया जाएगा।</li>
                    </ol>
                </div>

                <!-- Legal Verification Status & Signatures -->
                <div class="row g-3 pt-3 border-top page-break-inside-avoid">
                    <div class="col-6 text-center">
                        <div class="p-2 border rounded bg-light mb-2">
                            <i class="fas fa-video text-primary me-1"></i>
                            <strong>Customer Video Consent:</strong>
                            <?php if (!empty($video_consent)): ?>
                                <span class="badge bg-success ms-1">Recorded & Verified</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-1">Pending In-Person Capture</span>
                            <?php endif; ?>
                        </div>
                        <div style="height: 60px;"></div>
                        <div class="border-top pt-1 small text-dark fw-bold">Customer / Allottee Signature</div>
                        <div class="text-muted small">(I agree to all EMI rules & non-refundable policy)</div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="p-2 border rounded bg-light mb-2">
                            <i class="fas fa-user-shield text-success me-1"></i>
                            <strong>Maker-Checker Seal:</strong>
                            <span class="badge bg-primary ms-1">Authorized Allotment</span>
                        </div>
                        <div style="height: 60px;"></div>
                        <div class="border-top pt-1 small text-dark fw-bold">For APS DREAM HOME INFRA PROJECTS</div>
                        <div class="text-muted small">Authorized Signatory & Corporate Seal</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
