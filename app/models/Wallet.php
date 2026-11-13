<?php
namespace App\Models;

use App\Core\Database;
use App\Services\WalletService;
use Exception;

class Wallet
{
    private $db;
    private WalletService $service;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->service = new WalletService();
    }

    public function getByUserId($userId)
    {
        try {
            return $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ?", [$userId]);
        } catch (\Throwable $e) {
            error_log('Wallet::getByUserId error: ' . $e->getMessage());
            return null;
        }
    }

    public function getBalance($userId)
    {
        return $this->service->getBalance((int)$userId);
    }

    public function credit($userId, $amount, $category, $description)
    {
        return $this->service->credit((int)$userId, (float)$amount, (string)$category, (string)$description);
    }

    public function debit($userId, $amount, $description)
    {
        return $this->service->debit((int)$userId, (float)$amount, (string)$description, 'withdrawal');
    }

    public function getTransactions($userId, $limit = 20)
    {
        return $this->service->getTransactions((int)$userId, (int)$limit);
    }

    public function createWallet($userId)
    {
        return $this->service->ensureWallet((int)$userId);
    }
}
