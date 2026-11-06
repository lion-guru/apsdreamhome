<?php

/**
 * Migration: Migrate identity data from legacy tables to profile tables
 *
 * Migrates data from:
 * - associates → associate_profiles
 * - agents → agent_profiles
 * - employees → employee_profiles
 * - users (role='customer') → customer_profiles
 *
 * Idempotent: skips rows already migrated.
 *
 * Run via: php database/migrations/migrate_identity_to_profiles_s387.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$migrated = 0;
$skipped = 0;
$errors = 0;

function alreadyMigrated(PDO $pdo, string $profileTable, int $userId): bool {
    $row = $pdo->prepare("SELECT 1 FROM {$profileTable} WHERE user_id = ? LIMIT 1");
    $row->execute([$userId]);
    return $row->fetch() !== false;
}

// ─────────────────────────────────────────────────────────
// Migrate associates → associate_profiles
// ─────────────────────────────────────────────────────────
echo "━━━ associates → associate_profiles ━━━\n";

$stmt = $pdo->query("SELECT * FROM associates WHERE user_id IS NOT NULL");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($rows) . " associates to migrate\n";

foreach ($rows as $row) {
    if (alreadyMigrated($pdo, 'associate_profiles', (int)$row['user_id'])) {
        $skipped++;
        continue;
    }
    try {
        $pdo->prepare("
            INSERT IGNORE INTO associate_profiles (
                user_id, level, status, referral_code, sponsor_id, agent_track, agent_type,
                brokerage_model, brokerage_rate, telecaller_salary, telecaller_incentive_rate,
                telecaller_sqft_rate, telecaller_parent_id, total_sales, commission_earned,
                registration_count, required_registrations, salary_eligible, salary_amount,
                target_bonus_eligible, target_bonus_amount, team_size,
                max_leads_capacity, current_leads_count, specializations, preferred_locations,
                preferred_budget_min, preferred_budget_max, performance_score, response_time_avg_hours,
                joining_date, pan_number, aadhaar_number, bank_account, bank_ifsc,
                can_offline_book, can_collect_cash, daily_cash_limit, cash_policy_agreed,
                legal_consent_accepted, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $row['user_id'], $row['level'], $row['status'], $row['referral_code'], $row['sponsor_id'],
            $row['agent_track'], $row['agent_type'], $row['brokerage_model'], $row['brokerage_rate'],
            $row['telecaller_salary'], $row['telecaller_incentive_rate'], $row['telecaller_sqft_rate'],
            $row['telecaller_parent_id'], $row['total_sales'], $row['commission_earned'],
            $row['registration_count'], $row['required_registrations'], $row['salary_eligible'],
            $row['salary_amount'], $row['target_bonus_eligible'], $row['target_bonus_amount'],
            $row['team_size'],
            $row['max_leads_capacity'] ?? 50, $row['current_leads_count'] ?? 0,
            $row['specializations'] ?? '', $row['preferred_locations'] ?? '',
            $row['preferred_budget_min'] ?? 0, $row['preferred_budget_max'] ?? 0,
            $row['performance_score'] ?? 50, $row['response_time_avg_hours'] ?? 0,
            $row['joining_date'], $row['pan_number'], $row['aadhaar_number'],
            $row['bank_account'], $row['bank_ifsc'],
            $row['can_offline_book'], $row['can_collect_cash'], $row['daily_cash_limit'],
            $row['cash_policy_agreed'], $row['legal_consent_accepted'],
            $row['created_at'], $row['updated_at']
        ]);
        $migrated++;
    } catch (\Throwable $e) {
        echo "❌ ERROR migrating associate user_id={$row['user_id']}: " . $e->getMessage() . "\n";
        $errors++;
    }
}
echo "✅ Migrated: $migrated, Skipped: $skipped, Errors: $errors\n";

$migrated = 0;
$skipped = 0;
$errors = 0;

// ─────────────────────────────────────────────────────────
// Migrate agents → agent_profiles
// ─────────────────────────────────────────────────────────
echo "\n━━━ agents → agent_profiles ━━━\n";

$stmt = $pdo->query("SELECT * FROM agents WHERE user_id IS NOT NULL");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($rows) . " agents to migrate\n";

foreach ($rows as $row) {
    if (alreadyMigrated($pdo, 'agent_profiles', (int)$row['user_id'])) {
        $skipped++;
        continue;
    }
    try {
        $pdo->prepare("
            INSERT IGNORE INTO agent_profiles (
                user_id, license_number, experience_years, bank_name,
                account_number, ifsc_code, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $row['user_id'], $row['license_number'], $row['experience_years'],
            $row['bank_name'], $row['account_number'], $row['ifsc_code'],
            $row['created_at'], $row['updated_at']
        ]);
        $migrated++;
    } catch (\Throwable $e) {
        echo "❌ ERROR migrating agent user_id={$row['user_id']}: " . $e->getMessage() . "\n";
        $errors++;
    }
}
echo "✅ Migrated: $migrated, Skipped: $skipped, Errors: $errors\n";

$migrated = 0;
$skipped = 0;
$errors = 0;

// ─────────────────────────────────────────────────────────
// Migrate employees → employee_profiles
// ─────────────────────────────────────────────────────────
echo "\n━━━ employees → employee_profiles ━━━\n";

$stmt = $pdo->query("SELECT * FROM employees WHERE user_id IS NOT NULL");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($rows) . " employees to migrate\n";

foreach ($rows as $row) {
    if (alreadyMigrated($pdo, 'employee_profiles', (int)$row['user_id'])) {
        $skipped++;
        continue;
    }
    try {
        $pdo->prepare("
            INSERT IGNORE INTO employee_profiles (
                user_id, tenant_id, employee_code, role, department, designation,
                joining_date, salary, incentive_model, commission_rate,
                commission_type, offboarded_at, offboard_reason, address,
                emergency_contact, date_of_birth, pan_number, aadhaar_number,
                bank_account, bank_ifsc, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $row['user_id'],
            $row['tenant_id'] ?? 1,
            $row['employee_code'], $row['role'], $row['department'],
            $row['designation'], $row['joining_date'], $row['salary'], $row['incentive_model'],
            $row['commission_rate'], $row['commission_type'], $row['offboarded_at'],
            $row['offboard_reason'], $row['address'], $row['emergency_contact'],
            $row['date_of_birth'], $row['pan_number'], $row['aadhaar_number'],
            $row['bank_account'], $row['bank_ifsc'], $row['created_at'], $row['updated_at']
        ]);
        $migrated++;
    } catch (\Throwable $e) {
        echo "❌ ERROR migrating employee user_id={$row['user_id']}: " . $e->getMessage() . "\n";
        $errors++;
    }
}
echo "✅ Migrated: $migrated, Skipped: $skipped, Errors: $errors\n";

$migrated = 0;
$skipped = 0;
$errors = 0;

// ─────────────────────────────────────────────────────────
// Migrate customers (users with role='customer') → customer_profiles
// ─────────────────────────────────────────────────────────
echo "\n━━━ customers (users.role='customer') → customer_profiles ━━━\n";

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($rows) . " customers to migrate\n";

foreach ($rows as $row) {
    if (alreadyMigrated($pdo, 'customer_profiles', (int)$row['id'])) {
        $skipped++;
        continue;
    }
    try {
        $pdo->prepare("
            INSERT IGNORE INTO customer_profiles (
                user_id, preferred_location, budget_min, budget_max, property_type,
                occupation, annual_income, kyc_status, preferred_contact_method,
                marketing_consent, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $row['id'],
            $row['city'] ?? null,
            null, null,
            null,
            $row['occupation'] ?? null,
            null,
            $row['kyc_status'] ?? 'pending',
            null, null,
            $row['created_at'], $row['updated_at']
        ]);
        $migrated++;
    } catch (\Throwable $e) {
        echo "❌ ERROR migrating customer user_id={$row['id']}: " . $e->getMessage() . "\n";
        $errors++;
    }
}
echo "✅ Migrated: $migrated, Skipped: $skipped, Errors: $errors\n";

echo "\n━━━ Migration Complete ━━━\n";