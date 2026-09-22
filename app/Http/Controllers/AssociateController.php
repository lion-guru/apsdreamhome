<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;

/**
 * AssociateController (Facade)
 * Delegates to focused sub-controllers
 */
class AssociateController extends BaseController
{
    use TenantAwareTrait;

    // Sub-controller instances
    private \App\Http\Controllers\Associate\AuthController $authController;
    private \App\Http\Controllers\Associate\DashboardController $dashboardController;
    private \App\Http\Controllers\Associate\PropertyController $propertyController;
    private \App\Http\Controllers\Associate\CrmController $crmController;
    private \App\Http\Controllers\Associate\CommissionController $commissionController;
    private \App\Http\Controllers\Associate\TeamController $teamController;
    private \App\Http\Controllers\Associate\DocumentController $documentController;
    private \App\Http\Controllers\Associate\BookingController $bookingController;
    private \App\Http\Controllers\Associate\SiteVisitController $siteVisitController;
    private \App\Http\Controllers\Associate\ProfileController $profileController;
    private \App\Http\Controllers\Associate\ToolController $toolController;
    private \App\Http\Controllers\Associate\ReferralController $referralController;
    private \App\Http\Controllers\Associate\ColonyController $colonyController;

    public function __construct()
    {
        parent::__construct();

        // Initialize sub-controllers
        $this->authController        = new \App\Http\Controllers\Associate\AuthController();
        $this->dashboardController   = new \App\Http\Controllers\Associate\DashboardController();
        $this->propertyController    = new \App\Http\Controllers\Associate\PropertyController();
        $this->crmController         = new \App\Http\Controllers\Associate\CrmController();
        $this->commissionController  = new \App\Http\Controllers\Associate\CommissionController();
        $this->teamController        = new \App\Http\Controllers\Associate\TeamController();
        $this->documentController    = new \App\Http\Controllers\Associate\DocumentController();
        $this->bookingController     = new \App\Http\Controllers\Associate\BookingController();
        $this->siteVisitController   = new \App\Http\Controllers\Associate\SiteVisitController();
        $this->profileController     = new \App\Http\Controllers\Associate\ProfileController();
        $this->toolController        = new \App\Http\Controllers\Associate\ToolController();
        $this->referralController    = new \App\Http\Controllers\Associate\ReferralController();
        $this->colonyController      = new \App\Http\Controllers\Associate\ColonyController();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /* ============================================================
       AUTH (delegates to AuthController)
       ============================================================ */

    public function register()
    {
        return $this->authController->register();
    }

    public function store()
    {
        return $this->authController->store();
    }

    /* ============================================================
       DASHBOARD (delegates to DashboardController)
       ============================================================ */

    public function dashboard()
    {
        return $this->dashboardController->dashboard();
    }

    /* ============================================================
       PROPERTIES (delegates to PropertyController)
       ============================================================ */

    public function addProperty()
    {
        return $this->propertyController->addProperty();
    }

    public function storeAddProperty()
    {
        return $this->propertyController->storeAddProperty();
    }

    public function properties()
    {
        return $this->propertyController->properties();
    }

    public function editProperty($id)
    {
        return $this->propertyController->editProperty($id);
    }

    public function updateProperty($id)
    {
        return $this->propertyController->updateProperty($id);
    }

    public function deleteProperty($id)
    {
        return $this->propertyController->deleteProperty($id);
    }

    public function browse()
    {
        return $this->propertyController->browse();
    }

    public function sold()
    {
        return $this->propertyController->sold();
    }

    public function pending()
    {
        return $this->propertyController->pending();
    }

    public function listProperty()
    {
        // Legacy alias for addProperty
        return $this->propertyController->addProperty();
    }

    public function submitProperty()
    {
        // Legacy alias for storeAddProperty
        return $this->propertyController->storeAddProperty();
    }

    /* ============================================================
       CRM / LEADS (delegates to CrmController)
       ============================================================ */

    public function crmDashboard()
    {
        return $this->crmController->crmDashboard();
    }

    public function leads()
    {
        return $this->crmController->leads();
    }

    public function addLead()
    {
        return $this->crmController->addLead();
    }

    public function storeLead()
    {
        return $this->crmController->storeLead();
    }

    public function leadDetail($id)
    {
        return $this->crmController->leadDetail($id);
    }

    public function updateLeadStatus($id)
    {
        return $this->crmController->updateLeadStatus($id);
    }

    public function addLeadNote($id)
    {
        return $this->crmController->addLeadNote($id);
    }

    public function deleteLead($id)
    {
        return $this->crmController->deleteLead($id);
    }

    public function followups()
    {
        return $this->crmController->followups();
    }

    public function updateFollowup($id)
    {
        return $this->crmController->updateFollowup($id);
    }

    /* ============================================================
       COMMISSIONS (delegates to CommissionController)
       ============================================================ */

    public function commissions()
    {
        return $this->commissionController->commissions();
    }

    public function commissionCalculator()
    {
        return $this->commissionController->commissionCalculator();
    }

    public function rankEligibility()
    {
        return $this->commissionController->rankEligibility();
    }

    /* ============================================================
       TEAM / MLM (delegates to TeamController)
       ============================================================ */

    public function team()
    {
        return $this->teamController->team();
    }

    public function mlmPlan()
    {
        return $this->teamController->mlmPlan();
    }

    /* ============================================================
       DOCUMENTS (delegates to DocumentController)
       ============================================================ */

    public function documents()
    {
        return $this->documentController->documents();
    }

    public function uploadDocument()
    {
        return $this->documentController->uploadDocument();
    }

    /* ============================================================
       BOOKINGS & CUSTOMERS (delegates to BookingController)
       ============================================================ */

    public function myBookings()
    {
        return $this->bookingController->myBookings();
    }

    public function myCustomers()
    {
        return $this->bookingController->myCustomers();
    }

    public function customerDetail($id)
    {
        return $this->bookingController->customerDetail($id);
    }

    public function emiTracker()
    {
        return $this->bookingController->emiTracker();
    }

    public function paymentHistory()
    {
        return $this->bookingController->paymentHistory();
    }

    public function bookingReceipt($id)
    {
        return $this->bookingController->bookingReceipt($id);
    }

    /* ============================================================
       SITE VISITS (delegates to SiteVisitController)
       ============================================================ */

    public function siteVisits()
    {
        return $this->siteVisitController->siteVisits();
    }

    public function schedule()
    {
        return $this->siteVisitController->schedule();
    }

    public function scheduleSiteVisit()
    {
        return $this->siteVisitController->scheduleSiteVisit();
    }

    public function completeSiteVisit($id)
    {
        return $this->siteVisitController->completeSiteVisit($id);
    }

    public function cancelSiteVisit($id)
    {
        return $this->siteVisitController->cancelSiteVisit($id);
    }

    public function rescheduleSiteVisit($id)
    {
        return $this->siteVisitController->rescheduleSiteVisit($id);
    }

    public function calendarData()
    {
        return $this->siteVisitController->calendarData();
    }

    /* ============================================================
       PROFILE & SETTINGS (delegates to ProfileController)
       ============================================================ */

    public function profile()
    {
        return $this->profileController->profile();
    }

    public function settings()
    {
        return $this->profileController->settings();
    }

    /* ============================================================
       TOOLS (delegates to ToolController)
       ============================================================ */

    public function tools()
    {
        return $this->toolController->tools();
    }

    public function emiCalculator()
    {
        return $this->toolController->emiCalculator();
    }

    public function stampDutyCalculator()
    {
        return $this->toolController->stampDutyCalculator();
    }

    public function plotConverter()
    {
        return $this->toolController->plotConverter();
    }

    /* ============================================================
       REFERRAL (delegates to ReferralController)
       ============================================================ */

    public function referral()
    {
        return $this->referralController->referral();
    }

    /* ============================================================
       COLONY MAP (delegates to ColonyController)
       ============================================================ */

    public function colonyMap($id)
    {
        return $this->colonyController->colonyMap($id);
    }

    /* ============================================================
       LEGACY METHODS - Kept for backward compatibility
       ============================================================ */

    public function bookPlot()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = (int)TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET: render the booking form
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = $tid > 1 ? [$tid] : [];
            $colonies = $db->fetchAll("SELECT * FROM colonies WHERE is_active = 1{$tidSql} ORDER BY name", $params);
            $plots = $db->fetchAll("SELECT p.*, c.name as colony_name FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE p.is_active = 1{$tidSql} ORDER BY p.plot_number", $params);
            $this->render('associate/book_plot', [
                'page_title' => 'Book Plot - Associate Portal',
                'colonies' => $colonies,
                'plots' => $plots,
            ], 'layouts/associate');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Validate CSRF
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (empty($csrfToken) || ($csrfToken !== ($_SESSION['csrf_token'] ?? ''))) {
                $_SESSION['flash_error'] = 'Invalid CSRF token';
                $this->redirect('/associate/book-plot');
                return;
            }

            // Validate terms_consent (Master Deed V8 compliance)
            $termsConsent = $_POST['terms_consent'] ?? '';
            if (empty($termsConsent)) {
                $_SESSION['flash_error'] = 'You must accept the Master Agreement Terms, Cancellation Policy, and Refund Policy to proceed.';
                $this->redirect('/associate/book-plot');
                return;
            }

            // Validate master_deed_accepted
            $masterDeedAccepted = $_POST['master_deed_accepted'] ?? '';
            if (empty($masterDeedAccepted)) {
                $_SESSION['flash_error'] = 'You must accept the Master Deed & Agreement to proceed.';
                $this->redirect('/associate/book-plot');
                return;
            }

            // Handle Master Deed upload
            $masterDeedPath = '';
            if (isset($_FILES['master_deed_upload']) && $_FILES['master_deed_upload']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $tmpPath = $_FILES['master_deed_upload']['tmp_name'];
                $origName = basename($_FILES['master_deed_upload']['name']);
                $fileExt = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $mime = mime_content_type($tmpPath);
                if (in_array($mime, $allowedTypes) && $_FILES['master_deed_upload']['size'] <= $maxSize) {
                    $uploadDir = 'assets/documents/master-deeds/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $safeName = 'deed_' . time() . '_' . uniqid() . '.' . $fileExt;
                    if (move_uploaded_file($tmpPath, $uploadDir . $safeName)) {
                        $masterDeedPath = $uploadDir . $safeName;
                    }
                }
            }

            // Validate required fields
            $plotId = (int)($_POST['plot_id'] ?? 0);

            // Read additional form fields for booking notes
            $masterDeedAcceptedStr = $masterDeedAccepted ? 'Master Deed accepted.' : '';
            $colonyId = (int)($_POST['colony_id'] ?? 0);
            $customerName = trim($_POST['customer_name'] ?? '');
            $customerPhone = trim($_POST['customer_phone'] ?? '');
            $customerEmail = trim($_POST['customer_email'] ?? '');
            $customerAddress = trim($_POST['customer_address'] ?? '');
            $bookingAmount = (float)($_POST['booking_amount'] ?? 0);
            $paymentMode = trim($_POST['payment_mode'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $aadharNumber = trim($_POST['aadhar_number'] ?? '');
            $panNumber = trim($_POST['pan_number'] ?? '');

            if ($plotId <= 0 || $colonyId <= 0) {
                $_SESSION['flash_error'] = 'Please select a plot.';
                $this->redirect('/associate/book-plot');
                return;
            }
            if (empty($customerName) || empty($customerPhone)) {
                $_SESSION['flash_error'] = 'Customer name and phone are required.';
                $this->redirect('/associate/book-plot');
                return;
            }
            if ($bookingAmount <= 0) {
                $_SESSION['flash_error'] = 'Booking amount must be greater than zero.';
                $this->redirect('/associate/book-plot');
                return;
            }
            if (empty($paymentMode)) {
                $_SESSION['flash_error'] = 'Payment mode is required.';
                $this->redirect('/associate/book-plot');
                return;
            }

            // Check plot availability
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $plotParams = [$plotId];
            if ($tid > 1) $plotParams[] = $tid;
            $plot = $db->fetchRow("SELECT p.*, c.name as colony_name FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE p.id = ? AND p.is_active = 1 AND p.status = 'available'{$tidSql}", $plotParams);
            if (!$plot) {
                $_SESSION['flash_error'] = 'Plot is no longer available.';
                $this->redirect('/associate/book-plot');
                return;
            }

            // Generate booking number
            $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $db->beginTransaction();
            try {
                // Insert into plot_bookings
                $bookingId = $db->insert('plot_bookings', [
                    'tenant_id' => $tid,
                    'plot_id' => $plotId,
                    'colony_id' => $colonyId,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_email' => $customerEmail,
                    'customer_address' => $customerAddress,
                    'booking_number' => $bookingNumber,
                    'booking_date' => date('Y-m-d'),
                    'total_plot_value' => (float)($plot['total_price'] ?? $bookingAmount),
                    'booking_amount' => $bookingAmount,
                    'agreement_value' => (float)($plot['total_price'] ?? $bookingAmount),
                    'status' => 'pending',
                    'approval_status' => 'pending',
                    'associate_id' => $userId,
                    'channel' => 'associate_booking',
                    'notes' => 'Master Deed: ' . ($masterDeedAcceptedStr ? 'Accepted' : 'N/A') . '. Deed file: ' . ($masterDeedPath ?: 'N/A') . '. Terms accepted. Aadhar: ' . $aadharNumber . ' | PAN: ' . $panNumber . ' | ' . $notes,
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                // Update plot status to booked
                $db->update('plots', ['status' => 'booked', 'updated_at' => date('Y-m-d H:i:s')], "id = ?{$tidSql}", array_merge([$plotId], $tid > 1 ? [$tid] : []));

                $db->commit();

                // Send booking confirmation notification to customer (SMS + WhatsApp + Email)
                try {
                    $notifier = new \App\Services\BookingNotificationService();
                    $colony = $db->fetch("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
                    $notifier->sendBookingConfirmation(
                        [
                            'booking_number' => $bookingNumber,
                            'total_plot_value' => (float)($plot['total_price'] ?? $bookingAmount),
                            'booking_amount' => $bookingAmount,
                        ],
                        [
                            'id' => 0,
                            'name' => $customerName,
                            'phone' => $customerPhone,
                            'email' => $customerEmail,
                        ],
                        $plot,
                        $colony ?: ['name' => $plot['colony_name'] ?? 'APS Colony']
                    );
                } catch (\Throwable $notifEx) {
                    error_log('AssociateController::bookPlot notification error: ' . $notifEx->getMessage());
                }

                $_SESSION['flash_success'] = 'Plot booking submitted successfully! Booking #' . $bookingNumber . '. Waiting for admin approval.';
                $this->redirect('/associate/dashboard');
            } catch (Exception $e) {
                $db->rollBack();
                error_log('AssociateController::bookPlot insert error: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error saving booking. Please try again.';
                $this->redirect('/associate/book-plot');
            }
        } catch (Exception $e) {
            error_log('AssociateController::bookPlot error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'An error occurred. Please try again.';
            $this->redirect('/associate/book-plot');
        }
    }

    public function compareProperties()
    {
        // Legacy - redirects to tools
        $this->redirect('/associate/tools');
    }

    public function importLeads()
    {
        // Legacy - redirects to CRM
        $this->redirect('/associate/leads');
    }

    public function bulkWhatsApp()
    {
        // Legacy - redirects to CRM leads
        $this->redirect('/associate/leads');
    }

    public function assignLead($id)
    {
        // Legacy - not implemented in facade
        $_SESSION['error'] = 'Feature moved to CRM';
        $this->redirect('/associate/leads');
    }

    public function recalculateScore($id)
    {
        $_SESSION['error'] = 'Feature moved to CRM';
        $this->redirect('/associate/leads');
    }

    public function recalculateAllScores()
    {
        $_SESSION['error'] = 'Feature moved to CRM';
        $this->redirect('/associate/leads');
    }

    public function exportLeads()
    {
        // Legacy - redirects to CRM leads
        $this->redirect('/associate/leads');
    }
}