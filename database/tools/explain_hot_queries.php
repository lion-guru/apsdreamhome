<?php
/**
 * Explain Hot Queries
 * - Runs EXPLAIN on representative queries found in code
 * - Writes Markdown to docs/QUERY_PLAN_REPORT.md
 */

declare(strict_types=1);

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

function hasColumns(PDO $pdo, string $table, array $columns): bool {
    $in = str_repeat('?,', count($columns) - 1) . '?';
    $sql = "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name IN ($in)";
    $params = array_merge([$table], $columns);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() === count($columns);
}

function existingColumns(PDO $pdo, string $table, array $candidates): array {
    $in = str_repeat('?,', count($candidates) - 1) . '?';
    $sql = "SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name IN ($in)";
    $params = array_merge([$table], $candidates);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function tryExplain(PDO $pdo, string $label, string $query): array {
    // Prefer JSON explain if supported
    try {
        $stmt = $pdo->query("EXPLAIN FORMAT=JSON " . $query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['EXPLAIN'])) {
            return ['label' => $label, 'format' => 'JSON', 'explain' => $row['EXPLAIN']];
        }
    } catch (Throwable $e) {
        // Fallback to classic explain
    }

    try {
        $stmt = $pdo->query("EXPLAIN " . $query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['label' => $label, 'format' => 'TABLE', 'explain' => $rows];
    } catch (Throwable $e) {
        return ['label' => $label, 'format' => 'ERROR', 'error' => $e->getMessage()];
    }
}

function recommendationForExplain(array $rep): ?string {
    if (($rep['format'] ?? '') === 'JSON') {
        $json = (string)($rep['explain'] ?? '');
        if (stripos($json, 'filesort') !== false) {
            return 'Detected filesort. Consider composite index that matches WHERE equality prefix and ORDER BY columns/directions.';
        }
    } elseif (($rep['format'] ?? '') === 'TABLE') {
        $rows = $rep['explain'] ?? [];
        foreach ($rows as $row) {
            if (isset($row['Extra']) && stripos((string)$row['Extra'], 'Using filesort') !== false) {
                return 'Detected filesort. Consider composite index that matches WHERE equality prefix and ORDER BY columns/directions.';
            }
        }
    }
    return null;
}

function main(): void {
    $pdo = makePdo();
    $reports = [];

    // 1) Bookings: status + date range with ordering
    if (tableExists($pdo, 'bookings') && hasColumns($pdo, 'bookings', ['status','booking_date','created_at'])) {
        $q1 = "SELECT id, customer_id, property_id, booking_date, status, created_at\n            FROM bookings\n            WHERE status = 'confirmed'\n              AND booking_date BETWEEN '2023-01-01' AND '2030-12-31'\n            ORDER BY booking_date DESC, created_at DESC\n            LIMIT 50";
        $reports[] = tryExplain($pdo, 'bookings: status + booking_date order by booking_date,created_at', $q1);
    }

    // 2) Bookings: property + date ordering
    if (tableExists($pdo, 'bookings') && hasColumns($pdo, 'bookings', ['property_id','booking_date'])) {
        $q2 = "SELECT id, customer_id, property_id, booking_date, status\n            FROM bookings\n            WHERE property_id = 12345\n            ORDER BY booking_date DESC\n            LIMIT 50";
        $reports[] = tryExplain($pdo, 'bookings: property_id + booking_date order', $q2);
    }

    // 3) Properties search: type/city/state/price/bedrooms/bathrooms/min_area
    $propTable = null;
    foreach (['properties','property'] as $pt) { if (tableExists($pdo, $pt)) { $propTable = $pt; break; } }
    if ($propTable) {
        $hasCityState = hasColumns($pdo, $propTable, ['city','state']);
        // Determine bedroom/bathroom columns
        $bedCols = ['bedroom','bedrooms'];
        $bathCols = ['bathroom','bathrooms'];
        $typeCols = ['type','property_type'];
        $existing = fn($opts) => array_values(array_filter($opts, fn($c) => hasColumns($pdo, $propTable, [$c])));
        $bedCol = ($tmp = $existing($bedCols)) ? $tmp[0] : null;
        $bathCol = ($tmp = $existing($bathCols)) ? $tmp[0] : null;
        $typeCol = ($tmp = $existing($typeCols)) ? $tmp[0] : null;
        $hasPrice = hasColumns($pdo, $propTable, ['price']);
        $hasCreated = hasColumns($pdo, $propTable, ['created_at']);
        $hasMinArea = hasColumns($pdo, $propTable, ['min_area']);

        if ($hasCityState && $bedCol && $bathCol && $typeCol && $hasPrice) {
            $selectCols = ['id','title','city','state','price',$bedCol,$bathCol];
            if ($hasCreated) { $selectCols[] = 'created_at'; }
            $select = implode(',', array_map(fn($c) => $c, $selectCols));
            $order = $hasCreated ? ' ORDER BY created_at DESC' : '';
            $minAreaFilter = $hasMinArea ? ' AND min_area >= 1000' : '';
            $q3 = "SELECT {$select}\n                FROM {$propTable}\n                WHERE {$typeCol} = 'apartment'\n                  AND city = 'Noida'\n                  AND state = 'UP'\n                  AND price BETWEEN 1000000 AND 5000000\n                  AND {$bedCol} >= 2\n                  AND {$bathCol} >= 2{$minAreaFilter}{$order}\n                LIMIT 50";
            $reports[] = tryExplain($pdo, "{$propTable}: typed city/state price range with {$bedCol}/{$bathCol}", $q3);
        }
    }

    // 4) Users: status + utype + last_login order
    if (tableExists($pdo, 'users')) {
        $cols = array_filter(['status','utype','last_login'], fn($c) => hasColumns($pdo, 'users', [$c]));
        if (in_array('status', $cols) && in_array('utype', $cols)) {
            $order = in_array('last_login', $cols) ? ' ORDER BY last_login DESC' : '';
            $q4 = "SELECT id, email, status, utype, last_login FROM users\n                WHERE status = 'active' AND utype = 'admin'{$order}\n                LIMIT 50";
            $reports[] = tryExplain($pdo, 'users: status + utype ordered by last_login', $q4);
        }
    }

    // 5) Commission transactions: associate + date desc
    if (tableExists($pdo, 'commission_transactions')) {
        $dateCol = hasColumns($pdo, 'commission_transactions', ['transaction_date']) ? 'transaction_date' : (hasColumns($pdo, 'commission_transactions', ['created_at']) ? 'created_at' : null);
        if ($dateCol && hasColumns($pdo, 'commission_transactions', ['associate_id'])) {
            $amountCol = existingColumns($pdo, 'commission_transactions', ['amount','commission_amount','total_amount']);
            $selectCols = array_merge(['associate_id'], $amountCol);
            $selectCols[] = $dateCol;
            $select = implode(', ', $selectCols);
            $q5 = "SELECT {$select} FROM commission_transactions\n                WHERE associate_id = 1\n                ORDER BY {$dateCol} DESC\n                LIMIT 50";
            $reports[] = tryExplain($pdo, 'commission_transactions: associate + date desc', $q5);
        }
    }

    // 6) property_visits: property + user + created_at desc
    if (tableExists($pdo, 'property_visits')) {
        $need = ['property_id','user_id'];
        $ok = hasColumns($pdo, 'property_visits', $need);
        $dateCol = hasColumns($pdo, 'property_visits', ['created_at']) ? 'created_at' : null;
        if ($ok) {
            $selectCols = ['property_id','user_id'];
            if ($dateCol) { $selectCols[] = $dateCol; }
            $select = implode(', ', $selectCols);
            $order = $dateCol ? " ORDER BY {$dateCol} DESC" : '';
            $q6 = "SELECT {$select} FROM property_visits\n                WHERE property_id = 1 AND user_id = 1{$order}\n                LIMIT 50";
            $reports[] = tryExplain($pdo, 'property_visits: property_id + user_id order by created_at', $q6);
        }
    }

    // Build Markdown
    $md = [];
    $md[] = '# Query Plan Report';
    $md[] = 'Generated: ' . date('c');
    $md[] = '';
    foreach ($reports as $rep) {
        $md[] = '## ' . $rep['label'];
        if ($rep['format'] === 'JSON') {
            $md[] = '```json';
            $md[] = $rep['explain'];
            $md[] = '```';
        } elseif ($rep['format'] === 'TABLE') {
            $md[] = '```';
            // Render compactly
            foreach ($rep['explain'] as $row) {
                $md[] = implode(' | ', array_map(fn($k,$v) => $k . '=' . (is_scalar($v) ? (string)$v : json_encode($v)), array_keys($row), $row));
            }
            $md[] = '```';
        } elseif ($rep['format'] === 'ERROR') {
            $md[] = '- Error: ' . $rep['error'];
        } else {
            $md[] = '- Unsupported explain format';
        }
        $rec = recommendationForExplain($rep);
        if ($rec) {
            $md[] = '- Recommendation: ' . $rec;
        }
        $md[] = '';
    }

    $outDir = dirname(__DIR__, 2) . '/docs';
    if (!is_dir($outDir)) { mkdir($outDir, 0777, true); }
    $outFile = $outDir . '/QUERY_PLAN_REPORT.md';
    file_put_contents($outFile, implode("\n", $md));

    echo implode("\n", $md);
}

main();
?>
