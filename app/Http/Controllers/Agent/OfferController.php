<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;
use App\Services\AssociateOfferService;

/**
 * Agent portal: live offer campaigns with personal progress.
 * (Additive file — does not touch existing portal controllers.)
 */
class OfferController extends BaseController
{
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
            $_SESSION['error'] = 'Please login as an agent to access this page';
            $this->redirect('/agent/login');
        }
    }

    public function index()
    {
        $this->requireAuth();
        // plot_bookings.associate_id stores users.id — pass session id directly.
        $userId = (int)$_SESSION['user_id'];
        $offers = [];
        try {
            $db = \App\Core\Database\Database::getInstance()->getPdo();
            $offers = (new AssociateOfferService($db))->visibleOffers($userId);
        } catch (\Throwable $e) {
            error_log('AgentOfferController: ' . $e->getMessage());
        }
        $this->render('agent/offers', [
            'page_title' => 'Offers - Agent Portal',
            'page_description' => 'Live company offers and your progress',
            'offers' => $offers,
        ], 'layouts/agent');
    }
}
