<?php
// Phase 1: additive missing indexes (idempotent). Safe: ADD INDEX only, never drops.
// Run: php scripts/migrate_phase1_missing_indexes.php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$wanted = [
    'plot_bookings' => [
        'idx_pb_customer_status' => ['customer_id', 'status'],
        'idx_pb_plot_id'         => ['plot_id'],
        'idx_pb_colony_id'       => ['colony_id'],
        'idx_pb_booking_number'  => ['booking_number'],
        'idx_pb_status'          => ['status'],
    ],
    'ai_chat_messages' => [
        'idx_acm_user_id' => ['user_id'],
    ],
    'blog_comments' => [
        'idx_bc_user_id' => ['user_id'],
    ],
    'booking_video_consents' => [
        'idx_bvc_customer_id' => ['customer_id'],
    ],
];

foreach ($wanted as $table => $indexes) {
    // verify columns exist first
    $cols = [];
    foreach ($pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC) as $c) $cols[] = $c['Field'];
    $existing = [];
    foreach ($pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC) as $i) $existing[] = $i['Key_name'];
    foreach ($indexes as $name => $columns) {
        foreach ($columns as $col) {
            if (!in_array($col, $cols, true)) { echo "SKIP $table.$name: missing column $col\n"; continue 2; }
        }
        if (in_array($name, $existing, true)) { echo "OK $table.$name already exists\n"; continue; }
        $colList = '`' . implode('`, `', $columns) . '`';
        $pdo->exec("ALTER TABLE `$table` ADD INDEX `$name` ($colList)");
        echo "ADDED $table.$name (" . implode(',', $columns) . ")\n";
    }
}
echo "DONE phase1\n";
