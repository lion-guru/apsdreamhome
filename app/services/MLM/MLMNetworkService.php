<?php
namespace App\Services\MLM;

use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

class MLMNetworkService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

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
     * Register a Networker (paid onboarding)
     */
    public function registerNetworker(array $userData, int $packageId, ?int $sponsorId = null): array
    {
        try {
            $this->db->beginTransaction();
            
            // Get package
            $stmt = $this->db->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
            $stmt->execute([$packageId]);
            $package = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$package) {
                throw new \Exception('Invalid or inactive package');
            }
            
             // Create user
             $tid = $this->getTenantId();
             $tenantCol = $tid > 1 ? ", tenant_id" : "";
             $tenantVal = $tid > 1 ? ", ?" : "";
             $stmt = $this->db->prepare("INSERT INTO users (name, email, phone, role, onboarding_track, current_package_id, status, created_at$tenantCol) VALUES (?, ?, ?, 'associate', 'networker', ?, 'active', NOW()$tenantVal)");
             $params = [$userData['name'], $userData['email'], $userData['phone'] ?? '', $packageId];
             if ($tid > 1) $params[] = $tid;
             $stmt->execute($params);
            $userId = $this->db->lastInsertId();
            
            // Create wallet
            $stmt = $this->db->prepare("INSERT INTO user_wallets (user_id, user_type, balance, total_credited, is_active) VALUES (?, 'associate', 0, 0, 1)");
            $stmt->execute([$userId]);
            
            // Add to MLM tree - check existing mlm_network_tree structure
            $treeCols = $this->db->query("SHOW COLUMNS FROM mlm_network_tree")->fetchAll(\PDO::FETCH_COLUMN, 0);
            
            if (in_array('sponsor_id', $treeCols)) {
                $stmt = $this->db->prepare("INSERT INTO mlm_network_tree (associate_id, sponsor_id, parent_id, level) VALUES (?, ?, ?, ?)");
                $parentId = $sponsorId ?? 1;
                // Get parent's level
                $stmtP = $this->db->prepare("SELECT level FROM mlm_network_tree WHERE associate_id = ? LIMIT 1");
                $stmtP->execute([$parentId]);
                $parent = $stmtP->fetch(\PDO::FETCH_ASSOC);
                $level = $parent ? ($parent['level'] + 1) : 1;
                $stmt->execute([$userId, $sponsorId, $parentId, $level]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO mlm_network_tree (associate_id, parent_id, level) VALUES (?, ?, ?)");
                $parentId = $sponsorId ?? 1;
                $stmtP = $this->db->prepare("SELECT level FROM mlm_network_tree WHERE associate_id = ? LIMIT 1");
                $stmtP->execute([$parentId]);
                $parent = $stmtP->fetch(\PDO::FETCH_ASSOC);
                $level = $parent ? ($parent['level'] + 1) : 1;
                $stmt->execute([$userId, $parentId, $level]);
            }
            
            // Direct reward to sponsor
            if ($sponsorId) {
                $this->creditDirectReward($sponsorId, $userId, $package['direct_reward']);
            }
            
            // Level rewards up the tree
            $this->distributeLevelRewards($userId, $package);
            
            $this->db->commit();
            return ['success' => true, 'user_id' => $userId, 'message' => 'Networker registered successfully'];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Register Free Consultant (free onboarding, excluded from level income)
     */
    public function registerFreeConsultant(array $userData, ?int $sponsorId = null): array
    {
        try {
            $this->db->beginTransaction();

            $tid = $this->getTenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            $stmt = $this->db->prepare("INSERT INTO users (name, email, phone, role, onboarding_track, status, associate_payout_slab, created_at$tenantCol) VALUES (?, ?, ?, 'associate', 'free_consultant', 'active', '5%', NOW()$tenantVal)");
            $params = [$userData['name'], $userData['email'], $userData['phone'] ?? ''];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $userId = $this->db->lastInsertId();
            
            // Create wallet
            $stmt = $this->db->prepare("INSERT INTO user_wallets (user_id, user_type, balance, total_credited, is_active) VALUES (?, 'associate', 0, 0, 1)");
            $stmt->execute([$userId]);
            
            // Add to MLM tree
            $stmt = $this->db->prepare("INSERT INTO mlm_network_tree (associate_id, sponsor_id, parent_id, level) VALUES (?, ?, ?, ?)");
            $parentId = $sponsorId ?? 1;
            $stmtP = $this->db->prepare("SELECT level FROM mlm_network_tree WHERE associate_id = ? LIMIT 1");
            $stmtP->execute([$parentId]);
            $parent = $stmtP->fetch(\PDO::FETCH_ASSOC);
            $level = $parent ? ($parent['level'] + 1) : 1;
            $stmt->execute([$userId, $sponsorId, $parentId, $level]);
            
            // Free Consultants get NO direct or level rewards
            // They only earn from Associate Payout System (slab-based)
            
            $this->db->commit();
            return ['success' => true, 'user_id' => $userId, 'message' => 'Free Consultant registered'];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function creditDirectReward(int $sponsorId, int $newUserId, float $amount): void
    {
        // Credit sponsor's wallet
        $stmt = $this->db->prepare("UPDATE user_wallets SET balance = balance + ?, total_credited = total_credited + ? WHERE user_id = ? AND user_type = 'associate'");
        $stmt->execute([$amount, $amount, $sponsorId]);
        
        // Insert ledger entry
        $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, level, status, notes, created_at) VALUES (?, ?, 'referral', ?, 1, 'approved', 'Direct referral reward', NOW())");
        $stmt->execute([$sponsorId, $newUserId, $amount]);
    }
    
    private function distributeLevelRewards(int $newUserId, array $package): void
    {
        $levelReward = (float)$package['level_reward'];
        if ($levelReward <= 0) return;
        
        $cappingService = new DailyCappingService();
        
        // Walk up the tree 25 levels
        $currentUserId = $newUserId;
        for ($level = 1; $level <= 25; $level++) {
            $stmt = $this->db->prepare("SELECT sponsor_id FROM mlm_network_tree WHERE associate_id = ? LIMIT 1");
            $stmt->execute([$currentUserId]);
            $upline = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$upline || empty($upline['sponsor_id'])) break;
            
             // Check if upline is Free Consultant (excluded from level income)
             $tid = $this->getTenantId();
             $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
             $stmt = $this->db->prepare("SELECT onboarding_track, current_package_id FROM users WHERE id = ?$tenantWhere");
             $params = [$upline['sponsor_id']];
             if ($tid > 1) $params[] = $tid;
             $stmt->execute($params);
             $uplineUser = $stmt->fetch(\PDO::FETCH_ASSOC);
            $uplineUser = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($uplineUser && $uplineUser['onboarding_track'] === 'free_consultant') {
                $currentUserId = $upline['sponsor_id'];
                continue; // Skip free consultants
            }
            
            // Get upline's package for capping
            $uplinePkgId = $uplineUser['current_package_id'] ?? null;
            if ($uplinePkgId) {
                $stmt = $this->db->prepare("SELECT daily_capping FROM packages WHERE id = ?");
                $stmt->execute([$uplinePkgId]);
                $uplinePkg = $stmt->fetch(\PDO::FETCH_ASSOC);
                $dailyCap = $uplinePkg ? (float)$uplinePkg['daily_capping'] : 0;
            } else {
                $dailyCap = 0;
            }
            
            // Apply daily capping
            $credited = $cappingService->applyDailyCap($upline['sponsor_id'], $levelReward, $dailyCap);
            
            if ($credited > 0) {
                $stmt = $this->db->prepare("UPDATE user_wallets SET balance = balance + ?, total_credited = total_credited + ? WHERE user_id = ? AND user_type = 'associate'");
                $stmt->execute([$credited, $credited, $upline['sponsor_id']]);
                
                $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, level, status, notes, created_at) VALUES (?, ?, 'level_bonus', ?, ?, 'approved', CONCAT('Level ', ?, ' reward'), NOW())");
                $stmt->execute([$upline['sponsor_id'], $newUserId, $credited, $level, $level]);
            }
            
            $currentUserId = $upline['sponsor_id'];
        }
    }
    
    /**
     * Calculate associate payout based on slab (5%-20% of cumulative sales)
     */
    public function calculateAssociatePayout(int $userId, float $saleAmount): float
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->db->prepare("SELECT cumulative_sales, associate_payout_slab FROM users WHERE id = ?$tenantWhere");
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user) return 0;
        
        $cumulative = (float)$user['cumulative_sales'] + $saleAmount;
        $slab = $this->getSlabPercentage($cumulative);
        
        // Update slab
        $stmt = $this->db->prepare("UPDATE users SET cumulative_sales = ?, associate_payout_slab = ? WHERE id = ?$tenantWhere");
        $updParams = [$cumulative, $slab['label'], $userId];
        if ($tid > 1) $updParams[] = $tid;
        $stmt->execute($updParams);
        
        return $saleAmount * ($slab['percent'] / 100);
    }
    
    private function getSlabPercentage(float $cumulativeSales): array
    {
        if ($cumulativeSales >= 50000000) return ['percent' => 20, 'label' => '20%'];
        if ($cumulativeSales >= 20000000) return ['percent' => 17, 'label' => '17%'];
        if ($cumulativeSales >= 10000000) return ['percent' => 15, 'label' => '15%'];
        if ($cumulativeSales >= 5000000)  return ['percent' => 12, 'label' => '12%'];
        if ($cumulativeSales >= 1000000)  return ['percent' => 10, 'label' => '10%'];
        if ($cumulativeSales >= 500000)   return ['percent' => 7,  'label' => '7%'];
        return ['percent' => 5, 'label' => '5%'];
    }
    
    /**
     * Get downline tree for a user
     */
    public function getDownline(int $userId, int $maxDepth = 25): array
    {
        $tree = [];
        $this->buildTree($userId, $tree, 0, $maxDepth);
        return $tree;
    }
    
    private function buildTree(int $userId, array &$tree, int $depth, int $maxDepth): void
    {
        if ($depth >= $maxDepth) return;
        
        $stmt = $this->db->prepare("SELECT mnt.associate_id, u.name, u.email, u.onboarding_track FROM mlm_network_tree mnt JOIN users u ON u.id = mnt.associate_id AND u.tenant_id = ? WHERE mnt.parent_id = ?");
        $stmt->execute([$this->getTenantId(), $userId]);
        $children = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($children as $child) {
            $node = [
                'id' => $child['associate_id'],
                'name' => $child['name'],
                'email' => $child['email'],
                'track' => $child['onboarding_track'],
                'children' => [],
            ];
            $this->buildTree($child['associate_id'], $node['children'], $depth + 1, $maxDepth);
            $tree[] = $node;
        }
    }
}
