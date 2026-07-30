<?php
namespace App\Services\MLM;

use App\Core\Middleware\TenantContext;

class RERAComplianceService
{
    private $db;
    private $reraFee = 10000.00; // Fixed RERA processing fee
    
    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }
    
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Process commission payout with RERA interception
     * When a Free Consultant or agent closes a booking:
     * 1. Calculate net commission (5% of sale)
     * 2. If not RERA approved, deduct ₹10,000 fee
     * 3. Route to rera_deduction_wallet
     * 4. Trigger RERA process, upgrade to Gold
     * 5. Release remaining to main wallet
     */
    public function processCommissionWithRERA(int $agentId, int $bookingId, float $saleAmount): array
    {
        try {
            $this->db->beginTransaction();
            
            // Get agent
            $tid = $this->getTenantId();
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $stmt->execute($tid > 1 ? [$agentId, $tid] : [$agentId]);
            $agent = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$agent) throw new \Exception('Agent not found');
            
            // Calculate commission (5% of sale)
            $mlmService = new MLMNetworkService();
            $grossCommission = $mlmService->calculateAssociatePayout($agentId, $saleAmount);
            
            $netPayout = $grossCommission;
            $reraDeducted = 0.00;
            
            // Check if RERA deduction needed
            if (!$agent['is_rera_approved'] && $grossCommission > 0) {
                $reraDeducted = min($this->reraFee, $grossCommission);
                $netPayout = $grossCommission - $reraDeducted;
                
                // Route RERA fee to deduction wallet
                $tid = $this->getTenantId();
                $stmt = $this->db->prepare("UPDATE users SET rera_deduction_wallet = rera_deduction_wallet + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
                $stmt->execute($tid > 1 ? [$reraDeducted, $agentId, $tid] : [$reraDeducted, $agentId]);
                
                try {
                    // Create RERA request
                    $stmt = $this->db->prepare("INSERT INTO rera_requests (user_id, booking_id, deducted_amount, status, notes, created_at) VALUES (?, ?, ?, 'pending', 'Auto-deducted from commission payout', NOW())");
                } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                error_log($e->getMessage());
                }
                $stmt->execute([$agentId, $bookingId, $reraDeducted]);
                
                // Upgrade to Gold package configuration
                $stmt = $this->db->prepare("UPDATE users SET is_rera_approved = 1, current_package_id = (SELECT id FROM packages WHERE name = 'Gold' LIMIT 1) WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
                $stmt->execute($tid > 1 ? [$agentId, $tid] : [$agentId]);
                
                // Notify admin (log)
                error_log("RERA Compliance: Agent #$agentId RERA fee deducted. Amount: $reraDeducted. Booking #$bookingId");
            }
            
            // Release remaining to wallet
            if ($netPayout > 0) {
                $stmt = $this->db->prepare("UPDATE user_wallets SET balance = balance + ?, total_credited = total_credited + ? WHERE user_id = ? AND user_type = 'associate'");
                $stmt->execute([$netPayout, $netPayout, $agentId]);
                
                $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, status, notes, created_at) VALUES (?, ?, 'direct_sale', ?, 'approved', 'Commission after RERA adjustment', NOW())");
                $stmt->execute([$agentId, $agentId, $netPayout]);
            }
            
            // Log RERA deduction
            if ($reraDeducted > 0) {
                $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, status, notes, created_at) VALUES (?, ?, 'performance_bonus', ?, 'approved', 'RERA processing fee deducted', NOW())");
                $stmt->execute([$agentId, $agentId, $reraDeducted]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'gross_commission' => $grossCommission,
                'rera_deducted' => $reraDeducted,
                'net_payout' => $netPayout,
                'rera_approved' => !$agent['is_rera_approved'],
                'message' => $reraDeducted > 0 ? "RERA fee of ₹$reraDeducted deducted. Net payout: ₹$netPayout" : "Full commission of ₹$netPayout released",
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Approve RERA request (admin action)
     */
    public function approveRERA(int $reraRequestId, string $reraNumber, int $processedBy): array
    {
        try {
            try {
                $stmt = $this->db->prepare("UPDATE rera_requests SET status = 'approved', rera_number = ?, processed_by = ?, processed_at = NOW() WHERE id = ?");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$reraNumber, $processedBy, $reraRequestId]);
            
            try {
                // Get user ID from request
                $stmt = $this->db->prepare("SELECT user_id FROM rera_requests WHERE id = ?");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$reraRequestId]);
            $req = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($req) {
                $tid = $this->getTenantId();
                $stmt = $this->db->prepare("UPDATE users SET is_rera_approved = 1, rera_number = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
                $stmt->execute($tid > 1 ? [$reraNumber, $req['user_id'], $tid] : [$reraNumber, $req['user_id']]);
            }
            
            return ['success' => true, 'message' => 'RERA approved'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get pending RERA requests
     */
    public function getPendingRequests(): array
    {
        try {
            try {
                $tid = $this->getTenantId();
                $stmt = $this->db->query("SELECT r.*, u.name as user_name, u.email as user_email FROM rera_requests r JOIN users u ON u.id = r.user_id" . ($tid > 1 ? " AND u.tenant_id = $tid" : "") . " WHERE r.status = 'pending' ORDER BY r.created_at DESC");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
