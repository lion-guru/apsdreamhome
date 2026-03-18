<?php

namespace App\Services\MLM;

use App\Core\Database;
use Exception;

/**
 * Multi-Level Commission Service
 * Handles commission calculation and distribution for associates
 */
class CommissionService
{
    private $db;
    
    // Commission rates by level
    private const COMMISSION_RATES = [
        1 => 5.0,  // Level 1: 5% Direct commission
        2 => 3.0,  // Level 2: 3% Team commission
        3 => 2.0,  // Level 3: 2% Network commission
        4 => 1.0,  // Level 4: 1% Organization commission
        5 => 0.5   // Level 5: 0.5% Global commission
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Calculate and distribute commissions for a sale
     * @param float $saleAmount Sale amount
     * @param int $associateId Associate who made the sale
     * @param int $customerId Customer ID
     * @param int $propertyId Property ID (if applicable)
     * @return array Commission distribution details
     */
    public function distributeCommissions($saleAmount, $associateId, $customerId, $propertyId = null)
    {
        try {
            $commissions = [];
            $totalDistributed = 0;
            
            // Get the upline hierarchy (up to 5 levels)
            $upline = $this->getUplineHierarchy($associateId, 5);
            
            foreach ($upline as $level => $associate) {
                $commissionRate = self::COMMISSION_RATES[$level] ?? 0;
                $commissionAmount = ($saleAmount * $commissionRate) / 100;
                
                if ($commissionAmount > 0) {
                    // Record commission
                    $commissionId = $this->recordCommission([
                        'associate_id' => $associate['id'],
                        'source_associate_id' => $associateId,
                        'customer_id' => $customerId,
                        'property_id' => $propertyId,
                        'sale_amount' => $saleAmount,
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'level' => $level,
                        'type' => $level === 1 ? 'direct' : 'team',
                        'status' => 'pending'
                    ]);
                    
                    $commissions[] = [
                        'level' => $level,
                        'associate_id' => $associate['id'],
                        'associate_name' => $associate['name'],
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'commission_id' => $commissionId
                    ];
                    
                    $totalDistributed += $commissionAmount;
                }
            }
            
            // Update associate statistics
            $this->updateAssociateStats($associateId, $saleAmount);
            
            return [
                'success' => true,
                'total_commission' => $totalDistributed,
                'commissions' => $commissions,
                'message' => "Commission distributed successfully"
            ];
            
        } catch (Exception $e) {
            error_log("Commission distribution error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => "Failed to distribute commissions",
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get upline hierarchy for an associate
     * @param int $associateId Starting associate ID
     * @param int $maxLevels Maximum levels to go up
     * @return array Upline associates by level
     */
    public function getUplineHierarchy($associateId, $maxLevels = 5)
    {
        $upline = [];
        $currentAssociateId = $associateId;
        
        for ($level = 1; $level <= $maxLevels; $level++) {
            // Get the sponsor of current associate
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.customer_id, u.referrer_id 
                FROM users u 
                WHERE u.id = ? AND u.role = 'associate' AND u.status = 'active'
            ");
            $stmt->execute([$currentAssociateId]);
            $currentAssociate = $stmt->fetch();
            
            if (!$currentAssociate || empty($currentAssociate['referrer_id'])) {
                break; // No more upline
            }
            
            // Get sponsor details
            $stmt = $this->db->prepare("
                SELECT id, name, customer_id, email, phone 
                FROM users 
                WHERE id = ? AND role = 'associate' AND status = 'active'
            ");
            $stmt->execute([$currentAssociate['referrer_id']]);
            $sponsor = $stmt->fetch();
            
            if ($sponsor) {
                $upline[$level] = $sponsor;
                $currentAssociateId = $currentAssociate['referrer_id'];
            } else {
                break; // Invalid sponsor
            }
        }
        
        return $upline;
    }
    
    /**
     * Record commission in database
     */
    private function recordCommission($commissionData)
    {
        $stmt = $this->db->prepare("
            INSERT INTO commissions (
                associate_id, source_associate_id, customer_id, property_id,
                sale_amount, commission_rate, commission_amount, level, type,
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $commissionData['associate_id'],
            $commissionData['source_associate_id'],
            $commissionData['customer_id'],
            $commissionData['property_id'],
            $commissionData['sale_amount'],
            $commissionData['commission_rate'],
            $commissionData['commission_amount'],
            $commissionData['level'],
            $commissionData['type'],
            $commissionData['status']
        ]);
        
        return $this->db->getLastInsertId();
    }
    
    /**
     * Update associate statistics after sale
     */
    private function updateAssociateStats($associateId, $saleAmount)
    {
        // Update personal sales
        $stmt = $this->db->prepare("
            UPDATE users SET 
                total_sales = COALESCE(total_sales, 0) + ?,
                last_sale_date = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$saleAmount, $associateId]);
        
        // Update team sales for all upline
        $upline = $this->getUplineHierarchy($associateId, 5);
        foreach ($upline as $associate) {
            $stmt = $this->db->prepare("
                UPDATE users SET 
                    team_sales = COALESCE(team_sales, 0) + ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$saleAmount, $associate['id']]);
        }
    }
    
    /**
     * Get associate commission summary
     */
    public function getCommissionSummary($associateId, $startDate = null, $endDate = null)
    {
        try {
            $whereClause = "WHERE c.associate_id = ?";
            $params = [$associateId];
            
            if ($startDate) {
                $whereClause .= " AND c.created_at >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $whereClause .= " AND c.created_at <= ?";
                $params[] = $endDate;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    c.level,
                    c.type,
                    COUNT(*) as total_commissions,
                    SUM(c.commission_amount) as total_amount,
                    AVG(c.commission_amount) as average_amount,
                    MAX(c.created_at) as last_commission_date
                FROM commissions c
                $whereClause
                GROUP BY c.level, c.type
                ORDER BY c.level
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Get commission summary error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get associate downline team structure
     */
    public function getDownlineTeam($associateId, $maxLevels = 4)
    {
        $team = [];
        
        for ($level = 1; $level <= $maxLevels; $level++) {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id, u.name, u.email, u.phone, u.customer_id,
                    u.created_at, u.total_sales, u.team_sales,
                    (SELECT COUNT(*) FROM users WHERE referrer_id = u.id AND role = 'associate') as direct_count
                FROM users u
                WHERE u.referrer_id = ? AND u.role = 'associate' AND u.status = 'active'
                ORDER BY u.created_at DESC
            ");
            
            $parentIds = $level === 1 ? [$associateId] : array_column($team[$level - 1] ?? [], 'id');
            
            if (empty($parentIds)) {
                break;
            }
            
            $placeholders = str_repeat('?,', count($parentIds) - 1) . '?';
            $stmt = $this->db->prepare("
                SELECT 
                    u.id, u.name, u.email, u.phone, u.customer_id,
                    u.created_at, u.total_sales, u.team_sales,
                    (SELECT COUNT(*) FROM users WHERE referrer_id = u.id AND role = 'associate' AND status = 'active') as direct_count
                FROM users u
                WHERE u.referrer_id IN ($placeholders) AND u.role = 'associate' AND u.status = 'active'
                ORDER BY u.created_at DESC
            ");
            
            $stmt->execute($parentIds);
            $team[$level] = $stmt->fetchAll();
        }
        
        return $team;
    }
    
    /**
     * Process commission payments (mark as paid)
     */
    public function processCommissionPayments($commissionIds)
    {
        try {
            if (!is_array($commissionIds)) {
                $commissionIds = [$commissionIds];
            }
            
            $placeholders = str_repeat('?,', count($commissionIds) - 1) . '?';
            
            $stmt = $this->db->prepare("
                UPDATE commissions 
                SET status = 'paid', paid_at = NOW() 
                WHERE id IN ($placeholders) AND status = 'pending'
            ");
            
            $stmt->execute($commissionIds);
            
            return [
                'success' => true,
                'processed_count' => $stmt->rowCount()
            ];
            
        } catch (Exception $e) {
            error_log("Process commission payments error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get commission rates configuration
     */
    public function getCommissionRates()
    {
        return self::COMMISSION_RATES;
    }
    
    /**
     * Calculate projected earnings for associate
     */
    public function calculateProjectedEarnings($associateId, $targetMonthlySales)
    {
        try {
            $downline = $this->getDownlineTeam($associateId, 4);
            $totalTeamMembers = 0;
            
            foreach ($downline as $level => $members) {
                $totalTeamMembers += count($members);
            }
            
            // Project earnings based on current team structure
            $projectedEarnings = [
                'direct_sales' => $targetMonthlySales * (self::COMMISSION_RATES[1] / 100),
                'team_bonus' => $targetMonthlySales * $totalTeamMembers * 0.02, // Estimated team contribution
                'level_bonuses' => []
            ];
            
            foreach (self::COMMISSION_RATES as $level => $rate) {
                if ($level > 1 && isset($downline[$level - 1])) {
                    $teamSize = count($downline[$level - 1]);
                    $projectedEarnings['level_bonuses'][$level] = $targetMonthlySales * $teamSize * ($rate / 100);
                }
            }
            
            $projectedEarnings['total_projected'] = array_sum($projectedEarnings['level_bonuses']) + 
                                                  $projectedEarnings['direct_sales'] + 
                                                  $projectedEarnings['team_bonus'];
            
            return $projectedEarnings;
            
        } catch (Exception $e) {
            error_log("Calculate projected earnings error: " . $e->getMessage());
            return [];
        }
    }
}
