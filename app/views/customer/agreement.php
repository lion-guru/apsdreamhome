<?php
/**
 * Agreement to Sell — Customer Portal Fallback View
 * Used when PDF generation fails or file not found
 */
$doc = $doc ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agreement to Sell - APS Dream Home</title>
    <link href="<?php echo  BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo  BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20px; }
        }
        .agreement-header { border-bottom: 2px solid #0d9488; padding-bottom: 20px; margin-bottom: 30px; }
        .agreement-body { line-height: 1.9; font-size: 0.95rem; }
        .clause { margin-bottom: 20px; }
        .clause-title { font-weight: 700; color: #0d9488; margin-bottom: 8px; }
        .detail-row { display: flex; margin-bottom: 10px; }
        .detail-label { width: 220px; font-weight: 600; color: #374151; }
        .detail-value { flex: 1; color: #1f2937; }
        .signature-block { margin-top: 40px; }
        .signature-line { border-bottom: 1px solid #9ca3af; width: 250px; display: inline-block; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="no-print text-center mb-4">
            <a href="<?php echo  BASE_URL ?>/customer/documents" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Documents
            </a>
            <button class="btn btn-primary ms-2 no-print" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print / Save as PDF
            </button>
        </div>

        <div class="agreement-header text-center">
            <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                <i class="fas fa-file-contract fa-2x text-primary"></i>
                <div>
                    <h2 class="mb-0 fw-bold">APS Dream Home</h2>
                    <small class="text-muted">Gorakhpur, Uttar Pradesh</small>
                </div>
            </div>
            <h3 class="fw-bold text-primary mb-1">AGREEMENT TO SELL</h3>
            <p class="text-muted mb-0">Agreement No: AG-<?php echo  htmlspecialchars($doc['agreement_number'] ?? 'AG-' . str_pad($doc['id'] ?? 0, 6, '0', STR_PAD_LEFT)) ?></p>
        </div>

        <div class="agreement-body">
            <p class="text-center mb-4"><strong>Date:</strong> <?php echo  htmlspecialchars($doc['agreement_date'] ?? date('d M Y')) ?></p>

            <p>This Agreement to Sell is made on <strong><?php echo  htmlspecialchars($doc['agreement_date'] ?? date('d M Y')) ?></strong> at Gorakhpur, Uttar Pradesh.</p>

            <p><strong>BETWEEN</strong></p>
            <p><strong>APS Dream Home</strong>, a company registered under the Companies Act, 2013, having its registered office at Gorakhpur, Uttar Pradesh (hereinafter referred to as <strong>"the Seller"</strong>, which expression shall unless repugnant to the context include its successors and assigns), of the <strong>ONE PART</strong>.</p>

            <p><strong>AND</strong></p>

            <p><strong><?php echo  htmlspecialchars($doc['customer_name'] ?? 'The Purchaser') ?></strong>, son/daughter/wife of <strong><?php echo  htmlspecialchars($doc['father_husband_name'] ?? '—') ?></strong>, residing at <strong><?php echo  htmlspecialchars($doc['customer_address'] ?? '—') ?></strong>, PAN: <strong><?php echo  htmlspecialchars($doc['customer_pan'] ?? '—') ?></strong>, Aadhaar: <strong><?php echo  htmlspecialchars($doc['customer_aadhaar'] ?? '—') ?></strong> (hereinafter referred to as <strong>"the Purchaser"</strong>, which expression shall unless repugnant to the context include his/her heirs, executors, administrators and assigns), of the <strong>OTHER PART</strong>.</p>

            <hr class="my-4">

            <div class="clause">
                <div class="clause-title">1. PROPERTY DESCRIPTION</div>
                <p>The Seller agrees to sell and the Purchaser agrees to purchase the property described below:</p>
                <div class="card">
                    <div class="card-body">
                        <div class="detail-row">
                            <span class="detail-label">Booking Number:</span>
                            <span class="detail-value"><?php echo  htmlspecialchars($doc['booking_number'] ?? '—') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Plot Number:</span>
                            <span class="detail-value"><?php echo  htmlspecialchars($doc['plot_number'] ?? '—') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Colony:</span>
                            <span class="detail-value"><?php echo  htmlspecialchars($doc['colony_name'] ?? '—') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Plot Area:</span>
                            <span class="detail-value"><?php echo  htmlspecialchars($doc['plot_area'] ?? '—') ?> sq. ft.</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Khasra/Gata No:</span>
                            <span class="detail-value"><?php echo  htmlspecialchars($doc['khasra_number'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clause">
                <div class="clause-title">2. TOTAL SALE CONSIDERATION</div>
                <p>The total sale consideration for the said property is agreed at <strong>₹<?php echo  htmlspecialchars(number_format($doc['total_amount'] ?? 0, 2)) ?></strong> (Rupees <?php echo  htmlspecialchars($doc['amount_in_words'] ?? '—') ?> Only).</p>
            </div>

            <div class="clause">
                <div class="clause-title">3. PAYMENT SCHEDULE</div>
                <div class="detail-row"><span class="detail-label">Booking Amount:</span><span class="detail-value">₹<?php echo  htmlspecialchars(number_format($doc['booking_amount'] ?? 0, 2)) ?></span></div>
                <div class="detail-row"><span class="detail-label">Amount Paid to Date:</span><span class="detail-value text-success fw-bold">₹<?php echo  htmlspecialchars(number_format($doc['paid_amount'] ?? 0, 2)) ?></span></div>
                <div class="detail-row"><span class="detail-label">Balance Payable:</span><span class="detail-value text-danger fw-bold">₹<?php echo  htmlspecialchars(number_format(($doc['total_amount'] ?? 0) - ($doc['paid_amount'] ?? 0), 2)) ?></span></div>
                <p class="mt-2 small text-muted">The Purchaser shall pay the balance amount as per the payment schedule attached as Annexure-A.</p>
            </div>

            <div class="clause">
                <div class="clause-title">4. POSSESSION</div>
                <p>Possession of the said property shall be handed over to the Purchaser upon:</p>
                <ol class="small text-muted">
                    <li>Full payment of the sale consideration and all other dues.</li>
                    <li>Execution and registration of the Sale Deed.</li>
                    <li>Compliance with all statutory requirements including RERA.</li>
                </ol>
            </div>

            <div class="clause">
                <div class="clause-title">5. GENERAL TERMS</div>
                <ol class="small text-muted">
                    <li>This Agreement is subject to the Real Estate (Regulation and Development) Act, 2016 (RERA) and rules made thereunder.</li>
                    <li>The Purchaser shall not create any encumbrance on the property until the Sale Deed is executed.</li>
                    <li>Any dispute arising out of this Agreement shall be subject to the jurisdiction of courts at Gorakhpur.</li>
                    <li>Time is of the essence of this Agreement.</li>
                </ol>
            </div>

            <div class="signature-block">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>SELLER</strong></p>
                        <p class="signature-line"></p>
                        <p class="small text-muted">For APS Dream Home<br>Authorized Signatory</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><strong>PURCHASER</strong></p>
                        <p class="signature-line"></p>
                        <p class="small text-muted"><?php echo  htmlspecialchars($doc['customer_name'] ?? 'The Purchaser') ?></p>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <p class="small text-muted">Witness 1:<br><span class="signature-line"></span><br>Name: ___________________</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="small text-muted">Witness 2:<br><span class="signature-line"></span><br>Name: ___________________</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="no-print text-center mt-4">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print / Save as PDF
            </button>
        </div>
    </div>
</body>
</html>