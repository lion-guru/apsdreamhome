<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Invoice Generation Model
 * Handles invoice creation, management, payments, and templates
 */
class Invoice extends Model
{
    protected $table = 'invoices';
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'client_id',
        'client_type',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'billing_address',
        'shipping_address',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'payment_terms',
        'notes',
        'template_id',
        'generated_by',
        'sent_at',
        'paid_at',
        'reminder_count',
        'last_reminder',
        'created_at',
        'updated_at'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_VIEWED = 'viewed';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    const CLIENT_TYPE_CUSTOMER = 'customer';
    const CLIENT_TYPE_ASSOCIATE = 'associate';
    const CLIENT_TYPE_VENDOR = 'vendor';
    const CLIENT_TYPE_EMPLOYEE = 'employee';

    /**
     * Create a new invoice
     */
    public function createInvoice(array $invoiceData, array $items): array
    {
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Calculate totals
        $totals = $this->calculateTotals($items);

        $invoiceRecord = [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceData['invoice_date'] ?? date('Y-m-d'),
            'due_date' => $invoiceData['due_date'],
            'client_id' => $invoiceData['client_id'] ?? null,
            'client_type' => $invoiceData['client_type'] ?? self::CLIENT_TYPE_CUSTOMER,
            'client_name' => $invoiceData['client_name'],
            'client_email' => $invoiceData['client_email'] ?? null,
            'client_phone' => $invoiceData['client_phone'] ?? null,
            'client_address' => $invoiceData['client_address'] ?? null,
            'billing_address' => $invoiceData['billing_address'] ?? null,
            'shipping_address' => $invoiceData['shipping_address'] ?? null,
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount_amount' => $totals['discount_amount'],
            'total_amount' => $totals['total_amount'],
            'currency' => $invoiceData['currency'] ?? 'INR',
            'status' => self::STATUS_DRAFT,
            'payment_terms' => $invoiceData['payment_terms'] ?? null,
            'notes' => $invoiceData['notes'] ?? null,
            'template_id' => $invoiceData['template_id'] ?? null,
            'generated_by' => $invoiceData['generated_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $invoiceId = $this->insert($invoiceRecord);

        // Add invoice items
        $this->addInvoiceItems($invoiceId, $items);

        return [
            'success' => true,
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
            'message' => 'Invoice created successfully'
        ];
    }

    /**
     * Update invoice
     */
    public function updateInvoice(int $invoiceId, array $invoiceData, array $items = null): array
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        // Prevent updates if invoice is paid or cancelled
        if (in_array($invoice['status'], [self::STATUS_PAID, self::STATUS_CANCELLED])) {
            return ['success' => false, 'message' => 'Cannot update paid or cancelled invoices'];
        }

        $updateData = [
            'client_name' => $invoiceData['client_name'] ?? $invoice['client_name'],
            'client_email' => $invoiceData['client_email'] ?? $invoice['client_email'],
            'client_phone' => $invoiceData['client_phone'] ?? $invoice['client_phone'],
            'client_address' => $invoiceData['client_address'] ?? $invoice['client_address'],
            'billing_address' => $invoiceData['billing_address'] ?? $invoice['billing_address'],
            'shipping_address' => $invoiceData['shipping_address'] ?? $invoice['shipping_address'],
            'due_date' => $invoiceData['due_date'] ?? $invoice['due_date'],
            'payment_terms' => $invoiceData['payment_terms'] ?? $invoice['payment_terms'],
            'notes' => $invoiceData['notes'] ?? $invoice['notes'],
            'template_id' => $invoiceData['template_id'] ?? $invoice['template_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Recalculate totals if items provided
        if ($items !== null) {
            $totals = $this->calculateTotals($items);
            $updateData['subtotal'] = $totals['subtotal'];
            $updateData['tax_amount'] = $totals['tax_amount'];
            $updateData['discount_amount'] = $totals['discount_amount'];
            $updateData['total_amount'] = $totals['total_amount'];

            // Update invoice items
            $this->updateInvoiceItems($invoiceId, $items);
        }

        $this->update($invoiceId, $updateData);

        return [
            'success' => true,
            'message' => 'Invoice updated successfully'
        ];
    }

    /**
     * Send invoice to client
     */
    public function sendInvoice(int $invoiceId): array
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        if ($invoice['status'] === self::STATUS_DRAFT) {
            // Update status to sent
            $this->update($invoiceId, [
                'status' => self::STATUS_SENT,
                'sent_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Here you would integrate with email service to send the invoice
        // For now, just mark as sent

        return [
            'success' => true,
            'message' => 'Invoice sent successfully'
        ];
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(int $invoiceId, array $paymentData): array
    {
        $db = Database::getInstance();

        $paymentRecord = [
            'invoice_id' => $invoiceId,
            'payment_date' => $paymentData['payment_date'] ?? date('Y-m-d'),
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'] ?? 'online',
            'reference_number' => $paymentData['reference_number'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'received_by' => $paymentData['received_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO invoice_payments (invoice_id, payment_date, amount, payment_method, reference_number, notes, received_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $paymentRecord['invoice_id'], $paymentRecord['payment_date'], $paymentRecord['amount'],
                $paymentRecord['payment_method'], $paymentRecord['reference_number'], $paymentRecord['notes'],
                $paymentRecord['received_by'], $paymentRecord['created_at']
            ]
        );

        // Check if invoice is fully paid
        $totalPaid = $this->getTotalPayments($invoiceId);
        $invoice = $this->find($invoiceId);

        if ($totalPaid >= $invoice['total_amount']) {
            $this->update($invoiceId, [
                'status' => self::STATUS_PAID,
                'paid_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return [
            'success' => true,
            'message' => 'Payment recorded successfully'
        ];
    }

    /**
     * Generate invoice PDF/HTML
     */
    public function generateInvoiceHTML(int $invoiceId): ?string
    {
        $invoice = $this->getInvoiceDetails($invoiceId);
        if (!$invoice) {
            return null;
        }

        // Get invoice template
        $template = $this->getInvoiceTemplate($invoice['template_id']);

        // Replace placeholders in template
        $html = $this->replaceTemplatePlaceholders($template['template_html'], $invoice);

        return $html;
    }

    /**
     * Get invoice details with items
     */
    public function getInvoiceDetails(int $invoiceId): ?array
    {
        $db = Database::getInstance();

        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return null;
        }

        $invoice = $invoice->toArray();

        // Get invoice items
        $items = $db->query(
            "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order",
            [$invoiceId]
        )->fetchAll();

        // Get payments
        $payments = $db->query(
            "SELECT * FROM invoice_payments WHERE invoice_id = ? ORDER BY payment_date DESC",
            [$invoiceId]
        )->fetchAll();

        $invoice['items'] = $items;
        $invoice['payments'] = $payments;
        $invoice['total_paid'] = array_sum(array_column($payments, 'amount'));

        return $invoice;
    }

    /**
     * Get invoices with filters
     */
    public function getInvoices(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();

        $whereConditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $whereConditions[] = "i.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['client_type'])) {
            $whereConditions[] = "i.client_type = ?";
            $params[] = $filters['client_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "i.invoice_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "i.invoice_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(i.invoice_number LIKE ? OR i.client_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);

        $sql = "SELECT i.*, COUNT(ip.id) as payment_count, SUM(ip.amount) as paid_amount
                FROM invoices i
                LEFT JOIN invoice_payments ip ON i.id = ip.invoice_id
                {$whereClause}
                GROUP BY i.id
                ORDER BY i.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return $db->query($sql, $params)->fetchAll();
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $date = date('Ym');
        $prefix = 'INV-' . $date . '-';

        // Find the highest number for this month
        $existing = $this->query(
            "SELECT invoice_number FROM invoices
             WHERE invoice_number LIKE ?
             ORDER BY invoice_number DESC LIMIT 1",
            [$prefix . '%']
        )->fetch();

        if ($existing) {
            $lastNumber = (int)substr($existing['invoice_number'], -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals from items
     */
    private function calculateTotals(array $items): array
    {
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $discountPercent = $item['discount_percent'] ?? 0;
            $taxPercent = $item['tax_percent'] ?? 0;

            $lineTotal = $quantity * $unitPrice;
            $discountAmount = ($lineTotal * $discountPercent) / 100;
            $taxableAmount = $lineTotal - $discountAmount;
            $taxAmount = ($taxableAmount * $taxPercent) / 100;

            $subtotal += $lineTotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
        }

        $totalAmount = $subtotal - $totalDiscount + $totalTax;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($totalTax, 2),
            'discount_amount' => round($totalDiscount, 2),
            'total_amount' => round($totalAmount, 2)
        ];
    }

    /**
     * Add invoice items
     */
    private function addInvoiceItems(int $invoiceId, array $items): void
    {
        $db = Database::getInstance();

        foreach ($items as $index => $item) {
            $itemData = [
                'invoice_id' => $invoiceId,
                'item_type' => $item['item_type'] ?? 'service',
                'item_name' => $item['item_name'],
                'item_description' => $item['item_description'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'tax_percent' => $item['tax_percent'] ?? 0,
                'sort_order' => $index,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Calculate amounts
            $lineTotal = $itemData['quantity'] * $itemData['unit_price'];
            $discountAmount = ($lineTotal * $itemData['discount_percent']) / 100;
            $taxableAmount = $lineTotal - $discountAmount;
            $taxAmount = ($taxableAmount * $itemData['tax_percent']) / 100;

            $itemData['discount_amount'] = $discountAmount;
            $itemData['tax_amount'] = $taxAmount;
            $itemData['line_total'] = $lineTotal - $discountAmount + $taxAmount;

            $db->query(
                "INSERT INTO invoice_items (invoice_id, item_type, item_name, item_description, quantity, unit_price, discount_percent, discount_amount, tax_percent, tax_amount, line_total, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $itemData['invoice_id'], $itemData['item_type'], $itemData['item_name'],
                    $itemData['item_description'], $itemData['quantity'], $itemData['unit_price'],
                    $itemData['discount_percent'], $itemData['discount_amount'], $itemData['tax_percent'],
                    $itemData['tax_amount'], $itemData['line_total'], $itemData['sort_order'], $itemData['created_at']
                ]
            );
        }
    }

    /**
     * Update invoice items
     */
    private function updateInvoiceItems(int $invoiceId, array $items): void
    {
        $db = Database::getInstance();

        // Delete existing items
        $db->query("DELETE FROM invoice_items WHERE invoice_id = ?", [$invoiceId]);

        // Add new items
        $this->addInvoiceItems($invoiceId, $items);
    }

    /**
     * Get total payments for invoice
     */
    private function getTotalPayments(int $invoiceId): float
    {
        $db = Database::getInstance();

        $result = $db->query(
            "SELECT SUM(amount) as total FROM invoice_payments WHERE invoice_id = ?",
            [$invoiceId]
        )->fetch();

        return (float)($result['total'] ?? 0);
    }

    /**
     * Get invoice template
     */
    private function getInvoiceTemplate(int $templateId = null): ?array
    {
        if ($templateId) {
            $template = $this->query("SELECT * FROM invoice_templates WHERE id = ?", [$templateId])->fetch();
            if ($template) return $template;
        }

        // Get default template
        return $this->query("SELECT * FROM invoice_templates WHERE is_default = 1 LIMIT 1")->fetch();
    }

    /**
     * Replace template placeholders
     */
    private function replaceTemplatePlaceholders(string $template, array $invoice): string
    {
        $replacements = [
            '{{invoice_number}}' => $invoice['invoice_number'],
            '{{invoice_date}}' => date('d/m/Y', strtotime($invoice['invoice_date'])),
            '{{due_date}}' => date('d/m/Y', strtotime($invoice['due_date'])),
            '{{client_name}}' => htmlspecialchars($invoice['client_name']),
            '{{client_email}}' => htmlspecialchars($invoice['client_email'] ?? ''),
            '{{client_phone}}' => htmlspecialchars($invoice['client_phone'] ?? ''),
            '{{client_address}}' => nl2br(htmlspecialchars($invoice['client_address'] ?? '')),
            '{{company_name}}' => 'APS Dream Home', // You can make this configurable
            '{{company_address}}' => 'Your Company Address', // Make this configurable
            '{{company_email}}' => 'info@apsdreamhome.com',
            '{{company_phone}}' => '+91-XXXXXXXXXX',
            '{{subtotal}}' => number_format($invoice['subtotal'], 2),
            '{{tax_amount}}' => number_format($invoice['tax_amount'], 2),
            '{{discount_amount}}' => number_format($invoice['discount_amount'], 2),
            '{{total_amount}}' => number_format($invoice['total_amount'], 2),
            '{{currency}}' => $invoice['currency'],
            '{{payment_terms}}' => htmlspecialchars($invoice['payment_terms'] ?? ''),
        ];

        // Generate items HTML
        $itemsHtml = '';
        foreach ($invoice['items'] as $item) {
            $itemsHtml .= "<tr>
                <td>{$item['item_name']}" . ($item['item_description'] ? "<br><small>{$item['item_description']}</small>" : '') . "</td>
                <td>{$item['quantity']}</td>
                <td>₹" . number_format($item['unit_price'], 2) . "</td>
                <td>" . ($item['discount_percent'] > 0 ? $item['discount_percent'] . '%' : '-') . "</td>
                <td>" . ($item['tax_percent'] > 0 ? $item['tax_percent'] . '%' : '-') . "</td>
                <td>₹" . number_format($item['line_total'], 2) . "</td>
            </tr>";
        }
        $replacements['{{items}}'] = $itemsHtml;

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Send payment reminders
     */
    public function sendPaymentReminder(int $invoiceId): array
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        $invoice = $invoice->toArray();

        if ($invoice['status'] === self::STATUS_PAID) {
            return ['success' => false, 'message' => 'Invoice is already paid'];
        }

        // Increment reminder count
        $newCount = $invoice['reminder_count'] + 1;

        $this->update($invoiceId, [
            'reminder_count' => $newCount,
            'last_reminder' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Here you would send email/SMS reminder
        // For now, just log it

        $db = Database::getInstance();
        $db->query(
            "INSERT INTO invoice_reminders (invoice_id, reminder_type, reminder_date, subject, message, status, created_at)
             VALUES (?, 'email', NOW(), ?, ?, 'sent', NOW())",
            [
                $invoiceId,
                "Payment Reminder for Invoice {$invoice['invoice_number']}",
                "Dear {$invoice['client_name']}, this is a reminder that your invoice {$invoice['invoice_number']} is due for payment.",
                date('Y-m-d H:i:s')
            ]
        );

        return [
            'success' => true,
            'message' => 'Payment reminder sent successfully'
        ];
    }
}
