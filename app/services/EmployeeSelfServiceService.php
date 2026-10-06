<?php
/**
 * EmployeeSelfServiceService
 * Handles all employee self-service operations
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class EmployeeSelfServiceService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /* ═════════════════════════════════════════════════════════════
       TAX REGIME MANAGEMENT
       ═════════════════════════════════════════════════════════════ */

    /**
     * Get employee's tax regime for a financial year
     */
    public function getTaxRegime(int $employeeId, int $financialYear): ?array
    {
        return $this->fetchOne("
            SELECT * FROM employee_tax_regime 
            WHERE employee_id=? AND financial_year=?{$this->tenantSql()}
        ", array_merge([$employeeId, $financialYear], $this->tVal()));
    }

    /**
     * Set/update employee's tax regime
     * regime: 'old' or 'new'
     */
    public function setTaxRegime(int $employeeId, int $financialYear, string $regime): array
    {
        if (!in_array($regime, ['old', 'new'])) {
            return ['success' => false, 'message' => 'Invalid regime. Must be "old" or "new"'];
        }

        $this->pdo->beginTransaction();
        try {
            $tid = $this->tenantId();
            $stmt = $this->pdo->prepare("
                INSERT INTO employee_tax_regime (employee_id, financial_year, regime, created_at, updated_at, tenant_id)
                VALUES (?, ?, ?, NOW(), NOW(), ?)
                ON DUPLICATE KEY UPDATE regime=?, updated_at=NOW()
            ");
            $stmt->execute([$employeeId, $financialYear, $regime, $tid, $regime]);

            $this->pdo->commit();
            return ['success' => true, 'message' => "Tax regime set to {$regime} for FY {$financialYear}"];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /* ═════════════════════════════════════════════════════════════
       INVESTMENT DECLARATION (Sec 80C, 80D, HRA, etc.)
       ═════════════════════════════════════════════════════════════ */

    /**
     * Get investment declaration for employee/year
     */
    public function getInvestmentDeclaration(int $employeeId, int $financialYear): array
    {
        $declarations = $this->fetchAll("
            SELECT * FROM employee_investment_declarations 
            WHERE employee_id=? AND financial_year=?{$this->tenantSql()}
            ORDER BY section, created_at
        ", array_merge([$employeeId, $financialYear], $this->tVal()));

        // Group by section
        $grouped = [];
        foreach ($declarations as $d) {
            $grouped[$d['section']][] = $d;
        }

        return $grouped;
    }

    /**
     * Save investment declaration
     */
    public function saveInvestmentDeclaration(int $employeeId, int $financialYear, array $declarations): array
    {
        $this->pdo->beginTransaction();
        try {
            // Delete existing for this year
            $tid = $this->tenantId();
            $this->execute("DELETE FROM employee_investment_declarations WHERE employee_id=? AND financial_year=?{$this->tenantSql()}", 
                array_merge([$employeeId, $financialYear], $this->tVal()));

            // Insert new declarations
            $stmt = $this->pdo->prepare("
                INSERT INTO employee_investment_declarations 
                (employee_id, financial_year, section, subsection, investment_type, amount, description, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($declarations as $decl) {
                $stmt->execute([
                    $employeeId,
                    $financialYear,
                    $decl['section'] ?? '',      // '80C', '80D', 'HRA', '24b', etc.
                    $decl['subsection'] ?? '',   // 'PPF', 'ELSS', 'Life Insurance', etc.
                    $decl['investment_type'] ?? '',
                    (float)($decl['amount'] ?? 0),
                    $decl['description'] ?? '',
                    $tid
                ]);
            }

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Investment declaration saved'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Upload investment proof document
     */
    public function uploadInvestmentProof(int $employeeId, int $financialYear, string $section, array $fileData): array
    {
        $tid = $this->tenantId();
        // PUBLIC_PATH = webroot so uploaded proofs are actually retrievable.
        $uploadDir = PUBLIC_PATH . '/assets/uploads/investment_proofs/' . $employeeId . '/' . $financialYear . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($fileData['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
            return ['success' => false, 'message' => 'Proof must be JPG, PNG, WebP or PDF'];
        }
        if (($fileData['size'] ?? 0) > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Proof file must be under 5MB'];
        }
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$section) . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($fileData['name']));
        $targetPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
            return ['success' => false, 'message' => 'File upload failed'];
        }

        $relativePath = 'assets/uploads/investment_proofs/' . $employeeId . '/' . $financialYear . '/' . $fileName;

        $this->execute("
            INSERT INTO employee_investment_proofs 
            (employee_id, financial_year, section, file_path, original_name, file_size, tenant_id, uploaded_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), original_name=VALUES(original_name), file_size=VALUES(file_size), uploaded_at=NOW()
        ", [$employeeId, $financialYear, $section, $relativePath, $fileData['name'], $fileData['size'], $tid]);

        return ['success' => true, 'message' => 'Proof uploaded', 'path' => $relativePath];
    }

    /* ═════════════════════════════════════════════════════════════
       FORM 16 DOWNLOAD
       ═════════════════════════════════════════════════════════════ */

    /**
     * Get available Form 16s for employee
     */
    public function getForm16List(int $employeeId): array
    {
        // Canonical id space is employees.id (callers pass users.id).
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];
        return $this->fetchAll("
            SELECT f.*,
                   CASE WHEN f.file_path IS NOT NULL THEN 1 ELSE 0 END as available
            FROM employee_form16 f
            WHERE f.employee_id=?{$this->tenantSql()}
            ORDER BY f.financial_year DESC
        ", array_merge([$emp['id']], $this->tVal()));
    }

    /**
     * Generate Form 16 for employee (calls StatutoryComplianceService)
     */
    public function generateForm16(int $employeeId, int $financialYear): array
    {
        // Callers pass users.id; statutory + PDFs key on employees.id.
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return ['success' => false, 'message' => 'Employee record not found'];
        $eid = (int)$emp['id'];

        $statutory = new \App\Services\StatutoryComplianceService($this->pdo);
        $form16Data = $statutory->generateForm16($eid, $financialYear);

        // Generate PDF
        $pdfService = new \App\Services\PDF\PdfService();
        $result = $pdfService->generate('form16', $eid, ['financial_year' => $financialYear]);

        if (!$result['success']) {
            return ['success' => false, 'message' => 'PDF generation failed: ' . ($result['error'] ?? 'unknown')];
        }

        // Save record (absolute storage path; download streams it, never links it)
        $tid = $this->tenantId();
        $this->execute("
            INSERT INTO employee_form16 (employee_id, financial_year, file_path, generated_at, tenant_id)
            VALUES (?, ?, ?, NOW(), ?)
            ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), generated_at=NOW()
        ", [$eid, $financialYear, $result['data']['path'], $tid]);

        return ['success' => true, 'path' => $result['data']['path']];
    }

    /* ═════════════════════════════════════════════════════════════
       PAYSLIP HISTORY
       ═════════════════════════════════════════════════════════════ */

    public function getPayslipHistory(int $employeeId, int $limit = 24): array
    {
        // Get employee table ID
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        return $this->fetchAll("
            SELECT * FROM employee_payslips 
            WHERE employee_id=? AND status IN ('approved','paid'){$this->tenantSql()}
            ORDER BY period_year DESC, period_month DESC
            LIMIT ?
        ", array_merge([$emp['id']], $this->tVal(), [$limit]));
    }

    public function getPayslipPdf(int $payslipId): ?string
    {
        $payslip = $this->fetchOne("SELECT * FROM employee_payslips WHERE id=?", [$payslipId]);
        if (!$payslip) return null;

        $pdfService = new \App\Services\PDF\PdfService();
        $result = $pdfService->generate('payslip', $payslipId);
        return $result['success'] ? $result['data']['path'] : null;
    }

    /* ═════════════════════════════════════════════════════════════
       LEAVE BALANCE & APPLICATIONS
       ═════════════════════════════════════════════════════════════ */

    public function getLeaveBalances(int $employeeId, int $year = null): array
    {
        $year = $year ?? date('Y');
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        return $this->fetchAll("
            SELECT lb.*, lt.name as leave_type_name, lt.code as leave_type_code, lt.color
            FROM employee_leave_balances lb
            LEFT JOIN leave_types lt ON lb.leave_type_id = lt.id
            WHERE lb.employee_id=? AND lb.year=?{$this->tenantSql()}
            ORDER BY lt.name
        ", array_merge([$emp['id'], $year], $this->tVal()));
    }

    public function applyLeave(int $employeeId, array $data): array
    {
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return ['success' => false, 'message' => 'Employee record not found'];

        $startDate = $data['start_date'] ?? '';
        $endDate = $data['end_date'] ?? '';
        if (!$startDate || !$endDate) return ['success' => false, 'message' => 'Dates required'];

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $totalDays = $start->diff($end)->days + 1;

        // Check balance
        $leaveTypeId = (int)($data['leave_type_id'] ?? 0);
        $balance = $this->fetchOne("
            SELECT remaining_days FROM employee_leave_balances 
            WHERE employee_id=? AND leave_type_id=? AND year=YEAR(?)
        ", [$emp['id'], $leaveTypeId, $startDate]);

        if ($balance && (float)$balance['remaining_days'] < $totalDays) {
            return ['success' => false, 'message' => 'Insufficient leave balance. Available: ' . $balance['remaining_days'] . ' days'];
        }

        $tid = $this->tenantId();
        $this->execute("
            INSERT INTO employee_leaves 
            (employee_id, leave_type_id, start_date, end_date, total_days, reason, status, tenant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ", [$emp['id'], $leaveTypeId, $startDate, $endDate, $totalDays, $data['reason'] ?? '', $tid]);

        return ['success' => true, 'message' => 'Leave application submitted'];
    }

    public function getLeaveHistory(int $employeeId, int $limit = 50): array
    {
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        return $this->fetchAll("
            SELECT el.*, lt.name as leave_type_name, lt.color
            FROM employee_leaves el
            LEFT JOIN leave_types lt ON el.leave_type_id = lt.id
            WHERE el.employee_id=?{$this->tenantSql()}
            ORDER BY el.created_at DESC
            LIMIT ?
        ", array_merge([$emp['id']], $this->tVal(), [$limit]));
    }

    /* ═════════════════════════════════════════════════════════════
       REIMBURSEMENT CLAIMS
       ═════════════════════════════════════════════════════════════ */

    public function submitReimbursement(int $employeeId, array $data): array
    {
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return ['success' => false, 'message' => 'Employee record not found'];

        $tid = $this->tenantId();
        $filePath = '';
        
        if (!empty($data['receipt_file']['tmp_name'])) {
            // PUBLIC_PATH = webroot: admin inbox links BASE_URL + this path.
            $uploadDir = PUBLIC_PATH . '/assets/uploads/reimbursements/' . $employeeId . '/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($data['receipt_file']['name'] ?? '', PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!in_array($ext, $allowed, true)) {
                return ['success' => false, 'message' => 'Receipt must be JPG, PNG, WebP or PDF'];
            }
            if (($data['receipt_file']['size'] ?? 0) > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Receipt file must be under 5MB'];
            }
            $fileName = 'reimb_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($data['receipt_file']['name']));
            if (move_uploaded_file($data['receipt_file']['tmp_name'], $uploadDir . $fileName)) {
                $filePath = 'assets/uploads/reimbursements/' . $employeeId . '/' . $fileName;
            }
        }

        $this->execute("
            INSERT INTO employee_reimbursements 
            (employee_id, claim_type, amount, description, expense_date, receipt_path, status, tenant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ", [$emp['id'], $data['claim_type'] ?? 'other', (float)($data['amount'] ?? 0), 
           $data['description'] ?? '', $data['expense_date'] ?? date('Y-m-d'), $filePath, $tid]);

        return ['success' => true, 'message' => 'Reimbursement claim submitted'];
    }

    public function getReimbursementHistory(int $employeeId, int $limit = 50): array
    {
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        return $this->fetchAll("
            SELECT * FROM employee_reimbursements 
            WHERE employee_id=?{$this->tenantSql()}
            ORDER BY created_at DESC
            LIMIT ?
        ", array_merge([$emp['id']], $this->tVal(), [$limit]));
    }

    /* ═════════════════════════════════════════════════════════════
       PROFILE MANAGEMENT
       ═════════════════════════════════════════════════════════════ */

    public function updateProfile(int $employeeId, array $data): array
    {
        $allowedFields = ['name', 'email', 'phone', 'address', 'emergency_contact', 'date_of_birth', 'bank_account', 'bank_ifsc', 'pan_number', 'aadhaar_number'];
        $updates = [];
        $params = ['uid' => $employeeId];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($updates)) return ['success' => false, 'message' => 'No valid fields to update'];

        $params['tid'] = $this->tenantId();
        $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at=NOW() WHERE id=:uid AND tenant_id=:tid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        // Also update employees table (dynamic SET — only bound fields)
        $empSets = [];
        $empParams = ['uid' => $employeeId, 'tid' => $this->tenantId()];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $empSets[] = "$field = :$field";
                $empParams[$field] = $data[$field];
            }
        }
        if (!empty($empSets)) {
            $this->execute(
                "UPDATE employees SET " . implode(', ', $empSets) . ", updated_at=NOW() WHERE user_id=:uid AND tenant_id=:tid",
                $empParams
            );
        }

        return ['success' => true, 'message' => 'Profile updated'];
    }

    public function changePassword(int $employeeId, string $currentPassword, string $newPassword): array
    {
        $user = $this->fetchOne("SELECT password FROM users WHERE id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->execute("UPDATE users SET password=?, updated_at=NOW() WHERE id=?{$this->tenantSql()}", 
            array_merge([$hash, $employeeId], $this->tVal()));

        return ['success' => true, 'message' => 'Password changed successfully'];
    }

    /* ═════════════════════════════════════════════════════════════
       ATTENDANCE VIEW
       ═════════════════════════════════════════════════════════════ */

    public function getAttendance(int $employeeId, string $month = null): array
    {
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        $month = $month ?? date('Y-m');
        return $this->fetchAll("
            SELECT attendance_date as date, check_in_time as check_in, check_out_time as check_out, 
                   hours_worked as hours, status, late_minutes, overtime_hours
            FROM employee_attendance 
            WHERE employee_id=? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?{$this->tenantSql()}
            ORDER BY attendance_date DESC
        ", array_merge([$emp['id'], $month], $this->tVal()));
    }

    public function getAttendanceStats(int $employeeId, string $month = null): array
    {
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return [];

        $month = $month ?? date('Y-m');
        $stats = $this->fetchOne("
            SELECT 
                SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status='half_day' THEN 1 ELSE 0 END) as half_day,
                SUM(CASE WHEN status='late' THEN 1 ELSE 0 END) as late,
                SUM(COALESCE(hours_worked,0)) as total_hours,
                SUM(COALESCE(overtime_hours,0)) as total_overtime
            FROM employee_attendance 
            WHERE employee_id=? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?{$this->tenantSql()}
        ", array_merge([$emp['id'], $month], $this->tVal()));

        return $stats ?? ['present'=>0,'absent'=>0,'half_day'=>0,'late'=>0,'total_hours'=>0,'total_overtime'=>0];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('EmployeeSelfServiceService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeSelfServiceService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('EmployeeSelfServiceService::execute: ' . $e->getMessage());
            return 0;
        }
    }
}