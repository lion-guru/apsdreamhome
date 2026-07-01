<?php
$cheque = $cheque ?? [];
$bankName   = $cheque['bank_name'] ?? 'BANK NAME';
$branch     = $cheque['branch'] ?? '';
$ifsc       = $cheque['ifsc_code'] ?? '';
$acctNo     = $cheque['account_number'] ?? '';
$payee      = $cheque['payee_name'] ?? '';
$amount     = (float)($cheque['amount'] ?? 0);
$chequeDate = $cheque['cheque_date'] ?? date('Y-m-d');
$signatory  = $cheque['signatory_name'] ?? '';
$purpose    = $cheque['purpose'] ?? '';

function numberToWords(float $n): string
{
    if ($n == 0) return 'Zero';
    $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
             'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    $whole = (int)$n;
    $paise = round(($n - $whole) * 100);
    $words = '';
    if ($whole >= 10000000) { $words .= $ones[(int)floor($whole/10000000)] . ' Crore '; $whole %= 10000000; }
    if ($whole >= 100000)   { $words .= $ones[(int)floor($whole/100000)] . ' Lakh '; $whole %= 100000; }
    if ($whole >= 1000)     { $words .= $ones[(int)floor($whole/1000)] . ' Thousand '; $whole %= 1000; }
    if ($whole >= 100)      { $words .= $ones[(int)floor($whole/100)] . ' Hundred '; $whole %= 100; }
    if ($whole >= 20)       { $words .= $tens[(int)floor($whole/10)] . ' '; $whole %= 10; }
    if ($whole > 0)         { $words .= $ones[$whole] . ' '; }
    $words = trim($words) . ' Rupees';
    if ($paise > 0) {
        $pWords = '';
        if ($paise >= 20) { $pWords .= $tens[(int)floor($paise/10)] . ' '; $paise %= 10; }
        if ($paise > 0)   { $pWords .= $ones[$paise]; }
        $words .= ' and ' . trim($pWords) . ' Paise';
    }
    $words .= ' Only';
    return $words;
}

$amountInWords = numberToWords($amount);
$formattedDate = date('d/m/Y', strtotime($chequeDate));
$amountFigures = number_format($amount, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cheque Print — <?= htmlspecialchars($cheque['cheque_number'] ?? '') ?></title>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #e8e8e8;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 20px;
    }

    .print-actions {
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
    }
    .print-actions a,
    .print-actions button {
        padding: 10px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: opacity 0.2s;
    }
    .print-actions .btn-print {
        background: #0d9488;
        color: #fff;
    }
    .print-actions .btn-print:hover { opacity: 0.9; }
    .print-actions .btn-back {
        background: #fff;
        color: #333;
        border: 1px solid #ccc;
    }
    .print-actions .btn-back:hover { background: #f5f5f5; }

    .cheque-wrapper {
        width: 720px;
        height: 288px;
        background: #fff;
        border: 2px solid #333;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    /* Dashed tear line */
    .cheque-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 12px;
        width: 2px;
        height: 100%;
        background: repeating-linear-gradient(to bottom, #999 0px, #999 4px, transparent 4px, transparent 8px);
    }

    .cheque-inner {
        padding: 14px 20px 10px 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        font-size: 13px;
        color: #222;
    }

    /* Top row: bank + date */
    .cheque-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .bank-info {
        line-height: 1.5;
    }
    .bank-info .bank-name {
        font-size: 18px;
        font-weight: 700;
        color: #0a3d6b;
        letter-spacing: 0.5px;
    }
    .bank-info .branch-ifsc {
        font-size: 11px;
        color: #555;
    }
    .cheque-date {
        text-align: right;
        min-width: 140px;
    }
    .cheque-date label {
        display: block;
        font-size: 10px;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    .cheque-date .date-value {
        font-size: 16px;
        font-weight: 600;
        border-bottom: 1px solid #333;
        padding: 2px 8px;
        min-width: 120px;
        display: inline-block;
        text-align: center;
        letter-spacing: 1px;
    }

    /* Pay line */
    .cheque-pay {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-top: 4px;
    }
    .cheque-pay .pay-label {
        font-size: 12px;
        color: #555;
        white-space: nowrap;
    }
    .cheque-pay .pay-name {
        font-size: 16px;
        font-weight: 600;
        border-bottom: 1px solid #333;
        padding: 0 4px 2px 4px;
        flex: 1;
        min-width: 200px;
    }
    .cheque-pay .pay-suffix {
        font-size: 12px;
        color: #555;
        white-space: nowrap;
    }

    /* Amount in words */
    .cheque-amount-words {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }
    .cheque-amount-words .amt-label {
        font-size: 10px;
        color: #555;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .cheque-amount-words .amt-words {
        font-size: 12px;
        font-weight: 500;
        border-bottom: 1px solid #333;
        padding: 0 4px 2px 4px;
        flex: 1;
        min-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Amount in figures */
    .cheque-amount-fig {
        position: absolute;
        right: 24px;
        top: 110px;
        background: #f0f7ff;
        border: 1.5px solid #0a3d6b;
        border-radius: 4px;
        padding: 4px 10px;
        min-width: 110px;
        text-align: right;
    }
    .cheque-amount-fig .rupee-symbol {
        font-size: 12px;
        color: #0a3d6b;
        font-weight: 700;
    }
    .cheque-amount-fig .rupee-value {
        font-size: 16px;
        font-weight: 700;
        color: #0a3d6b;
        letter-spacing: 0.5px;
    }

    /* Account number */
    .cheque-acct {
        font-size: 10px;
        color: #666;
    }
    .cheque-acct span {
        font-weight: 600;
        color: #333;
    }

    /* Bottom row */
    .cheque-bottom {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .signatory-area {
        text-align: center;
        min-width: 160px;
    }
    .signatory-area .sig-line {
        border-top: 1px solid #333;
        width: 160px;
        margin: 0 auto;
        padding-top: 4px;
    }
    .signatory-area .sig-label {
        font-size: 10px;
        color: #555;
    }
    .signatory-area .sig-name {
        font-size: 11px;
        font-weight: 600;
        color: #333;
        margin-top: 2px;
    }

    .micr-line {
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        letter-spacing: 2px;
        color: #444;
        text-align: center;
        margin-top: 2px;
    }

    /* Background pattern (anti-forgery) */
    .cheque-bg-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0.03;
        background: repeating-linear-gradient(
            45deg,
            #0a3d6b 0px, #0a3d6b 1px,
            transparent 1px, transparent 8px
        );
        pointer-events: none;
    }

    /* Status badge */
    .status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 2px 10px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        z-index: 2;
    }
    .status-badge.issued    { background: #dbeafe; color: #1e40af; }
    .status-badge.cleared   { background: #d1fae5; color: #065f46; }
    .status-badge.bounced   { background: #fee2e2; color: #991b1b; }
    .status-badge.cancelled { background: #e5e7eb; color: #374151; }

    /* Print media */
    @media print {
        body {
            background: #fff;
            padding: 0;
            margin: 0;
        }
        .print-actions { display: none !important; }
        .cheque-wrapper {
            box-shadow: none;
            border: 1px solid #000;
            margin: 0;
            width: 100%;
            max-width: 180mm;
            height: auto;
            min-height: 80mm;
            page-break-inside: avoid;
        }
        .status-badge { display: none; }
        .cheque-bg-pattern { display: none; }
        @page {
            size: landscape;
            margin: 10mm;
        }
    }
</style>
</head>
<body>

<div class="print-actions">
    <button class="btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Cheque
    </button>
    <a class="btn-back" href="<?= BASE_URL ?>/admin/finance/cheques">
        <i class="fas fa-arrow-left"></i> Back to Register
    </a>
</div>

<div class="cheque-wrapper">
    <div class="cheque-bg-pattern"></div>

    <?php $st = $cheque['status'] ?? 'issued'; ?>
    <span class="status-badge <?= $st ?>"><?= htmlspecialchars(strtoupper($st)) ?></span>

    <div class="cheque-inner">

        <!-- Row 1: Bank + Date -->
        <div class="cheque-top">
            <div class="bank-info">
                <div class="bank-name"><?= htmlspecialchars($bankName) ?></div>
                <div class="branch-ifsc"><?= htmlspecialchars($branch) ?><?= $branch && $ifsc ? ' &bull; ' : '' ?>IFSC: <?= htmlspecialchars($ifsc) ?></div>
            </div>
            <div class="cheque-date">
                <label>Date</label>
                <div class="date-value"><?= htmlspecialchars($formattedDate) ?></div>
            </div>
        </div>

        <!-- Row 2: Pay -->
        <div class="cheque-pay">
            <span class="pay-label">Pay</span>
            <span class="pay-name"><?= htmlspecialchars($payee) ?></span>
            <span class="pay-suffix">or Bearer</span>
        </div>

        <!-- Row 3: Amount in words -->
        <div class="cheque-amount-words">
            <span class="amt-label">Rupees</span>
            <span class="amt-words"><?= htmlspecialchars($amountInWords) ?></span>
        </div>

        <!-- Row 4: Account + Signatory + Amount figure -->
        <div class="cheque-bottom">
            <div>
                <div class="cheque-acct">A/c No: <span><?= htmlspecialchars($acctNo) ?></span></div>
                <?php if ($purpose): ?>
                    <div class="cheque-acct" style="margin-top: 2px;">Purpose: <?= htmlspecialchars($purpose) ?></div>
                <?php endif; ?>
            </div>

            <div class="signatory-area">
                <div class="sig-line"></div>
                <div class="sig-label">Authorized Signatory</div>
                <?php if ($signatory): ?>
                    <div class="sig-name"><?= htmlspecialchars($signatory) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Amount in figures -->
        <div class="cheque-amount-fig">
            <span class="rupee-symbol">&#8377;</span>
            <span class="rupee-value"><?= htmlspecialchars($amountFigures) ?></span>
        </div>

    </div>
</div>

</body>
</html>
