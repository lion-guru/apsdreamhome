<?php

namespace App\Models;

use App\Models\Model;
use App\Core\Database;
use PDO;

/**
 * Payment Model
 * Handles all payment-related database operations
 */
class Payment extends Model
{
    protected static string $table = 'payments';
    protected $primaryKey = 'id';

    /**
     * Get all payments with filters
     */
    public function getAllPayments($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $conditions[] = "p.payment_method = :payment_method";
            $params['payment_method'] = $filters['payment_method'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = "p.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['property_id'])) {
            $conditions[] = "p.property_id = :property_id";
            $params['property_id'] = $filters['property_id'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "p.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "p.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['min_amount'])) {
            $conditions[] = "p.amount >= :min_amount";
            $params['min_amount'] = $filters['min_amount'];
        }

        if (!empty($filters['max_amount'])) {
            $conditions[] = "p.amount <= :max_amount";
            $params['max_amount'] = $filters['max_amount'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "
            SELECT p.*, prop.title as property_title, prop.location as property_location,
                   u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            {$whereClause}
            ORDER BY p.created_at DESC
        ";

        $db = Database::getInstance();
        $stmt = $db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($id)
    {
        $sql = "
            SELECT p.*, prop.title as property_title, prop.location as property_location, prop.price as property_price,
                   u.name as user_name, u.email as user_email, u.phone as user_phone,
                   u.address as user_address, u.city as user_city, u.state as user_state
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new payment
     */
    public function createPayment($data)
    {
        $sql = "
            INSERT INTO {$this->table} (
                user_id, property_id, amount, payment_method, transaction_id,
                gateway_response, status, notes, created_at, updated_at
            ) VALUES (
                :user_id, :property_id, :amount, :payment_method, :transaction_id,
                :gateway_response, :status, :notes, NOW(), NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    /**
     * Update payment
     */
    public function updatePayment($id, $data)
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
     * Update payment status
     */
    public function updatePaymentStatus($id, $status, $transactionId = null, $gatewayResponse = null)
    {
        $data = ['status' => $status];

        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }

        if ($gatewayResponse) {
            $data['gateway_response'] = $gatewayResponse;
        }

        return $this->updatePayment($id, $data);
    }

    /**
     * Get payments by user
     */
    public function getPaymentsByUser($userId)
    {
        $sql = "
            SELECT p.*, prop.title as property_title, prop.location as property_location
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments by property
     */
    public function getPaymentsByProperty($propertyId)
    {
        $sql = "
            SELECT p.*, u.name as user_name, u.email as user_email
            FROM {$this->table} p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.property_id = :property_id
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['property_id' => $propertyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics()
    {
        $stats = [];

        // Payment status breakdown
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count, SUM(amount) as total_amount
            FROM {$this->table}
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Payment method breakdown
        $stmt = $this->db->prepare("
            SELECT payment_method, COUNT(*) as count, SUM(amount) as total_amount
            FROM {$this->table}
            WHERE status = 'completed'
            GROUP BY payment_method
        ");
        $stmt->execute();
        $stats['by_method'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monthly payments (last 12 months)
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   COUNT(*) as count,
                   SUM(amount) as total_amount
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute();
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total revenue
        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed_amount,
                COUNT(*) as total_payments,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_payments,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                AVG(CASE WHEN status = 'completed' THEN amount END) as avg_payment
            FROM {$this->table}
        ");
        $stmt->execute();
        $stats['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments()
    {
        $sql = "
            SELECT p.*, prop.title as property_title, prop.location as property_location,
                   u.name as user_name, u.email as user_email, u.phone as user_phone,
                   DATEDIFF(NOW(), p.created_at) as days_pending
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status = 'pending'
            ORDER BY p.created_at ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get failed payments
     */
    public function getFailedPayments()
    {
        $sql = "
            SELECT p.*, prop.title as property_title, prop.location as property_location,
                   u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status = 'failed'
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get successful payments
     */
    public function getSuccessfulPayments($limit = null)
    {
        $sql = "
            SELECT p.*, prop.title as property_title, prop.location as property_location,
                   u.name as user_name, u.email as user_email
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.status = 'completed'
            ORDER BY p.created_at DESC
        ";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment summary for dashboard
     */
    public function getPaymentSummary($days = 30)
    {
        $sql = "
            SELECT
                COUNT(*) as total_payments,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_payments,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                AVG(CASE WHEN status = 'completed' THEN amount END) as avg_payment
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments by date range
     */
    public function getPaymentsByDateRange($startDate, $endDate)
    {
        $sql = "
            SELECT p.*, prop.title as property_title, u.name as user_name
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE DATE(p.created_at) BETWEEN :start_date AND :end_date
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments by amount range
     */
    public function getPaymentsByAmountRange($minAmount, $maxAmount)
    {
        $sql = "
            SELECT p.*, prop.title as property_title, u.name as user_name
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.amount BETWEEN :min_amount AND :max_amount
            ORDER BY p.amount DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top paying customers
     */
    public function getTopPayingCustomers($limit = 10)
    {
        $sql = "
            SELECT u.id, u.name, u.email,
                   COUNT(p.id) as payment_count,
                   SUM(p.amount) as total_paid,
                   AVG(p.amount) as avg_payment,
                   MAX(p.created_at) as last_payment_date
            FROM users u
            JOIN {$this->table} p ON u.id = p.user_id
            WHERE p.status = 'completed'
            GROUP BY u.id, u.name, u.email
            ORDER BY total_paid DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment trends
     */
    public function getPaymentTrends($days = 30)
    {
        $sql = "
            SELECT
                DATE(created_at) as date,
                COUNT(*) as payment_count,
                SUM(amount) as total_amount,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_count,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment gateway statistics
     */
    public function getGatewayStatistics()
    {
        $sql = "
            SELECT payment_method,
                   COUNT(*) as total_transactions,
                   COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_transactions,
                   COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_transactions,
                   SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
                   ROUND(AVG(CASE WHEN status = 'completed' THEN amount END), 2) as avg_amount
            FROM {$this->table}
            GROUP BY payment_method
            ORDER BY total_transactions DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search payments
     */
    public function searchPayments($searchTerm, $filters = [])
    {
        $conditions = ["(u.name LIKE :search OR u.email LIKE :search OR prop.title LIKE :search OR p.transaction_id LIKE :search)"];
        $params = ['search' => "%{$searchTerm}%"];

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $conditions[] = "p.payment_method = :payment_method";
            $params['payment_method'] = $filters['payment_method'];
        }

        if (!empty($filters['min_amount'])) {
            $conditions[] = "p.amount >= :min_amount";
            $params['min_amount'] = $filters['min_amount'];
        }

        if (!empty($filters['max_amount'])) {
            $conditions[] = "p.amount <= :max_amount";
            $params['max_amount'] = $filters['max_amount'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT p.*, prop.title as property_title, u.name as user_name, u.email as user_email
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            {$whereClause}
            ORDER BY
                CASE
                    WHEN u.name LIKE :search THEN 1
                    WHEN prop.title LIKE :search THEN 2
                    WHEN p.transaction_id LIKE :search THEN 3
                    ELSE 4
                END,
                p.created_at DESC
        ";

        $db = Database::getInstance();
        $stmt = $db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get refund requests
     */
    public function getRefundRequests()
    {
        $sql = "
            SELECT p.*, prop.title as property_title, u.name as user_name, u.email as user_email,
                   pr.reason, pr.requested_at, pr.status as refund_status
            FROM {$this->table} p
            LEFT JOIN properties prop ON p.property_id = prop.id
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN payment_refunds pr ON p.id = pr.payment_id
            WHERE pr.id IS NOT NULL
            ORDER BY pr.requested_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Process refund
     */
    public function processRefund($paymentId, $refundAmount, $reason)
    {
        $sql = "
            INSERT INTO payment_refunds (payment_id, refund_amount, reason, status, processed_by, processed_at)
            VALUES (:payment_id, :refund_amount, :reason, 'processed', :processed_by, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'payment_id' => $paymentId,
            'refund_amount' => $refundAmount,
            'reason' => $reason,
            'processed_by' => $_SESSION['user_id'] ?? null
        ]);
    }

    /**
     * Get payment receipts
     */
    public function getPaymentReceipts($paymentId)
    {
        $sql = "
            SELECT * FROM payment_receipts
            WHERE payment_id = :payment_id
            ORDER BY created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['payment_id' => $paymentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate payment receipt
     */
    public function generateReceipt($paymentId, $receiptData)
    {
        $sql = "
            INSERT INTO payment_receipts (payment_id, receipt_number, file_path, created_at)
            VALUES (:payment_id, :receipt_number, :file_path, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($receiptData);
    }
}
