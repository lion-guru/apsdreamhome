<?php
/**
 * seed_last_18.php - Seed 18 remaining empty feature tables
 * 
 * Dynamically detects column names, ENUM values, and inserts sensible test data.
 * Skips tables that already have data.
 */

$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "=== Seed Last 18 Empty Tables ===\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// ─── Helper ──────────────────────────────────────────────────────────────────
function getEnumValues(PDO $pdo, string $table, string $column): ?array
{
    $row = $pdo->query("SHOW COLUMNS FROM `$table` WHERE Field = " . $pdo->quote($column))->fetch();
    if (!$row) return null;
    $type = $row['Type'] ?? '';
    if (!str_starts_with($type, "enum('")) return null;
    // Extract values inside enum(...)
    preg_match("/^enum\('(.+)'\)$/", $type, $m);
    if (!$m) return null;
    // Split by ',' but handle escaped single quotes '' 
    // Simpler: explode by "','"
    return explode("','", $m[1]);
}

function getColumnTypes(PDO $pdo, string $table): array
{
    $rows = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll();
    $map = [];
    foreach ($rows as $r) {
        $map[$r['Field']] = [
            'type'     => $r['Type'],
            'null'     => $r['Null'],
            'default'  => $r['Default'],
            'extra'    => $r['Extra'],
        ];
    }
    return $map;
}

function isNullable(string $nullStr): bool
{
    return strtoupper($nullStr) === 'YES';
}

// ─── Seed data definitions ───────────────────────────────────────────────────
// Each entry: table name => array of rows (each row is an assoc array values)
// Only non-id, non-auto-increment columns are included here.
// The script will skip NULLABLE columns that are not supplied (using NULL)
// and use defaults where possible.

$seedData = [
    'leaderboard_entries' => [
        ['user_id' => 1, 'score' => 1200, 'period' => 'monthly', 'rank' => 1],
        ['user_id' => 2, 'score' => 980, 'period' => 'monthly', 'rank' => 2],
        ['user_id' => 3, 'score' => 750, 'period' => 'monthly', 'rank' => 3],
        ['user_id' => 4, 'score' => 500, 'period' => 'weekly', 'rank' => 1],
        ['user_id' => 5, 'score' => 300, 'period' => 'weekly', 'rank' => 2],
    ],
    'lead_export_logs' => [
        ['user_id' => 1, 'export_type' => 'csv', 'count' => 150, 'file_path' => '/exports/leads_20260501.csv'],
        ['user_id' => 2, 'export_type' => 'excel', 'count' => 75, 'file_path' => '/exports/leads_20260502.xlsx'],
        ['user_id' => 3, 'export_type' => 'csv', 'count' => 200, 'file_path' => '/exports/hot_leads_20260503.csv'],
    ],
    'mlm_settings' => [
        ['setting_key' => 'commission_level_1', 'setting_value' => '5'],
        ['setting_key' => 'commission_level_2', 'setting_value' => '3'],
        ['setting_key' => 'commission_level_3', 'setting_value' => '1'],
        ['setting_key' => 'min_payout', 'setting_value' => '500'],
        ['setting_key' => 'max_depth', 'setting_value' => '10'],
        ['setting_key' => 'referral_bonus', 'setting_value' => '200'],
    ],
    'payment_history' => [
        ['payment_id' => 'PAY-001', 'amount' => 25000.00, 'status' => 'completed', 'method' => 'bank_transfer'],
        ['payment_id' => 'PAY-002', 'amount' => 15000.00, 'status' => 'pending', 'method' => 'upi'],
        ['payment_id' => 'PAY-003', 'amount' => 50000.00, 'status' => 'completed', 'method' => 'cheque'],
        ['payment_id' => 'PAY-004', 'amount' => 10000.00, 'status' => 'failed', 'method' => 'credit_card'],
    ],
    'property_feature_mappings' => [
        ['property_id' => 1, 'feature_id' => 1],
        ['property_id' => 1, 'feature_id' => 2],
        ['property_id' => 1, 'feature_id' => 3],
        ['property_id' => 2, 'feature_id' => 1],
        ['property_id' => 2, 'feature_id' => 4],
        ['property_id' => 3, 'feature_id' => 2],
        ['property_id' => 3, 'feature_id' => 5],
    ],
    'crash_reports' => [
        ['error_message' => 'Undefined variable: index', 'stack_trace' => '#0 /var/www/index.php(12): process()', 'url' => '/properties', 'user_id' => 1],
        ['error_message' => 'Division by zero', 'stack_trace' => '#0 /var/www/calc.php(45): calculateTax()', 'url' => '/user/dashboard', 'user_id' => 2],
    ],
    'performance_reports' => [
        ['report_name' => 'Monthly Lead Conversion', 'date_range' => '{"start":"2026-04-01","end":"2026-04-30"}', 'metrics_included' => '["total_leads","converted","rate"]', 'generated_data' => '{"total_leads":450,"converted":89,"rate":"19.8%"}', 'generated_by' => 1],
        ['report_name' => 'Agent Performance Q2', 'date_range' => '{"start":"2026-04-01","end":"2026-06-30"}', 'metrics_included' => '["active_agents","deals","revenue"]', 'generated_data' => '{"active_agents":12,"total_deals":34,"revenue":8500000}', 'generated_by' => 1],
        ['report_name' => 'Site Visit Summary', 'date_range' => '{"start":"2026-05-01","end":"2026-05-26"}', 'metrics_included' => '["visits","completed","cancelled"]', 'generated_data' => '{"total_visits":156,"completed":134,"cancelled":22}', 'generated_by' => 2],
    ],
    'portfolio_reports' => [
        ['portfolio_id' => 1, 'report_title' => 'Q2 2026 Performance', 'report_period_start' => '2026-04-01', 'report_period_end' => '2026-06-30', 'report_data' => '{"properties":3,"total_invested":4500000,"current_value":5200000,"roi":"15.6%"}', 'generated_by' => 1],
        ['portfolio_id' => 1, 'report_title' => 'Monthly Review May 2026', 'report_period_start' => '2026-05-01', 'report_period_end' => '2026-05-31', 'report_data' => '{"properties":3,"total_invested":4500000,"current_value":5280000,"roi":"17.3%"}', 'generated_by' => 1],
        ['portfolio_id' => 2, 'report_title' => 'Annual Summary 2025-26', 'report_period_start' => '2025-04-01', 'report_period_end' => '2026-03-31', 'report_data' => '{"properties":5,"total_invested":12000000,"current_value":13500000,"roi":"12.5%"}', 'generated_by' => 2],
    ],
    'reports' => [
        ['name' => 'Monthly Sales Report', 'type' => 'sales', 'parameters' => '{"month":"2026-05","format":"pdf","include_charts":true}'],
        ['name' => 'Lead Source Analysis', 'type' => 'leads', 'parameters' => '{"from":"2026-01-01","to":"2026-05-26","group_by":"source"}'],
        ['name' => 'Commission Summary', 'type' => 'commission', 'parameters' => '{"period":"2026-04","status":"paid"}'],
    ],
    'security_logs' => [
        ['event_type' => 'login_failed', 'user_id' => 1, 'ip_address' => '192.168.1.100', 'details' => '{"attempts":3,"reason":"invalid_password"}'],
        ['event_type' => 'password_change', 'user_id' => 2, 'ip_address' => '192.168.1.101', 'details' => '{"changed_by":"user"}'],
        ['event_type' => 'login_success', 'user_id' => 3, 'ip_address' => '10.0.0.55', 'details' => '{"mfa_used":true}'],
        ['event_type' => 'logout', 'user_id' => 1, 'ip_address' => '192.168.1.100', 'details' => '{}'],
    ],
    'admin_user_menu_permissions' => [
        ['user_id' => 1, 'menu_id' => 1, 'can_view' => 1],
        ['user_id' => 1, 'menu_id' => 2, 'can_view' => 1],
        ['user_id' => 1, 'menu_id' => 3, 'can_view' => 0],
        ['user_id' => 2, 'menu_id' => 1, 'can_view' => 1],
        ['user_id' => 2, 'menu_id' => 2, 'can_view' => 0],
    ],
    'analytics_user_behavior' => [
        ['user_id' => 1, 'action_type' => 'page_view', 'action_data' => '{"page":"/properties"}', 'time_on_page' => 45],
        ['user_id' => 1, 'action_type' => 'search', 'action_data' => '{"query":"3 BHK flat","results":12}', 'time_on_page' => 30],
        ['user_id' => 2, 'action_type' => 'property_view', 'action_data' => '{"property_id":1}', 'time_on_page' => 120],
        ['user_id' => 3, 'action_type' => 'inquiry_submit', 'action_data' => '{"property_id":2,"type":"call"}', 'time_on_page' => 90],
        ['user_id' => 1, 'action_type' => 'logout', 'action_data' => '{}', 'time_on_page' => 0],
    ],
    'user_analytics' => [
        ['user_id' => 1, 'page_views' => 45, 'sessions' => 3],
        ['user_id' => 2, 'page_views' => 22, 'sessions' => 1],
        ['user_id' => 3, 'page_views' => 78, 'sessions' => 5],
        ['user_id' => 4, 'page_views' => 15, 'sessions' => 2],
    ],
    'user_behavior_analytics' => [
        ['user_id' => 1, 'behavior_type' => 'high_engagement', 'value' => 85],
        ['user_id' => 2, 'behavior_type' => 'medium_engagement', 'value' => 55],
        ['user_id' => 3, 'behavior_type' => 'low_engagement', 'value' => 20],
        ['user_id' => 4, 'behavior_type' => 'bounce', 'value' => 5],
    ],
    'user_browsing_history' => [
        ['user_id' => 1, 'url' => '/properties', 'title' => 'Properties - APS Dream Home', 'timestamp' => '2026-05-26 10:30:00'],
        ['user_id' => 1, 'url' => '/property/1', 'title' => 'Property Detail - 3 BHK Villa', 'timestamp' => '2026-05-26 10:32:00'],
        ['user_id' => 2, 'url' => '/user/dashboard', 'title' => 'Dashboard - APS Dream Home', 'timestamp' => '2026-05-26 11:00:00'],
        ['user_id' => 3, 'url' => '/list-property', 'title' => 'List Property - APS Dream Home', 'timestamp' => '2026-05-26 12:00:00'],
        ['user_id' => 3, 'url' => '/services', 'title' => 'Services - APS Dream Home', 'timestamp' => '2026-05-26 12:10:00'],
    ],
    'user_dashboard_configs' => [
        ['user_id' => 1, 'user_type' => 'associate', 'config_name' => 'default', 'widgets_configuration' => '{"widgets":[{"id":"recent_properties","position":1},{"id":"inquiries","position":2},{"id":"bookings","position":3}]}', 'layout_settings' => '{"columns":2,"theme":"light"}'],
        ['user_id' => 2, 'user_type' => 'employee', 'config_name' => 'default', 'widgets_configuration' => '{"widgets":[{"id":"my_properties","position":1},{"id":"recent_activity","position":2}]}', 'layout_settings' => '{"columns":1,"theme":"light"}'],
    ],
    'user_permissions' => [
        ['user_id' => 1, 'permission' => 'properties.create', 'granted' => 1],
        ['user_id' => 1, 'permission' => 'properties.edit', 'granted' => 1],
        ['user_id' => 1, 'permission' => 'properties.delete', 'granted' => 0],
        ['user_id' => 2, 'permission' => 'properties.create', 'granted' => 1],
        ['user_id' => 2, 'permission' => 'inquiries.view', 'granted' => 1],
        ['user_id' => 3, 'permission' => 'properties.view', 'granted' => 1],
    ],
    'user_search_history' => [
        ['user_id' => 1, 'query' => '3 BHK flat near Gorakhpur', 'results_count' => 12],
        ['user_id' => 1, 'query' => 'plots under 30 lakhs', 'results_count' => 5],
        ['user_id' => 2, 'query' => 'house for rent Lucknow', 'results_count' => 8],
        ['user_id' => 3, 'query' => 'commercial property Varanasi', 'results_count' => 3],
    ],
];

// ─── Attempt all variants for each table (column names don't match) ──────────
// Since column names in DB may differ from the hint, we build a column map
// and then try to match our hints to real columns.

/**
 * Try to match a hint column name to actual DB column using substring matching.
 * Returns null if no match found.
 */
function matchColumnHint(string $hint, array $actualCols): ?string
{
    // 1. exact match
    if (in_array($hint, $actualCols)) return $hint;
    // 2. case-insensitive
    foreach ($actualCols as $a) {
        if (strcasecmp($a, $hint) === 0) return $a;
    }
    // 3. hint is a substring of actual or vice versa
    foreach ($actualCols as $a) {
        if (stripos($a, $hint) !== false || stripos($hint, $a) !== false) return $a;
    }
    // 4. common variant mappings
    $map = [
        'payment_id'       => ['transaction_id'],
        'status'           => ['payment_status'],
        'method'           => ['payment_method'],
        'menu_id'          => ['menu_item_id'],
        'menu_item_id'     => ['menu_id'],
        'permission_key'   => ['permission'],
        'permission'       => ['permission_key'],
        'score'            => ['metric_value'],
        'rank'             => ['rank_position'],
        'period'           => ['report_period'],
        'url'              => ['page'],
        'title'            => ['report_title'],
        'timestamp'        => ['created_at', 'visit_time'],
        'action'           => ['action_type'],
        'behavior_type'    => ['user_segment'],
        'value'            => ['metric_value'],
        'widget_config'    => ['widgets_configuration'],
        'event_type'       => ['action'],
        'name'             => ['report_name', 'title'],
        'data'             => ['report_data', 'content', 'generated_data'],
        'layout'           => ['layout_settings'],
        'query'            => ['search_query'],
        'search_query'     => ['query'],
        'count'            => ['lead_count', 'total_count'],
        'export_type'      => ['action'],
        'can_view'         => ['is_active', 'is_default'],
        'granted'          => ['can_view'],
    ];
    foreach ($actualCols as $a) {
        if (isset($map[$hint]) && in_array($a, $map[$hint])) return $a;
    }
    // 5. singular/plural
    $plural = preg_replace('/s$/', '', $hint);
    foreach ($actualCols as $a) {
        if (strcasecmp($a, $plural) === 0) return $a;
    }
    return null;
}

$totalSeeded = 0;
$totalFailed = 0;
$skipped = [];

foreach ($seedData as $table => $rows) {
    echo "──────────────────────────────\n";
    echo "Table: $table\n";

    // Check existence
    $st = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    if ($st->rowCount() == 0) {
        echo "  ⚠ TABLE DOES NOT EXIST - skipping\n";
        $skipped[] = $table;
        continue;
    }

    // Check empty
    $cnt = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    if ($cnt > 0) {
        echo "  ⏭ Already has $cnt rows - skipping\n";
        $skipped[] = $table;
        continue;
    }

    // Get actual columns
    $colDefs = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll();
    $actualCols = array_column($colDefs, 'Field');
    $colTypes = getColumnTypes($pdo, $table);

    echo "  Columns: " . implode(', ', $actualCols) . "\n";

    // Get auto-increment column (skip it)
    $autoIncCol = null;
    foreach ($colDefs as $c) {
        if (stripos($c['Extra'] ?? '', 'auto_increment') !== false) {
            $autoIncCol = $c['Field'];
            break;
        }
    }

    // For each row, try to map hint columns to actual columns
    $inserted = 0;
    $failed = 0;

    // Pre-compute ENUM values for each column
    $enumVals = [];
    foreach ($actualCols as $ac) {
        $ev = getEnumValues($pdo, $table, $ac);
        if ($ev) $enumVals[$ac] = $ev;
    }

    foreach ($rows as $hintRow) {
        try {
            $insertData = [];
            $missingRequired = false;

            foreach ($actualCols as $ac) {
                // Skip auto-increment
                if ($ac === $autoIncCol) continue;

                $info = $colTypes[$ac];
                $isNull = isNullable($info['null']);
                $hasDefault = ($info['default'] !== null);

                // Try to find a value for this column
                $found = false;
                foreach ($hintRow as $hint => $val) {
                    $matched = matchColumnHint($hint, [$ac]);
                    if ($matched !== null) {
                        // Check ENUM constraint
                        if (isset($enumVals[$ac])) {
                            $valStr = (string)$val;
                            if (!in_array($valStr, $enumVals[$ac])) {
                                $val = $enumVals[$ac][0]; // use first valid
                            }
                        }
                        // Check if column is JSON type (for array values)
                        $typeLower = strtolower($info['type']);
                        if (is_array($val)) {
                            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                        }

                        $insertData[$ac] = $val;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    // Column not in our hints — use default or null
                    if ($hasDefault) {
                        // Use DEFAULT — skip in INSERT
                        continue;
                    } elseif ($isNull) {
                        $insertData[$ac] = null;
                    } elseif ($autoIncCol !== $ac) {
                        // Required column with no value — try first enum or 0/empty
                        if (isset($enumVals[$ac])) {
                            $insertData[$ac] = $enumVals[$ac][0];
                        } else {
                            // Try a sensible default
                            $typeLower = strtolower($info['type']);
                            if (str_contains($typeLower, 'int')) {
                                $insertData[$ac] = 0;
                            } elseif (str_contains($typeLower, 'decimal') || str_contains($typeLower, 'float') || str_contains($typeLower, 'double')) {
                                $insertData[$ac] = 0.00;
                            } elseif (str_contains($typeLower, 'json')) {
                                $insertData[$ac] = '{}';
                            } else {
                                $insertData[$ac] = '';
                            }
                        }
                    }
                }
            }

            if (empty($insertData)) {
                echo "    ⚠ No data to insert for row\n";
                $failed++;
                continue;
            }

            // Build INSERT
            $cols = array_keys($insertData);
            $placeholders = [];
            foreach ($cols as $c) { $placeholders[] = ':' . $c; }
            $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            foreach ($insertData as $k => $v) {
                $paramType = is_int($v) ? PDO::PARAM_INT : (is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':' . $k, $v, $paramType);
            }
            $stmt->execute();
            $inserted++;
        } catch (Exception $e) {
            echo "    ✗ Row insert failed: " . $e->getMessage() . "\n";
            $failed++;
        }
    }

    if ($inserted > 0) {
        echo "  ✅ Inserted $inserted row(s)\n";
        $totalSeeded += $inserted;
    }
    if ($failed > 0) {
        echo "  ❌ $failed row(s) failed\n";
        $totalFailed += $failed;
    }
}

echo "\n══════════════════════════════\n";
echo "Summary:\n";
echo "  Tables attempted: " . count($seedData) . "\n";
echo "  Skipped (exists/has data): " . count($skipped) . "\n";
echo "  Rows seeded: $totalSeeded\n";
echo "  Rows failed: $totalFailed\n";
echo "══════════════════════════════\n";
