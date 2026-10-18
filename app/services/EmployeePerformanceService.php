<?php

namespace App\Services;

use App\Core\Database\Database;

class EmployeePerformanceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function recordPerformance($employeeId, $periodType, $periodStart, $periodEnd, $data)
    {
        $tasksAssigned = $data['tasks_assigned'] ?? 0;
        $tasksCompleted = $data['tasks_completed'] ?? 0;
        $tasksOnTime = $data['tasks_on_time'] ?? 0;
        $tasksOverdue = $data['tasks_overdue'] ?? 0;
        $avgCompletionTime = $data['avg_completion_time_hours'] ?? null;
        $qualityScore = $data['quality_score'] ?? null;
        $productivityScore = $data['productivity_score'] ?? null;
        $attendanceScore = $data['attendance_score'] ?? null;

        // Calculate overall score
        $overallScore = $this->calculateOverallScore([
            'tasks_completed' => $tasksCompleted,
            'tasks_assigned' => $tasksAssigned,
            'tasks_on_time' => $tasksOnTime,
            'tasks_overdue' => $tasksOverdue,
            'quality_score' => $qualityScore,
            'productivity_score' => $productivityScore,
            'attendance_score' => $attendanceScore
        ]);

        $this->db->execute(
            "INSERT INTO employee_performance 
             (tenant_id, employee_id, period_type, period_start, period_end,
              tasks_assigned, tasks_completed, tasks_on_time, tasks_overdue,
              avg_completion_time_hours, quality_score, productivity_score, 
              attendance_score, overall_score)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                tasks_assigned = VALUES(tasks_assigned),
                tasks_completed = VALUES(tasks_completed),
                tasks_on_time = VALUES(tasks_on_time),
                tasks_overdue = VALUES(tasks_overdue),
                avg_completion_time_hours = VALUES(avg_completion_time_hours),
                quality_score = VALUES(quality_score),
                productivity_score = VALUES(productivity_score),
                attendance_score = VALUES(attendance_score),
                overall_score = VALUES(overall_score),
                updated_at = NOW()",
            [1, $employeeId, $periodType, $periodStart, $periodEnd,
             $data['tasks_assigned'] ?? 0, $data['tasks_completed'] ?? 0,
             $data['tasks_on_time'] ?? 0, $data['tasks_overdue'] ?? 0,
             $avgCompletionTime, $qualityScore, $productivityScore,
             $attendanceScore, $overallScore]
        );

        return ['success' => true];
    }

    private function calculateOverallScore($metrics)
    {
        $weights = [
            'completion_rate' => 0.30,
            'on_time_rate' => 0.25,
            'quality' => 0.20,
            'productivity' => 0.15,
            'attendance' => 0.10
        ];

        $completionRate = $metrics['tasks_assigned'] > 0 ? ($metrics['tasks_completed'] / $metrics['tasks_assigned']) * 100 : 0;
        $onTimeRate = $metrics['tasks_completed'] > 0 ? ($metrics['tasks_on_time'] / $metrics['tasks_completed']) * 100 : 0;

        $score = 0;
        $score += min(100, $completionRate) * $weights['completion_rate'];
        $score += $onTimeRate * $weights['on_time_rate'];
        $score += ($metrics['quality_score'] ?? 50) * $weights['quality'];
        $score += ($metrics['productivity_score'] ?? 50) * $weights['productivity'];
        $score += ($metrics['attendance_score'] ?? 50) * $weights['attendance'];

        return round($score, 2);
    }

    public function getPerformance($employeeId, $periodType = 'monthly', $periodStart = null, $periodEnd = null)
    {
        $query = "SELECT * FROM employee_performance WHERE tenant_id = 1 AND employee_id = ? AND period_type = ?";
        $params = [$employeeId, $periodType];

        if ($periodStart) {
            $query .= " AND period_start >= ?";
            $params[] = $periodStart;
        }
        if ($periodEnd) {
            $query .= " AND period_end <= ?";
            $params[] = $periodEnd;
        }

        $query .= " ORDER BY period_start DESC LIMIT 12";
        return $this->db->fetchAll($query, $params);
    }

    public function getLatestPerformance($employeeId)
    {
        return $this->db->fetchOne(
            "SELECT * FROM employee_performance WHERE tenant_id = 1 AND employee_id = ? ORDER BY period_start DESC LIMIT 1",
            [$employeeId]
        );
    }

    public function calculateAndStoreMonthlyPerformance($employeeId, $year, $month)
    {
        $periodStart = sprintf('%04d-%02d-01', $year, $month);
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        // Get task metrics
        $taskStats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_assigned,
                SUM(status = 'completed') as completed,
                SUM(status = 'completed' AND completed_at <= due_date) as on_time,
                SUM(status = 'completed' AND completed_at > due_date) as overdue,
                AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours
             FROM tasks 
             WHERE assigned_to = ? 
             AND DATE(created_at) BETWEEN ? AND ?",
            [$employeeId, $periodStart, $periodEnd]
        );

        // Get attendance score
        $attendance = $this->db->fetchOne(
            "SELECT 
                SUM(status = 'present') as present,
                COUNT(*) as total
             FROM employee_attendance 
             WHERE employee_id = ? 
             AND attendance_date BETWEEN ? AND ?",
            [$employeeId, $periodStart, $periodEnd]
        );

        $attendanceScore = $attendance['total'] > 0 ? round(($attendance['present'] / $attendance['total']) * 100, 2) : 50;

        // Calculate productivity based on completed tasks vs avg time
        $avgHours = $taskStats['avg_hours'] ?? 0;
        $productivityScore = $avgHours > 0 ? min(100, max(0, 100 - ($avgHours - 4) * 5)) : 50; // Baseline 4 hours per task

        $this->recordPerformance($employeeId, 'monthly', $periodStart, $periodEnd, [
            'tasks_assigned' => $taskStats['total_assigned'] ?? 0,
            'tasks_completed' => $taskStats['completed'] ?? 0,
            'tasks_on_time' => $taskStats['on_time'] ?? 0,
            'tasks_overdue' => $taskStats['overdue'] ?? 0,
            'avg_completion_time_hours' => round($taskStats['avg_hours'] ?? 0, 2),
            'quality_score' => 50, // Default, can be updated via review
            'productivity_score' => round($productivityScore, 2),
            'attendance_score' => $attendanceScore
        ]);

        return ['success' => true];
    }

    public function getDepartmentPerformance($department, $periodType = 'monthly')
    {
        return $this->db->fetchAll(
            "SELECT e.id, e.name, e.role, ep.overall_score, ep.tasks_completed, ep.tasks_on_time, ep.quality_score
             FROM users e
             JOIN employees emp ON emp.user_id = e.id
             LEFT JOIN employee_performance ep ON ep.employee_id = e.id 
                 AND ep.tenant_id = 1 
                 AND ep.period_type = ?
                 AND ep.period_start >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
             WHERE e.status = 'active' AND emp.status = 'active' AND emp.department = ?
             ORDER BY ep.overall_score DESC",
            [$periodType, $department]
        );
    }

    public function createReview($data)
    {
        $this->db->execute(
            "INSERT INTO employee_performance_reviews 
             (tenant_id, employee_id, reviewer_id, review_period_start, review_period_end,
              overall_rating, quality_rating, productivity_rating, communication_rating, 
              teamwork_rating, initiative_rating, strengths, areas_for_improvement, 
              goals, employee_comments, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                1, $data['employee_id'], $data['reviewer_id'],
                $data['period_start'], $data['period_end'],
                $data['overall_rating'] ?? null,
                $data['quality_rating'] ?? null,
                $data['productivity_rating'] ?? null,
                $data['communication_rating'] ?? null,
                $data['teamwork_rating'] ?? null,
                $data['initiative_rating'] ?? null,
                $data['strengths'] ?? null,
                $data['areas_for_improvement'] ?? null,
                $data['goals'] ?? null,
                $data['employee_comments'] ?? null,
                $data['status'] ?? 'draft'
            ]
        );

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function getReviews($employeeId, $status = null)
    {
        $query = "SELECT epr.*, u.name as reviewer_name 
                  FROM employee_performance_reviews epr
                  JOIN users u ON u.id = epr.reviewer_id
                  WHERE epr.tenant_id = 1 AND epr.employee_id = ?";
        $params = [$employeeId];
        
        if ($status) {
            $query .= " AND epr.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY epr.review_period_end DESC";
        return $this->db->fetchAll($query, $params);
    }
}