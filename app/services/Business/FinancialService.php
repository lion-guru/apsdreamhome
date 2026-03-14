<?php

/**
 * Financial Service
 * Handles all financial-related business logic
 */

namespace App\Services\Business;

use App\Core\Database;
use App\Core\Security\CSRFProtection;
use App\Core\Security\InputValidation;
use App\Core\Session\SessionManager;
use App\Core\Logger\Logger;

class FinancialService
{
    private $db;
    private $csrfProtection;
    private $inputValidation;
    private $sessionManager;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->csrfProtection = new CSRFProtection();
        $this->inputValidation = new InputValidation();
        $this->sessionManager = SessionManager::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Get all transactions with pagination
     */
    public function getAllTransactions($page = 1, $limit = 10, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT 
                t.id, t.type, t.amount, t.status, t.description,
                t.transaction_date, t.created_at,
                u.name as user_name, u.email as user_email,
                p.title as property_title, p.id as property_id,
                a.name as associate_name
                FROM transactions t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN properties p ON t.property_id = p.id
                LEFT JOIN associates a ON t.associate_id = a.id
                WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['type'])) {
                $sql .= " AND t.type = ?";
                $params[] = $filters['type'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND t.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND t.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['associate_id'])) {
                $sql .= " AND t.associate_id = ?";
                $params[] = $filters['associate_id'];
            }
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND t.transaction_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND t.transaction_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            $sql .= " ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $transactions = $this->db->fetchAll($sql, $params);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(t.id) as total
                         FROM transactions t
                         LEFT JOIN users u ON t.user_id = u.id
                         LEFT JOIN properties p ON t.property_id = p.id
                         LEFT JOIN associates a ON t.associate_id = a.id
                         WHERE 1=1";
            
            $countParams = [];
            
            if (!empty($filters['type'])) {
                $countSql .= " AND t.type = ?";
                $countParams[] = $filters['type'];
            }
            
            if (!empty($filters['status'])) {
                $countSql .= " AND t.status = ?";
                $countParams[] = $filters['status'];
            }
            
            if (!empty($filters['user_id'])) {
                $countSql .= " AND t.user_id = ?";
                $countParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['associate_id'])) {
                $countSql .= " AND t.associate_id = ?";
                $countParams[] = $filters['associate_id'];
            }
            
            if (!empty($filters['start_date'])) {
                $countSql .= " AND t.transaction_date >= ?";
                $countParams[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $countSql .= " AND t.transaction_date <= ?";
                $countParams[] = $filters['end_date'];
            }
            
            $totalResult = $this->db->fetch($countSql, $countParams);
            $total = $totalResult['total'] ?? 0;
            
            return [
                'transactions' => $transactions,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            $this->logger->error("FinancialService::getAllTransactions - Error: " . $e->getMessage());
            return [
                'transactions' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => 0
            ];
        }
    }

    /**
     * Create new transaction
     */
    public function createTransaction($data)
    {
        try {
            // Validate input data
            $validationRules = [
                'type' => ['required' => true, 'type' => 'string', 'in' => ['payment', 'commission', 'refund', 'bonus']],
                'amount' => ['required' => true, 'type' => 'numeric', 'min' => 0],
                'description' => ['required' => true, 'type' => 'string', 'min' => 5, 'max' => 500],
                'user_id' => ['type' => 'string'],
                'associate_id' => ['type' => 'string'],
                'property_id' => ['type' => 'string'],
                'payment_method' => ['type' => 'string', 'max' => 50],
                'transaction_date' => ['required' => true, 'type' => 'date']
            ];

            $validation = $this->inputValidation->validate($data, $validationRules);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Generate transaction ID
            $transactionId = 'TXN' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Insert transaction
            $sql = "INSERT INTO transactions (
                id, type, amount, status, description,
                user_id, associate_id, property_id, payment_method,
                transaction_date, created_at, updated_at
            ) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, NOW(), NOW())";

            $params = [
                $transactionId,
                $data['type'],
                $data['amount'],
                $data['description'],
                $data['user_id'] ?? null,
                $data['associate_id'] ?? null,
                $data['property_id'] ?? null,
                $data['payment_method'] ?? null,
                $data['transaction_date']
            ];

            $this->db->execute($sql, $params);

            // Log activity
            $this->logActivity('transaction_created', $transactionId, 'New transaction created: ' . $data['description']);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => 'Transaction created successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("FinancialService::createTransaction - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to create transaction']
            ];
        }
    }

    /**
     * Update transaction status
     */
    public function updateTransactionStatus($id, $status)
    {
        try {
            $validStatuses = ['pending', 'completed', 'failed', 'cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'errors' => ['status' => 'Invalid status']
                ];
            }

            $this->db->execute(
                "UPDATE transactions SET status = ?, updated_at = NOW() WHERE id = ?",
                [$status, $id]
            );

            // Log activity
            $this->logActivity('transaction_status_updated', $id, 'Transaction status updated to: ' . $status);

            return [
                'success' => true,
                'message' => 'Transaction status updated successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("FinancialService::updateTransactionStatus - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to update transaction status']
            ];
        }
    }

    /**
     * Get commission report
     */
    public function getCommissionReport($filters = [])
    {
        try {
            $sql = "SELECT 
                a.id as associate_id, a.name as associate_name, a.email as associate_email,
                a.commission_rate,
                COUNT(p.id) as properties_sold,
                COALESCE(SUM(p.price), 0) as total_property_value,
                COALESCE(SUM(p.price * a.commission_rate / 100), 0) as total_commission,
                COALESCE(AVG(p.price), 0) as avg_property_price,
                MAX(p.sold_date) as last_sale_date
                FROM associates a
                LEFT JOIN properties p ON a.id = p.associate_id AND p.status = 'sold'
                WHERE a.status = 'active'";
            
            $params = [];
            
            if (!empty($filters['associate_id'])) {
                $sql .= " AND a.id = ?";
                $params[] = $filters['associate_id'];
            }
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND p.sold_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND p.sold_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            $sql .= " GROUP BY a.id, a.name, a.email, a.commission_rate
                      ORDER BY total_commission DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("FinancialService::getCommissionReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get financial statistics
     */
    public function getFinancialStatistics()
    {
        try {
            $sql = "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as total_payments,
                SUM(CASE WHEN type = 'commission' THEN amount ELSE 0 END) as total_commissions,
                SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) as total_refunds,
                SUM(CASE WHEN type = 'bonus' THEN amount ELSE 0 END) as total_bonuses,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_transactions,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                SUM(amount) as total_amount,
                AVG(amount) as avg_transaction_amount,
                MAX(amount) as max_transaction_amount,
                MIN(amount) as min_transaction_amount
                FROM transactions";
            
            return $this->db->fetch($sql);

        } catch (Exception $e) {
            $this->logger->error("FinancialService::getFinancialStatistics - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get monthly revenue report
     */
    public function getMonthlyRevenueReport($year = null)
    {
        try {
            $year = $year ?? date('Y');
            
            $sql = "SELECT 
                MONTH(transaction_date) as month,
                MONTHNAME(transaction_date) as month_name,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as payments,
                SUM(CASE WHEN type = 'commission' THEN amount ELSE 0 END) as commissions,
                SUM(amount) as total_revenue
                FROM transactions
                WHERE YEAR(transaction_date) = ? AND status = 'completed'
                GROUP BY MONTH(transaction_date), MONTHNAME(transaction_date)
                ORDER BY month";
            
            return $this->db->fetchAll($sql, [$year]);

        } catch (Exception $e) {
            $this->logger->error("FinancialService::getMonthlyRevenueReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get associate commission details
     */
    public function getAssociateCommissionDetails($associateId, $startDate = null, $endDate = null)
    {
        try {
            $sql = "SELECT 
                p.id as property_id, p.title, p.price, p.sold_date,
                a.commission_rate,
                (p.price * a.commission_rate / 100) as commission_amount,
                t.id as transaction_id, t.status as payment_status,
                t.created_at as commission_paid_date
                FROM properties p
                JOIN associates a ON p.associate_id = a.id
                LEFT JOIN transactions t ON p.id = t.property_id AND t.type = 'commission'
                WHERE p.associate_id = ? AND p.status = 'sold'";
            
            $params = [$associateId];
            
            if ($startDate) {
                $sql .= " AND p.sold_date >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND p.sold_date <= ?";
                $params[] = $endDate;
            }
            
            $sql .= " ORDER BY p.sold_date DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("FinancialService::getAssociateCommissionDetails - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process commission payment
     */
    public function processCommissionPayment($associateId, $propertyIds)
    {
        try {
            $associate = $this->db->fetch(
                "SELECT name, commission_rate FROM associates WHERE id = ?",
                [$associateId]
            );

            if (!$associate) {
                return [
                    'success' => false,
                    'errors' => ['general' => 'Associate not found']
                ];
            }

            $processedCommissions = [];
            $totalAmount = 0;

            foreach ($propertyIds as $propertyId) {
                // Get property details
                $property = $this->db->fetch(
                    "SELECT title, price FROM properties WHERE id = ? AND status = 'sold' AND associate_id = ?",
                    [$propertyId, $associateId]
                );

                if ($property) {
                    $commissionAmount = $property['price'] * $associate['commission_rate'] / 100;
                    $totalAmount += $commissionAmount;

                    // Create commission transaction
                    $transactionId = 'TXN' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

                    $sql = "INSERT INTO transactions (
                        id, type, amount, status, description,
                        user_id, associate_id, property_id,
                        transaction_date, created_at, updated_at
                    ) VALUES (?, 'commission', ?, 'completed', ?, ?, ?, ?, NOW(), NOW())";

                    $params = [
                        $transactionId,
                        $commissionAmount,
                        'Commission payment for property: ' . $property['title'],
                        null,
                        $associateId,
                        $propertyId,
                        date('Y-m-d')
                    ];

                    $this->db->execute($sql, $params);

                    $processedCommissions[] = [
                        'property_id' => $propertyId,
                        'title' => $property['title'],
                        'commission_amount' => $commissionAmount,
                        'transaction_id' => $transactionId
                    ];
                }
            }

            // Log activity
            $this->logActivity('commission_processed', $associateId, 'Commission processed for ' . count($processedCommissions) . ' properties');

            return [
                'success' => true,
                'processed_commissions' => $processedCommissions,
                'total_amount' => $totalAmount,
                'message' => 'Commission processed successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("FinancialService::processCommissionPayment - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to process commission payment']
            ];
        }
    }

    /**
     * Log activity
     */
    private function logActivity($action, $transactionId, $details = '')
    {
        try {
            $sql = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $this->sessionManager->get('admin_id'),
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            
            $this->db->execute($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("FinancialService::logActivity - Error: " . $e->getMessage());
        }
    }
}
