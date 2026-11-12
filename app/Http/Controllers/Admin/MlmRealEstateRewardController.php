<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\MLM\CareerRewardService;

/**
 * Admin MLM Real Estate Career Rewards Controller
 *
 * Dedicated desk for managing associate career rewards:
 * - Review eligible and claimed rank rewards (Mobile, Tablet, Laptop, Tour, Pulsar, Bullet, Luxury Car)
 * - Approve claims
 * - Dispatch with tracking ID, courier partner, and serial number / IMEI
 * - Mark officially Handed Over at company ceremonies
 * - Convert to Cash Equivalent and disburse directly into Associate's wallet
 * - Reject claims with reason
 */
class MlmRealEstateRewardController extends AdminController
{
    private CareerRewardService $rewardService;

    public function __construct()
    {
        parent::__construct();
        $this->rewardService = new CareerRewardService();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $statusFilter = isset($_GET['status']) ? (string)$_GET['status'] : null;
        $data = $this->rewardService->getAllRewardClaims($statusFilter);

        $this->render('admin/mlm-realestate/rewards', [
            'page_title'       => 'MLM Career Rank Rewards Desk - Admin',
            'page_description' => 'Review, approve, dispatch, and fulfill associate career rank rewards.',
            'claims'           => $data['claims'],
            'stats'            => $data['stats'],
            'statusFilter'     => $statusFilter ?: 'all',
        ]);
    }

    public function approve(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Security token mismatch. Please try again.');
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $adminId = $this->getUserId();
        $adminNotes = isset($_POST['admin_notes']) ? trim((string)$_POST['admin_notes']) : null;

        $res = $this->rewardService->approveReward($id, $adminId, $adminNotes);

        if ($res['success']) {
            $this->setFlash('success', (string)$res['message']);
        } else {
            $this->setFlash('error', (string)$res['message']);
        }

        $this->redirect('/admin/mlm-realestate/rewards');
    }

    public function dispatch(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Security token mismatch. Please try again.');
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $adminId = $this->getUserId();
        $dispatchData = [
            'tracking_number' => (string)($_POST['tracking_number'] ?? ''),
            'courier_partner' => (string)($_POST['courier_partner'] ?? ''),
            'serial_number'   => (string)($_POST['serial_number'] ?? ''),
            'admin_notes'     => (string)($_POST['admin_notes'] ?? ''),
        ];

        $res = $this->rewardService->dispatchReward($id, $adminId, $dispatchData);

        if ($res['success']) {
            $this->setFlash('success', (string)$res['message']);
        } else {
            $this->setFlash('error', (string)$res['message']);
        }

        $this->redirect('/admin/mlm-realestate/rewards');
    }

    public function deliver(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Security token mismatch. Please try again.');
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $adminId = $this->getUserId();
        $deliveryData = [
            'handover_event' => (string)($_POST['handover_event'] ?? 'Official Ceremony'),
            'handover_photo' => (string)($_POST['handover_photo'] ?? ''),
            'admin_notes'    => (string)($_POST['admin_notes'] ?? ''),
        ];

        $res = $this->rewardService->markDelivered($id, $adminId, $deliveryData);

        if ($res['success']) {
            $this->setFlash('success', (string)$res['message']);
        } else {
            $this->setFlash('error', (string)$res['message']);
        }

        $this->redirect('/admin/mlm-realestate/rewards');
    }

    public function convertToCash(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Security token mismatch. Please try again.');
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $adminId = $this->getUserId();
        $customAmount = !empty($_POST['cash_amount']) ? (float)$_POST['cash_amount'] : null;
        $notes = isset($_POST['notes']) ? trim((string)$_POST['notes']) : null;

        $res = $this->rewardService->convertToCashWallet($id, $adminId, $customAmount, $notes);

        if ($res['success']) {
            $this->setFlash('success', (string)$res['message']);
        } else {
            $this->setFlash('error', (string)$res['message']);
        }

        $this->redirect('/admin/mlm-realestate/rewards');
    }

    public function reject(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Security token mismatch. Please try again.');
            $this->redirect('/admin/mlm-realestate/rewards');
            return;
        }

        $adminId = $this->getUserId();
        $reason = trim((string)($_POST['reason'] ?? 'Requirements not verified'));

        $res = $this->rewardService->rejectReward($id, $reason, $adminId);

        if ($res['success']) {
            $this->setFlash('success', (string)$res['message']);
        } else {
            $this->setFlash('error', (string)$res['message']);
        }

        $this->redirect('/admin/mlm-realestate/rewards');
    }
}

