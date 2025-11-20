<?php
declare(strict_types=1);

// Unique Constraints Generator
// Proposes ADD UNIQUE constraints for safe candidates, skipping if duplicates exist
// or the constraint already exists. Candidates are heuristics for common uniqueness.
// Usage: php database/tools/generate_unique_constraints_migration.php --out=database/migrations/016_add_unique_constraints.sql

error_reporting(E_ALL);
ini_set('display_errors', '1');

function projectRoot(): string { return dirname(__DIR__, 2); }

function getDbConnection(): mysqli {
    $configPath = projectRoot() . '/includes/config/config.php';
    if (!file_exists($configPath)) { fwrite(STDERR, "Config not found: {$configPath}\n"); exit(1); }
    require $configPath; // expects $con (mysqli)
    if (!isset($con) || !($con instanceof mysqli)) { fwrite(STDERR, "Database connection not available.\n"); exit(1); }
    return $con;
}

function getDatabaseName(mysqli $con): string {
    $res = $con->query('SELECT DATABASE() as db');
    if (!$res) { throw new RuntimeException('Failed to query current database: ' . $con->error); }
    $row = $res->fetch_assoc();
    return $row['db'] ?? '';
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $stmt = $con->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function columnExists(mysqli $con, string $db, string $table, string $column): bool {
    $stmt = $con->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function uniqueConstraintExists(mysqli $con, string $db, string $table, string $name): bool {
    $stmt = $con->prepare("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'UNIQUE'");
    $stmt->bind_param('sss', $db, $table, $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function hasAnyUniqueIndexWithColumns(mysqli $con, string $db, string $table, array $columns): bool {
    // Check if there is any unique index covering exactly these columns in order
    $stmt = $con->prepare('SELECT INDEX_NAME, SEQ_IN_INDEX, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX');
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $map = [];
    while ($row = $res->fetch_assoc()) {
        $name = $row['INDEX_NAME'];
        if (!isset($map[$name])) { $map[$name] = ['cols' => [], 'non_unique' => (int)$row['NON_UNIQUE']]; }
        $map[$name]['cols'][] = strtolower((string)$row['COLUMN_NAME']);
    }
    $stmt->close();
    $target = array_map('strtolower', $columns);
    foreach ($map as $idx) {
        if ($idx['non_unique'] === 0 && $idx['cols'] === $target) return true;
    }
    return false;
}

function hasDuplicates(mysqli $con, string $table, string $column): bool {
    // Count any duplicate non-null values
    $sql = sprintf('SELECT `%s` FROM `%s` WHERE `%s` IS NOT NULL GROUP BY `%s` HAVING COUNT(*) > 1 LIMIT 1', $column, $table, $column, $column);
    $res = $con->query($sql);
    if (!$res) { return false; }
    $row = $res->fetch_row();
    return (bool)$row;
}

function parseOutArg(array $argv): string {
    $default = projectRoot() . '/database/migrations/016_add_unique_constraints.sql';
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--out=')) {
            $val = substr($arg, 6);
            if (!preg_match('/^([a-zA-Z]:\\\\|\\\\|\/)/', $val)) { // not absolute
                $val = projectRoot() . '/' . ltrim($val, '/');
            }
            return $val;
        }
    }
    return $default;
}

function writeMigration(string $content, string $outPath): void {
    $dir = dirname($outPath);
    if (!is_dir($dir)) { throw new RuntimeException('Migrations directory missing: ' . $dir); }
    if (file_put_contents($outPath, $content) === false) { throw new RuntimeException('Failed to write migration: ' . $outPath); }
    echo "Wrote unique constraints migration: {$outPath}\n";
}

function generateUniqueConstraintsMigration(mysqli $con, string $db): string {
    $lines = [];
    $lines[] = '-- Auto-generated unique constraints migration';
    $lines[] = '-- Adds UNIQUE constraints for common identifiers when no duplicates exist.';
    $lines[] = '';

    $candidates = [
        ['table' => 'users', 'column' => 'email', 'name' => 'uq_users_email'],
        ['table' => 'users', 'column' => 'username', 'name' => 'uq_users_username'],
        ['table' => 'customers', 'column' => 'email', 'name' => 'uq_customers_email'],
        ['table' => 'associates', 'column' => 'referral_code', 'name' => 'uq_associates_referral_code'],
        ['table' => 'api_keys', 'column' => 'api_key', 'name' => 'uq_api_keys_api_key'],
    ];

    foreach ($candidates as $cand) {
        $t = $cand['table']; $c = $cand['column']; $name = $cand['name'];
        if (!tableExists($con, $db, $t)) { $lines[] = sprintf('-- SKIP: `%s` missing', $t); continue; }
        if (!columnExists($con, $db, $t, $c)) { $lines[] = sprintf('-- SKIP: `%s`.`%s` missing', $t, $c); continue; }
        if (hasDuplicates($con, $t, $c)) { $lines[] = sprintf('-- SKIP: `%s`.`%s` has duplicates; cannot add UNIQUE safely', $t, $c); continue; }
        if (uniqueConstraintExists($con, $db, $t, $name) || hasAnyUniqueIndexWithColumns($con, $db, $t, [$c])) {
            $lines[] = sprintf('-- SKIP: UNIQUE on `%s`(%s) already exists', $t, $c);
            continue;
        }
        $lines[] = sprintf('ALTER TABLE `%s` ADD UNIQUE `%s` (`%s`);', $t, $name, $c);
        $lines[] = '';
    }

    if (!array_filter($lines, fn($ln) => str_starts_with($ln, 'ALTER TABLE'))) {
        $lines[] = '-- No new unique constraints required. Safe no-op.';
    }
    return implode("\n", $lines) . "\n";
}

// Main
try {
    $con = getDbConnection();
    $db = getDatabaseName($con);
    if ($db === '') { throw new RuntimeException('Current database name could not be determined.'); }
    $content = generateUniqueConstraintsMigration($con, $db);
    $outPath = parseOutArg($_SERVER['argv'] ?? []);
    writeMigration($content, $outPath);
    echo "Done.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}

?>

