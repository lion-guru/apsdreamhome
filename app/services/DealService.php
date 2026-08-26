<?php

namespace App\Services;

use App\Core\Database;

class DealService
{
    use \App\Traits\ServiceTenantTrait;
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDeals($filters = []) {
        try {
            $where = ["d.status != 'deleted'"];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(d.title LIKE ? OR l.name LIKE ?)";
                $s = '%' . $filters['search'] . '%';
                $params[] = $s; $params[] = $s;
            }
            if (!empty($filters['stage'])) {
                $where[] = "d.stage = ?";
                $params[] = $filters['stage'];
            }
            if (!empty($filters['assigned_to'])) {
                $where[] = "d.assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }
            if (!empty($filters['lead_id'])) {
                $where[] = "d.lead_id = ?";
                $params[] = $filters['lead_id'];
            }
            if (!empty($filters['min_value'])) {
                $where[] = "d.deal_value >= ?";
                $params[] = (float)$filters['min_value'];
            }
            if (!empty($filters['max_value'])) {
                $where[] = "d.deal_value <= ?";
                $params[] = (float)$filters['max_value'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = "d.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "d.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            $tid = $this->tenantId();
            if ($tid > 1) {
                $where[] = "d.tenant_id = ?";
                $params[] = $tid;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $page = max(1, $filters['page'] ?? 1);
            $perPage = min(100, max(10, $filters['per_page'] ?? 25));
            $offset = ($page - 1) * $perPage;
            $orderBy = $filters['sort'] ?? 'd.created_at';
            $orderDir = strtoupper($filters['direction'] ?? 'DESC');
            $allowedSort = ['d.created_at','d.updated_at','d.deal_value','d.stage','d.probability','d.expected_close_date','l.name','u.name'];
            if (!in_array($orderBy, $allowedSort)) $orderBy = 'd.created_at';

            $countStmt = $this->db->query("SELECT COUNT(*) as total FROM lead_deals d $whereClause", $params);
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            $sql = "SELECT d.*, l.name as lead_name, u.name as assigned_name
                    FROM lead_deals d
                    LEFT JOIN leads l ON l.id = d.lead_id
                    LEFT JOIN users u ON u.id = d.assigned_to
                    $whereClause
                    ORDER BY $orderBy $orderDir
                    LIMIT $offset, $perPage";

            $stmt = $this->db->query($sql, $params);
            $deals = $stmt->fetchAll() ?: [];

            return [
                'deals' => $deals,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int)ceil($total / $perPage),
            ];
        } catch (\Exception $e) {
            error_log('DealService::getDeals error: ' . $e->getMessage());
            return ['deals' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 0];
        }
    }

    public function getDealById($id) {
        try {
            $sql = "SELECT d.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                           u.name as assigned_name
                    FROM lead_deals d
                    LEFT JOIN leads l ON l.id = d.lead_id
                    LEFT JOIN users u ON u.id = d.assigned_to
                    WHERE d.id = ?";
            $params = [$id];
            $tid = $this->tenantId();
            if ($tid > 1) {
                $sql .= " AND d.tenant_id = ?";
                $params[] = $tid;
            }
            $stmt = $this->db->query($sql, $params);
            $deal = $stmt->fetch();
            if (!$deal) return null;

            $deal['activities'] = $this->getDealActivities($id);
            return $deal;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function createDeal($data) {
        try {
            $columns = "lead_id, title, stage, deal_value, probability, expected_close_date, assigned_to, created_by, created_at";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, NOW()";
            $values = [
                $data['lead_id'],
                $data['title'] ?? '',
                $data['stage'] ?? 'qualification',
                $data['deal_value'] ?? 0,
                $data['probability'] ?? 50,
                $data['expected_close_date'] ?? null,
                $data['assigned_to'] ?? null,
                $data['created_by'] ?? null,
            ];
            if ($this->tenantId() > 1) {
                $columns .= ", tenant_id";
                $placeholders .= ", ?";
                $values[] = $this->tenantId();
            }
            $stmt = $this->db->prepare("INSERT INTO lead_deals ({$columns}) VALUES ({$placeholders})");
            $stmt->execute($values);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('DealService::createDeal error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDeal($id, $data) {
        try {
            $allowed = ['title', 'stage', 'deal_value', 'probability', 'expected_close_date', 'assigned_to', 'notes'];
            $fields = [];
            $params = [];
            foreach ($allowed as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            if (empty($fields)) return false;
            $params[] = $id;
            $sql = "UPDATE lead_deals SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            if ($this->tenantId() > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $this->tenantId();
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function closeDeal($id, $won, $reason, $reasonDetail = '') {
        try {
            $stage = $won ? 'won' : 'lost';
            $notes = "Deal {$stage}: " . ($reason ?: 'No reason provided') . ($reasonDetail ? " — $reasonDetail" : '');
            $sql = "UPDATE lead_deals SET stage = ?, notes = ?, closed_at = NOW(), updated_at = NOW() WHERE id = ?";
            $params = [$stage, $notes, $id];
            if ($this->tenantId() > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $this->tenantId();
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteDeal($id) {
        try {
            $sql = "UPDATE lead_deals SET status = 'deleted', updated_at = NOW() WHERE id = ?";
            $params = [$id];
            if ($this->tenantId() > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $this->tenantId();
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getDealActivities($dealId, $limit = 50) {
        try {
$sql = "SELECT a.*, u.name as user_name FROM crm_interactions a
                      LEFT JOIN users u ON u.id = a.user_id
                      WHERE a.lead_id IN (SELECT lead_id FROM lead_deals WHERE id = ? AND status != 'deleted')";
            $params = [$dealId];
            $tid = $this->tenantId();
            if ($tid > 1) {
                $sql .= " AND a.tenant_id = ?";
                $params[] = $tid;
            }
            $sql .= " ORDER BY a.created_at DESC LIMIT ?";
            $params[] = $limit;
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPipelineValue() {
        try {
$sql = "SELECT stage, COUNT(*) as count, SUM(deal_value) as total_value
                      FROM lead_deals
                      WHERE status != 'deleted' AND stage NOT IN ('won', 'lost')";
            $params = [];
            if ($this->tenantId() > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $this->tenantId();
            }
            $sql .= " GROUP BY stage ORDER BY FIELD(stage, 'qualification', 'proposal', 'negotiation', 'closing')";
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getWeightedPipeline() {
        try {
$sql = "SELECT SUM(deal_value * probability / 100) as weighted_value
                      FROM lead_deals
                      WHERE status != 'deleted' AND stage NOT IN ('won', 'lost')";
            $params = [];
            if ($this->tenantId() > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $this->tenantId();
            }
            $stmt = $this->db->query($sql, $params);
            return (float)($stmt->fetch()['weighted_value'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getRevenueForecast($months = 6) {
        try {
            $forecast = [];
            for ($i = 0; $i < $months; $i++) {
                $monthStart = date('Y-m-01', strtotime("+$i months"));
                $monthEnd = date('Y-m-t', strtotime("+$i months"));

$sql = "SELECT SUM(deal_value * probability / 100) as forecast
                          FROM lead_deals
                          WHERE status != 'deleted'
                          AND stage NOT IN ('won', 'lost')
                          AND expected_close_date BETWEEN ? AND ?";
                $params = [$monthStart, $monthEnd];
                if ($this->tenantId() > 1) {
                    $sql .= " AND tenant_id = ?";
                    $params[] = $this->tenantId();
                }
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $forecast[] = [
                    'month' => date('Y-m', strtotime("+$i months")),
                    'forecast' => (float)($stmt->fetch()['forecast'] ?? 0),
                ];
            }
            return $forecast;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getWinLossStats() {
        try {
$sql1 = "SELECT notes, COUNT(*) as count, SUM(deal_value) as total_value
                       FROM lead_deals
                       WHERE status != 'deleted' AND stage = 'lost'";
            $params1 = [];
            if ($this->tenantId() > 1) {
                $sql1 .= " AND tenant_id = ?";
                $params1[] = $this->tenantId();
            }
            $sql1 .= " GROUP BY close_reason ORDER BY count DESC";
            $stmt = $this->db->query($sql1, $params1);
            $lossReasons = $stmt->fetchAll() ?: [];

$sql2 = "SELECT COUNT(*) as won_count, SUM(deal_value) as won_value
                       FROM lead_deals
                       WHERE status != 'deleted' AND stage = 'won'";
            $params2 = [];
            if ($this->tenantId() > 1) {
                $sql2 .= " AND tenant_id = ?";
                $params2[] = $this->tenantId();
            }
            $stmt = $this->db->query($sql2, $params2);
            $won = $stmt->fetch() ?: ['won_count' => 0, 'won_value' => 0];

$sql3 = "SELECT COUNT(*) as lost_count, SUM(deal_value) as lost_value
                       FROM lead_deals
                       WHERE status != 'deleted' AND stage = 'lost'";
            $params3 = [];
            if ($this->tenantId() > 1) {
                $sql3 .= " AND tenant_id = ?";
                $params3[] = $this->tenantId();
            }
            $stmt = $this->db->query($sql3, $params3);
            $lost = $stmt->fetch() ?: ['lost_count' => 0, 'lost_value' => 0];

            return [
                'won' => $won,
                'lost' => $lost,
                'loss_reasons' => $lossReasons,
            ];
        } catch (\Exception $e) {
            return ['won' => ['won_count' => 0, 'won_value' => 0], 'lost' => ['lost_count' => 0, 'lost_value' => 0], 'loss_reasons' => []];
        }
    }

    public function getStages() {
        return [
            'qualification' => 'Qualification',
            'proposal' => 'Proposal',
            'negotiation' => 'Negotiation',
            'closing' => 'Closing',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost',
        ];
    }

    public function getCloseReasons() {
        return [
            'price' => 'Price/Budget',
            'competitor' => 'Chose Competitor',
            'timing' => 'Timing Not Right',
            'budget' => 'Budget Constraints',
            'product' => 'Product Fit',
            'authority' => 'No Decision Authority',
            'no_response' => 'No Response',
            'other' => 'Other',
        ];
    }
}