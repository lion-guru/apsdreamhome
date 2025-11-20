<?php
require_once __DIR__ . '/NotificationService.php';
/**
 * Multi-Level Commission Calculator
 * APS Dream Homes - MLM Commission System
 * Supports 5-level commission structure
 */

class CommissionCalculator {
    private $conn;
    private NotificationService $notifier;
    
    // Commission structure by level
    private $commission_structure = [
        1 => 5.0,  // Direct sponsor
        2 => 3.0,  // Level 2
        3 => 2.0,  // Level 3
        4 => 1.5,  // Level 4
        5 => 1.0   // Level 5
    ];
    
    public function __construct() {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
        $this->notifier = new NotificationService();
    }
    
    /**
     * Calculate commission for property sale
     */
    public function calculatePropertyCommission($property_id, $sale_amount, $buyer_user_id) {
        if ($sale_amount <= 0) {
            return ['success' => false, 'error' => 'Invalid sale amount'];
        }
        
        // Get buyer's sponsor
        $stmt = $this->conn->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $buyer_user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result || !$result['sponsor_user_id']) {
            return ['success' => false, 'error' => 'No sponsor found for buyer'];
        }
        
        $this->conn->begin_transaction();
        
        try {
            $total_commission = 0;
            $commissions = [];
            
            // Calculate commissions for all levels
            $current_user = $buyer_user_id;
            $level = 1;
            
            while ($level <= 5) {
                $stmt = $this->conn->prepare("
                    SELECT 
                        mp.user_id, mp.current_level, u.name, u.email,
                        CASE mp.current_level
                            WHEN 'Bronze' THEN 1.0
                            WHEN 'Silver' THEN 1.2
                            WHEN 'Gold' THEN 1.5
                            WHEN 'Platinum' THEN 2.0
                            WHEN 'Diamond' THEN 2.5
                            ELSE 1.0
                        END as multiplier
                    FROM mlm_network_tree nt
                    JOIN mlm_profiles mp ON nt.ancestor_user_id = mp.user_id
                    JOIN users u ON mp.user_id = u.id
                    WHERE nt.descendant_user_id = ? AND nt.level = ?
                ");
                $stmt->bind_param("ii", $buyer_user_id, $level);
                $stmt->execute();
                $ancestor = $stmt->get_result()->fetch_assoc();
                
                if (!$ancestor) break;
                
                $base_percentage = $this->commission_structure[$level];
                $final_percentage = $base_percentage * $ancestor['multiplier'];
                $commission_amount = ($sale_amount * $final_percentage) / 100;
                
                if ($commission_amount > 0) {
                    // Create commission record
                    $stmt = $this->conn->prepare("
                        INSERT INTO mlm_commission_ledger 
                        (beneficiary_user_id, source_user_id, commission_type, amount, level, property_id, sale_amount, commission_percentage, status, created_at)
                        VALUES (?, ?, 'property_sale', ?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    $stmt->bind_param("iiididd", 
                        $ancestor['user_id'], 
                        $buyer_user_id, 
                        $commission_amount, 
                        $level, 
                        $property_id, 
                        $sale_amount, 
                        $final_percentage
                    );
                    $stmt->execute();
                    
                    $commissions[] = [
                        'user_id' => $ancestor['user_id'],
                        'name' => $ancestor['name'],
                        'level' => $level,
                        'amount' => $commission_amount,
                        'percentage' => $final_percentage
                    ];
                    
                    $total_commission += $commission_amount;
                }
                
                $level++;
            }
            
            // Update buyer's lifetime sales
            $stmt = $this->conn->prepare("UPDATE mlm_profiles SET lifetime_sales = lifetime_sales + ? WHERE user_id = ?");
            $stmt->bind_param("di", $sale_amount, $buyer_user_id);
            $stmt->execute();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'total_commission' => $total_commission,
                'commissions' => $commissions,
                'property_id' => $property_id,
                'sale_amount' => $sale_amount
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Calculate referral commission
     */
    public function calculateReferralCommission($referrer_user_id, $referred_user_id, $user_type) {
        $commission_amount = 0;
        
        switch ($user_type) {
            case 'customer':
                $commission_amount = 100; // ₹100 for customer referral
                break;
            case 'agent':
                $commission_amount = 500; // ₹500 for agent referral
                break;
            case 'associate':
                $commission_amount = 1000; // ₹1000 for associate referral
                break;
            case 'builder':
                $commission_amount = 2000; // ₹2000 for builder referral
                break;
            case 'investor':
                $commission_amount = 1500; // ₹1500 for investor referral
                break;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO mlm_commission_ledger 
            (beneficiary_user_id, source_user_id, commission_type, amount, level, status, created_at)
            VALUES (?, ?, 'referral_bonus', ?, 1, 'pending', NOW())
        ");
        $stmt->bind_param("iid", $referrer_user_id, $referred_user_id, $commission_amount);
        
        if ($stmt->execute()) {
            // Update referrer's pending commission
            $stmt = $this->conn->prepare("UPDATE mlm_profiles SET pending_commission = pending_commission + ? WHERE user_id = ?");
            $stmt->bind_param("di", $commission_amount, $referrer_user_id);
            $stmt->execute();
            
            return ['success' => true, 'amount' => $commission_amount];
        }
        
        return ['success' => false, 'error' => 'Failed to create commission record'];
    }
    
    /**
     * Approve commission
     */
    public function approveCommission($commission_id, $approved_by) {
        $stmt = $this->conn->prepare("
            SELECT beneficiary_user_id, amount, status 
            FROM mlm_commission_ledger 
            WHERE id = ? AND status = 'pending'
        ");
        $stmt->bind_param("i", $commission_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            return ['success' => false, 'error' => 'Commission not found or already processed'];
        }
        
        $stmt = $this->conn->prepare("
            UPDATE mlm_commission_ledger 
            SET status = 'approved', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $commission_id);
        
        if ($stmt->execute()) {
            // Update user's total commission
            $stmt = $this->conn->prepare("
                UPDATE mlm_profiles 
                SET total_commission = total_commission + ?, 
                    pending_commission = pending_commission - ? 
                WHERE user_id = ?
            ");
            $stmt->bind_param("ddi", $result['amount'], $result['amount'], $result['beneficiary_user_id']);
            $stmt->execute();

            $this->sendCommissionApprovedNotifications($commission_id, $result['beneficiary_user_id'], $result['amount']);

            return ['success' => true, 'amount' => $result['amount']];
        }
        
        return ['success' => false, 'error' => 'Failed to approve commission'];
    }
    
    /**
     * Get user's commissions
     */
    public function getUserCommissions($user_id, $status = null, $limit = 50) {
        $sql = "
            SELECT 
                cl.*,
                u.name as source_name,
                p.title as property_title
            FROM mlm_commission_ledger cl
            LEFT JOIN users u ON cl.source_user_id = u.id
            LEFT JOIN properties p ON cl.property_id = p.id
            WHERE cl.beneficiary_user_id = ?
        ";
        
        if ($status) {
            $sql .= " AND cl.status = ?";
        }
        
        $sql .= " ORDER BY cl.created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($status) {
            $stmt->bind_param("isi", $user_id, $status, $limit);
        } else {
            $stmt->bind_param("ii", $user_id, $limit);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function sendCommissionApprovedNotifications(int $commissionId, int $beneficiaryId, float $amount): void
    {
        try {
            $stmt = $this->conn->prepare('SELECT name, email FROM users WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $beneficiaryId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $payload = [
                'commission_id' => $commissionId,
                'beneficiary_user_id' => $beneficiaryId,
                'amount' => $amount,
            ];

            $adminSubject = 'Commission approved (ID: ' . $commissionId . ')';
            $adminBody = $this->buildAdminCommissionEmail($commissionId, $user, $amount);
            $this->notifier->notifyAdmin($adminSubject, $adminBody, 'commission_approved_admin', $payload);

            if (!empty($user['email'])) {
                $beneficiarySubject = 'Your commission has been approved';
                $beneficiaryBody = $this->buildBeneficiaryCommissionEmail($commissionId, $user['name'] ?? 'Associate', $amount);
                $this->notifier->sendEmail($user['email'], $beneficiarySubject, $beneficiaryBody, 'commission_approved_beneficiary', $beneficiaryId, $payload);
            }
        } catch (Throwable $e) {
            error_log('Commission approval notification error: ' . $e->getMessage());
        }
    }

    private function buildAdminCommissionEmail(int $commissionId, ?array $user, float $amount): string
    {
        $name = htmlspecialchars($user['name'] ?? 'Unknown');
        $email = htmlspecialchars($user['email'] ?? 'N/A');
        $formattedAmount = number_format($amount, 2);

        return "<h2>Commission Approved</h2>
            <p><strong>Commission ID:</strong> {$commissionId}</p>
            <p><strong>Beneficiary:</strong> {$name} ({$email})</p>
            <p><strong>Amount:</strong> ₹{$formattedAmount}</p>
            <p>View commission records in the admin analytics dashboard.</p>";
    }

    private function buildBeneficiaryCommissionEmail(int $commissionId, string $name, float $amount): string
    {
        $safeName = htmlspecialchars($name);
        $formattedAmount = number_format($amount, 2);

        return "<h2>Congratulations, {$safeName}!</h2>
            <p>Your commission (ID {$commissionId}) has been approved.</p>
            <p><strong>Payout Amount:</strong> ₹{$formattedAmount}</p>
            <p>The amount will be included in the next payout batch. You can monitor status in your referral dashboard.</p>";
    }
}
?>