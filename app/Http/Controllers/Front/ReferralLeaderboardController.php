<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\ReferralService;

class ReferralLeaderboardController extends BaseController
{
    private ReferralService $referralService;

    public function __construct()
    {
        parent::__construct();
        $this->referralService = new ReferralService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function requireCustomer(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    public function leaderboard()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        
        $leaderboard = $this->referralService->getLeaderboard();
        $myRank = $this->referralService->getUserRank($userId);
        $myStats = $this->referralService->getReferralEarningsBreakdown($userId);

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/pages/referral_leaderboard.php';
    }

    public function leaderboardData()
    {
        $this->requireCustomer();
        $period = $_GET['period'] ?? 'all';
        $leaderboard = $this->referralService->getLeaderboard(20, $period);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
    }
}