<?php
/** Registry-day dossier packet — printable A4 compile. Read-only view:
 *  every prefilled value comes from live tables; unknowns are blank lines. */
/** @var array $booking */
/** @var array $plot */
/** @var array $customer */
/** @var array $schedule */
/** @var array $milestones */
/** @var array $eligibility */
/** @var array $titleCert */
$booking     = $booking ?? [];
$plot        = $plot ?? [];
$customer    = $customer ?? [];
$schedule    = $schedule ?? [];
$milestones  = $milestones ?? [];
$eligibility = $eligibility ?? [];
$titleCert   = $titleCert ?? [];
$appointment = $appointment ?? '';
$paid_total  = $paid_total ?? 0;
$due_total   = $due_total ?? 0;
$base        = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$blank = '<span class="fill"></span>';
$inr = function ($v) { return '₹' . number_format((float)$v, 2); };
$doneStages = 0;
foreach ($milestones as $m) { if (($m['status'] ?? '') === 'completed') $doneStages++; }
?>
<style>
.reg-packet { background: #fff; color: #111; font-size: 13px; }
.reg-packet .sheet { max-width: 800px; margin: 0 auto 18px; border: 1px solid #999; padding: 22px 26px; background: #fff; }
.reg-packet h2.sec { font-size: 16px; border-bottom: 2px solid #111; padding-bottom: 4px; margin: 0 0 12px; }
.reg-packet h2.sec .hin { font-weight: normal; }
.reg-packet table.kv { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.reg-packet table.kv td { border: 1px solid #bbb; padding: 5px 8px; vertical-align: top; }
.reg-packet table.kv td.k { width: 32%; background: #f4f4f4; font-weight: 600; }
.reg-packet .fill { display: inline-block; min-width: 180px; border-bottom: 1px dotted #111; }
.reg-packet .sigrow { display: flex; gap: 24px; margin-top: 26px; }
.reg-packet .sigbox { flex: 1; border-top: 1px solid #111; padding-top: 4px; font-size: 12px; }
.reg-packet .note { font-size: 12px; color: #444; }
.reg-packet .toolbar { max-width: 800px; margin: 0 auto 14px; display: flex; gap: 8px; }
@media print {
    .reg-packet .toolbar, .admin-sidebar, .sidebar, header.admin-header { display: none !important; }
    .reg-packet .sheet { border: none; margin: 0; padding: 0 0 10px; }
    .reg-packet .page-break { page-break-after: always; }
    body { background: #fff !important; }
}
</style>
<div class="reg-packet">
    <div class="toolbar">
        <a href="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/registry-stepper" class="btn btn-outline-secondary btn-sm">← Back to Stepper</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Dossier</button>
    </div>

    <!-- 1. Cover + index -->
    <div class="sheet">
        <h2 class="sec">1. Registry Check-Slip &amp; Document Index <span class="hin">(टोकन / सूची)</span></h2>
        <table class="kv">
            <tr><td class="k">Booking No.</td><td><?= $e($booking['booking_number'] ?? '') ?></td><td class="k">Booking Date</td><td><?= $e($booking['booking_date'] ?? $booking['created_at'] ?? '') ?></td></tr>
            <tr><td class="k">Buyer</td><td><?= $e($customer['name'] ?? $booking['customer_name'] ?? '') ?> (<?= $e($customer['phone'] ?? $booking['customer_phone'] ?? '') ?>)</td><td class="k">Plot</td><td><?= $e($plot['plot_number'] ?? $booking['plot_number'] ?? '') ?>, <?= $e($plot['colony_name'] ?? $booking['colony_name'] ?? '') ?></td></tr>
            <tr><td class="k">Milestones Done</td><td><?= (int)$doneStages ?> / <?= count($milestones) ?></td><td class="k">Sub-Registrar Appt.</td><td><?= $appointment !== '' ? $e($appointment) : $blank ?></td></tr>
            <tr><td class="k">Amount Paid</td><td><?= $inr($paid_total) ?></td><td class="k">Balance Due</td><td><?= $inr($due_total) ?></td></tr>
        </table>
        <p class="note">Packet contents: ① check-slip ② sale-deed draft ③ payment/NOC statement ④ title summary ⑤ buyer KYC + 2 witness sheets ⑥ possession slip. Tick each before leaving for the kachahari.</p>
    </div>

    <!-- 2. Sale deed draft -->
    <div class="sheet page-break">
        <h2 class="sec">2. Hindi Sale-Deed Draft <span class="hin">(विक्रय विलेख मसौदा — ड्राफ्ट)</span></h2>
        <p class="note">DRAFT only — get vetted by the legal desk before execution. चौहद्दी नीचे कार्यालय में भरें।</p>
        <table class="kv">
            <tr><td class="k">Khasra No.</td><td><?= $e($plot['khasra_number'] ?? '') !== '' ? $e($plot['khasra_number']) : $blank ?></td><td class="k">Survey No.</td><td><?= $e($plot['survey_number'] ?? '') !== '' ? $e($plot['survey_number']) : $blank ?></td></tr>
            <tr><td class="k">Plot No.</td><td><?= $e($plot['plot_number'] ?? '') ?></td><td class="k">Area</td><td><?= $e($plot['area_sqft'] ?? '') ?> sqft<?= $e($plot['facing'] ?? '') !== '' ? ', facing ' . $plot['facing'] : '' ?></td></tr>
            <tr><td class="k">Colony</td><td colspan="3"><?= $e($plot['colony_name'] ?? $booking['colony_name'] ?? '') ?></td></tr>
            <tr><td class="k">Consideration</td><td colspan="3"><?= $inr($plot['total_price'] ?? $booking['plot_total_price'] ?? 0) ?> (as per booking)</td></tr>
            <tr><td class="k">चौहद्दी — पूरब</td><td><?= $blank ?></td><td class="k">पश्चिम</td><td><?= $blank ?></td></tr>
            <tr><td class="k">चौहद्दी — उत्तर</td><td><?= $blank ?></td><td class="k">दक्षिण</td><td><?= $blank ?></td></tr>
            <tr><td class="k">Seller (vendor)</td><td colspan="3"><?= $blank ?> &nbsp; S/o <?= $blank ?></td></tr>
            <tr><td class="k">Buyer (vendee)</td><td colspan="3"><?= $e($customer['name'] ?? '') ?> &nbsp; S/o, D/o, W/o <?= $blank ?></td></tr>
            <?php $co_owners = $co_owners ?? []; if (!empty($co_owners)): ?>
            <tr><td class="k">Joint buyers</td><td colspan="3"><?php foreach ($co_owners as $co): ?>· <?= $e($co['full_name'] ?? '') ?> (<?= $e($co['relationship'] ?? '—') ?>, <?= $e($co['gender'] ?? '') ?>, <?= $e($co['share_percentage'] ?? '') ?>%) &nbsp;<?php endforeach; ?></td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- 3. Payment / NOC statement -->
    <div class="sheet page-break">
        <h2 class="sec">3. Payment Statement &amp; No-Dues Summary <span class="hin">(भुगतान / NOC)</span></h2>
        <table class="kv">
            <tr><td class="k">Registry Eligible</td><td><?= !empty($eligibility['eligible']) ? '<strong>YES</strong>' : '<strong>NO</strong> — ' . $e(implode('; ', (array)($eligibility['reasons'] ?? []))) ?></td><td class="k">Dues Clear</td><td><?= ((float)$due_total <= 0) ? '<strong>YES — NIL balance</strong>' : '<strong>NO — ' . $inr($due_total) . ' due</strong>' ?></td></tr>
        </table>
        <?php if (!empty($schedule)): ?>
        <table class="kv">
            <tr><td class="k">#</td><td class="k">Installment</td><td class="k">Amount</td><td class="k">Paid</td></tr>
            <?php $i = 1; foreach ($schedule as $s): ?>
            <tr><td><?= $i++ ?></td><td><?= $e($s['label'] ?? $s['title'] ?? $s['installment_no'] ?? '-') ?></td><td><?= $inr($s['amount'] ?? $s['due_amount'] ?? 0) ?></td><td><?= $inr($s['paid_amount'] ?? $s['amount_paid'] ?? 0) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p class="note">No installment schedule on record — attach manual receipts. Lines: <?= $blank ?></p>
        <?php endif; ?>
    </div>

    <!-- 4. Title summary -->
    <div class="sheet">
        <h2 class="sec">4. Title Search &amp; Non-Encumbrance Summary <span class="hin">(टाइटल)</span></h2>
        <?php if (!empty($titleCert)): ?>
        <table class="kv">
            <tr><td class="k">Certificate No.</td><td><?= $e($titleCert['cert_no'] ?? '') ?></td><td class="k">Generated</td><td><?= $e($titleCert['created_at'] ?? '') ?></td></tr>
            <tr><td class="k">Verdict</td><td colspan="3"><?= $e($titleCert['verdict'] ?? '') ?></td></tr>
            <tr><td class="k">Full Report</td><td colspan="3"><?= $e($titleCert['file_path'] ?? '') !== '' ? 'Filed at: ' . $e($titleCert['file_path']) : 'On file with legal desk' ?></td></tr>
        </table>
        <?php else: ?>
        <p class="note"><strong>Title chain under compilation</strong> — no certificate on record for this plot. Get the legal desk to generate one before registry day; attach EC + khasra chain printouts here.</p>
        <?php endif; ?>
    </div>

    <!-- 5. KYC + witnesses -->
    <div class="sheet page-break">
        <h2 class="sec">5. Buyer KYC &amp; Witness Sheets <span class="hin">(केवाईसी / गवाह)</span></h2>
        <table class="kv">
            <tr><td class="k">Buyer Name</td><td><?= $e($customer['name'] ?? '') ?></td><td class="k">Phone</td><td><?= $e($customer['phone'] ?? '') ?></td></tr>
            <tr><td class="k">Address</td><td colspan="3"><?= $e($customer['address'] ?? '') !== '' ? $e($customer['address']) : $blank ?></td></tr>
            <tr><td class="k">Aadhaar</td><td><?= $e($customer['aadhaar_number'] ?? '') !== '' ? $e($customer['aadhaar_number']) : $blank ?></td><td class="k">PAN</td><td><?= $e($customer['pan_number'] ?? '') !== '' ? $e($customer['pan_number']) : $blank ?></td></tr>
            <tr><td class="k">KYC Status</td><td colspan="3"><?= $e($customer['kyc_status'] ?? 'unverified') ?></td></tr>
        </table>
        <?php for ($w = 1; $w <= 2; $w++): ?>
        <p><strong>Witness <?= $w ?> (गवाह <?= $w ?>):</strong> Name <?= $blank ?> &nbsp; S/o <?= $blank ?> &nbsp; Aadhaar <?= $blank ?><br>Address: <?= $blank ?></p>
        <div class="sigrow"><div class="sigbox">Photo + thumbprint (witness <?= $w ?>)</div><div class="sigbox">Signature (witness <?= $w ?>)</div></div>
        <?php endfor; ?>
    </div>

    <!-- 6. Possession slip -->
    <div class="sheet">
        <h2 class="sec">6. Possession Handover Slip <span class="hin">(कब्ज़ा पर्ची)</span></h2>
        <p>Plot <strong><?= $e($plot['plot_number'] ?? '') ?></strong>, <?= $e($plot['colony_name'] ?? '') ?> handed over to <strong><?= $e($customer['name'] ?? '') ?></strong> (Booking <?= $e($booking['booking_number'] ?? '') ?>) on date <?= $blank ?>. Pillars demarcated: Yes / No (circle).</p>
        <div class="sigrow"><div class="sigbox">Buyer signature + date</div><div class="sigbox">Site engineer signature + date</div><div class="sigbox">Company seal</div></div>
    </div>
</div>
