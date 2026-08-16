<?php

namespace App\Http\Controllers\Admin;

use App\Services\AuctionService;

class AuctionController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new AuctionService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function index()
    {
        $status = $_GET['status'] ?? null;
        $auctions = $this->service ? $this->service->getAllAuctions($status) : [];
        $stats = $this->service ? $this->service->getStats() : [];
        if ($this->service) $this->service->processEndingAuctions();
        return $this->render('admin.auctions.index', [
            'page_title' => 'Property Auctions',
            'page_heading' => 'Property Auctions',
            'auctions' => $auctions,
            'stats' => $stats,
            'current_status' => $status
        ]);
    }

    public function create()
    {
        $properties = $this->fetchProperties();
        return $this->render('admin.auctions.create', [
            'page_title' => 'Create Auction',
            'page_heading' => 'Create New Auction',
            'properties' => $properties
        ]);
    }

    public function store()
    {
        if (!$this->service) return $this->redirect(BASE_URL . '/admin/auctions');
        $data = [
            'property_id' => $_POST['property_id'] ?: null,
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? null,
            'auction_type' => $_POST['auction_type'] ?? 'english',
            'start_price' => (float)($_POST['start_price'] ?? 0),
            'reserve_price' => $_POST['reserve_price'] ? (float)$_POST['reserve_price'] : null,
            'bid_increment' => (float)($_POST['bid_increment'] ?? 1000),
            'buy_now_price' => $_POST['buy_now_price'] ? (float)$_POST['buy_now_price'] : null,
            'deposit_amount' => $_POST['deposit_amount'] ? (float)$_POST['deposit_amount'] : null,
            'starts_at' => $_POST['starts_at'] ?? date('Y-m-d H:i:s'),
            'ends_at' => $_POST['ends_at'] ?? date('Y-m-d H:i:s', strtotime('+7 days')),
            'extension_seconds' => (int)($_POST['extension_seconds'] ?? 60),
            'status' => $_POST['status'] ?? 'scheduled',
            'terms' => $_POST['terms'] ?? null,
            'image_url' => $_POST['image_url'] ?? null,
            'created_by' => $this->getUserId()
        ];
        $id = $this->service->createAuction($data);
        $this->setFlash($id ? 'success' : 'error', $id ? "Auction #$id created" : 'Failed to create auction');
        return $this->redirect(BASE_URL . '/admin/auctions/show/' . $id);
    }

    public function show($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : 0;
        if (!$this->service || !$id) return $this->redirect(BASE_URL . '/admin/auctions');
        $auction = $this->service->getAuctionById($id);
        if (!$auction) {
            $this->setFlash('error', 'Auction not found');
            return $this->redirect(BASE_URL . '/admin/auctions');
        }
        $bids = $this->service->getBids($id, 100);
        return $this->render('admin.auctions.show', [
            'page_title' => $auction['title'],
            'page_heading' => $auction['title'],
            'auction' => $auction,
            'bids' => $bids
        ]);
    }

    public function start($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : (int)($_GET['id'] ?? 0);
        if ($this->service && $id) $this->service->startAuction($id);
        return $this->redirect(BASE_URL . '/admin/auctions/show/' . $id);
    }

    public function end($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $result = $this->service->endAuction($id);
            $this->setFlash('success', 'Auction ended: ' . ($result['status'] ?? 'closed'));
        }
        return $this->redirect(BASE_URL . '/admin/auctions/show/' . $id);
    }

    public function cancel($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->cancelAuction($id, $_GET['reason'] ?? null);
            $this->setFlash('success', 'Auction cancelled');
        }
        return $this->redirect(BASE_URL . '/admin/auctions');
    }

    public function delete($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : (int)($_GET['id'] ?? 0);
        if ($id) {
            try {
                $tid = $this->tenantId();
                $this->pdo()->prepare("DELETE FROM auction_deposits WHERE auction_id = ? AND tenant_id = ?")->execute([$id, $tid]);
                $this->pdo()->prepare("DELETE FROM auction_watchers WHERE auction_id = ? AND tenant_id = ?")->execute([$id, $tid]);
                $this->pdo()->prepare("DELETE FROM auction_bids WHERE auction_id = ? AND tenant_id = ?")->execute([$id, $tid]);
                $this->pdo()->prepare("DELETE FROM auctions WHERE id = ? AND tenant_id = ?")->execute([$id, $tid]);
                $this->setFlash('success', 'Auction deleted');
            } catch (\Throwable $e) {
                $this->setFlash('error', 'Delete failed: ' . $e->getMessage());
            }
        }
        return $this->redirect(BASE_URL . '/admin/auctions');
    }

    public function processEnding()
    {
        if ($this->service) {
            $count = $this->service->processEndingAuctions();
            $this->setFlash('success', "Processed $count ending auctions");
        }
        return $this->redirect(BASE_URL . '/admin/auctions');
    }

    private function fetchProperties()
    {
        try {
            $stmt = $this->pdo()->query("SELECT id, name, address, price FROM user_properties WHERE status = 'approved' ORDER BY created_at DESC LIMIT 100");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    private function getUserId() { return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0); }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }
}
