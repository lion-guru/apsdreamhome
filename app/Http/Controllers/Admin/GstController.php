<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Traits\TenantAwareTrait;

class GstController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, u.name as user_name
                FROM invoices i
                LEFT JOIN users u ON i.user_id = u.id
                WHERE i.gst_type IS NOT NULL OR i.gstin IS NOT NULL
                ORDER BY i.created_at DESC
                LIMIT 100
            ");
            $stmt->execute();
            $invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $invoices = [];
        }
        return $this->render('admin/gst/index', [
            'page_title' => 'GST Invoices',
            'invoices' => $invoices
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM invoices i
                LEFT JOIN users u ON i.user_id = u.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $invoice = null;
        }
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('/admin/gst');
        }
        return $this->render('admin/gst/show', [
            'page_title' => 'GST Invoice: ' . ($invoice['invoice_number'] ?? ''),
            'invoice' => $invoice
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $custStmt = $this->db->prepare("SELECT id, name, email, phone, gstin FROM users WHERE role = 'customer'{$tidSql} ORDER BY name ASC LIMIT 200");
            $custStmt->execute($tidParams);
            $users = $custStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = [];
        }
        return $this->render('admin/gst/create', [
            'page_title' => 'Create GST Invoice',
            'users' => $users
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $user_id = $_POST['user_id'] ?? 0;
        $invoice_number = $_POST['invoice_number'] ?? 'INV-' . strtoupper(uniqid());
        $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
        $due_date = $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $client_name = $_POST['client_name'] ?? '';
        $client_gstin = $_POST['client_gstin'] ?? '';
        $total_amount = $_POST['total_amount'] ?? 0;
        $gst_type = $_POST['gst_type'] ?? 'cgst_sgst';
        $gst_rate = $_POST['gst_rate'] ?? 18;
        $cgst_amount = $_POST['cgst_amount'] ?? 0;
        $sgst_amount = $_POST['sgst_amount'] ?? 0;
        $igst_amount = $_POST['igst_amount'] ?? 0;
        $gstin = $_POST['gstin'] ?? '';
        $hsn_code = $_POST['hsn_code'] ?? '';
        $place_of_supply = $_POST['place_of_supply'] ?? '';
        $e_invoice_number = $_POST['e_invoice_number'] ?? '';
        $e_way_bill = $_POST['e_way_bill'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO invoices (user_id, invoice_number, invoice_date, due_date, client_name, gstin, total_amount, gst_type, gst_rate, cgst_amount, sgst_amount, igst_amount, hsn_code, place_of_supply, e_invoice_number, e_way_bill, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $invoice_number, $invoice_date, $due_date, $client_name, $client_gstin, $total_amount, $gst_type, $gst_rate, $cgst_amount, $sgst_amount, $igst_amount, $hsn_code, $place_of_supply, $e_invoice_number, $e_way_bill, $status, $tid]);
            $this->setFlash('success', 'GST invoice created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create GST invoice: ' . $e->getMessage());
        }
        $this->redirect('/admin/gst');
    }
}
