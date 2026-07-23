<?php

namespace App\Http\Controllers\Admin;

use App\Services\Accounting\InvoiceService;
use Exception;

class InvoiceController extends AdminController
{
    protected $db;
    protected $service;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \App\Core\Database\Database::getInstance();
            if (method_exists($this->db, 'getPdo')) {
                $this->db = $this->db->getPdo();
            }
        } catch (\Exception $e) {
            $this->db = null;
        }
        try {
            $this->service = new InvoiceService($this->db instanceof \PDO ? $this->db : null);
        } catch (\Exception $e) {
            $this->service = new InvoiceService();
        }
    }

    private function safe(callable $fn, $fallback = null)
    {
        try {
            return $fn();
        } catch (\Exception $e) {
            return $fallback;
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->safe(fn() => $this->service->getStats(), []);
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'page' => $_GET['page'] ?? 1,
            'per_page' => 20,
        ];
        $result = $this->safe(fn() => $this->service->listInvoices($filters), ['invoices' => [], 'total' => 0, 'page' => 1, 'total_pages' => 1]);

        return $this->render('admin/invoices/index', [
            'page_title' => 'Invoice Management',
            'page_heading' => 'Invoice Management',
            'stats' => $stats,
            'invoices' => $result['invoices'],
            'total' => $result['total'],
            'page' => $result['page'],
            'total_pages' => $result['total_pages'],
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $users = $this->safe(fn() => $this->service->getUsers(), []);
        $bookings = $this->safe(fn() => $this->service->getBookings(), []);
        $invoiceNumber = $this->safe(fn() => $this->service->generateInvoiceNumber(), 'APS-INV-' . date('Ymd') . '-0001');

        return $this->render('admin/invoices/create', [
            'page_title' => 'Create Invoice',
            'page_heading' => 'Create Invoice',
            'users' => $users,
            'bookings' => $bookings,
            'invoice_number' => $invoiceNumber,
            'company' => $this->service->getCompanyDetails(),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $data = [
            'client_id' => $_POST['client_id'] ?? null,
            'client_type' => $_POST['client_type'] ?? 'customer',
            'client_name' => $_POST['client_name'] ?? '',
            'client_email' => $_POST['client_email'] ?? '',
            'client_phone' => $_POST['client_phone'] ?? '',
            'client_address' => $_POST['client_address'] ?? '',
            'billing_address' => $_POST['billing_address'] ?? '',
            'shipping_address' => $_POST['shipping_address'] ?? '',
            'place_of_supply' => $_POST['place_of_supply'] ?? 'Uttar Pradesh',
            'gstin' => $_POST['gstin'] ?? '',
            'hsn_code' => $_POST['hsn_code'] ?? '',
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'discount_amount' => $_POST['discount_amount'] ?? 0,
            'status' => $_POST['status'] ?? 'draft',
            'payment_terms' => $_POST['payment_terms'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'booking_id' => !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null,
            'items' => [],
        ];

        if (!empty($_POST['item_name']) && is_array($_POST['item_name'])) {
            $count = count($_POST['item_name']);
            for ($i = 0; $i < $count; $i++) {
                if (empty($_POST['item_name'][$i])) continue;
                $data['items'][] = [
                    'item_type' => $_POST['item_type'][$i] ?? 'service',
                    'item_name' => $_POST['item_name'][$i] ?? '',
                    'item_description' => $_POST['item_description'][$i] ?? '',
                    'quantity' => $_POST['item_quantity'][$i] ?? 1,
                    'unit_price' => $_POST['item_unit_price'][$i] ?? 0,
                    'discount_percent' => $_POST['item_discount'][$i] ?? 0,
                    'tax_percent' => $_POST['item_tax'][$i] ?? 18,
                    'sort_order' => $i + 1,
                ];
            }
        }

        if (empty($data['items'])) {
            $subtotal = (float)($_POST['subtotal'] ?? 0);
            if ($subtotal > 0) {
                $data['subtotal'] = $subtotal;
                $data['items'][] = [
                    'item_type' => 'service',
                    'item_name' => $_POST['item_name_text'] ?? 'Invoice Item',
                    'item_description' => $_POST['item_description_text'] ?? '',
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'discount_percent' => 0,
                    'tax_percent' => 18,
                ];
            }
        }

        try {
            $invoiceId = $this->service->createInvoice($data);
            redirect(BASE_URL . '/admin/invoices/manage/' . $invoiceId . '?success=Invoice created successfully');
            exit;
        } catch (\Exception $e) {
            redirect(BASE_URL . '/admin/invoices/manage/create?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        $invoice = $this->service->getInvoice((int)$id);
        if (!$invoice) {
            redirect(BASE_URL . '/admin/invoices/manage?error=Invoice not found');
            exit;
        }

        return $this->render('admin/invoices/show', [
            'page_title' => 'Invoice ' . ($invoice['invoice_number'] ?? ''),
            'page_heading' => 'Invoice Details',
            'invoice' => $invoice,
            'company' => $this->service->getCompanyDetails(),
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $invoice = $this->service->getInvoice((int)$id);
        if (!$invoice) {
            redirect(BASE_URL . '/admin/invoices/manage?error=Invoice not found');
            exit;
        }
        $users = $this->safe(fn() => $this->service->getUsers(), []);
        $bookings = $this->safe(fn() => $this->service->getBookings(), []);

        return $this->render('admin/invoices/edit', [
            'page_title' => 'Edit Invoice ' . ($invoice['invoice_number'] ?? ''),
            'page_heading' => 'Edit Invoice',
            'invoice' => $invoice,
            'users' => $users,
            'bookings' => $bookings,
            'company' => $this->service->getCompanyDetails(),
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $pdo = $this->db instanceof \PDO ? $this->db : (\App\Core\Database\Database::getInstance())->getConnection();

        $data = [
            'client_id' => $_POST['client_id'] ?? null,
            'client_type' => $_POST['client_type'] ?? 'customer',
            'client_name' => $_POST['client_name'] ?? '',
            'client_email' => $_POST['client_email'] ?? '',
            'client_phone' => $_POST['client_phone'] ?? '',
            'client_address' => $_POST['client_address'] ?? '',
            'billing_address' => $_POST['billing_address'] ?? '',
            'shipping_address' => $_POST['shipping_address'] ?? '',
            'place_of_supply' => $_POST['place_of_supply'] ?? 'Uttar Pradesh',
            'gstin' => $_POST['gstin'] ?? '',
            'hsn_code' => $_POST['hsn_code'] ?? '',
            'due_date' => $_POST['due_date'] ?? '',
            'discount_amount' => (float)($_POST['discount_amount'] ?? 0),
            'status' => $_POST['status'] ?? 'draft',
            'payment_terms' => $_POST['payment_terms'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $invoice = $this->service->getInvoice((int)$id);
            if (!$invoice) {
                redirect(BASE_URL . '/admin/invoices/manage?error=Invoice not found');
                exit;
            }

            $placeOfSupply = $data['place_of_supply'];
            $subtotal = (float)($invoice['subtotal'] ?? 0);
            $discountAmount = $data['discount_amount'];
            $taxableAmount = $subtotal - $discountAmount;
            if ($taxableAmount < 0) $taxableAmount = 0;
            $gst = $this->service->calculateGst($taxableAmount, $placeOfSupply);

            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $gst['tax_amount'];
            $data['gst_type'] = $gst['gst_type'];
            $data['gst_rate'] = $gst['gst_rate'];
            $data['cgst_amount'] = $gst['cgst_amount'];
            $data['sgst_amount'] = $gst['sgst_amount'];
            $data['igst_amount'] = $gst['igst_amount'];
            $data['total_amount'] = $taxableAmount + $gst['tax_amount'];

            $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = (int)$id;
            $pdo->prepare("UPDATE invoices SET {$set} WHERE id = ?")->execute($vals);

            if (!empty($_POST['item_name']) && is_array($_POST['item_name'])) {
                $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([(int)$id]);
                $count = count($_POST['item_name']);
                for ($i = 0; $i < $count; $i++) {
                    if (empty($_POST['item_name'][$i])) continue;
                    $this->service->addLineItem((int)$id, [
                        'item_type' => $_POST['item_type'][$i] ?? 'service',
                        'item_name' => $_POST['item_name'][$i] ?? '',
                        'item_description' => $_POST['item_description'][$i] ?? '',
                        'quantity' => $_POST['item_quantity'][$i] ?? 1,
                        'unit_price' => $_POST['item_unit_price'][$i] ?? 0,
                        'discount_percent' => $_POST['item_discount'][$i] ?? 0,
                        'tax_percent' => $_POST['item_tax'][$i] ?? 18,
                        'sort_order' => $i + 1,
                    ]);
                }
            }

            redirect(BASE_URL . '/admin/invoices/manage/' . $id . '?success=Invoice updated');
            exit;
        } catch (\Exception $e) {
            redirect(BASE_URL . '/admin/invoices/manage/' . $id . '/edit?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        try {
            $this->service->deleteInvoice((int)$id);
            redirect(BASE_URL . '/admin/invoices/manage?success=Invoice deleted');
            exit;
        } catch (\Exception $e) {
            redirect(BASE_URL . '/admin/invoices/manage?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function markAsPaid($id)
    {
        $this->requireAdmin();
        try {
            $this->service->markAsPaid((int)$id);
            $success = isset($_GET['redirect']) ? '' : '?success=Invoice marked as paid';
            redirect(BASE_URL . '/admin/invoices/manage/' . $id . $success);
            exit;
        } catch (\Exception $e) {
            redirect(BASE_URL . '/admin/invoices/manage?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function sendInvoice($id)
    {
        $this->requireAdmin();
        try {
            $this->service->markAsSent((int)$id);
            redirect(BASE_URL . '/admin/invoices/manage/' . $id . '?success=Invoice marked as sent');
            exit;
        } catch (\Exception $e) {
            redirect(BASE_URL . '/admin/invoices/manage?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function downloadPdf($id)
    {
        $this->requireAdmin();
        $invoice = $this->service->getInvoice((int)$id);
        if (!$invoice) {
            redirect(BASE_URL . '/admin/invoices/manage?error=Invoice not found');
            exit;
        }

        $company = $this->service->getCompanyDetails();
        $statusLabels = [
            'draft' => 'Draft', 'sent' => 'Sent', 'viewed' => 'Viewed',
            'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled',
        ];
        $invoice['status_label'] = $statusLabels[$invoice['status'] ?? ''] ?? ucfirst($invoice['status']);

        ob_start();
        extract(['invoice' => $invoice, 'company' => $company]);
        require __DIR__ . '/../../../views/admin/invoices/pdf.php';
        $html = ob_get_clean();

        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . ($invoice['invoice_number'] ?? 'invoice') . '.html"');
        echo $html;
        exit;
    }

    public function createFromBooking($bookingId)
    {
        $this->requireAdmin();
        try {
            $invoiceId = $this->service->createFromBooking((int)$bookingId);
            redirect(BASE_URL . '/admin/invoices/manage/' . $invoiceId . '?success=Invoice generated from booking');
            exit;
        } catch (\Exception $e) {
            redirect(BASE_URL . '/admin/invoices/manage?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
