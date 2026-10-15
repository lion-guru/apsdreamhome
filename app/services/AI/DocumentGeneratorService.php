<?php
/**
 * DocumentGeneratorService - AI-assisted document generation
 * Generates agreements, allotment letters, receipts, etc.
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class DocumentGeneratorService
{
    use ServiceTenantTrait;

    private $db;
    private $freeEngines;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->freeEngines = FreeAIEngines::getInstance();
    }

    /**
     * Generate Sale Agreement
     */
    public function generateSaleAgreement(array $params): array
    {
        $required = ['booking_id', 'template'];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                return ['success' => false, 'error' => "Missing required field: $field"];
            }
        }

        $tid = $this->tenantId();
        $booking = $this->db->fetch(
            "SELECT b.*, p.plot_number, p.colony_id, c.name as colony_name, 
                    u.name as customer_name, u.email, u.phone, u.pan_number, u.aadhaar_number,
                    u.address as customer_address
             FROM plot_bookings b
             JOIN plots p ON b.plot_id = p.id
             JOIN colonies c ON p.colony_id = c.id
             JOIN users u ON b.customer_id = u.id
             WHERE b.id = ?" . ($this->tenantId() > 1 ? " AND b.tenant_id = ?" : ""),
            array_merge([$params['booking_id']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );

        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found'];
        }

        // Get plot pricing
        $plot = $this->db->fetch("SELECT * FROM plots WHERE id = ?", [$booking['plot_id']]);
        
        // Generate agreement using AI
        $prompt = $this->buildAgreementPrompt($booking, $params['template']);
        $result = $this->freeEngines->generate($prompt, [
            'system' => 'You are a legal document generator for APS Dream Home. Generate professional sale agreements with proper legal clauses.',
            'max_tokens' => 4000,
            'temperature' => 0.3
        ]);

        if (!$result['text']) {
            return ['success' => false, 'error' => 'Failed to generate agreement'];
        }

        // Save generated document
        $tid = $this->tenantId();
        $docId = $this->db->insert('generated_documents', [
            'booking_id' => $params['booking_id'],
            'document_type' => 'sale_agreement',
            'template_used' => $params['template'],
            'content' => $result['text'],
            'status' => 'draft',
            'generated_by' => $params['generated_by'] ?? 0,
            'tenant_id' => $tid > 1 ? $tid : 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'document_id' => $docId,
            'content' => $result['text'],
            'booking' => $booking
        ];
    }

    /**
     * Generate Allotment Letter
     */
    public function generateAllotmentLetter(array $params): array
    {
        $required = ['booking_id'];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                return ['success' => false, 'error' => "Missing required field: $field"];
            }
        }

        $tid = $this->tenantId();
        $booking = $this->db->fetch(
            "SELECT b.*, p.plot_number, p.colony_id, c.name as colony_name,
                    u.name as customer_name, u.email, u.phone, u.address as customer_address
             FROM plot_bookings b
             JOIN plots p ON b.plot_id = p.id
             JOIN colonies c ON p.colony_id = c.id
             JOIN users u ON b.customer_id = u.id
             WHERE b.id = ?" . ($this->tenantId() > 1 ? " AND b.tenant_id = ?" : ""),
            array_merge([$params['booking_id']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );

        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found'];
        }

        $prompt = $this->buildAllotmentLetterPrompt($booking);
        $result = $this->freeEngines->generate($prompt, [
            'system' => 'You are a legal document generator for APS Dream Home. Generate professional allotment letters.',
            'max_tokens' => 3000,
            'temperature' => 0.3
        ]);

        if (!$result['text']) {
            return ['success' => false, 'error' => 'Failed to generate allotment letter'];
        }

        $tid = $this->tenantId();
        $docId = $this->db->insert('generated_documents', [
            'booking_id' => $params['booking_id'],
            'document_type' => 'allotment_letter',
            'content' => $result['text'],
            'status' => 'draft',
            'generated_by' => $params['generated_by'] ?? 0,
            'tenant_id' => $tid > 1 ? $tid : 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'document_id' => $docId,
            'content' => $result['text']
        ];
    }

    /**
     * Generate Receipt
     */
    public function generateReceipt(array $params): array
    {
        $required = ['payment_id'];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                return ['success' => false, 'error' => "Missing required field: $field"];
            }
        }

        $tid = $this->tenantId();
        $payment = $this->db->fetch(
            "SELECT py.*, b.booking_number, b.plot_id, u.name as customer_name, u.email, u.phone
             FROM payments py
             LEFT JOIN plot_bookings b ON py.booking_id = b.id
             LEFT JOIN users u ON py.customer_id = u.id
             WHERE py.id = ?" . ($this->tenantId() > 1 ? " AND py.tenant_id = ?" : ""),
            array_merge([$params['payment_id']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );

        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        $prompt = $this->buildReceiptPrompt($payment);
        $result = $this->freeEngines->generate($prompt, [
            'system' => 'You are a receipt generator for APS Dream Home. Generate professional payment receipts with company details.',
            'max_tokens' => 2000,
            'temperature' => 0.2
        ]);

        if (!$result['text']) {
            return ['success' => false, 'error' => 'Failed to generate receipt'];
        }

        $tid = $this->tenantId();
        $docId = $this->db->insert('generated_documents', [
            'payment_id' => $params['payment_id'],
            'document_type' => 'receipt',
            'content' => $result['text'],
            'status' => 'final',
            'generated_by' => $params['generated_by'] ?? 0,
            'tenant_id' => $tid > 1 ? $tid : 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'document_id' => $docId,
            'content' => $result['text']
        ];
    }

    /**
     * Get generated documents
     */
    public function getDocuments(array $filters = []): array
    {
        $tid = $this->tenantId();
        $where = "1=1";
        $params = [];

        if (!empty($filters['booking_id'])) {
            $where .= " AND booking_id = ?";
            $params[] = $filters['booking_id'];
        }
        if (!empty($filters['document_type'])) {
            $where .= " AND document_type = ?";
            $params[] = $filters['document_type'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if ($this->tenantId() > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $this->tenantId();
        }

        $docs = $this->db->fetchAll(
            "SELECT * FROM generated_documents WHERE $where ORDER BY created_at DESC",
            $params
        ) ?: [];

        return ['success' => true, 'documents' => $docs];
    }

    /**
     * Build agreement prompt
     */
    private function buildAgreementPrompt(array $booking, string $template): string
    {
        $plotDetails = "Plot: {$booking['plot_number']}, Colony: {$booking['colony_name']}";
        $customerDetails = "Name: {$booking['customer_name']}, Email: {$booking['email']}, Phone: {$booking['phone']}";
        
        if ($booking['pan_number']) $customerDetails .= ", PAN: {$booking['pan_number']}";
        if ($booking['aadhaar_number']) $customerDetails .= ", Aadhaar: {$booking['aadhaar_number']}";
        if ($booking['customer_address']) $customerDetails .= ", Address: {$booking['customer_address']}";

        $price = $booking['total_plot_value'] ?? $booking['agreement_value'] ?? 0;
        $bookingAmount = $booking['booking_amount'] ?? 0;
        $balance = $price - $bookingAmount;

        $clauses = [
            'parties' => "This Agreement is made between APS Dream Home (Seller) and {$booking['customer_name']} (Buyer).",
            'property' => "The property being sold is Plot No. {$booking['plot_number']} in {$booking['colony_name']}.",
            'price' => "Total consideration: ₹" . number_format($price, 0) . ". Booking amount paid: ₹" . number_format($bookingAmount, 0) . ". Balance: ₹" . number_format($balance, 0) . ".",
            'payment' => "Payment schedule as per annexed schedule. Delayed payments attract 18% per annum interest.",
            'possession' => "Possession shall be handed over within 30 days of full payment and registration.",
            'registration' => "Registration at Sub-Registrar Office. Stamp duty and registration charges as per UP Govt rates.",
            'default' => "Default in payment > 60 days allows Seller to cancel agreement and forfeit 10% of paid amount.",
            'dispute' => "Disputes subject to Gorakhpur jurisdiction. Arbitration under Arbitration Act, 1996.",
        ];

        return "Generate a professional Sale Agreement for APS Dream Home with the following details:\n\n" .
               "CUSTOMER: {$customerDetails}\n" .
               "PROPERTY: {$plotDetails}\n" .
               "PRICING: Total ₹" . number_format($price, 0) . ", Paid ₹" . number_format($bookingAmount, 0) . ", Balance ₹" . number_format($balance, 0) . "\n\n" .
               "Include these clauses:\n" . implode("\n", $clauses) . "\n\n" .
               "Format as professional legal document with proper formatting, clause numbers, and signature blocks for both parties. Use formal legal Hindi-English bilingual style where appropriate.";
    }

    private function buildAllotmentLetterPrompt(array $booking): string
    {
        return "Generate a professional Allotment Letter for APS Dream Home:\n\n" .
               "TO: {$booking['customer_name']}\n" .
               "Email: {$booking['email']}\n" .
               "Phone: {$booking['phone']}\n\n" .
               "Dear {$booking['customer_name']},\n\n" .
               "We are pleased to inform you that Plot No. {$booking['plot_number']} in {$booking['colony_name']} has been allotted to you.\n\n" .
               "Details:\n" .
               "- Plot Number: {$booking['plot_number']}\n" .
               "- Colony: {$booking['colony_name']}\n" .
               "- Total Price: ₹" . number_format($booking['total_plot_value'] ?? 0, 0) . "\n" .
               "- Booking Amount Paid: ₹" . number_format($booking['booking_amount'] ?? 0, 0) . "\n" .
               "- Balance Due: ₹" . number_format(($booking['total_plot_value'] ?? 0) - ($booking['booking_amount'] ?? 0), 0) . "\n\n" .
               "Please complete the remaining payment as per the payment schedule. Upon full payment, registry will be initiated.\n\n" .
               "For queries, contact us at +91-XXXXXXXXXX or email support@apsdreamhome.com\n\n" .
               "Regards,\nAPS Dream Home Team\n";
    }

    private function buildReceiptPrompt(array $payment): string
    {
        return "Generate a professional Payment Receipt for APS Dream Home:\n\n" .
               "RECEIPT NO: {$payment['payment_id']}\n" .
               "DATE: " . date('d/m/Y') . "\n\n" .
               "Received from: {$payment['customer_name']}\n" .
               "Booking: {$payment['booking_number']}\n" .
               "Amount: ₹" . number_format($payment['amount'], 2) . "\n" .
               "Mode: {$payment['gateway']}\n" .
               "Transaction ID: {$payment['gateway_transaction_id']}\n\n" .
               "Thank you for your payment.\n\n" .
               "APS Dream Home\n" .
               "Gorakhpur, Uttar Pradesh\n";
    }
}