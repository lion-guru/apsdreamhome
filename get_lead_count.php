<?php
/**
 * APS Dream Home - Lead Count API
 * Get total leads count for dashboard
 */

header('Content-Type: application/json');

try {
    // Database connection
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Check if table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'leads'");
    
    if ($table_check->rowCount() === 0) {
        echo json_encode(['count' => 0, 'message' => 'No leads table found']);
        exit;
    }
    
    // Get total leads count
    $sql = "SELECT COUNT(*) as total FROM leads";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    
    // Get today's leads
    $today_sql = "SELECT COUNT(*) as today FROM leads WHERE DATE(created_at) = CURDATE()";
    $today_stmt = $pdo->prepare($today_sql);
    $today_stmt->execute();
    $today_result = $today_stmt->fetch();
    
    // Get new leads
    $new_sql = "SELECT COUNT(*) as new FROM leads WHERE status = 'new'";
    $new_stmt = $pdo->prepare($new_sql);
    $new_stmt->execute();
    $new_result = $new_stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'count' => (int)$result['total'],
        'today' => (int)$today_result['today'],
        'new' => (int)$new_result['new'],
        'last_updated' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'count' => 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'General error: ' . $e->getMessage(),
        'count' => 0
    ]);
}
?>
