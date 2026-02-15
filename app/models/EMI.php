<?php

namespace App\Models;

use App\Core\Model;

/**
 * EMI Model
 * Handles EMI plans, payments and scheduling
 */
class EMI extends Model
{
    protected static $table = 'emi_plans';
    protected static $primaryKey = 'id';

    protected $fillable = [
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
}
