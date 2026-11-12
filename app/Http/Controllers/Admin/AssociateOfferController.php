<?php

namespace App\Http\Controllers\Admin;

use App\Services\AssociateOfferService;
use App\Services\FestivalOfferService;

/**
 * Admin Management for Associate Festival & Campaign Offers (Session 460)
 */
class AssociateOfferController extends AdminController
{
    private FestivalOfferService $festivalService;

    public function __construct()
    {
        parent::__construct();
        $this->festivalService = new FestivalOfferService($this->db);
    }

    public function index()
    {
        $this->requireAdmin();
        $svc = new AssociateOfferService();
        $offers = $svc->listOffers(true);
        try {
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies ORDER BY name LIMIT 200") ?? [];
        } catch (\Throwable $e) {
            $colonies = [];
        }

        $overview = $this->festivalService->getAdminOffersOverview();

        return $this->render('admin/associate_offers/index', [
            'page_title' => 'Associate Festival & Campaign Offers',
            'offers'     => $offers,
            'colonies'   => $colonies,
            'claims'     => $overview['claims'],
            'stats'      => $overview['stats'],
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }

        $svc = new AssociateOfferService();
        try {
            $res = $svc->createOffer($_POST, (int)($_SESSION['user_id'] ?? 0));
            $this->setFlash($res['success'] ? 'success' : 'error', $res['success'] ? 'Festival offer campaign created as draft' : ($res['message'] ?? 'Failed'));
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/associate-offers');
    }

    public function activate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }
        $svc = new AssociateOfferService();
        $ok = $svc->setStatus((int)($_POST['id'] ?? 0), 'active');
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Festival offer activated — now visible to all associates' : 'Activation failed');
        $this->redirect('/admin/associate-offers');
    }

    public function close()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }
        $svc = new AssociateOfferService();
        $ok = $svc->setStatus((int)($_POST['id'] ?? 0), 'closed');
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Offer closed' : 'Close failed');
        $this->redirect('/admin/associate-offers');
    }

    public function delete()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }
        $svc = new AssociateOfferService();
        $ok = $svc->deleteOffer((int)($_POST['id'] ?? 0));
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Draft offer deleted' : 'Only draft offers can be deleted');
        $this->redirect('/admin/associate-offers');
    }

    /**
     * Admin: Approve an associate festival claim
     */
    public function approveClaim($claimId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }

        $adminId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1);
        $notes = trim($_POST['admin_notes'] ?? '');
        $res = $this->festivalService->approveAssociateOfferClaim((int)$claimId, $adminId, $notes ?: null);

        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        $this->redirect('/admin/associate-offers#claims');
    }

    /**
     * Admin: 1-Click Disburse Cash Reward to Associate Wallet
     */
    public function disburseCash($claimId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }

        $adminId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1);
        $res = $this->festivalService->disburseAssociateOfferCash((int)$claimId, $adminId);

        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        $this->redirect('/admin/associate-offers#claims');
    }

    /**
     * Admin: Mark Physical Gift Handed Over / Delivered
     */
    public function markDelivered($claimId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/associate-offers');
        }

        $adminId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1);
        $notes = trim($_POST['admin_notes'] ?? 'Physical gift handed over at official event');
        $res = $this->festivalService->markAssociateOfferDelivered((int)$claimId, $adminId, $notes);

        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        $this->redirect('/admin/associate-offers#claims');
    }
}
