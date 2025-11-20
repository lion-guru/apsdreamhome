<?php
require_once __DIR__ . '/../services/ReferralService.php';
require_once __DIR__ . '/../services/CommissionAgreementService.php';
require_once __DIR__ . '/../services/RankService.php';

class AdminNetworkController
{
    private ReferralService $referralService;
    private CommissionAgreementService $agreementService;
    private RankService $rankService;

    public function __construct()
    {
        $this->referralService = new ReferralService();
        $this->agreementService = new CommissionAgreementService();
        $this->rankService = new RankService();
    }

    public function index(): void
    {
        $this->ensureAdmin();
        $ranks = $this->rankService->getRanks();
        require __DIR__ . '/../views/admin/mlm_network_inspector.php';
    }

    public function searchUsers(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $query = trim($_GET['query'] ?? '');
        if ($query === '') {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        $config = AppConfig::getInstance();
        $conn = $config->getDatabaseConnection();
        $stmt = $conn->prepare('SELECT id, name, email FROM users WHERE email LIKE ? OR name LIKE ? LIMIT 10');
        $like = '%' . $query . '%';
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['success' => true, 'data' => $rows]);
    }

    public function networkTree(): void
    {
        $this->ensureAdmin();
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

        $tree = $this->referralService->getNetworkTree($userId, $maxDepth, $options);
        echo json_encode(['success' => true, 'data' => $tree]);
    }

    public function listAgreements(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');
        $userId = (int) ($_GET['user_id'] ?? 0);
        $filters = [];
        if ($userId > 0) {
            $filters['user_id'] = $userId;
        }
        $agreements = $this->agreementService->listAgreements($filters);
        echo json_encode(['success' => true, 'data' => $agreements]);
    }

    public function createAgreement(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');
        $data = $_POST;
        $result = $this->agreementService->createAgreement($data);
        echo json_encode($result);
    }

    public function updateAgreement(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'id required']);
            return;
        }
        $data = $_POST;
        $success = $this->agreementService->updateAgreement($id, $data);
        echo json_encode(['success' => $success]);
    }

    public function deleteAgreement(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'id required']);
            return;
        }
        $success = $this->agreementService->deleteAgreement($id);
        echo json_encode(['success' => $success]);
    }

    public function rebuildNetwork(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id required']);
            return;
        }

        $script = escapeshellarg(ROOT . 'tools/rebuild_network.php');
        $command = sprintf('php %s --user=%d 2>&1', $script, $userId);
        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            echo json_encode(['success' => true, 'message' => 'Rebuild initiated']);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Rebuild failed',
                'output' => $output
            ]);
        }
    }

    private function ensureAdmin(): void
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASE_URL . 'admin/');
            exit();
        }
    }
}
