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
        // Legacy - redirects to property listing
        $this->redirect('/associate/add-property');
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