<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

/**
 * DocumentLockerService
 * Manages secure document storage and retrieval for users.
 */
class DocumentLockerService
{
    protected $db;
    protected $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new \App\Services\LoggingService();
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS mlm_document_locker (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            document_type ENUM('registry', 'id_proof', 'payment_receipt', 'other') DEFAULT 'other',
            file_url TEXT NOT NULL,
            status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->db->query($sql);
    }

    /**
     * Add a document record to the locker.
     */
    public function addDocument($userId, $title, $type, $url)
    {
        try {
            $sql = "INSERT INTO mlm_document_locker (user_id, title, document_type, file_url, status) 
                    VALUES (?, ?, ?, ?, 'pending')";
            $this->db->query($sql, [$userId, $title, $type, $url]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Document added to locker'
            ];
        } catch (Exception $e) {
            $this->logger->error("Error adding document to locker: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all documents for a user.
     */
    public function getUserDocuments($userId)
    {
        $sql = "SELECT * FROM mlm_document_locker WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->select($sql, [$userId]);
    }

    /**
     * Update document status (for Admin).
     */
    public function updateStatus($id, $status, $remarks = '')
    {
        $sql = "UPDATE mlm_document_locker SET status = ?, remarks = ? WHERE id = ?";
        return $this->db->query($sql, [$status, $remarks, $id]);
    }
}
