<?php

namespace App\Services\Finance;

use App\Core\Database;
use App\Models\Payment;

/**
 * Invoice Generation Service
 * Creates and manages invoices, receipts, and financial documents
 */
class InvoiceService
{
    private $db;
    private $companyDetails = [
        'name' => 'APS Dream Homes Pvt Ltd',
        'address' => '1st floor singhariya chauraha, Kunraghat, Deoria Road',
        'city' => 'Gorakhpur',
        'state' => 'Uttar Pradesh',
        'pincode' => '273008',
        'gst_number' => 'GSTIN_HERE',
        'pan_number' => 'PAN_HERE',
        'phone' => '+91-9277121112',
        'email' => 'info@apsdreamhomes.com',
        'website' => 'www.apsdreamhomes.com'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate invoice for payment
     */
    public function generateInvoice(int $paymentId): array
    {
        $payment = (new Payment())->find($paymentId);
        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Calculate tax breakdown
        $taxBreakdown = $this->calculateTaxBreakdown($payment['amount']);

        // Create invoice record
        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'payment_id' => $paymentId,
            'user_id' => $payment['user_id'],
            'property_id' => $payment['property_id'],
            'booking_id' => $payment['booking_id'],
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'subtotal' => $taxBreakdown['subtotal'],
            'cgst' => $taxBreakdown['cgst'],
            'sgst' => $taxBreakdown['sgst'],
            'igst' => $taxBreakdown['igst'],
            'total' => $payment['amount'],
            'status' => $payment['status'] === 'completed' ? 'paid' : 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $sql = "INSERT INTO invoices (" . implode(', ', array_keys($invoiceData)) . ") 
                VALUES (" . implode(', ', array_fill(0, count($invoiceData), '?')) . ")";

        $this->db->query($sql, array_values($invoiceData));
        $invoiceId = $this->db->lastInsertId();

        // Generate PDF
        $pdfPath = $this->generateInvoicePDF($invoiceId, $invoiceData, $payment->toArray());

        return [
            'success' => true,
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
            'pdf_path' => $pdfPath,
            'amount' => $payment['amount']
        ];
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get last invoice number for this month
        $sql = "SELECT invoice_number FROM invoices 
                WHERE invoice_number LIKE ? 
                ORDER BY id DESC LIMIT 1";
        $last = $this->db->query($sql, ["{$prefix}{$year}{$month}%"])->fetchColumn();

        if ($last) {
            $sequence = (int)substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf("%s%s%s%04d", $prefix, $year, $month, $sequence);
    }

    /**
     * Calculate tax breakdown
     */
    private function calculateTaxBreakdown(float $amount): array
    {
        $gstRate = 18; // 18% GST for real estate services
        $gstAmount = $amount * ($gstRate / 100);
        $subtotal = $amount - $gstAmount;

        // CGST and SGST for intra-state (9% each)
        $cgst = $gstAmount / 2;
        $sgst = $gstAmount / 2;
        $igst = 0; // IGST for inter-state

        return [
            'subtotal' => round($subtotal, 2),
            'cgst' => round($cgst, 2),
            'sgst' => round($sgst, 2),
            'igst' => round($igst, 2),
            'total_gst' => round($gstAmount, 2)
        ];
    }

    /**
     * Generate invoice PDF
     */
    private function generateInvoicePDF(int $invoiceId, array $invoice, $payment): string
    {
        // Get customer details
        $customer = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$payment['user_id']]
        )->fetch(\PDO::FETCH_ASSOC);

        // Get property details
        $property = $this->db->query(
            "SELECT * FROM properties WHERE id = ?",
            [$payment['property_id']]
        )->fetch(\PDO::FETCH_ASSOC);

        // Build HTML for PDF
        $html = $this->buildInvoiceHTML($invoice, $payment, $customer, $property);

        // Save to file
        $filename = "invoice_{$invoice['invoice_number']}.pdf";
        $filepath = storage_path('invoices/' . $filename);

        // Generate PDF using TCPDF/DOMPDF
        // $this->generatePDF($html, $filepath);

        return $filepath;
    }

    /**
     * Build invoice HTML
     */
    private function buildInvoiceHTML(array $invoice, array $payment, array $customer, array $property): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
                .header { text-align: center; margin-bottom: 30px; }
                .logo { font-size: 24px; font-weight: bold; color: #007bff; }
                .invoice-title { font-size: 20px; color: #333; margin-top: 20px; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                .total { text-align: right; margin-top: 20px; font-size: 16px; }
                .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
            </style>
        </head>
        <body>
            <div class="invoice-box">
                <div class="header">
                    <div class="logo"><?= $this->companyDetails['name'] ?></div>
                    <div><?= $this->companyDetails['address'] ?></div>
                    <div><?= $this->companyDetails['city'] ?>, <?= $this->companyDetails['state'] ?> - <?= $this->companyDetails['pincode'] ?></div>
                    <div>Phone: <?= $this->companyDetails['phone'] ?> | Email: <?= $this->companyDetails['email'] ?></div>
                    <div>GSTIN: <?= $this->companyDetails['gst_number'] ?></div>
                </div>

                <div class="invoice-title">TAX INVOICE</div>

                <div class="details">
                    <table>
                        <tr>
                            <th>Invoice No:</th>
                            <td><?= $invoice['invoice_number'] ?></td>
                            <th>Date:</th>
                            <td><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td><?= $customer['name'] ?? 'N/A' ?></td>
                            <th>Due Date:</th>
                            <td><?= date('d-m-Y', strtotime($invoice['due_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td colspan="3"><?= $customer['address'] ?? 'N/A' ?></td>
                        </tr>
                    </table>
                </div>

                <div class="details">
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $property['title'] ?? 'Property Booking' ?></td>
                                <td><?= number_format($invoice['subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td>CGST (9%)</td>
                                <td><?= number_format($invoice['cgst'], 2) ?></td>
                            </tr>
                            <tr>
                                <td>SGST (9%)</td>
                                <td><?= number_format($invoice['sgst'], 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="total">
                    <strong>Total: ₹<?= number_format($invoice['total'], 2) ?></strong>
                </div>

                <div class="footer">
                    <p>This is a computer generated invoice and does not require signature.</p>
                    <p>Thank you for choosing <?= $this->companyDetails['name'] ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate receipt for payment
     */
    public function generateReceipt(int $paymentId): array
    {
        $payment = (new Payment())->find($paymentId);
        if (!$payment || $payment['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Payment not completed'];
        }

        $receiptNumber = 'RCP' . date('Ymd') . str_pad($paymentId, 4, '0', STR_PAD_LEFT);

        return [
            'success' => true,
            'receipt_number' => $receiptNumber,
            'payment_id' => $paymentId,
            'amount' => $payment['amount'],
            'date' => $payment['completed_at'] ?? date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get invoice by ID
     */
    public function getInvoice(int $invoiceId): ?array
    {
        return $this->db->query(
            "SELECT i.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                    p.title as property_title, p.location as property_location
             FROM invoices i
             JOIN users u ON i.user_id = u.id
             LEFT JOIN properties p ON i.property_id = p.id
             WHERE i.id = ?",
            [$invoiceId]
        )->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get invoices by user
     */
    public function getUserInvoices(int $userId): array
    {
        return $this->db->query(
            "SELECT i.*, p.title as property_title
             FROM invoices i
             LEFT JOIN properties p ON i.property_id = p.id
             WHERE i.user_id = ?
             ORDER BY i.created_at DESC",
            [$userId]
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Send invoice via email
     */
    public function sendInvoiceEmail(int $invoiceId): bool
    {
        $invoice = $this->getInvoice($invoiceId);
        if (!$invoice) {
            return false;
        }

        $emailService = new \App\Services\EmailService();
        
        return $emailService->send([
            'to' => $invoice['customer_email'],
            'subject' => "Invoice #{$invoice['invoice_number']} - APS Dream Homes",
            'body' => "Dear {$invoice['customer_name']},\n\nPlease find attached your invoice for your recent booking.\n\nInvoice Number: {$invoice['invoice_number']}\nAmount: ₹{$invoice['total']}\n\nThank you for choosing APS Dream Homes.",
            'attachment' => $invoice['pdf_path'] ?? null
        ]);
    }
}
