<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Performance Management Model
 * Handles performance reviews, KPIs, goals, and feedback
 */
class Performance extends Model
{
    protected $table = 'performance_reviews';
    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period_start',
        'review_period_end',
        'review_type',
        'overall_rating',
        'performance_level',
        'goals_achievement',
        'strengths',
        'areas_for_improvement',
        'development_plan',
        'reviewer_comments',
        'employee_comments',
        'status',
        'review_date',
        'next_review_date',
        'is_self_review',
        'created_at',
        'updated_at'
    ];

    const REVIEW_TYPE_MONTHLY = 'monthly';
    const REVIEW_TYPE_QUARTERLY = 'quarterly';
    const REVIEW_TYPE_ANNUAL = 'annual';
    const REVIEW_TYPE_PROBATION = 'probation';

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ACKNOWLEDGED = 'acknowledged';

    const PERFORMANCE_LEVEL_EXCEEDS = 'exceeds_expectations';
    const PERFORMANCE_LEVEL_MEETS = 'meets_expectations';
    const PERFORMANCE_LEVEL_BELOW = 'below_expectations';
    const PERFORMANCE_LEVEL_NEEDS_IMPROVEMENT = 'needs_improvement';

    /**
     * Create a new performance review
     */
    public function createReview(array $data): array
    {
        $reviewData = [
            'employee_id' => $data['employee_id'],
            'reviewer_id' => $data['reviewer_id'] ?? null,
            'review_period_start' => $data['review_period_start'],
            'review_period_end' => $data['review_period_end'],
            'review_type' => $data['review_type'] ?? self::REVIEW_TYPE_MONTHLY,
            'is_self_review' => $data['is_self_review'] ?? false,
            'status' => self::STATUS_DRAFT,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $reviewId = $this->insert($reviewData);

        return [
            'success' => true,
            'review_id' => $reviewId,
            'message' => 'Performance review created successfully'
        ];
    }

    /**
     * Update performance review
     */
    public function updateReview(int $reviewId, array $data): array
    {
        $updateData = [
            'overall_rating' => $data['overall_rating'] ?? null,
            'performance_level' => $data['performance_level'] ?? null,
            'goals_achievement' => $data['goals_achievement'] ?? null,
            'strengths' => $data['strengths'] ?? null,
            'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
            'development_plan' => $data['development_plan'] ?? null,
            'reviewer_comments' => $data['reviewer_comments'] ?? null,
            'employee_comments' => $data['employee_comments'] ?? null,
            'status' => $data['status'] ?? null,
            'review_date' => $data['review_date'] ?? null,
            'next_review_date' => $data['next_review_date'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->update($reviewId, $updateData);

        return [
            'success' => true,
            'message' => 'Performance review updated successfully'
        ];
    }

    /**
     * Get performance reviews for an employee
     */
    public function getEmployeeReviews(int $employeeId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT pr.*, a.auser as reviewer_name
                FROM performance_reviews pr
                LEFT JOIN admin a ON pr.reviewer_id = a.aid
                WHERE pr.employee_id = ?
                ORDER BY pr.created_at DESC
                LIMIT ? OFFSET ?";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Get pending reviews for reviewer
     */
    public function getPendingReviews(int $reviewerId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT pr.*, e.name as employee_name, e.employee_code, e.designation
                FROM performance_reviews pr
                LEFT JOIN employees e ON pr.employee_id = e.id
                WHERE pr.reviewer_id = ? AND pr.status IN ('submitted', 'under_review')
                ORDER BY pr.created_at ASC
                LIMIT ? OFFSET ?";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$reviewerId, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Submit review for approval
     */
    public function submitReview(int $reviewId): array
    {
        $review = $this->find($reviewId);
        if (!$review) {
            return ['success' => false, 'message' => 'Review not found'];
        }

        if ($review['status'] !== self::STATUS_DRAFT) {
            return ['success' => false, 'message' => 'Review is not in draft status'];
        }

        $this->update($reviewId, [
            'status' => self::STATUS_SUBMITTED,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'Review submitted for approval successfully'
        ];
    }

    /**
     * Approve review
     */
    public function approveReview(int $reviewId, int $approverId, string $comments = null): array
    {
        $review = $this->find($reviewId);
        if (!$review) {
            return ['success' => false, 'message' => 'Review not found'];
        }

        $this->update($reviewId, [
            'status' => self::STATUS_COMPLETED,
            'reviewer_comments' => $comments,
            'review_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'Review approved successfully'
        ];
    }

    /**
     * Create employee KPI targets
     */
    public function setEmployeeKPIs(int $employeeId, array $kpis, string $periodStart, string $periodEnd): array
    {
        $db = Database::getInstance();

        foreach ($kpis as $kpi) {
            $kpiData = [
                'employee_id' => $employeeId,
                'kpi_id' => $kpi['kpi_id'],
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'target_value' => $kpi['target_value'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->query(
                "INSERT INTO employee_kpis (employee_id, kpi_id, period_start, period_end, target_value, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE target_value = ?, updated_at = ?",
                [
                    $kpiData['employee_id'], $kpiData['kpi_id'], $kpiData['period_start'],
                    $kpiData['period_end'], $kpiData['target_value'], $kpiData['status'],
                    $kpiData['created_at'], $kpi['target_value'], date('Y-m-d H:i:s')
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'Employee KPIs set successfully'
        ];
    }

    /**
     * Update KPI actual values
     */
    public function updateKPIActual(int $employeeKpiId, float $actualValue, string $comments = null): array
    {
        $db = Database::getInstance();

        // Get KPI details
        $kpiData = $db->query("SELECT * FROM employee_kpis WHERE id = ?", [$employeeKpiId])->fetch();
        if (!$kpiData) {
            return ['success' => false, 'message' => 'KPI not found'];
        }

        $achievement = ($actualValue / $kpiData['target_value']) * 100;
        $score = min(5, ($achievement / 100) * 5); // Score out of 5

        $db->query(
            "UPDATE employee_kpis SET
             actual_value = ?, achievement_percentage = ?, score = ?, comments = ?, updated_at = ?
             WHERE id = ?",
            [$actualValue, $achievement, $score, $comments, date('Y-m-d H:i:s'), $employeeKpiId]
        );

        return [
            'success' => true,
            'message' => 'KPI updated successfully'
        ];
    }

    /**
     * Get employee KPIs for a period
     */
    public function getEmployeeKPIs(int $employeeId, string $periodStart, string $periodEnd): array
    {
        $sql = "SELECT ek.*, k.name as kpi_name, k.description, k.category, k.unit, k.weightage
                FROM employee_kpis ek
                LEFT JOIN kpis k ON ek.kpi_id = k.id
                WHERE ek.employee_id = ? AND ek.period_start = ? AND ek.period_end = ?
                ORDER BY k.category, k.name";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $periodStart, $periodEnd]);

        return $stmt->fetchAll();
    }

    /**
     * Calculate overall performance score
     */
    public function calculatePerformanceScore(int $employeeId, string $periodStart, string $periodEnd): array
    {
        $kpis = $this->getEmployeeKPIs($employeeId, $periodStart, $periodEnd);

        $totalScore = 0;
        $totalWeightage = 0;
        $completedKPIs = 0;

        foreach ($kpis as $kpi) {
            if ($kpi['actual_value'] !== null) {
                $weightedScore = ($kpi['score'] ?? 0) * ($kpi['weightage'] / 100);
                $totalScore += $weightedScore;
                $totalWeightage += $kpi['weightage'];
                $completedKPIs++;
            }
        }

        $overallScore = $totalWeightage > 0 ? ($totalScore / $totalWeightage) * 100 : 0;

        return [
            'overall_score' => round($overallScore, 2),
            'total_kpis' => count($kpis),
            'completed_kpis' => $completedKPIs,
            'weighted_score' => round($totalScore, 2),
            'total_weightage' => $totalWeightage
        ];
    }

    /**
     * Create performance goal
     */
    public function createGoal(array $data): array
    {
        $goalData = [
            'employee_id' => $data['employee_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'] ?? 'individual',
            'priority' => $data['priority'] ?? 'medium',
            'target_date' => $data['target_date'],
            'assigned_by' => $data['assigned_by'] ?? null,
            'status' => 'not_started',
            'progress_percentage' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $goalId = $this->insertInto('performance_goals', $goalData);

        return [
            'success' => true,
            'goal_id' => $goalId,
            'message' => 'Performance goal created successfully'
        ];
    }

    /**
     * Update goal progress
     */
    public function updateGoalProgress(int $goalId, float $progress, string $status = null): array
    {
        $updateData = [
            'progress_percentage' => $progress,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status) {
            $updateData['status'] = $status;
        }

        $this->query(
            "UPDATE performance_goals SET progress_percentage = ?, status = ?, updated_at = ? WHERE id = ?",
            [$progress, $status ?? 'in_progress', date('Y-m-d H:i:s'), $goalId]
        );

        return [
            'success' => true,
            'message' => 'Goal progress updated successfully'
        ];
    }

    /**
     * Get employee goals
     */
    public function getEmployeeGoals(int $employeeId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT pg.*, a.auser as assigned_by_name
                FROM performance_goals pg
                LEFT JOIN admin a ON pg.assigned_by = a.aid
                WHERE pg.employee_id = ?
                ORDER BY pg.created_at DESC
                LIMIT ? OFFSET ?";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Add performance feedback
     */
    public function addFeedback(array $data): array
    {
        $feedbackData = [
            'review_id' => $data['review_id'],
            'feedback_type' => $data['feedback_type'] ?? 'manager',
            'feedback_by' => $data['feedback_by'],
            'feedback_for' => $data['feedback_for'],
            'rating_overall' => $data['rating_overall'] ?? null,
            'rating_communication' => $data['rating_communication'] ?? null,
            'rating_technical_skills' => $data['rating_technical_skills'] ?? null,
            'rating_leadership' => $data['rating_leadership'] ?? null,
            'rating_teamwork' => $data['rating_teamwork'] ?? null,
            'rating_quality' => $data['rating_quality'] ?? null,
            'positive_feedback' => $data['positive_feedback'] ?? null,
            'areas_improvement' => $data['areas_improvement'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $feedbackId = $this->insertInto('performance_feedback', $feedbackData);

        return [
            'success' => true,
            'feedback_id' => $feedbackId,
            'message' => 'Feedback submitted successfully'
        ];
    }

    /**
     * Get review feedback
     */
    public function getReviewFeedback(int $reviewId): array
    {
        $sql = "SELECT pf.*, a.auser as feedback_by_name
                FROM performance_feedback pf
                LEFT JOIN admin a ON pf.feedback_by = a.aid
                WHERE pf.review_id = ?
                ORDER BY pf.created_at DESC";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$reviewId]);

        return $stmt->fetchAll();
    }

    /**
     * Get performance analytics
     */
    public function getPerformanceAnalytics(int $employeeId = null, string $startDate = null, string $endDate = null): array
    {
        $db = Database::getInstance();

        $whereClause = "";
        $params = [];

        if ($employeeId) {
            $whereClause .= " AND pr.employee_id = ?";
            $params[] = $employeeId;
        }

        if ($startDate) {
            $whereClause .= " AND pr.review_period_start >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $whereClause .= " AND pr.review_period_end <= ?";
            $params[] = $endDate;
        }

        $sql = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(pr.overall_rating) as avg_rating,
                    COUNT(CASE WHEN pr.performance_level = 'exceeds_expectations' THEN 1 END) as exceeds_count,
                    COUNT(CASE WHEN pr.performance_level = 'meets_expectations' THEN 1 END) as meets_count,
                    COUNT(CASE WHEN pr.performance_level = 'below_expectations' THEN 1 END) as below_count,
                    COUNT(CASE WHEN pr.performance_level = 'needs_improvement' THEN 1 END) as improvement_count
                FROM performance_reviews pr
                WHERE pr.status = 'completed' {$whereClause}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $analytics = $stmt->fetch();

        return $analytics ?: [
            'total_reviews' => 0,
            'avg_rating' => 0,
            'exceeds_count' => 0,
            'meets_count' => 0,
            'below_count' => 0,
            'improvement_count' => 0
        ];
    }
}
