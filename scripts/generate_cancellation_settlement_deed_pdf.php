<?php
/**
 * Cancellation & Settlement Deed PDF Generator
 * Generates:
 * 1. Standard A4 Printable PDF
 * 2. Non-Judicial Stamp Paper PDF (with 95mm top blank margin for ₹100/₹500 stamp)
 * 3. MS Word / Google Docs compatible file (.doc)
 * 4. Interactive Web Printable HTML view
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Ensure target directories exist
$downloadsDir = __DIR__ . '/../public/downloads';
$docsDir = __DIR__ . '/../public/documents';
if (!is_dir($downloadsDir)) mkdir($downloadsDir, 0755, true);
if (!is_dir($docsDir)) mkdir($docsDir, 0755, true);

class CancellationDeedPDF extends TCPDF
{
    public $isStampPaper = false;

    // Header
    public function Header()
    {
        if ($this->page == 1 && $this->isStampPaper) {
            // Space reserved for ₹100 / ₹500 Non-Judicial Stamp Paper
            $this->SetFont('helvetica', 'I', 8);
            $this->SetTextColor(160, 160, 160);
            $this->SetXY(15, 10);
            $this->Cell(180, 5, '[ SPACE RESERVED FOR RS. 100/- OR RS. 500/- NON-JUDICIAL STAMP PAPER ]', 0, 0, 'C');
            return;
        }

        // Standard Company Header on Page 1 (non-stamp) or header on subsequent pages
        if ($this->page == 1) {
            $this->SetFont('helvetica', 'B', 15);
            $this->SetTextColor(11, 28, 44);
            $this->Cell(0, 7, 'APS DREAM HOMES PVT. LTD.', 0, 1, 'C');

            $this->SetFont('helvetica', 'B', 8);
            $this->SetTextColor(41, 128, 185);
            $this->Cell(0, 4, 'CIN: U70109UP2022PTC163047  |  ROC: KANPUR  |  EST. APRIL 2022', 0, 1, 'C');

            $this->SetFont('helvetica', '', 7.5);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(0, 4, 'Regd. Office: 1st Floor, APS Building, Near Ganpati Lawn, Singhariya Chauraha, Kunraghat, Gorakhpur - 273008', 0, 1, 'C');
            $this->Cell(0, 4, 'Phone: +91 92771 21112, +91 551 356 8563  |  Email: official@apsdreamhomes.com  |  Web: apsdreamhomes.com', 0, 1, 'C');

            $this->SetDrawColor(41, 128, 185);
            $this->SetLineWidth(0.6);
            $this->Line(15, $this->GetY() + 1, 195, $this->GetY() + 1);
            $this->Ln(4);
        } else {
            $this->SetFont('helvetica', 'I', 8);
            $this->SetTextColor(130, 130, 130);
            $this->Cell(100, 5, 'APS Dream Homes Pvt. Ltd. — Cancellation & Ex-Gratia Settlement Deed', 0, 0, 'L');
            $this->Cell(80, 5, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 1, 'R');
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.2);
            $this->Line(15, $this->GetY(), 195, $this->GetY());
            $this->Ln(3);
        }
    }

    // Footer
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 7.5);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(100, 4, 'Strictly Confidential — Governed by Master Deed 2026 & Gorakhpur Jurisdiction', 0, 0, 'L');
        $this->Cell(80, 4, 'Second Party Initials: _______ | First Party Initials: _______', 0, 1, 'R');
    }
}

function generateDeedHtmlContent($isStampPaper = false) {
    $topSpacer = $isStampPaper ? '<div style="height: 340px;"></div>' : '';
    
    return <<<HTML
    <style>
        body { font-family: helvetica, sans-serif; font-size: 8.5pt; color: #222; line-height: 1.35; }
        h1 { font-size: 11.5pt; font-weight: bold; text-align: center; color: #0b1c2c; margin-bottom: 2px; text-transform: uppercase; }
        .sub-title { font-size: 8pt; font-weight: bold; text-align: center; color: #c0392b; margin-bottom: 8px; }
        .section-title { font-size: 8.5pt; font-weight: bold; background-color: #f1f5f9; color: #0b1c2c; padding: 3px 6px; margin-top: 6px; margin-bottom: 4px; border-left: 3px solid #2980b9; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 3px; margin-bottom: 5px; }
        table.grid td { border: 0.5px solid #cbd5e1; padding: 4px 6px; font-size: 8pt; vertical-align: middle; }
        table.grid th { border: 0.5px solid #cbd5e1; background-color: #f8fafc; font-weight: bold; font-size: 8pt; padding: 4px 6px; text-align: left; }
        .highlight-box { background-color: #fff7ed; border: 1px solid #fdba74; padding: 6px; margin-top: 4px; margin-bottom: 6px; }
        .red-alert-box { background-color: #fef2f2; border: 1px solid #f87171; padding: 6px; margin-top: 4px; margin-bottom: 6px; }
        .clause-item { margin-bottom: 4px; text-align: justify; }
        .sign-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .sign-box { border: 0.5px solid #94a3b8; padding: 6px; text-align: center; height: 75px; }
    </style>

    $topSpacer

    <h1>DEED OF BOOKING CANCELLATION, RECEIPT SURRENDER &amp; EX-GRATIA SETTLEMENT</h1>
    <div class="sub-title">STRICTLY EXECUTED UNDER TRIPARTITE MASTER LEGAL DEED (SECTIONS 2.1 &amp; 2.9) &amp; COMPANIES ACT 2013</div>

    <p style="text-align: justify; margin-bottom: 4px;">
        This Deed of Cancellation, Receipt Surrender and Full &amp; Final Settlement is executed on this <strong>______ day of __________________, 2026</strong> at Gorakhpur, Uttar Pradesh, by and between:
    </p>

    <table class="grid">
        <tr>
            <td style="width: 25%; background-color: #f8fafc; font-weight: bold;">FIRST PARTY<br/>(Developer / Company)</td>
            <td style="width: 75%;">
                <strong>APS DREAM HOMES PVT. LTD.</strong> (CIN: U70109UP2022PTC163047), a Company incorporated under the Companies Act, 2013, having its Registered Office at 1st Floor, APS Building, Near Ganpati Lawn, Singhariya Chauraha, Kunraghat, Gorakhpur - 273008, represented by its Authorized Signatory (hereinafter referred to as the <strong>"Company / First Party"</strong>).
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8fafc; font-weight: bold;">SECOND PARTY<br/>(Customer / Allottee)</td>
            <td>
                <strong>Mr./Mrs./Ms. ____________________________________________________________________</strong><br/>
                S/o, W/o, D/o: ____________________________________________________________________<br/>
                Permanent Address: ________________________________________________________________<br/>
                Mobile No: _____________________________ | Aadhaar / PAN No: _________________________
            </td>
        </tr>
    </table>

    <div class="section-title">1. PLOT ALLOTMENT &amp; BOOKING PARTICULARS</div>
    <table class="grid">
        <tr>
            <th style="width: 25%;">Project / Township Name</th>
            <td style="width: 25%;">_________________________</td>
            <th style="width: 25%;">Plot No. &amp; Block</th>
            <td style="width: 25%;">Plot: ________ Block: _______</td>
        </tr>
        <tr>
            <th>Super / Carpet Area</th>
            <td>_____________ Sq. Ft.</td>
            <th>Elapsed Since Booking</th>
            <td>Approx. _______ Yrs / Mths</td>
        </tr>
        <tr>
            <th>Total Agreed Value</th>
            <td>₹ _______________________</td>
            <th>Token Amount Deposited</th>
            <td><strong>₹ _______________________</strong></td>
        </tr>
        <tr>
            <th>Original Booking Receipt No.</th>
            <td colspan="3">____________________________________ | <strong>Booking Date:</strong> ____ / ____ / 20____</td>
        </tr>
    </table>

    <div class="section-title">2. VOLUNTARY CANCELLATION &amp; MANDATORY SURRENDER OF ORIGINAL DOCUMENTS</div>
    <div class="clause-item">
        <strong>2.1 Voluntary Termination:</strong> The Second Party had booked the aforesaid plot by paying an initial booking/token advance of <strong>₹ _______________________/- (In Words: _____________________________________________________)</strong>. Due to unforeseen personal, financial constraints, and considerable lapse of time, the Second Party has formally and voluntarily applied to terminate the booking without any coercion or duress.
    </div>
    <div class="clause-item">
        <strong>2.2 Mandatory Surrender of All Original Papers:</strong> The Second Party hereby acknowledges, physically delivers, and irrevocably surrenders all original documentation to the Company, including:
        <br/>&nbsp;&nbsp;• <strong>Original Booking Application Form</strong> issued by the Company;
        <br/>&nbsp;&nbsp;• <strong>All Original Payment Receipts</strong>, Token Advances, Money Receipts, and Bank Slips;
        <br/>&nbsp;&nbsp;• <strong>Original Provisional Allotment Letter</strong> and Site Demarcation papers (if any);
        <br/>&nbsp;&nbsp;• <strong>Original Tripartite Master Deed</strong> or customer agreements issued.
    </div>
    <div class="clause-item">
        <strong>2.3 Nullification of Duplicate Claims:</strong> The Second Party explicitly confirms that no copies, digital images, photocopies, or duplicates have been retained or assigned to any third party. Any future production of such documents shall be deemed void, illegal, and fraudulent.
    </div>

    <div class="section-title">3. STATUTORY NON-REFUNDABLE POLICY &amp; WAIVER OF ACCUMULATED DEFAULT INTEREST</div>
    <div class="red-alert-box">
        <strong>STATUTORY FORFEITURE &amp; WAIVER DECLARATION:</strong> Both parties confirm that in accordance with <strong>Sections 2.1 and 2.9 of the official Tripartite Master Legal Deed</strong>, the initial booking token amount is strictly <strong>Non-Refundable / गैर-वापसी योग्य</strong> under all circumstances. Following the initial token deposit, no regular EMIs or installments were paid. While heavy penal interest (18% p.a.) and default delay penalties accrued on the total plot value (₹ _______________________/-), <strong>all such accumulated penal interest and default charges are officially shown in records but completely waived off and forgiven by the Company</strong> on compassionate humanitarian grounds.
    </div>

    <div class="section-title">4. SPECIAL EX-GRATIA SETTLEMENT &amp; STAGGERED CASH-FLOW TIMELINE</div>
    <div class="highlight-box">
        <strong>4.1 Ex-Gratia Settlement Consideration:</strong> Notwithstanding the strict statutory forfeiture clause stated above, upon the special written appeal, medical/financial hardships, and compassionate request of the Second Party, the First Party (Company), as a pure matter of goodwill and without establishing any legal precedent, has agreed to release an ex-gratia partial settlement sum of:
        <br/><br/>
        <div style="font-size: 9pt; text-align: center; font-weight: bold; color: #0b1c2c; background: #fff; padding: 4px; border: 1px dashed #2980b9;">
            SETTLEMENT AMOUNT: ₹ __________________________/-<br/>
            (In Words: ___________________________________________________________________________________)
        </div>
        <br/>
        <strong>4.2 Staggered Cash-Flow Timeline (Up to 180 Working Days):</strong> The Second Party acknowledges that corporate auditing and banking clearance cycles require procedural time. The agreed settlement amount shall not be demanded immediately and will be disbursed through official corporate banking channels in staggered installments over a <strong>liquidation cycle of up to 180 working days</strong> based on cash-flow velocity.
        <br/><br/>
        <strong>4.3 Payment Realization Mode:</strong> [ ] Cheque &nbsp;&nbsp; [ ] Online Transfer (NEFT/RTGS/IMPS/UPI) &nbsp;&nbsp; [ ] Cash
        <br/>&nbsp;&nbsp;Instrument / UTR / Txn No.: ____________________________________ Dated: ____ / ____ / 2026
        <br/>&nbsp;&nbsp;Drawn on Bank &amp; Branch: ____________________________________________________________________
    </div>

    <div class="section-title">5. FULL, FINAL &amp; IRREVOCABLE DISCHARGE (NO FUTURE CLAIMS)</div>
    <div class="clause-item">
        <strong>5.1 Complete Release:</strong> Upon receipt of the settlement amount in Clause 4.1, the Second Party hereby permanently, completely, and irrevocably discharges, indemnifies, and holds harmless <strong>APS DREAM HOMES PVT. LTD.</strong>, its Directors, Shareholders, Promoters, and Associates from all liabilities, rights, claims, suits, charges, or demands whatsoever.
    </div>
    <div class="clause-item">
        <strong>5.2 Waiver of Appreciation &amp; Disputes:</strong> The Second Party explicitly waives any claim toward market price appreciation, penal interest, compensation, or allotment rights over Plot No. ________. The Second Party covenants never to institute any litigation before any Civil Court, RERA Authority, Consumer Commission, Police, or Arbitration Tribunal in connection with this booking.
    </div>
    <div class="clause-item">
        <strong>5.3 Gorakhpur Jurisdiction:</strong> This Deed is executed under the laws of India and shall be subject to the exclusive jurisdiction of the competent Courts at <strong>Gorakhpur, Uttar Pradesh</strong>.
    </div>

    <br/>
    <div style="text-align: center; font-weight: bold; font-size: 8pt; color: #0b1c2c; margin-top: 5px; margin-bottom: 8px;">
        IN WITNESS WHEREOF, THE PARTIES HERETO HAVE SET THEIR HANDS AND SEALS ON THE DATE FIRST ABOVE WRITTEN.
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 48%; border: 1px solid #94a3b8; padding: 8px; text-align: center;">
                <div style="height: 35px;"></div>
                ____________________________________________<br/>
                <strong>SIGNATURE / THUMB IMPRESSION</strong><br/>
                <strong>SECOND PARTY (ALLOTTEE / CUSTOMER)</strong><br/>
                Name: _____________________________________<br/>
                Date: ____ / ____ / 2026 | Place: Gorakhpur
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; border: 1px solid #94a3b8; padding: 8px; text-align: center;">
                <div style="height: 35px;"></div>
                ____________________________________________<br/>
                <strong>FOR APS DREAM HOMES PVT. LTD.</strong><br/>
                <strong>(AUTHORIZED SIGNATORY &amp; COMPANY SEAL)</strong><br/>
                First Party / Developer<br/>
                Date: ____ / ____ / 2026 | Place: Gorakhpur
            </td>
        </tr>
    </table>

    <br/>
    <div class="section-title">WITNESSES (ATTESTING IN PRESENCE OF BOTH PARTIES)</div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 3px;">
        <tr>
            <td style="width: 48%; border: 0.5px solid #cbd5e1; padding: 6px;">
                <strong>WITNESS 1:</strong><br/>
                Name: __________________________________________<br/>
                S/o, D/o: ______________________________________<br/>
                Address: _______________________________________<br/>
                Mobile: ________________________________________<br/>
                Signature: _____________________________________
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; border: 0.5px solid #cbd5e1; padding: 6px;">
                <strong>WITNESS 2:</strong><br/>
                Name: __________________________________________<br/>
                S/o, D/o: ______________________________________<br/>
                Address: _______________________________________<br/>
                Mobile: ________________________________________<br/>
                Signature: _____________________________________
            </td>
        </tr>
    </table>
HTML;
}

// 1. Generate Standard A4 PDF
echo "Generating Standard A4 PDF...\n";
$pdfA4 = new CancellationDeedPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdfA4->isStampPaper = false;
$pdfA4->SetCreator('APS Dream Homes CRM');
$pdfA4->SetAuthor('APS Dream Homes Pvt. Ltd.');
$pdfA4->SetTitle('Deed of Booking Cancellation & Ex-Gratia Settlement');
$pdfA4->SetMargins(15, 30, 15);
$pdfA4->SetHeaderMargin(8);
$pdfA4->SetFooterMargin(15);
$pdfA4->SetAutoPageBreak(true, 18);
$pdfA4->AddPage();
$pdfA4->writeHTML(generateDeedHtmlContent(false), true, false, true, false, '');
$a4PdfPath = $downloadsDir . '/cancellation_settlement_deed_standard_a4.pdf';
$pdfA4->Output($a4PdfPath, 'F');
echo "Saved Standard A4 PDF to: " . $a4PdfPath . " (" . filesize($a4PdfPath) . " bytes)\n";

// 2. Generate Stamp Paper PDF (Space reserved for ₹100 / ₹500 non-judicial stamp at top)
echo "Generating Stamp Paper PDF (₹100 / ₹500 Stamp Paper ready)...\n";
$pdfStamp = new CancellationDeedPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdfStamp->isStampPaper = true;
$pdfStamp->SetCreator('APS Dream Homes CRM');
$pdfStamp->SetAuthor('APS Dream Homes Pvt. Ltd.');
$pdfStamp->SetTitle('Stamp Paper Deed - Cancellation & Ex-Gratia Settlement');
$pdfStamp->SetMargins(15, 10, 15);
$pdfStamp->SetHeaderMargin(5);
$pdfStamp->SetFooterMargin(15);
$pdfStamp->SetAutoPageBreak(true, 18);
$pdfStamp->AddPage();
// On Page 1, start writing text lower (after 95mm)
$pdfStamp->SetY(95);
$pdfStamp->writeHTML(generateDeedHtmlContent(false), true, false, true, false, '');
$stampPdfPath = $downloadsDir . '/cancellation_settlement_deed_stamp_paper.pdf';
$pdfStamp->Output($stampPdfPath, 'F');
echo "Saved Stamp Paper PDF to: " . $stampPdfPath . " (" . filesize($stampPdfPath) . " bytes)\n";

// 3. Generate Word DOC (.doc) format
echo "Generating Word Document (.doc)...\n";
$docContent = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<title>Deed of Booking Cancellation & Settlement - APS Dream Homes</title>
<!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>100</w:Zoom>
  <w:DoNotOptimizeForBrowser/>
 </w:WordDocument>
</xml>
<![endif]-->
<style>
@page { size: 21.0cm 29.7cm; margin: 2.0cm 2.0cm 2.0cm 2.0cm; mso-page-orientation: portrait; }
body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #1e293b; }
.header-table { width: 100%; border-bottom: 3px double #2980b9; padding-bottom: 10px; margin-bottom: 15px; }
h1 { font-size: 16pt; font-weight: bold; text-align: center; color: #0b1c2c; margin: 10px 0 5px 0; text-transform: uppercase; }
.subtitle { font-size: 10.5pt; font-weight: bold; text-align: center; color: #c0392b; margin-bottom: 15px; }
.sec-head { font-size: 11pt; font-weight: bold; background-color: #f1f5f9; color: #0b1c2c; padding: 6px 10px; border-left: 4px solid #2980b9; margin: 15px 0 8px 0; }
table.data-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
table.data-table td, table.data-table th { border: 1px solid #cbd5e1; padding: 8px 10px; font-size: 10.5pt; }
table.data-table th { background-color: #f8fafc; font-weight: bold; }
.alert-box { background-color: #fff0f0; border-left: 4px solid #e74c3c; padding: 10px; margin: 10px 0; }
.exgratia-box { background-color: #f0fdf4; border: 2px dashed #22c55e; padding: 12px; margin: 12px 0; text-align: center; font-size: 11.5pt; }
</style>
</head>
<body>
<table class="header-table">
<tr>
<td align="center">
  <div style="font-size: 20pt; font-weight: bold; color: #0b1c2c; letter-spacing: 1px;">APS DREAM HOMES PVT. LTD.</div>
  <div style="font-size: 10pt; font-weight: bold; color: #2980b9; margin: 4px 0;">CIN: U70109UP2022PTC163047 | ROC: KANPUR | EST. APRIL 2022</div>
  <div style="font-size: 9.5pt; color: #64748b;">Regd. Office: 1st Floor, APS Building, Near Ganpati Lawn, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008</div>
  <div style="font-size: 9.5pt; color: #64748b;">Phone: +91 92771 21112 | Email: official@apsdreamhomes.com | Website: apsdreamhomes.com</div>
</td>
</tr>
</table>

<h1>DEED OF BOOKING CANCELLATION, RECEIPT SURRENDER &amp; EX-GRATIA SETTLEMENT</h1>
<div class="subtitle">STRICTLY EXECUTED UNDER TRIPARTITE MASTER LEGAL DEED (SECTIONS 2.1 &amp; 2.9) &amp; COMPANIES ACT 2013</div>

<p>This Deed of Cancellation, Receipt Surrender and Full &amp; Final Settlement is executed on this <strong>______ day of __________________, 2026</strong> at Gorakhpur, Uttar Pradesh, by and between:</p>

<table class="data-table">
<tr>
  <td style="width: 25%; font-weight: bold; background: #f8fafc;">FIRST PARTY<br/>(Developer / Company)</td>
  <td style="width: 75%;">
    <strong>APS DREAM HOMES PVT. LTD.</strong> (CIN: U70109UP2022PTC163047), having its Registered Office at 1st Floor, APS Building, Near Ganpati Lawn, Singhariya Chauraha, Kunraghat, Gorakhpur - 273008, represented through its Authorized Signatory (hereinafter referred to as the <strong>"Company / First Party"</strong>).
  </td>
</tr>
<tr>
  <td style="font-weight: bold; background: #f8fafc;">SECOND PARTY<br/>(Customer / Allottee)</td>
  <td>
    <strong>Mr./Mrs./Ms. ______________________________________________________________________</strong><br/>
    Son/Wife/Daughter of: _______________________________________________________________<br/>
    Permanent Address: __________________________________________________________________<br/>
    Mobile No.: _______________________________ | Aadhaar / PAN: _________________________
  </td>
</tr>
</table>

<div class="sec-head">1. PLOT ALLOTMENT &amp; BOOKING PARTICULARS</div>
<table class="data-table">
<tr>
  <th style="width: 25%;">Project / Township Name</th>
  <td style="width: 25%;">_____________________________</td>
  <th style="width: 25%;">Plot Number &amp; Block</th>
  <td style="width: 25%;">Plot: __________ Block: ________</td>
</tr>
<tr>
  <th>Super / Carpet Area</th>
  <td>_________________ Sq. Ft.</td>
  <th>Original Booking Date</th>
  <td>____ / ____ / 20____</td>
</tr>
<tr>
  <th>Total Agreed Plot Value</th>
  <td>₹ ___________________________</td>
  <th>Token Amount Deposited</th>
  <td><strong>₹ ___________________________</strong></td>
</tr>
<tr>
  <th>Original Booking Receipt No.</th>
  <td colspan="3">_________________________________________________________________________________</td>
</tr>
</table>

<div class="sec-head">2. VOLUNTARY CANCELLATION &amp; MANDATORY SURRENDER OF ORIGINAL DOCUMENTS</div>
<p><strong>2.1 Voluntary Termination:</strong> The Second Party had booked the aforesaid plot by paying an initial booking/token advance. Due to unforeseen personal and financial constraints, and considerable lapse of time, the Second Party has formally and voluntarily applied to terminate the booking without any coercion or duress.</p>
<p><strong>2.2 Mandatory Surrender of All Original Papers:</strong> The Second Party hereby acknowledges, physically delivers, and irrevocably surrenders all original documentation to the Company, including:</p>
<ul>
  <li><strong>Original Booking Form / Application Form</strong> issued by the Company;</li>
  <li><strong>All Original Payment Receipts</strong>, Token Advances, Money Receipts, and Bank Slips;</li>
  <li><strong>Original Provisional Allotment Letter</strong> and Site Demarcation papers (if any);</li>
  <li><strong>Original Tripartite Master Deed</strong> or customer agreements issued.</li>
</ul>
<p><strong>2.3 Nullification of Duplicate Claims:</strong> The Second Party explicitly confirms that no copies, digital images, photocopies, or duplicates have been retained or assigned to any third party. Any future production of such documents shall be deemed void, illegal, and fraudulent.</p>

<div class="sec-head">3. ACKNOWLEDGEMENT OF NON-REFUNDABLE TOKEN POLICY (MASTER DEED SECTIONS 2.1 &amp; 2.9)</div>
<div class="alert-box">
  <strong>STATUTORY FORFEITURE DECLARATION:</strong> Both parties confirm that in accordance with <strong>Sections 2.1 and 2.9 of the official Tripartite Master Legal Deed</strong>, the initial booking token amount (minimum 25% / ₹51,000) is strictly <strong>Non-Refundable / गैर-वापसी योग्य</strong> under all circumstances to indemnify the Company against administrative overheads, brokerages, site development, and plot inventory blockage. The Second Party was bound to clear 25% of the plot cost within 15 calendar days, failing which the token stands legally forfeited.
</div>

<div class="sec-head">4. SPECIAL EX-GRATIA / HUMANITARIAN PARTIAL SETTLEMENT</div>
<p><strong>4.1 Ex-Gratia Settlement Consideration:</strong> Notwithstanding the strict statutory forfeiture clause stated above, upon the special written appeal, medical/financial hardships, and compassionate humanitarian request of the Second Party, the First Party (Company), as a pure matter of goodwill and without establishing any legal precedent, has agreed to release an ex-gratia settlement sum of:</p>

<div class="exgratia-box">
  <strong>SETTLEMENT AMOUNT: ₹ __________________________/-</strong><br/>
  (In Words: ___________________________________________________________________________________)
</div>

<p><strong>4.2 Payment Realization Details:</strong> The aforesaid ex-gratia amount is released via:</p>
<p>[ ] Cheque&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ ] Bank Transfer (NEFT / RTGS / IMPS / UPI)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ ] Cash<br/>
Cheque / UTR / Transaction No.: ____________________________________ Dated: ____ / ____ / 2026<br/>
Drawn on Bank &amp; Branch: ____________________________________________________________________</p>

<div class="sec-head">5. FULL, FINAL &amp; IRREVOCABLE DISCHARGE (NO FUTURE CLAIMS)</div>
<p><strong>5.1 Complete Release:</strong> Upon receipt of the settlement amount in Clause 4.1, the Second Party hereby permanently, completely, and irrevocably discharges, indemnifies, and holds harmless <strong>APS DREAM HOMES PVT. LTD.</strong>, its Directors, Shareholders, Promoters, and Associates from all liabilities, rights, claims, suits, charges, or demands whatsoever.</p>
<p><strong>5.2 Waiver of Appreciation &amp; Disputes:</strong> The Second Party explicitly waives any claim toward market price appreciation, penal interest, compensation, or allotment rights over the subject plot. The Second Party covenants never to institute any litigation before any Civil Court, RERA Authority, Consumer Commission, Police, or Arbitration Tribunal in connection with this booking.</p>
<p><strong>5.3 Gorakhpur Jurisdiction:</strong> This Deed is executed under the laws of India and shall be subject to the exclusive jurisdiction of the competent Courts at <strong>Gorakhpur, Uttar Pradesh</strong>.</p>

<br/>
<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
<tr>
  <td style="width: 48%; border: 1px solid #94a3b8; padding: 15px; text-align: center;">
    <br/><br/>
    ___________________________________________________<br/>
    <strong>SIGNATURE / THUMB IMPRESSION</strong><br/>
    <strong>SECOND PARTY (ALLOTTEE / CUSTOMER)</strong><br/>
    Name: ___________________________________________<br/>
    Date: ____ / ____ / 2026 | Place: Gorakhpur
  </td>
  <td style="width: 4%;"></td>
  <td style="width: 48%; border: 1px solid #94a3b8; padding: 15px; text-align: center;">
    <br/><br/>
    ___________________________________________________<br/>
    <strong>FOR APS DREAM HOMES PVT. LTD.</strong><br/>
    <strong>(AUTHORIZED SIGNATORY &amp; COMPANY SEAL)</strong><br/>
    First Party / Developer<br/>
    Date: ____ / ____ / 2026 | Place: Gorakhpur
  </td>
</tr>
</table>

<div class="sec-head">WITNESSES (ATTESTING IN PRESENCE OF BOTH PARTIES)</div>
<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
<tr>
  <td style="width: 48%; border: 1px solid #cbd5e1; padding: 10px;">
    <strong>WITNESS 1:</strong><br/>
    Name: ______________________________________________<br/>
    S/o, D/o: __________________________________________<br/>
    Address: ___________________________________________<br/>
    Mobile No.: ________________________________________<br/>
    Signature: _________________________________________
  </td>
  <td style="width: 4%;"></td>
  <td style="width: 48%; border: 1px solid #cbd5e1; padding: 10px;">
    <strong>WITNESS 2:</strong><br/>
    Name: ______________________________________________<br/>
    S/o, D/o: __________________________________________<br/>
    Address: ___________________________________________<br/>
    Mobile No.: ________________________________________<br/>
    Signature: _________________________________________
  </td>
</tr>
</table>

</body>
</html>';

$docPath = $downloadsDir . '/cancellation_settlement_deed.doc';
file_put_contents($docPath, $docContent);
echo "Saved Word Document to: " . $docPath . " (" . filesize($docPath) . " bytes)\n";

// 4. Generate Web Printable HTML view with Print and Stamp mode buttons
echo "Generating Web Printable HTML view...\n";
$webHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cancellation & Settlement Deed — APS Dream Homes Pvt. Ltd.</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body {
        background-color: #f1f5f9;
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #1e293b;
        margin: 0;
        padding: 20px 0;
    }
    .action-bar {
        max-width: 900px;
        margin: 0 auto 20px auto;
        padding: 15px 25px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .deed-container {
        max-width: 900px;
        margin: 0 auto;
        background: #ffffff;
        padding: 50px;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        position: relative;
    }
    .company-header {
        text-align: center;
        border-bottom: 3px double #2980b9;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .company-name {
        font-size: 26px;
        font-weight: 800;
        color: #0b1c2c;
        letter-spacing: 1px;
    }
    .company-sub {
        font-size: 13px;
        font-weight: 700;
        color: #2980b9;
        margin: 4px 0;
    }
    .company-meta {
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }
    .deed-title {
        text-align: center;
        font-size: 19px;
        font-weight: 800;
        color: #0b1c2c;
        text-transform: uppercase;
        margin-top: 15px;
        margin-bottom: 4px;
    }
    .deed-subtitle {
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        color: #c0392b;
        margin-bottom: 20px;
    }
    .sec-badge {
        background-color: #f1f5f9;
        color: #0b1c2c;
        font-weight: 700;
        font-size: 13px;
        padding: 7px 12px;
        border-left: 4px solid #2980b9;
        margin: 20px 0 10px 0;
        border-radius: 0 6px 6px 0;
    }
    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    table.data-table th, table.data-table td {
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 13px;
    }
    table.data-table th {
        background-color: #f8fafc;
        font-weight: 700;
        color: #334155;
    }
    .alert-red {
        background-color: #fef2f2;
        border: 1px solid #f87171;
        border-left: 5px solid #dc2626;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 13px;
        margin: 15px 0;
    }
    .highlight-green {
        background-color: #f0fdf4;
        border: 2px dashed #22c55e;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        margin: 15px 0;
    }
    .clause-text {
        font-size: 13px;
        line-height: 1.6;
        margin-bottom: 10px;
        text-align: justify;
    }
    .sign-box-outer {
        border: 1px solid #94a3b8;
        padding: 20px 15px;
        border-radius: 8px;
        text-align: center;
        background: #fafafa;
    }
    .stamp-spacer {
        display: none;
        height: 380px;
        border: 2px dashed #cbd5e1;
        margin-bottom: 25px;
        text-align: center;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-weight: bold;
    }

    /* Print Styling */
    @media print {
        body {
            background: #fff;
            padding: 0;
        }
        .action-bar {
            display: none !important;
        }
        .deed-container {
            box-shadow: none;
            padding: 0;
            margin: 0;
            max-width: 100%;
        }
        .stamp-mode .company-header {
            display: none !important;
        }
        .stamp-mode .stamp-spacer {
            display: block !important;
            border: none;
        }
    }
</style>
</head>
<body>

<div class="action-bar">
    <div>
        <h5 class="m-0 fw-bold text-dark"><i class="fa-solid fa-file-contract text-primary me-2"></i>Cancellation &amp; Settlement Deed</h5>
        <small class="text-muted">Master Deed Section 2.1 &amp; 2.9 Statutory Compliance</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-dark btn-sm" onclick="toggleStampMode(this)">
            <i class="fa-solid fa-stamp me-1"></i> <span id="stamp-btn-text">Switch to ₹100/₹500 Stamp Paper Mode</span>
        </button>
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Deed (Ctrl+P)
        </button>
        <a href="../downloads/cancellation_settlement_deed_standard_a4.pdf" class="btn btn-danger btn-sm" download>
            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
        </a>
        <a href="../downloads/cancellation_settlement_deed.doc" class="btn btn-info btn-sm text-white" download>
            <i class="fa-solid fa-file-word me-1"></i> Word / Docs (.doc)
        </a>
    </div>
</div>

<div class="deed-container" id="deedContent">

    <div class="stamp-spacer" id="stampSpacer">
        [ SPACE RESERVED FOR ₹100 / ₹500 NON-JUDICIAL STAMP PAPER ]
    </div>

    <div class="company-header" id="companyHeader">
        <div class="company-name">APS DREAM HOMES PVT. LTD.</div>
        <div class="company-sub">CIN: U70109UP2022PTC163047 | ROC: KANPUR | EST. APRIL 2022</div>
        <div class="company-meta">
            Regd. Office: 1st Floor, APS Building, Near Ganpati Lawn, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008<br>
            Phone: +91 92771 21112, +91 551 356 8563 | Email: official@apsdreamhomes.com | Web: apsdreamhomes.com
        </div>
    </div>

    <div class="deed-title">DEED OF BOOKING CANCELLATION, RECEIPT SURRENDER &amp; EX-GRATIA SETTLEMENT</div>
    <div class="deed-subtitle">STRICTLY EXECUTED UNDER TRIPARTITE MASTER LEGAL DEED (SECTIONS 2.1 &amp; 2.9) &amp; COMPANIES ACT 2013</div>

    <p class="clause-text">
        This Deed of Cancellation, Receipt Surrender and Full &amp; Final Settlement is executed on this <strong>______ day of __________________, 2026</strong> at Gorakhpur, Uttar Pradesh, by and between:
    </p>

    <table class="data-table">
        <tr>
            <th style="width: 25%;">FIRST PARTY<br><small class="fw-normal text-muted">(Developer / Company)</small></th>
            <td style="width: 75%;">
                <strong>APS DREAM HOMES PVT. LTD.</strong> (CIN: U70109UP2022PTC163047), a Company incorporated under the Companies Act, 2013, having its Registered Office at 1st Floor, APS Building, Near Ganpati Lawn, Singhariya Chauraha, Kunraghat, Gorakhpur - 273008, represented through its Authorized Signatory (hereinafter referred to as the <strong>"Company / First Party"</strong>).
            </td>
        </tr>
        <tr>
            <th>SECOND PARTY<br><small class="fw-normal text-muted">(Customer / Allottee)</small></th>
            <td>
                <strong>Mr./Mrs./Ms. _________________________________________________________________________</strong><br>
                Son / Wife / Daughter of: __________________________________________________________________<br>
                Permanent Address: _____________________________________________________________________<br>
                Mobile No: ________________________________ | Aadhaar / PAN No: __________________________
            </td>
        </tr>
    </table>

    <div class="sec-badge">1. PLOT ALLOTMENT &amp; BOOKING PARTICULARS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Project / Township Name</th>
            <td style="width: 25%;">_____________________________</td>
            <th style="width: 25%;">Plot Number &amp; Block</th>
            <td style="width: 25%;">Plot: __________ Block: ________</td>
        </tr>
        <tr>
            <th>Super / Carpet Area</th>
            <td>_________________ Sq. Ft.</td>
            <th>Original Booking Date</th>
            <td>____ / ____ / 20____</td>
        </tr>
        <tr>
            <th>Total Agreed Consideration</th>
            <td>₹ ___________________________</td>
            <th>Token Amount Deposited</th>
            <td><strong class="text-danger">₹ ___________________________</strong></td>
        </tr>
        <tr>
            <th>Original Booking Receipt No.</th>
            <td colspan="3">_________________________________________________________________________________</td>
        </tr>
    </table>

    <div class="sec-badge">2. VOLUNTARY CANCELLATION &amp; MANDATORY SURRENDER OF ORIGINAL DOCUMENTS</div>
    <div class="clause-text">
        <strong>2.1 Voluntary Termination:</strong> The Second Party had booked the aforesaid plot by paying an initial booking/token advance. Due to unforeseen personal and financial constraints, and considerable lapse of time, the Second Party has formally and voluntarily applied to terminate the booking without any coercion or duress.
    </div>
    <div class="clause-text">
        <strong>2.2 Mandatory Physical Surrender of All Original Papers:</strong> The Second Party hereby acknowledges, physically delivers, and irrevocably surrenders all original documentation to the Company, including:
        <ul class="mb-1 ps-4">
            <li><strong>Original Booking Form / Application Form</strong> issued by the Company;</li>
            <li><strong>All Original Payment Receipts</strong>, Token Advances, Money Receipts, and Bank Slips;</li>
            <li><strong>Original Provisional Allotment Letter</strong> and Site Demarcation papers (if any);</li>
            <li><strong>Original Tripartite Master Deed</strong> or customer agreements issued.</li>
        </ul>
    </div>
    <div class="clause-text">
        <strong>2.3 Nullification of Duplicate Claims:</strong> The Second Party explicitly confirms that no copies, digital images, photocopies, or duplicates have been retained or assigned to any third party. Any future production of such documents shall be deemed void, illegal, and fraudulent.
    </div>

    <div class="sec-badge">3. ACKNOWLEDGEMENT OF NON-REFUNDABLE TOKEN POLICY (MASTER DEED SECTIONS 2.1 &amp; 2.9)</div>
    <div class="alert-red">
        <strong class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> STATUTORY FORFEITURE DECLARATION:</strong> Both parties confirm that in accordance with <strong>Sections 2.1 and 2.9 of the official Tripartite Master Legal Deed</strong>, the initial booking token amount (minimum 25% / ₹51,000) is strictly <strong>Non-Refundable / गैर-वापसी योग्य</strong> under all circumstances to indemnify the Company against administrative overheads, brokerages, site development, and plot inventory blockage. The Second Party was bound to clear 25% of the plot cost within 15 calendar days, failing which the token stands legally forfeited.
    </div>

    <div class="sec-badge">4. SPECIAL EX-GRATIA / HUMANITARIAN PARTIAL SETTLEMENT</div>
    <div class="clause-text">
        <strong>4.1 Ex-Gratia Settlement Consideration:</strong> Notwithstanding the strict statutory forfeiture clause stated above, upon the special written appeal, medical/financial hardships, and compassionate humanitarian request of the Second Party, the First Party (Company), as a pure matter of goodwill and without establishing any legal precedent, has agreed to release an ex-gratia settlement sum of:
    </div>

    <div class="highlight-green">
        <div style="font-size: 18px; font-weight: 800; color: #166534;">
            SETTLEMENT AMOUNT: ₹ __________________________/-
        </div>
        <div style="font-size: 14px; color: #374151; margin-top: 5px;">
            (In Words: ___________________________________________________________________________________)
        </div>
    </div>

    <div class="clause-text">
        <strong>4.2 Payment Realization Details:</strong> The aforesaid ex-gratia amount is released via:
        <br>[ ] Cheque&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ ] Bank Transfer (NEFT / RTGS / IMPS / UPI)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ ] Cash
        <br>Cheque / UTR / Transaction No.: ____________________________________ Dated: ____ / ____ / 2026
        <br>Drawn on Bank &amp; Branch: ____________________________________________________________________
    </div>

    <div class="sec-badge">5. FULL, FINAL &amp; IRREVOCABLE DISCHARGE (NO FUTURE CLAIMS)</div>
    <div class="clause-text">
        <strong>5.1 Complete Release:</strong> Upon receipt of the settlement amount in Clause 4.1, the Second Party hereby permanently, completely, and irrevocably discharges, indemnifies, and holds harmless <strong>APS DREAM HOMES PVT. LTD.</strong>, its Directors, Shareholders, Promoters, and Associates from all liabilities, rights, claims, suits, charges, or demands whatsoever.
    </div>
    <div class="clause-text">
        <strong>5.2 Waiver of Appreciation &amp; Disputes:</strong> The Second Party explicitly waives any claim toward market price appreciation, penal interest, compensation, or allotment rights over Plot No. ________. The Second Party covenants never to institute any litigation before any Civil Court, RERA Authority, Consumer Commission, Police, or Arbitration Tribunal in connection with this booking.
    </div>
    <div class="clause-text">
        <strong>5.3 Gorakhpur Jurisdiction:</strong> This Deed is executed under the laws of India and shall be subject to the exclusive jurisdiction of the competent Courts at <strong>Gorakhpur, Uttar Pradesh</strong>.
    </div>

    <div class="my-4 text-center fw-bold text-dark" style="font-size: 14px;">
        IN WITNESS WHEREOF, THE PARTIES HERETO HAVE SET THEIR HANDS AND SEALS ON THE DATE FIRST ABOVE WRITTEN.
    </div>

    <div class="row g-4 my-2">
        <div class="col-6">
            <div class="sign-box-outer">
                <div style="height: 60px;"></div>
                <div class="border-top border-dark pt-2">
                    <strong class="text-dark">SIGNATURE / THUMB IMPRESSION</strong><br>
                    <span class="text-muted">SECOND PARTY (ALLOTTEE / CUSTOMER)</span><br>
                    <small>Name: ___________________________________</small><br>
                    <small>Date: ____ / ____ / 2026 | Place: Gorakhpur</small>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="sign-box-outer">
                <div style="height: 60px;"></div>
                <div class="border-top border-dark pt-2">
                    <strong class="text-dark">FOR APS DREAM HOMES PVT. LTD.</strong><br>
                    <span class="text-muted">(AUTHORIZED SIGNATORY &amp; COMPANY SEAL)</span><br>
                    <small>First Party / Developer</small><br>
                    <small>Date: ____ / ____ / 2026 | Place: Gorakhpur</small>
                </div>
            </div>
        </div>
    </div>

    <div class="sec-badge">WITNESSES (ATTESTING IN PRESENCE OF BOTH PARTIES)</div>
    <div class="row g-3">
        <div class="col-6">
            <div class="p-3 border rounded">
                <strong>WITNESS 1:</strong><br>
                <small class="text-muted">Name:</small> ___________________________________<br>
                <small class="text-muted">S/o, D/o:</small> ________________________________<br>
                <small class="text-muted">Address:</small> _________________________________<br>
                <small class="text-muted">Mobile:</small> __________________________________<br>
                <small class="text-muted">Signature:</small> _______________________________
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 border rounded">
                <strong>WITNESS 2:</strong><br>
                <small class="text-muted">Name:</small> ___________________________________<br>
                <small class="text-muted">S/o, D/o:</small> ________________________________<br>
                <small class="text-muted">Address:</small> _________________________________<br>
                <small class="text-muted">Mobile:</small> __________________________________<br>
                <small class="text-muted">Signature:</small> _______________________________
            </div>
        </div>
    </div>

</div>

<script>
let stampMode = false;
function toggleStampMode(btn) {
    stampMode = !stampMode;
    const deed = document.getElementById('deedContent');
    const header = document.getElementById('companyHeader');
    const spacer = document.getElementById('stampSpacer');
    const btnText = document.getElementById('stamp-btn-text');

    if (stampMode) {
        document.body.classList.add('stamp-mode');
        spacer.style.display = 'flex';
        header.style.display = 'none';
        btnText.innerText = 'Switch to Standard A4 Mode';
        btn.classList.replace('btn-outline-dark', 'btn-warning');
    } else {
        document.body.classList.remove('stamp-mode');
        spacer.style.display = 'none';
        header.style.display = 'block';
        btnText.innerText = 'Switch to ₹100/₹500 Stamp Paper Mode';
        btn.classList.replace('btn-warning', 'btn-outline-dark');
    }
}
</script>

</body>
</html>
HTML;

$htmlPath = $docsDir . '/cancellation_settlement_deed.html';
file_put_contents($htmlPath, $webHtml);
echo "Saved Web Printable HTML to: " . $htmlPath . " (" . filesize($htmlPath) . " bytes)\n";

echo "ALL 4 CANCELLATION DEED FORMATS GENERATED SUCCESSFULLY!\n";
