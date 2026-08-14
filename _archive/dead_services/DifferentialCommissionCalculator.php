<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

class DifferentialCommissionCalculator
{
    protected $db;
    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Load commission percentage for a given rank from mlm_levels table
     */
    protected function getRankPercent($level): int
    {
        try {
            $stmt = $this->db->prepare("SELECT direct_commission FROM mlm_levels WHERE level_number = ? LIMIT 1");
            $stmt->execute([$level]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (int)$row['direct_commission'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Load rank name for a given level
     */
    protected function getRankName($level): string
    {
        try {
            $stmt = $this->db->prepare("SELECT level_name FROM mlm_levels WHERE level_number = ? LIMIT 1");
            $stmt->execute([$level]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? $row['level_name'] : 'Associate';
        } catch (\Exception $e) {
            return 'Associate';
        }
    }

    /**
     * Calculate and distribute differential commission for a sale
     * @param int $saleAmount Total sale amount
     * @param int $buyerUserId Customer who made the purchase
     * @param int $propertyId Property/plot ID being sold
     * @param int|null $associateId Optional: direct associate for the sale (overrides sponsor lookup)
     */
    public function calculate($saleAmount, $buyerUserId, $propertyId, $associateId = null)
    {
        try {
            // 1. Determine the starting agent
            if ($associateId && $associateId > 0) {
                // Direct associate assigned to this booking â€” use them as the starting agent
                $currentAgentId = (int)$associateId;
            } else {
                // Fallback: look up the buyer's sponsor in mlm_profiles
                $stmt = $this->db->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
                $stmt->execute([$buyerUserId]);
                $profile = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$profile || !$profile['sponsor_user_id']) {
                    return ['success' => false, 'message' => 'No sponsor found'];
                }
                $currentAgentId = $profile['sponsor_user_id'];
            }
            $distributedPercent = 0;
            $commissions = [];

            // 2. Traverse up the tree
            // Differential Logic: Senior gets (Senior % - Max % already distributed in their line)
            while ($currentAgentId) {
                $agentData = $this->getAgentRankData($currentAgentId);
                if (!$agentData) break;

                $agentLevel = (int)($agentData['level'] ?? $agentData['current_level'] ?? 1);
                $agentPercent = $this->getRankPercent($agentLevel);

                // Only pay if this agent has a higher percentage than what's already been distributed
                if ($agentPercent > $distributedPercent) {
                    $payablePercent = $agentPercent - $distributedPercent;
                    $amount = ($saleAmount * $payablePercent) / 100;

                    if ($amount > 0) {
                        $this->recordCommission($currentAgentId, $buyerUserId, $propertyId, $amount, $payablePercent, 'differential');
                        $commissions[] = [
                            'user_id' => $currentAgentId,
                            'rank' => $this->getRankName($agentLevel),
                            'amount' => $amount,
                            'percent' => $payablePercent
                        ];
                    }

                    $distributedPercent = $agentPercent;
                }

                // Move up to the next sponsor
                $stmt = $this->db->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
                $stmt->execute([$currentAgentId]);
                $nextSponsor = $stmt->fetch(PDO::FETCH_ASSOC);
                $currentAgentId = $nextSponsor['sponsor_user_id'] ?? null;

                // Stop if we hit 20% (Site Manager)
                if ($distributedPercent >= 20) break;
            }

            return [
                'success' => true,
                'total_distributed' => $distributedPercent,
                'commissions' => $commissions
            ];
        } catch (Exception $e) {
            error_log("MLM Calculation Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getAgentRankData($userId)
    {
        // Get level from mlm_profiles â€” current_level stores rank name (e.g. 'associate')
        $stmt = $this->db->prepare("SELECT current_level FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        // Map rank name to numeric level (1-7)
        $rankMap = [
            'associate' => 1, 'senior_associate' => 2, 'bdm' => 3,
            'sr_bdm' => 4, 'vice_president' => 5, 'president' => 6, 'site_manager' => 7,
        ];
        $level = $rankMap[strtolower($row['current_level'] ?? '')] ?? 1;
        return ['level' => $level];
    }

    protected function recordCommission($beneficiaryId, $sourceId, $propertyId, $amount, $percent, $type)
    {
        // 1. Insert into 'commissions' table (Primary for Mobile V2)
        $stmt = $this->db->prepare("
            INSERT INTO commissions (user_id, associate_id, source_user_id, source_associate_id, property_id, amount, percentage, commission_type, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$beneficiaryId, $beneficiaryId, $sourceId, $sourceId, $propertyId, $amount, $percent, $type, $type]);

        // 2. Insert into 'mlm_commission_ledger' table (For Legacy Analytics/Reports)
        $stmt2 = $this->db->prepare("
            INSERT INTO mlm_commission_ledger 
            (beneficiary_user_id, source_user_id, commission_type, amount, level, property_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        // We'll calculate level later or just use 1 as placeholder for now as differential is rank-based
        $stmt2->execute([$beneficiaryId, $sourceId, $type, $amount, 1, $propertyId]);
    }
}?>