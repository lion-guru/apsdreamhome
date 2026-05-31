<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Invoice Controller - Plot Booking Invoice Management
 * Generates and manages invoices for plot bookings, installments, and payments
 */
class InvoiceController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        parent::__construct();
    }

    /**
     * Display all invoices
     * Route: /admin/invoices
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            // Get all invoices with customer and property details
            $sql = "SELECT i.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                    p.title as property_title, p.location as property_location,
                    CASE i.status
                        WHEN 'draft' THEN 'Draft'
                        WHEN 'sent' THEN 'Sent'
                        WHEN 'partial' THEN 'Partial Payment'
                        WHEN 'paid' THEN 'Paid'
                        WHEN 'overdue' THEN 'Overdue'
                        WHEN 'cancelled' THEN 'Cancelled'
                        ELSE i.status
                    END as status_label
                    FROM invoices i
                    LEFT JOIN users c ON i.customer_id = c.id
                    LEFT JOIN properties p ON i.property_id = p.id
                    ORDER BY i.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get invoice statistics
            $stats = $this->getInvoiceStatistics();
            
            $data = [
                'page_title' => 'Invoice Management',
                'invoices' => $invoices,
                'stats' => $stats
            ];
            
            return $this->render('admin.invoices.index', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.invoices.index', [
                'page_title' => 'Invoice Management',
                'invoices' => [],
                'stats' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display create invoice form
     * Route: /admin/invoices/create
     */
    public function create()
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            // Get users for dropdown
            $users = $conn->query("SELECT id, name, email, phone FROM users ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get properties for dropdown
            $properties = $conn->query("SELECT id, title, location, price FROM properties WHERE status = 'available' ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd-His');
            
            $data = [
                'page_title' => 'Create Invoice',
                'users' => $users,
                'properties' => $properties,
                'invoice_number' => $invoiceNumber
            ];
            
            return $this->render('admin.invoices.create', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.invoices.create', [
                'page_title' => 'Create Invoice',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new invoice
     * Route: /admin/invoices/store
     */
    public function store()
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            $invoiceNumber = $_POST['invoice_number'] ?? 'INV-' . date('Ymd-His');
            $customerId = $_POST['customer_id'] ?? 0;
            $propertyId = $_POST['property_id'] ?? 0;
            $amount = $_POST['amount'] ?? 0;
            $dueDate = $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
            $description = $_POST['description'] ?? '';
            $installmentNumber = $_POST['installment_number'] ?? 1;
            $totalInstallments = $_POST['total_installments'] ?? 1;
            
            $sql = "INSERT INTO invoices 
                    (invoice_number, customer_id, property_id, amount, due_date, description, 
                     installment_number, total_installments, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sent', NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $invoiceNumber, $customerId, $propertyId, $amount, $dueDate, $description,
                $installmentNumber, $totalInstallments
            ]);
            
            header('Location: /admin/invoices?success=Invoice created successfully');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices/create?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Display single invoice details
     * Route: /admin/invoices/{id}
     */
    public function show($id)
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT i.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                    c.address as customer_address,
                    p.title as property_title, p.location as property_location, p.price as property_price,
                    CASE i.status
                        WHEN 'draft' THEN 'Draft'
                        WHEN 'sent' THEN 'Sent'
                        WHEN 'partial' THEN 'Partial Payment'
                        WHEN 'paid' THEN 'Paid'
                        WHEN 'overdue' THEN 'Overdue'
                        WHEN 'cancelled' THEN 'Cancelled'
                        ELSE i.status
                    END as status_label
                    FROM invoices i
                    LEFT JOIN users c ON i.customer_id = c.id
                    LEFT JOIN properties p ON i.property_id = p.id
                    WHERE i.id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                header('Location: /admin/invoices?error=Invoice not found');
                exit;
            }
            
            // Get payment history for this invoice
            $payments = $conn->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC");
            $payments->execute([$id]);
            $paymentHistory = $payments->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Invoice Details - ' . $invoice['invoice_number'],
                'invoice' => $invoice,
                'payment_history' => $paymentHistory
            ];
            
            return $this->render('admin.invoices.show', $data);
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Display edit invoice form
     * Route: /admin/invoices/{id}/edit
     */
    public function edit($id)
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            // Get invoice details
            $sql = "SELECT i.*, c.name as customer_name, p.title as property_title
                    FROM invoices i
                    LEFT JOIN users c ON i.customer_id = c.id
                    LEFT JOIN properties p ON i.property_id = p.id
                    WHERE i.id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                header('Location: /admin/invoices?error=Invoice not found');
                exit;
            }
            
            // Get users and properties for dropdown
            $users = $conn->query("SELECT id, name, email, phone FROM users ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $properties = $conn->query("SELECT id, title, location, price FROM properties ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Edit Invoice',
                'invoice' => $invoice,
                'users' => $users,
                'properties' => $properties
            ];
            
            return $this->render('admin.invoices.edit', $data);
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Update invoice
     * Route: /admin/invoices/{id}/update
     */
    public function update($id)
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            $invoiceNumber = $_POST['invoice_number'] ?? '';
            $customerId = $_POST['customer_id'] ?? 0;
            $propertyId = $_POST['property_id'] ?? 0;
            $amount = $_POST['amount'] ?? 0;
            $dueDate = $_POST['due_date'] ?? '';
            $description = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? 'sent';
            
            $sql = "UPDATE invoices 
                    SET invoice_number = ?, customer_id = ?, property_id = ?, amount = ?, 
                        due_date = ?, description = ?, status = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$invoiceNumber, $customerId, $propertyId, $amount, $dueDate, $description, $status, $id]);
            
            header('Location: /admin/invoices?success=Invoice updated successfully');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices/' . $id . '/edit?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Delete invoice
     * Route: /admin/invoices/{id}/delete
     */
    public function delete($id)
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            // Delete associated payments first
            $conn->prepare("DELETE FROM payments WHERE invoice_id = ?")->execute([$id]);
            
            // Delete invoice
            $conn->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);
            
            header('Location: /admin/invoices?success=Invoice deleted successfully');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Mark invoice as paid
     * Route: /admin/invoices/{id}/mark-paid
     */
    public function markAsPaid($id)
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            // Update invoice status
            $conn->prepare("UPDATE invoices SET status = 'paid', updated_at = NOW() WHERE id = ?")->execute([$id]);
            
            // Record payment
            $invoice = $conn->prepare("SELECT amount, customer_id FROM invoices WHERE id = ?");
            $invoice->execute([$id]);
            $invoiceData = $invoice->fetch(\PDO::FETCH_ASSOC);
            
            $paymentSql = "INSERT INTO payments (invoice_id, customer_id, amount, payment_date, payment_method, status, created_at)
                          VALUES (?, ?, ?, NOW(), 'cash', 'completed', NOW())";
            $conn->prepare($paymentSql)->execute([$id, $invoiceData['customer_id'], $invoiceData['amount']]);
            
            header('Location: /admin/invoices?success=Invoice marked as paid');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Send invoice to customer
     * Route: /admin/invoices/{id}/send
     */
    public function sendInvoice($id)
    {
        $this->requireAdmin();
        try {
            $conn = $this->db->getConnection();
            
            // Update invoice status to sent
            $conn->prepare("UPDATE invoices SET status = 'sent', updated_at = NOW() WHERE id = ?")->execute([$id]);
            
            // Here you would integrate with email service to send invoice
            // For now, we'll just update the status
            
            header('Location: /admin/invoices?success=Invoice sent successfully');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/invoices?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Get invoice statistics
     */
    private function getInvoiceStatistics()
    {
        try {
            $conn = $this->db->getConnection();
            
            $stats = [
                'total_invoices' => $conn->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
                'total_amount' => $conn->query("SELECT COALESCE(SUM(amount), 0) FROM invoices")->fetchColumn(),
                'paid_invoices' => $conn->query("SELECT COUNT(*) FROM invoices WHERE status = 'paid'")->fetchColumn(),
                'paid_amount' => $conn->query("SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE status = 'paid'")->fetchColumn(),
                'pending_invoices' => $conn->query("SELECT COUNT(*) FROM invoices WHERE status IN ('sent', 'partial')")->fetchColumn(),
                'pending_amount' => $conn->query("SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE status IN ('sent', 'partial')")->fetchColumn(),
                'overdue_invoices' => $conn->query("SELECT COUNT(*) FROM invoices WHERE status = 'overdue'")->fetchColumn(),
                'overdue_amount' => $conn->query("SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE status = 'overdue'")->fetchColumn()
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            return [];
        }
    }
}
