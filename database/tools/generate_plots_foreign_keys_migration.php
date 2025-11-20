<?php
// generate_plots_foreign_keys_migration.php
// Creates a safe migration to add FKs for plots.project_id, plots.customer_id, and plots.associate_id
// with type alignment, orphan cleanup, indexes, and idempotent guards.

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection \$con is not available (mysqli).\n");
    exit(1);
}

// Resolve current database name
$dbName = null;
$resDb = $con->query('SELECT DATABASE() AS db');
if ($resDb && ($rowDb = $resDb->fetch_assoc())) {
    $dbName = $rowDb['db'] ?? null;
}
if (!$dbName) {
    fwrite(STDERR, "[ERROR] Unable to determine current database name.\n");
    exit(1);
}

// Helpers
function columnInfo(mysqli $con, string $db, string $table, string $column): ?array {
    $sql = "SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $info = $res->fetch_assoc();
    $stmt->close();
    return $info ?: null;
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $sql = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function indexExists(mysqli $con, string $db, string $table, string $indexName): bool {
    $sql = "SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $indexName);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function foreignKeyExists(mysqli $con, string $db, string $table, string $fkName): bool {
    $sql = "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $fkName);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function primaryKeyColumn(mysqli $con, string $db, string $table): ?string {
    $sql = "SELECT k.COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS t JOIN information_schema.KEY_COLUMN_USAGE k ON t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND t.TABLE_SCHEMA = k.TABLE_SCHEMA AND t.TABLE_NAME = k.TABLE_NAME WHERE t.TABLE_SCHEMA = ? AND t.TABLE_NAME = ? AND t.CONSTRAINT_TYPE = 'PRIMARY KEY' LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['COLUMN_NAME'] ?? null;
}

function countOrphans(mysqli $con, string $table, string $column, string $refTable, string $refColumn): int {
    $sql = "SELECT COUNT(*) AS c FROM `{$table}` t LEFT JOIN `{$refTable}` r ON t.`{$column}` = r.`{$refColumn}` WHERE t.`{$column}` IS NOT NULL AND r.`{$refColumn}` IS NULL";
    $res = $con->query($sql);
    if (!$res) { return 0; }
    $row = $res->fetch_assoc();
    return (int)($row['c'] ?? 0);
}

function modifyColumnSQL(string $table, string $column, string $columnType, bool $nullable): string {
    return sprintf(
        "ALTER TABLE `%s` MODIFY COLUMN `%s` %s %s;",
        $table,
        $column,
        $columnType,
        $nullable ? 'NULL' : 'NOT NULL'
    );
}

function orphanCleanupSQL(string $table, string $column, string $refTable, string $refColumn): string {
    return sprintf(
        "UPDATE `%s` t LEFT JOIN `%s` r ON t.`%s` = r.`%s` SET t.`%s` = NULL WHERE r.`%s` IS NULL;",
        $table, $refTable, $column, $refColumn, $column, $refColumn
    );
}

function generateFkBlock(mysqli $con, string $db, array $spec, array &$sqlOut): void {
    $t = $spec['table'];
    $c = $spec['column'];
    $rt = $spec['ref_table'];
    $rc = $spec['ref_column'];
    $fkName = $spec['fk_name'];
    $idxName = $spec['index_name'];
    $onDelete = $spec['on_delete'];
    $onUpdate = $spec['on_update'];
    $nullableDesired = $spec['nullable'];

    // Validate presence
    if (!tableExists($con, $db, $t)) {
        $sqlOut[] = sprintf("-- SKIP: `%s` table missing", $t);
        return;
    }
    if (!tableExists($con, $db, $rt)) {
        $sqlOut[] = sprintf("-- SKIP: `%s` table missing", $rt);
        return;
    }

    // Ensure referenced PK column exists
    $pkCol = primaryKeyColumn($con, $db, $rt);
    if (!$pkCol) {
        $sqlOut[] = sprintf("-- SKIP: `%s` primary key missing", $rt);
        return;
    }
    if (strtolower($pkCol) !== strtolower($rc)) {
        // Adjust to actual PK if mismatch
        $rc = $pkCol;
    }

    // Align type and nullability
    $refInfo = columnInfo($con, $db, $rt, $rc);
    $colInfo = columnInfo($con, $db, $t, $c);
    if (!$refInfo) {
        $sqlOut[] = sprintf("-- SKIP: `%s`.`%s` column missing", $rt, $rc);
        return;
    }
    if (!$colInfo) {
        $sqlOut[] = sprintf("-- SKIP: `%s`.`%s` column missing", $t, $c);
        return;
    }

    $refType = $refInfo['COLUMN_TYPE'];
    $currentType = $colInfo['COLUMN_TYPE'];
    $currentNullable = strtoupper($colInfo['IS_NULLABLE'] ?? 'YES') === 'YES';

    // Decide target nullability: prefer requested nullable; if ON DELETE SET NULL, force nullable
    $targetNullable = $nullableDesired || strtoupper($onDelete) === 'SET NULL';

    if (strtolower($currentType) !== strtolower($refType) || $currentNullable !== $targetNullable) {
        $sqlOut[] = modifyColumnSQL($t, $c, $refType, $targetNullable);
    } else {
        $sqlOut[] = sprintf("-- SKIP TYPE: `%s`.`%s` already %s %s", $t, $c, $refType, $currentNullable ? 'NULL' : 'NOT NULL');
    }

    // Orphan cleanup only if we will allow NULLs
    if ($targetNullable) {
        $orphans = countOrphans($con, $t, $c, $rt, $rc);
        if ($orphans > 0) {
            $sqlOut[] = orphanCleanupSQL($t, $c, $rt, $rc);
        } else {
            $sqlOut[] = sprintf("-- SKIP ORPHANS: `%s`.`%s` has none", $t, $c);
        }
    } else {
        $sqlOut[] = sprintf("-- SKIP ORPHANS: `%s`.`%s` not nullable; using %s", $t, $c, $onDelete);
    }

    // Index
    if (!indexExists($con, $db, $t, $idxName)) {
        $sqlOut[] = sprintf("ALTER TABLE `%s` ADD INDEX `%s` (`%s`);", $t, $idxName, $c);
    } else {
        $sqlOut[] = sprintf("-- SKIP IDX: `%s` exists", $idxName);
    }

    // FK
    if (!foreignKeyExists($con, $db, $t, $fkName)) {
        $sqlOut[] = sprintf(
            "ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON DELETE %s ON UPDATE %s;",
            $t, $fkName, $c, $rt, $rc, $onDelete, $onUpdate
        );
    } else {
        $sqlOut[] = sprintf("-- SKIP FK: `%s` exists", $fkName);
    }
}

// Spec for plots FKs
$specs = [
    [
        'table' => 'plots',
        'column' => 'project_id',
        'ref_table' => 'projects',
        'ref_column' => 'id',
        'fk_name' => 'fk_plots_project_id',
        'index_name' => 'ix_plots_project_id',
        'on_delete' => 'SET NULL',
        'on_update' => 'CASCADE',
        'nullable' => true,
    ],
    [
        'table' => 'plots',
        'column' => 'customer_id',
        'ref_table' => 'users',
        'ref_column' => 'id',
        'fk_name' => 'fk_plots_customer_id',
        'index_name' => 'ix_plots_customer_id',
        'on_delete' => 'SET NULL',
        'on_update' => 'CASCADE',
        'nullable' => true,
    ],
    [
        'table' => 'plots',
        'column' => 'associate_id',
        'ref_table' => 'associates',
        'ref_column' => 'id',
        'fk_name' => 'fk_plots_associate_id',
        'index_name' => 'ix_plots_associate_id',
        'on_delete' => 'SET NULL',
        'on_update' => 'CASCADE',
        'nullable' => true,
    ],
];

$argv = $_SERVER['argv'] ?? [];
$outArg = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--out=')) {
        $outArg = trim(substr($arg, 6), "\"' ");
        break;
    }
}
$migrationDir = __DIR__ . '/../migrations';
if (!is_dir($migrationDir)) { mkdir($migrationDir, 0777, true); }
$outFile = $migrationDir . '/' . ($outArg ?: '009_add_plots_foreign_keys.sql');

$sqlOut = [];
$sqlOut[] = sprintf('-- %s', basename($outFile));
$sqlOut[] = sprintf('-- Generated: %s', date('Y-m-d H:i:s'));
$sqlOut[] = '-- Add safe foreign keys for plots relations';
$sqlOut[] = '';

foreach ($specs as $spec) {
    generateFkBlock($con, $dbName, $spec, $sqlOut);
    $sqlOut[] = '';
}

$content = implode("\n", $sqlOut) . "\n";
if (file_put_contents($outFile, $content) === false) {
    fwrite(STDERR, "[ERROR] Failed to write migration file: {$outFile}\n");
    exit(1);
}

echo "Written migration: {$outFile}\n";
?>
