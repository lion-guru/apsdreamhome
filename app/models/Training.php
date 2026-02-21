<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Associate MLM Training Module Model
 * Handles e-learning courses, modules, quizzes, certifications, and progress tracking
 */
class Training extends Model
{
    protected $table = 'training_courses';

    /**
     * Enroll user in course
     */
    public function enrollUserInCourse(int $userId, string $userType, int $courseId): array
    {
        // Check if course exists and is active
        $course = $this->find($courseId);
        if (!$course || !$course['is_active']) {
            return ['success' => false, 'message' => 'Course not found or not available'];
        }

        // Check if user is already enrolled
        $existingEnrollment = $this->query(
            "SELECT id FROM user_course_enrollments WHERE user_id = ? AND user_type = ? AND course_id = ?",
            [$userId, $userType, $courseId]
        )->fetch();

        if ($existingEnrollment) {
            return ['success' => false, 'message' => 'User is already enrolled in this course'];
        }

        // Check enrollment limits
        if ($course['max_enrollments'] && $course['current_enrollments'] >= $course['max_enrollments']) {
            return ['success' => false, 'message' => 'Course enrollment limit reached'];
        }

        // Check prerequisites
        if (!$this->checkPrerequisites($userId, $userType, $courseId)) {
            return ['success' => false, 'message' => 'Prerequisites not met'];
        }

        // Create enrollment
        $enrollmentId = $this->insertInto('user_course_enrollments', [
            'user_id' => $userId,
            'user_type' => $userType,
            'course_id' => $courseId,
            'status' => 'enrolled',
            'enrollment_date' => date('Y-m-d H:i:s')
        ]);

        // Update course enrollment count
        $this->query(
            "UPDATE training_courses SET current_enrollments = current_enrollments + 1 WHERE id = ?",
            [$courseId]
        );

        // Initialize module progress
        $this->initializeModuleProgress($enrollmentId, $courseId);

        // Log enrollment
        $this->logTrainingEvent($courseId, $userId, $userType, 'enrollment', [
            'enrollment_id' => $enrollmentId
        ]);

        return [
            'success' => true,
            'enrollment_id' => $enrollmentId,
            'message' => 'Successfully enrolled in course'
        ];
    }

    /**
     * Update module progress
     */
    public function updateModuleProgress(int $enrollmentId, int $moduleId, array $progressData): array
    {
        // Get or create progress record
        $progress = $this->query(
            "SELECT * FROM module_progress WHERE enrollment_id = ? AND module_id = ?",
            [$enrollmentId, $moduleId]
        )->fetch();

        if (!$progress) {
            $progressId = $this->insertInto('module_progress', [
                'enrollment_id' => $enrollmentId,
                'module_id' => $moduleId,
                'status' => 'in_progress'
            ]);
            $progress = $this->query("SELECT * FROM module_progress WHERE id = ?", [$progressId])->fetch();
        }

        // Update progress
        $updateData = [
            'progress_percentage' => $progressData['progress_percentage'] ?? $progress['progress_percentage'],
            'time_spent_minutes' => ($progress['time_spent_minutes'] ?? 0) + ($progressData['time_spent_minutes'] ?? 0),
            'last_attempt_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($progressData['score_percentage'])) {
            $updateData['score_percentage'] = $progressData['score_percentage'];
        }

        if ($progressData['progress_percentage'] >= 100) {
            $updateData['status'] = 'completed';
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->query(
            "UPDATE module_progress SET " . implode(', ', array_map(function($k) { return "$k = ?"; }, array_keys($updateData))) . " WHERE id = ?",
            array_merge(array_values($updateData), [$progress['id']])
        );

        // Update overall course progress
        $this->updateCourseProgress($enrollmentId);

        // Check for completion and rewards
        if ($updateData['status'] === 'completed') {
            $this->handleModuleCompletion($enrollmentId, $moduleId, $progressData);
        }

        return ['success' => true, 'message' => 'Module progress updated'];
    }

    /**
     * Submit quiz attempt
     */
    public function submitQuizAttempt(int $enrollmentId, int $quizId, array $answers): array
    {
        $quiz = $this->query("SELECT * FROM training_quizzes WHERE id = ?", [$quizId])->fetch();
        if (!$quiz) {
            return ['success' => false, 'message' => 'Quiz not found'];
        }

        // Get questions and calculate score
        $questions = $this->query("SELECT * FROM quiz_questions WHERE quiz_id = ? AND is_active = 1 ORDER BY question_order", [$quizId])->fetchAll();

        $correctAnswers = 0;
        $totalQuestions = count($questions);
        $detailedResults = [];

        foreach ($questions as $question) {
            $userAnswer = $answers[$question['id']] ?? '';
            $isCorrect = $this->checkAnswer($question, $userAnswer);

            $detailedResults[] = [
                'question_id' => $question['id'],
                'user_answer' => $userAnswer,
                'correct_answer' => $question['correct_answer'],
                'is_correct' => $isCorrect,
                'points' => $isCorrect ? $question['points'] : 0
            ];

            if ($isCorrect) {
                $correctAnswers++;
            }
        }

        $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
        $passed = $scorePercentage >= $quiz['passing_score_percentage'];

        // Create attempt record
        $attemptId = $this->insertInto('quiz_attempts', [
            'enrollment_id' => $enrollmentId,
            'quiz_id' => $quizId,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percentage' => $scorePercentage,
            'passed' => $passed,
            'answers' => json_encode($detailedResults),
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        // Update module progress if this is a module quiz
        if ($quiz['module_id']) {
            $this->updateModuleProgress($enrollmentId, $quiz['module_id'], [
                'score_percentage' => $scorePercentage,
                'progress_percentage' => 100
            ]);
        }

        // Handle course completion if this is a final exam
        if ($quiz['quiz_type'] === 'final_exam' && $passed) {
            $this->handleCourseCompletion($enrollmentId, $scorePercentage);
        }

        return [
            'success' => true,
            'attempt_id' => $attemptId,
            'score_percentage' => $scorePercentage,
            'passed' => $passed,
            'results' => $detailedResults
        ];
    }

    /**
     * Get user enrollments
     */
    public function getUserEnrollments(int $userId, string $userType, string $status = null): array
    {
        $query = "SELECT uce.*, tc.course_title, tc.course_category, tc.difficulty_level, tc.course_duration_hours
                  FROM user_course_enrollments uce
                  LEFT JOIN training_courses tc ON uce.course_id = tc.id
                  WHERE uce.user_id = ? AND uce.user_type = ?";

        $params = [$userId, $userType];

        if ($status) {
            $query .= " AND uce.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY uce.enrollment_date DESC";

        $enrollments = $this->query($query, $params)->fetchAll();

        // Add progress details for each enrollment
        foreach ($enrollments as &$enrollment) {
            $enrollment['modules_progress'] = $this->getEnrollmentModulesProgress($enrollment['id']);
            $enrollment['next_module'] = $this->getNextModule($enrollment['id']);
        }

        return $enrollments;
    }

    /**
     * Get course details with modules
     */
    public function getCourseDetails(int $courseId): ?array
    {
        $course = $this->find($courseId);
        if (!$course) {
            return null;
        }

        $course = $course->toArray();

        // Get modules
        $modules = $this->query(
            "SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC",
            [$courseId]
        )->fetchAll();

        // Get quizzes
        $quizzes = $this->query(
            "SELECT * FROM training_quizzes WHERE course_id = ? AND is_active = 1 ORDER BY quiz_type, created_at",
            [$courseId]
        )->fetchAll();

        // Decode JSON fields
        $course['course_objectives'] = json_decode($course['course_objectives'], true);
        $course['prerequisites'] = json_decode($course['prerequisites'], true);
        $course['target_audience'] = json_decode($course['target_audience'], true);

        $course['modules'] = $modules;
        $course['quizzes'] = $quizzes;

        return $course;
    }

    /**
     * Issue certificate
     */
    public function issueCertificate(int $enrollmentId): array
    {
        $enrollment = $this->query(
            "SELECT uce.*, tc.course_title, tc.certificate_template,
                    u.first_name, u.last_name
             FROM user_course_enrollments uce
             LEFT JOIN training_courses tc ON uce.course_id = tc.id
             LEFT JOIN users u ON uce.user_id = u.id AND uce.user_type = 'associate'
             WHERE uce.id = ? AND uce.status = 'completed'",
            [$enrollmentId]
        )->fetch();

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found or course not completed'];
        }

        // Generate certificate number
        $certificateNumber = $this->generateCertificateNumber($enrollment);

        // Create certificate record
        $certificateId = $this->insertInto('training_certificates', [
            'enrollment_id' => $enrollmentId,
            'certificate_number' => $certificateNumber,
            'certificate_title' => 'Certificate of Completion',
            'issued_to_name' => $enrollment['first_name'] . ' ' . $enrollment['last_name'],
            'issued_to_id' => $enrollment['user_id'],
            'issued_by_name' => 'APS Dream Home Training Department',
            'issued_by_id' => 1, // System admin
            'course_name' => $enrollment['course_title'],
            'completion_date' => date('Y-m-d', strtotime($enrollment['completion_date'])),
            'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($enrollment['completion_date']))),
            'issued_at' => date('Y-m-d H:i:s')
        ]);

        // Update enrollment
        $this->query(
            "UPDATE user_course_enrollments SET certificate_issued = 1, certificate_number = ? WHERE id = ?",
            [$certificateNumber, $enrollmentId]
        );

        // Award points and badges
        $this->awardCompletionRewards($enrollment['user_id'], $enrollment['user_type'], $enrollment['course_id']);

        return [
            'success' => true,
            'certificate_id' => $certificateId,
            'certificate_number' => $certificateNumber,
            'message' => 'Certificate issued successfully'
        ];
    }

    /**
     * Get training analytics
     */
    public function getTrainingAnalytics(string $period = '30 days'): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $analytics = [
            'enrollments' => $this->query(
                "SELECT COUNT(*) as total, course_category FROM user_course_enrollments uce
                 LEFT JOIN training_courses tc ON uce.course_id = tc.id
                 WHERE uce.enrollment_date >= ?
                 GROUP BY course_category",
                [$startDate]
            )->fetchAll(),
            'completions' => $this->query(
                "SELECT COUNT(*) as total, course_category FROM user_course_enrollments uce
                 LEFT JOIN training_courses tc ON uce.course_id = tc.id
                 WHERE uce.status = 'completed' AND uce.completion_date >= ?
                 GROUP BY course_category",
                [$startDate]
            )->fetchAll(),
            'quiz_performance' => $this->query(
                "SELECT AVG(score_percentage) as avg_score, COUNT(*) as total_attempts
                 FROM quiz_attempts qa
                 LEFT JOIN training_quizzes tq ON qa.quiz_id = tq.id
                 WHERE qa.completed_at >= ?",
                [$startDate]
            )->fetch(),
            'popular_courses' => $this->query(
                "SELECT tc.course_title, COUNT(uce.id) as enrollments
                 FROM training_courses tc
                 LEFT JOIN user_course_enrollments uce ON tc.id = uce.course_id
                 WHERE uce.enrollment_date >= ?
                 GROUP BY tc.id, tc.course_title
                 ORDER BY enrollments DESC LIMIT 10",
                [$startDate]
            )->fetchAll(),
            'certificates_issued' => $this->query(
                "SELECT COUNT(*) as total FROM training_certificates WHERE issued_at >= ?",
                [$startDate]
            )->fetch()['total']
        ];

        return $analytics;
    }

    // Helper methods

    private function checkPrerequisites(int $userId, string $userType, int $courseId): bool
    {
        $course = $this->find($courseId);
        $prerequisites = json_decode($course['prerequisites'], true);

        if (empty($prerequisites)) {
            return true;
        }

        foreach ($prerequisites as $prereqCourse) {
            $completed = $this->query(
                "SELECT id FROM user_course_enrollments
                 WHERE user_id = ? AND user_type = ? AND course_id = ? AND status = 'completed'",
                [$userId, $userType, $prereqCourse]
            )->fetch();

            if (!$completed) {
                return false;
            }
        }

        return true;
    }

    private function initializeModuleProgress(int $enrollmentId, int $courseId): void
    {
        $modules = $this->query("SELECT id FROM course_modules WHERE course_id = ? ORDER BY module_order", [$courseId])->fetchAll();

        foreach ($modules as $module) {
            $this->insertInto('module_progress', [
                'enrollment_id' => $enrollmentId,
                'module_id' => $module['id'],
                'status' => 'not_started'
            ]);
        }
    }

    private function updateCourseProgress(int $enrollmentId): void
    {
        $progress = $this->query(
            "SELECT COUNT(*) as total_modules, COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_modules
             FROM module_progress WHERE enrollment_id = ?",
            [$enrollmentId]
        )->fetch();

        $progressPercentage = $progress['total_modules'] > 0
            ? round(($progress['completed_modules'] / $progress['total_modules']) * 100, 2)
            : 0;

        $status = $progressPercentage >= 100 ? 'completed' : 'in_progress';

        $this->query(
            "UPDATE user_course_enrollments SET progress_percentage = ?, status = ? WHERE id = ?",
            [$progressPercentage, $status, $enrollmentId]
        );
    }

    private function handleModuleCompletion(int $enrollmentId, int $moduleId, array $progressData): void
    {
        $module = $this->query("SELECT * FROM course_modules WHERE id = ?", [$moduleId])->fetch();

        // Award points for module completion
        if ($module['points_reward'] > 0) {
            // Would integrate with gamification system
            // $this->gamification->awardPoints($userId, $userType, $module['points_reward'], 'module_completed');
        }

        // Log completion
        $enrollment = $this->query("SELECT * FROM user_course_enrollments WHERE id = ?", [$enrollmentId])->fetch();
        $this->logTrainingEvent($enrollment['course_id'], $enrollment['user_id'], $enrollment['user_type'], 'module_complete', [
            'module_id' => $moduleId,
            'enrollment_id' => $enrollmentId
        ]);
    }

    private function handleCourseCompletion(int $enrollmentId, float $finalScore): void
    {
        $enrollment = $this->query("SELECT * FROM user_course_enrollments WHERE id = ?", [$enrollmentId])->fetch();

        $this->query(
            "UPDATE user_course_enrollments SET
             status = 'completed',
             completion_date = NOW(),
             final_score_percentage = ?
             WHERE id = ?",
            [$finalScore, $enrollmentId]
        );

        // Issue certificate
        $this->issueCertificate($enrollmentId);

        // Log completion
        $this->logTrainingEvent($enrollment['course_id'], $enrollment['user_id'], $enrollment['user_type'], 'course_complete', [
            'enrollment_id' => $enrollmentId,
            'final_score' => $finalScore
        ]);
    }

    private function checkAnswer(array $question, string $userAnswer): bool
    {
        $correctAnswer = $question['correct_answer'];

        switch ($question['question_type']) {
            case 'multiple_choice':
                return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
            case 'true_false':
                return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
            case 'short_answer':
                // Simple text matching - in real implementation, would use NLP
                return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
            default:
                return false;
        }
    }

    private function getEnrollmentModulesProgress(int $enrollmentId): array
    {
        return $this->query(
            "SELECT mp.*, cm.module_title, cm.content_type
             FROM module_progress mp
             LEFT JOIN course_modules cm ON mp.module_id = cm.id
             WHERE mp.enrollment_id = ?
             ORDER BY cm.module_order",
            [$enrollmentId]
        )->fetchAll();
    }

    private function getNextModule(int $enrollmentId): ?array
    {
        $nextModule = $this->query(
            "SELECT cm.* FROM module_progress mp
             LEFT JOIN course_modules cm ON mp.module_id = cm.id
             WHERE mp.enrollment_id = ? AND mp.status != 'completed'
             ORDER BY cm.module_order ASC LIMIT 1",
            [$enrollmentId]
        )->fetch();

        return $nextModule;
    }

    private function generateCertificateNumber(array $enrollment): string
    {
        $year = date('Y');
        $courseId = str_pad($enrollment['course_id'], 3, '0', STR_PAD_LEFT);
        $userId = str_pad($enrollment['user_id'], 6, '0', STR_PAD_LEFT);

        return "CERT-{$year}-{$courseId}-{$userId}";
    }

    private function awardCompletionRewards(int $userId, string $userType, int $courseId): void
    {
        $course = $this->find($courseId);

        // Award course completion points
        if ($course['points_reward'] > 0) {
            // Would integrate with gamification system
            // $this->gamification->awardPoints($userId, $userType, $course['points_reward'], 'course_completed');
        }

        // Award badge if specified
        if ($course['badge_reward_id']) {
            // Would integrate with gamification system
            // $this->gamification->awardBadge($userId, $userType, $course['badge_reward_id']);
        }
    }

    private function logTrainingEvent(int $courseId, int $userId, string $userType, string $eventType, array $eventData = []): void
    {
        $this->insertInto('training_analytics', [
            'course_id' => $courseId,
            'user_id' => $userId,
            'user_type' => $userType,
            'event_type' => $eventType,
            'event_data' => json_encode($eventData),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
