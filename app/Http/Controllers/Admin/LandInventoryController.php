<?php

namespace App\Http\Controllers\Admin;

use App\Services\Land\LandAcquisitionService;
use \App\Traits\TenantAwareTrait;
use Exception;

/**
 * Land Inventory Admin Controller
 *
 * Admin UI for the full land acquisition + plot inventory module:
 *   - Land leads pipeline (new → screening → visit → DD → negotiation → legal → agreement → registered)
 *   - Site visits, documents, legal opinions (per lead)
 *   - Closed deals (land_deals), registration, mutation
 *   - Payment ledger (advance, balance, stamp duty, reg fee, mutation, broker commission)
 *   - Colony development costs, layouts, broker master
 */
class LandInventoryController extends AdminController
{
    use TenantAwareTrait;

    protected $db;
    private $service;
    private $uploadPath;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \App\Core\Database\Database::getInstance();
        } catch (\Exception $e) {
            $this->db = null;
        }
        try {
            $this->service = new LandAcquisitionService();
        } catch (\Exception $e) {
            error_log("LandInventoryController: service failed: " . $e->getMessage());
            $this->service = null;
        }

        // File upload path for land documents
        $this->uploadPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/../../../public', '/\\')
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'land-docs';
        if (!is_dir($this->uploadPath)) {
            @mkdir($this->uploadPath, 0755, true);
        }
    }

    // ============================================================
    //  LEADS
    // ============================================================

    public function leads()
    {
        $this->requireAdmin();
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'district' => $_GET['district'] ?? '',
            'source'   => $_GET['source'] ?? '',
        ];
        $result = $this->service->listLeads($filters);
        $this->render('admin/land-inventory/leads', [
            'page_title'   => 'Land Leads',
            'page_heading' => 'Land Acquisition — Leads Pipeline',
            'leads'        => $result['data'] ?? [],
            'filters'      => $filters,
            'statuses'     => ['new','screening','visit_done','dd','negotiation','legal','sale_agreement','registered','rejected','dropped'],
            'sources'      => ['broker','scout','direct','referral','web','phone'],
        ]);
    }

    public function leadDetail($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $lead = $this->service->fetchLead($id);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/land-inventory/leads');
        }

        $docs    = $this->service->listDocuments($id);
        $visits  = $this->service->getVisitHistory($id);
        $opinions= $this->service->listOpinions($id);

        // Find any linked deal
        $deal = null;
        $ledger = ['data' => [], 'summary' => ['total_amount' => 0, 'cleared_amount' => 0]];
        try {
            $deal = $this->db->fetch("SELECT * FROM land_deals WHERE land_lead_id = ?", [$id]);
            if ($deal) {
                $ledger = $this->service->getAcquisitionLedger((int)$deal['id']);
            }
        } catch (\Exception $e) { error_log('LandInventoryController::leadDetail error: ' . $e->getMessage()); }

        $brokers = $this->service->listBrokers();
        $this->render('admin/land-inventory/lead-detail', [
            'page_title'   => 'Lead #' . $id,
            'page_heading' => 'Lead — ' . htmlspecialchars($lead['land_owner_name'] ?? ''),
            'lead'         => $lead,
            'documents'    => $docs['data'] ?? [],
            'visits'       => $visits['data'] ?? [],
            'opinions'     => $opinions['data'] ?? [],
            'deal'         => $deal,
            'ledger'       => $ledger,
            'brokers'      => $brokers['data'] ?? [],
        ]);
    }

    public function leadCreate()
    {
        $this->requireAdmin();
        $brokers = $this->service->listBrokers();
        $this->render('admin/land-inventory/lead-form', [
            'page_title'   => 'New Land Lead',
            'page_heading' => 'Create Land Lead',
            'lead'         => null,
            'brokers'      => $brokers['data'] ?? [],
            'mode'         => 'create',
        ]);
    }

    /**
     * Unified form handler — handles both "new" (no id) and "edit" routes.
     * Called by /admin/land-inventory/leads/new and /admin/land-inventory/leads/{id}/edit.
     */
    public function leadForm($id = null)
    {
        $this->requireAdmin();
        if ($id !== null) {
            return $this->leadEdit((int)$id);
        }
        return $this->leadCreate();
    }

    public function leadStore()
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->createLead($_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Land lead created (#' . $r['id'] . ')');
            return $this->redirect('/admin/land-inventory/leads/' . $r['id']);
        }
        $this->setFlash('error', 'Failed to create lead: ' . ($r['error'] ?? 'unknown'));
        return $this->redirect('/admin/land-inventory/leads/create');
    }

    public function leadEdit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $lead = $this->service->fetchLead($id);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/land-inventory/leads');
        }
        $brokers = $this->service->listBrokers();
        $this->render('admin/land-inventory/lead-form', [
            'page_title'   => 'Edit Lead #' . $id,
            'page_heading' => 'Edit Land Lead',
            'lead'         => $lead,
            'brokers'      => $brokers['data'] ?? [],
            'mode'         => 'edit',
        ]);
    }

    public function leadUpdate($id)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->updateLead((int)$id, $_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Lead updated');
            return $this->redirect('/admin/land-inventory/leads/' . $id);
        }
        $this->setFlash('error', 'Update failed: ' . ($r['error'] ?? 'unknown'));
        return $this->redirect('/admin/land-inventory/leads/' . $id . '/edit');
    }

    public function leadAdvance($id)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $newStatus = $_POST['new_status'] ?? '';
        $r = $this->service->advanceLead((int)$id, $newStatus);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Lead advanced to ' . $r['new_status']);
        } else {
            $this->setFlash('error', $r['error'] ?? 'Failed to advance lead');
        }
        return $this->redirect('/admin/land-inventory/leads/' . $id);
    }

    // ============================================================
    //  SITE VISITS
    // ============================================================

    public function visits($leadId)
    {
        $this->requireAdmin();
        $leadId = (int)$leadId;
        $lead = $this->service->fetchLead($leadId);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/land-inventory/leads');
        }
        $visits = $this->service->getVisitHistory($leadId);
        $this->render('admin/land-inventory/visits', [
            'page_title'   => 'Site Visits — Lead #' . $leadId,
            'page_heading' => 'Site Visit Log',
            'lead'         => $lead,
            'visits'       => $visits['data'] ?? [],
        ]);
    }

    public function visitStore($leadId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $data = $_POST;
        $data['visited_by'] = $_SESSION['user_id'] ?? ($_SESSION['admin_id'] ?? null);
        $r = $this->service->recordSiteVisit((int)$leadId, $data);

        // Auto-advance lead to visit_done on first visit
        try {
            $lead = $this->service->fetchLead((int)$leadId);
            if ($lead && ($lead['status'] ?? '') === 'new') {
                $this->service->advanceLead((int)$leadId, 'screening');
            }
            if ($lead && ($lead['status'] ?? '') === 'screening') {
                $this->service->advanceLead((int)$leadId, 'visit_done');
            }
        } catch (\Exception $e) { error_log('LandInventoryController::visitStore error: ' . $e->getMessage()); }

        if (!empty($r['success'])) {
            $this->setFlash('success', 'Site visit recorded');
        } else {
            $this->setFlash('error', 'Failed to record visit: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/leads/' . $leadId . '/visits');
    }

    // ============================================================
    //  DOCUMENTS
    // ============================================================

    public function documents($leadId)
    {
        $this->requireAdmin();
        $leadId = (int)$leadId;
        $lead = $this->service->fetchLead($leadId);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/land-inventory/leads');
        }
        $docs = $this->service->listDocuments($leadId);
        $this->render('admin/land-inventory/documents', [
            'page_title'   => 'Documents — Lead #' . $leadId,
            'page_heading' => 'Document Checklist (DD)',
            'lead'         => $lead,
            'documents'    => $docs['data'] ?? [],
        ]);
    }

    public function documentUpload($leadId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();

        $data = $_POST;
        $data['uploaded_by'] = $_SESSION['user_id'] ?? ($_SESSION['admin_id'] ?? null);

        // Handle file upload with validation
        $filePath = null;
        if (!empty($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
            $validation = \UploadValidator::validate($_FILES['document_file'], ['types' => 'documents', 'max_size' => 25]);
            if ($validation['valid']) {
                $leadDir = $this->uploadPath . DIRECTORY_SEPARATOR . $leadId;
                if (!is_dir($leadDir)) {
                    @mkdir($leadDir, 0755, true);
                }
                $target = $leadDir . DIRECTORY_SEPARATOR . $validation['sanitized_name'];
                if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target)) {
                    $filePath = 'assets/uploads/land-docs/' . $leadId . '/' . $validation['sanitized_name'];
                }
            } else {
                $data['file_path'] = null;
                $r = ['success' => false, 'error' => 'Upload rejected: ' . $validation['error']];
                $this->setFlash('error', 'Upload rejected: ' . $validation['error']);
                return $this->redirect('/admin/land-inventory/leads/' . $leadId . '/documents');
            }
        }
        $data['file_path'] = $filePath;
        $r = $this->service->addDocument((int)$leadId, $data);

        if (!empty($r['success'])) {
            $this->setFlash('success', 'Document uploaded');
        } else {
            $this->setFlash('error', 'Upload failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/leads/' . $leadId . '/documents');
    }

    // ============================================================
    //  LEGAL OPINIONS
    // ============================================================

    public function opinions($leadId)
    {
        $this->requireAdmin();
        $leadId = (int)$leadId;
        $lead = $this->service->fetchLead($leadId);
        if (!$lead) {
            $this->setFlash('error', 'Lead not found');
            return $this->redirect('/admin/land-inventory/leads');
        }
        $ops = $this->service->listOpinions($leadId);
        $this->render('admin/land-inventory/opinions', [
            'page_title'   => 'Legal Opinions — Lead #' . $leadId,
            'page_heading' => 'Title Clearance & Legal Opinions',
            'lead'         => $lead,
            'opinions'     => $ops['data'] ?? [],
        ]);
    }

    public function opinionStore($leadId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->recordLegalOpinion((int)$leadId, $_POST);

        // Auto-advance lead: any opinion moves dd→legal (or new→legal)
        if (!empty($r['success'])) {
            try {
                $lead = $this->service->fetchLead((int)$leadId);
                if ($lead) {
                    $st = $lead['status'] ?? 'new';
                    if ($st === 'visit_done') {
                        $this->service->advanceLead((int)$leadId, 'dd');
                    }
                    if (in_array($st, ['dd','visit_done'], true)) {
                        $this->service->advanceLead((int)$leadId, 'legal');
                    }
                }
            } catch (\Exception $e) { error_log('LandInventoryController::opinionStore error: ' . $e->getMessage()); }
            $this->setFlash('success', 'Legal opinion recorded');
        } else {
            $this->setFlash('error', 'Failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/leads/' . $leadId . '/opinions');
    }

    // ============================================================
    //  DEALS
    // ============================================================

    public function acquisitions()
    {
        $this->requireAdmin();
        try {
            $filters = ['status' => $_GET['status'] ?? ''];
            $result = $this->service->listDeals($filters);
            $this->render('admin/land-inventory/acquisitions', [
                'page_title'   => 'Land Deals',
                'page_heading' => 'Closed / In-Progress Land Deals',
                'deals'        => $result['data'] ?? [],
                'filters'      => $filters,
                'statuses'     => ['in_progress','registered','mutated','closed','cancelled'],
            ]);
        } catch (\Throwable $e) {
            error_log("LandInventory acquisitions error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->setFlash('error', 'Failed to load acquisitions: ' . $e->getMessage());
            return $this->render('admin/land-inventory/acquisitions', [
                'page_title'   => 'Land Acquisitions',
                'page_heading' => 'Land Acquisitions',
                'deals'        => [],
                'filters'      => [],
                'statuses'     => ['in_progress','registered','mutated','closed','cancelled'],
            ]);
        }
    }

    public function acquisitionDetail($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $deal = $this->service->fetchDeal($id);
        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            return $this->redirect('/admin/land-inventory/acquisitions');
        }
        $ledger = $this->service->getAcquisitionLedger($id);
        $colonies = $this->safeFetchAll("SELECT id, name FROM colonies ORDER BY name");
        $this->render('admin/land-inventory/acquisition-detail', [
            'page_title'   => 'Deal #' . $id,
            'page_heading' => 'Land Deal — Registration & Payments',
            'deal'         => $deal,
            'ledger'       => $ledger,
            'payments'     => $ledger['data'] ?? [],
            'colonies'     => $colonies,
        ]);
    }

    public function acquisitionRegister($id)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->registerAcquisition((int)$id, $_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Deal registered. '
                . ($r['auto_payments_created'] ?? 0)
                . ' auto payment record(s) created for stamp duty / reg fee.');
        } else {
            $this->setFlash('error', 'Registration failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/acquisitions/' . $id);
    }

    /**
     * Render the deal-registration form (GET). POST submissions are handled by
     * acquisitionRegister(). Pre-fills with the linked lead's owner + price.
     */
    public function registerForm($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $deal = $this->service->fetchDeal($id);
        $lead = null;
        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            return $this->redirect('/admin/land-inventory/acquisitions');
        }
        try {
            $lead = $this->db->fetch(
                "SELECT l.*, d.total_consideration AS negotiated_price, d.total_consideration AS final_price
                 FROM land_deals d JOIN land_leads l ON l.id = d.land_lead_id
                 WHERE d.id = ?",
                [$id]
            );
        } catch (\Exception $e) { error_log('LandInventoryController::registerForm error: ' . $e->getMessage()); }
        $this->render('admin/land-inventory/registration-form', [
            'page_title'   => 'Register — Deal #' . $id,
            'page_heading' => 'Register Property & Create Payment Plan',
            'deal'         => $deal,
            'lead'         => $lead,
        ]);
    }

    // ============================================================
    //  REGISTER LEAD — form POST handler for registration-form.php
    //  URL: POST /admin/land-inventory/leads/{leadId}/register
    // ============================================================

    /**
     * Handle registration form submission from lead context.
     * Creates a deal if none exists, then registers it with stamp duty / reg fee.
     */
    public function registerSubmit($leadId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $leadId = (int)$leadId;

        // Find existing deal for this lead, or create one
        try {
            $deal = $this->db->fetchOne("SELECT id FROM land_deals WHERE land_lead_id = ?", [$leadId]);

            if (!$deal) {
                $this->db->execute(
                    "INSERT INTO land_deals (land_lead_id, total_consideration, status, created_at)
                     VALUES (?, ?, 'in_progress', NOW())",
                    [
                        $leadId,
                        $this->decOrZero($_POST['final_price'] ?? 0),
                    ]
                );
                $dealId = (int)$this->db->lastInsertId();
            } else {
                $dealId = (int)$deal['id'];
                $this->db->execute(
                    "UPDATE land_deals SET total_consideration = ? WHERE id = ?",
                    [
                        $this->decOrZero($_POST['final_price'] ?? 0),
                        $dealId,
                    ]
                );
            }

            // Build registration data mapping form fields to what registerAcquisition expects
            $regData = [
                'registration_date'    => $_POST['registration_date'] ?? date('Y-m-d'),
                'registration_number'  => $_POST['sale_deed_number'] ?? '',
                'sub_registrar_office' => $_POST['registration_office'] ?? '',
                'stamp_duty_amount'    => $this->decOrZero($_POST['stamp_duty_amount'] ?? 0),
                'registration_fee'     => $this->decOrZero($_POST['registration_fee'] ?? 0),
                'payee_name'           => $_POST['payee_name'] ?? 'Land Owner',
            ];

            // Optional mutation / RERA fields
            $tid = (int)$this->tenantId();
            if (!empty($_POST['mutation_filed_date'])) {
                $this->db->execute("UPDATE land_deals SET mutation_date = ?, mutation_number = ? WHERE id = ? AND tenant_id = ?", [
                    $_POST['mutation_filed_date'], $_POST['mutation_number'] ?? '', $dealId, $tid,
                ]);
            }
            // rera_registration column does not exist; skipped

            $r = $this->service->registerAcquisition($dealId, $regData);

            if (!empty($r['success'])) {
                $this->setFlash('success', 'Property registered successfully (#D' . $dealId . '). '
                    . ($r['auto_payments_created'] ?? 0) . ' auto-payment record(s) created.');
            } else {
                $this->setFlash('error', 'Registration failed: ' . ($r['error'] ?? 'unknown'));
            }
        } catch (\Exception $e) {
            error_log('LandInventoryController::registerSubmit error: ' . $e->getMessage());
            $this->setFlash('error', 'Registration error: ' . $e->getMessage());
        }

        $this->redirect('/admin/land-inventory/leads/' . $leadId);
    }

    private function decOrZero($val): ?float
    {
        if ($val === '' || $val === null) return null;
        return (float)str_replace(',', '', $val);
    }

    // ============================================================
    //  PAYMENTS
    // ============================================================

    public function payments($dealId)
    {
        $this->requireAdmin();
        $dealId = (int)$dealId;
        $deal = $this->service->fetchDeal($dealId);
        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            return $this->redirect('/admin/land-inventory/acquisitions');
        }
        $ledger = $this->service->getAcquisitionLedger($dealId);
        $this->render('admin/land-inventory/payments', [
            'page_title'   => 'Payments — Deal #' . $dealId,
            'page_heading' => 'Payment Ledger',
            'deal'         => $deal,
            'payments'     => $ledger['data'] ?? [],
            'summary'      => $ledger['summary'] ?? ['total_amount'=>0,'cleared_amount'=>0,'pending_amount'=>0],
        ]);
    }

    public function paymentStore($dealId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->recordPayment((int)$dealId, $_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Payment recorded (#' . $r['id'] . ')');
        } else {
            $this->setFlash('error', 'Failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/acquisitions/' . $dealId . '/payments');
    }

    /**
     * Render the payment form. If $pid is null → new payment, else edit existing.
     */
    public function paymentForm($dealId, $pid = null)
    {
        $this->requireAdmin();
        $dealId = (int)$dealId;
        $deal = $this->service->fetchDeal($dealId);
        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            return $this->redirect('/admin/land-inventory/acquisitions');
        }
        $payment = [];
        if ($pid !== null) {
            $pid = (int)$pid;
            try {
                $payment = $this->db->fetch("SELECT * FROM land_deal_payments WHERE id = ?", [$pid]) ?: [];
            } catch (\Exception $e) { error_log('LandInventoryController::paymentForm error: ' . $e->getMessage()); }
        }
        $this->render('admin/land-inventory/payment-form', [
            'page_title'   => 'Payment — Deal #' . $dealId,
            'page_heading' => ($pid ? 'Edit' : 'Add') . ' Payment',
            'acquisition'  => $deal,
            'payment'      => $payment,
        ]);
    }

    /**
     * Update an existing payment record.
     */
    public function paymentUpdate($dealId, $pid)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->recordPayment((int)$dealId, $_POST, (int)$pid);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Payment updated');
        } else {
            $this->setFlash('error', 'Failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/acquisitions/' . $dealId . '/payments');
    }

    // ============================================================
    //  COLONY DEVELOPMENT COSTS
    // ============================================================

    public function developmentCosts($colonyId)
    {
        $this->requireAdmin();
        $colonyId = (int)$colonyId;
        $colony = $this->safeFetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/colonies');
        }
        $summary = $this->service->getColonyCostSummary($colonyId);
        $this->render('admin/land-inventory/development-costs', [
            'page_title'   => 'Development Costs — ' . ($colony['name'] ?? 'Colony'),
            'page_heading' => 'Colony Development Cost Tracker',
            'colony'       => $colony,
            'costs'        => $summary['data'] ?? [],
            'summary'      => $summary['summary'] ?? ['total_amount'=>0,'paid_amount'=>0,'balance_amount'=>0],
        ]);
    }

    public function developmentCostStore($colonyId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->addDevelopmentCost((int)$colonyId, $_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Cost added');
        } else {
            $this->setFlash('error', 'Failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/colonies/' . $colonyId . '/costs');
    }

    /**
     * Render the cost-addition form (re-uses the cost-list page UI).
     */
    public function developmentCostForm($colonyId)
    {
        $this->requireAdmin();
        $colonyId = (int)$colonyId;
        $colony = $this->safeFetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/colonies');
        }
        $summary = $this->service->getColonyCostSummary($colonyId);
        $this->render('admin/land-inventory/development-cost-form', [
            'page_title'   => 'Add Cost — ' . ($colony['name'] ?? 'Colony'),
            'page_heading' => 'Add Development Cost',
            'colony'       => $colony,
            'costs'        => $summary['data'] ?? [],
            'summary'      => $summary['summary'] ?? ['total_acquisition'=>0,'total_development'=>0,'total_land'=>0,'grand_total'=>0,'count'=>0],
        ]);
    }

    // ============================================================
    //  COLONY LAYOUTS
    // ============================================================

    public function layouts($colonyId)
    {
        $this->requireAdmin();
        $colonyId = (int)$colonyId;
        $colony = $this->safeFetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/colonies');
        }
        $result = $this->service->getLayouts($colonyId);
        $this->render('admin/land-inventory/layouts', [
            'page_title'   => 'Layouts — ' . ($colony['name'] ?? 'Colony'),
            'page_heading' => 'Colony Layouts & Subdivisions',
            'colony'       => $colony,
            'layouts'      => $result['data'] ?? [],
        ]);
    }

    public function layoutStore($colonyId)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->createLayout((int)$colonyId, $_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Layout created');
        } else {
            $this->setFlash('error', 'Failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/colonies/' . $colonyId . '/layouts');
    }

    /**
     * Render the new-layout form.
     */
    public function layoutForm($colonyId)
    {
        $this->requireAdmin();
        $colonyId = (int)$colonyId;
        $colony = $this->safeFetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/colonies');
        }
        $this->render('admin/land-inventory/layout-form', [
            'page_title'   => 'New Layout — ' . ($colony['name'] ?? 'Colony'),
            'page_heading' => 'Create New Layout Plan',
            'colony'       => $colony,
        ]);
    }

    // ============================================================
    //  BROKERS
    // ============================================================

    public function brokers()
    {
        $this->requireAdmin();
        $result = $this->service->listBrokers();
        $this->render('admin/land-inventory/brokers', [
            'page_title'   => 'Land Brokers',
            'page_heading' => 'Land Broker Master',
            'brokers'      => $result['data'] ?? [],
        ]);
    }

    public function brokerStore()
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $r = $this->service->createBroker($_POST);
        if (!empty($r['success'])) {
            $this->setFlash('success', 'Broker added');
        } else {
            $this->setFlash('error', 'Failed: ' . ($r['error'] ?? 'unknown'));
        }
        return $this->redirect('/admin/land-inventory/brokers');
    }

    // ============================================================
    //  Helpers
    // ============================================================

    /**
     * Inline CSRF check — if the global middleware is not in play, this still
     * protects the form-based POSTs. Skipped when CSRF token is missing from
     * the request to keep the controller usable from API clients.
     */
    private function verifyCsrfOrDie(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if ($token === '' || !isset($_SESSION['csrf_token'])) {
            throw new \Exception('CSRF token missing');
        }
        if (!hash_equals((string)$_SESSION['csrf_token'], (string)$token)) {
            throw new \Exception('Invalid CSRF token');
        }
    }

    private function safeFetchAll(string $sql, array $params = []): array
    {
        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function safeFetchOne(string $sql, array $params = []): ?array
    {
        try {
            $r = $this->db->fetchOne($sql, $params);
            return $r ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // ============================================================
    //  FARMER LAND BANK ACQUISITION LEDGER
    // ============================================================

    /**
     * Farmer Land Bank Acquisition Ledger
     * Aggregated view of all farmers/land owners with their deals, advances, balances
     */
    public function farmerLedger()
    {
        $this->requireAdmin();
        try {
            // Filters
            $filters = [
                'district' => $_GET['district'] ?? '',
                'status'   => $_GET['status'] ?? '',
                'search'   => $_GET['search'] ?? '',
            ];

            // Build query to get aggregated farmer/owner data
            $sql = "
                SELECT 
                    ll.land_owner_name as farmer_name,
                    ll.owner_phone as farmer_phone,
                    ll.owner_email as farmer_email,
                    ll.village,
                    ll.tehsil,
                    ll.district,
                    ll.state,
                    ll.pincode,
                    ll.survey_number,
                    SUM(ll.area_acres) as total_area_acres,
                    SUM(ll.area_sqft) as total_area_sqft,
                    COUNT(DISTINCT ll.id) as total_leads,
                    COUNT(DISTINCT ld.id) as total_deals,
                    COALESCE(SUM(ld.total_consideration), 0) as total_deal_value,
                    COALESCE(SUM(ld.advance_paid), 0) as total_advance_paid,
                    COALESCE(SUM(ld.balance_amount), 0) as total_balance,
                    MAX(ld.sale_agreement_date) as latest_agreement_date,
                    MAX(ld.registration_date) as latest_registration_date,
                    MAX(ld.mutation_date) as latest_mutation_date,
                    GROUP_CONCAT(DISTINCT ld.status) as deal_statuses,
                    GROUP_CONCAT(DISTINCT ll.status) as lead_statuses
                FROM land_leads ll
                LEFT JOIN land_deals ld ON ld.land_lead_id = ll.id
                WHERE 1=1
            ";

            $params = [];
            
            if (!empty($filters['district'])) {
                $sql .= " AND ll.district = ?";
                $params[] = $filters['district'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (ll.land_owner_name LIKE ? OR ll.owner_phone LIKE ? OR ll.village LIKE ? OR ll.survey_number LIKE ?)";
                $searchParam = '%' . $filters['search'] . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND (ld.status = ? OR ll.status = ?)";
                $params[] = $filters['status'];
                $params[] = $filters['status'];
            }

            $sql .= " GROUP BY ll.land_owner_name, ll.owner_phone, ll.village, ll.tehsil, ll.district, ll.state, ll.pincode, ll.survey_number
                      ORDER BY total_deal_value DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $farmers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get unique districts for filter dropdown
            $districts = $this->db->fetchAll("SELECT DISTINCT district FROM land_leads WHERE district IS NOT NULL AND district != '' ORDER BY district");

            // Get summary stats
            $summaryStmt = $this->db->query("
                SELECT 
                    COUNT(DISTINCT ll.land_owner_name) as total_farmers,
                    COUNT(DISTINCT ll.id) as total_leads,
                    COUNT(DISTINCT ld.id) as total_deals,
                    COALESCE(SUM(ld.total_consideration), 0) as total_deal_value,
                    COALESCE(SUM(ld.advance_paid), 0) as total_advance,
                    COALESCE(SUM(ld.balance_amount), 0) as total_balance,
                    COALESCE(SUM(ll.area_acres), 0) as total_acres
                FROM land_leads ll
                LEFT JOIN land_deals ld ON ld.land_lead_id = ll.id
            ");
            $summary = $summaryStmt->fetch(\PDO::FETCH_ASSOC);

            $this->render('admin/land-inventory/farmer-ledger', [
                'page_title'   => 'Farmer Land Bank Ledger',
                'page_heading' => 'Farmer / Land Owner Acquisition Ledger',
                'farmers'      => $farmers,
                'filters'      => $filters,
                'districts'    => $districts,
                'summary'      => $summary,
            ]);
        } catch (\Exception $e) {
            error_log("LandInventoryController::farmerLedger error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load farmer ledger: ' . $e->getMessage());
            $this->render('admin/land-inventory/farmer-ledger', [
                'page_title'   => 'Farmer Land Bank Ledger',
                'page_heading' => 'Farmer / Land Owner Acquisition Ledger',
                'farmers'      => [],
                'filters'      => [],
                'districts'    => [],
                'summary'      => [],
            ]);
        }
    }

    /**
     * Farmer Detail View - shows all leads and deals for a specific farmer
     */
    public function farmerDetail($farmerName, $village = '')
    {
        $this->requireAdmin();
        try {
            $sql = "
                SELECT ll.*, ld.*
                FROM land_leads ll
                LEFT JOIN land_deals ld ON ld.land_lead_id = ll.id
                WHERE ll.land_owner_name = ?
            ";
            $params = [$farmerName];
            
            if (!empty($village)) {
                $sql .= " AND ll.village = ?";
                $params[] = $village;
            }
            
            $sql .= " ORDER BY ll.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $this->setFlash('error', 'Farmer not found');
                return $this->redirect('/admin/land-inventory/farmer-ledger');
            }

            // Group by lead
            $leads = [];
            $deals = [];
            foreach ($rows as $row) {
                $leadId = $row['id'];
                if (!isset($leads[$leadId])) {
                    $leads[$leadId] = [
                        'id' => $row['id'],
                        'lead_source' => $row['lead_source'],
                        'broker_id' => $row['broker_id'],
                        'land_owner_name' => $row['land_owner_name'],
                        'owner_phone' => $row['owner_phone'],
                        'owner_email' => $row['owner_email'],
                        'village' => $row['village'],
                        'tehsil' => $row['tehsil'],
                        'district' => $row['district'],
                        'state' => $row['state'],
                        'pincode' => $row['pincode'],
                        'gps_lat' => $row['gps_lat'],
                        'gps_lng' => $row['gps_lng'],
                        'survey_number' => $row['survey_number'],
                        'area_acres' => $row['area_acres'],
                        'area_sqft' => $row['area_sqft'],
                        'expected_price' => $row['expected_price'],
                        'status' => $row['status'],
                        'assigned_to' => $row['assigned_to'],
                        'notes' => $row['notes'],
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at'],
                        'deals' => []
                    ];
                }
                if ($row['id'] !== $leadId && !empty($row['id'])) {
                    // This is deal data
                }
                if (!empty($row['id']) && !empty($row['total_consideration'])) {
                    $dealId = $row['id']; // This is deal id in the joined query
                    // Actually we need to be more careful with column names
                }
            }

            // Better approach: fetch leads and their deals separately
            $leadsStmt = $this->db->prepare("
                SELECT * FROM land_leads WHERE land_owner_name = ?" . ($village ? " AND village = ?" : "") . " ORDER BY created_at DESC
            ");
            $leadsParams = [$farmerName];
            if ($village) $leadsParams[] = $village;
            $leadsStmt->execute($leadsParams);
            $leads = $leadsStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch deals for these leads
            $leadIds = array_column($leads, 'id');
            $deals = [];
            if (!empty($leadIds)) {
                $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
                $dealsStmt = $this->db->prepare("
                    SELECT ld.*, ll.land_owner_name, ll.village
                    FROM land_deals ld
                    JOIN land_leads ll ON ll.id = ld.land_lead_id
                    WHERE ld.land_lead_id IN ($placeholders)
                    ORDER BY ld.created_at DESC
                ");
                $dealsStmt->execute($leadIds);
                $deals = $dealsStmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Get payments for these deals
            $dealIds = array_column($deals, 'id');
            $payments = [];
            if (!empty($dealIds)) {
                $placeholders = implode(',', array_fill(0, count($dealIds), '?'));
                $paymentsStmt = $this->db->prepare("
                    SELECT * FROM land_deal_payments WHERE deal_id IN ($placeholders) ORDER BY payment_date DESC
                ");
                $paymentsStmt->execute($dealIds);
                $payments = $paymentsStmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Group payments by deal
            $paymentsByDeal = [];
            foreach ($payments as $p) {
                $paymentsByDeal[$p['deal_id']][] = $p;
            }

            $this->render('admin/land-inventory/farmer-detail', [
                'page_title'   => 'Farmer Detail — ' . htmlspecialchars($farmerName),
                'page_heading' => 'Land Owner Detail',
                'farmer_name'  => $farmerName,
                'village'      => $village,
                'leads'        => $leads,
                'deals'        => $deals,
                'payments'     => $paymentsByDeal,
            ]);
        } catch (\Exception $e) {
            error_log("LandInventoryController::farmerDetail error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load farmer detail: ' . $e->getMessage());
            return $this->redirect('/admin/land-inventory/farmer-ledger');
        }
    }
}
