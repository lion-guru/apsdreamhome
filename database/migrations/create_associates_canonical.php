<?php
/**
 * Migration: Associates Table — Canonical Schema
 * ──────────────────────────────────────────────
 * Single source of truth for the associates table.
 * Captures the actual production schema as of 2026-09-04.
 *
 * 35 columns, 65 active rows, 2 FK constraints (mlm_payouts, mlm_rank_history).
 *
 * Run via: php database/migrations/create_associates_canonical.php
 *
 * Columns:
 *   Core:       id, tenant_id, user_id, name, email, phone, level, referral_code, sponsor_id, status
 *   Agent:      agent_track, agent_type, brokerage_model, brokerage_rate
 *   Telecaller: telecaller_salary, telecaller_incentive_rate, telecaller_sqft_rate, telecaller_parent_id
 *   Metrics:    total_sales, commission_earned, registration_count, required_registrations
 *   Salary:     salary_eligible, salary_amount, target_bonus_eligible, target_bonus_amount
 *   Team:       team_size, joining_date
 *   KYC:        address, pan_number, aadhaar_number, bank_account, bank_ifsc
 *   Timestamps: created_at, updated_at
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $password = $config['password'] ?: (getenv('DB_PASS') ?: '');
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    // Check current state
    $cols = $pdo->query("SHOW COLUMNS FROM associates")->fetchAll(PDO::FETCH_COLUMN);
    $currentCount = $pdo->query("SELECT COUNT(*) FROM associates")->fetchColumn();
    echo "Current: " . count($cols) . " columns, {$currentCount} rows\n";

    // Verify expected columns exist (don't alter — this is a verification migration)
    $expected = [
        'id', 'tenant_id', 'user_id', 'name', 'email', 'phone', 'level',
        'referral_code', 'sponsor_id', 'status',
        'agent_track', 'agent_type', 'brokerage_model', 'brokerage_rate',
        'telecaller_salary', 'telecaller_incentive_rate', 'telecaller_sqft_rate', 'telecaller_parent_id',
        'total_sales', 'commission_earned', 'registration_count', 'required_registrations',
        'salary_eligible', 'salary_amount', 'target_bonus_eligible', 'target_bonus_amount',
        'team_size', 'joining_date',
        'address', 'pan_number', 'aadhaar_number', 'bank_account', 'bank_ifsc',
        'created_at', 'updated_at'
    ];

    $missing = array_diff($expected, $cols);
    $extra   = array_diff($cols, $expected);

    if (!empty($missing)) {
        echo "⚠️  Missing columns (expected but not in DB): " . implode(', ', $missing) . "\n";
        echo "   These may have been removed intentionally. Check code before re-adding.\n";
    }

    if (!empty($extra)) {
        echo "ℹ️  Extra columns (in DB but not in expected list): " . implode(', ', $extra) . "\n";
        echo "   These are legitimate additions. Update this migration if they should be canonical.\n";
    }

    if (empty($missing) && empty($extra)) {
        echo "✅ Schema matches canonical definition — all 35 columns present.\n";
    }

    // Verify FK constraints
    $fks = $pdo->query("
        SELECT TABLE_NAME, CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'associates' 
        AND TABLE_SCHEMA = '{$config['database']}'
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "\nFK constraints referencing associates: " . count($fks) . "\n";
    foreach ($fks as $fk) {
        echo "  - {$fk['TABLE_NAME']}.{$fk['CONSTRAINT_NAME']}\n";
    }

    // Verify sub-tables exist
    foreach (['associate_downline_rates', 'salaried_agent_structures'] as $t) {
        $r = $pdo->query("SHOW TABLES LIKE '{$t}'");
        echo "\n{$t}: " . ($r->rowCount() ? 'EXISTS ✅' : 'MISSING ❌') . "\n";
    }

    echo "\n✅ Canonical migration verification complete.\n";
    echo "   Do NOT re-run old migrations from _archive/migrations/associates_churn/\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
