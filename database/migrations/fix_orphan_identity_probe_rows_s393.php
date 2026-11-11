<?php
/**
 * Cleanup orphan identity probe rows that point to non-existent users.
 * Session 393 (2026-11-09). Safety dump: backups/db/apsdreamhome_pre-restore_2026-11-09_db-fix.sql
 *
 * Scope is intentionally narrow:
 * - employees.id IN (7,8): obvious Tester probe records; no users row and no employee_profiles bridge.
 * - employee_leave_requests employee_id IN (7,8): only FK child rows for those probes.
 * - associates.id = 465: Probe ClaimSponsor record; no users row and no associate_profiles bridge.
 *
 * Idempotent: after the first run the target rows are gone and every DELETE matches zero rows.
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getConnection();

function cleanup_count(PDO $pdo, string $sql): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return (int)$stmt->rowCount();
}

$pdo->beginTransaction();

try {
    // Child rows first: employee_leave_requests is the only live FK child in the current DB.
    $leaveDeleted = cleanup_count(
        $pdo,
        "DELETE FROM employee_leave_requests WHERE employee_id IN (7, 8)"
    );
    echo "DELETED employee_leave_requests rows={$leaveDeleted}\n";

    // Defensive child cleanup for tables that currently have zero matching rows.
    $attendanceDeleted = cleanup_count(
        $pdo,
        "DELETE FROM employee_attendance WHERE employee_id IN (7, 8)"
    );
    $documentDeleted = cleanup_count(
        $pdo,
        "DELETE FROM employee_documents WHERE employee_id IN (7, 8)"
    );
    $payslipDeleted = cleanup_count(
        $pdo,
        "DELETE FROM employee_payslips WHERE employee_id IN (7, 8)"
    );
    echo "DELETED employee_attendance rows={$attendanceDeleted}\n";
    echo "DELETED employee_documents rows={$documentDeleted}\n";
    echo "DELETED employee_payslips rows={$payslipDeleted}\n";

    $employeeDeleted = cleanup_count(
        $pdo,
        "DELETE e FROM employees e
         LEFT JOIN users u ON u.id = e.user_id
         LEFT JOIN employee_profiles ep ON ep.legacy_id = e.id
         WHERE e.id IN (7, 8)
           AND e.user_id IS NOT NULL
           AND u.id IS NULL
           AND ep.id IS NULL"
    );
    echo "DELETED employees rows={$employeeDeleted}\n";

    $associateDeleted = cleanup_count(
        $pdo,
        "DELETE a FROM associates a
         LEFT JOIN users u ON u.id = a.user_id
         LEFT JOIN associate_profiles ap ON ap.legacy_id = a.id
         WHERE a.id = 465
           AND a.user_id IS NOT NULL
           AND u.id IS NULL
           AND ap.id IS NULL"
    );
    echo "DELETED associates rows={$associateDeleted}\n";

    $remainingEmployees = (int)$pdo->query("
        SELECT COUNT(*)
        FROM employees e
        LEFT JOIN users u ON u.id = e.user_id
        LEFT JOIN employee_profiles ep ON ep.legacy_id = e.id
        WHERE e.user_id IS NOT NULL
          AND (u.id IS NULL OR ep.id IS NULL)
    ")->fetchColumn();

    $remainingAssociates = (int)$pdo->query("
        SELECT COUNT(*)
        FROM associates a
        LEFT JOIN users u ON u.id = a.user_id
        LEFT JOIN associate_profiles ap ON ap.legacy_id = a.id
        WHERE a.user_id IS NOT NULL
          AND (u.id IS NULL OR ap.id IS NULL)
    ")->fetchColumn();

    echo "REMAINING employee identity orphans={$remainingEmployees}\n";
    echo "REMAINING associate identity orphans={$remainingAssociates}\n";

    if ($remainingEmployees !== 0 || $remainingAssociates !== 0) {
        throw new RuntimeException('Identity orphans remain after cleanup; aborting.');
    }

    $pdo->commit();
    echo "OK: identity orphan cleanup complete\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "FAIL: " . $e->getMessage() . "\n";
    exit(1);
}
