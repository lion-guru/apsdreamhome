<?php
/**
 * Auto Revenue Commission Cron
 * Calculates daily revenue and agent commissions from converted leads.
 * Optimized to use the Database singleton and ensure schema integrity.
 */

// Use CLI-friendly initialization if needed, otherwise core/init.php
if (PHP_SAPI === 'cli') {
    require_once __DIR__ . '/../../includes/legacy_bootstrap.php';
} else {
    require_once __DIR__ . '/core/init.php';
}

use App\Core\Database;

try {
    $db = \App\Core\App::database();

    // Ensure the daily revenue commission table exists
    $db->execute("
        CREATE TABLE IF NOT EXISTS revenue_commission_daily (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_date DATE NOT NULL,
            agent_id INT NOT NULL,
            revenue DECIMAL(15, 2) DEFAULT 0.00,
            deals INT DEFAULT 0,
            commission DECIMAL(15, 2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (stat_date, agent_id)
        )
    ");

    // Calculate revenue and deals for leads converted today
    // Note: Adjust the 'Converted' status and 'updated_at' field if they differ in your schema
    $sql = "
        SELECT 
            assigned_to as agent_id, 
            SUM(total_purchase_value) as total_revenue, 
            COUNT(*) as total_deals 
        FROM leads 
        WHERE status = 'Converted' 
        AND DATE(updated_at) = CURDATE() 
        AND assigned_to IS NOT NULL 
        GROUP BY assigned_to
    ";

    $results = $db->fetchAll($sql);

    if (empty($results)) {
        echo "[" . date('Y-m-d H:i:s') . "] No converted leads found for today.\n";
        exit;
    }

    $processedCount = 0;
    foreach ($results as $row) {
        $agentId = $row['agent_id'];
        $revenue = $row['total_revenue'] ?? 0;
        $deals = $row['total_deals'] ?? 0;
        
        // Example: 2% commission logic (can be made dynamic based on agent settings)
        $commissionRate = 0.02; 
        $calculatedCommission = $revenue * $commissionRate;

        $insertSql = "
            INSERT INTO revenue_commission_daily (stat_date, agent_id, revenue, deals, commission) 
            VALUES (CURDATE(), ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                revenue = VALUES(revenue), 
                deals = VALUES(deals), 
                commission = VALUES(commission)
        ";

        if ($db->execute($insertSql, [$agentId, $revenue, $deals, $calculatedCommission])) {
            $processedCount++;
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Successfully processed commission for $processedCount agents.\n";

} catch (Exception $e) {
    error_log("Revenue Commission Cron Error: " . $e->getMessage());
    if (PHP_SAPI === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
