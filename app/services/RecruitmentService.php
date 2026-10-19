<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class RecruitmentService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    public function getJobPostings(array $filters = []): array
    {
        $where = ['jp.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['status'])) { $where[] = 'jp.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['department'])) { $where[] = 'jp.department = ?'; $params[] = $filters['department']; }
        if (!empty($filters['employment_type'])) { $where[] = 'jp.employment_type = ?'; $params[] = $filters['employment_type']; }
        if (!empty($filters['search'])) { $where[] = '(jp.title LIKE ? OR jp.job_code LIKE ? OR jp.description LIKE ?)'; $s = '%' . $filters['search'] . '%'; $params[] = $s; $params[] = $s; $params[] = $s; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT jp.*, u.name as hiring_manager_name, u2.name as recruiter_name,
                    (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = jp.id AND ja.tenant_id = ?) as applications_count
                FROM job_postings jp
                LEFT JOIN users u ON jp.hiring_manager_id = u.id
                LEFT JOIN users u2 ON jp.recruiter_id = u2.id
                $whereSql
                ORDER BY jp.created_at DESC";

        $params[] = $this->tenantId();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$this->tenantId()], $params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJobPosting(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT jp.*, u.name as hiring_manager_name, u2.name as recruiter_name
            FROM job_postings jp
            LEFT JOIN users u ON jp.hiring_manager_id = u.id
            LEFT JOIN users u2 ON jp.recruiter_id = u2.id
            WHERE jp.id = ? AND jp.tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createJobPosting(array $data): array
    {
        $required = ['title', 'job_code', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $columns = array_merge(
            ['job_code', 'title', 'department', 'designation', 'employment_type', 'experience_min', 'experience_max',
             'salary_min', 'salary_max', 'salary_currency', 'location', 'is_remote', 'description', 'requirements',
             'responsibilities', 'benefits', 'skills_required', 'education_requirement', 'openings',
             'status', 'published_on', 'hiring_manager_id', 'recruiter_id', 'created_by', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['job_code'], $data['title'], $data['department'] ?? null, $data['designation'] ?? null,
            $data['employment_type'] ?? 'full_time', $data['experience_min'] ?? 0, $data['experience_max'] ?? 0,
            $data['salary_min'] ?? null, $data['salary_max'] ?? null, $data['salary_currency'] ?? 'INR',
            $data['location'] ?? null, $data['is_remote'] ?? false, $data['description'],
            $data['requirements'] ?? null, $data['responsibilities'] ?? null, $data['benefits'] ?? null,
            $data['skills_required'] ? json_encode($data['skills_required']) : null,
            $data['education_requirement'] ?? null, $data['openings'] ?? 1,
            $data['status'] ?? 'draft',
            $data['published_on'] ? json_encode($data['published_on']) : null,
            $data['hiring_manager_id'] ?? null, $data['recruiter_id'] ?? null,
            $data['created_by'] ?? 1, date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO job_postings (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Job posting created'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Job code already exists'];
            }
            throw $e;
        }
    }

    public function updateJobPosting(int $id, array $data): array
    {
        $allowed = ['title', 'department', 'designation', 'employment_type', 'experience_min', 'experience_max',
            'salary_min', 'salary_max', 'location', 'is_remote', 'description', 'requirements',
            'responsibilities', 'benefits', 'skills_required', 'education_requirement', 'openings',
            'status', 'closes_at', 'hiring_manager_id', 'recruiter_id'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $field === 'skills_required' && is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
            }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];

        if (isset($data['status']) && $data['status'] === 'published') {
            $fields[] = 'published_at = NOW()';
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE job_postings SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Updated' : 'Not found'];
    }

    public function publishJob(int $id): array
    {
        $this->conn->execute("UPDATE job_postings SET status = 'published', published_at = NOW() WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        return ['success' => true, 'message' => 'Job published'];
    }

    public function getApplications(array $filters = []): array
    {
        $where = ['ja.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['job_id'])) { $where[] = 'ja.job_id = ?'; $params[] = $filters['job_id']; }
        if (!empty($filters['status'])) { $where[] = 'ja.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['current_stage'])) { $where[] = 'ja.current_stage = ?'; $params[] = $filters['current_stage']; }
        if (!empty($filters['screened_by'])) { $where[] = 'ja.screened_by = ?'; $params[] = $filters['screened_by']; }
        if (!empty($filters['search'])) { $where[] = '(ja.candidate_name LIKE ? OR ja.email LIKE ? OR ja.application_number LIKE ?)'; $s = '%' . $filters['search'] . '%'; $params[] = $s; $params[] = $s; $params[] = $s; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT ja.*, jp.title as job_title, jp.job_code, jp.department,
                    u.name as screened_by_name
                FROM job_applications ja
                JOIN job_postings jp ON ja.job_id = jp.id
                LEFT JOIN users u ON ja.screened_by = u.id
                WHERE $whereSql
                ORDER BY ja.applied_at DESC";

        $params[] = $this->tenantId();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$this->tenantId()], $params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApplication(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT ja.*, jp.title as job_title, jp.job_code, jp.department,
                   u.name as screened_by_name
            FROM job_applications ja
            JOIN job_postings jp ON ja.job_id = jp.id
            LEFT JOIN users u ON ja.screened_by = u.id
            WHERE ja.id = ? AND ja.tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createApplication(array $data): array
    {
        $required = ['job_id', 'candidate_name', 'email', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $appNumber = 'APP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $columns = array_merge(
            ['application_number', 'job_id', 'candidate_name', 'email', 'phone', 'alternate_phone',
             'current_location', 'preferred_location', 'current_ctc', 'expected_ctc', 'notice_period_days',
             'resume_path', 'cover_letter', 'linkedin_url', 'portfolio_url', 'current_employer',
             'current_designation', 'total_experience_years', 'relevant_experience_years',
             'highest_qualification', 'skills', 'certifications', 'source', 'referral_employee_id',
             'status', 'current_stage', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $appNumber, $data['job_id'], $data['candidate_name'], $data['email'], $data['phone'],
            $data['alternate_phone'] ?? null, $data['current_location'] ?? null, $data['preferred_location'] ?? null,
            $data['current_ctc'] ?? null, $data['expected_ctc'] ?? null, $data['notice_period_days'] ?? 0,
            $data['resume_path'] ?? null, $data['cover_letter'] ?? null, $data['linkedin_url'] ?? null,
            $data['portfolio_url'] ?? null, $data['current_employer'] ?? null, $data['current_designation'] ?? null,
            $data['total_experience_years'] ?? 0, $data['relevant_experience_years'] ?? 0,
            $data['highest_qualification'] ?? null,
            $data['skills'] ? json_encode($data['skills']) : null,
            $data['certifications'] ? json_encode($data['certifications']) : null,
            $data['source'] ?? 'careers_page', $data['referral_employee_id'] ?? null,
            'applied', 'applied', date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO job_applications (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        $this->conn->execute("UPDATE job_postings SET openings = openings - 1 WHERE id = ? AND tenant_id = ?", [$data['job_id'], $this->tenantId()]);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'application_number' => $appNumber, 'message' => 'Application submitted'];
    }

    public function updateApplication(int $id, array $data): array
    {
        $allowed = ['candidate_name', 'email', 'phone', 'current_location', 'preferred_location',
            'current_ctc', 'expected_ctc', 'notice_period_days', 'resume_path', 'cover_letter',
            'linkedin_url', 'portfolio_url', 'current_employer', 'current_designation',
            'total_experience_years', 'relevant_experience_years', 'highest_qualification',
            'skills', 'certifications', 'source', 'referral_employee_id', 'status', 'current_stage',
            'screened_by', 'screened_at'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = in_array($field, ['skills', 'certifications']) && is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
            }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];

        if (isset($data['status']) && $data['status'] === 'hired') {
            $fields[] = 'last_stage_change_at = NOW()';
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE job_applications SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Updated' : 'Not found'];
    }

    public function scheduleInterview(int $applicationId, array $data): array
    {
        $required = ['round_number', 'round_type', 'interviewer_id', 'interview_type', 'scheduled_at'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $columns = array_merge(
            ['application_id', 'round_number', 'round_type', 'interviewer_id', 'interview_type',
             'scheduled_at', 'duration_minutes', 'meeting_link', 'meeting_id', 'meeting_password',
             'location', 'status', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $applicationId, $data['round_number'], $data['round_type'], $data['interviewer_id'],
            $data['interview_type'], $data['scheduled_at'], $data['duration_minutes'] ?? 60,
            $data['meeting_link'] ?? null, $data['meeting_id'] ?? null, $data['meeting_password'] ?? null,
            $data['location'] ?? null, 'scheduled', date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO interview_schedules (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        $this->conn->execute("UPDATE job_applications SET current_stage = 'interview_scheduled', last_stage_change_at = NOW() WHERE id = ? AND tenant_id = ?", [$applicationId, $this->tenantId()]);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Interview scheduled'];
    }

    public function submitInterviewFeedback(int $scheduleId, array $data): array
    {
        $fields = ['status = ?', 'feedback = ?', 'rating = ?', 'recommendation = ?', 'feedback_submitted_at = NOW()'];
        $params = [$data['status'] ?? 'completed', $data['feedback'] ?? null, $data['rating'] ?? null, $data['recommendation'] ?? null];

        $params[] = $scheduleId; $params[] = $this->tenantId();
        $sql = "UPDATE interview_schedules SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $schedule = $this->conn->fetch("SELECT application_id FROM interview_schedules WHERE id = ? AND tenant_id = ?", [$scheduleId, $this->tenantId()]);
        if ($schedule && isset($data['recommendation'])) {
            $newStage = in_array($data['recommendation'], ['strong_hire', 'hire']) ? 'offer_extended' : 'rejected';
            $this->conn->execute("UPDATE job_applications SET current_stage = ?, last_stage_change_at = NOW() WHERE id = ? AND tenant_id = ?", [$newStage, $schedule['application_id'], $this->tenantId()]);
        }

        return ['success' => $stmt->rowCount() > 0, 'message' => 'Feedback submitted'];
    }

    public function createOffer(array $data): array
    {
        $required = ['application_id', 'candidate_name', 'email', 'job_title', 'ctc_annual', 'ctc_monthly'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $offerNumber = 'OFR-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $columns = array_merge(
            ['offer_number', 'application_id', 'candidate_name', 'email', 'job_title', 'department',
             'reporting_manager_id', 'joining_date', 'employment_type', 'probation_period_months',
             'ctc_annual', 'ctc_monthly', 'basic_salary', 'hra', 'allowances', 'bonus_variable',
             'pf_employer', 'esi_employer', 'gratuity', 'mediclaim', 'other_benefits',
             'offer_validity_days', 'status', 'created_by', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $offerNumber, $data['application_id'], $data['candidate_name'], $data['email'],
            $data['job_title'], $data['department'] ?? null, $data['reporting_manager_id'] ?? null,
            $data['joining_date'] ?? null, $data['employment_type'] ?? 'full_time',
            $data['probation_period_months'] ?? 3, $data['ctc_annual'], $data['ctc_monthly'],
            $data['basic_salary'] ?? null, $data['hra'] ?? null, $data['allowances'] ?? null,
            $data['bonus_variable'] ?? null, $data['pf_employer'] ?? null, $data['esi_employer'] ?? null,
            $data['gratuity'] ?? null, $data['mediclaim'] ?? null, $data['other_benefits'] ?? null,
            $data['offer_validity_days'] ?? 7, 'draft', $data['created_by'] ?? 1, date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO offer_letters (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        $this->conn->execute("UPDATE job_applications SET current_stage = 'offer_extended', last_stage_change_at = NOW() WHERE id = ? AND tenant_id = ?", [$data['application_id'], $this->tenantId()]);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'offer_number' => $offerNumber, 'message' => 'Offer created'];
    }

    public function sendOffer(int $id): array
    {
        $this->conn->execute("UPDATE offer_letters SET status = 'sent', sent_at = NOW() WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        return ['success' => true, 'message' => 'Offer sent'];
    }

    public function acceptOffer(int $id): array
    {
        $this->conn->execute("UPDATE offer_letters SET status = 'accepted', responded_at = NOW() WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        $offer = $this->conn->fetch("SELECT application_id FROM offer_letters WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        if ($offer) {
            $this->conn->execute("UPDATE job_applications SET current_stage = 'offer_accepted', last_stage_change_at = NOW(), status = 'hired' WHERE id = ? AND tenant_id = ?", [$offer['application_id'], $this->tenantId()]);
        }
        return ['success' => true, 'message' => 'Offer accepted'];
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, status FROM job_postings WHERE tenant_id = ? GROUP BY status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $stats['jobs_by_status'][$row['status']] = $row['c'];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, status FROM job_applications WHERE tenant_id = ? GROUP BY status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $stats['apps_by_status'][$row['status']] = $row['c'];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM job_applications WHERE tenant_id = ? AND status IN ("interview_scheduled","interviewed")');
        $stmt->execute([$this->tenantId()]);
        $stats['active_interviews'] = $stmt->fetchColumn();

        return $stats;
    }
}