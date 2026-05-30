<?php
/**
 * APS Dream Home - Core Data Seeder
 * Seeds sample data into empty core business tables.
 * Skips tables that already have data.
 */

// Configuration
$dbHost = '127.0.0.1';
$dbPort = '3307';
$dbName = 'apsdreamhome';
$dbUser = 'root';
$dbPass = '';

echo "============================================================\n";
echo " APS Dream Home - Core Data Seeder\n";
echo "============================================================\n\n";

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Database connection established\n\n";
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

$results = ['seeded' => [], 'skipped' => [], 'error' => []];

/**
 * Helper: Check if a table exists in the database.
 */
function tableExists(PDO $pdo, string $table): bool {
    $st = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return $st->rowCount() > 0;
}

/**
 * Helper: Get the actual column names for a table.
 */
function getTableColumns(PDO $pdo, string $table): array {
    $st = $pdo->query("SHOW COLUMNS FROM `$table`");
    $cols = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $cols[] = [
            'name' => $r['Field'],
            'type' => $r['Type'],
            'null' => $r['Null'],
            'default' => $r['Default'],
            'auto' => $r['Extra'] === 'auto_increment',
        ];
    }
    return $cols;
}

/**
 * Helper: Get all rows from a table.
 */
function getTableRows(PDO $pdo, string $table): array {
    $st = $pdo->query("SELECT * FROM `$table`");
    return $st->fetchAll();
}

/**
 * Helper: Build and execute a dynamic INSERT.
 * Only inserts into columns that actually exist on the table.
 * $data is an associative array of column_name => value.
 */
function insertRow(PDO $pdo, string $table, array $data, array $existingCols): bool {
    // Filter data to only include columns that exist and are not auto_increment
    $colNames = array_column($existingCols, 'name');
    $autoCols = [];
    foreach ($existingCols as $c) {
        if ($c['auto']) {
            $autoCols[] = $c['name'];
        }
    }

    $filtered = [];
    foreach ($data as $col => $val) {
        if (in_array($col, $colNames) && !in_array($col, $autoCols)) {
            $filtered[$col] = $val;
        }
    }

    if (empty($filtered)) {
        echo "    ⚠ No valid columns to insert\n";
        return false;
    }

    $cols = array_keys($filtered);
    $placeholders = [];
    foreach ($cols as $c) {
        $placeholders[] = ':' . $c;
    }

    $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")";

    try {
        $st = $pdo->prepare($sql);
        foreach ($filtered as $col => $val) {
            $st->bindValue(':' . $col, $val);
        }
        $st->execute();
        return true;
    } catch (PDOException $e) {
        echo "    ✗ SQL error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Helper: Build and execute a bulk INSERT.
 */
function insertRows(PDO $pdo, string $table, array $rows, array $existingCols): int {
    $count = 0;
    foreach ($rows as $row) {
        if (insertRow($pdo, $table, $row, $existingCols)) {
            $count++;
        }
    }
    return $count;
}

/**
 * Seed a table with sample data.
 * $table: table name
 * $dataFn: function(PDO $pdo, array $columns) that returns array of associative arrays to insert
 */
function seedTable(string $label, PDO $pdo, string $table, callable $dataFn, array &$results): void {
    echo "--- $label ($table) ---\n";

    if (!tableExists($pdo, $table)) {
        echo "  ⏭ Table does not exist. Skipping.\n\n";
        return;
    }

    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    if ($count > 0) {
        echo "  ⏭ Already has $count row(s). Skipping.\n\n";
        $results['skipped'][] = $table;
        return;
    }

    $cols = getTableColumns($pdo, $table);
    $colNames = array_column($cols, 'name');
    echo "  Columns available: " . implode(', ', $colNames) . "\n";

    try {
        $rows = $dataFn($pdo, $cols);
        if (empty($rows)) {
            echo "  ⚠ No data generated. Skipping.\n\n";
            return;
        }

        $inserted = insertRows($pdo, $table, $rows, $cols);
        echo "  ✓ Inserted $inserted row(s)\n\n";
        $results['seeded'][] = $table;
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        $results['error'][] = $table;
    }
}

// ============================================================
// TABLE 1: employee_attendance
// ============================================================
seedTable('Employee Attendance', $pdo, 'employee_attendance',
    function ($pdo, $cols) {
        // Get valid employee IDs from employees table
        $empRows = getTableRows($pdo, 'employees');
        if (empty($empRows)) {
            echo "  ⚠ No employees found. Using default employee_id=1\n";
            $employeeIds = [1];
        } else {
            $employeeIds = array_column($empRows, 'id');
        }

        $rows = [];
        $statuses = ['present', 'present', 'present', 'present', 'present', 'absent', 'half_day', 'present', 'present', 'leave'];
        $dates = [];
        $base = new DateTime('-30 days');
        for ($i = 0; $i < 10; $i++) {
            $dates[] = (clone $base)->modify("+$i days")->format('Y-m-d');
        }

        foreach ($employeeIds as $eid) {
            for ($i = 0; $i < min(10, count($dates)); $i++) {
                $status = $statuses[$i % count($statuses)];
                $checkIn = $dates[$i] . ' 09:00:00';
                $checkOut = $dates[$i] . ' 18:00:00';
                $hours = $status === 'present' ? 9.0 : ($status === 'half_day' ? 4.5 : null);
                $leaveType = null;

                // Ensure days don't go past today
                $d = new DateTime($dates[$i]);
                if ($d > new DateTime()) continue;

                $row = [
                    'employee_id' => $eid,
                    'attendance_date' => $dates[$i],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'check_in_time' => '09:00:00',
                    'check_out_time' => '18:00:00',
                    'total_hours' => $hours,
                    'attendance_status' => $status,
                    'leave_type' => $status === 'leave' ? 'casual' : null,
                    'remarks' => $status === 'absent' ? 'No intimation' : ($status === 'leave' ? 'Pre-approved leave' : null),
                ];
                $rows[] = $row;
            }
            break; // Just one employee to keep it simple
        }
        return $rows;
    },
    $results
);

// ============================================================
// TABLE 2: employee_leave_balances
// ============================================================
seedTable('Employee Leave Balances', $pdo, 'employee_leave_balances',
    function ($pdo, $cols) {
        $empRows = getTableRows($pdo, 'employees');
        if (empty($empRows)) {
            echo "  ⚠ No employees found. Using default employee_id=1\n";
            $employeeIds = [1];
        } else {
            $employeeIds = array_column($empRows, 'id');
        }

        // Check if leave_types table exists
        $leaveTypes = [];
        if (tableExists($pdo, 'leave_types')) {
            $lt = getTableRows($pdo, 'leave_types');
            foreach ($lt as $l) {
                $leaveTypes[] = $l['id'];
            }
        }
        if (empty($leaveTypes)) {
            $leaveTypes = [1, 2, 3]; // fallback
        }

        $rows = [];
        $year = date('Y');
        foreach ($employeeIds as $eid) {
            foreach ($leaveTypes as $ltId) {
                $allocated = [1 => 21, 2 => 7, 3 => 7, 4 => 84, 5 => 7, 6 => 3, 7 => 0][$ltId] ?? 10;
                $used = rand(0, min(5, $allocated));
                $carried = $ltId === 1 ? 2 : 0;
                $remaining = $allocated - $used + $carried;
                $rows[] = [
                    'employee_id' => $eid,
                    'leave_type_id' => $ltId,
                    'year' => $year,
                    'allocated_days' => $allocated,
                    'used_days' => $used,
                    'remaining_days' => $remaining,
                    'carried_forward' => $carried,
                ];
            }
            break; // Just one employee
        }
        return $rows;
    },
    $results
);

// ============================================================
// TABLE 3: employee_documents
// ============================================================
seedTable('Employee Documents', $pdo, 'employee_documents',
    function ($pdo, $cols) {
        $empRows = getTableRows($pdo, 'employees');
        $employeeId = !empty($empRows) ? $empRows[0]['id'] : 1;

        $docs = [
            ['document_type' => 'resume', 'document_name' => 'John_Resume_2026.pdf', 'file_path' => 'uploads/documents/resumes/john_resume.pdf'],
            ['document_type' => 'id_proof', 'document_name' => 'Aadhar_Card.pdf', 'file_path' => 'uploads/documents/id_proofs/aadhar_card.pdf'],
            ['document_type' => 'degree', 'document_name' => 'B.Tech_Certificate.pdf', 'file_path' => 'uploads/documents/degrees/btech_certificate.pdf'],
        ];

        $rows = [];
        foreach ($docs as $d) {
            $d['employee_id'] = $employeeId;
            $rows[] = $d;
        }
        return $rows;
    },
    $results
);

// ============================================================
// TABLE 4: mlm_earnings
// ============================================================
seedTable('MLM Earnings', $pdo, 'mlm_earnings',
    function ($pdo, $cols) {
        // Get associate user IDs
        $assocUsers = $pdo->query("SELECT id FROM users WHERE role = 'associate' LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($assocUsers)) {
            // Fallback: try any user
            $assocUsers = $pdo->query("SELECT id FROM users LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        }
        if (empty($assocUsers)) {
            echo "  ⚠ No users found\n";
            return [];
        }

        $types = ['direct_sale', 'team_bonus', 'matching_bonus'];
        $rows = [];
        foreach ($assocUsers as $i => $uid) {
            $rows[] = [
                'user_id' => $uid,
                'earning_type' => $types[$i % count($types)],
                'amount' => [5000, 12000, 2500][$i % 3],
                'level' => ($i % 3) + 1,
                'from_user_id' => $assocUsers[($i + 1) % count($assocUsers)],
                'status' => ['approved', 'pending', 'paid'][$i % 3],
            ];
        }
        return $rows;
    },
    $results
);

// ============================================================
// TABLE 5: mlm_points
// ============================================================
seedTable('MLM Points', $pdo, 'mlm_points',
    function ($pdo, $cols) {
        $assocUsers = $pdo->query("SELECT id FROM users WHERE role = 'associate' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($assocUsers)) {
            $assocUsers = $pdo->query("SELECT id FROM users LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
        }
        if (empty($assocUsers)) return [];

        $rows = [];
        $types = ['referral', 'purchase', 'training'];
        foreach ($assocUsers as $i => $uid) {
            $rows[] = [
                'user_id' => $uid,
                'points' => [100, 250, 50][$i],
                'points_type' => $types[$i],
                'description' => ['New customer referral', 'Commission on sale', 'Training module completed'][$i],
            ];
        }
        return $rows;
    },
    $results
);

// ============================================================
// TABLE 6: mlm_transactions
// ============================================================
seedTable('MLM Transactions', $pdo, 'mlm_transactions',
    function ($pdo, $cols) {
        $assocUsers = $pdo->query("SELECT id FROM users WHERE role = 'associate' LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($assocUsers)) {
            $assocUsers = $pdo->query("SELECT id FROM users LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);
        }
        if (empty($assocUsers)) return [];

        $rows = [];
        $txTypes = ['commission_credit', 'points_redeem', 'referral_bonus'];
        foreach ($assocUsers as $i => $uid) {
            if ($i >= 3) break;
            $rows[] = [
                'user_id' => $uid,
                'from_user_id' => $assocUsers[($i + 1) % count($assocUsers)],
                'transaction_type' => $txTypes[$i],
                'points' => [0, 100, 0][$i],
                'amount' => [15000.00, 0.00, 5000.00][$i],
                'description' => ['Commission for plot sale #1024', 'Points redeemed for rewards', 'Referral bonus for customer #CS-501'][$i],
                'reference_id' => ['INV-2026-001', 'RED-2026-001', 'REF-2026-001'][$i],
            ];
        }
        return $rows;
    },
    $results
);

// ============================================================
// SUMMARY
// ============================================================
echo "============================================================\n";
echo " SEEDING COMPLETE\n";
echo "============================================================\n";

if (!empty($results['seeded'])) {
    echo "✓ Tables seeded: " . implode(', ', $results['seeded']) . "\n";
    foreach ($results['seeded'] as $t) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "   - $t: $cnt row(s)\n";
    }
}

if (!empty($results['skipped'])) {
    echo "⏭ Tables skipped (already had data): " . implode(', ', $results['skipped']) . "\n";
}

if (!empty($results['error'])) {
    echo "✗ Tables with errors: " . implode(', ', $results['error']) . "\n";
}

echo "\nDone.\n";
