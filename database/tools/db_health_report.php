<?php
/**
 * DB Health Report
 * - Summarizes indexes, unique indexes, foreign keys per table
 * - Checks common orphan relationships safely
 * - Writes Markdown to docs/DB_HEALTH_REPORT.md and prints console summary
 */

declare(strict_types=1);

// Bootstrap DB config (use centralized constants and options)
require_once __DIR__ . '/../../includes/db_config.php';

function makePdo(): PDO {
    $dsn = getDatabaseDSN();
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, DB_OPTIONS);
    $pdo->exec("SET time_zone = '+05:30'");
    return $pdo;
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function getTables(PDO $pdo): array {
    $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME";
    return array_map(fn($r) => $r['TABLE_NAME'], $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
}

function getIndexCounts(PDO $pdo, string $table): array {
    $all = $pdo->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ?");
    $uniq = $pdo->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND NON_UNIQUE = 0");
    $fk = $pdo->prepare("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE table_schema = DATABASE() AND table_name = ? AND REFERENCED_TABLE_NAME IS NOT NULL");
    $all->execute([$table]);
    $uniq->execute([$table]);
    $fk->execute([$table]);
    return [
        'indexes' => (int)$all->fetchColumn(),
        'unique' => (int)$uniq->fetchColumn(),
        'fks' => (int)$fk->fetchColumn(),
    ];
}

function orphanCount(PDO $pdo, string $childTable, string $childCol, string $parentTable, string $parentPk = 'id'): ?int {
    if (!tableExists($pdo, $childTable) || !tableExists($pdo, $parentTable)) { return null; }
    if (!columnExists($pdo, $childTable, $childCol) || !columnExists($pdo, $parentTable, $parentPk)) { return null; }
    $sql = "SELECT COUNT(*) AS orphan_count FROM {$childTable} c LEFT JOIN {$parentTable} p ON p.{$parentPk} = c.{$childCol} WHERE c.{$childCol} IS NOT NULL AND p.{$parentPk} IS NULL";
    try {
        return (int)$pdo->query($sql)->fetchColumn();
    } catch (Throwable $e) {
        return null;
    }
}

function main(): void {
    $pdo = makePdo();
    $tables = getTables($pdo);

    $lines = [];
    $lines[] = '# Database Health Report';
    $lines[] = 'Generated: ' . date('c');
    $lines[] = '';

    // Summary per table
    $lines[] = '## Table Summary';
    foreach ($tables as $t) {
        $c = getIndexCounts($pdo, $t);
        $lines[] = "- `{$t}`: indexes={$c['indexes']}, unique={$c['unique']}, foreign_keys={$c['fks']}";
    }
    $lines[] = '';

    // Orphan checks
    $pairs = [
        ['bookings','property_id','properties','id'],
        ['bookings','customer_id','customers','id'],
        ['transactions','customer_id','customers','id'],
        ['transactions','property_id','properties','id'],
        ['mlm_commissions','associate_id','associates','id'],
        ['mlm_commissions','booking_id','bookings','id'],
        ['plot_bookings','plot_id','plots','id'],
        ['plot_bookings','associate_id','associates','id'],
        ['property_visits','property_id','properties','id'],
        ['property_visits','user_id','users','id'],
    ];

    $lines[] = '## Orphan Checks';
    foreach ($pairs as [$child,$fk,$parent,$pk]) {
        $count = orphanCount($pdo, $child, $fk, $parent, $pk);
        if ($count === null) {
            $lines[] = "- `{$child}.{$fk}` -> `{$parent}.{$pk}`: skipped (missing table/column)";
        } else {
            $status = $count === 0 ? 'healthy' : "orphaned_rows={$count}";
            $lines[] = "- `{$child}.{$fk}` -> `{$parent}.{$pk}`: {$status}";
        }
    }

    $report = implode("\n", $lines) . "\n";

    // Write to docs
    $outDir = dirname(__DIR__, 2) . '/docs';
    if (!is_dir($outDir)) { mkdir($outDir, 0777, true); }
    $outFile = $outDir . '/DB_HEALTH_REPORT.md';
    file_put_contents($outFile, $report);

    // Print to console
    echo $report;
}

main();
?>

