<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

class DifferentialCommissionCalculator
{
    protected $db;
    protected $ranks = [
        'Associate' => ['percent' => 6, 'team_percent' => 2, 'target' => 1000000],
        'Sr. Associate' => ['percent' => 8, 'team_percent' => 3, 'target' => 3500000],
        'BDM' => ['percent' => 10, 'team_percent' => 4, 'target' => 7000000],
        'Sr. BDM' => ['percent' => 12, 'team_percent' => 5, 'target' => 15000000],
        'Vice President' => ['percent' => 15, 'team_percent' => 6, 'target' => 30000000],
        'President' => ['percent' => 18, 'team_percent' => 7, 'target' => 50000000],
        'Site Manager' => ['percent' => 20, 'team_percent' => 8, 'target' => 100000000],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Calculate and distribute differential commission for a sale
     */
    public function calculate($saleAmount, $buyerUserId, $propertyId)
    {
        try {
            // 1. Get the sponsor (direct agent)
            $stmt = $this->db->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$buyerUserId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile || !$profile['sponsor_user_id']) {
                return ['success' => false, 'message' => 'No sponsor found'];
            }

            $currentAgentId = $profile['sponsor_user_id'];
            $distributedPercent = 0;
            $commissions = [];

            // 2. Traverse up the tree
            // Differential Logic: Senior gets (Senior % - Max % already distributed in their line)
            while ($currentAgentId) {
                $agentData = $this->getAgentRankData($currentAgentId);
                if (!$agentData) break;

                $agentPercent = $this->ranks[$agentData['rank']]['percent'] ?? 0;

                // Only pay if this agent has a higher percentage than what's already been distributed
                if ($agentPercent > $distributedPercent) {
                    $payablePercent = $agentPercent - $distributedPercent;
                    $amount = ($saleAmount * $payablePercent) / 100;

                    if ($amount > 0) {
                        $this->recordCommission($currentAgentId, $buyerUserId, $propertyId, $amount, $payablePercent, 'differential');
                        $commissions[] = [
                            'user_id' => $currentAgentId,
                            'rank' => $agentData['rank'],
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
        // For now, mapping rank from mlm_profiles or associate_levels
        $stmt = $this->db->prepare("SELECT current_level as rank FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function recordCommission($beneficiaryId, $sourceId, $propertyId, $amount, $percent, $type)
    {
        // 1. Insert into 'commissions' table (Primary for Mobile V2)
        $stmt = $this->db->prepare("
            INSERT INTO commissions (user_id, source_user_id, property_id, amount, percentage, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$beneficiaryId, $sourceId, $propertyId, $amount, $percent, $type]);

        // 2. Insert into 'mlm_commission_ledger' table (For Legacy Analytics/Reports)
        $stmt2 = $this->db->prepare("
            INSERT INTO mlm_commission_ledger 
            (beneficiary_user_id, source_user_id, commission_type, amount, level, property_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        // We'll calculate level later or just use 1 as placeholder for now as differential is rank-based
        $stmt2->execute([$beneficiaryId, $sourceId, $type, $amount, 1, $propertyId]);
    }
}
