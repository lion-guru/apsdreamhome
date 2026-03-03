<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

class CommissionCalculator
{
    private $conn;
    private $notifier;

    public function __construct(NotificationService $notifier)
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->notifier = $notifier;
    }

    public function calculatePropertyCommission($property_id, $sale_amount, $buyer_user_id, $plan_type = 'legacy')
    {
        if ($plan_type === 'legacy') {
            return $this->calculateLegacyCommission($property_id, $sale_amount, $buyer_user_id);
        }

        return $this->calculateStandardCommission($property_id, $sale_amount, $buyer_user_id);
    }

    private function calculateStandardCommission($property_id, $sale_amount, $buyer_user_id)
    {
        // Original 5-level structure logic
        // This is a placeholder for the standard logic if it differs from legacy
        // For now, assuming legacy is the primary one requested
        return $this->calculateLegacyCommission($property_id, $sale_amount, $buyer_user_id);
    }

    private function calculateLegacyCommission($property_id, $sale_amount, $buyer_user_id)
    {
        if ($sale_amount <= 0) return ['success' => false, 'error' => 'Invalid sale amount'];

        // Get buyer's sponsor
        $stmt = $this->conn->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$buyer_user_id]);
        $buyer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$buyer || empty($buyer['sponsor_user_id'])) {
            return ['success' => false, 'error' => 'No sponsor found for buyer'];
        }

        $this->conn->beginTransaction();
        try {
            $total_commission = 0;
            $commissions = [];

            // 1. Direct Referral Bonus (Level 1)
            $sponsor_id = $buyer['sponsor_user_id'];

            // Get Sponsor's Level Data
            $stmt = $this->conn->prepare("
                SELECT mp.user_id, mp.current_level, al.commission_percent, al.direct_referral_bonus
                FROM mlm_profiles mp
                LEFT JOIN associate_levels al ON mp.current_level = al.name
                WHERE mp.user_id = ?
            ");
            $stmt->execute([$sponsor_id]);
            $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sponsor) {
                // Direct Referral Bonus Logic
                // Use direct_referral_bonus from table, default to 1% if not found
                $percentage = $sponsor['direct_referral_bonus'] ?? 1.0;
                $commission_amount = ($sale_amount * $percentage) / 100;

                if ($commission_amount > 0) {
                    $this->recordCommission($sponsor['user_id'], $buyer_user_id, $property_id, $sale_amount, $commission_amount, $percentage, 1, 'direct_bonus');
                    $total_commission += $commission_amount;
                    $commissions[] = ['user_id' => $sponsor['user_id'], 'amount' => $commission_amount, 'level' => 1, 'type' => 'direct_bonus'];
                }
            }

            // 2. Level/Override Bonuses (Uplines)
            // Traverse up the tree for level bonuses
            $current_descendant_id = $buyer_user_id;
            $level = 1;
            $max_levels = 10; // Cap at 10 levels for safety

            while ($level <= $max_levels) {
                // Find ancestor using network tree or recursive lookup
                // Using mlm_network_tree for efficiency if populated
                $stmt = $this->conn->prepare("
                    SELECT mp.user_id, mp.current_level, al.level_bonus
                    FROM mlm_network_tree nt
                    JOIN mlm_profiles mp ON nt.ancestor_user_id = mp.user_id
                    LEFT JOIN associate_levels al ON mp.current_level = al.name
                    WHERE nt.descendant_user_id = ? AND nt.level = ?
                ");
                $stmt->execute([$buyer_user_id, $level]);
                $ancestor = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$ancestor) break;

                // Apply Level Bonus if eligible
                $level_bonus_pct = $ancestor['level_bonus'] ?? 0;

                if ($level_bonus_pct > 0) {
                    $bonus_amount = ($sale_amount * $level_bonus_pct) / 100;
                    $this->recordCommission($ancestor['user_id'], $buyer_user_id, $property_id, $sale_amount, $bonus_amount, $level_bonus_pct, $level, 'level_bonus');
                    $total_commission += $bonus_amount;
                    $commissions[] = ['user_id' => $ancestor['user_id'], 'amount' => $bonus_amount, 'level' => $level, 'type' => 'level_bonus'];
                }

                $level++;
            }

            $this->conn->commit();
            return ['success' => true, 'total_commission' => $total_commission, 'commissions' => $commissions];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function recordCommission($beneficiary_id, $source_id, $property_id, $sale_amount, $amount, $percentage, $level, $type)
    {
        // Create commission record
        $insertStmt = $this->conn->prepare("
            INSERT INTO mlm_commission_ledger 
            (beneficiary_user_id, source_user_id, commission_type, amount, level, property_id, sale_amount, commission_percentage, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $insertStmt->execute([
            $beneficiary_id,
            $source_id,
            $type,
            $amount,
            $level,
            $property_id,
            $sale_amount,
            $percentage
        ]);

        // Notify beneficiary
        $this->notifier->notifyUser(
            $beneficiary_id,
            "New Commission Earned!",
            "You have earned a commission of " . number_format($amount, 2) . " from a level $level sale."
        );
    }
}
