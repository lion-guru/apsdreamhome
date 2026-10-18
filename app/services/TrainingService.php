<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class TrainingService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== TRAINING COURSES ====================

    public function getCourses(array $filters = []): array
    {
        $where = [];
        $params = [$this->tenantId()];

        if (!empty($filters['category'])) {
            $where[] = 'tc.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['is_active'])) {
            $where[] = 'tc.is_active = ?';
            $params[] = (int)$filters['is_active'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(tc.title LIKE ? OR tc.code LIKE ? OR tc.description LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT tc.*, 
                    (SELECT COUNT(*) FROM course_assignments ca WHERE ca.course_id = tc.id AND ca.tenant_id = ?) as total_assigned,
                    (SELECT COUNT(*) FROM course_assignments ca WHERE ca.course_id = tc.id AND ca.tenant_id = ? AND ca.status = 'completed') as total_completed
                FROM training_courses tc
                $whereSql
                ORDER BY tc.created_at DESC";

        $params[] = $this->tenantId();
        $params[] = $this->tenantId();

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourse(int $courseId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM training_courses WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$courseId, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createCourse(array $data): array
    {
        $required = ['code', 'title'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("$field is required");
            }
        }

        $columns = array_merge(
            ['code', 'title', 'description', 'category', 'duration_hours', 'format', 'max_participants', 'prerequisites', 'instructor_name', 'instructor_email', 'material_url', 'assessment_required', 'passing_score', 'valid_for_months', 'certificate_template', 'is_active', 'created_by', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['code'],
            $data['title'],
            $data['description'] ?? null,
            $data['category'] ?? 'other',
            $data['duration_hours'] ?? 0,
            $data['format'] ?? 'online',
            $data['max_participants'] ?? null,
            $data['prerequisites'] ?? null,
            $data['instructor_name'] ?? null,
            $data['instructor_email'] ?? null,
            $data['material_url'] ?? null,
            $data['assessment_required'] ?? false,
            $data['passing_score'] ?? 70,
            $data['valid_for_months'] ?? 12,
            $data['certificate_template'] ?? null,
            $data['is_active'] ?? true,
            $data['created_by'] ?? 1,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO training_courses (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Course created successfully'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Course code already exists'];
            }
            throw $e;
        }
    }

    public function updateCourse(int $courseId, array $data): array
    {
        $allowed = ['title', 'description', 'category', 'duration_hours', 'format', 'max_participants', 'prerequisites', 'instructor_name', 'instructor_email', 'material_url', 'assessment_required', 'passing_score', 'valid_for_months', 'certificate_template', 'is_active'];
        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        $params[] = $courseId;
        $params[] = $this->tenantId();

        $sql = "UPDATE training_courses SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Course updated' : 'Course not found'];
    }

    public function deleteCourse(int $courseId): array
    {
        // Check if course has assignments
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM course_assignments WHERE course_id = ? AND tenant_id = ?');
        $stmt->execute([$courseId, $this->tenantId()]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return ['success' => false, 'message' => "Cannot delete: $count assignments exist for this course"];
        }

        $stmt = $this->conn->prepare('DELETE FROM training_courses WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$courseId, $this->tenantId()]);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Course deleted' : 'Course not found'];
    }

    public function getCategories(): array
    {
        return [
            'technical' => 'Technical',
            'soft_skills' => 'Soft Skills',
            'compliance' => 'Compliance',
            'leadership' => 'Leadership',
            'safety' => 'Safety',
            'onboarding' => 'Onboarding',
            'other' => 'Other'
        ];
    }

    // ==================== COURSE ASSIGNMENTS ====================

    public function getAssignments(array $filters = []): array
    {
        $where = ['ca.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['course_id'])) {
            $where[] = 'ca.course_id = ?';
            $params[] = $filters['course_id'];
        }
        if (!empty($filters['employee_id'])) {
            $where[] = 'ca.employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'ca.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['assigned_by'])) {
            $where[] = 'ca.assigned_by = ?';
            $params[] = $filters['assigned_by'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT ca.*, tc.title as course_title, tc.code as course_code, tc.category as course_category,
                    u.name as employee_name, u.email as employee_email,
                    a.name as assigned_by_name
                FROM course_assignments ca
                JOIN training_courses tc ON ca.course_id = tc.id
                JOIN employees e ON ca.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                LEFT JOIN users a ON ca.assigned_by = a.id
                $whereSql
                ORDER BY ca.assigned_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeAssignments(int $employeeId): array
    {
        $stmt = $this->conn->prepare("
            SELECT ca.*, tc.title as course_title, tc.code as course_code, tc.category, tc.duration_hours, tc.format,
                   tc.material_url, tc.assessment_required, tc.passing_score
            FROM course_assignments ca
            JOIN training_courses tc ON ca.course_id = tc.id
            WHERE ca.employee_id = ? AND ca.tenant_id = ?
            ORDER BY ca.assigned_at DESC
        ");
        $stmt->execute([$employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignment(int $assignmentId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT ca.*, tc.title as course_title, tc.code as course_code
            FROM course_assignments ca
            JOIN training_courses tc ON ca.course_id = tc.id
            WHERE ca.id = ? AND ca.tenant_id = ?
        ");
        $stmt->execute([$assignmentId, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function assignCourse(array $data): array
    {
        $required = ['course_id', 'employee_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("$field is required");
            }
        }

        // Check if already assigned
        $stmt = $this->conn->prepare('SELECT id FROM course_assignments WHERE course_id = ? AND employee_id = ? AND tenant_id = ?');
        $stmt->execute([$data['course_id'], $data['employee_id'], $this->tenantId()]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Employee already assigned to this course'];
        }

        $columns = array_merge(
            ['course_id', 'employee_id', 'assigned_by', 'due_date', 'assigned_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['course_id'],
            $data['employee_id'],
            $data['assigned_by'] ?? 1,
            $data['due_date'] ?? null,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO course_assignments (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Course assigned successfully'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Employee already assigned to this course'];
            }
            throw $e;
        }
    }

    public function updateAssignmentProgress(int $assignmentId, array $data): array
    {
        $allowed = ['progress', 'score', 'status', 'started_at', 'completed_at', 'feedback', 'rating'];
        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        // Auto-update status based on progress
        if (isset($data['progress'])) {
            if ($data['progress'] >= 100) {
                $fields[] = "status = 'completed'";
                $fields[] = "completed_at = NOW()";
            } elseif ($data['progress'] > 0) {
                $fields[] = "status = 'in_progress'";
                $fields[] = "started_at = COALESCE(started_at, NOW())";
            }
        }

        $params[] = $assignmentId;
        $params[] = $this->tenantId();

        $sql = "UPDATE course_assignments SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Progress updated' : 'Assignment not found'];
    }

    public function issueCertificate(int $assignmentId, array $data): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM course_assignments WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$assignmentId, $this->tenantId()]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment) {
            return ['success' => false, 'message' => 'Assignment not found'];
        }

        if ($assignment['status'] !== 'completed') {
            return ['success' => false, 'message' => 'Cannot issue certificate: course not completed'];
        }

        $certNumber = 'CERT-' . date('Y') . '-' . str_pad($assignmentId, 6, '0', STR_PAD_LEFT);

        $fields = ['certificate_issued = 1', 'certificate_number = ?', 'certificate_issued_at = NOW()'];
        $params = [$certNumber];

        if (!empty($data['certificate_number'])) {
            $certNumber = $data['certificate_number'];
            $params[0] = $certNumber;
        }

        $params[] = $assignmentId;
        $params[] = $this->tenantId();

        $sql = "UPDATE course_assignments SET " . implode(', ', $fields) . " WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'certificate_number' => $certNumber, 'message' => 'Certificate issued'];
    }

    // ==================== CERTIFICATIONS ====================

    public function getEmployeeCertifications(int $employeeId): array
    {
        $stmt = $this->conn->prepare("
            SELECT ec.*, tc.title as course_title
            FROM employee_certifications ec
            LEFT JOIN training_courses tc ON ec.course_id = tc.id
            WHERE ec.employee_id = ? AND ec.tenant_id = ?
            ORDER BY ec.issue_date DESC
        ");
        $stmt->execute([$employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCertification(array $data): array
    {
        $required = ['employee_id', 'certification_name', 'issue_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("$field is required");
            }
        }

        $columns = array_merge(
            ['employee_id', 'course_id', 'certification_name', 'issuing_body', 'issue_date', 'expiry_date', 'credential_id', 'credential_url', 'status', 'attachment_path', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['employee_id'],
            $data['course_id'] ?? null,
            $data['certification_name'],
            $data['issuing_body'] ?? null,
            $data['issue_date'],
            $data['expiry_date'] ?? null,
            $data['credential_id'] ?? null,
            $data['credential_url'] ?? null,
            $data['status'] ?? 'active',
            $data['attachment_path'] ?? null,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO employee_certifications (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Certification added'];
    }

    public function updateCertification(int $certId, array $data): array
    {
        $allowed = ['certification_name', 'issuing_body', 'expiry_date', 'credential_id', 'credential_url', 'status', 'attachment_path'];
        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        $params[] = $certId;
        $params[] = $this->tenantId();

        $sql = "UPDATE employee_certifications SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Certification updated' : 'Not found'];
    }

    public function getExpiringCertifications(int $days = 30): array
    {
        $stmt = $this->conn->prepare("
            SELECT ec.*, u.name as employee_name, u.email as employee_email
            FROM employee_certifications ec
            JOIN employees e ON ec.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE ec.tenant_id = ? 
            AND ec.expiry_date IS NOT NULL 
            AND ec.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND ec.status = 'active'
            ORDER BY ec.expiry_date ASC
        ");
        $stmt->execute([$this->tenantId(), $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== STATS ====================

    public function getStats(): array
    {
        $stats = [];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM training_courses WHERE tenant_id = ? AND is_active = 1');
        $stmt->execute([$this->tenantId()]);
        $stats['total_courses'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM course_assignments WHERE tenant_id = ?');
        $stmt->execute([$this->tenantId()]);
        $stats['total_assignments'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM course_assignments WHERE tenant_id = ? AND status = "completed"');
        $stmt->execute([$this->tenantId()]);
        $stats['completed_assignments'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM course_assignments WHERE tenant_id = ? AND status = "in_progress"');
        $stmt->execute([$this->tenantId()]);
        $stats['in_progress_assignments'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM course_assignments WHERE tenant_id = ? AND status = "overdue"');
        $stmt->execute([$this->tenantId()]);
        $stats['overdue_assignments'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM employee_certifications WHERE tenant_id = ? AND status = "active"');
        $stmt->execute([$this->tenantId()]);
        $stats['active_certifications'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM employee_certifications WHERE tenant_id = ? AND status = "expired"');
        $stmt->execute([$this->tenantId()]);
        $stats['expired_certifications'] = $stmt->fetchColumn();

        $completionRate = $stats['total_assignments'] > 0 
            ? round(($stats['completed_assignments'] / $stats['total_assignments']) * 100, 1) 
            : 0;
        $stats['completion_rate'] = $completionRate;

        return $stats;
    }
}