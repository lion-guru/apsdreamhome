<?php

namespace App\Http\Controllers\Admin;

use TCPDF;

class PaymentReceiptPdfController extends AdminController
{
    public function generate($id)
    {
        $this->requireAdmin();
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('APS Dream Home');
        $pdf->SetAuthor('APS Dream Home');
        $pdf->SetTitle('Test');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Test', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Test', 0, 1, 'C');
        $pdf->Cell(0, 5, 'GSTIN: test  |  Ph: test  |  Email: test', 0, 1, 'C');
        $pdf->Ln(3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'PAYMENT RECEIPT', 0, 1, 'C');
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, 'Receipt No:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, 'TEST', 0, 1);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, 'Date:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, date('d M Y, h:i A'), 0, 1);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, 'Payment Mode:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, 'Transaction ID:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, 'N/A', 0, 1);
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Customer Details', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 7, 'Name:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Cell(50, 7, 'Phone:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Cell(50, 7, 'Email:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Property Details', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 7, 'Property:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Cell(50, 7, 'Plot No:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Cell(50, 7, 'Area:', 0, 0);
        $pdf->Cell(0, 7, 'Test sq.ft.', 0, 1);
        $pdf->Cell(50, 7, 'Booking No:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'Payment Details', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 7, 'Amount Paid:', 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'Rs. 1000', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 7, 'Amount in Words:', 0, 0);
        $pdf->Cell(0, 7, 'Test', 0, 1);
        $pdf->Ln(3);
        
        $pdf->Output('test.pdf', 'D');
    }
    
    private function numberToWords($number)
    {
        $number = (float)$number;
        if ($number == 0) return 'Zero Rupees Only';
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $words = '';
        $num = (int)$number;
        if ($num >= 10000000) { $words .= $this->numberToWords(floor($num / 10000000)) . ' Crore '; $num %= 10000000; }
        if ($num >= 100000) { $words .= $this->numberToWords(floor($num / 100000)) . ' Lakh '; $num %= 100000; }
        if ($num >= 1000) { $words .= $this->numberToWords(floor($num / 1000)) . ' Thousand '; $num %= 1000; }
        if ($num >= 100) { $words .= $ones[floor($num / 100)] . ' Hundred '; $num %= 100; }
        if ($num >= 20) { $words .= $tens[floor($num / 10)] . ' '; $num %= 10; }
        if ($num > 0) { $words .= $ones[$num] . ' '; }
        return trim($words) . ' Rupees Only';
    }
}