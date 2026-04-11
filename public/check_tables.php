<?php
// Simple database connection without framework
$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>TOTAL: " . count($tables) . " tables\n\n";
    
    // Group by category
    $categories = [
        'user' => [], 'lead' => [], 'customer' => [], 'property' => [],
        'plot' => [], 'project' => [], 'booking' => [], 'sale' => [],
        'payment' => [], 'commission' => [], 'mlm' => [], 'associate' => [],
        'agent' => [], 'employee' => [], 'visit' => [], 'task' => [],
        'ticket' => [], 'wallet' => [], 'payout' => [], 'referral' => [],
        'network' => [], 'gallery' => [], 'content' => [], 'setting' => [],
        'admin' => [], 'other' => []
    ];
    
    foreach ($tables as $table) {
        $found = false;
        foreach (array_keys($categories) as $cat) {
            if (strpos($table, $cat) !== false) {
                $categories[$cat][] = $table;
                $found = true;
                break;
            }
        }
        if (!$found) $categories['other'][] = $table;
    }
    
    foreach ($categories as $cat => $list) {
        if (!empty($list)) {
            echo "\n📁 " . strtoupper($cat) . " (" . count($list) . " tables)\n";
            echo str_repeat('-', 50) . "\n";
            foreach ($list as $t) {
                // Get row count
                $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                echo sprintf("  %-35s %6d rows\n", $t, $count);
            }
        }
    }
    
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
