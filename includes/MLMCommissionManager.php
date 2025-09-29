<?php
/**
 * MLM Commission Management System
 * Advanced commission tracking and payout system for associates
 */

class MLMCommissionManager {
    private $conn;
    private $logger;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->createCommissionTables();
    }

    /**
     * Create commission management tables
     */
    private function createCommissionTables() {
        // Associate levels and commission structure
        $sql = "CREATE TABLE IF NOT EXISTS associate_levels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            level_name VARCHAR(50) NOT NULL,
            level_number INT NOT NULL UNIQUE,
            min_team_size INT DEFAULT 0,
            min_personal_sales DECIMAL(15,2) DEFAULT 0,
            commission_percentage DECIMAL(5,2) NOT NULL,
            bonus_percentage DECIMAL(5,2) DEFAULT 0,
            override_percentage DECIMAL(5,2) DEFAULT 0,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $this->conn->query($sql);

        // Commission payouts table
        $sql = "CREATE TABLE IF NOT EXISTS commission_payouts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            payout_period_start DATE NOT NULL,
            payout_period_end DATE NOT NULL,
            total_commission DECIMAL(15,2) DEFAULT 0,
            total_bonus DECIMAL(15,2) DEFAULT 0,
            total_override DECIMAL(15,2) DEFAULT 0,
            gross_amount DECIMAL(15,2) NOT NULL,
            tds_deducted DECIMAL(15,2) DEFAULT 0,
            processing_fee DECIMAL(15,2) DEFAULT 0,
            net_amount DECIMAL(15,2) NOT NULL,
            payout_status ENUM('pending','processed','paid','cancelled') DEFAULT 'pending',
            payout_date DATE,
            transaction_id VARCHAR(100),
            bank_reference VARCHAR(100),
            remarks TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Associate achievements table
        $sql = "CREATE TABLE IF NOT EXISTS associate_achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            achievement_type ENUM('team_builder','sales_champion','leadership','target_achiever') NOT NULL,
            achievement_title VARCHAR(100) NOT NULL,
            achievement_description TEXT,
            target_value DECIMAL(15,2),
            achieved_value DECIMAL(15,2),
            achievement_date DATE NOT NULL,
            reward_amount DECIMAL(15,2),
            reward_type ENUM('cash','gift','travel','recognition') DEFAULT 'cash',
            status ENUM('pending','approved','paid','cancelled') DEFAULT 'pending',
            approved_by INT,
            approved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Insert default commission levels
        $this->insertDefaultCommissionLevels();
    }

    /**
     * Insert default commission levels
     */
    private function insertDefaultCommissionLevels() {
        $checkSql = "SELECT COUNT(*) as count FROM associate_levels";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $levels = [
                ['Associate', 1, 0, 0, 10.00, 0.00, 0.00, 'active'],
                ['Senior Associate', 2, 3, 500000, 12.00, 1.00, 0.50, 'active'],
                ['Team Leader', 3, 10, 1500000, 15.00, 2.00, 1.00, 'active'],
                ['Manager', 4, 25, 5000000, 18.00, 3.00, 2.00, 'active'],
                ['Senior Manager', 5, 50, 10000000, 20.00, 5.00, 3.00, 'active'],
                ['Director', 6, 100, 25000000, 22.00, 8.00, 5.00, 'active'],
                ['Senior Director', 7, 200, 50000000, 25.00, 10.00, 8.00, 'active']
            ];

            foreach ($levels as $level) {
                $sql = "INSERT INTO associate_levels (level_name, level_number, min_team_size, min_personal_sales, commission_percentage, bonus_percentage, override_percentage, status)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("siidddds", $level[0], $level[1], $level[2], $level[3], $level[4], $level[5], $level[6], $level[7]);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /**
     * Calculate commission for a plot booking
     */
    public function calculateBookingCommission($bookingId) {
        // Get booking details
        $bookingSql = "SELECT pb.*, p.current_price, a.user_id
                      FROM plot_bookings pb
                      JOIN plots p ON pb.plot_id = p.id
                      LEFT JOIN associates a ON pb.associate_id = a.id
                      WHERE pb.id = ?";
        $stmt = $this->conn->prepare($bookingSql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$booking) return false;

        $totalAmount = $booking['total_amount'] ?? $booking['booking_amount'];
        $associateId = $booking['associate_id'];

        // Calculate different types of commissions
        $commissions = [];

        // 1. Direct Commission
        if ($associateId) {
            $directCommission = $this->calculateDirectCommission($associateId, $totalAmount, $booking);
            if ($directCommission > 0) {
                $commissions[] = [
                    'associate_id' => $associateId,
                    'commission_type' => 'direct',
                    'commission_level' => 1,
                    'commission_amount' => $directCommission,
                    'remarks' => 'Direct commission for plot booking'
                ];
            }
        }

        // 2. Level Commissions (MLM)
        if ($associateId) {
            $levelCommissions = $this->calculateLevelCommissions($associateId, $totalAmount, $booking);
            $commissions = array_merge($commissions, $levelCommissions);
        }

        // 3. Bonus Commission
        $bonusCommissions = $this->calculateBonusCommissions($associateId, $totalAmount, $booking);
        $commissions = array_merge($commissions, $bonusCommissions);

        // 4. Override Commission
        $overrideCommissions = $this->calculateOverrideCommissions($associateId, $totalAmount, $booking);
        $commissions = array_merge($commissions, $overrideCommissions);

        // Insert commission records
        foreach ($commissions as $commission) {
            $this->insertCommissionRecord($bookingId, $commission);
        }

        return count($commissions);
    }

    /**
     * Calculate direct commission
     */
    private function calculateDirectCommission($associateId, $totalAmount, $booking) {
        // Get associate level and commission percentage
        $levelInfo = $this->getAssociateLevelInfo($associateId);
        if (!$levelInfo) return 0;

        $commissionPercentage = $levelInfo['commission_percentage'];
        return $totalAmount * ($commissionPercentage / 100);
    }

    /**
     * Calculate level commissions (MLM)
     */
    private function calculateLevelCommissions($associateId, $totalAmount, $booking) {
        $commissions = [];
        $hierarchy = $this->getAssociateHierarchy($associateId);

        $levelPercentages = [
            1 => 5,   // Level 1 - 5%
            2 => 3,   // Level 2 - 3%
            3 => 2,   // Level 3 - 2%
            4 => 1,   // Level 4 - 1%
            5 => 0.5  // Level 5 - 0.5%
        ];

        $level = 1;
        foreach ($hierarchy as $uplineId => $uplineInfo) {
            if ($level > 5) break;

            $percentage = $levelPercentages[$level] ?? 0;
            $amount = $totalAmount * ($percentage / 100);

            if ($amount > 0) {
                $commissions[] = [
                    'associate_id' => $uplineId,
                    'commission_type' => 'level',
                    'commission_level' => $level,
                    'commission_amount' => $amount,
                    'remarks' => "Level $level commission from downline sale"
                ];
            }

            $level++;
        }

        return $commissions;
    }

    /**
     * Calculate bonus commissions
     */
    private function calculateBonusCommissions($associateId, $totalAmount, $booking) {
        $commissions = [];

        if (!$associateId) return $commissions;

        $associateInfo = $this->getAssociateInfo($associateId);
        if (!$associateInfo) return $commissions;

        $levelInfo = $this->getAssociateLevelInfo($associateId);
        if (!$levelInfo) return $commissions;

        // Monthly sales bonus
        $monthlySales = $this->getAssociateMonthlySales($associateId);
        $bonusPercentage = $levelInfo['bonus_percentage'];

        if ($monthlySales >= 5000000) { // 50 lakh monthly target
            $bonusAmount = $totalAmount * ($bonusPercentage / 100);
            $commissions[] = [
                'associate_id' => $associateId,
                'commission_type' => 'bonus',
                'commission_level' => 0,
                'commission_amount' => $bonusAmount,
                'remarks' => 'Monthly sales bonus'
            ];
        }

        // Team building bonus
        $teamSize = $this->getAssociateTeamSize($associateId);
        if ($teamSize >= 10) {
            $teamBonus = $totalAmount * 0.02; // 2% team bonus
            $commissions[] = [
                'associate_id' => $associateId,
                'commission_type' => 'bonus',
                'commission_level' => 0,
                'commission_amount' => $teamBonus,
                'remarks' => 'Team building bonus'
            ];
        }

        return $commissions;
    }

    /**
     * Calculate override commissions
     */
    private function calculateOverrideCommissions($associateId, $totalAmount, $booking) {
        $commissions = [];

        if (!$associateId) return $commissions;

        $associateInfo = $this->getAssociateInfo($associateId);
        $levelInfo = $this->getAssociateLevelInfo($associateId);

        if (!$associateInfo || !$levelInfo) return $commissions;

        // Override commission for leaders
        if ($associateInfo['level'] >= 3) { // Team Leader and above
            $overridePercentage = $levelInfo['override_percentage'];
            $overrideAmount = $totalAmount * ($overridePercentage / 100);

            $commissions[] = [
                'associate_id' => $associateId,
                'commission_type' => 'override',
                'commission_level' => $associateInfo['level'],
                'commission_amount' => $overrideAmount,
                'remarks' => 'Leadership override commission'
            ];
        }

        return $commissions;
    }

    /**
     * Insert commission record
     */
    private function insertCommissionRecord($bookingId, $commission) {
        $sql = "INSERT INTO commission_tracking (
            booking_id, associate_id, commission_type, commission_level,
            commission_amount, payment_status, remarks
        ) VALUES (?, ?, ?, ?, ?, 'pending', ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisdds",
            $bookingId,
            $commission['associate_id'],
            $commission['commission_type'],
            $commission['commission_level'],
            $commission['commission_amount'],
            $commission['remarks']
        );
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get associate level info
     */
    private function getAssociateLevelInfo($associateId) {
        $sql = "SELECT al.* FROM associates a
                JOIN associate_levels al ON a.level = al.level_number
                WHERE a.id = ? AND al.status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $levelInfo = $result->fetch_assoc();
        $stmt->close();

        return $levelInfo;
    }

    /**
     * Get associate info
     */
    private function getAssociateInfo($associateId) {
        $sql = "SELECT a.*, u.full_name, u.email, u.phone FROM associates a
                JOIN users u ON a.user_id = u.id WHERE a.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $info = $result->fetch_assoc();
        $stmt->close();

        return $info;
    }

    /**
     * Get associate hierarchy
     */
    private function getAssociateHierarchy($associateId, $maxLevels = 5) {
        $hierarchy = [];
        $currentId = $associateId;
        $level = 0;

        while ($currentId && $level < $maxLevels) {
            $sql = "SELECT sponsor_id FROM associates WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $currentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) break;

            $associate = $result->fetch_assoc();
            $stmt->close();

            if ($associate['sponsor_id']) {
                $hierarchy[$associate['sponsor_id']] = ['level' => $level + 1];
                $currentId = $associate['sponsor_id'];
                $level++;
            } else {
                break;
            }
        }

        return $hierarchy;
    }

    /**
     * Get associate monthly sales
     */
    private function getAssociateMonthlySales($associateId) {
        $currentMonth = date('Y-m-01');
        $nextMonth = date('Y-m-01', strtotime('+1 month'));

        $sql = "SELECT SUM(pb.total_amount) as monthly_sales
                FROM plot_bookings pb
                WHERE pb.associate_id = ? AND pb.status IN ('confirmed', 'completed')
                AND pb.booking_date >= ? AND pb.booking_date < ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $associateId, $currentMonth, $nextMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $sales = $result->fetch_assoc()['monthly_sales'] ?? 0;
        $stmt->close();

        return $sales;
    }

    /**
     * Get associate team size
     */
    private function getAssociateTeamSize($associateId) {
        $sql = "SELECT COUNT(*) as team_size FROM associates WHERE sponsor_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $teamSize = $result->fetch_assoc()['team_size'] ?? 0;
        $stmt->close();

        return $teamSize;
    }

    /**
     * Process commission payouts
     */
    public function processCommissionPayouts($associateId, $periodStart, $periodEnd) {
        // Get all pending commissions for the period
        $sql = "SELECT ct.*, pb.total_amount as booking_amount
                FROM commission_tracking ct
                JOIN plot_bookings pb ON ct.booking_id = pb.id
                WHERE ct.associate_id = ? AND ct.payment_status = 'pending'
                AND pb.booking_date >= ? AND pb.booking_date <= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $associateId, $periodStart, $periodEnd);
        $stmt->execute();
        $result = $stmt->get_result();

        $commissions = [];
        $totalCommission = 0;
        $totalBonus = 0;
        $totalOverride = 0;

        while ($row = $result->fetch_assoc()) {
            $commissions[] = $row;
            if ($row['commission_type'] === 'bonus') {
                $totalBonus += $row['commission_amount'];
            } elseif ($row['commission_type'] === 'override') {
                $totalOverride += $row['commission_amount'];
            } else {
                $totalCommission += $row['commission_amount'];
            }
        }
        $stmt->close();

        $grossAmount = $totalCommission + $totalBonus + $totalOverride;

        if ($grossAmount <= 0) {
            return ['success' => false, 'message' => 'No commissions to process'];
        }

        // Calculate deductions
        $tdsRate = 0.05; // 5% TDS
        $processingFee = 100; // Fixed processing fee
        $tdsAmount = $grossAmount * $tdsRate;
        $netAmount = $grossAmount - $tdsAmount - $processingFee;

        // Create payout record
        $payoutSql = "INSERT INTO commission_payouts (
            associate_id, payout_period_start, payout_period_end,
            total_commission, total_bonus, total_override, gross_amount,
            tds_deducted, processing_fee, net_amount, payout_status, remarks, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";

        $stmt = $this->conn->prepare($payoutSql);
        $remarks = "Commission payout for period: " . $periodStart . " to " . $periodEnd;
        $createdBy = $_SESSION['user_id'] ?? 1;
        $stmt->bind_param("issddddddddss",
            $associateId,
            $periodStart,
            $periodEnd,
            $totalCommission,
            $totalBonus,
            $totalOverride,
            $grossAmount,
            $tdsAmount,
            $processingFee,
            $netAmount,
            $remarks,
            $createdBy
        );
        $result = $stmt->execute();
        $payoutId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Commission payout created for associate ID: $associateId, Amount: $netAmount", 'info', 'commission');
        }

        return $result ? [
            'success' => true,
            'payout_id' => $payoutId,
            'gross_amount' => $grossAmount,
            'net_amount' => $netAmount,
            'commissions' => $commissions
        ] : ['success' => false, 'message' => 'Failed to create payout'];
    }

    /**
     * Get commission summary for associate
     */
    public function getCommissionSummary($associateId, $periodStart = null, $periodEnd = null) {
        if (!$periodStart) $periodStart = date('Y-m-01');
        if (!$periodEnd) $periodEnd = date('Y-m-t');

        $summary = [];

        // Total pending commissions
        $sql = "SELECT SUM(commission_amount) as pending FROM commission_tracking
                WHERE associate_id = ? AND payment_status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary['pending'] = $result->fetch_assoc()['pending'] ?? 0;
        $stmt->close();

        // Total paid commissions
        $sql = "SELECT SUM(commission_amount) as paid FROM commission_tracking
                WHERE associate_id = ? AND payment_status = 'paid'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary['paid'] = $result->fetch_assoc()['paid'] ?? 0;
        $stmt->close();

        // Monthly breakdown
        $sql = "SELECT MONTH(ct.created_at) as month, YEAR(ct.created_at) as year,
                       SUM(ct.commission_amount) as amount, ct.commission_type
                FROM commission_tracking ct
                WHERE ct.associate_id = ? AND ct.payment_status = 'paid'
                GROUP BY YEAR(ct.created_at), MONTH(ct.created_at), ct.commission_type
                ORDER BY year DESC, month DESC
                LIMIT 12";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary['monthly_breakdown'] = [];
        while ($row = $result->fetch_assoc()) {
            $summary['monthly_breakdown'][] = $row;
        }
        $stmt->close();

        return $summary;
    }

    /**
     * Get associate dashboard data
     */
    public function getAssociateDashboard($associateId) {
        $dashboard = [];

        // Associate info
        $associateInfo = $this->getAssociateInfo($associateId);
        $dashboard['associate_info'] = $associateInfo;

        // Commission summary
        $dashboard['commission_summary'] = $this->getCommissionSummary($associateId);

        // Team information
        $dashboard['team_info'] = [
            'team_size' => $this->getAssociateTeamSize($associateId),
            'active_members' => $this->getActiveTeamMembers($associateId),
            'total_team_sales' => $this->getTeamSales($associateId)
        ];

        // Recent commissions
        $sql = "SELECT ct.*, pb.booking_number, pb.total_amount as booking_amount
                FROM commission_tracking ct
                LEFT JOIN plot_bookings pb ON ct.booking_id = pb.id
                WHERE ct.associate_id = ?
                ORDER BY ct.created_at DESC
                LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['recent_commissions'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['recent_commissions'][] = $row;
        }
        $stmt->close();

        // Achievements
        $dashboard['achievements'] = $this->getAssociateAchievements($associateId);

        return $dashboard;
    }

    /**
     * Get active team members
     */
    private function getActiveTeamMembers($associateId) {
        $sql = "SELECT COUNT(*) as active_count FROM associates
                WHERE sponsor_id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['active_count'] ?? 0;
        $stmt->close();

        return $count;
    }

    /**
     * Get team sales
     */
    private function getTeamSales($associateId) {
        $sql = "SELECT SUM(pb.total_amount) as team_sales
                FROM plot_bookings pb
                JOIN associates a ON pb.associate_id = a.id
                WHERE a.sponsor_id = ? AND pb.status IN ('confirmed', 'completed')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $sales = $result->fetch_assoc()['team_sales'] ?? 0;
        $stmt->close();

        return $sales;
    }

    /**
     * Get associate achievements
     */
    private function getAssociateAchievements($associateId) {
        $sql = "SELECT * FROM associate_achievements
                WHERE associate_id = ? AND status = 'approved'
                ORDER BY achievement_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $achievements = [];
        while ($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
        $stmt->close();

        return $achievements;
    }

    /**
     * Generate commission report
     */
    public function generateCommissionReport($associateId, $startDate, $endDate) {
        $report = [];

        // Commission breakdown by type
        $sql = "SELECT commission_type, COUNT(*) as count, SUM(commission_amount) as total
                FROM commission_tracking
                WHERE associate_id = ? AND created_at >= ? AND created_at <= ?
                GROUP BY commission_type";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $associateId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['breakdown_by_type'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['breakdown_by_type'][] = $row;
        }
        $stmt->close();

        // Monthly commission trend
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                       SUM(commission_amount) as monthly_total
                FROM commission_tracking
                WHERE associate_id = ? AND created_at >= ? AND created_at <= ?
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $associateId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['monthly_trend'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['monthly_trend'][] = $row;
        }
        $stmt->close();

        // Top performing months
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                       COUNT(*) as commission_count, SUM(commission_amount) as total
                FROM commission_tracking
                WHERE associate_id = ? AND created_at >= ? AND created_at <= ?
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY total DESC
                LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $associateId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['top_months'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['top_months'][] = $row;
        }
        $stmt->close();

        return $report;
    }
}
?>
