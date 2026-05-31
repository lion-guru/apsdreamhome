<?php
/**
 * Step 4: Implement dashboard stats
 * Populate admin_dashboard_stats table with actual statistics
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 4: IMPLEMENT DASHBOARD STATS ===\n\n";

try {
    // Check current state
    $statsCount = $pdo->query("SELECT COUNT(*) FROM admin_dashboard_stats")->fetchColumn();
    
    echo "Current dashboard stats: $statsCount records\n\n";
    
    // Get actual statistics from database
    $totalLeads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    $totalProperties = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeLeads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new' OR status = 'pending'")->fetchColumn();
    $convertedLeads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'converted' OR status = 'closed'")->fetchColumn();
    $totalActivities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
    $pendingActivities = $pdo->query("SELECT COUNT(*) FROM activities WHERE completed = 0")->fetchColumn();
    
    echo "Current database statistics:\n";
    echo "  Total Leads: $totalLeads\n";
    echo "  Active Leads: $activeLeads\n";
    echo "  Converted Leads: $convertedLeads\n";
    echo "  Total Properties: $totalProperties\n";
    echo "  Total Users: $totalUsers\n";
    echo "  Total Activities: $totalActivities\n";
    echo "  Pending Activities: $pendingActivities\n\n";
    
    // Clear old stats if any
    if ($statsCount > 0) {
        $pdo->exec("DELETE FROM admin_dashboard_stats");
        echo "✓ Cleared old dashboard stats\n";
    }
    
    // Insert current stats
    $insertStat = $pdo->prepare("INSERT INTO admin_dashboard_stats (stat_type, stat_value, stat_date, created_at, updated_at) 
VALUES (:stat_type, :stat_value, CURDATE(), NOW(), NOW())");
    
    $stats = [
        ['total_leads', $totalLeads],
        ['active_leads', $activeLeads],
        ['converted_leads', $convertedLeads],
        ['total_properties', $totalProperties],
        ['total_users', $totalUsers],
        ['total_activities', $totalActivities],
        ['pending_activities', $pendingActivities],
        ['conversion_rate', $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0],
        ['activity_completion_rate', $totalActivities > 0 ? round((($totalActivities - $pendingActivities) / $totalActivities) * 100, 2) : 0],
    ];
    
    foreach ($stats as $stat) {
        $insertStat->execute([
            ':stat_type' => $stat[0],
            ':stat_value' => $stat[1]
        ]);
        echo "✓ Inserted {$stat[0]}: {$stat[1]}\n";
    }
    
    echo "\n✓ Dashboard stats populated with current data\n";
    
    // Verify
    $newStatsCount = $pdo->query("SELECT COUNT(*) FROM admin_dashboard_stats")->fetchColumn();
    echo "Total dashboard stats: $newStatsCount records\n\n";
    
    // Show summary
    $currentStats = $pdo->query("SELECT * FROM admin_dashboard_stats")->fetchAll(PDO::FETCH_ASSOC);
    echo "Current dashboard stats:\n";
    foreach ($currentStats as $stat) {
        echo "  {$stat['stat_type']}: {$stat['stat_value']} ({$stat['stat_date']})\n";
    }
    
    echo "\n=== STEP 4 COMPLETE ===\n";
    echo "Dashboard stats table now ready with current statistics\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
