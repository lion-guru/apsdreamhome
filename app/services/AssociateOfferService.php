<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;

/**
 * Associate/agent offer campaigns (festival & season sale boosts).
 * Admin creates drafts, activates them; associates/agents see live offers
 * with their own progress toward each offer's criteria.
 */
class AssociateOfferService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    public function createOffer(array $data, int $createdBy): array
    {
        $title = trim($data['title'] ?? '');
        if ($title === '') return ['success' => false, 'message' => 'Title required'];
        $rewardType = in_array($data['reward_type'] ?? '', ['bonus_amount', 'commission_boost_pct', 'gift'], true) ? $data['reward_type'] : 'bonus_amount';
        $criteriaType = in_array($data['criteria_type'] ?? '', ['sale_volume', 'booking_count'], true) ? $data['criteria_type'] : 'sale_volume';
        $starts = $data['starts_at'] ?? date('Y-m-d');
        $ends = $data['ends_at'] ?? date('Y-m-d', strtotime('+30 days'));
        if ($ends < $starts) return ['success' => false, 'message' => 'End date must be on/after start date'];
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("
            INSERT INTO associate_offers (tenant_id, title, description, reward_type, reward_value, criteria_type, criteria_value, colony_id, starts_at, ends_at, status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, NOW())
        ");
        $stmt->execute([$tid, $title, trim($data['description'] ?? ''), $rewardType, (float)($data['reward_value'] ?? 0), $criteriaType, (float)($data['criteria_value'] ?? 0), (int)($data['colony_id'] ?? 0) ?: null, $starts, $ends, $createdBy]);
        return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    public function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['draft', 'active', 'closed'], true)) return false;
        $stmt = $this->pdo->prepare("UPDATE associate_offers SET status=? WHERE id=? AND tenant_id=?");
        $stmt->execute([$status, $id, $this->tenantId()]);
        return $stmt->rowCount() > 0;
    }

    public function deleteOffer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM associate_offers WHERE id=? AND status='draft' AND tenant_id=?");
        $stmt->execute([$id, $this->tenantId()]);
        return $stmt->rowCount() > 0;
    }

    public function listOffers(bool $includeExpired = true): array
    {
        $sql = "SELECT o.*, c.name AS colony_name FROM associate_offers o LEFT JOIN colonies c ON c.id=o.colony_id WHERE o.tenant_id=? ";
        if (!$includeExpired) $sql .= "AND o.status='active' AND CURDATE() BETWEEN o.starts_at AND o.ends_at ";
        $sql .= "ORDER BY o.starts_at DESC, o.id DESC";
        return $this->fetchAll($sql, [$this->tenantId()]);
    }

    /**
     * Live offers visible to an associate/agent, each with their progress.
     * NOTE: plot_bookings.associate_id stores users.id (portal session id),
     * NOT associates.id — see Admin\BookingController JOIN users.
     */
    public function visibleOffers(int $userId): array
    {
        $offers = $this->listOffers(false);
        foreach ($offers as &$o) {
            $o['progress'] = $this->progressFor($userId, $o);
            $target = (float)($o['criteria_value'] ?? 0);
            $o['achieved'] = $target > 0 && (float)$o['progress']['value'] >= $target;
        }
        return $offers;
    }

    /**
     * Associate's progress toward one offer from plot bookings.
     */
    public function progressFor(int $userId, array $offer): array
    {
        $start = $offer['starts_at'] ?? '2000-01-01';
        $end = $offer['ends_at'] ?? date('Y-m-d');
        $colonySql = !empty($offer['colony_id']) ? " AND pb.colony_id = " . (int)$offer['colony_id'] : "";
        $row = $this->fetchOne("
            SELECT COUNT(*) AS n, COALESCE(SUM(pb.booking_amount), 0) AS vol
            FROM plot_bookings pb
            WHERE pb.associate_id = ? AND pb.created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
            AND pb.status NOT IN ('cancelled', 'defaulted', 'transferred') $colonySql
        ", [$userId, $start, $end]);
        if (($offer['criteria_type'] ?? '') === 'booking_count') {
            return ['value' => (int)($row['n'] ?? 0), 'unit' => 'bookings'];
        }
        return ['value' => round((float)($row['vol'] ?? 0), 2), 'unit' => 'Rs'];
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('AssociateOfferService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('AssociateOfferService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }
}
