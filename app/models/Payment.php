<?php

namespace App\Models;

use App\Models\Model;
use App\Core\Database;
use PDO;
use Exception;

/**
 * Payment Model
 * Handles all payment-related database operations
 */
class Payment extends Model
{
    protected static $table = 'payments';
    protected static $primaryKey = 'id';

    /**
     * Get dashboard statistics for accounting
     */
    public function getDashboardStats()
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $currentMonth = date('Y-m');

        // 1. Get Monthly Revenue
        $revenueQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
                         WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? 
                         AND (status = 'completed' OR status = 'success')";
        $stmt = $conn->prepare($revenueQuery);
        $stmt->execute([$currentMonth]);
        $revenue = $stmt->fetchColumn();
        $stmt->closeCursor();

        // 2. Get Monthly Expenses
        // Assuming expenses table exists and follows similar pattern
        $expenses = 0;
        try {
            $expensesQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
                              WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?";
            $stmt = $conn->prepare($expensesQuery);
            $stmt->execute([$currentMonth]);
            $expenses = $stmt->fetchColumn();
            $stmt->closeCursor();
        } catch (Exception $e) {
            // Ignore if expenses table doesn't exist
        }

        // 3. Get Pending Payments Count
        $pendingQuery = "SELECT COUNT(*) as total FROM payments WHERE status = 'pending'";
        $stmt = $conn->query($pendingQuery);
        $pendingPayments = $stmt->fetchColumn();
        $stmt->closeCursor();

        // Calculate Monthly Profit
        $monthlyProfit = $revenue - $expenses;

        return [
            'monthly_revenue' => '₹' . number_format($revenue, 2),
            'monthly_expenses' => '₹' . number_format($expenses, 2),
            'pending_payments' => $pendingPayments,
            'monthly_profit' => '₹' . number_format($monthlyProfit, 2)
        ];
    }

    /**
     * Record a new payment with related operations (transaction, booking update, notification)
     */
    public function recordPayment(array $data)
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            $conn->beginTransaction();

            // Generate unique transaction ID
            $transactionId = 'TXN' . time() . rand(1000, 9999);

            // Insert payment record
            $sql = "INSERT INTO payments (
                        transaction_id, 
                        customer_id, 
                        amount, 
                        payment_type,
                        payment_method,
                        notes,
                        status,
                        payment_date,
                        created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $transactionId,
                $data['customer_id'],
                $data['amount'],
                $data['payment_type'],
                $data['payment_method'],
                $data['description'] ?? '', // Map description to notes
                $data['status'] ?? 'completed',
                $_SESSION['admin_id'] ?? 1
            ]);

            $paymentId = $conn->lastInsertId();

            // If this is a booking payment, update the booking status
            if ($data['payment_type'] === 'booking' && !empty($data['booking_id'])) {
                // First verify if booking_id column exists or update it if passed
                // For now, assuming booking_id logic is handled if provided
                // But we need to update the payment record with booking_id if it was passed separately
                // The insert above didn't include booking_id. Let's update it.
                $updatePaymentQuery = "UPDATE payments SET booking_id = ? WHERE id = ?";
                $stmtUpd = $conn->prepare($updatePaymentQuery);
                $stmtUpd->execute([$data['booking_id'], $paymentId]);

                $updateBookingQuery = "UPDATE bookings SET 
                                     status = 'confirmed',
                                     updated_at = NOW()
                                     WHERE id = ?";
                $stmt = $conn->prepare($updateBookingQuery);
                $stmt->execute([$data['booking_id']]);
            }

            // Create notification for the customer (if notifications table exists)
            try {
                $notificationMessage = "Your payment of ₹" . number_format($data['amount'], 2) . " has been received.";
                $notificationLink = "payments/view.php?id=" . $paymentId;

                $sqlNotif = "INSERT INTO notifications (
                                user_id,
                                type,
                                title,
                                message,
                                link,
                                created_at
                            ) VALUES (?, 'payment', 'Payment Received', ?, ?, NOW())";

                $stmtNotif = $conn->prepare($sqlNotif);
                $stmtNotif->execute([$data['customer_id'], $notificationMessage, $notificationLink]);
            } catch (Exception $e) {
                // Ignore notification errors
            }

            $conn->commit();
            return $paymentId;
        } catch (Exception $e) {
            if (isset($conn)) $conn->rollBack();
            error_log("Payment::recordPayment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get paginated payments for DataTables
     */
    public function getPaginatedPayments($start, $length, $search, $order, $filters = [])
    {
        $conditions = [];
        $params = [];

        // Base query
        $sql = "SELECT p.*, 
                       u.name as customer_name, u.email as customer_email, u.phone as customer_mobile
                FROM payments p
                LEFT JOIN users u ON p.customer_id = u.id";

        // Search
        if (!empty($search)) {
            $conditions[] = "(p.transaction_id LIKE :search OR u.name LIKE :search OR u.phone LIKE :search OR p.amount LIKE :search)";
            $params['search'] = "%$search%";
        }

        // Filters
        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $conditions[] = "p.payment_type = :payment_type";
            $params['payment_type'] = $filters['type'];
        }
        if (!empty($filters['dateRange'])) {
            $dates = explode(' - ', $filters['dateRange']);
            if (count($dates) == 2) {
                $dateFrom = date('Y-m-d', strtotime($dates[0]));
                $dateTo = date('Y-m-d', strtotime($dates[1]));
                $conditions[] = "DATE(p.payment_date) BETWEEN :date_from AND :date_to";
                $params['date_from'] = $dateFrom;
                $params['date_to'] = $dateTo;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Ordering
        $columns = ['p.payment_date', 'p.transaction_id', 'c.name', 'p.payment_type', 'p.amount', 'p.status'];
        if (isset($order['column']) && isset($columns[$order['column']])) {
            $sql .= " ORDER BY " . $columns[$order['column']] . " " . ($order['dir'] === 'asc' ? 'ASC' : 'DESC');
        } else {
            $sql .= " ORDER BY p.payment_date DESC";
        }

        // Pagination
        if ($length != -1) {
            $sql .= " LIMIT :start, :length";
            $params['start'] = (int)$start;
            $params['length'] = (int)$length;
        }

        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare($sql);

        // Bind parameters
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key === 'start' || $key === 'length' ? $key : ":$key", $value, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total payments count for DataTables
     */
    public function getTotalPaymentsCount($search = '', $filters = [])
    {
        $conditions = [];
        $params = [];

        $sql = "SELECT COUNT(*) as count 
                FROM payments p
                LEFT JOIN users u ON p.customer_id = u.id";

        // Search
        if (!empty($search)) {
            $conditions[] = "(p.transaction_id LIKE :search OR u.name LIKE :search OR u.phone LIKE :search OR p.amount LIKE :search)";
            $params['search'] = "%$search%";
        }

        // Filters
        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $conditions[] = "p.payment_type = :payment_type";
            $params['payment_type'] = $filters['type'];
        }
        if (!empty($filters['dateRange'])) {
            $dates = explode(' - ', $filters['dateRange']);
            if (count($dates) == 2) {
                $dateFrom = date('Y-m-d', strtotime($dates[0]));
                $dateTo = date('Y-m-d', strtotime($dates[1]));
                $conditions[] = "DATE(p.payment_date) BETWEEN :date_from AND :date_to";
                $params['date_from'] = $dateFrom;
                $params['date_to'] = $dateTo;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key !== 'start' && $key !== 'length') {
                $stmt->bindValue(":$key", $value);
            }
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($id)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "SELECT p.*, 
                       u.name as customer_name, u.email as customer_email, u.phone as customer_mobile,
                       creator.name as created_by_name
                FROM payments p 
                LEFT JOIN users u ON p.customer_id = u.id 
                LEFT JOIN users creator ON p.created_by = creator.id
                WHERE p.id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update payment
     */
    public function updatePayment($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['customer_id'])) {
            $fields[] = "customer_id = :customer_id";
            $params[':customer_id'] = $data['customer_id'];
        }
        if (isset($data['amount'])) {
            $fields[] = "amount = :amount";
            $params[':amount'] = $data['amount'];
        }
        if (isset($data['payment_date'])) {
            $fields[] = "payment_date = :payment_date";
            $params[':payment_date'] = $data['payment_date'];
        }
        if (isset($data['payment_type'])) {
            $fields[] = "payment_type = :payment_type";
            $params[':payment_type'] = $data['payment_type'];
        }
        if (isset($data['transaction_id'])) {
            $fields[] = "transaction_id = :transaction_id";
            $params[':transaction_id'] = $data['transaction_id'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        if (isset($data['description'])) {
            $fields[] = "notes = :notes";
            $params[':notes'] = $data['description'];
        } elseif (isset($data['notes'])) {
            $fields[] = "notes = :notes";
            $params[':notes'] = $data['notes'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE payments SET " . implode(', ', $fields) . " WHERE id = :id";

        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function deletePayment($id)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("DELETE FROM payments WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get all payments (Legacy support if needed, but updated query)
     */
    public function getAllPayments($filters = [])
    {
        return $this->getPaginatedPayments(0, 1000, '', [], $filters);
    }
}
