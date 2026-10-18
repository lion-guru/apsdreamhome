<?php
namespace App\Services;

use PDO;
use Exception;
use App\Core\Database\Database;

/**
 * NotificationArchiveService
 * Handles automated and manual archiving of read notifications older than X days.
 */
class NotificationArchiveService
{
    private $db;
    private PDO $pdo;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
        if (is_object($this->db) && method_exists($this->db, 'getConnection')) {
            $this->pdo = $this->db->getConnection();
        } elseif (is_object($this->db) && method_exists($this->db, 'getPdo')) {
            $this->pdo = $this->db->getPdo();
        } else {
            $this->pdo = $this->db;
        }
    }

    /**
     * Archive read notifications older than $days.
     * Transaction-safe, batch-oriented.
     *
     * @param int $days Number of days (default 90)
     * @param int $batchSize Number of records per batch (default 500)
     * @return array{archived_count: int, oldest_date: ?string, newest_date: ?string, message: string}
     */
    public function archiveOldNotifications(int $days = 90, int $batchSize = 500): array
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $totalArchived = 0;
        $oldestDate = null;
        $newestDate = null;

        try {
            // Find IDs of read notifications older than cutoff
            $selectStmt = $this->pdo->prepare(
                "SELECT id, created_at FROM notifications 
                 WHERE is_read = 1 AND created_at < :cutoff
                 ORDER BY created_at ASC
                 LIMIT :limit"
            );
            $selectStmt->bindValue(':cutoff', $cutoffDate);
            $selectStmt->bindValue(':limit', $batchSize, PDO::PARAM_INT);
            $selectStmt->execute();

            $candidates = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($candidates)) {
                return [
                    'archived_count' => 0,
                    'oldest_date'    => null,
                    'newest_date'    => null,
                    'message'        => 'No eligible read notifications older than ' . $days . ' days found.'
                ];
            }

            $ids = array_column($candidates, 'id');
            $oldestDate = $candidates[0]['created_at'] ?? null;
            $newestDate = end($candidates)['created_at'] ?? null;

            $this->pdo->beginTransaction();

            // Insert into archive table
            $inPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            $insertSql = "INSERT IGNORE INTO notifications_archive 
                (id, tenant_id, user_id, type, title, message, status, is_read, priority, related_id, related_type, action_url, template_key, template_data, created_at, read_at, is_important, expires_at, updated_at, archived_at)
                SELECT id, tenant_id, user_id, type, title, message, status, is_read, priority, related_id, related_type, action_url, template_key, template_data, created_at, read_at, is_important, expires_at, updated_at, NOW()
                FROM notifications
                WHERE id IN ($inPlaceholders)";

            $insertStmt = $this->pdo->prepare($insertSql);
            $insertStmt->execute($ids);

            // Delete from active notifications
            $deleteSql = "DELETE FROM notifications WHERE id IN ($inPlaceholders)";
            $deleteStmt = $this->pdo->prepare($deleteSql);
            $deleteStmt->execute($ids);

            $this->pdo->commit();
            $totalArchived = count($ids);

            return [
                'archived_count' => $totalArchived,
                'oldest_date'    => $oldestDate,
                'newest_date'    => $newestDate,
                'message'        => "Successfully archived {$totalArchived} read notifications older than {$days} days."
            ];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('NotificationArchiveService::archiveOldNotifications error: ' . $e->getMessage());
            throw new Exception('Archiving failed: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve statistics about active vs archived notifications.
     */
    public function getArchiveStats(): array
    {
        try {
            $activeStmt = $this->pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread, SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count FROM notifications");
            $activeRow = $activeStmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'unread' => 0, 'read_count' => 0];

            $archiveStmt = $this->pdo->query("SELECT COUNT(*) as total, MIN(created_at) as oldest, MAX(archived_at) as last_archived FROM notifications_archive");
            $archiveRow = $archiveStmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'oldest' => null, 'last_archived' => null];

            // Count eligible for 90-day archive
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-90 days"));
            $eligibleStmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 1 AND created_at < ?");
            $eligibleStmt->execute([$cutoffDate]);
            $eligibleCount = (int)($eligibleStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

            return [
                'active_total'       => (int)($activeRow['total'] ?? 0),
                'active_unread'      => (int)($activeRow['unread'] ?? 0),
                'active_read'        => (int)($activeRow['read_count'] ?? 0),
                'eligible_for_archive'=> $eligibleCount,
                'archive_total'      => (int)($archiveRow['total'] ?? 0),
                'oldest_archived'    => $archiveRow['oldest'],
                'last_archived_at'   => $archiveRow['last_archived'],
            ];
        } catch (\Throwable $e) {
            error_log('NotificationArchiveService::getArchiveStats error: ' . $e->getMessage());
            return [
                'active_total' => 0, 'active_unread' => 0, 'active_read' => 0,
                'eligible_for_archive' => 0, 'archive_total' => 0,
                'oldest_archived' => null, 'last_archived_at' => null
            ];
        }
    }

    /**
     * Restore an archived notification back to the active table.
     */
    public function restoreFromArchive(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $insertStmt = $this->pdo->prepare(
                "INSERT IGNORE INTO notifications 
                (id, tenant_id, user_id, type, title, message, status, is_read, priority, related_id, related_type, action_url, template_key, template_data, created_at, read_at, is_important, expires_at, updated_at)
                SELECT id, tenant_id, user_id, type, title, message, status, is_read, priority, related_id, related_type, action_url, template_key, template_data, created_at, read_at, is_important, expires_at, updated_at
                FROM notifications_archive WHERE id = ?"
            );
            $insertStmt->execute([$id]);

            $delStmt = $this->pdo->prepare("DELETE FROM notifications_archive WHERE id = ?");
            $delStmt->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('NotificationArchiveService::restoreFromArchive error: ' . $e->getMessage());
            return false;
        }
    }
}
