<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;
use InvalidArgumentException;

class TrainingService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==================== TRAINING COURSES ====================

    public function getCourses(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'tc.course_category = ?';
            $params[] = $filters['category'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $where[] = 'tc.is_active = ?';
            $params[] = (int)$filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(tc.course_title LIKE ? OR tc.course_description LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $isActive = (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) ? (int)$filters['is_active'] : 1;
        $whereSql = $where ? ' (' . implode(' AND ', $where) . ')' : ' (1 = 1)';

        $sql = "SELECT tc.*,
                    CONCAT('CRS-', tc.id) AS code,
                    tc.course_title AS title,
                    tc.course_description AS description,
                    tc.course_category AS category,
                    'offline' AS format,
                    tc.course_duration_hours AS duration_hours,
                    (SELECT COUNT(*) FROM course_assignments ca WHERE ca.course_id = tc.id AND ca.tenant_id = ?) as total_assigned,
                    (SELECT COUNT(*) FROM course_assignments ca WHERE ca.course_id = tc.id AND ca.tenant_id = ? AND ca.status = 'completed') as total_completed
                FROM training_courses tc
                WHERE tc.is_active = $isActive AND $whereSql
                ORDER BY tc.created_at DESC";

        array_unshift($params, $this->tenantId(), $this->tenantId());
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getStats(): array
    {
        $total = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM training_courses WHERE is_active = 1") ?: 0);
        $assigned = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM course_assignments") ?: 0);
        $completed = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM course_assignments WHERE status = 'completed'") ?: 0);
        return [
            'total_courses' => $total,
            'total_assignments' => $assigned,
            'total_assigned' => $assigned,
            'completed_assignments' => $completed,
            'total_completed' => $completed,
            'completion_rate' => $assigned > 0 ? round($completed / $assigned * 100, 1) : 0,
        ];
    }

    public function getCategories(): array
    {
        $map = [
            'sales' => 'Sales',
            'product' => 'Product',
            'compliance' => 'Compliance',
            'leadership' => 'Leadership',
            'technical' => 'Technical',
            'business' => 'Business',
        ];
        try {
            $rows = $this->db->fetchAll(
                "SELECT DISTINCT course_category FROM training_courses WHERE course_category IS NOT NULL AND course_category != '' ORDER BY course_category"
            ) ?: [];
            foreach ($rows as $r) {
                $v = $r['course_category'] ?? null;
                if ($v !== null) $map[$v] = ucwords(str_replace('_', ' ', $v));
            }
        } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }
        return $map;
    }

    public function createCourse(array $data): int
    {
        $tid = $this->tenantId();
        $data['tenant_id'] = $tid;
        return $this->db->insert('training_courses', $data);
    }

    public function updateCourse(int $id, array $data): bool
    {
        $this->db->update('training_courses', $data, ['id' => $id, 'tenant_id' => $this->tenantId()]);
        return true;
    }

    public function deleteCourse(int $id): bool
    {
        $this->db->delete('training_courses', ['id' => $id, 'tenant_id' => $this->tenantId()]);
        return true;
    }

    public function getCourse(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM training_courses WHERE id = ? AND tenant_id = ?",
            [$id, $this->tenantId()]
        );
    }

    // ==================== COURSE ASSIGNMENTS ====================

    /**
     * Certifications held by an employee (training_certifications).
     *
     * NOTE (S451): employee/self-service/certifications and
     * admin/employee-certifications called methods that never existed ->
     * "Call to undefined method TrainingService::getEmployeeCertifications()".
     */
    public function getEmployeeCertifications(int $employeeId): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare(
                "SELECT tc.*, tc.course_id, c.title as course_title
                 FROM training_certifications tc
                 LEFT JOIN training_courses c ON c.id = tc.course_id
                 WHERE tc.employee_id = ?" . ($tid > 1 ? " AND tc.tenant_id = ?" : "") . "
                 ORDER BY tc.issued_at DESC"
            );
            $stmt->execute($tid > 1 ? [$employeeId, $tid] : [$employeeId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[TrainingService::getEmployeeCertifications] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Course assignments (course_assignments), optionally for one employee.
     *
     * NOTE (S451): admin/training/assignments called getAssignments(), which
     * never existed.
     */
    public function getAssignments($filters = [], int $limit = 100): array
    {
        try {
            // NOTE (S451): callers pass a FILTER ARRAY here (admin/training/
            // assignments), not an employee id. Accept both.
            $employeeId = is_array($filters) ? (int)($filters['employee_id'] ?? 0) : (int)$filters;
            $tid = $this->tenantId();
            $where = ['1=1'];
            $params = [];
            if ($employeeId > 0) { $where[] = 'ca.employee_id = ?'; $params[] = $employeeId; }
            if (is_array($filters)) {
                if (!empty($filters['status'])) { $where[] = 'ca.status = ?'; $params[] = $filters['status']; }
                if (!empty($filters['course_id'])) { $where[] = 'ca.course_id = ?'; $params[] = (int)$filters['course_id']; }
            }
            if ($tid > 1) { $where[] = 'ca.tenant_id = ?'; $params[] = $tid; }
            $stmt = $this->db->prepare(
                "SELECT ca.*, c.title as course_title, e.name as employee_name
                 FROM course_assignments ca
                 LEFT JOIN training_courses c ON c.id = ca.course_id
                 LEFT JOIN employees e ON e.id = ca.employee_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY ca.assigned_at DESC LIMIT " . (int)$limit
            );
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[TrainingService::getAssignments] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Certifications expiring within N days (training_certifications).
     *
     * NOTE (S451): admin/training/certifications called
     * getExpiringCertifications(), which never existed.
     */
    public function getExpiringCertifications(int $days = 30, int $limit = 100): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare(
                "SELECT tc.*, tc.course_id, c.title as course_title, e.name as employee_name
                 FROM training_certifications tc
                 LEFT JOIN training_courses c ON c.id = tc.course_id
                 LEFT JOIN employees e ON e.id = tc.employee_id
                 WHERE tc.expires_at IS NOT NULL
                   AND tc.expires_at <= DATE_ADD(NOW(), INTERVAL " . (int)$days . " DAY)
                   AND (tc.status IS NULL OR tc.status <> 'expired')"
                . ($tid > 1 ? " AND tc.tenant_id = ?" : "") . "
                 ORDER BY tc.expires_at ASC LIMIT " . (int)$limit
            );
            $stmt->execute($tid > 1 ? [$tid] : []);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[TrainingService::getExpiringCertifications] ' . $e->getMessage());
            return [];
        }
    }

    public function getEmployeeAssignments(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT ca.*, tc.course_title as course_title, tc.course_category as course_category, tc.course_duration_hours as duration_hours
            FROM course_assignments ca
            JOIN training_courses tc ON ca.course_id = tc.id
            WHERE ca.employee_id = ? AND ca.tenant_id = ?
            ORDER BY ca.assigned_at DESC
        ", [$employeeId, $this->tenantId()]) ?: [];
    }

        public function assignCourse(int $employeeId, int $courseId, array $data = []): int
    {
        $tid = $this->tenantId();
        $data['tenant_id'] = $tid;
        $data['employee_id'] = $employeeId;
        $data['course_id'] = $courseId;
        $data['assigned_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'assigned';
        return (int)$this->db->insert('course_assignments', $data);
    }

    public function updateAssignmentProgress(int $assignmentId, array $data): bool
    {
        $this->db->update('course_assignments', $data, ['id' => $assignmentId, 'tenant_id' => $this->tenantId()]);
        return true;
    }

    // ==================== COURSE CONTENT ====================

    public function addCourseContent(int $courseId, array $data): int
    {
        $data['tenant_id'] = $this->tenantId();
        $data['course_id'] = $courseId;
        return (int)$this->db->insert('training_course_content', $data);
    }

    public function getCourseContent(int $courseId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM training_course_content WHERE course_id = ? AND tenant_id = ? ORDER BY module_order, content_order",
            [$courseId, $this->tenantId()]
        ) ?: [];
    }

    public function updateContent(int $contentId, array $data): bool
    {
        $this->db->update('training_course_content', $data, ['id' => $contentId, 'tenant_id' => $this->tenantId()]);
        return true;
    }

    public function deleteContent(int $contentId): bool
    {
        $this->db->delete('training_course_content', ['id' => $contentId, 'tenant_id' => $this->tenantId()]);
        return true;
    }

    // ==================== ASSESSMENTS ====================

    public function addAssessment(int $courseId, array $data): int
    {
        $data['tenant_id'] = $this->tenantId();
        $data['course_id'] = $courseId;
        return (int)$this->db->insert('training_assessments', $data);
    }

    public function getAssessments(int $courseId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM training_assessments WHERE course_id = ? AND tenant_id = ? ORDER BY order_index",
            [$courseId, $this->tenantId()]
        ) ?: [];
    }

    // ==================== EMPLOYEE ASSIGNMENTS ====================

        public function getAssignment(int $assignmentId): ?array
    {
        return $this->db->fetchOne("
            SELECT ca.*, tc.course_title as course_title, tc.course_category as course_category
            FROM course_assignments ca
            JOIN training_courses tc ON ca.course_id = tc.id
            WHERE ca.id = ? AND ca.tenant_id = ?
        ", [$assignmentId, $this->tenantId()]);
    }

    public function getCourseEnrollments(int $courseId): array
    {
        return $this->db->fetchAll(
            "SELECT ca.*, u.name as employee_name, ep.employee_code
             FROM course_assignments ca
             LEFT JOIN employee_profiles ep ON ep.legacy_id = ca.employee_id OR ep.user_id = ca.employee_id OR ep.id = ca.employee_id
             LEFT JOIN users u ON u.id = ep.user_id
             WHERE ca.course_id = ? AND ca.tenant_id = ?
             ORDER BY ca.assigned_at DESC",
            [$courseId, $this->tenantId()]
        ) ?: [];
    }

    public function getCourseStats(int $courseId): array
    {
        return $this->db->fetchOne("
            SELECT 
                COUNT(ca.id) as total_assigned,
                SUM(CASE WHEN ca.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN ca.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN ca.status = 'failed' THEN 1 ELSE 0 END) as failed,
                AVG(ca.progress_percentage) as avg_progress,
                AVG(ca.score) as avg_score
            FROM course_assignments ca
            WHERE ca.course_id = ? AND ca.tenant_id = ?
        ", [$courseId, $this->tenantId()]) ?: [
            'total_assigned' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'failed' => 0,
            'avg_progress' => 0,
            'avg_score' => 0
        ];
    }

    // ==================== COURSE CONTENT PROGRESS ====================

    public function getContentProgress(int $employeeId, int $courseId): array
    {
        return $this->db->fetchAll("
            SELECT ccc.*, 
                   CASE WHEN ecp.completed_at IS NOT NULL THEN 1 ELSE 0 END as completed,
                   ecp.completed_at
            FROM training_course_content ccc
            LEFT JOIN employee_content_progress ecp 
                ON ecp.content_id = ccc.id AND ecp.employee_id = ? AND ecp.tenant_id = ?
            WHERE ccc.course_id = ? AND ccc.tenant_id = ?
            ORDER BY ccc.module_order, ccc.content_order
        ", [$employeeId, $this->tenantId(), $courseId, $this->tenantId()]) ?: [];
    }

    public function markContentComplete(int $employeeId, int $contentId, array $data = []): array
    {
        $tid = $this->tenantId();
        $existing = $this->db->fetchOne(
            "SELECT id FROM employee_content_progress WHERE employee_id = ? AND content_id = ? AND tenant_id = ?",
            [$employeeId, $contentId, $tid]
        );

        $data['tenant_id'] = $tid;
        $data['employee_id'] = $employeeId;
        $data['content_id'] = $contentId;

        if ($existing) {
            $this->db->update('employee_content_progress', $data, ['id' => $existing['id']]);
            return ['success' => true, 'message' => 'Progress updated'];
        }

        $data['tenant_id'] = $tid;
        $id = $this->db->insert('employee_content_progress', $data);
        return ['success' => true, 'id' => $id, 'message' => 'Progress recorded'];
    }

    public function getAssessmentResults(int $assessmentId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM training_assessment_results WHERE assessment_id = ? AND tenant_id = ? ORDER BY created_at DESC",
            [$assessmentId, $this->tenantId()]
        ) ?: [];
    }

    public function submitAssessment(int $employeeId, int $assessmentId, array $answers): array
    {
        $assessment = $this->db->fetchOne(
            "SELECT * FROM training_assessments WHERE id = ? AND tenant_id = ?",
            [$assessmentId, $this->tenantId()]
        );
        if (!$assessment) {
            return ['success' => false, 'message' => 'Assessment not found'];
        }

        $correct = 0;
        $total = count($answers);

        foreach ($answers as $qId => $answer) {
            $question = $this->db->fetchOne(
                "SELECT * FROM training_assessment_questions WHERE id = ? AND assessment_id = ?",
                [$qId, $assessmentId]
            );
            if ($question && isset($question['correct_answer'])) {
                if (is_array($question['correct_answer'])) {
                    if (is_array($answer) && array_diff($question['correct_answer'], $answer) === [] && array_diff($answer, $question['correct_answer']) === []) {
                        $correct++;
                    }
                } else {
                    if ((string)$question['correct_answer'] === (string)$answer) {
                        $correct++;
                    }
                }
            }
        }

        $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
        $passed = $score >= ($assessment['passing_score'] ?? 70);

        $tid = $this->tenantId();
        $this->db->insert('training_assessment_results', [
            'tenant_id' => $tid,
            'assessment_id' => $assessmentId,
            'employee_id' => $employeeId,
            'score' => $score,
            'passed' => $passed,
            'answers' => json_encode($answers),
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'score' => $score, 'passed' => $passed, 'correct' => $correct, 'total' => $total];
    }
}