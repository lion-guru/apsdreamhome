<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Services\FestivalOfferService;
use App\Traits\TenantAwareTrait;
use Exception;

/**
 * Associate Festival & Promotional Campaign Offers Controller (Session 460)
 */
class OfferController extends BaseController
{
    use TenantAwareTrait;

    private FestivalOfferService $offerService;

    public function __construct()
    {
        parent::__construct();
        $this->offerService = new FestivalOfferService();
    }

    private function requireAuth(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access festival offers.';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Display Associate Festival & Special Promotional Offers
     */
    public function index()
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        try {
            $dashboard = $this->offerService->getAssociateFestivalDashboard($userId);
            $customerOffers = $this->offerService->getActiveCustomerOffers();

            $this->render('associate/offers', [
                'page_title'       => 'Festival & Special Campaign Offers — Associate Portal',
                'page_description' => 'Participate in festive sales targets, win cash bonuses, gold coins, and consumer electronic gifts.',
                'dashboard'        => $dashboard,
                'offers'           => $dashboard['offers'],
                'claims'           => $dashboard['claims'],
                'customer_offers'  => $customerOffers,
                'current_page'     => 'offers',
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('[Associate\OfferController] index error: ' . $e->getMessage());
            $this->setFlash('error', 'Unable to load festival offers: ' . $e->getMessage());
            return $this->redirect('/associate/dashboard');
        }
    }

    /**
     * Submit Associate Claim for Festival Offer
     */
    public function claim($offerId)
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $offerId = (int)$offerId;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/associate/offers');
        }

        // Validate CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->setFlash('error', 'Security token expired. Please try again.');
            return $this->redirect('/associate/offers');
        }

        try {
            $res = $this->offerService->claimAssociateOffer($userId, $offerId, $_POST);
            $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        } catch (\Throwable $e) {
            error_log('[Associate\OfferController] claim error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to submit claim: ' . $e->getMessage());
        }

        return $this->redirect('/associate/offers');
    }
}
