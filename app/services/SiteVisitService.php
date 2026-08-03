<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

use \App\Traits\ServiceTenantTrait;

/**
 * SiteVisitService
 * Manages GPS tracking and coordination for site visits.
 */
class SiteVisitService
{
    use \App\Traits\ServiceTenantTrait;

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
            id INT(11) NOT NULL AUTO_INCREMENT,
            agent_id BIGINT(20) UNSIGNED NULL,
            lead_id INT(11) NULL,
            property_id BIGINT(20) UNSIGNED NULL,
            status ENUM('in_progress','completed','cancelled') NOT NULL DEFAULT 'in_progress',
            current_lat DECIMAL(10,8) NULL,
            current_lng DECIMAL(11,8) NULL,
            destination_lat DECIMAL(10,8) NULL,
            destination_lng DECIMAL(11,8) NULL,
            start_time DATETIME NULL,
            end_time DATETIME NULL,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_agent (agent_id),
            KEY idx_status (status),
            KEY idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->db->getConnection()->exec($sql);
        } catch (\Exception $e) {
            error_log('SiteVisitService::ensureTableExists error: ' . $e->getMessage());
        }
    }

    /**
     * Start a site visit session.
     */
    public function startVisit($agentId, $leadId = null, $propertyId = null, $destLat = null, $destLng = null)
    {
        try {
            $columns = array_merge(['agent_id', 'lead_id', 'property_id', 'status', 'destination_lat', 'destination_lng', 'start_time'], array_keys($this->tenantInsertData()));
            $values = array_merge([$agentId, $leadId, $propertyId, 'in_progress', $destLat, $destLng], array_values($this->tenantInsertData()));
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $this->db->query("INSERT INTO mlm_site_visits (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")", $values);
            
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
        $sql = "SELECT * FROM mlm_site_visits WHERE agent_id = ? AND status = 'in_progress'" . $this->tenantSql() . " LIMIT 1";
        return $this->db->selectOne($sql, array_merge([$agentId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
    }

    /**
     * Get visit status for a lead (customer view).
     */
    public function getVisitStatus($visitId)
    {
        $sql = "SELECT id, assigned_to AS agent_id, status, visit_date, visit_time,
                       visit_type, customer_name, customer_phone,
                       latitude, longitude, location_address, notes
                FROM property_visits WHERE id = ?";
        return $this->db->selectOne($sql, [$visitId]);
    }
}
