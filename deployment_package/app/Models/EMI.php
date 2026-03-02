<?php

namespace App\Models;

/**
 * EMI Model
 * Handles EMI plans, payments and scheduling
 */
class EMI extends Model
{
    protected static $table = 'emi_plans';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'customer_id',
        'booking_id',
        'total_amount',
        'down_payment',
        'emi_amount',
        'interest_rate',
        'tenure_months',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    /**
     * Create a complete EMI plan with installments
     */
    public function createPlan($data)
    {
        try {
            $db = self::getConnection();
            $db->beginTransaction();

            // Calculate EMI amount
            $principal = floatval($data['total_amount']) - floatval($data['down_payment']);
            $rate = floatval($data['interest_rate']) / (12 * 100); // Monthly interest rate
            $tenure = intval($data['tenure_months']);

            if ($rate > 0) {
                $emiAmount = $principal * $rate * pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1);
            } else {
                $emiAmount = $principal / $tenure;
            }
            $emiAmount = round($emiAmount, 2);

            // Calculate end date
            $startDate = new \DateTime($data['start_date']);
            $endDate = clone $startDate;
            $endDate->modify("+$tenure months");
            $endDateFormatted = $endDate->format('Y-m-d');

            // Insert EMI plan
            $sql = "INSERT INTO emi_plans (customer_id, property_id, booking_id, total_amount, interest_rate, tenure_months, emi_amount, down_payment, start_date, end_date, created_by, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['customer_id'],
                $data['property_id'] ?? null,
                $data['booking_id'] ?? null,
                $data['total_amount'],
                $data['interest_rate'],
                $tenure,
                $emiAmount,
                $data['down_payment'],
                $data['start_date'],
                $endDateFormatted,
                $_SESSION['admin_id'] ?? 1
            ]);

            $emiPlanId = $db->lastInsertId();

            if (!$emiPlanId) {
                throw new \Exception("Failed to create EMI plan");
            }

            // Create installments
            $installmentDate = clone $startDate;
            $remainingPrincipal = $principal;

            for ($i = 1; $i <= $tenure; $i++) {
                $interestComponent = round($remainingPrincipal * $rate, 2);
                $principalComponent = round($emiAmount - $interestComponent, 2);
                $remainingPrincipal -= $principalComponent;
                $installmentDateFormatted = $installmentDate->format('Y-m-d');

                $sqlInst = "INSERT INTO emi_installments (emi_plan_id, installment_number, due_date, amount, principal_component, interest_component, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                $stmtInst = $db->prepare($sqlInst);
                $stmtInst->execute([
                    $emiPlanId,
                    $i,
                    $installmentDateFormatted,
                    $emiAmount,
                    $principalComponent,
                    $interestComponent
                ]);

                $installmentDate->modify('+1 month');
            }

            // Create notification for the customer
            $notificationMessage = "Your EMI plan has been created with monthly installment of ₹" . number_format($emiAmount, 2);
            $notificationLink = "emi/view.php?id=" . $emiPlanId;

            // Check if notifications table exists and has user_id
            try {
                $db->insert('notifications', [
                    'user_id' => $data['customer_id'],
                    'type' => 'emi',
                    'title' => 'EMI Plan Created',
                    'message' => $notificationMessage,
                    'link' => $notificationLink,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Ignore notification error if table/column missing
            }

            $db->commit();
            return [
                'success' => true,
                'emi_plan_id' => $emiPlanId,
                'emi_amount' => $emiAmount
            ];
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("EMI::createPlan error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get EMI statistics for dashboard
     */
    public function getStats()
    {
        $db = self::getConnection();
        $currentMonth = date('Y-m');

        // 1. Active EMI plans count
        $activeSql = "SELECT COUNT(*) FROM emi_plans WHERE status = 'active'";
        $activeCount = $db->query($activeSql)->fetchColumn();

        // 2. Monthly EMI collection (Total expected for current month)
        $collectionSql = "SELECT COALESCE(SUM(amount), 0) 
                          FROM emi_installments ei 
                          JOIN emi_plans ep ON ei.emi_plan_id = ep.id 
                          WHERE ep.status = 'active' 
                          AND DATE_FORMAT(ei.due_date, '%Y-%m') = ?";
        $stmtColl = $db->prepare($collectionSql);
        $stmtColl->execute([$currentMonth]);
        $monthlyCollection = $stmtColl->fetchColumn();

        // 3. Pending EMIs count (due this month)
        $pendingSql = "SELECT COUNT(*) 
                       FROM emi_installments ei 
                       JOIN emi_plans ep ON ei.emi_plan_id = ep.id 
                       WHERE ep.status = 'active' 
                       AND ei.status = 'pending' 
                       AND DATE_FORMAT(ei.due_date, '%Y-%m') = ?";
        $stmtPend = $db->prepare($pendingSql);
        $stmtPend->execute([$currentMonth]);
        $pendingCount = $stmtPend->fetchColumn();

        // 4. Overdue EMIs count
        $overdueSql = "SELECT COUNT(*) 
                       FROM emi_installments ei 
                       JOIN emi_plans ep ON ei.emi_plan_id = ep.id 
                       WHERE ep.status = 'active' 
                       AND ei.status IN ('pending', 'overdue') 
                       AND ei.due_date < CURDATE()";
        $overdueCount = $db->query($overdueSql)->fetchColumn();

        return [
            'active_count' => $activeCount,
            'monthly_collection' => '₹' . number_format($monthlyCollection, 2),
            'pending_count' => $pendingCount,
            'overdue_count' => $overdueCount
        ];
    }

    /**
     * Get full details of an EMI plan including customer and property info
     */
    public function getPlanDetails($id)
    {
        $db = self::getConnection();
        $sql = "SELECT ep.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                       p.title as property_title, p.location as property_location
                FROM emi_plans ep
                LEFT JOIN customers c ON ep.customer_id = c.id
                LEFT JOIN properties p ON ep.property_id = p.id
                WHERE ep.id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all installments for a specific EMI plan
     */
    public function getInstallments($planId)
    {
        $db = self::getConnection();
        $sql = "SELECT * FROM emi_installments WHERE emi_plan_id = ? ORDER BY installment_number ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Record payment for a specific installment
     */
    public function recordInstallmentPayment($data)
    {
        $db = self::getConnection();

        try {
            $db->beginTransaction();

            $installmentId = (int)$data['installment_id'];
            $paymentDate = $data['payment_date'];
            $paymentMethod = $data['payment_method'];
            $notes = $data['notes'] ?? '';

            // Get installment details
            $sql = "SELECT ei.*, ep.customer_id, ep.property_id 
                    FROM emi_installments ei
                    JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                    WHERE ei.id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$installmentId]);
            $installment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$installment) {
                throw new \Exception('Installment not found');
            }

            if ($installment['status'] === 'paid') {
                throw new \Exception('This installment is already paid');
            }

            // Calculate late fee (simplified version for now, as late fee config might vary)
            $lateFee = 0;
            if (strtotime($paymentDate) > strtotime($installment['due_date'])) {
                // You could fetch from a config table here if needed
                // For now, let's assume no automatic calculation or handle it via $data
                $lateFee = floatval($data['late_fee'] ?? 0);
            }

            $totalAmount = $installment['amount'] + $lateFee;
            $transactionId = 'EMI' . time() . rand(1000, 9999);
            $description = "EMI Payment - Installment #" . $installment['installment_number'];
            if ($lateFee > 0) {
                $description .= " (Including Late Fee: ₹" . number_format($lateFee, 2) . ")";
            }

            // 1. Create payment record in main payments table
            $sqlPay = "INSERT INTO payments (
                            transaction_id, customer_id, property_id, amount, 
                            payment_type, payment_method, description, status, 
                            payment_date, created_by
                        ) VALUES (?, ?, ?, ?, 'emi', ?, ?, 'completed', ?, ?)";
            $stmtPay = $db->prepare($sqlPay);
            $stmtPay->execute([
                $transactionId,
                $installment['customer_id'],
                $installment['property_id'],
                $totalAmount,
                $paymentMethod,
                $description . ($notes ? " - " . $notes : ""),
                $paymentDate,
                $_SESSION['admin_id'] ?? 1
            ]);

            $paymentId = $db->lastInsertId();

            // 2. Update installment status
            $sqlInst = "UPDATE emi_installments SET 
                            status = 'paid', 
                            payment_date = ?, 
                            payment_id = ?,
                            late_fee = ?,
                            notes = ?
                        WHERE id = ?";
            $stmtInst = $db->prepare($sqlInst);
            $stmtInst->execute([$paymentDate, $paymentId, $lateFee, $notes, $installmentId]);

            // 3. Update EMI plan status if all installments are paid
            $planId = $installment['emi_plan_id'];
            $sqlCheck = "SELECT COUNT(*) FROM emi_installments WHERE emi_plan_id = ? AND status != 'paid'";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([$planId]);
            $remaining = $stmtCheck->fetchColumn();

            if ($remaining == 0) {
                $sqlClose = "UPDATE emi_plans SET status = 'completed' WHERE id = ?";
                $db->prepare($sqlClose)->execute([$planId]);
            }

            $db->commit();
            return $paymentId;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get all EMI plans with filters for DataTables
     */
    public function getFilteredPlans($params)
    {
        $db = self::getConnection();

        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = $params['search'] ?? '';
        $orderBy = $params['orderBy'] ?? 'ep.id';
        $orderDir = $params['orderDir'] ?? 'DESC';

        // Base query
        $sql = "SELECT ep.*, c.name as customer_name, p.title as property_title
                FROM emi_plans ep
                LEFT JOIN customers c ON ep.customer_id = c.id
                LEFT JOIN properties p ON ep.property_id = p.id";

        $where = [];
        $bindings = [];

        if (!empty($search)) {
            $where[] = "(c.name LIKE ? OR p.title LIKE ? OR ep.total_amount LIKE ? OR ep.status LIKE ?)";
            $searchParam = "%$search%";
            $bindings = array_fill(0, 4, $searchParam);
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Count filtered records
        $countSql = "SELECT COUNT(*) FROM ($sql) as subquery";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($bindings);
        $filteredCount = $stmtCount->fetchColumn();

        // Count total records
        $totalCountSql = "SELECT COUNT(*) FROM emi_plans";
        $totalCount = $db->query($totalCountSql)->fetchColumn();

        // Add ordering and pagination
        $sql .= " ORDER BY $orderBy $orderDir LIMIT ?, ?";
        $bindings[] = (int)$start;
        $bindings[] = (int)$length;

        $stmt = $db->prepare($sql);
        // Bind integer values for LIMIT
        $stmt->bindValue(count($bindings) - 1, (int)$length, \PDO::PARAM_INT);
        $stmt->bindValue(count($bindings) - 2, (int)$start, \PDO::PARAM_INT);

        // Re-bind other parameters
        for ($i = 0; $i < count($bindings) - 2; $i++) {
            $stmt->bindValue($i + 1, $bindings[$i]);
        }

        $stmt->execute();
        $plans = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Add progress info for each plan
        foreach ($plans as &$plan) {
            $progressSql = "SELECT 
                                COUNT(*) as total_installments,
                                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_installments
                             FROM emi_installments 
                             WHERE emi_plan_id = ?";
            $stmtProg = $db->prepare($progressSql);
            $stmtProg->execute([$plan['id']]);
            $progress = $stmtProg->fetch(\PDO::FETCH_ASSOC);

            $plan['total_installments'] = $progress['total_installments'];
            $plan['paid_installments'] = $progress['paid_installments'];
            $plan['progress_percent'] = $progress['total_installments'] > 0
                ? round(($progress['paid_installments'] / $progress['total_installments']) * 100, 2)
                : 0;
        }

        return [
            'data' => $plans,
            'totalRecords' => $totalCount,
            'filteredRecords' => $filteredCount
        ];
    }

    /**
     * Get EMI plan by booking ID
     */
    public function getByBookingId($bookingId)
    {
        return $this->where('booking_id', $bookingId)->first();
    }

    /**
     * Get active EMI plans for a customer
     */
    public function getActiveByCustomerId($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Calculate monthly EMI amount (Standard formula)
     * P * r * (1+r)^n / ((1+r)^n - 1)
     */
    public function calculateEMIAmount($principal, $annualRate, $tenureMonths)
    {
        if ($annualRate == 0) {
            return $principal / $tenureMonths;
        }

        $monthlyRate = ($annualRate / 100) / 12;
        $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / (pow(1 + $monthlyRate, $tenureMonths) - 1);

        return round($emi, 2);
    }

    /**
     * Get EMI payment schedule
     */
    public function getSchedule($emiPlanId)
    {
        $sql = "SELECT * FROM emi_payments WHERE emi_plan_id = ? ORDER BY due_date ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$emiPlanId]);
        return $stmt->fetchAll();
    }

    /**
     * Record an EMI payment
     */
    public function recordPayment($data)
    {
        $sql = "INSERT INTO emi_payments (emi_plan_id, amount, payment_date, transaction_id, status, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([
            $data['emi_plan_id'],
            $data['amount'],
            $data['payment_date'] ?? date('Y-m-d'),
            $data['transaction_id'] ?? null,
            $data['status'] ?? 'completed',
            $data['notes'] ?? null
        ]);
    }

    /**
     * Update EMI plan status
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE emi_plans SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    /**
     * Calculate foreclosure amount
     * Sum of principal component of all unpaid installments
     */
    public function calculateForeclosureAmount($planId)
    {
        $db = self::getConnection();
        $sql = "SELECT SUM(principal_component) 
                FROM emi_installments 
                WHERE emi_plan_id = ? AND status != 'paid'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$planId]);
        return floatval($stmt->fetchColumn() ?: 0);
    }

    /**
     * Foreclose an EMI plan
     */
    public function foreclosePlan($data)
    {
        $db = self::getConnection();

        try {
            $db->beginTransaction();

            $planId = $data['emi_plan_id'];
            $amount = $data['amount'];
            $paymentDate = $data['payment_date'];
            $paymentMethod = $data['payment_method'];
            $notes = $data['notes'] ?? '';

            // Get plan details
            $plan = $this->getPlanDetails($planId);
            if (!$plan) throw new \Exception("Plan not found");
            if ($plan['status'] !== 'active') throw new \Exception("Plan is not active");

            // 1. Create payment record
            $transactionId = 'FC' . time() . rand(1000, 9999);
            $description = "EMI Foreclosure Payment";

            $sqlPay = "INSERT INTO payments (
                            transaction_id, customer_id, property_id, amount, 
                            payment_type, payment_method, description, status, 
                            payment_date, created_by
                        ) VALUES (?, ?, ?, ?, 'foreclosure', ?, ?, 'completed', ?, ?)";
            $stmtPay = $db->prepare($sqlPay);
            $stmtPay->execute([
                $transactionId,
                $plan['customer_id'],
                $plan['property_id'],
                $amount,
                $paymentMethod,
                $description . ($notes ? " - " . $notes : ""),
                $paymentDate,
                $_SESSION['admin_id'] ?? 1
            ]);

            $paymentId = $db->lastInsertId();

            // 2. Update all pending installments to 'paid'
            $sqlInst = "UPDATE emi_installments SET 
                            status = 'paid', 
                            payment_date = ?, 
                            payment_id = ?,
                            notes = CONCAT(COALESCE(notes, ''), ' - Foreclosed')
                        WHERE emi_plan_id = ? AND status != 'paid'";
            $stmtInst = $db->prepare($sqlInst);
            $stmtInst->execute([$paymentDate, $paymentId, $planId]);

            // 3. Update EMI plan
            $sqlPlan = "UPDATE emi_plans SET 
                            status = 'completed', 
                            foreclosure_date = ?,
                            foreclosure_amount = ?,
                            foreclosure_payment_id = ?
                        WHERE id = ?";
            $stmtPlan = $db->prepare($sqlPlan);
            $stmtPlan->execute([$paymentDate, $amount, $paymentId, $planId]);

            // 4. Log to foreclosure_logs for audit/reporting
            // Check if table exists first to avoid errors during migration
            try {
                $sqlLog = "INSERT INTO foreclosure_logs (
                                emi_plan_id, status, message, foreclosure_amount, 
                                original_amount, penalty_amount, waiver_amount, notes,
                                attempted_at, attempted_by
                           ) VALUES (?, 'success', 'Foreclosure successful', ?, ?, ?, ?, ?, NOW(), ?)";
                $stmtLog = $db->prepare($sqlLog);
                $stmtLog->execute([
                    $planId,
                    $amount,
                    $data['original_amount'] ?? 0,
                    $data['penalty_amount'] ?? 0,
                    $data['waiver_amount'] ?? 0,
                    $notes,
                    $_SESSION['admin_id'] ?? 1
                ]);
            } catch (\Exception $e) {
                // Ignore log error if table missing, but log it
                error_log("Foreclosure logging failed: " . $e->getMessage());
            }

            $db->commit();
            return true;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get Foreclosure Statistics
     */
    public function getForeclosureStats()
    {
        $db = self::getConnection();

        // Use foreclosure_logs if available for complete history including attempts
        try {
            $sql = "SELECT 
                        COUNT(*) as total_attempts,
                        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_attempts,
                        SUM(CASE WHEN status != 'success' THEN 1 ELSE 0 END) as failed_attempts,
                        COALESCE(SUM(CASE WHEN status = 'success' THEN foreclosure_amount ELSE 0 END), 0) as total_foreclosure_amount,
                        COALESCE(AVG(CASE WHEN status = 'success' THEN foreclosure_amount ELSE NULL END), 0) as average_foreclosure_amount
                    FROM foreclosure_logs";

            $stats = $db->query($sql)->fetch(\PDO::FETCH_ASSOC);

            if ($stats) {
                return $stats;
            }
        } catch (\Exception $e) {
            // Fallback to emi_plans if table doesn't exist
        }

        // Fallback implementation using emi_plans
        $sql = "SELECT 
                    COUNT(*) as total_foreclosures,
                    COALESCE(SUM(foreclosure_amount), 0) as total_amount,
                    COALESCE(AVG(foreclosure_amount), 0) as average_amount
                FROM emi_plans 
                WHERE status = 'completed' AND foreclosure_date IS NOT NULL";

        $stats = $db->query($sql)->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_attempts' => $stats['total_foreclosures'],
            'successful_attempts' => $stats['total_foreclosures'],
            'failed_attempts' => 0,
            'total_foreclosure_amount' => $stats['total_amount'],
            'average_foreclosure_amount' => $stats['average_amount']
        ];
    }

    /**
     * Get Monthly Foreclosure Trend
     */
    public function getForeclosureTrend($months = 12)
    {
        $db = self::getConnection();

        try {
            $sql = "SELECT 
                        DATE_FORMAT(attempted_at, '%Y-%m') as month,
                        COUNT(*) as total_attempts,
                        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_attempts,
                        SUM(CASE WHEN status = 'success' THEN foreclosure_amount ELSE 0 END) as total_foreclosure_amount
                    FROM foreclosure_logs 
                    WHERE attempted_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                    GROUP BY month
                    ORDER BY month DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute([(int)$months]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback to emi_plans
        }

        $sql = "SELECT 
                    DATE_FORMAT(foreclosure_date, '%Y-%m') as month,
                    COUNT(*) as total_attempts,
                    COUNT(*) as successful_attempts,
                    SUM(foreclosure_amount) as total_foreclosure_amount
                FROM emi_plans 
                WHERE status = 'completed' 
                AND foreclosure_date IS NOT NULL
                AND foreclosure_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY month
                ORDER BY month DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([(int)$months]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get Detailed Foreclosure Report
     */
    public function getForeclosureReportData($filters = [])
    {
        $db = self::getConnection();

        try {
            $sql = "SELECT 
                        fl.emi_plan_id,
                        c.name as customer_name,
                        p.title as property_title,
                        fl.status as foreclosure_status,
                        fl.foreclosure_amount,
                        fl.original_amount,
                        fl.penalty_amount,
                        fl.waiver_amount,
                        fl.notes,
                        fl.attempted_at,
                        u.auser as admin_name
                    FROM foreclosure_logs fl
                    JOIN emi_plans ep ON fl.emi_plan_id = ep.id
                    LEFT JOIN customers c ON ep.customer_id = c.id
                    LEFT JOIN properties p ON ep.property_id = p.id
                    LEFT JOIN admin u ON fl.attempted_by = u.id
                    WHERE 1=1";

            $params = [];

            if (!empty($filters['start_date'])) {
                $sql .= " AND fl.attempted_at >= ?";
                $params[] = $filters['start_date'];
            }

            if (!empty($filters['end_date'])) {
                $sql .= " AND fl.attempted_at <= ?";
                $params[] = $filters['end_date'];
            }

            if (!empty($filters['customer_id'])) {
                $sql .= " AND ep.customer_id = ?";
                $params[] = $filters['customer_id'];
            }

            $sql .= " ORDER BY fl.attempted_at DESC LIMIT 500";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback to emi_plans
        }

        $sql = "SELECT 
                    ep.id as emi_plan_id,
                    ep.customer_id,
                    c.name as customer_name,
                    p.title as property_title,
                    'success' as foreclosure_status,
                    ep.foreclosure_amount,
                    ep.foreclosure_date as attempted_at,
                    pay.created_by as foreclosed_by_id,
                    u.auser as admin_name
                FROM emi_plans ep
                LEFT JOIN customers c ON ep.customer_id = c.id
                LEFT JOIN properties p ON ep.property_id = p.id
                LEFT JOIN payments pay ON ep.foreclosure_payment_id = pay.id
                LEFT JOIN admin u ON pay.created_by = u.aid
                WHERE ep.status = 'completed' AND ep.foreclosure_date IS NOT NULL";

        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND ep.foreclosure_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND ep.foreclosure_date <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND ep.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        $sql .= " ORDER BY ep.foreclosure_date DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get details for installment receipt
     */
    public function getInstallmentReceiptDetails($installmentId)
    {
        $db = self::getConnection();
        $query = "SELECT ei.*, ep.*, c.name as customer_name, c.phone as customer_phone,
                         c.email as customer_email, p.title as property_title,
                         p.address as property_address, p.location as property_location, py.transaction_id,
                         py.payment_method, py.description as payment_description
                  FROM emi_installments ei
                  JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                  JOIN customers c ON ep.customer_id = c.id
                  JOIN properties p ON ep.property_id = p.id
                  LEFT JOIN payments py ON ei.payment_id = py.id
                  WHERE ei.id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$installmentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
