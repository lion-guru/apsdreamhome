<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Services\MLM\CareerRewardService;
use App\Traits\TenantAwareTrait;

/**
 * Associate Career Rewards Controller
 *
 * Allows associates to view their 7-tier Career Rank Rewards,
 * track progress toward each reward (Mobile, Tablet, Laptop, Tour, Pulsar, Bullet, Luxury Car),
 * submit claim requests, choose physical handover vs courier vs wallet cash conversion,
 * and view live fulfillment tracking.
 */
class RewardController extends BaseController
{
    use TenantAwareTrait;

    private CareerRewardService $rewardService;

    public function __construct()
    {
        parent::__construct();
        $this->rewardService = new CareerRewardService();
    }

    private function requireAuth(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to view your Career Rewards.';
            $this->redirect('/associate/login');
            exit;
        }
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        try {
            $data = $this->rewardService->getAssociateRewardDashboard($userId);

            $this->render('associate/rewards', array_merge([
                'page_title'       => 'Career Rank Rewards - APS Dream Home',
                'page_description' => 'Track and claim your authentic rank rewards from Mobile Phone to Luxury Car.',
                'current_page'     => 'rewards',
            ], $data), 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('[RewardController] index error: ' . $e->getMessage());
            $this->setFlash('error', 'Error loading reward dashboard: ' . $e->getMessage());
            $this->redirect('/associate/dashboard');
        }
    }

    public function claim(int $rewardId): void
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/rewards');
            return;
        }

        // CSRF verification
        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Security token mismatch. Please try again.');
            $this->redirect('/associate/rewards');
            return;
        }

        $deliveryType = trim((string)($_POST['delivery_type'] ?? 'physical_handover'));
        $shippingAddress = trim((string)($_POST['shipping_address'] ?? ''));
        $contactPhone = trim((string)($_POST['contact_phone'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));

        $res = $this->rewardService->claimReward($userId, $rewardId, [
            'delivery_type'    => $deliveryType,
            'shipping_address' => $shippingAddress,
            'contact_phone'    => $contactPhone,
            'notes'            => $notes,
        ]);

        if ($res['success']) {
            $this->setFlash('success', (string)$res['message']);
        } else {
            $this->setFlash('error', (string)$res['message']);
        }

        $this->redirect('/associate/rewards');
    }
}

