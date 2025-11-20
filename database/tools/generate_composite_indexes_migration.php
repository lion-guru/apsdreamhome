<?php
declare(strict_types=1);

// Composite Indexes Generator
// Scans live schema and generates ADD INDEX statements for multi-column patterns
// derived from common query hotspots. Skips existing indexes and missing columns.
// Usage: php database/tools/generate_composite_indexes_migration.php --out=database/migrations/015_add_composite_indexes.sql

error_reporting(E_ALL);
ini_set('display_errors', '1');

function projectRoot(): string {
    return dirname(__DIR__, 2);
}

function getDbConnection(): mysqli {
    $configPath = projectRoot() . '/includes/config/config.php';
    if (!file_exists($configPath)) {
        fwrite(STDERR, "Config not found: {$configPath}\n");
        exit(1);
    }
    require $configPath; // expects $con (mysqli)
    if (!isset($con) || !($con instanceof mysqli)) {
        fwrite(STDERR, "Database connection $con not available after requiring config.\n");
        exit(1);
    }
    return $con;
}

function getDatabaseName(mysqli $con): string {
    $res = $con->query('SELECT DATABASE() as db');
    if (!$res) {
        throw new RuntimeException('Failed to query current database: ' . $con->error);
    }
    $row = $res->fetch_assoc();
    return $row['db'] ?? '';
}

function isBaseTable(mysqli $con, string $db, string $table): bool {
    $stmt = $con->prepare('SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return isset($row['TABLE_TYPE']) && strtoupper($row['TABLE_TYPE']) === 'BASE TABLE';
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

function getExistingIndexDefs(mysqli $con, string $db, string $table): array {
    // Returns map: index_name => [col1, col2, ...] in order
    $stmt = $con->prepare('SELECT INDEX_NAME, SEQ_IN_INDEX, COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX');
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $defs = [];
    while ($row = $res->fetch_assoc()) {
        $idx = $row['INDEX_NAME'];
        $col = strtolower((string)$row['COLUMN_NAME']);
        if (!isset($defs[$idx])) { $defs[$idx] = []; }
        $defs[$idx][] = $col;
    }
    $stmt->close();
    return $defs;
}

function hasIndexWithColumns(mysqli $con, string $db, string $table, array $columns): bool {
    // Compare ignoring ASC/DESC since many MySQL versions store only ASC in metadata
    $normalizedTarget = array_map(fn($c) => strtolower($c), $columns);
    $existing = getExistingIndexDefs($con, $db, $table);
    foreach ($existing as $cols) {
        if ($cols === $normalizedTarget) { return true; }
    }
    return false;
}

function indexExistsByName(mysqli $con, string $db, string $table, string $indexName): bool {
    $stmt = $con->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1');
    $stmt->bind_param('sss', $db, $table, $indexName);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function makeIndexName(string $table, array $cols): string {
    $suffix = implode('_', array_map(fn($c) => preg_replace('/\s+DESC$/i', '', $c), $cols));
    $name = 'ix_' . $table . '_' . $suffix;
    if (strlen($name) > 64) { $name = substr($name, 0, 64); }
    return $name;
}

function findFirstExistingColumn(mysqli $con, string $db, string $table, array $options): ?string {
    foreach ($options as $opt) {
        if (columnExists($con, $db, $table, $opt)) return $opt;
    }
    return null;
}

/**
 * Returns composite index suggestions.
 * Each item: [table => string, columns => ["col1", "col2 DESC"], name => string]
 */
function getCompositeSuggestions(mysqli $con, string $db): array {
    $suggestions = [];

    // bookings
    if (tableExists($con, $db, 'bookings') && isBaseTable($con, $db, 'bookings')) {
        $suggestions[] = ['table' => 'bookings', 'columns' => ['status', 'booking_date DESC'], 'name' => 'idx_bookings_status_date'];
        if (columnExists($con, $db, 'bookings', 'payment_status')) {
            $suggestions[] = ['table' => 'bookings', 'columns' => ['payment_status', 'status'], 'name' => 'idx_bookings_payment_status'];
        }
        if (columnExists($con, $db, 'bookings', 'property_id')) {
            $suggestions[] = ['table' => 'bookings', 'columns' => ['property_id', 'booking_date DESC'], 'name' => 'idx_bookings_property_date'];
        }
        if (columnExists($con, $db, 'bookings', 'customer_id') && columnExists($con, $db, 'bookings', 'created_at')) {
            $suggestions[] = ['table' => 'bookings', 'columns' => ['customer_id', 'created_at DESC'], 'name' => 'idx_bookings_customer_created'];
        }
    }

    // commission_transactions
    if (tableExists($con, $db, 'commission_transactions') && isBaseTable($con, $db, 'commission_transactions')) {
        if (columnExists($con, $db, 'commission_transactions', 'associate_id') && columnExists($con, $db, 'commission_transactions', 'transaction_date')) {
            $suggestions[] = ['table' => 'commission_transactions', 'columns' => ['associate_id', 'transaction_date DESC'], 'name' => 'idx_commission_transactions_associate_date'];
        }
        if (columnExists($con, $db, 'commission_transactions', 'status') && columnExists($con, $db, 'commission_transactions', 'transaction_date')) {
            $suggestions[] = ['table' => 'commission_transactions', 'columns' => ['status', 'transaction_date DESC'], 'name' => 'idx_commission_transactions_status_date'];
        }
    }

    // properties
    if (tableExists($con, $db, 'properties') && isBaseTable($con, $db, 'properties')) {
        $typeCol = findFirstExistingColumn($con, $db, 'properties', ['type_id', 'property_type_id', 'type']);
        if (columnExists($con, $db, 'properties', 'status') && $typeCol) {
            $suggestions[] = ['table' => 'properties', 'columns' => ['status', $typeCol], 'name' => 'idx_properties_status_type'];
        }
        // price + bedrooms + bathrooms
        $bedCol = findFirstExistingColumn($con, $db, 'properties', ['bedrooms', 'bedroom']);
        $bathCol = findFirstExistingColumn($con, $db, 'properties', ['bathrooms', 'bathroom']);
        if (columnExists($con, $db, 'properties', 'price') && $bedCol && $bathCol) {
            $suggestions[] = ['table' => 'properties', 'columns' => ['price', $bedCol, $bathCol], 'name' => 'idx_properties_price_bed_bath'];
        }
        if (columnExists($con, $db, 'properties', 'city') && columnExists($con, $db, 'properties', 'state') && columnExists($con, $db, 'properties', 'price')) {
            $suggestions[] = ['table' => 'properties', 'columns' => ['city', 'state', 'price'], 'name' => 'idx_properties_city_state_price'];
        }
    }

    // property_visits
    if (tableExists($con, $db, 'property_visits') && isBaseTable($con, $db, 'property_visits')) {
        $dateCol = findFirstExistingColumn($con, $db, 'property_visits', ['visit_date', 'created_at']);
        if (columnExists($con, $db, 'property_visits', 'property_id') && $dateCol) {
            $suggestions[] = ['table' => 'property_visits', 'columns' => ['property_id', $dateCol . ' DESC'], 'name' => 'idx_property_visits_property_date'];
        }
        if (columnExists($con, $db, 'property_visits', 'customer_id') && $dateCol) {
            $suggestions[] = ['table' => 'property_visits', 'columns' => ['customer_id', $dateCol . ' DESC'], 'name' => 'idx_property_visits_customer_date'];
        }
        if (columnExists($con, $db, 'property_visits', 'status') && $dateCol) {
            $suggestions[] = ['table' => 'property_visits', 'columns' => ['status', $dateCol . ' DESC'], 'name' => 'idx_property_visits_status_date'];
        }
    }

    // users
    if (tableExists($con, $db, 'users') && isBaseTable($con, $db, 'users')) {
        if (columnExists($con, $db, 'users', 'email') && columnExists($con, $db, 'users', 'status')) {
            $suggestions[] = ['table' => 'users', 'columns' => ['email', 'status'], 'name' => 'idx_users_email_status'];
        }
        $lastLoginCol = findFirstExistingColumn($con, $db, 'users', ['last_login', 'last_login_at']);
        if (columnExists($con, $db, 'users', 'role') && $lastLoginCol) {
            $suggestions[] = ['table' => 'users', 'columns' => ['role', $lastLoginCol . ' DESC'], 'name' => 'idx_users_role_last_login'];
        }
    }

    // associates
    if (tableExists($con, $db, 'associates') && isBaseTable($con, $db, 'associates')) {
        if (columnExists($con, $db, 'associates', 'sponsor_id') && columnExists($con, $db, 'associates', 'level')) {
            $suggestions[] = ['table' => 'associates', 'columns' => ['sponsor_id', 'level'], 'name' => 'idx_associates_sponsor_level'];
        }
        if (columnExists($con, $db, 'associates', 'level') && columnExists($con, $db, 'associates', 'total_business')) {
            $suggestions[] = ['table' => 'associates', 'columns' => ['level', 'total_business DESC'], 'name' => 'idx_associates_level_business'];
        }
    }

    return $suggestions;
}

function generateCompositeIndexMigration(mysqli $con, string $db): string {
    $lines = [];
    $lines[] = '-- Auto-generated composite index migration';
    $lines[] = '-- Adds multi-column indexes for common query patterns, skipping existing ones.';
    $lines[] = '';

    $suggestions = getCompositeSuggestions($con, $db);
    foreach ($suggestions as $s) {
        $table = $s['table'];
        $colsSpec = $s['columns'];
        $name = $s['name'] ?: makeIndexName($table, $colsSpec);

        // Validate table and columns
        if (!tableExists($con, $db, $table) || !isBaseTable($con, $db, $table)) {
            $lines[] = sprintf('-- SKIP: `%s` not a base table or missing', $table);
            continue;
        }
        $colNames = [];
        $colFragments = [];
        $valid = true;
        foreach ($colsSpec as $cSpec) {
            // cSpec can be "col" or "col DESC"
            $parts = preg_split('/\s+/', trim($cSpec));
            $col = $parts[0];
            $dir = (isset($parts[1]) && strtoupper($parts[1]) === 'DESC') ? 'DESC' : 'ASC';
            if (!columnExists($con, $db, $table, $col)) { $valid = false; break; }
            $colNames[] = strtolower($col);
            $colFragments[] = "`{$col}`" . ($dir === 'DESC' ? ' DESC' : '');
        }
        if (!$valid) {
            $lines[] = sprintf('-- SKIP: `%s` missing one or more columns for %s', $table, $name);
            continue;
        }

        // Skip if an index with same column sequence already exists
        if (hasIndexWithColumns($con, $db, $table, $colNames)) {
            $lines[] = sprintf('-- SKIP: `%s` already has index covering (%s)', $table, implode(', ', $colNames));
            continue;
        }
        // Skip if index by name exists
        if (indexExistsByName($con, $db, $table, $name)) {
            $lines[] = sprintf('-- SKIP: index `%s` already exists on `%s`', $name, $table);
            continue;
        }

        $lines[] = sprintf('ALTER TABLE `%s` ADD INDEX `%s` (%s);', $table, $name, implode(', ', $colFragments));
        $lines[] = '';
    }

    if (!array_filter($lines, fn($ln) => str_starts_with($ln, 'ALTER TABLE'))) {
        $lines[] = '-- No new composite indexes required. Safe no-op.';
    }

    return implode("\n", $lines) . "\n";
}

function parseOutArg(array $argv): string {
    $default = projectRoot() . '/database/migrations/015_add_composite_indexes.sql';
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
    if (file_put_contents($outPath, $content) === false) {
        throw new RuntimeException('Failed to write migration: ' . $outPath);
    }
    echo "Wrote composite indexes migration: {$outPath}\n";
}

// Main
try {
    $con = getDbConnection();
    $db = getDatabaseName($con);
    if ($db === '') { throw new RuntimeException('Current database name could not be determined.'); }
    $content = generateCompositeIndexMigration($con, $db);
    $outPath = parseOutArg($_SERVER['argv'] ?? []);
    writeMigration($content, $outPath);
    echo "Done.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}

?>

