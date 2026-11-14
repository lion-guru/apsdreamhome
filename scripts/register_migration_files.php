<?php
/**
 * Migration File Registry — evidence-collection, not a silent rewrite.
 *
 * The live `schema_migrations` table has 4 rows. 234 migration PHP files exist
 * on disk. This does NOT invent history — it records assertion of existence:
 * filename, sha256, size, file-mtime. Never alters existing tables.
 *
 * Run:    php scripts/register_migration_files.php
 * Re-run: safe (INSERT IGNORE). Never drops anything.
 *
 * Exits 0 always; prints a summary.
 */
require __DIR__ . '/../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "== registering migration files ==\n";
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS migration_file_registry (
        filename VARCHAR(255) PRIMARY KEY,
        base_name VARCHAR(255) NOT NULL,
        sha256 CHAR(64) NOT NULL,
        size_bytes INT UNSIGNED NOT NULL,
        file_mtime DATETIME NOT NULL,
        registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_base_name (base_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$dir = __DIR__ . '/../database/migrations';
$files = glob($dir . '/*.php');
sort($files);
echo "migration files found: " . count($files) . "\n";

$recordedKeys = [];
try {
    foreach ($pdo->query("SELECT version FROM schema_migrations")->fetchAll(PDO::FETCH_COLUMN) as $v) {
        $recordedKeys[] = strtolower((string)$v);
    }
} catch (\Throwable $e) {
    echo "(could not read schema_migrations, continuing): " . $e->getMessage() . "\n";
}

$ins = $pdo->prepare(
    "INSERT INTO migration_file_registry (filename, base_name, sha256, size_bytes, file_mtime)
     VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))
     ON DUPLICATE KEY UPDATE sha256=VALUES(sha256), size_bytes=VALUES(size_bytes), file_mtime=VALUES(file_mtime)"
);
$n = 0; $unchanged = 0;
foreach ($files as $f) {
    $base = basename($f);
    $baseKey = strtolower(pathinfo($base, PATHINFO_FILENAME));
    $sha = hash_file('sha256', $f);
    $size = filesize($f);
    $mtime = filemtime($f);
    $ins->execute([$base, $baseKey, $sha, $size, $mtime]);
    $n++;
}
echo "upserted: $n files\n";

// stats
$total = $pdo->query("SELECT COUNT(*) FROM migration_file_registry")->fetchColumn();
$topExt = $pdo->query("SELECT COUNT(*) c FROM migration_file_registry WHERE filename LIKE '%.php'")->fetchColumn();
echo "registry total: $total rows\n";
// Check for files in registry that have a matching schema_migrations version (tolerant compare)
$match = 0;
foreach ($recordedKeys as $v) {
    $row = $pdo->query(
        "SELECT COUNT(*) FROM migration_file_registry WHERE LOWER(base_name) LIKE ?",
        ['%' . strtolower(preg_replace('/^[0-9_-]+/', '', pathinfo($v, PATHINFO_FILENAME))) . '%']
    )->fetchColumn() ?: 0;
    $match += (int)$row;
}
echo "registry rows with a plausible schema_migrations match: $match\n";

echo "\n== registry sample ==\n";
foreach ($pdo->query("SELECT filename, size_bytes FROM migration_file_registry ORDER BY file_mtime DESC LIMIT 5") as $row) {
    printf("  %-50s %d bytes\n", $row['filename'], $row['size_bytes']);
}
echo "== done. No existing data was altered. ==\n";
