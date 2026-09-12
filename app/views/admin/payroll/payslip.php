<?php
// Standalone printable Indian corporate payslip (emitted as-is, no layout).
// Vars: $payslip (employee_payslips + employee_name/email), $extra (employees bank/pan),
// $period_label (MM/YYYY), $BASE_URL (auto-injected).
$payslip = $payslip ?? [];
$extra = $extra ?? [];
$period_label = $period_label ?? '';
$BASE_URL = $BASE_URL ?? '';
if (!function_exists('numberToWords')) {
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
        return $words . ' Only';
    }
}
$basic = (float)($payslip['basic_salary'] ?? 0);
$hra = (float)($payslip['hra'] ?? 0);
$allow = (float)($payslip['allowances'] ?? 0);
$gross = $basic + $hra + $allow;
$pf = (float)($payslip['pf'] ?? 0);
$esi = (float)($payslip['esi'] ?? 0);
$tds = (float)($payslip['tds'] ?? 0);
$pt = (float)($payslip['professional_tax'] ?? 0);
$other = (float)($payslip['deductions'] ?? 0);
$totalDed = $pf + $esi + $tds + $pt + $other;
$net = (float)($payslip['net_salary'] ?? max(0, $gross - $totalDed));
$words = numberToWords($net);
$pid = (int)($payslip['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payslip <?= $pid ?> (<?= htmlspecialchars($period_label) ?>) - APS Dream Home</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; background: #eef1f5; color: #111; margin: 0; padding: 24px; }
  .toolbar { max-width: 860px; margin: 0 auto 16px; display: flex; gap: 10px; flex-wrap: wrap; }
  .toolbar a, .toolbar button { display: inline-block; padding: 9px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; cursor: pointer; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; }
  .toolbar a.primary { background: #0a192f; color: #fff; border-color: #0a192f; }
  .toolbar a.success { background: #047857; color: #fff; border-color: #047857; }
  .sheet { max-width: 860px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; }
  .chead { background: #0a192f; color: #fff; padding: 22px 28px; display: flex; justify-content: space-between; align-items: center; }
  .chead h1 { margin: 0; font-size: 22px; letter-spacing: 1px; }
  .chead p { margin: 4px 0 0; font-size: 12px; color: #cbd5e1; }
  .chead .doc { text-align: right; }
  .chead .doc strong { font-size: 16px; color: #d4af37; }
  .meta { display: flex; flex-wrap: wrap; border-bottom: 2px solid #0a192f; }
  .meta div { width: 50%; padding: 8px 28px; font-size: 13px; border-top: 1px solid #e2e8f0; }
  .meta div:nth-child(odd) { border-right: 1px solid #e2e8f0; }
  .meta span { color: #64748b; display: inline-block; min-width: 150px; }
  table { width: 100%; border-collapse: collapse; font-size: 14px; }
  th { background: #f1f5f9; text-align: left; padding: 10px 28px; border-bottom: 2px solid #0a192f; }
  td { padding: 8px 28px; border-bottom: 1px solid #e2e8f0; }
  td.num, th.num { text-align: right; }
  tr.total td { font-weight: bold; background: #f8fafc; }
  tr.net td { font-weight: bold; font-size: 16px; background: #0a192f; color: #fff; }
  .words { padding: 12px 28px; font-size: 13px; background: #fffbeb; border-bottom: 1px solid #e2e8f0; }
  .sign { display: flex; justify-content: space-between; padding: 40px 28px 24px; font-size: 13px; }
  .sign div { text-align: center; }
  .sign .line { border-top: 1px solid #111; padding-top: 6px; min-width: 200px; }
  .foot { padding: 12px 28px; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; }
  @media print {
    body { background: #fff; padding: 0; }
    .toolbar { display: none; }
    .sheet { border: none; max-width: 100%; }
  }
</style>
</head>
<body>
<div class="toolbar">
  <a href="<?= htmlspecialchars($BASE_URL) ?>/admin/payroll">&#8592; Back to Payroll</a>
  <button onclick="window.print()">🖨️ Print</button>
  <a class="success" href="<?= htmlspecialchars($BASE_URL) ?>/admin/payroll/payslip/<?= $pid ?>/pdf">⬇ Download PDF</a>
</div>
<div class="sheet">
  <div class="chead">
    <div>
      <h1>APS DREAM HOME</h1>
      <p>Gorakhpur, UP | +91 92771 21112 | www.apsdreamhome.com</p>
    </div>
    <div class="doc">
      <strong>PAYSLIP</strong>
      <p>No: PAY-<?= str_pad((string)$pid, 6, '0', STR_PAD_LEFT) ?> | Period: <?= htmlspecialchars($period_label) ?></p>
      <p>Status: <?= htmlspecialchars(strtoupper($payslip['status'] ?? 'draft')) ?></p>
    </div>
  </div>
  <div class="meta">
    <div><span>Employee Name</span><strong><?= htmlspecialchars($payslip['employee_name'] ?? '') ?></strong></div>
    <div><span>Employee Code</span><strong><?= htmlspecialchars($extra['employee_code'] ?? 'N/A') ?></strong></div>
    <div><span>Department / Designation</span><?= htmlspecialchars(($extra['department'] ?? 'N/A') . ' / ' . ($extra['designation'] ?? 'N/A')) ?></div>
    <div><span>Email</span><?= htmlspecialchars($payslip['employee_email'] ?? '') ?></div>
    <div><span>PAN</span><?= htmlspecialchars($extra['pan_number'] ?? 'N/A') ?></div>
    <div><span>Bank A/c | IFSC</span><?= htmlspecialchars(($extra['bank_account'] ?? 'N/A') . ' | ' . ($extra['bank_ifsc'] ?? 'N/A')) ?></div>
    <div><span>Days Worked (Present)</span><?= (int)($payslip['days_present'] ?? 0) ?></div>
    <div><span>LOP Days</span><?= (int)($payslip['lop_days'] ?? 0) ?></div>
  </div>
  <table>
    <tr><th>Earnings</th><th class="num">Amount (₹)</th></tr>
    <tr><td>Basic Salary</td><td class="num"><?= number_format($basic, 2) ?></td></tr>
    <tr><td>House Rent Allowance (HRA)</td><td class="num"><?= number_format($hra, 2) ?></td></tr>
    <tr><td>Other Allowances</td><td class="num"><?= number_format($allow, 2) ?></td></tr>
    <tr class="total"><td>Gross Earnings</td><td class="num">₹<?= number_format($gross, 2) ?></td></tr>
  </table>
  <table>
    <tr><th>Deductions</th><th class="num">Amount (₹)</th></tr>
    <tr><td>Provident Fund (12% of basic)</td><td class="num"><?= number_format($pf, 2) ?></td></tr>
    <tr><td>ESI (0.75%)</td><td class="num"><?= number_format($esi, 2) ?></td></tr>
    <tr><td>TDS</td><td class="num"><?= number_format($tds, 2) ?></td></tr>
    <tr><td>Professional Tax</td><td class="num"><?= number_format($pt, 2) ?></td></tr>
    <tr><td>Other Deductions (incl. LOP)</td><td class="num"><?= number_format($other, 2) ?></td></tr>
    <tr class="total"><td>Total Deductions</td><td class="num">₹<?= number_format($totalDed, 2) ?></td></tr>
    <tr class="net"><td>Net Pay</td><td class="num">₹<?= number_format($net, 2) ?></td></tr>
  </table>
  <div class="words"><strong>Net in words:</strong> <?= htmlspecialchars($words) ?></div>
  <div class="sign">
    <div><div class="line">Employee Signature</div></div>
    <div><div class="line">Authorized Signatory<br>APS Dream Home (Seal)</div></div>
  </div>
  <div class="foot">This is a computer-generated payslip. No signature required. For queries: support@apsdreamhome.com | +91 92771 21112</div>
</div>
</body>
</html>
