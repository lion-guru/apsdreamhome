<?php
/**
 * Propose FK Cascade Policies
 * - Scans foreign keys and suggests ON DELETE/UPDATE actions
 * - Heuristics: nullable -> SET NULL; non-null dependent -> CASCADE; else RESTRICT
 * - Writes Markdown to docs/FK_CASCADE_POLICY.md
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db_config.php';

function makePdo(): PDO {
    $dsn = getDatabaseDSN();
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, DB_OPTIONS);
    return $pdo;
}

function getForeignKeys(PDO $pdo): array {
    $sql = "SELECT rc.CONSTRAINT_NAME,
                   rc.UPDATE_RULE, rc.DELETE_RULE,
                   k.TABLE_NAME AS child_table,
                   k.COLUMN_NAME AS child_column,
                   k.REFERENCED_TABLE_NAME AS parent_table,
                   k.REFERENCED_COLUMN_NAME AS parent_column
            FROM information_schema.REFERENTIAL_CONSTRAINTS rc
            JOIN information_schema.KEY_COLUMN_USAGE k
              ON rc.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
             AND rc.CONSTRAINT_NAME = k.CONSTRAINT_NAME
            WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
            ORDER BY child_table, rc.CONSTRAINT_NAME, k.ORDINAL_POSITION";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by constraint
    $fks = [];
    foreach ($rows as $r) {
        $key = $r['child_table'] . '|' . $r['CONSTRAINT_NAME'];
        if (!isset($fks[$key])) {
            $fks[$key] = [
                'constraint' => $r['CONSTRAINT_NAME'],
                'child_table' => $r['child_table'],
                'parent_table' => $r['parent_table'],
                'columns' => [],
                'update_rule' => $r['UPDATE_RULE'],
                'delete_rule' => $r['DELETE_RULE'],
            ];
        }
        $fks[$key]['columns'][] = [
            'child_column' => $r['child_column'],
            'parent_column' => $r['parent_column'],
        ];
    }
    return array_values($fks);
}

function isNullable(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $stmt->execute([$table, $column]);
    return strtoupper((string)$stmt->fetchColumn()) === 'YES';
}

function childRowCount(PDO $pdo, string $table): ?int {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return null;
    }
}

function heuristicSuggestDeleteRule(PDO $pdo, array $fk): string {
    // If any child column is nullable, SET NULL is reasonable
    $nullable = false;
    foreach ($fk['columns'] as $col) {
        if (isNullable($pdo, $fk['child_table'], $col['child_column'])) { $nullable = true; break; }
    }
    if ($nullable) { return 'SET NULL'; }

    // Name-based heuristic for dependent rows
    $child = strtolower($fk['child_table']);
    $dependentHints = ['bookings','booking_items','property_visits','commission_transactions','transactions','images','photos','messages','comments'];
    foreach ($dependentHints as $hint) {
        if (str_contains($child, $hint)) { return 'CASCADE'; }
    }
    // Default conservative
    return 'RESTRICT';
}

function buildAddConstraintSQL(array $fk, string $deleteRule, ?string $updateRule = null): string {
    $updateRule = $updateRule ?: $fk['update_rule'];
    $child = $fk['child_table'];
    $parent = $fk['parent_table'];
    $colsChild = implode('`, `', array_map(fn($c) => $c['child_column'], $fk['columns']));
    $colsParent = implode('`, `', array_map(fn($c) => $c['parent_column'], $fk['columns']));
    $name = $fk['constraint'];
    return "ALTER TABLE `{$child}` DROP FOREIGN KEY `{$name}`;\n" .
           "ALTER TABLE `{$child}` ADD CONSTRAINT `{$name}` FOREIGN KEY (`{$colsChild}`) REFERENCES `{$parent}` (`{$colsParent}`) ON DELETE {$deleteRule} ON UPDATE {$updateRule};";
}

function main(): void {
    $pdo = makePdo();
    $fks = getForeignKeys($pdo);
    $md = [];
    $md[] = '# FK Cascade Policy Proposal';
    $md[] = 'Generated: ' . date('c');
    $md[] = '';

    foreach ($fks as $fk) {
        $childCount = childRowCount($pdo, $fk['child_table']);
        $suggest = heuristicSuggestDeleteRule($pdo, $fk);
        $md[] = '## ' . $fk['child_table'] . ' -> ' . $fk['parent_table'] . ' (' . $fk['constraint'] . ')';
        $md[] = '- Current: DELETE ' . $fk['delete_rule'] . ', UPDATE ' . $fk['update_rule'];
        $md[] = '- Child rows: ' . ($childCount ?? 'unknown');
        $md[] = '- Suggested DELETE rule: ' . $suggest;
        $md[] = '';
        if ($suggest !== $fk['delete_rule']) {
            $md[] = '```sql';
            $md[] = buildAddConstraintSQL($fk, $suggest);
            $md[] = '```';
        } else {
            $md[] = '- No change suggested.';
        }
        $md[] = '';
    }

    $outDir = dirname(__DIR__, 2) . '/docs';
    if (!is_dir($outDir)) { mkdir($outDir, 0777, true); }
    $outFile = $outDir . '/FK_CASCADE_POLICY.md';
    file_put_contents($outFile, implode("\n", $md));
    echo implode("\n", $md);
}

main();
?>

