<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Middleware\TenantContext;
use App\Traits\TenantAwareTrait;

class MobileAgentApiController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function analytics()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $stats = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT 
                        COUNT(*) as total_leads,
                        SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
                        SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost
                    FROM leads WHERE assigned_to = ?{$tidSql}
                ");
                $stmt->execute($params);
                $stats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent analytics error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $stats]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function bookings()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND pb.tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $bookings = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT pb.*, p.title as property_title, p.price
                    FROM plot_bookings pb
                    LEFT JOIN plots p ON p.id = pb.plot_id
                    WHERE pb.agent_id = ?{$tidSql}
                    ORDER BY pb.created_at DESC
                ");
                $stmt->execute($params);
                $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent bookings error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $bookings]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function commissions()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $commissions = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM mlm_commission_ledger
                    WHERE user_id = ?{$tidSql}
                    ORDER BY created_at DESC
                ");
                $stmt->execute($params);
                $commissions = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent commissions error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $commissions]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function documents()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $documents = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM agent_documents
                    WHERE agent_id = ?{$tidSql}
                    ORDER BY created_at DESC
                ");
                $stmt->execute($params);
                $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent documents error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $documents]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function followUps()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $followUps = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM crm_tasks
                    WHERE assigned_to = ?{$tidSql}
                    AND status != 'completed'
                    ORDER BY due_date ASC
                ");
                $stmt->execute($params);
                $followUps = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent follow-ups error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $followUps]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function leads()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $leads = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT l.*, u.name as customer_name
                    FROM leads l
                    LEFT JOIN users u ON u.id = l.customer_id
                    WHERE l.assigned_to = ?{$tidSql}
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute($params);
                $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent leads error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $leads]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function payouts()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $payouts = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM payout_entries
                    WHERE user_id = ?{$tidSql}
                    ORDER BY created_at DESC
                ");
                $stmt->execute($params);
                $payouts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent payouts error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $payouts]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function properties()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $properties = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT p.*, c.name as colony_name
                    FROM user_properties p
                    LEFT JOIN colonies c ON c.id = p.colony_id
                    WHERE p.agent_id = ?{$tidSql} AND p.deleted_at IS NULL
                    ORDER BY p.created_at DESC
                ");
                $stmt->execute($params);
                $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent properties error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $properties]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function siteVisits()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $visits = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT sv.*, l.name as lead_name, l.phone as lead_phone
                    FROM site_visits sv
                    LEFT JOIN leads l ON l.id = sv.lead_id
                    WHERE sv.agent_id = ?{$tidSql}
                    ORDER BY sv.scheduled_at DESC
                ");
                $stmt->execute($params);
                $visits = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent site visits error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $visits]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function myTeam()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $team = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT u.*, mnt.rank as current_rank
                    FROM users u
                    LEFT JOIN mlm_network_tree mnt ON mnt.associate_id = u.id
                    WHERE mnt.parent_id = ?{$tidSql} AND u.tenant_id = u.tenant_id
                    ORDER BY u.created_at DESC
                ");
                $stmt->execute($params);
                $team = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent my team error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $team]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function rankProgress()
    {
        try {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $progress = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT 
                        r.name as current_rank,
                        r.required_gbv as next_rank_gbv,
                        r.gbv_threshold as current_rank_gbv,
                        COALESCE(SUM(CASE WHEN pb.status = 'confirmed' THEN pb.total_plot_value ELSE 0 END), 0) as current_gbv
                    FROM users u
                    LEFT JOIN mlm_rank_benefits r ON r.name = u.rank
                    LEFT JOIN plot_bookings pb ON pb.agent_id = u.id
                    WHERE u.id = ?{$tidSql}
                    GROUP BY u.id, r.name, r.required_gbv, r.gbv_threshold
                ");
                $stmt->execute($params);
                $progress = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Agent rank progress error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $progress]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }
}