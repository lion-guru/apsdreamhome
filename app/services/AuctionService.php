<?php

namespace App\Services;

use \App\Traits\ServiceTenantTrait;

class AuctionService
{
    use \App\Traits\ServiceTenantTrait;

    private $pdo;

    public function __construct($db = null)
    {
        if (is_object($db) && method_exists($db, 'getPdo')) {
            $this->pdo = $db->getPdo();
        } else {
            $this->pdo = $db;
        }
    }

    public function createAuction($data)
    {
        try {
            $insertData = $this->tenantInsertData();
            $cols = "property_id, plot_id, title, description, auction_type, start_price, reserve_price, bid_increment, buy_now_price, deposit_amount, starts_at, ends_at, original_ends_at, extension_seconds, auto_extend_threshold_seconds, status, terms, image_url, created_by" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $sql = "INSERT INTO auctions ($cols) VALUES ($ph)";
            $stmt = $this->pdo->prepare($sql);
            $params = [
                $data['property_id'] ?? null,
                $data['plot_id'] ?? null,
                $data['title'],
                $data['description'] ?? null,
                $data['auction_type'] ?? 'english',
                $data['start_price'],
                $data['reserve_price'] ?? null,
                $data['bid_increment'] ?? 1000,
                $data['buy_now_price'] ?? null,
                $data['deposit_amount'] ?? null,
                $data['starts_at'],
                $data['ends_at'],
                $data['ends_at'],
                $data['extension_seconds'] ?? 60,
                $data['auto_extend_threshold_seconds'] ?? 60,
                $data['status'] ?? 'draft',
                $data['terms'] ?? null,
                $data['image_url'] ?? null,
                $data['created_by'] ?? null
            ];
            if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
            $stmt->execute($params);
            return $this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log("Auction create: " . $e->getMessage());
            return null;
        }
    }

    public function getAllAuctions($status = null, $limit = 50)
    {
        try {
            $sql = "SELECT a.*, p.name as property_title, p.address as property_address FROM auctions a LEFT JOIN user_properties p ON a.property_id = p.id WHERE 1=1" . $this->tenantSql();
            $params = [];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            if ($status) {
                $sql .= " AND a.status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY a.starts_at DESC LIMIT " . (int)$limit;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getLiveAuctions($limit = 20)
    {
        try {
            $tid = $this->tenantId();
            $sql = "SELECT a.*, p.name as property_title, p.address as property_address, p.city_name as property_city, p.price as property_price, p.area_sqft, p.image as property_image FROM auctions a LEFT JOIN user_properties p ON a.property_id = p.id WHERE a.status = 'live' AND a.ends_at > NOW()" . ($tid > 1 ? " AND a.tenant_id = ?" : "") . " ORDER BY a.ends_at ASC LIMIT " . (int)$limit;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($tid > 1 ? [$tid] : []);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getAuctionById($id)
    {
        try {
            $sql = "SELECT a.*, p.name as property_title, p.address as property_address, p.city_name as property_city, p.price as property_price, p.area_sqft, p.image as property_image FROM auctions a LEFT JOIN user_properties p ON a.property_id = p.id WHERE a.id = ?" . $this->tenantSql();
            $stmt = $this->pdo->prepare($sql);
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }

    public function placeBid($auctionId, $bidderId, $bidderName, $amount, $maxAutoBid = null)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT * FROM auctions WHERE id = ? FOR UPDATE" . $this->tenantSql());
            $params = [$auctionId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            $auction = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$auction) {
                $this->pdo->rollBack();
                return ['error' => 'Auction not found'];
            }
            if ($auction['status'] !== 'live') {
                $this->pdo->rollBack();
                return ['error' => 'Auction is not live'];
            }
            if (strtotime($auction['ends_at']) < time()) {
                $this->pdo->rollBack();
                return ['error' => 'Auction has ended'];
            }

            $minBid = ($auction['current_bid'] ?: $auction['start_price']) + $auction['bid_increment'];
            if ($amount < $minBid) {
                $this->pdo->rollBack();
                return ['error' => "Minimum bid is ₹" . number_format($minBid)];
            }

            $this->pdo->prepare("UPDATE auction_bids SET status = 'outbid' WHERE auction_id = ? AND status = 'winning'")
                ->execute([$auctionId]);

            $newEndsAt = $auction['ends_at'];
            $autoExtended = false;
            $secondsLeft = strtotime($auction['ends_at']) - time();
            if ($secondsLeft <= $auction['auto_extend_threshold_seconds']) {
                $newEndsAt = date('Y-m-d H:i:s', time() + $auction['extension_seconds']);
                $autoExtended = true;
            }

            $this->pdo->prepare("INSERT INTO auction_bids (auction_id, bidder_id, bidder_name, bid_amount, max_auto_bid, bid_type, ip_address, status) VALUES (?,?,?,?,?,'manual',?,'winning')")
                ->execute([$auctionId, $bidderId, $bidderName, $amount, $maxAutoBid, $_SERVER['REMOTE_ADDR'] ?? '']);

            $this->pdo->prepare("UPDATE auctions SET current_bid = ?, bid_count = bid_count + 1, ends_at = ?" . $this->tenantSql() . " WHERE id = ?")
                ->execute(array_merge([$amount, $newEndsAt, $auctionId], $this->tenantId() > 1 ? [$this->tenantId()] : []));

            $this->pdo->commit();
            return [
                'success' => true,
                'amount' => $amount,
                'ends_at' => $newEndsAt,
                'auto_extended' => $autoExtended
            ];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Auction bid: " . $e->getMessage());
            return ['error' => 'Bid failed: ' . $e->getMessage()];
        }
    }

    public function getBids($auctionId, $limit = 50)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT b.*, u.name as full_name, u.email FROM auction_bids b LEFT JOIN users u ON b.bidder_id = u.id WHERE b.auction_id = ? ORDER BY b.placed_at DESC LIMIT " . (int)$limit);
            $stmt->execute([$auctionId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function endAuction($auctionId)
    {
        try {
            $winnerStmt = $this->pdo->prepare("SELECT bidder_id, bidder_name, bid_amount FROM auction_bids WHERE auction_id = ? AND status = 'winning' ORDER BY bid_amount DESC LIMIT 1");
            $winnerStmt->execute([$auctionId]);
            $winner = $winnerStmt->fetch(\PDO::FETCH_ASSOC);

            $auction = $this->getAuctionById($auctionId);
            $status = 'ended';
            if ($winner && (!$auction['reserve_price'] || $winner['bid_amount'] >= $auction['reserve_price'])) {
                $status = 'sold';
                $this->pdo->prepare("UPDATE auction_bids SET status = 'won' WHERE auction_id = ? AND bidder_id = ?")
                    ->execute([$auctionId, $winner['bidder_id']]);
            }

            $this->pdo->prepare("UPDATE auctions SET status = ?, winner_id = ?, winning_bid = ?, closed_at = NOW() WHERE id = ?" . $this->tenantSql())
                ->execute(array_merge([$status, $winner['bidder_id'] ?? null, $winner['bid_amount'] ?? null, $auctionId], $this->tenantId() > 1 ? [$this->tenantId()] : []));

            return ['success' => true, 'status' => $status, 'winner' => $winner];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    public function startAuction($auctionId)
    {
        try {
            $tid = $this->tenantId();
            $sql = "UPDATE auctions SET status = 'live' WHERE id = ? AND status IN ('draft','scheduled')" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($tid > 1 ? [$auctionId, $tid] : [$auctionId]);
            return true;
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function cancelAuction($auctionId, $reason = null)
    {
        try {
            $tid = $this->tenantId();
            $sql = "UPDATE auctions SET status = 'cancelled', close_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($tid > 1 ? [$reason, $auctionId, $tid] : [$reason, $auctionId]);
            return true;
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function watch($auctionId, $userId, $notifyOutbid = true, $notifyEnding = true)
    {
        try {
            $this->pdo->prepare("INSERT IGNORE INTO auction_watchers (auction_id, user_id, notify_outbid, notify_ending) VALUES (?,?,?,?)")
                ->execute([$auctionId, $userId, $notifyOutbid ? 1 : 0, $notifyEnding ? 1 : 0]);
            $this->pdo->prepare("UPDATE auctions SET watcher_count = (SELECT COUNT(*) FROM auction_watchers WHERE auction_id = ?) WHERE id = ?")
                ->execute([$auctionId, $auctionId]);
            return true;
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function unwatch($auctionId, $userId)
    {
        try {
            $this->pdo->prepare("DELETE FROM auction_watchers WHERE auction_id = ? AND user_id = ?")
                ->execute([$auctionId, $userId]);
            $this->pdo->prepare("UPDATE auctions SET watcher_count = (SELECT COUNT(*) FROM auction_watchers WHERE auction_id = ?) WHERE id = ?")
                ->execute([$auctionId, $auctionId]);
            return true;
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function isWatching($auctionId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM auction_watchers WHERE auction_id = ? AND user_id = ?");
            $stmt->execute([$auctionId, $userId]);
            return (bool)$stmt->fetch();
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function recordDeposit($auctionId, $userId, $amount, $method = null, $transactionId = null)
    {
        try {
            $this->pdo->prepare("INSERT INTO auction_deposits (auction_id, user_id, amount, payment_method, transaction_id, status, paid_at) VALUES (?,?,?,?,?,'paid',NOW()) ON DUPLICATE KEY UPDATE amount = VALUES(amount), payment_method = VALUES(payment_method), transaction_id = VALUES(transaction_id), status = 'paid', paid_at = NOW()")
                ->execute([$auctionId, $userId, $amount, $method, $transactionId]);
            return true;
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function hasDeposit($auctionId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, amount, status FROM auction_deposits WHERE auction_id = ? AND user_id = ? AND status = 'paid'");
            $stmt->execute([$auctionId, $userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }

    public function processEndingAuctions()
    {
        try {
            $tid = $this->tenantId();
            $sql = "SELECT id FROM auctions WHERE status = 'live' AND ends_at <= NOW()" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($tid > 1 ? [$tid] : []);
            $auctions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $ended = 0;
            foreach ($auctions as $a) {
                $this->endAuction($a['id']);
                $ended++;
            }
            return $ended;
        } catch (\Throwable $e) { return 0; }
    }

    public function getStats()
    {
        $stats = [
            'total_auctions' => 0,
            'live' => 0,
            'scheduled' => 0,
            'ended' => 0,
            'sold' => 0,
            'total_bids' => 0,
            'total_value' => 0,
            'unique_bidders' => 0
        ];
        try {
            $tid = $this->tenantId();
            $tsql = $this->tenantSql();
            $params = $tid > 1 ? [$tid] : [];

            $sql = "SELECT COUNT(*) FROM auctions" . $tsql;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
            $stats['total_auctions'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM auctions WHERE status = 'live'" . $tsql;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
            $stats['live'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM auctions WHERE status = 'scheduled'" . $tsql;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
            $stats['scheduled'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM auctions WHERE status = 'ended'" . $tsql;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
            $stats['ended'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM auctions WHERE status = 'sold'" . $tsql;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
            $stats['sold'] = (int)$stmt->fetchColumn();

            $sql = "SELECT COALESCE(SUM(winning_bid), 0) FROM auctions WHERE status = 'sold'" . $tsql;
            $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
            $stats['total_value'] = (float)$stmt->fetchColumn();
        } catch (\Throwable $e) { error_log("Auction stats: " . $e->getMessage()); }
        return $stats;
    }
}
