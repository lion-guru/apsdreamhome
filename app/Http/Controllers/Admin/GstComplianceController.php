<?php
namespace App\Http\Controllers\Admin;

use App\Services\GstComplianceService;
use App\Http\Controllers\AdminController;
use App\Traits\TenantAwareTrait;

class GstComplianceController extends AdminController
{
    use TenantAwareTrait;

    private GstComplianceService $gstService;

    public function __construct()
    {
        parent::__construct();
        $this->gstService = new GstComplianceService();
    }

    public function dashboard()
    {
        $this->requireAdmin();

        $currentPeriod = date('Y-m');
        $prevPeriod = date('Y-m', strtotime('-1 month'));

        $stats = [
            'current_period' => $currentPeriod,
            'total_invoices' => 0,
            'b2b_invoices' => 0,
            'b2c_invoices' => 0,
            'total_taxable' => 0,
            'total_tax' => 0,
            'pending_irn' => 0,
            'failed_irn' => 0,
            'gstr1_status' => 'not_filed',
            'gstr3b_status' => 'not_filed',
        ];

        $invoices = $this->gstService->getInvoiceList(['from_date' => date('Y-m-01', strtotime($currentPeriod)), 'to_date' => date('Y-m-t', strtotime($currentPeriod))]);
        foreach ($invoices as $inv) {
            $stats['total_invoices']++;
            $stats['total_taxable'] += (float)$inv['taxable_value'];
            $stats['total_tax'] += (float)$inv['total_tax'];
            if ($inv['invoice_type'] === 'B2B') $stats['b2b_invoices']++;
            if ($inv['invoice_type'] === 'B2C') $stats['b2c_invoices']++;
            if ($inv['einvoice_status'] === 'pending') $stats['pending_irn']++;
            if ($inv['einvoice_status'] === 'failed') $stats['failed_irn']++;
        }

        $gstr1 = $this->db->fetchOne("SELECT * FROM gst_returns_filing_log WHERE tenant_id = ? AND return_type = 'GSTR1' AND return_period = ?", [$this->tenantId(), $currentPeriod]);
        $gstr3b = $this->db->fetchOne("SELECT * FROM gst_returns_filing_log WHERE tenant_id = ? AND return_type = 'GSTR3B' AND return_period = ?", [$this->tenantId(), $currentPeriod]);
        if ($gstr1) $stats['gstr1_status'] = $gstr1['status'];
        if ($gstr3b) $stats['gstr3b_status'] = $gstr3b['status'];

        $recentInvoices = array_slice($invoices, 0, 10);
        $hsnCodes = $this->gstService->getHsnCodes(20);

        $this->render('admin/gst/dashboard', [
            'page_title' => 'GST Compliance Dashboard',
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'hsnCodes' => $hsnCodes,
            'currentPeriod' => $currentPeriod,
        ]);
    }

    public function invoices()
    {
        $this->requireAdmin();

        $filters = [
            'from_date' => $_GET['from_date'] ?? date('Y-m-01'),
            'to_date' => $_GET['to_date'] ?? date('Y-m-t'),
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'gstin' => $_GET['gstin'] ?? '',
            'irn_status' => $_GET['irn_status'] ?? '',
        ];

        $invoices = $this->gstService->getInvoiceList($filters);
        $invoiceTypes = ['B2B', 'B2C', 'B2CL', 'EXP', 'SEZ', 'DE'];
        $irnStatuses = ['not_applicable', 'pending', 'generated', 'failed', 'cancelled'];

        $this->render('admin/gst/invoices', [
            'page_title' => 'GST Invoices',
            'invoices' => $invoices,
            'filters' => $filters,
            'invoiceTypes' => $invoiceTypes,
            'irnStatuses' => $irnStatuses,
        ]);
    }

    public function invoiceCreate()
    {
        $this->requireAdmin();

        $hsnCodes = $this->gstService->getHsnCodes(200);
        $customers = $this->db->fetchAll("SELECT id, name, phone, email, address, city as state_code FROM users WHERE tenant_id = ? AND role = 'customer' ORDER BY name", [$this->tenantId()]);
        $plots = $this->db->fetchAll("SELECT id, plot_number, colony_id FROM plots WHERE tenant_id = ? ORDER BY plot_number", [$this->tenantId()]);

        $this->render('admin/gst/invoice_form', [
            'page_title' => 'Create GST Invoice',
            'mode' => 'create',
            'hsnCodes' => $hsnCodes,
            'customers' => $customers,
            'plots' => $plots,
            'invoiceTypes' => ['B2B', 'B2C', 'B2CL', 'EXP', 'SEZ', 'DE'],
            'data' => [
                'invoice_date' => date('Y-m-d'),
                'invoice_type' => 'B2B',
                'supply_type' => 'intra_state',
                'place_of_supply' => '',
                'reverse_charge' => 0,
            ],
        ]);
    }

    public function invoiceStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $items = [];
        $itemCount = (int)($_POST['item_count'] ?? 0);
        for ($i = 0; $i < $itemCount; $i++) {
            if (!empty($_POST["item_hsn_$i"])) {
                $items[] = [
                    'hsn_code' => $_POST["item_hsn_$i"],
                    'description' => $_POST["item_desc_$i"],
                    'uom' => $_POST["item_uom_$i"] ?? 'PCS',
                    'quantity' => (float)($_POST["item_qty_$i"] ?? 1),
                    'rate' => (float)($_POST["item_rate_$i"] ?? 0),
                    'discount_amount' => (float)($_POST["item_discount_$i"] ?? 0),
                    'gst_rate' => (float)($_POST["item_gst_$i"] ?? 0),
                    'cess_rate' => (float)($_POST["item_cess_$i"] ?? 0),
                ];
            }
        }

        if (empty($items)) {
            $this->setFlash('error', 'At least one invoice item is required');
            return $this->redirect('/admin/gst/invoices/create');
        }

        $data = [
            'invoice_number' => trim($_POST['invoice_number'] ?? ''),
            'invoice_date' => $_POST['invoice_date'] ?? date('Y-m-d'),
            'invoice_type' => $_POST['invoice_type'] ?? 'B2B',
            'supply_type' => $_POST['supply_type'] ?? 'intra_state',
            'place_of_supply' => $_POST['place_of_supply'] ?? '',
            'reverse_charge' => isset($_POST['reverse_charge']) ? 1 : 0,
            'customer_gstin' => trim($_POST['customer_gstin'] ?? ''),
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'customer_address' => trim($_POST['customer_address'] ?? ''),
            'customer_state_code' => $_POST['customer_state_code'] ?? '',
            'customer_pincode' => $_POST['customer_pincode'] ?? '',
            'booking_id' => (int)($_POST['booking_id'] ?? 0) ?: null,
            'plot_id' => (int)($_POST['plot_id'] ?? 0) ?: null,
            'notes' => trim($_POST['notes'] ?? ''),
            'items' => $items,
        ];

        if (empty($data['invoice_number'])) {
            $this->setFlash('error', 'Invoice number is required');
            return $this->redirect('/admin/gst/invoices/create');
        }
        if (empty($data['customer_gstin']) && $data['invoice_type'] === 'B2B') {
            $this->setFlash('error', 'Customer GSTIN is required for B2B invoices');
            return $this->redirect('/admin/gst/invoices/create');
        }

        try {
            $invoiceId = $this->gstService->createInvoice($data);
            $this->setFlash('success', "GST Invoice created successfully (ID: $invoiceId)");
            return $this->redirect('/admin/gst/invoices');
        } catch (\Throwable $e) {
            error_log('GstComplianceController::invoiceStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create invoice: ' . $e->getMessage());
            return $this->redirect('/admin/gst/invoices/create');
        }
    }

    public function invoiceView($id)
    {
        $this->requireAdmin();

        $invoice = $this->gstService->getInvoice((int)$id);
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            return $this->redirect('/admin/gst/invoices');
        }

        $this->render('admin/gst/invoice_view', [
            'page_title' => "Invoice #{$invoice['invoice_number']}",
            'invoice' => $invoice,
        ]);
    }

    public function invoiceEInvoice($id)
    {
        $this->requireAdmin();

        $irn = $this->gstService->generateIrn((int)$id);
        if ($irn) {
            $this->setFlash('success', "IRN generated: $irn");
        } else {
            $validation = $this->gstService->validateInvoiceForEInvoicing((int)$id);
            $this->setFlash('error', 'E-invoicing failed: ' . implode(', ', $validation['errors']));
        }
        return $this->redirect("/admin/gst/invoices/view/$id");
    }

    public function gstr1()
    {
        $this->requireAdmin();

        $period = $_GET['period'] ?? date('Y-m');
        $data = $this->gstService->prepareGstr1Data($period);

        $this->render('admin/gst/gstr1', [
            'page_title' => "GSTR-1 Return - $period",
            'period' => $period,
            'data' => $data,
        ]);
    }

    public function gstr1Save()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $period = $_POST['period'] ?? date('Y-m');
        $this->gstService->saveGstr1Summary($period, $_POST['data'] ?? []);
        $this->setFlash('success', "GSTR-1 data saved for $period");
        return $this->redirect("/admin/gst/gstr1?period=$period");
    }

    public function gstr3b()
    {
        $this->requireAdmin();

        $period = $_GET['period'] ?? date('Y-m');
        $data = $this->gstService->prepareGstr3bData($period);

        $saved = $this->db->fetchAll("SELECT * FROM gst_gstr3b_summary WHERE tenant_id = ? AND return_period = ?", [$this->tenantId(), $period]);
        $savedData = [];
        foreach ($saved as $s) $savedData[$s['table_section']] = $s;

        $this->render('admin/gst/gstr3b', [
            'page_title' => "GSTR-3B Return - $period",
            'period' => $period,
            'data' => $data,
            'saved' => $savedData,
        ]);
    }

    public function gstr3bSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $period = $_POST['period'] ?? date('Y-m');
        $this->gstService->saveGstr3bSummary($period, $_POST['data'] ?? []);
        $this->setFlash('success', "GSTR-3B data saved for $period");
        return $this->redirect("/admin/gst/gstr3b?period=$period");
    }

    public function hsnCodes()
    {
        $this->requireAdmin();

        $codes = $this->gstService->getHsnCodes(500);

        $this->render('admin/gst/hsn_codes', [
            'page_title' => 'HSN/SAC Master',
            'codes' => $codes,
        ]);
    }

    public function hsnCodeStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'hsn_code' => trim($_POST['hsn_code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'gst_rate' => (float)($_POST['gst_rate'] ?? 0),
            'cgst_rate' => (float)($_POST['cgst_rate'] ?? 0),
            'sgst_rate' => (float)($_POST['sgst_rate'] ?? 0),
            'igst_rate' => (float)($_POST['igst_rate'] ?? 0),
            'cess_rate' => (float)($_POST['cess_rate'] ?? 0),
            'unit' => trim($_POST['unit'] ?? ''),
            'is_service' => isset($_POST['is_service']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by' => $_SESSION['admin_id'] ?? 1,
        ];

        if (empty($data['hsn_code']) || empty($data['description'])) {
            $this->setFlash('error', 'HSN code and description are required');
            return $this->redirect('/admin/gst/hsn-codes');
        }

        try {
            $this->gstService->createHsnCode($data);
            $this->setFlash('success', 'HSN code created');
        } catch (\Throwable $e) {
            error_log('GstComplianceController::hsnCodeStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create HSN code: ' . $e->getMessage());
        }
        return $this->redirect('/admin/gst/hsn-codes');
    }

    public function hsnCodeUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'description' => trim($_POST['description'] ?? ''),
            'gst_rate' => (float)($_POST['gst_rate'] ?? 0),
            'cgst_rate' => (float)($_POST['cgst_rate'] ?? 0),
            'sgst_rate' => (float)($_POST['sgst_rate'] ?? 0),
            'igst_rate' => (float)($_POST['igst_rate'] ?? 0),
            'cess_rate' => (float)($_POST['cess_rate'] ?? 0),
            'unit' => trim($_POST['unit'] ?? ''),
            'is_service' => isset($_POST['is_service']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        try {
            $this->gstService->updateHsnCode((int)$id, $data);
            $this->setFlash('success', 'HSN code updated');
        } catch (\Throwable $e) {
            error_log('GstComplianceController::hsnCodeUpdate: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to update HSN code: ' . $e->getMessage());
        }
        return $this->redirect('/admin/gst/hsn-codes');
    }

    public function returnsLog()
    {
        $this->requireAdmin();

        $logs = $this->db->fetchAll("
            SELECT * FROM gst_returns_filing_log 
            WHERE tenant_id = ? 
            ORDER BY return_period DESC, return_type
            LIMIT 100
        ", [$this->tenantId()]);

        $this->render('admin/gst/returns_log', [
            'page_title' => 'GST Returns Filing Log',
            'logs' => $logs,
        ]);
    }

    public function einvoiceLog()
    {
        $this->requireAdmin();

        $logs = $this->db->fetchAll("
            SELECT gel.*, gi.invoice_number, gi.invoice_date
            FROM gst_einvoicing_log gel
            JOIN gst_invoices gi ON gi.id = gel.invoice_id
            WHERE gel.tenant_id = ?
            ORDER BY gel.created_at DESC
            LIMIT 100
        ", [$this->tenantId()]);

        $this->render('admin/gst/einvoice_log', [
            'page_title' => 'E-Invoicing Log',
            'logs' => $logs,
        ]);
    }
}