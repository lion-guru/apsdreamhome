<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class EngagementSurveyService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== SURVEYS ====================

    public function getSurveys(array $filters = []): array
    {
        $where = ['es.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['status'])) {
            $where[] = 'es.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['survey_type'])) {
            $where[] = 'es.survey_type = ?';
            $params[] = $filters['survey_type'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(es.title LIKE ? OR es.description LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT es.*, 
                    (SELECT COUNT(*) FROM survey_responses sr WHERE sr.survey_id = es.id AND sr.tenant_id = ?) as response_count,
                    u.name as creator_name
                FROM engagement_surveys es
                LEFT JOIN users u ON es.created_by = u.id
                $whereSql
                ORDER BY es.created_at DESC";

        $params[] = $this->tenantId();

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSurvey(int $surveyId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT es.*, u.name as creator_name
            FROM engagement_surveys es
            LEFT JOIN users u ON es.created_by = u.id
            WHERE es.id = ? AND es.tenant_id = ?
        ");
        $stmt->execute([$surveyId, $this->tenantId()]);
        $survey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($survey) {
            $survey['questions'] = json_decode($survey['questions'], true) ?? [];
            $survey['settings'] = json_decode($survey['settings'], true) ?? [];
        }
        
        return $survey ?: null;
    }

    public function getActiveSurveyForEmployee(int $employeeId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT es.* FROM engagement_surveys es
            WHERE es.tenant_id = ? 
            AND es.status = 'active'
            AND es.start_date <= CURDATE()
            AND (es.end_date IS NULL OR es.end_date >= CURDATE())
            ORDER BY es.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getEmployeeAvailableSurveys(int $employeeId): array
    {
        $stmt = $this->conn->prepare("
            SELECT es.*, 
                (SELECT COUNT(*) FROM survey_responses sr WHERE sr.survey_id = es.id AND sr.tenant_id = ? AND sr.employee_id = ?) as has_responded
            FROM engagement_surveys es
            WHERE es.tenant_id = ? 
            AND es.status = 'active'
            AND es.start_date <= CURDATE()
            AND (es.end_date IS NULL OR es.end_date >= CURDATE())
            ORDER BY es.created_at DESC
        ");
        $stmt->execute([$this->tenantId(), $employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSurvey(array $data): array
    {
        $required = ['title', 'questions'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("$field is required");
            }
        }

        $questions = is_array($data['questions']) ? json_encode($data['questions']) : $data['questions'];
        $settings = !empty($data['settings']) ? (is_array($data['settings']) ? json_encode($data['settings']) : $data['settings']) : json_encode([]);

        $columns = array_merge(
            ['title', 'description', 'survey_type', 'status', 'start_date', 'end_date', 'is_anonymous', 'allow_multiple', 'questions', 'settings', 'created_by', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['title'],
            $data['description'] ?? null,
            $data['survey_type'] ?? 'pulse',
            $data['status'] ?? 'draft',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['is_anonymous'] ?? true,
            $data['allow_multiple'] ?? false,
            $questions,
            $settings,
            $data['created_by'] ?? 1,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO engagement_surveys (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Survey created'];
    }

    public function updateSurvey(int $surveyId, array $data): array
    {
        $allowed = ['title', 'description', 'survey_type', 'status', 'start_date', 'end_date', 'is_anonymous', 'allow_multiple', 'questions', 'settings'];
        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                if ($field === 'questions' || $field === 'settings') {
                    $params[] = is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
                } else {
                    $params[] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        $params[] = $surveyId;
        $params[] = $this->tenantId();

        $sql = "UPDATE engagement_surveys SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Survey updated' : 'Survey not found'];
    }

    public function deleteSurvey(int $surveyId): array
    {
        // Check for responses
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM survey_responses WHERE survey_id = ? AND tenant_id = ?');
        $stmt->execute([$surveyId, $this->tenantId()]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return ['success' => false, 'message' => "Cannot delete: $count responses exist. Archive instead."];
        }

        $stmt = $this->conn->prepare('DELETE FROM engagement_surveys WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$surveyId, $this->tenantId()]);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Survey deleted' : 'Survey not found'];
    }

    public function activateSurvey(int $surveyId): array
    {
        $stmt = $this->conn->prepare("
            UPDATE engagement_surveys 
            SET status = 'active', updated_at = NOW() 
            WHERE id = ? AND tenant_id = ? AND status IN ('draft', 'scheduled')
        ");
        $stmt->execute([$surveyId, $this->tenantId()]);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Survey activated' : 'Cannot activate'];
    }

    public function closeSurvey(int $surveyId): array
    {
        $stmt = $this->conn->prepare("
            UPDATE engagement_surveys 
            SET status = 'closed', updated_at = NOW() 
            WHERE id = ? AND tenant_id = ? AND status = 'active'
        ");
        $stmt->execute([$surveyId, $this->tenantId()]);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Survey closed' : 'Cannot close'];
    }

    // ==================== SURVEY RESPONSES ====================

    public function submitResponse(int $surveyId, int $employeeId, array $answers): array
    {
        $survey = $this->getSurvey($surveyId);
        if (!$survey) {
            return ['success' => false, 'message' => 'Survey not found'];
        }

        if ($survey['status'] !== 'active') {
            return ['success' => false, 'message' => 'Survey is not active'];
        }

        if ($survey['start_date'] && $survey['start_date'] > date('Y-m-d')) {
            return ['success' => false, 'message' => 'Survey has not started yet'];
        }

        if ($survey['end_date'] && $survey['end_date'] < date('Y-m-d')) {
            return ['success' => false, 'message' => 'Survey has ended'];
        }

        // Check if already responded (unless allow_multiple)
        if (!$survey['allow_multiple']) {
            $stmt = $this->conn->prepare('SELECT id FROM survey_responses WHERE survey_id = ? AND employee_id = ? AND tenant_id = ?');
            $stmt->execute([$surveyId, $employeeId, $this->tenantId()]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'You have already responded to this survey'];
            }
        }

        $columns = array_merge(
            ['survey_id', 'employee_id', 'answers', 'submitted_at', 'ip_address', 'user_agent', 'completion_time_seconds'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $surveyId,
            $employeeId,
            json_encode($answers),
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $data['completion_time_seconds'] ?? null
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO survey_responses (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Response submitted'];
    }

    public function getResponses(int $surveyId, array $filters = []): array
    {
        $where = ['sr.survey_id = ?', 'sr.tenant_id = ?'];
        $params = [$surveyId, $this->tenantId()];

        if (!empty($filters['employee_id'])) {
            $where[] = 'sr.employee_id = ?';
            $params[] = $filters['employee_id'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT sr.*, e.name as employee_name, e.email as employee_email, e.department
            FROM survey_responses sr
            JOIN employees e ON sr.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            $whereSql
            ORDER BY sr.submitted_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode answers
        foreach ($responses as &$r) {
            $r['answers'] = json_decode($r['answers'], true) ?? [];
        }

        return $responses;
    }

    public function getEmployeeResponse(int $surveyId, int $employeeId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM survey_responses WHERE survey_id = ? AND employee_id = ? AND tenant_id = ? ORDER BY submitted_at DESC LIMIT 1');
        $stmt->execute([$surveyId, $employeeId, $this->tenantId()]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($response) {
            $response['answers'] = json_decode($response['answers'], true) ?? [];
        }
        
        return $response ?: null;
    }

    // ==================== ANALYTICS ====================

    public function getSurveyAnalytics(int $surveyId): array
    {
        $survey = $this->getSurvey($surveyId);
        if (!$survey) {
            return ['success' => false, 'message' => 'Survey not found'];
        }

        $questions = json_decode($survey['questions'], true) ?? [];
        $responses = $this->getResponses($surveyId);

        $analytics = [
            'survey' => $survey,
            'total_responses' => count($responses),
            'questions_analysis' => [],
            'enps' => null,
            'completion_stats' => []
        ];

        // Analyze each question
        foreach ($questions as $qIndex => $question) {
            $analysis = [
                'question' => $question,
                'responses' => 0,
                'distribution' => [],
                'average' => null
            ];

            foreach ($responses as $response) {
                if (isset($response['answers'][$qIndex])) {
                    $answer = $response['answers'][$qIndex];
                    $analysis['responses']++;
                    
                    if (is_numeric($answer)) {
                        $analysis['average'] = ($analysis['average'] ?? 0) + $answer;
                    } else {
                        $key = (string)$answer;
                        $analysis['distribution'][$key] = ($analysis['distribution'][$key] ?? 0) + 1;
                    }
                }
            }

            if ($analysis['average'] !== null && $analysis['responses'] > 0) {
                $analysis['average'] = round($analysis['average'] / $analysis['responses'], 2);
            }

            $analytics['questions_analysis'][] = $analysis;
        }

        // Calculate eNPS if survey has NPS question
        $npsIndex = -1;
        foreach ($questions as $i => $q) {
            if (strpos(strtolower($q['text'] ?? ''), 'recommend') !== false || strpos(strtolower($q['type'] ?? ''), 'nps') !== false) {
                $npsIndex = $i;
                break;
            }
        }

        if ($npsIndex >= 0) {
            $promoters = $passives = $detractors = 0;
            foreach ($responses as $response) {
                $score = $response['answers'][$npsIndex] ?? null;
                if (is_numeric($score)) {
                    if ($score >= 9) $promoters++;
                    elseif ($score >= 7) $passives++;
                    else $detractors++;
                }
            }
            $total = $promoters + $passives + $detractors;
            if ($total > 0) {
                $analytics['enps'] = round((($promoters - $detractors) / $total) * 100);
                $analytics['enps_breakdown'] = [
                    'promoters' => $promoters,
                    'passives' => $passives,
                    'detractors' => $detractors
                ];
            }
        }

        // Completion time stats
        $times = array_column($responses, 'completion_time_seconds');
        $times = array_filter($times, fn($t) => $t > 0);
        if (!empty($times)) {
            $analytics['completion_stats'] = [
                'avg_seconds' => round(array_sum($times) / count($times)),
                'min_seconds' => min($times),
                'max_seconds' => max($times)
            ];
        }

        return $analytics;
    }

    public function getDepartmentBreakdown(int $surveyId): array
    {
        $responses = $this->getResponses($surveyId);
        $departments = [];

        foreach ($responses as $response) {
            $dept = $response['department'] ?? 'Unknown';
            if (!isset($departments[$dept])) {
                $departments[$dept] = ['count' => 0, 'responses' => []];
            }
            $departments[$dept]['count']++;
            $departments[$dept]['responses'][] = $response;
        }

        // Calculate avg scores per department if applicable
        foreach ($departments as &$dept) {
            $scores = [];
            foreach ($dept['responses'] as $r) {
                foreach ($r['answers'] as $answer) {
                    if (is_numeric($answer)) {
                        $scores[] = $answer;
                    }
                }
            }
            $dept['avg_score'] = !empty($scores) ? round(array_sum($scores) / count($scores), 2) : null;
            unset($dept['responses']);
        }

        return $departments;
    }

    public function getTrendData(int $surveyId, string $period = 'month'): array
    {
        $stmt = $this->conn->prepare("
            SELECT DATE(sr.submitted_at) as date, COUNT(*) as responses
            FROM survey_responses sr
            WHERE sr.survey_id = ? AND sr.tenant_id = ?
            GROUP BY DATE(sr.submitted_at)
            ORDER BY DATE(sr.submitted_at) ASC
        ");
        $stmt->execute([$surveyId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== SURVEY TYPES & CATEGORIES ====================

    public function getSurveyTypes(): array
    {
        return [
            'pulse' => 'Pulse Survey (Quick, Frequent)',
            'annual' => 'Annual Engagement Survey',
            'onboarding' => 'Onboarding Survey',
            'exit' => 'Exit Survey',
            'custom' => 'Custom Survey'
        ];
    }

    public function getQuestionTypes(): array
    {
        return [
            'rating' => 'Rating Scale (1-5, 1-10)',
            'nps' => 'Net Promoter Score (0-10)',
            'multiple_choice' => 'Multiple Choice (Single)',
            'checkbox' => 'Multiple Choice (Multiple)',
            'text' => 'Short Text',
            'textarea' => 'Long Text',
            'yes_no' => 'Yes/No',
            'likert' => 'Likert Scale'
        ];
    }

    // ==================== STATS ====================

    public function getStats(): array
    {
        $stats = [];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM engagement_surveys WHERE tenant_id = ?');
        $stmt->execute([$this->tenantId()]);
        $stats['total_surveys'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM engagement_surveys WHERE tenant_id = ? AND status = "active"');
        $stmt->execute([$this->tenantId()]);
        $stats['active_surveys'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM engagement_surveys WHERE tenant_id = ? AND status = "draft"');
        $stmt->execute([$this->tenantId()]);
        $stats['draft_surveys'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM engagement_surveys WHERE tenant_id = ? AND status = "closed"');
        $stmt->execute([$this->tenantId()]);
        $stats['closed_surveys'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM survey_responses WHERE tenant_id = ?');
        $stmt->execute([$this->tenantId()]);
        $stats['total_responses'] = $stmt->fetchColumn();

        // Response rate for active surveys
        if ($stats['active_surveys'] > 0) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT sr.employee_id) as respondents
                FROM survey_responses sr
                JOIN engagement_surveys es ON sr.survey_id = es.id
                WHERE sr.tenant_id = ? AND es.status = 'active'
            ");
            $stmt->execute([$this->tenantId()]);
            $stats['unique_respondents'] = $stmt->fetchColumn();
        }

        return $stats;
    }
}