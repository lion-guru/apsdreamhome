<?php
/**
 * ProgramApplicationController (Admin)
 * Session: 681
 *
 * Admin desk for Student Internship Program + Nari Shakti registrations.
 * List with filters (program_type, status), status transitions, admin notes.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ProgramApplicationController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private const PROGRAMS = ['student', 'nari_shakti'];
    private const STATUSES = ['pending', 'reviewing', 'approved', 'rejected'];

    public function index()
    {
        $this->requireAdmin();

        $program = $_GET['program'] ?? null;
        $status = $_GET['status'] ?? null;
        if ($program !== null && !in_array($program, self::PROGRAMS, true)) {
            $program = null;
        }
        if ($status !== null && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        $where = [];
        $params = [];
        if ($program) {
            $where[] = 'program_type = ?';
            $params[] = $program;
        }
        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenantId();
        $sql = 'SELECT * FROM program_applications WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 200';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $applications = $stmt->fetchAll(\PDO::FETCH_OBJ) ?: [];
        } catch (\Throwable $e) {
            error_log('program-applications: ' . $e->getMessage());
            $applications = [];
        }

        $counts = [];
        try {
            $cStmt = $this->db->prepare(
                "SELECT status, COUNT(*) AS n FROM program_applications WHERE tenant_id = ? GROUP BY status"
            );
            $cStmt->execute([$this->tenantId()]);
            foreach ($cStmt->fetchAll(\PDO::FETCH_OBJ) as $row) {
                $counts[$row->status] = (int)$row->n;
            }
        } catch (\Throwable $e) {
            $counts = [];
        }

        return $this->render('admin/programs/applications', [
            'page_title' => 'Program Applications',
            'page_description' => 'Student Internship Program and Nari Shakti registrations',
            'applications' => $applications,
            'counts' => $counts,
            'program' => $program,
            'status' => $status,
        ]);
    }

    public function updateStatus($id)
    {
        // CSRF is enforced globally by the router for POST; the admin gate runs here.
        $this->requireAdmin();

        $id = (int)$id;
        $newStatus = trim($_POST['status'] ?? '');
        $notes = trim(strip_tags($_POST['admin_notes'] ?? ''));
        if (!in_array($newStatus, self::STATUSES, true)) {
            header('Location: ' . BASE_URL . '/admin/programs/applications?error=invalid');
            return;
        }
        try {
            $stmt = $this->db->prepare(
                "UPDATE program_applications SET status = ?, admin_notes = ?, updated_at = NOW()
                 WHERE id = ? AND tenant_id = ?"
            );
            $stmt->execute([$newStatus, $notes !== '' ? $notes : null, $id, $this->tenantId()]);
            header('Location: ' . BASE_URL . '/admin/programs/applications?saved=1');
            return;
        } catch (\Throwable $e) {
            error_log('program-application-status: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/programs/applications?error=failed');
            return;
        }
    }
}
