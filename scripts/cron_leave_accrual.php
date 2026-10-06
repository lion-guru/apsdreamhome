<?php
/**
 * Cron: Auto-calculate leave balances for all active employees
 * Standalone: php scripts/cron_leave_accrual.php [--tenant=N]
 * Runner: require_once + cron_leave_accrual($pdo, $tenantId)
 */

function cron_leave_accrual($pdo = null, $tenantId = 1)
{
    $tenantId = (int)$tenantId ?: 1;
    if ($pdo === null) {
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getPdo();
    }

    $currentYear = (int)date('Y');
    $currentMonth = (int)date('n');

    // Idempotency guard: re-runs within the same month must not double-accrue.
    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_accrual_log (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        employee_id INT NOT NULL,
        leave_type_id INT NOT NULL,
        year INT NOT NULL,
        month TINYINT NOT NULL,
        days DECIMAL(5,1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_accrual (tenant_id, employee_id, leave_type_id, year, month)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Get leave types with accrual rules
    $leaveTypes = $pdo->query("
        SELECT id, name, code, days_per_year, accrual_frequency
        FROM leave_types
        WHERE status='active' AND accrual_frequency IS NOT NULL
    ")->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($leaveTypes)) {
        return ['accrued' => 0, 'errors' => 0, 'message' => 'No leave types with accrual rules'];
    }

    // Get all active employees
    $empStmt = $pdo->prepare("
        SELECT e.id, e.user_id, e.joining_date, e.department
        FROM employees e
        WHERE e.status='active' AND e.tenant_id=?
    ");
    $empStmt->execute([$tenantId]);
    $employees = $empStmt->fetchAll(\PDO::FETCH_ASSOC);

    $accrued = 0;
    $errors = 0;

    foreach ($employees as $emp) {
        $joiningDate = $emp['joining_date'] ? new DateTime($emp['joining_date']) : new DateTime();
        $now = new DateTime();

        foreach ($leaveTypes as $lt) {
            try {
                // Check if employee is eligible (completed probation etc.)
                if ($joiningDate > $now) continue;

                // Calculate accrual based on frequency
                $daysToAccrue = 0;
                $frequency = $lt['accrual_frequency'] ?? 'monthly';

                switch ($frequency) {
                    case 'monthly':
                        $daysToAccrue = ($lt['days_per_year'] ?? 0) / 12;
                        break;
                    case 'quarterly':
                        if (in_array($currentMonth, [1,4,7,10])) {
                            $daysToAccrue = ($lt['days_per_year'] ?? 0) / 4;
                        }
                        break;
                    case 'half_yearly':
                        if (in_array($currentMonth, [1,7])) {
                            $daysToAccrue = ($lt['days_per_year'] ?? 0) / 2;
                        }
                        break;
                    case 'yearly':
                        if ($currentMonth === 1) {
                            $daysToAccrue = ($lt['days_per_year'] ?? 0);
                        }
                        break;
                }

                if ($daysToAccrue <= 0) continue;

                // Skip if this employee+type already accrued this month (retry-safe).
                $logged = $pdo->prepare("SELECT id FROM leave_accrual_log WHERE tenant_id=? AND employee_id=? AND leave_type_id=? AND year=? AND month=?");
                $logged->execute([$tenantId, $emp['id'], $lt['id'], $currentYear, $currentMonth]);
                if ($logged->fetchColumn()) continue;

                // NOTE: no joining-year discount — each monthly run grants one month's
                // share (days/12); months before joining simply had no runs. The old
                // monthsWorked/12 multiplier under-granted joining-year staff all year.

                // Upsert leave balance
                $stmt = $pdo->prepare("
                    INSERT INTO employee_leave_balances (employee_id, leave_type_id, year, allocated_days, used_days, remaining_days, carried_forward, tenant_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 0, ?, 0, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        allocated_days = allocated_days + VALUES(allocated_days),
                        remaining_days = allocated_days - used_days + carried_forward,
                        updated_at = NOW()
                ");
                $stmt->execute([
                    $emp['id'], $lt['id'], $currentYear,
                    round($daysToAccrue, 1), round($daysToAccrue, 1), $tenantId
                ]);
                $pdo->prepare("INSERT IGNORE INTO leave_accrual_log (tenant_id, employee_id, leave_type_id, year, month, days) VALUES (?,?,?,?,?,?)")->execute([$tenantId, $emp['id'], $lt['id'], $currentYear, $currentMonth, round($daysToAccrue, 1)]);
                $accrued++;
            } catch (\Throwable $e) {
                error_log('cron_leave_accrual: ' . $e->getMessage());
                $errors++;
            }
        }
    }

    return ['accrued' => $accrued, 'errors' => $errors];
}

// CLI entry point (no-op when included by the master runner)
if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    require_once __DIR__ . '/../config/bootstrap.php';
    $cliTenant = 1;
    foreach ($argv ?? [] as $arg) {
        if (preg_match('/^--tenant=(\d+)$/', $arg, $m)) $cliTenant = (int)$m[1];
    }
    try {
        $result = cron_leave_accrual(null, $cliTenant);
        echo "Leave accrual completed: {$result['accrued']} balances updated\n";
        if ($result['errors']) echo "Errors: {$result['errors']}\n";
    } catch (\Exception $e) {
        echo "Failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
