<?php

/**
 * LeadChurnPredictionService - Self-hosted AI churn prediction
 * ===============================================================
 * Predicts which leads will go cold / never convert, surfacing them
 * for early intervention. Zero external API dependency — uses only
 * existing lead fields:
 *
 *   lead_score, status, created_at, last_contacted_at,
 *   next_activity_date, last_activity_date, ai_score, priority,
 *   lead_category, source, budget, estimated_value
 *
 * Risk tiers:
 *   low      (< 25%)   → keep nurturing
 *   medium   (25-50%)  → send property recommendations
 *   high     (50-75%)  → phone call + follow-up within 24h
 *   critical (>= 75%)  → owner-escalation + immediate site-visit offer
 *
 * Writes to `lead_churn_predictions` (migration s619). Idempotent —
 * re-predicting a lead overwrites its prior row.
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;

class LeadChurnPredictionService
{
    use ServiceTenantTrait;

    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Predict churn risk for a single lead.
     *
     * Returns: ['lead_id', 'risk_level', 'risk_score', 'churn_probability',
     *           'next_action', 'factors', 'predicted_at']
     */
    public function predict(int $leadId): array
    {
        $lead = $this->getLead($leadId);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $factors = [];
        $score = 0.0;

        // ── Factor 1: Lead score (existing AI score) ──────────────────
        $leadScore = (int)($lead['lead_score'] ?? 0);
        if ($leadScore === 0) {
            $score += 30; $factors[] = 'No lead score assigned';
        } elseif ($leadScore < 30) {
            $score += 20; $factors[] = 'Low lead score (' . $leadScore . ')';
        } elseif ($leadScore < 60) {
            $score += 10; $factors[] = 'Moderate lead score (' . $leadScore . ')';
        } else {
            $factors[] = 'Good lead score (' . $leadScore . ')';
        }

        // ── Factor 2: Status (cold / lost / not_interested = high risk) ─
        $status = strtolower((string)($lead['status'] ?? ''));
        $riskyStatuses = ['not_interested', 'closed_lost', 'dead', 'cold', 'dormant'];
        if (in_array($status, $riskyStatuses)) {
            $score += 35; $factors[] = 'Status: ' . ucfirst($status);
        } elseif ($status === 'nurture') {
            $score += 25; $factors[] = 'Status: Nurture';
        } elseif ($status === 'follow_up') {
            $score += 15; $factors[] = 'Status: Follow-up pending';
        } elseif ($status === 'pending') {
            $score += 10; $factors[] = 'Status: Pending';
        } elseif (in_array($status, ['contacted', 'qualified', 'proposal', 'negotiation'])) {
            $factors[] = 'Active status: ' . ucfirst($status);
        }

        // ── Factor 3: Days since last contact (stale = high risk) ──────
        $lastContacted = $lead['last_contacted_at'] ?? null;
        if ($lastContacted) {
            $daysSinceContact = (time() - strtotime($lastContacted)) / 86400;
            if ($daysSinceContact > 30) {
                $score += 25; $factors[] = 'No contact for ' . (int)$daysSinceContact . ' days';
            } elseif ($daysSinceContact > 14) {
                $score += 15; $factors[] = 'No contact for ' . (int)$daysSinceContact . ' days';
            } elseif ($daysSinceContact > 7) {
                $score += 8; $factors[] = 'No contact for ' . (int)$daysSinceContact . ' days';
            } else {
                $factors[] = 'Recently contacted (' . (int)$daysSinceContact . ' days ago)';
            }
        } else {
            $score += 15; $factors[] = 'Never contacted';
        }

        // ── Factor 4: Missed next_activity_date (overdue follow-up) ─────
        $nextActivity = $lead['next_activity_date'] ?? null;
        if ($nextActivity && strtotime($nextActivity) < time()) {
            $daysOverdue = (time() - strtotime($nextActivity)) / 86400;
            $score += 20; $factors[] = 'Follow-up overdue by ' . (int)$daysOverdue . ' days';
        }

        // ── Factor 5: AI score (ai_score column) ───────────────────────
        $aiScore = (int)($lead['ai_score'] ?? 0);
        if ($aiScore > 0 && $aiScore < 40) {
            $score += 15; $factors[] = 'Low AI score (' . $aiScore . ')';
        }

        // ── Factor 6: Lead age (older un-converted = higher risk) ──────
        $created = strtotime($lead['created_at'] ?? 'now');
        $daysOld = (time() - $created) / 86400;
        if ($daysOld > 90 && !in_array($status, ['closed_won', 'registration_done'])) {
            $score += 15; $factors[] = 'Lead is ' . (int)$daysOld . ' days old';
        } elseif ($daysOld > 30) {
            $score += 8; $factors[] = 'Lead is ' . (int)$daysOld . ' days old';
        }

        // ── Factor 7: Priority ─────────────────────────────────────────
        $priority = strtolower((string)($lead['priority'] ?? 'medium'));
        if ($priority === 'low') {
            $score += 10; $factors[] = 'Low priority';
        }

        // ── Clamp & classify ───────────────────────────────────────────
        $churnProbability = min(100, round($score, 1));
        $riskLevel = $this->classifyRisk($churnProbability);

        $nextAction = $this->nextAction($riskLevel, $lead);

        // ── Persist (upsert) ───────────────────────────────────────────
        $this->savePrediction($leadId, $riskLevel, $churnProbability, $nextAction, $factors);

        return [
            'success' => true,
            'lead_id' => $leadId,
            'risk_level' => $riskLevel,
            'risk_score' => $churnProbability,
            'churn_probability' => $churnProbability,
            'next_action' => $nextAction,
            'factors' => $factors,
            'predicted_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Predict churn for ALL leads (batch).
     * Skips already-converted / closed_won leads.
     */
    public function predictAll(int $limit = 500): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND l.tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $sql = "
            SELECT l.id FROM leads l
            WHERE l.deleted_at IS NULL{$tenantFilter}
              AND l.status NOT IN ('closed_won', 'registration_done', 'closed_lost')
            ORDER BY l.last_contacted_at IS NULL, l.created_at ASC
            LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $results = ['predicted' => 0, 'skipped' => 0, 'leads' => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $res = $this->predict((int)$row['id']);
            if ($res['success']) {
                $results['predicted']++;
                $results['leads'][] = [
                    'lead_id' => $leadId = (int)$row['id'],
                    'risk_level' => $res['risk_level'],
                    'risk_score' => $res['risk_score'],
                    'next_action' => $res['next_action'],
                ];
            } else {
                $results['skipped']++;
            }
        }
        return $results;
    }

    /**
     * Get all high/critical risk leads for the Morning Brief dashboard.
     */
    public function getHighRiskLeads(int $limit = 20): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND p.tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $sql = "
            SELECT p.*, l.name, l.phone, l.email, l.source, l.status,
                   l.lead_score, l.ai_score, l.budget,
                   u.name as assigned_to_name
            FROM lead_churn_predictions p
            JOIN leads l ON l.id = p.lead_id
            LEFT JOIN users u ON u.id = l.assigned_to
            WHERE p.risk_level IN ('high', 'critical'){$tenantFilter}
            ORDER BY p.risk_score DESC
            LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get churn stats summary for dashboard widgets.
     */
    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $total = (int)($this->db->fetchOne(
            "SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL{$tenantFilter}", $params
        )['COUNT(*)'] ?? 0);

        $byLevel = [];
        foreach (['low', 'medium', 'high', 'critical'] as $level) {
            $row = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM lead_churn_predictions
                 WHERE risk_level = ?{$tenantFilter}",
                array_merge([$level], $params)
            );
            $byLevel[$level] = (int)($row['cnt'] ?? 0);
        }

        $avgRisk = (float)($this->db->fetchOne(
            "SELECT AVG(risk_score) as avg FROM lead_churn_predictions{$tenantFilter}", $params
        )['avg'] ?? 0);

        return [
            'total_leads' => $total,
            'total_predictions' => array_sum($byLevel),
            'by_level' => $byLevel,
            'high_critical' => ($byLevel['high'] ?? 0) + ($byLevel['critical'] ?? 0),
            'avg_risk_score' => round($avgRisk, 1),
        ];
    }

    /* ─── private helpers ─── */

    private function getLead(int $leadId): ?array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND l.tenant_id = ?" : "";
        $params = $tid > 1 ? [$leadId, $tid] : [$leadId];

        $sql = "SELECT * FROM leads l WHERE l.id = ?{$tenantFilter}
                AND l.deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function classifyRisk(float $probability): string
    {
        if ($probability >= 75) return 'critical';
        if ($probability >= 50) return 'high';
        if ($probability >= 25) return 'medium';
        return 'low';
    }

    private function nextAction(string $riskLevel, array $lead): string
    {
        return match ($riskLevel) {
            'critical' => 'Owner escalation + immediate site-visit offer',
            'high' => 'Phone call + follow-up within 24 hours',
            'medium' => 'Send property recommendations + check-in call',
            'low' => 'Keep nurturing (newsletter / market updates)',
            default => 'Keep nurturing',
        };
    }

    private function savePrediction(int $leadId, string $riskLevel, float $riskScore, string $nextAction, array $factors): void
    {
        $tid = $this->tenantId();
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);

        $columns = array_merge(
            ['lead_id', 'risk_level', 'risk_score', 'churn_probability',
             'next_action', 'factors'],
            $tenantCols
        );
        $values = array_merge(
            [$leadId, $riskLevel, $riskScore, $riskScore,
             $nextAction, json_encode($factors)],
            $tenantVals
        );

        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        // Upsert: update on duplicate lead_id
        $sql = "INSERT INTO lead_churn_predictions ($colStr) VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE
                  risk_level = VALUES(risk_level),
                  risk_score = VALUES(risk_score),
                  churn_probability = VALUES(churn_probability),
                  next_action = VALUES(next_action),
                  factors = VALUES(factors),
                  predicted_at = CURRENT_TIMESTAMP";
        $this->db->prepare($sql)->execute($values);
    }
}
