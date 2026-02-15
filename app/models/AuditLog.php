<?php
namespace App\Models;

use App\Models\Model;

/**
 * AuditLog Model
 * Handles all audit logging database operations
 */
class AuditLog extends Model
{
    public static $table = 'audit_log';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id', 'changes', 'ip_address', 'created_at'
    ];

    /**
     * Create a new audit log entry
     */
    public function log($userId, $action, $entityType, $entityId, $changes = null)
    {
        return static::query()->insert([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => !\is_null($changes) ? \json_encode($changes) : null,
            'ip_address' => $this->getClientIp(),
            'created_at' => \date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all audit logs with filters
     */
    public function getAll($filters = [], $limit = 50, $offset = 0)
    {
        $query = static::query()
            ->select(['al.*', 'u.auser as user_name'])
            ->from(static::$table . ' as al')
            ->leftJoin('admin as u', 'al.user_id', '=', 'u.id');

        if (!empty($filters['user_id'])) {
            $query->where('al.user_id', '=', $filters['user_id']);
        }
        if (!empty($filters['action'])) {
            $query->where('al.action', '=', $filters['action']);
        }
        if (!empty($filters['entity_type'])) {
            $query->where('al.entity_type', '=', $filters['entity_type']);
        }
        if (!empty($filters['entity_id'])) {
            $query->where('al.entity_id', '=', $filters['entity_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('al.created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('al.created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('al.created_at', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get history for a specific entity
     */
    public function getEntityHistory($entityType, $entityId)
    {
        return static::query()
            ->select(['al.*', 'u.auser as user_name'])
            ->from(static::$table . ' as al')
            ->leftJoin('admin as u', 'al.user_id', '=', 'u.id')
            ->where('al.entity_type', '=', $entityType)
            ->where('al.entity_id', '=', $entityId)
            ->orderBy('al.created_at', 'DESC')
            ->get();
    }

    /**
     * Get activity for a specific user
     */
    public function getUserActivity($userId, $limit = 50)
    {
        return static::query()
            ->select(['al.*', 'u.auser as user_name'])
            ->from(static::$table . ' as al')
            ->leftJoin('admin as u', 'al.user_id', '=', 'u.id')
            ->where('al.user_id', '=', $userId)
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get distinct action types
     */
    public function getActionTypes()
    {
        return static::query()
            ->select(['DISTINCT action'])
            ->orderBy('action', 'ASC')
            ->get();
    }

    /**
     * Get distinct entity types
     */
    public function getEntityTypes()
    {
        return static::query()
            ->select(['DISTINCT entity_type'])
            ->orderBy('entity_type', 'ASC')
            ->get();
    }

    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? null;
        }
    }
}
