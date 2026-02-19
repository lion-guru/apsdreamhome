<?php

namespace App\Models;

use App\Models\Model;
use App\Core\Database;
use PDO;

/**
 * Associate Model
 * Handles all associate-related database operations for MLM system
 */
class Associate extends Model
{
    protected static string $table = 'associates';
    protected $primaryKey = 'id';

    /**
     * Get associate by ID with complete details
     */
    public function getAssociateById($id)
    {
        $sql = "
            SELECT a.*, a.id as associate_id, u.name as user_name, u.email as user_email, u.phone as user_phone,
                   u.city as user_city, u.state as user_state, u.pincode as user_pincode,
                   su.name as sponsor_name, su.email as sponsor_email,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.id) as downline_count,
                   (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE associate_id = a.id AND status = 'completed') as total_earnings,
                   (SELECT COUNT(*) FROM payments WHERE associate_id = a.id AND status = 'completed') as total_sales
            FROM {$this->table} a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN associates s ON a.sponsor_id = s.id
            LEFT JOIN users su ON s.user_id = su.id
            WHERE a.id = :id
        ";

        $db = Database::getInstance();
        $stmt = $db->query($sql, ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent bookings for associate
     */
    public function getRecentBookings($associateId, $limit = 5)
    {
        $sql = "
            SELECT c.name as customer_name, c.email, c.phone, b.total_amount, b.amount as paid_amount,
                   (b.total_amount - b.amount) as remaining_amount, b.booking_date, b.status
            FROM bookings b
            JOIN customers c ON b.customer_id = c.id
            WHERE b.associate_id = :associate_id
            ORDER BY b.booking_date DESC LIMIT :limit
        ";

        // Since PDO limit binding can be tricky with some drivers, we cast to int
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':associate_id', $associateId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent transactions for associate
     */
    public function getRecentTransactions($associateId, $limit = 5)
    {
        // Check if mlm_transactions table exists first, or wrap in try-catch
        try {
            $sql = "
                SELECT transaction_type, amount, description, created_at, status
                FROM mlm_transactions 
                WHERE associate_id = :associate_id
                ORDER BY created_at DESC LIMIT :limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':associate_id', $associateId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table might not exist yet, return empty array
            return [];
        }
    }

    /**
     * Get associate by user ID
     */
    public function getAssociateByUserId($userId)
    {
        $sql = "
            SELECT a.*, a.id as associate_id, u.name, u.email, u.phone, u.city, u.state, u.pincode
            FROM {$this->table} a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.user_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get associate by email
     */
    public function getAssociateByEmail($email)
    {
        $sql = "
            SELECT a.*, a.id as associate_id, u.name, u.email, u.phone
            FROM {$this->table} a
            JOIN users u ON a.user_id = u.id
            WHERE u.email = :email
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authenticate associate by email or mobile
     */
    public function authenticateAssociate($loginId, $password)
    {
        // Check if input is email or mobile
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $associate = $this->getAssociateByEmail($loginId);
        } else {
            $associate = $this->getAssociateByMobile($loginId);
        }

        if ($associate && password_verify($password, $associate['password'])) {
            return $associate;
        }

        return false;
    }

    /**
     * Get associate by mobile
     */
    public function getAssociateByMobile($mobile)
    {
        $sql = "
            SELECT a.*, a.id as associate_id, u.name, u.email, u.phone
            FROM {$this->table} a
            JOIN users u ON a.user_id = u.id
            WHERE u.phone = :mobile
        ";

        // Note: Some systems store mobile in users table, some in associates table directly.
        // Checking both for compatibility.

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mobile' => $mobile]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if email exists
     */
    public function isEmailExists($email)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if mobile exists
     */
    public function isMobileExists($mobile)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$mobile]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get associate by referral code
     */
    public function getAssociateByReferralCode($code)
    {
        $sql = "
            SELECT a.*, a.id as associate_id, u.name as full_name, u.phone as mobile, a.current_level,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.id) as total_team_size
            FROM associates a
            JOIN users u ON a.user_id = u.id
            WHERE a.associate_code = ? AND a.status = 'active'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new associate user
     */
    public function createAssociateUser($userData)
    {
        $sql = "INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at) 
                VALUES (:name, :email, :phone, :password, 'associate', 'active', NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData);
        return $this->db->lastInsertId();
    }

    /**
     * Create new associate
     */
    public function createAssociate($data)
    {
        $sql = "
            INSERT INTO {$this->table} (
                user_id, sponsor_id, associate_code, current_level, status, 
                created_at, updated_at
            ) VALUES (
                :user_id, :sponsor_id, :associate_code, :current_level, :status, 
                NOW(), NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    /**
     * Update associate
     */
    public function updateAssociate($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $setParts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $setParts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get downline team
     */
    public function getDownlineTeam($associateId, $level = null)
    {
        $sql = "
            SELECT a.*, u.name, u.email, u.phone, u.city, u.state,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.associate_id) as direct_downline,
                   (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE associate_id = a.associate_id AND status = 'completed') as team_earnings
            FROM {$this->table} a
            JOIN users u ON a.user_id = u.id
            WHERE a.sponsor_id = :associate_id
        ";

        $params = ['associate_id' => $associateId];

        if ($level) {
            $sql .= " AND a.level <= :level";
            $params['level'] = $level;
        }

        $sql .= " ORDER BY a.level, a.joining_date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get complete downline hierarchy
     */
    public function getDownlineHierarchy($associateId, $maxLevel = 10)
    {
        $hierarchy = [];
        $this->buildHierarchy($associateId, $hierarchy, 1, $maxLevel);
        return $hierarchy;
    }

    /**
     * Build hierarchy recursively
     */
    private function buildHierarchy($sponsorId, &$hierarchy, $currentLevel, $maxLevel)
    {
        if ($currentLevel > $maxLevel) {
            return;
        }

        $downline = $this->getDownlineTeam($sponsorId, $currentLevel);

        foreach ($downline as $member) {
            $member['level'] = $currentLevel;
            $member['children'] = [];
            $hierarchy[] = $member;

            $this->buildHierarchy($member['associate_id'], $member['children'], $currentLevel + 1, $maxLevel);
        }
    }

    /**
     * Get associate earnings and commissions
     */
    public function getAssociateEarnings($associateId, $filters = [])
    {
        $conditions = ["p.associate_id = :associate_id"];
        $params = ['associate_id' => $associateId];

        if (!empty($filters['date_from'])) {
            $conditions[] = "p.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "p.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT p.*, prop.title as property_title, u.name as user_name,
                   c.commission_percentage, c.commission_amount, c.level as commission_level
            FROM payments p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN associate_commissions c ON p.id = c.payment_id
            {$whereClause}
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get commission summary
     */
    public function getCommissionSummary($associateId)
    {
        $sql = "
            SELECT
                SUM(CASE WHEN c.level = 1 THEN c.commission_amount ELSE 0 END) as level_1_earnings,
                SUM(CASE WHEN c.level = 2 THEN c.commission_amount ELSE 0 END) as level_2_earnings,
                SUM(CASE WHEN c.level = 3 THEN c.commission_amount ELSE 0 END) as level_3_earnings,
                SUM(CASE WHEN c.level = 4 THEN c.commission_amount ELSE 0 END) as level_4_earnings,
                SUM(CASE WHEN c.level = 5 THEN c.commission_amount ELSE 0 END) as level_5_earnings,
                SUM(c.commission_amount) as total_commissions,
                COUNT(c.id) as total_commission_payments,
                COUNT(DISTINCT p.id) as total_sales_generated
            FROM associate_commissions c
            JOIN payments p ON c.payment_id = p.id
            WHERE c.associate_id = :associate_id AND p.status = 'completed'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['associate_id' => $associateId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get payout history
     */
    public function getPayoutHistory($associateId, $filters = [])
    {
        $conditions = ["po.associate_id = :associate_id"];
        $params = ['associate_id' => $associateId];

        if (!empty($filters['date_from'])) {
            $conditions[] = "po.payout_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "po.payout_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "po.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT po.*, a.name as approved_by_name
            FROM payouts po
            LEFT JOIN admin a ON po.approved_by = a.aid
            {$whereClause}
            ORDER BY po.payout_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pending payouts
     */
    public function getPendingPayouts($associateId = null)
    {
        $params = [];
        $whereClause = "";

        if ($associateId) {
            $whereClause = "WHERE po.associate_id = :associate_id";
            $params['associate_id'] = $associateId;
        }

        $sql = "
            SELECT po.*, a.name as associate_name, a.email as associate_email,
                   u.name as user_name, u.phone as user_phone
            FROM payouts po
            JOIN associates a ON po.associate_id = a.associate_id
            LEFT JOIN users u ON a.user_id = u.id
            {$whereClause} AND po.status = 'pending'
            ORDER BY po.request_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Request payout
     */
    public function requestPayout($associateId, $amount, $paymentMethod, $accountDetails)
    {
        $sql = "
            INSERT INTO payouts (associate_id, amount, payment_method, account_details, status, request_date)
            VALUES (:associate_id, :amount, :payment_method, :account_details, 'pending', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'associate_id' => $associateId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'account_details' => $accountDetails
        ]);
    }

    /**
     * Get business statistics for associate
     */
    public function getBusinessStats($associateId)
    {
        $stats = [];

        // Personal sales
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_sales,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_sales,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_sales_value
            FROM payments
            WHERE associate_id = :associate_id
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $stats['personal'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Team performance
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT a.associate_id) as total_team_members,
                COUNT(CASE WHEN a.level = 1 THEN 1 END) as direct_members,
                COUNT(CASE WHEN a.level = 2 THEN 1 END) as level_2_members,
                COUNT(CASE WHEN a.level = 3 THEN 1 END) as level_3_members
            FROM associates a
            WHERE a.sponsor_id = :associate_id OR a.sponsor_id IN (
                SELECT sub.associate_id FROM associates sub WHERE sub.sponsor_id = :associate_id
            )
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $stats['team'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Monthly performance
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as sales_count,
                SUM(amount) as sales_value
            FROM payments
            WHERE associate_id = :associate_id AND status = 'completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $stats['monthly'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get team members with performance
     */
    public function getTeamMembers($associateId, $level = null)
    {
        $params = ['associate_id' => $associateId];

        $levelCondition = "";
        if ($level) {
            $levelCondition = "AND a.level = :level";
            $params['level'] = $level;
        }

        $sql = "
            SELECT a.*, u.name, u.email, u.phone, u.city, u.state,
                   (SELECT COUNT(*) FROM payments WHERE associate_id = a.associate_id) as total_sales,
                   (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE associate_id = a.associate_id AND status = 'completed') as total_earnings,
                   (SELECT MAX(created_at) FROM payments WHERE associate_id = a.associate_id) as last_sale_date,
                   DATEDIFF(NOW(), a.joining_date) as days_in_system
            FROM associates a
            JOIN users u ON a.user_id = u.id
            WHERE a.sponsor_id = :associate_id {$levelCondition}
            ORDER BY a.joining_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get commission details
     */
    public function getCommissionDetails($associateId)
    {
        $sql = "
            SELECT c.*, p.amount as sale_amount, p.created_at as sale_date,
                   prop.title as property_title, u.name as customer_name
            FROM associate_commissions c
            JOIN payments p ON c.payment_id = p.id
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE c.associate_id = :associate_id
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['associate_id' => $associateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get associate rank and achievements
     */
    public function getAssociateRank($associateId)
    {
        // Get current associate stats
        $currentStats = $this->getBusinessStats($associateId);

        // Calculate rank based on criteria
        $personalSales = $currentStats['personal']['total_sales_value'] ?? 0;
        $teamMembers = $currentStats['team']['total_team_members'] ?? 0;

        $rank = 'Bronze';
        if ($personalSales >= 1000000 && $teamMembers >= 50) {
            $rank = 'Diamond';
        } elseif ($personalSales >= 500000 && $teamMembers >= 20) {
            $rank = 'Gold';
        } elseif ($personalSales >= 200000 && $teamMembers >= 10) {
            $rank = 'Silver';
        }

        return [
            'current_rank' => $rank,
            'personal_sales' => $personalSales,
            'team_members' => $teamMembers,
            'next_rank' => $this->getNextRankRequirements($rank),
            'achievements' => $this->getAchievements($associateId)
        ];
    }

    /**
     * Get next rank requirements
     */
    private function getNextRankRequirements($currentRank)
    {
        switch ($currentRank) {
            case 'Bronze':
                return ['rank' => 'Silver', 'personal_sales' => 200000, 'team_members' => 10];
            case 'Silver':
                return ['rank' => 'Gold', 'personal_sales' => 500000, 'team_members' => 20];
            case 'Gold':
                return ['rank' => 'Diamond', 'personal_sales' => 1000000, 'team_members' => 50];
            default:
                return ['rank' => 'Diamond Elite', 'personal_sales' => 2000000, 'team_members' => 100];
        }
    }

    /**
     * Get associate achievements
     */
    private function getAchievements($associateId)
    {
        $achievements = [];

        $stats = $this->getBusinessStats($associateId);

        if (($stats['personal']['total_sales_value'] ?? 0) >= 100000) {
            $achievements[] = ['name' => 'Century Club', 'description' => 'Achieved ₹1 Lakh in personal sales'];
        }

        if (($stats['team']['total_team_members'] ?? 0) >= 5) {
            $achievements[] = ['name' => 'Team Builder', 'description' => 'Built a team of 5+ members'];
        }

        if (count($stats['monthly']) >= 3) {
            $achievements[] = ['name' => 'Consistent Performer', 'description' => 'Consistent sales for 3+ months'];
        }

        return $achievements;
    }

    /**
     * Update KYC status
     */
    public function updateKYCStatus($associateId, $kycStatus, $kycDocuments = null)
    {
        $data = ['kyc_status' => $kycStatus];

        if ($kycDocuments) {
            $data['kyc_documents'] = json_encode($kycDocuments);
        }

        return $this->updateAssociate($associateId, $data);
    }

    /**
     * Update bank details
     */
    public function updateBankDetails($associateId, $bankDetails)
    {
        return $this->updateAssociate($associateId, ['bank_details' => json_encode($bankDetails)]);
    }

    /**
     * Get associates for admin panel
     */
    public function getAssociatesForAdmin($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR a.associate_code LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $conditions[] = "a.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['kyc_status'])) {
            $conditions[] = "a.kyc_status = :kyc_status";
            $params['kyc_status'] = $filters['kyc_status'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT a.*, u.name, u.email, u.phone, u.city, u.state,
                   su.name as sponsor_name,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.associate_id) as downline_count,
                   (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE associate_id = a.associate_id AND status = 'completed') as total_earnings
            FROM associates a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN associates s ON a.sponsor_id = s.associate_id
            LEFT JOIN users su ON s.user_id = su.id
            {$whereClause}
            ORDER BY a.joining_date DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get complete downline details with performance metrics
     */
    public function getCompleteDownlineDetails($associateId, $level = null)
    {
        $params = ['associate_id' => $associateId];

        $levelCondition = "";
        if ($level) {
            $levelCondition = "AND a.level = :level";
            $params['level'] = $level;
        }

        $sql = "
            SELECT a.*, u.name, u.email, u.phone, u.city, u.state,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.associate_id) as direct_downline_count,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id IN (SELECT associate_id FROM associates WHERE sponsor_id = a.associate_id)) as indirect_downline_count,
                   (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE p_sub.associate_id = a.associate_id AND p_sub.status = 'completed') as total_team_sales,
                   (SELECT COALESCE(SUM(ac_sub.commission_amount), 0) FROM associate_commissions ac_sub WHERE ac_sub.associate_id = a.associate_id) as total_commissions_earned,
                   (SELECT COALESCE(AVG(p_sub.amount), 0) FROM payments p_sub WHERE p_sub.associate_id = a.associate_id AND p_sub.status = 'completed') as avg_sale_value,
                   (SELECT MAX(p_sub.created_at) FROM payments p_sub WHERE p_sub.associate_id = a.associate_id) as last_sale_date,
                   DATEDIFF(NOW(), a.joining_date) as days_in_system,
                   (SELECT COUNT(*) FROM associates sub WHERE sub.sponsor_id = a.associate_id) as personal_recruits,
                   (SELECT COALESCE(SUM(amount), 0) FROM payments sp WHERE sp.associate_id = a.associate_id AND sp.status = 'completed') as personal_sales
            FROM associates a
            JOIN users u ON a.user_id = u.id
            WHERE a.sponsor_id = :associate_id {$levelCondition}
            ORDER BY a.level, a.joining_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get team performance analytics
     */
    public function getTeamPerformanceAnalytics($associateId)
    {
        $analytics = [];

        // Overall team performance
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_members,
                COUNT(CASE WHEN level = 1 THEN 1 END) as direct_members,
                COUNT(CASE WHEN level = 2 THEN 1 END) as level_2_members,
                COUNT(CASE WHEN level = 3 THEN 1 END) as level_3_members,
                COUNT(CASE WHEN level = 4 THEN 1 END) as level_4_members,
                COUNT(CASE WHEN level = 5 THEN 1 END) as level_5_members,
                AVG(DATEDIFF(NOW(), joining_date)) as avg_days_in_system,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members,
                SUM(CASE WHEN kyc_status = 'verified' THEN 1 ELSE 0 END) as verified_kyc_members
            FROM associates
            WHERE sponsor_id = :associate_id OR sponsor_id IN (
                SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
            )
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $analytics['team_overview'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Performance by level
        $stmt = $this->db->prepare("
            SELECT
                a.level,
                COUNT(*) as members_count,
                COALESCE(SUM(p.amount), 0) as total_sales,
                COALESCE(AVG(p.amount), 0) as avg_sale_value,
                COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as completed_sales,
                MAX(p.created_at) as last_sale_date
            FROM associates a
            LEFT JOIN payments p ON a.associate_id = p.associate_id AND p.status = 'completed'
            WHERE a.sponsor_id = :associate_id OR a.sponsor_id IN (
                SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
            )
            GROUP BY a.level
            ORDER BY a.level
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $analytics['performance_by_level'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monthly team growth
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(joining_date, '%Y-%m') as month,
                COUNT(*) as new_members,
                SUM(CASE WHEN level = 1 THEN 1 ELSE 0 END) as direct_recruits,
                SUM(CASE WHEN level > 1 THEN 1 ELSE 0 END) as indirect_recruits
            FROM associates
            WHERE (sponsor_id = :associate_id OR sponsor_id IN (
                SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
            ))
            AND joining_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(joining_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $analytics['monthly_growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top performers in team
        $stmt = $this->db->prepare("
            SELECT a.*, u.name, u.email,
                   COALESCE(SUM(p.amount), 0) as total_sales,
                   COUNT(p.id) as sales_count,
                   COALESCE(SUM(ac.commission_amount), 0) as total_commissions,
                   MAX(p.created_at) as last_sale_date,
                   DATEDIFF(NOW(), a.joining_date) as days_in_system
            FROM associates a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN payments p ON a.associate_id = p.associate_id AND p.status = 'completed'
            LEFT JOIN associate_commissions ac ON a.associate_id = ac.associate_id
            WHERE a.sponsor_id = :associate_id OR a.sponsor_id IN (
                SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
            )
            GROUP BY a.associate_id
            HAVING total_sales > 0
            ORDER BY total_sales DESC
            LIMIT 10
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $analytics['top_performers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Inactive members analysis
        $stmt = $this->db->prepare("
            SELECT a.*, u.name, u.email,
                   DATEDIFF(NOW(), a.joining_date) as days_since_joining,
                   (SELECT MAX(created_at) FROM payments p WHERE p.associate_id = a.associate_id) as last_sale_date,
                   (SELECT COUNT(*) FROM associates sub WHERE sub.sponsor_id = a.associate_id) as recruits_made
            FROM associates a
            JOIN users u ON a.user_id = u.id
            WHERE a.sponsor_id = :associate_id OR a.sponsor_id IN (
                SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
            )
            AND a.status = 'inactive'
            ORDER BY a.joining_date DESC
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $analytics['inactive_members'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $analytics;
    }

    /**
     * Get downline genealogy tree
     */
    public function getDownlineGenealogy($associateId, $maxDepth = 5)
    {
        $tree = [];
        $this->buildGenealogyTree($associateId, $tree, 0, $maxDepth);
        return $tree;
    }

    /**
     * Build genealogy tree recursively
     */
    private function buildGenealogyTree($sponsorId, &$tree, $currentDepth, $maxDepth)
    {
        if ($currentDepth >= $maxDepth) {
            return;
        }

        $downline = $this->getDownlineTeam($sponsorId);

        foreach ($downline as $member) {
            $member['depth'] = $currentDepth;
            $member['children'] = [];
            $tree[] = $member;

            $this->buildGenealogyTree($member['associate_id'], $member['children'], $currentDepth + 1, $maxDepth);
        }
    }

    /**
     * Get associate's business volume and rank progression
     */
    public function getBusinessVolumeAndRank($associateId)
    {
        // Current business volume
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END), 0) as personal_volume,
                COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as personal_sales_count,
                (SELECT COUNT(*) FROM associates WHERE sponsor_id = :associate_id) as direct_recruits,
                (SELECT COUNT(*) FROM associates WHERE sponsor_id IN (
                    SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
                )) as total_downline
            FROM payments p
            WHERE p.associate_id = :associate_id
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $currentVolume = $stmt->fetch(PDO::FETCH_ASSOC);

        // Rank requirements
        $rankRequirements = [
            'Bronze' => ['min_personal' => 0, 'min_direct' => 0, 'min_group' => 0],
            'Silver' => ['min_personal' => 200000, 'min_direct' => 3, 'min_group' => 1000000],
            'Gold' => ['min_personal' => 500000, 'min_direct' => 5, 'min_group' => 2500000],
            'Diamond' => ['min_personal' => 1000000, 'min_direct' => 10, 'min_group' => 5000000]
        ];

        $currentRank = 'Bronze';
        foreach ($rankRequirements as $rank => $req) {
            if (
                $currentVolume['personal_volume'] >= $req['min_personal'] &&
                $currentVolume['direct_recruits'] >= $req['min_direct']
            ) {
                $currentRank = $rank;
            } else {
                break;
            }
        }

        // Next rank requirements
        $nextRank = null;
        $ranks = array_keys($rankRequirements);
        $currentRankIndex = array_search($currentRank, $ranks);

        if ($currentRankIndex < count($ranks) - 1) {
            $nextRank = $ranks[$currentRankIndex + 1];
        }

        // Progress calculations
        $progress = [];
        if ($nextRank) {
            $nextReq = $rankRequirements[$nextRank];
            $currentReq = $rankRequirements[$currentRank];

            $progress['personal'] = min(($currentVolume['personal_volume'] / $nextReq['min_personal']) * 100, 100);
            $progress['direct'] = min(($currentVolume['direct_recruits'] / $nextReq['min_direct']) * 100, 100);
            $progress['group'] = 0; // This would need group volume calculation

            // Overall progress (weighted average)
            $progress['overall'] = ($progress['personal'] * 0.5) + ($progress['direct'] * 0.3) + ($progress['group'] * 0.2);
        }

        return [
            'current_volume' => $currentVolume,
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'rank_requirements' => $rankRequirements,
            'progress' => $progress
        ];
    }

    /**
     * Get associate's commission details with downline contributions
     */
    public function getCommissionDetailsWithDownline($associateId, $filters = [])
    {
        $conditions = ["c.associate_id = :associate_id"];
        $params = ['associate_id' => $associateId];

        if (!empty($filters['date_from'])) {
            $conditions[] = "c.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "c.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['level'])) {
            $conditions[] = "c.level = :level";
            $params['level'] = $filters['level'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT c.*, p.amount as sale_amount, p.created_at as sale_date,
                   prop.title as property_title, u.name as customer_name,
                   down_u.name as downline_name, down.level as downline_level,
                   down.associate_code as downline_code
            FROM associate_commissions c
            JOIN payments p ON c.payment_id = p.id
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN associates down ON c.downline_associate_id = down.associate_id
            LEFT JOIN users down_u ON down.user_id = down_u.id
            {$whereClause}
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get team sales funnel
     */
    public function getTeamSalesFunnel($associateId)
    {
        $funnel = [];

        // Subquery for team member user IDs (direct + level 2)
        $teamUserIdsSql = "
            SELECT u.id FROM users u
            JOIN associates a ON u.id = a.user_id
            WHERE a.sponsor_id = :associate_id OR a.sponsor_id IN (
                SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
            )
        ";

        // Total leads in team
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_leads
            FROM leads l
            WHERE l.assigned_to IN ($teamUserIdsSql)
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $funnel['total_leads'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_leads'];

        // Converted leads (bookings)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_bookings
            FROM bookings b
            WHERE b.agent_id IN ($teamUserIdsSql)
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $funnel['total_bookings'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

        // Completed sales
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_sales
            FROM payments p
            WHERE p.status = 'completed' AND p.associate_id IN (
                SELECT associate_id FROM associates
                WHERE sponsor_id = :associate_id OR sponsor_id IN (
                    SELECT associate_id FROM associates WHERE sponsor_id = :associate_id
                )
            )
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $funnel['total_sales'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];

        // Calculate conversion rates
        $funnel['booking_conversion'] = $funnel['total_leads'] > 0 ?
            round(($funnel['total_bookings'] / $funnel['total_leads']) * 100, 2) : 0;

        $funnel['sales_conversion'] = $funnel['total_bookings'] > 0 ?
            round(($funnel['total_sales'] / $funnel['total_bookings']) * 100, 2) : 0;

        $funnel['overall_conversion'] = $funnel['total_leads'] > 0 ?
            round(($funnel['total_sales'] / $funnel['total_leads']) * 100, 2) : 0;

        return $funnel;
    }

    /**
     * Get associate's mentorship statistics
     */
    public function getMentorshipStats($associateId)
    {
        $stats = [];

        // Total mentees
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_mentees
            FROM associates
            WHERE sponsor_id = :associate_id
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $stats['total_mentees'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_mentees'];

        // Active mentees
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as active_mentees
            FROM associates
            WHERE sponsor_id = :associate_id AND status = 'active'
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $stats['active_mentees'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['active_mentees'];

        // Mentees with verified KYC
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as verified_mentees
            FROM associates
            WHERE sponsor_id = :associate_id AND kyc_status = 'verified'
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $stats['verified_mentees'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['verified_mentees'];

        // Average performance of mentees
        $stmt = $this->db->prepare("
            SELECT
                AVG(CASE WHEN p.amount IS NOT NULL THEN p.amount ELSE 0 END) as avg_sale_value,
                COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as total_mentee_sales,
                COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END), 0) as total_mentee_volume
            FROM associates a
            LEFT JOIN payments p ON a.associate_id = p.associate_id
            WHERE a.sponsor_id = :associate_id
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $performance = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['avg_mentee_sale'] = (float)($performance['avg_sale_value'] ?? 0);
        $stats['total_mentee_sales'] = (int)($performance['total_mentee_sales'] ?? 0);
        $stats['total_mentee_volume'] = (float)($performance['total_mentee_volume'] ?? 0);

        // Mentorship effectiveness score
        $effectivenessScore = 0;
        if ($stats['total_mentees'] > 0) {
            $activityRate = ($stats['active_mentees'] / $stats['total_mentees']) * 100;
            $verificationRate = ($stats['verified_mentees'] / $stats['total_mentees']) * 100;
            $performanceRate = min(($stats['total_mentee_volume'] / ($stats['total_mentees'] * 100000)) * 100, 100);

            $effectivenessScore = ($activityRate * 0.4) + ($verificationRate * 0.3) + ($performanceRate * 0.3);
        }

        $stats['effectiveness_score'] = round($effectivenessScore, 2);

        return $stats;
    }

    /**
     * Get associate's training and development progress
     */
    public function getTrainingProgress($associateId)
    {
        $progress = [];

        // Training modules completed
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_modules,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_modules,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_modules
            FROM associate_training
            WHERE associate_id = :associate_id
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $training = $stmt->fetch(PDO::FETCH_ASSOC);

        $progress['training'] = $training;
        $progress['training_completion'] = $training['total_modules'] > 0 ?
            round(($training['completed_modules'] / $training['total_modules']) * 100, 2) : 0;

        // Certification status
        $stmt = $this->db->prepare("
            SELECT certification_type, status, completed_date, expiry_date
            FROM associate_certifications
            WHERE associate_id = :associate_id
            ORDER BY completed_date DESC
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $progress['certifications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Skill assessments
        $stmt = $this->db->prepare("
            SELECT skill_name, proficiency_level, last_assessed, next_assessment_due
            FROM associate_skills
            WHERE associate_id = :associate_id
            ORDER BY last_assessed DESC
        ");
        $stmt->execute(['associate_id' => $associateId]);
        $progress['skills'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $progress;
    }

    /**
     * Get associates for admin with filters and pagination
     */
    public static function getAdminAssociates($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "a.status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'name', 'email', 'created_at', 'status'];
            $sort = in_array($filters['sort'] ?? '', $allowed_sorts) ? $filters['sort'] : 'created_at';
            // map sort fields to correct table aliases
            if ($sort === 'name' || $sort === 'email') $sort = 'u.' . $sort;
            elseif ($sort === 'id' || $sort === 'created_at' || $sort === 'status') $sort = 'a.' . $sort;

            $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY {$sort} {$order}";

            $sql = "
                SELECT a.*, u.name, u.email, u.phone, u.city
                FROM associates a
                LEFT JOIN users u ON a.user_id = u.id
                {$where_clause}
                {$order_clause}
                LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->bindValue(':limit', (int)$filters['per_page'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)(($filters['page'] - 1) * $filters['per_page']), \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Admin associates query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total associates count for pagination
     */
    public static function getAdminTotalAssociates($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "a.status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total 
                    FROM associates a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    {$where_clause}";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);
        } catch (\Exception $e) {
            error_log('Admin total associates query error: ' . $e->getMessage());
            return 0;
        }
    }
}
