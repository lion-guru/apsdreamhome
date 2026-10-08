<?php

namespace App\Http\Controllers\Admin;

use App\Services\AssociateOfferService;

/**
 * Admin CRUD for associate/agent offer campaigns.
 * (New-track file — probe-verified, no edits to shared controllers.)
 */
class AssociateOfferController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $svc = new AssociateOfferService();
        $offers = $svc->listOffers(true);
        try {
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies ORDER BY name LIMIT 200") ?? [];
        } catch (\Exception $e) {
            $colonies = [];
        }
        return $this->render('admin/associate_offers/index', [
            'page_title' => 'Offer Campaigns',
            'offers' => $offers,
            'colonies' => $colonies,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/associate-offers'); }
        $svc = new AssociateOfferService();
        try {
            $res = $svc->createOffer($_POST, (int)($_SESSION['user_id'] ?? 0));
            $this->setFlash($res['success'] ? 'success' : 'error', $res['success'] ? 'Offer campaign created as draft' : ($res['message'] ?? 'Failed'));
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/associate-offers');
    }

    public function activate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/associate-offers'); }
        $svc = new AssociateOfferService();
        $ok = $svc->setStatus((int)($_POST['id'] ?? 0), 'active');
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Offer activated — now visible to associates/agents' : 'Activation failed');
        $this->redirect('/admin/associate-offers');
    }

    public function close()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/associate-offers'); }
        $svc = new AssociateOfferService();
        $ok = $svc->setStatus((int)($_POST['id'] ?? 0), 'closed');
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Offer closed' : 'Close failed');
        $this->redirect('/admin/associate-offers');
    }

    public function delete()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/associate-offers'); }
        $svc = new AssociateOfferService();
        $ok = $svc->deleteOffer((int)($_POST['id'] ?? 0));
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Draft offer deleted' : 'Only draft offers can be deleted');
        $this->redirect('/admin/associate-offers');
    }
}
