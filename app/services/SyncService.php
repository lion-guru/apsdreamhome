<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

/**
 * SyncService
 * Handles incremental data fetching for mobile offline synchronization.
 */
class SyncService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Get delta updates for a specific table since a given timestamp.
     * 
     * @param string $table The table name
     * @param string $lastSync The ISO timestamp of the last sync
     * @param array $options Additional filters or limit/offset
     * @return array
     */
    public function getDeltaUpdates($table, $lastSync, $options = [])
    {
        try {
            $allowedTables = ['properties', 'leads', 'commissions', 'mlm_profiles', 'mlm_monthly_incentives'];
            if (!in_array($table, $allowedTables)) {
                throw new Exception("Table $table is not supported for sync.");
            }

            $limit = $options['limit'] ?? 100;
            $offset = $options['offset'] ?? 0;
            
            // Map table to its sync timestamp column (default to sync_updated_at or updated_at)
            $timestampColumn = ($table === 'properties') ? 'sync_updated_at' : 'updated_at';
            
            // Check if column exists, fallback to created_at if necessary
            // For this phase, we assume sync_updated_at exists for properties as verified in audit
            
            $sql = "SELECT * FROM $table WHERE $timestampColumn > ? ORDER BY $timestampColumn ASC LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$lastSync, $limit, $offset]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("SyncService Error ($table): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a comprehensive sync package for multiple modules.
     */
    public function getSyncPackage($lastSync, $userId = null)
    {
        $package = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => []
        ];

        // 1. Sync Properties
        $package['data']['properties'] = $this->getDeltaUpdates('properties', $lastSync);

        // 2. Sync Leads (Filtered by user if provided)
        $leadsOptions = [];
        if ($userId) {
            // Future optimization: filtered by user
        }
        $package['data']['leads'] = $this->getDeltaUpdates('leads', $lastSync);

        // 3. Sync Network Tree (If user is an associate)
        if ($userId) {
            try {
                $perfCalculator = new \App\Services\PerformanceRankCalculator();
                $package['data']['network_tree'] = $perfCalculator->getHierarchyTree($userId, 3);
            } catch (Exception $e) {
                $package['data']['network_tree'] = null;
            }

            // 4. Sync Monthly Incentives
            $package['data']['incentives'] = $this->getDeltaUpdates('mlm_monthly_incentives', $lastSync);
        }

        return $package;
    }
}
