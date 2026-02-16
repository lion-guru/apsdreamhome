<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\ReferralService;
use App\Services\CommissionAgreementService;
use App\Services\RankService;
use Exception;
use Throwable;
use PDO;

/**
 * NetworkController
 * Admin UI endpoints for MLM network management.
 */
class NetworkController extends BaseController
{
    private $referralService;
    private $agreementService;
    private $rankService;

    public function __construct()
    {
        parent::__construct();

        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->referralService = new ReferralService();
        $this->agreementService = new CommissionAgreementService();
        $this->rankService = new RankService();
    }

    public function index(): void
    {
        $this->data['ranks'] = $this->rankService->getRanks();
        $this->data['page_title'] = 'MLM Network Inspector';
        $this->render('admin/mlm_network_inspector');
    }

    public function searchUsers(): void
    {
        header('Content-Type: application/json');

        $query = trim($_GET['query'] ?? '');
        if ($query === '') {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        try {
            // Using PDO from BaseController instead of mysqli
            $sql = "SELECT id, name, email FROM users WHERE email LIKE ? OR name LIKE ? LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $like = '%' . $query . '%';
            $stmt->execute([$like, $like]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function networkTree(): void
    {
        header('Content-Type: application/json');

        $userId = (int) ($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id required']);
            return;
        }

        $maxDepth = (int) ($_GET['depth'] ?? 5);
        $query = $_GET['query'] ?? null;
        $rank = $_GET['rank'] ?? null;

        $options = [];
        if ($query) {
            $options['query'] = $query;
        }
        if ($rank) {
            $options['rank'] = $rank;
        }

        try {
            $tree = $this->referralService->getNetworkTree($userId, $maxDepth, $options);
            echo json_encode(['success' => true, 'data' => $tree]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
