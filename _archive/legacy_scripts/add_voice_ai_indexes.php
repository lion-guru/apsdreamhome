<?php
// Database connection parameters
$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to check if an index exists
function indexExists($pdo, $table, $indexName) {
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = :table 
            AND INDEX_NAME = :indexName";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['table' => $table, 'indexName' => $indexName]);
    return (int)$stmt->fetchColumn() > 0;
}

// Function to create an index
function createIndex($pdo, $table, $indexName, $columns) {
    $columnsStr = implode(', ', $columns);
    $sql = "CREATE INDEX $indexName ON $table ($columnsStr)";
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        // Ignore duplicate key errors (if index already exists due to race condition)
        if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
            return true;
        }
        throw $e;
    }
}

// Define indexes to add: [table => [indexName => [columns]]]
$indexes = [
    'ai_call_sessions' => [
        'idx_ai_call_sessions_created_at' => ['created_at'],
        'idx_ai_call_sessions_lead_id' => ['lead_id'],
        'idx_ai_call_sessions_status' => ['status'],
        'idx_ai_call_sessions_ai_agent_id' => ['ai_agent_id'],
        'idx_ai_call_sessions_created_at_status' => ['created_at', 'status'],
        'idx_ai_call_sessions_lead_id_status' => ['lead_id', 'status'],
    ],
    'ai_calling_schedule' => [
        'idx_ai_calling_schedule_scheduled_date' => ['scheduled_date'],
        'idx_ai_calling_schedule_status' => ['status'],
        'idx_ai_calling_schedule_ai_agent_id' => ['ai_agent_id'],
        'idx_ai_calling_schedule_status_priority' => ['status', 'priority'],
        'idx_ai_calling_schedule_scheduled_date_status' => ['scheduled_date', 'status'],
    ],
    'ai_call_extracted_leads' => [
        'idx_ai_call_extracted_leads_is_verified' => ['is_verified'],
        'idx_ai_call_extracted_leads_created_at' => ['created_at'],
        'idx_ai_call_extracted_leads_lead_id' => ['lead_id'],
    ],
    'ai_call_logs' => [
        'idx_ai_call_logs_lead_id' => ['lead_id'],
        'idx_ai_call_logs_follow_up_needed' => ['follow_up_needed'],
        'idx_ai_call_logs_created_at' => ['created_at'],
    ],
    'leads' => [
        'idx_leads_status' => ['status'],
        'idx_leads_created_at' => ['created_at'],
    ],
    'properties' => [
        'idx_properties_id' => ['id'], // likely primary key
        'idx_properties_status' => ['status'],
    ],
    'user_properties' => [
        'idx_user_properties_id' => ['id'], // likely primary key
        'idx_user_properties_status' => ['status'],
    ],
];

$added = [];
$skipped = [];

foreach ($indexes as $table => $indexList) {
    foreach ($indexList as $indexName => $columns) {
        if (!indexExists($pdo, $table, $indexName)) {
            try {
                createIndex($pdo, $table, $indexName, $columns);
                $added[] = "Index '$indexName' on table '$table' (columns: " . implode(', ', $columns) . ")";
            } catch (Exception $e) {
                $skipped[] = "Failed to create index '$indexName' on '$table': " . $e->getMessage();
            }
        } else {
            $skipped[] = "Index '$indexName' already exists on table '$table'";
        }
    }
}

// Output summary
echo "Voice AI Agent System and OLN Service Index Optimization\n";
echo "========================================================\n\n";

if (!empty($added)) {
    echo "Indexes Added:\n";
    foreach ($added as $line) {
        echo "  + $line\n";
    }
    echo "\n";
} else {
    echo "No new indexes were added.\n\n";
}

if (!empty($skipped)) {
    echo "Indexes Skipped (already existed or errors):\n";
    foreach ($skipped as $line) {
        echo "  - $line\n";
    }
    echo "\n";
}

echo "Total indexes added: " . count($added) . "\n";
echo "Total indexes skipped: " . count($skipped) . "\n";
?>