<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?php echo htmlspecialchars($receipt_no ?? 'N/A'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1a1a1a;
            background: white;
        }
        
        .receipt-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }
        
        /* Header Section */
        .receipt-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .company-logo {
            max-height: 70px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #0d6efd;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .company-tagline {
            font-size: 14px;
            color: #6c757d;
            margin: 2px 0 8px 0;
            font-weight: 400;
        }
        
        .company-details {
            font-size: 11px;
            color: #6c757d;
            line-height: 1.6;
        }
        
        .company-details strong {
            color: #343a40;
        }
        
        /* Receipt Title Bar */
        .receipt-title-bar {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 15px 20px;
            margin: -15mm -15mm 25px -15mm;
            text-align: center;
        }
        
        .receipt-title {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .receipt-subtitle {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 4px;
        }
        
        /* Main Content Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 18px 20px;
        }
        
        .info-box-title {
            font-size: 12px;
            font-weight: 600;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            padding: 4px 0;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-size: 11px;
            font-weight: 500;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .info-value {
            font-size: 13px;
            font-weight: 500;
            color: #212529;
            text-align: right;
            max-width: 65%;
            word-break: break-word;
        }
        
        /* Amount Section */
        .amount-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #0d6efd;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .amount-label {
            font-size: 13px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .amount-value {
            font-size: 42px;
            font-weight: 700;
            color: #0d6efd;
            margin: 5px 0 15px 0;
            line-height: 1;
            font-family: 'Georgia', serif;
        }
        
        .amount-words {
            font-size: 14px;
            color: #343a40;
            font-style: italic;
            border-top: 1px dashed #adb5bd;
            padding-top: 15px;
            margin-top: 10px;
            line-height: 1.6;
        }
        
        .payment-type-badge {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }
        
        /* Property Details */
        .property-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0d6efd;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            color: #0d6efd;
        }
        
        .property-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        
        .property-card-header {
            background: #f8f9fa;
            padding: 12px 20px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #343a40;
        }
        
        .property-card-body {
            padding: 20px;
        }
        
        .property-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .property-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .property-field-label {
            font-size: 10px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .property-field-value {
            font-size: 13px;
            font-weight: 500;
            color: #212529;
        }
        
        /* Footer Section */
        .receipt-footer {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 25px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .footer-box {
            padding: 0 10px;
        }
        
        .footer-title {
            font-size: 11px;
            font-weight: 600;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .footer-text {
            font-size: 11px;
            color: #6c757d;
            line-height: 1.7;
        }
        
        .signature-line {
            border-top: 1px solid #adb5bd;
            width: 180px;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }
        
        .qr-code-box {
            text-align: center;
            padding: 15px;
        }
        
        .qr-code-box img {
            max-width: 100px;
            border: 1px solid #dee2e6;
            padding: 5px;
            background: white;
        }
        
        .qr-code-box p {
            font-size: 10px;
            color: #6c757d;
            margin-top: 8px;
        }
        
        /* Print Button (hidden in print) */
        .print-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .btn-print {
            background: #0d6efd;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .btn-print:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 120px;
            font-weight: 700;
            color: rgba(13, 110, 253, 0.03);
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
            font-family: Georgia, serif;
        }
        
        /* Print Styles */
        @media print {
            .print-actions { display: none !important; }
            .watermark { display: block !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .amount-section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .receipt-title-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .info-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .btn-print { display: none !important; }
            .no-print { display: none !important; }
            @page { margin: 15mm; }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .info-grid, .property-grid, .footer-grid {
                grid-template-columns: 1fr;
            }
            .amount-value { font-size: 32px; }
            .company-name { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="watermark">APS Dream Home</div>
    
    <div class="receipt-container">
        <!-- Receipt Title Bar (full bleed) -->
        <div class="receipt-title-bar">
            <div class="receipt-title"><i class="fas fa-receipt me-2"></i>Official Payment Receipt</div>
            <div class="receipt-subtitle">Receipt No: <?php echo htmlspecialchars($receipt_no); ?> | Date: <?php echo date('d M Y, h:i A', strtotime($payment['payment_date'] ?? 'now')); ?></div>
        </div>
        
        <!-- Header -->
        <div class="receipt-header">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                    <div class="company-name">APS Dream Home</div>
                    <div class="company-tagline">Real Estate & Developers</div>
                    <div class="company-details">
                        <strong>GSTIN:</strong> <?php echo htmlspecialchars($company['gstin']); ?><br>
                        <strong>Address:</strong> <?php echo htmlspecialchars($company['address']); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($company['phone']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($company['email']); ?><br>
                        <strong>Website:</strong> <?php echo htmlspecialchars($company['website']); ?>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="info-box" style="background: white; border-color: #dee2e6;">
                        <div class="info-box-title"><i class="fas fa-hashtag me-2"></i>Receipt Details</div>
                        <div class="info-row">
                            <span class="info-label">Receipt No</span>
                            <span class="info-value"><?php echo htmlspecialchars($receipt_no); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date</span>
                            <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($payment['payment_date'] ?? 'now')); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Booking Ref</span>
                            <span class="info-value"><?php echo htmlspecialchars($payment['booking_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Payment Mode</span>
                            <span class="info-value"><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'] ?? 'N/A')); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <div class="qr-code-box no-print">
                        <?php 
                        $qrData = BASE_URL . '/verify-receipt/' . $receipt_no;
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrData);
                        ?>
                        <img src="<?php echo $qrUrl; ?>" alt="Verify Receipt QR Code">
                        <p>Scan to verify authenticity</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer & Property Info -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-box-title"><i class="fas fa-user me-2"></i>Customer Details</div>
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['customer_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['customer_phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['customer_email'] ?? 'N/A'); ?></span>
                </div>
                <?php if (!empty($payment['customer_address'])): ?>
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['customer_address']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-box">
                <div class="info-box-title"><i class="fas fa-building me-2"></i>Property Details</div>
                <div class="info-row">
                    <span class="info-label">Property</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['property_title'] ?? 'N/A'); ?></span>
                </div>
                <?php if (!empty($payment['plot_number'])): ?>
                <div class="info-row">
                    <span class="info-label">Plot No</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['plot_number']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['area_sqft'])): ?>
                <div class="info-row">
                    <span class="info-label">Area</span>
                    <span class="info-value"><?php echo number_format($payment['area_sqft'], 2); ?> sq ft</span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Location</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['property_location'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Booking Total</span>
                    <span class="info-value">₹<?php echo number_format(floatval($payment['booking_total'] ?? 0), 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Amount Section -->
        <div class="amount-section">
            <div class="amount-label"><i class="fas fa-rupee-sign me-2"></i>Amount Received</div>
            <div class="amount-value">₹<?php echo number_format(floatval($payment['payment_amount'] ?? 0), 2); ?></div>
            <div class="amount-words">
                <strong>Amount in Words:</strong><br>
                <?php echo htmlspecialchars($amount_in_words ?? 'N/A'); ?>
            </div>
            <span class="payment-type-badge">
                <?php echo ucwords(str_replace('_', ' ', $payment['payment_type'] ?? 'booking')); ?> Payment
            </span>
        </div>
        
        <!-- Property Card Detail (if available) -->
        <?php if (!empty($payment['plot_number']) || !empty($payment['area_sqft'])): ?>
        <div class="property-section">
            <div class="section-title"><i class="fas fa-map-marked-alt"></i> Plot Specifications</div>
            <div class="property-card">
                <div class="property-card-header">
                    Plot: <?php echo htmlspecialchars($payment['plot_number'] ?? 'N/A'); ?> | Block: <?php echo htmlspecialchars($payment['block'] ?? 'N/A'); ?>
                </div>
                <div class="property-card-body">
                    <div class="property-grid">
                        <div class="property-field">
                            <span class="property-field-label">Plot Number</span>
                            <span class="property-field-value"><?php echo htmlspecialchars($payment['plot_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Block</span>
                            <span class="property-field-value"><?php echo htmlspecialchars($payment['block'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Area</span>
                            <span class="property-field-value"><?php echo number_format(floatval($payment['area_sqft'] ?? 0), 2); ?> sq ft</span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Facing</span>
                            <span class="property-field-value"><?php echo htmlspecialchars(ucfirst($payment['facing'] ?? 'N/A')); ?></span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Corner Plot</span>
                            <span class="property-field-value"><?php echo ($payment['corner_plot'] ?? 0) ? 'Yes' : 'No'; ?></span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Dimensions</span>
                            <span class="property-field-value"><?php echo htmlspecialchars(($payment['width_ft'] ?? 0) . ' ft x ' . ($payment['length_ft'] ?? 0) . ' ft'); ?></span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Rate/sq ft</span>
                            <span class="property-field-value">₹<?php echo number_format(floatval($payment['price_per_sqft'] ?? 0), 2); ?></span>
                        </div>
                        <div class="property-field">
                            <span class="property-field-label">Plot Status</span>
                            <span class="property-field-value"><?php echo ucfirst(htmlspecialchars($payment['status'] ?? 'available')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Notes if any -->
        <?php if (!empty($payment['payment_notes']) || !empty($payment['notes'])): ?>
        <div class="property-section">
            <div class="section-title"><i class="fas fa-sticky-note"></i> Remarks / Notes</div>
            <div class="info-box" style="background: #fff3cd; border-color: #ffc107;">
                <p style="margin: 0; font-size: 13px; color: #664d03;">
                    <?php echo nl2br(htmlspecialchars($payment['payment_notes'] ?? $payment['notes'] ?? '')); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="receipt-footer">
            <div class="footer-grid">
                <div class="footer-box">
                    <div class="footer-title"><i class="fas fa-shield-alt me-2"></i>Verification</div>
                    <div class="footer-text">
                        This is a computer-generated receipt and is valid without a physical signature.<br>
                        Verify authenticity at: <strong><?php echo htmlspecialchars($company['website'] . '/verify-receipt/' . $receipt_no); ?></strong><br>
                        Receipt generated by: <strong><?php echo htmlspecialchars($processed_by ?? 'System'); ?></strong> on <?php echo date('d M Y h:i A'); ?>
                    </div>
                </div>
                <div class="footer-box text-end">
                    <div class="footer-title"><i class="fas fa-signature me-2"></i>Authorized Signatory</div>
                    <div class="signature-line">Authorized Signatory</div>
                    <div class="footer-text" style="margin-top: 10px; text-align: right;">
                        <strong><?php echo htmlspecialchars($company['name']); ?></strong><br>
                        <?php echo htmlspecialchars($company['address']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Button -->
    <div class="print-actions no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print / Save as PDF
        </button>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-print if ?print=true in URL
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === 'true') {
                window.addEventListener('load', function() {
                    setTimeout(function() { window.print(); }, 500);
                });
            }
            
            // Listen for print event
            window.addEventListener('beforeprint', function() {
                document.body.classList.add('printing');
            });
            
            window.addEventListener('afterprint', function() {
                document.body.classList.remove('printing');
            });
        })();
    </script>
</body>
</html>