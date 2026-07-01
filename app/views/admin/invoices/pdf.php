<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tax Invoice <?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></title>
<style>
    @page { size: A4; margin: 15mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; }
    .invoice-box { max-width: 800px; margin: 0 auto; padding: 30px; }

    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #0d9488; padding-bottom: 20px; margin-bottom: 20px; }
    .header-left h1 { font-size: 22px; color: #0d9488; margin-bottom: 4px; }
    .header-left .subtitle { font-size: 11px; color: #64748b; }
    .header-right { text-align: right; }
    .header-right .inv-label { font-size: 20px; font-weight: 700; color: #0d9488; text-transform: uppercase; }
    .header-right .inv-number { font-size: 14px; font-weight: 600; }

    .parties { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 30px; }
    .party-box { flex: 1; }
    .party-box h3 { font-size: 10px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    .party-box .name { font-size: 14px; font-weight: 700; }
    .party-box p { font-size: 11px; color: #475569; margin: 2px 0; }

    .meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; background: #f8fafc; padding: 14px; border-radius: 6px; border: 1px solid #e2e8f0; }
    .meta-grid .item label { font-size: 9px; text-transform: uppercase; color: #64748b; display: block; letter-spacing: 0.5px; }
    .meta-grid .item span { font-size: 12px; font-weight: 600; }

    table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.items th { background: #1e293b; color: #fff; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    table.items th:first-child { border-radius: 4px 0 0 0; }
    table.items th:last-child { border-radius: 0 4px 0 0; }
    table.items td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    table.items tr:nth-child(even) { background: #f8fafc; }
    table.items .text-right { text-align: right; }
    table.items .text-center { text-align: center; }

    .totals { display: flex; justify-content: flex-end; margin-bottom: 25px; }
    .totals-table { width: 320px; }
    .totals-table table { width: 100%; border-collapse: collapse; }
    .totals-table td { padding: 5px 10px; font-size: 11px; }
    .totals-table .total-row { background: #0d9488; color: #fff; font-weight: 700; font-size: 13px; }
    .totals-table .total-row td { padding: 8px 10px; }

    .gst-breakdown { background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 12px; margin-bottom: 20px; }
    .gst-breakdown h4 { font-size: 11px; color: #166534; margin-bottom: 8px; }
    .gst-breakdown .row { display: flex; gap: 30px; }
    .gst-breakdown .col { font-size: 11px; }
    .gst-breakdown .col label { color: #166534; font-weight: 600; }

    .terms { margin-bottom: 20px; }
    .terms h4 { font-size: 11px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px; }
    .terms p { font-size: 10px; color: #475569; margin-bottom: 4px; }

    .bank-details { background: #eff6ff; border: 1px solid #93c5fd; border-radius: 6px; padding: 12px; margin-bottom: 20px; }
    .bank-details h4 { font-size: 11px; color: #1e40af; margin-bottom: 6px; }
    .bank-details .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
    .bank-details .grid span { font-size: 10px; color: #1e40af; }
    .bank-details .grid strong { font-size: 10px; }

    .footer { text-align: center; border-top: 2px solid #e2e8f0; padding-top: 15px; margin-top: 25px; }
    .footer p { font-size: 10px; color: #64748b; }
    .footer .thanks { font-size: 14px; font-weight: 700; color: #0d9488; margin-bottom: 5px; }

    .stamp-area { display: flex; justify-content: space-between; margin-top: 30px; }
    .stamp-box { width: 200px; border-top: 1px solid #94a3b8; text-align: center; padding-top: 5px; }
    .stamp-box p { font-size: 9px; color: #64748b; }

    @media print {
        body { font-size: 11px; }
        .invoice-box { padding: 0; max-width: 100%; }
        .no-print { display: none !important; }
    }

    .print-btn { position: fixed; bottom: 30px; right: 30px; background: #0d9488; color: #fff; border: none; border-radius: 50%; width: 56px; height: 56px; font-size: 20px; cursor: pointer; box-shadow: 0 4px 12px rgba(13,148,136,0.4); z-index: 999; }
    .print-btn:hover { background: #4338ca; }
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()" title="Print Invoice"><i class="fas fa-print"></i></button>

<div class="invoice-box">
    <div class="header">
        <div class="header-left">
            <h1><?= htmlspecialchars($company['company_name'] ?? 'APS Dream Home') ?></h1>
            <div class="subtitle"><?= htmlspecialchars($company['address'] ?? '') ?></div>
            <?php if (!empty($company['gstin'])): ?>
                <div class="subtitle">GSTIN: <?= htmlspecialchars($company['gstin']) ?></div>
            <?php endif; ?>
            <div class="subtitle">PAN: <?= htmlspecialchars($company['pan'] ?? '') ?></div>
            <div class="subtitle"><?= htmlspecialchars($company['phone'] ?? '') ?> | <?= htmlspecialchars($company['email'] ?? '') ?></div>
        </div>
        <div class="header-right">
            <div class="inv-label">Tax Invoice</div>
            <div class="inv-number"><?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></div>
            <div class="subtitle"><?= htmlspecialchars($invoice['status_label'] ?? '') ?></div>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h3>Bill To</h3>
            <div class="name"><?= htmlspecialchars($invoice['client_name'] ?? '') ?></div>
            <?php if (!empty($invoice['client_email'])): ?><p><?= htmlspecialchars($invoice['client_email']) ?></p><?php endif; ?>
            <?php if (!empty($invoice['client_phone'])): ?><p><?= htmlspecialchars($invoice['client_phone']) ?></p><?php endif; ?>
            <?php if (!empty($invoice['client_address'])): ?><p><?= nl2br(htmlspecialchars($invoice['client_address'])) ?></p><?php endif; ?>
            <?php if (!empty($invoice['gstin'])): ?><p><strong>GSTIN:</strong> <?= htmlspecialchars($invoice['gstin']) ?></p><?php endif; ?>
        </div>
        <div class="party-box" style="text-align:right;">
            <h3>Ship To</h3>
            <?php if (!empty($invoice['shipping_address'])): ?>
                <p><?= nl2br(htmlspecialchars($invoice['shipping_address'])) ?></p>
            <?php else: ?>
                <p class="text-muted">Same as billing address</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="meta-grid">
        <div class="item"><label>Invoice Date</label><span><?= htmlspecialchars($invoice['invoice_date'] ?? '') ?></span></div>
        <div class="item"><label>Due Date</label><span><?= htmlspecialchars($invoice['due_date'] ?? '') ?></span></div>
        <div class="item"><label>Place of Supply</label><span><?= htmlspecialchars($invoice['place_of_supply'] ?? '') ?></span></div>
        <div class="item"><label>Currency</label><span><?= htmlspecialchars($invoice['currency'] ?? 'INR') ?></span></div>
    </div>

    <div class="table-responsive"><table class="items">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:35%">Description</th>
                <th style="width:10%" class="text-center">Qty</th>
                <th style="width:15%" class="text-right">Unit Price</th>
                <th style="width:10%" class="text-right">Disc %</th>
                <th style="width:10%" class="text-right">Tax %</th>
                <th style="width:15%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 0; foreach (($invoice['items'] ?? []) as $item): $i++; ?>
                <tr>
                    <td><?= $i ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item['item_name'] ?? '') ?></strong>
                        <?php if (!empty($item['item_description'])): ?>
                            <br><span style="font-size:10px;color:#64748b;"><?= htmlspecialchars($item['item_description']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= (int)($item['quantity'] ?? 1) ?></td>
                    <td class="text-right">₹<?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                    <td class="text-right"><?= ($item['discount_percent'] ?? 0) > 0 ? $item['discount_percent'] . '%' : '-' ?></td>
                    <td class="text-right"><?= ($item['tax_percent'] ?? 0) > 0 ? $item['tax_percent'] . '%' : '-' ?></td>
                    <td class="text-right"><strong>₹<?= number_format($item['line_total'] ?? 0, 2) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div>

    <div class="totals">
        <div class="totals-table">
            <div class="table-responsive"><table>
                <tr><td>Subtotal</td><td class="text-right">₹<?= number_format($invoice['subtotal'] ?? 0, 2) ?></td></tr>
                <?php if (($invoice['discount_amount'] ?? 0) > 0): ?>
                    <tr><td style="color:#dc2626">Discount</td><td class="text-right" style="color:#dc2626">-₹<?= number_format($invoice['discount_amount'], 2) ?></td></tr>
                <?php endif; ?>
                <?php if (($invoice['tax_amount'] ?? 0) > 0): ?>
                    <tr><td>Tax (GST)</td><td class="text-right">₹<?= number_format($invoice['tax_amount'], 2) ?></td></tr>
                <?php endif; ?>
                <tr class="total-row"><td>TOTAL</td><td class="text-right">₹<?= number_format($invoice['total_amount'] ?? 0, 2) ?></td></tr>
            </table></div>
        </div>
    </div>

    <?php if (($invoice['tax_amount'] ?? 0) > 0): ?>
        <div class="gst-breakdown">
            <h4>GST Breakdown</h4>
            <div class="row">
                <?php if (($invoice['gst_type'] ?? '') === 'cgst_sgst'): ?>
                    <div class="col"><label>CGST (<?= number_format(($invoice['gst_rate'] ?? 18) / 2, 1) ?>%)</label> ₹<?= number_format($invoice['cgst_amount'] ?? 0, 2) ?></div>
                    <div class="col"><label>SGST (<?= number_format(($invoice['gst_rate'] ?? 18) / 2, 1) ?>%)</label> ₹<?= number_format($invoice['sgst_amount'] ?? 0, 2) ?></div>
                <?php else: ?>
                    <div class="col"><label>IGST (<?= number_format($invoice['gst_rate'] ?? 18, 1) ?>%)</label> ₹<?= number_format($invoice['igst_amount'] ?? 0, 2) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($company['bank_name'])): ?>
        <div class="bank-details">
            <h4>Bank Details</h4>
            <div class="grid">
                <span>Bank: <strong><?= htmlspecialchars($company['bank_name']) ?></strong></span>
                <span>Account: <strong><?= htmlspecialchars($company['bank_account']) ?></strong></span>
                <span>IFSC: <strong><?= htmlspecialchars($company['bank_ifsc']) ?></strong></span>
                <span>Branch: <strong><?= htmlspecialchars($company['bank_branch']) ?></strong></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="terms">
        <h4>Terms & Conditions</h4>
        <p>1. Payment is due within 30 days of the invoice date.</p>
        <p>2. Late payments may attract interest at 18% per annum.</p>
        <p>3. Subject to Gorakhpur (Uttar Pradesh) jurisdiction for any disputes.</p>
        <p>4. This is a computer-generated invoice and does not require a physical signature.</p>
        <?php if (!empty($invoice['payment_terms'])): ?>
            <p><strong>Additional:</strong> <?= htmlspecialchars($invoice['payment_terms']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($invoice['notes'])): ?>
        <div class="terms">
            <h4>Notes</h4>
            <p><?= htmlspecialchars($invoice['notes']) ?></p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <div class="thanks">Thank you for your business!</div>
        <p><?= htmlspecialchars($company['company_name'] ?? 'APS Dream Home') ?> | <?= htmlspecialchars($company['address'] ?? '') ?></p>
        <p><?= htmlspecialchars($company['phone'] ?? '') ?> | <?= htmlspecialchars($company['email'] ?? '') ?></p>
    </div>

    <div class="stamp-area">
        <div class="stamp-box">
            <p>Authorized Signatory</p>
        </div>
        <div class="stamp-box">
            <p>Company Stamp & Seal</p>
        </div>
    </div>
</div>
</body>
</html>
