<?php

namespace App\Services\Training;

use App\Core\Database;

/**
 * Training Module Service
 * Video courses, certifications for associates
 */
class TrainingService
{
    private $db;

    // Course status
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    // Enrollment status
    const ENROLLMENT_ACTIVE = 'active';
    const ENROLLMENT_COMPLETED = 'completed';
    const ENROLLMENT_DROPPED = 'dropped';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create new course
     */
    public function createCourse(array $data): array
    {
        $course = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'category' => $data['category'] ?? 'general',
            'difficulty_level' => $data['difficulty_level'] ?? 'beginner',
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? null,
            'is_mandatory' => $data['is_mandatory'] ?? false,
            'points_reward' => $data['points_reward'] ?? 100,
            'status' => self::STATUS_DRAFT,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $sql = "INSERT INTO training_courses (" . implode(', ', array_keys($course)) . ") 
                VALUES (" . implode(', ', array_fill(0, count($course), '?')) . ")";

        $this->db->query($sql, array_values($course));

        return [
            'success' => true,
            'course_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Add module to course
     */
    public function addModule(int $courseId, array $data): array
    {
        // Get next order
        $order = $this->db->query(
            "SELECT COALESCE(MAX(sort_order), 0) + 1 FROM training_modules WHERE course_id = ?",
            [$courseId]
        )->fetchColumn();

        $module = [
            'course_id' => $courseId,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'sort_order' => $order,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->query(
            "INSERT INTO training_modules (course_id, title, description, sort_order, created_at) VALUES (?, ?, ?, ?, ?)",
            array_values($module)
        );

        return [
            'success' => true,
            'module_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Add lesson to module
     */
    public function addLesson(int $moduleId, array $data): array
    {
        $order = $this->db->query(
            "SELECT COALESCE(MAX(sort_order), 0) + 1 FROM training_lessons WHERE module_id = ?",
            [$moduleId]
        )->fetchColumn();

        $lesson = [
            'module_id' => $moduleId,
            'title' => $data['title'],
            'content_type' => $data['content_type'] ?? 'video', // video, text, quiz, document
            'content_url' => $data['content_url'] ?? null,
            'content_text' => $data['content_text'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'sort_order' => $order,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->query(
            "INSERT INTO training_lessons (module_id, title, content_type, content_url, content_text, duration_minutes, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($lesson)
        );

        return [
            'success' => true,
            'lesson_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Enroll user in course
     */
    public function enrollUser(int $courseId, int $userId): array
    {
        // Check if already enrolled
        $existing = $this->db->query(
            "SELECT id, status FROM training_enrollments WHERE course_id = ? AND user_id = ?",
            [$courseId, $userId]
        )->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['status'] === self::ENROLLMENT_ACTIVE) {
                return ['success' => false, 'error' => 'Already enrolled'];
            }
            // Reactivate
            $this->db->query(
                "UPDATE training_enrollments SET status = ?, enrolled_at = NOW(), progress = 0 WHERE id = ?",
                [self::ENROLLMENT_ACTIVE, $existing['id']]
            );
            return ['success' => true, 'enrollment_id' => $existing['id']];
        }

        $this->db->query(
            "INSERT INTO training_enrollments (course_id, user_id, status, enrolled_at, created_at) VALUES (?, ?, ?, NOW(), NOW())",
            [$courseId, $userId, self::ENROLLMENT_ACTIVE]
        );

        return [
            'success' => true,
            'enrollment_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Mark lesson as complete
     */
    public function completeLesson(int $enrollmentId, int $lessonId): array
    {
        $this->db->query(
            "INSERT INTO training_lesson_progress (enrollment_id, lesson_id, completed_at, created_at) VALUES (?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE completed_at = NOW()",
            [$enrollmentId, $lessonId]
        );

        // Update overall progress
        $progress = $this->calculateProgress($enrollmentId);

        // Check if course completed
        if ($progress >= 100) {
            $this->completeCourse($enrollmentId);
        }

        return [
            'success' => true,
            'progress' => $progress
        ];
    }

    /**
     * Calculate course progress
     */
    private function calculateProgress(int $enrollmentId): float
    {
        $enrollment = $this->db->query(
            "SELECT e.course_id FROM training_enrollments e WHERE e.id = ?",
            [$enrollmentId]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$enrollment) return 0;

        $totalLessons = $this->db->query(
            "SELECT COUNT(*) FROM training_lessons l
             JOIN training_modules m ON l.module_id = m.id
             WHERE m.course_id = ?",
            [$enrollment['course_id']]
        )->fetchColumn();

        if ($totalLessons == 0) return 0;

        $completedLessons = $this->db->query(
            "SELECT COUNT(*) FROM training_lesson_progress p
             JOIN training_lessons l ON p.lesson_id = l.id
             JOIN training_modules m ON l.module_id = m.id
             WHERE p.enrollment_id = ? AND m.course_id = ?",
            [$enrollmentId, $enrollment['course_id']]
        )->fetchColumn();

        $progress = ($completedLessons / $totalLessons) * 100;

        // Update enrollment
        $this->db->query(
            "UPDATE training_enrollments SET progress = ? WHERE id = ?",
            [$progress, $enrollmentId]
        );

        return round($progress, 2);
    }

    /**
     * Complete course and award certificate
     */
    private function completeCourse(int $enrollmentId): void
    {
        $enrollment = $this->db->query(
            "SELECT e.*, c.title as course_title, c.points_reward FROM training_enrollments e
             JOIN training_courses c ON e.course_id = c.id
             WHERE e.id = ?",
            [$enrollmentId]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$enrollment) return;

        // Update enrollment
        $this->db->query(
            "UPDATE training_enrollments SET status = ?, completed_at = NOW() WHERE id = ?",
            [self::ENROLLMENT_COMPLETED, $enrollmentId]
        );

        // Generate certificate
        $this->generateCertificate($enrollment);

        // Award points
        $gamification = new \App\Services\Gamification\GamificationService();
        $gamification->addPoints(
            $enrollment['user_id'],
            $enrollment['points_reward'],
            "Completed course: {$enrollment['course_title']}"
        );
    }

    /**
     * Generate certificate
     */
    private function generateCertificate(array $enrollment): string
    {
        $certificateNumber = 'CERT-' . date('Ymd') . '-' . str_pad($enrollment['id'], 5, '0', STR_PAD_LEFT);

        $this->db->query(
            "INSERT INTO training_certificates (enrollment_id, certificate_number, issued_at, created_at) VALUES (?, ?, NOW(), NOW())",
            [$enrollment['id'], $certificateNumber]
        );

        return $certificateNumber;
    }

    /**
     * Get course details
     */
    public function getCourse(int $courseId): ?array
    {
        $course = $this->db->query(
            "SELECT c.*, u.name as instructor_name,
                   (SELECT COUNT(*) FROM training_enrollments WHERE course_id = c.id AND status = 'completed') as completions,
                   (SELECT AVG(rating) FROM training_reviews WHERE course_id = c.id) as avg_rating
             FROM training_courses c
             LEFT JOIN users u ON c.instructor_id = u.id
             WHERE c.id = ?",
            [$courseId]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$course) return null;

        // Get modules and lessons
        $course['modules'] = $this->db->query(
            "SELECT m.*, 
                    (SELECT COUNT(*) FROM training_lessons l WHERE l.module_id = m.id) as lesson_count
             FROM training_modules m
             WHERE m.course_id = ?
             ORDER BY m.sort_order",
            [$courseId]
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($course['modules'] as &$module) {
            $module['lessons'] = $this->db->query(
                "SELECT * FROM training_lessons WHERE module_id = ? ORDER BY sort_order",
                [$module['id']]
            )->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $course;
    }

    /**
     * Get user's enrolled courses
     */
    public function getUserEnrollments(int $userId): array
    {
        return $this->db->query(
            "SELECT e.*, c.title, c.description, c.thumbnail_url, c.category, c.difficulty_level,
                   c.duration_minutes, c.points_reward
             FROM training_enrollments e
             JOIN training_courses c ON e.course_id = c.id
             WHERE e.user_id = ?
             ORDER BY e.enrolled_at DESC",
            [$userId]
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get available courses
     */
    public function getAvailableCourses(int $userId = null, string $category = null): array
    {
        $sql = "SELECT c.*, u.name as instructor_name,
                       (SELECT COUNT(*) FROM training_enrollments WHERE course_id = c.id) as enrollment_count
                FROM training_courses c
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE c.status = ?";

        $params = [self::STATUS_PUBLISHED];

        if ($category) {
            $sql .= " AND c.category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $courses = $this->db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        if ($userId) {
            foreach ($courses as &$course) {
                $course['enrolled'] = $this->db->query(
                    "SELECT status FROM training_enrollments WHERE course_id = ? AND user_id = ?",
                    [$course['id'], $userId]
                )->fetchColumn() ?: false;
            }
        }

        return $courses;
    }

    /**
     * Add quiz to lesson
     */
    public function addQuiz(int $lessonId, array $questions): array
    {
        foreach ($questions as $index => $question) {
            $this->db->query(
                "INSERT INTO training_quizzes (lesson_id, question, options, correct_answer, points, sort_order, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $lessonId,
                    $question['question'],
                    json_encode($question['options']),
                    $question['correct_answer'],
                    $question['points'] ?? 1,
                    $index + 1
                ]
            );
        }

        return ['success' => true, 'questions_added' => count($questions)];
    }

    /**
     * Submit quiz answers
     */
    public function submitQuiz(int $enrollmentId, int $lessonId, array $answers): array
    {
        $questions = $this->db->query(
            "SELECT id, correct_answer, points FROM training_quizzes WHERE lesson_id = ?",
            [$lessonId]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $score = 0;
        $total = 0;
        $results = [];

        foreach ($questions as $question) {
            $total += $question['points'];
            $userAnswer = $answers[$question['id']] ?? null;

            if ($userAnswer === $question['correct_answer']) {
                $score += $question['points'];
                $results[$question['id']] = ['correct' => true];
            } else {
                $results[$question['id']] = ['correct' => false, 'correct_answer' => $question['correct_answer']];
            }
        }

        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
        $passed = $percentage >= 70; // 70% passing score

        // Save attempt
        $this->db->query(
            "INSERT INTO training_quiz_attempts (enrollment_id, lesson_id, score, total, percentage, passed, answers, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$enrollmentId, $lessonId, $score, $total, $percentage, $passed, json_encode($answers)]
        );

        if ($passed) {
            $this->completeLesson($enrollmentId, $lessonId);
        }

        return [
            'success' => true,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $passed,
            'results' => $results
        ];
    }

    /**
     * Get leaderboard for training
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return $this->db->query(
            "SELECT u.id, u.name, u.avatar,
                    COUNT(DISTINCT e.course_id) as courses_completed,
                    SUM(c.points_reward) as total_points,
                    COUNT(DISTINCT cert.id) as certificates
             FROM users u
             JOIN training_enrollments e ON u.id = e.user_id AND e.status = 'completed'
             JOIN training_courses c ON e.course_id = c.id
             LEFT JOIN training_certificates cert ON e.id = cert.enrollment_id
             GROUP BY u.id
             ORDER BY total_points DESC
             LIMIT ?",
            [$limit]
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Initialize default courses
     */
    public function initializeDefaultCourses(): void
    {
        $courses = [
            [
                'title' => 'Real Estate Basics',
                'description' => 'Introduction to real estate industry fundamentals',
                'category' => 'onboarding',
                'difficulty_level' => 'beginner',
                'duration_minutes' => 60,
                'is_mandatory' => true,
                'points_reward' => 50
            ],
            [
                'title' => 'Property Sales Techniques',
                'description' => 'Advanced sales strategies for property consultants',
                'category' => 'sales',
                'difficulty_level' => 'intermediate',
                'duration_minutes' => 120,
                'is_mandatory' => false,
                'points_reward' => 100
            ],
            [
                'title' => 'Customer Relationship Management',
                'description' => 'Building and maintaining client relationships',
                'category' => 'soft_skills',
                'difficulty_level' => 'intermediate',
                'duration_minutes' => 90,
                'is_mandatory' => false,
                'points_reward' => 75
            ],
            [
                'title' => 'Legal Aspects of Real Estate',
                'description' => 'Understanding property laws and regulations',
                'category' => 'legal',
                'difficulty_level' => 'advanced',
                'duration_minutes' => 180,
                'is_mandatory' => true,
                'points_reward' => 150
            ]
        ];

        foreach ($courses as $course) {
            $this->createCourse($course);
        }
    }
}
