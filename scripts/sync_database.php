# =============================================================================
# Database Sync Scripts for Cloud Development
# =============================================================================
# Usage:
#   php scripts/sync_database.php export  — Export local PC DB to SQL file
#   php scripts/sync_database.php import  — Import SQL to Docker/Codespaces DB
#
# Workflow:
#   1. On PC:       php scripts/sync_database.php export  → database/apsdreamhome_latest.sql
#   2. Commit + push to GitHub
#   3. In cloud:   (auto-import on startup, or:) php scripts/sync_database.php import
# =============================================================================

$host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1');
$port = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3307'));
$db   = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'apsdreamhome');
$user = getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root');
$pass = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? '');

$action = $argv[1] ?? 'import';
$sqlFile = dirname(__DIR__) . '/database/apsdreamhome_latest.sql';

if (!extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "pdo_mysql extension required\n");
    exit(1);
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

if ($action === 'export') {
    echo "Exporting database '$db' from $host:$port...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
    echo sprintf("Found %d tables\n", count($tables));

    $lines = ["SET NAMES utf8mb4;", "SET FOREIGN_KEY_CHECKS=0;"];
    $insertCount = 0;

    foreach ($tables as $t) {
        $table = $t[0];
        try {
            $createRow = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            $lines[] = "DROP TABLE IF EXISTS `$table`;";
            $lines[] = $createRow[1];

            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $cols = [];
                    $vals = [];
                    foreach ($row as $col => $val) {
                        $cols[] = "`$col`";
                        $vals[] = $val === null ? 'NULL' : $pdo->quote($val);
                    }
                    $lines[] = "INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");";
                    $insertCount++;
                }
            }
        } catch (Exception $e) {
            echo "  SKIP $table: " . $e->getMessage() . "\n";
        }
    }

    $lines[] = "SET FOREIGN_KEY_CHECKS=1;";
    file_put_contents($sqlFile, implode("\n", $lines) . "\n");
    $sizeMB = round(filesize($sqlFile) / 1024 / 1024, 2);
    echo sprintf("SUCCESS: %d tables, %d rows → %s (%s MB)\n", count($tables), $insertCount, $sqlFile, $sizeMB);
    echo "Commit and push database/apsdreamhome_latest.sql to sync with cloud.\n";
}

if ($action === 'import') {
    echo "Importing database to $db at $host:$port...\n";
    if (!file_exists($sqlFile)) {
        fwrite(STDERR, "SQL file not found: $sqlFile\n");
        fwrite(STDOUT, "Run 'php scripts/sync_database.php export' on your PC first.\n");
        exit(1);
    }
    echo "File size: " . round(filesize($sqlFile)/1024/1024, 1) . " MB\n";
    $sqlContent = file_get_contents($sqlFile);

    // Run the full SQL dump as one batch
    $pdo->exec($sqlContent);
    echo "Import complete!\n";

    // Verify
    $tableCount = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db'")->fetchColumn();
    echo "Database now has $tableCount tables.\n";
}
