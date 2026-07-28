<?php

namespace App\Http\Controllers\Front;

use App\Services\AuctionService;
use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class AuctionController extends BaseController
{
    use TenantAwareTrait;
    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new AuctionService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function index()
    {
        if ($this->service) $this->service->processEndingAuctions();
        $live = $this->service ? $this->service->getLiveAuctions(30) : [];
        $upcoming = $this->service ? $this->service->getAllAuctions('scheduled', 20) : [];
        $closed = $this->service ? $this->service->getAllAuctions('sold', 10) : [];
        $this->renderView('auctions.index', [
            'page_title' => 'Property Auctions',
            'page_heading' => 'Live Property Auctions',
            'live' => $live,
            'upcoming' => $upcoming,
            'closed' => $closed
        ]);
    }

    public function show($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : 0;
        if (!$this->service || !$id) {
            $this->renderView('errors.404');
            return;
        }
        $auction = $this->service->getAuctionById($id);
        if (!$auction) {
            $this->renderView('errors.404');
            return;
        }
        $bids = $this->service->getBids($id, 30);
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        $isWatching = $userId ? $this->service->isWatching($id, $userId) : false;
        $deposit = $userId ? $this->service->hasDeposit($id, $userId) : null;
        $this->renderView('auctions.show', [
            'page_title' => $auction['title'],
            'page_heading' => $auction['title'],
            'auction' => $auction,
            'bids' => $bids,
            'is_watching' => $isWatching,
            'has_deposit' => $deposit
        ]);
    }

    public function bid()
    {
        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $maxAutoBid = !empty($_POST['max_auto_bid']) ? (float)$_POST['max_auto_bid'] : null;
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        $userName = $_SESSION['user_name'] ?? 'Guest';
        if (!$userId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Please login to place a bid']);
            exit;
        }
        if (!$auctionId || $amount <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid bid']);
            exit;
        }
        if (!$this->service) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Service unavailable']);
            exit;
        }
        $auction = $this->service->getAuctionById($auctionId);
        if ($auction && $auction['deposit_amount'] > 0) {
            $deposit = $this->service->hasDeposit($auctionId, $userId);
            if (!$deposit) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'A deposit of ₹' . number_format($auction['deposit_amount']) . ' is required to bid']);
                exit;
            }
        }
        $result = $this->service->placeBid($auctionId, $userId, $userName, $amount, $maxAutoBid);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function watch()
    {
        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        if (!$userId || !$auctionId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Login required']);
            exit;
        }
        $this->service->watch($auctionId, $userId);
        echo json_encode(['success' => true, 'watching' => true]);
        exit;
    }

    public function unwatch()
    {
        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        if ($userId && $auctionId) $this->service->unwatch($auctionId, $userId);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'watching' => false]);
        exit;
    }

    public function deposit()
    {
        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        if (!$userId || !$auctionId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Login required']);
            exit;
        }
        $auction = $this->service->getAuctionById($auctionId);
        if (!$auction || !$auction['deposit_amount']) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No deposit required']);
            exit;
        }
        $txn = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
        $this->service->recordDeposit($auctionId, $userId, $auction['deposit_amount'], 'mock_gateway', $txn);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'transaction' => $txn]);
        exit;
    }

    private function renderView($view, $data = [])
    {
        $data = array_merge(['BASE_URL' => BASE_URL ?? ''], $data);
        extract($data);
        $viewFile = APP_PATH . '/views/pages/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            // The view file is self-contained: it manages its own ob_start /
            // ob_get_clean and includes layouts/base.php directly.
            // Do NOT wrap with an outer layout here or the page will render twice.
            include $viewFile;
        } else {
            echo "View not found: $view";
        }
    }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }
}
