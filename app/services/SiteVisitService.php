<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

/**
 * SiteVisitService
 * Manages GPS tracking and coordination for site visits.
 */
class SiteVisitService
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
        $sql = "CREATE TABLE IF NOT EXISTS mlm_site_visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agent_id INT NOT NULL,
            lead_id INT,
            property_id INT,
            status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
            current_lat DECIMAL(10, 8),
            current_lng DECIMAL(11, 8),
            destination_lat DECIMAL(10, 8),
            destination_lng DECIMAL(11, 8),
            start_time DATETIME,
            end_time DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_agent_id (agent_id),
            INDEX idx_lead_id (lead_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->db->query($sql);
    }

    /**
     * Start a site visit session.
     */
    public function startVisit($agentId, $leadId = null, $propertyId = null, $destLat = null, $destLng = null)
    {
        try {
            $sql = "INSERT INTO mlm_site_visits (agent_id, lead_id, property_id, status, destination_lat, destination_lng, start_time) 
                    VALUES (?, ?, ?, 'in_progress', ?, ?, NOW())";
            $this->db->query($sql, [$agentId, $leadId, $propertyId, $destLat, $destLng]);
            
            return [
                'success' => true,
                'visit_id' => $this->db->lastInsertId(),
                'message' => 'Site visit started'
            ];
        } catch (Exception $e) {
            $this->logger->error("Error starting site visit: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update agent's current location.
     */
    public function updateLocation($visitId, $lat, $lng)
    {
        $sql = "UPDATE mlm_site_visits SET current_lat = ?, current_lng = ? WHERE id = ?";
        return $this->db->query($sql, [$lat, $lng, $visitId]);
    }

    /**
     * Complete a site visit.
     */
    public function completeVisit($visitId)
    {
        $sql = "UPDATE mlm_site_visits SET status = 'completed', end_time = NOW() WHERE id = ?";
        return $this->db->query($sql, [$visitId]);
    }

    /**
     * Get active visits for an agent.
     */
    public function getActiveVisit($agentId)
    {
        $sql = "SELECT * FROM mlm_site_visits WHERE agent_id = ? AND status = 'in_progress' LIMIT 1";
        return $this->db->selectOne($sql, [$agentId]);
    }

    /**
     * Get visit status for a lead (customer view).
     */
    public function getVisitStatus($visitId)
    {
        $sql = "SELECT id, agent_id, status, current_lat, current_lng, destination_lat, destination_lng 
                FROM mlm_site_visits WHERE id = ?";
        return $this->db->selectOne($sql, [$visitId]);
    }
}
