<?php

namespace App\Services\Career;

use App\Core\Database\Database;

/**
 * Modern Career Service
 * Handles job applications, career management, and HR operations
 */
class CareerService
{
    private Database $db;
    private array $config;
    private array $allowedExtensions = ['pdf', 'doc', 'docx'];
    private int $maxFileSize = 5 * 1024 * 1024; // 5MB

    // Application statuses
    public const STATUS_RECEIVED = 'received';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';
    public const STATUS_INTERVIEW_COMPLETED = 'interview_completed';
    public const STATUS_OFFERED = 'offered';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public function __construct(Database $db = null, array $config = [])
    {
        $this->db = $db ?: Database::getInstance();
        $this->config = array_merge([
            'auto_email_notifications' => true,
            'resume_storage_path' => __DIR__ . '/../../../storage/resumes/',
            'interview_reminder_hours' => 24,
            'application_retention_days' => 365
        ], $config);

        $this->initializeCareerTables();
        $this->ensureStorageDirectory();
    }

    /**
     * Submit job application
     */
    public function submitApplication(array $data, array $files = []): array
    {
        $email = $data['email'] ?? 'unknown';

        try {
            // Validate required fields
            $validation = $this->validateApplicationData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Check for duplicate applications
            if ($this->hasRecentApplication($data['email'], $data['job_id'] ?? null)) {
                return [
                    'success' => false,
                    'message' => 'You have already applied for this position recently'
                ];
            }

            // Process file uploads
            $resumePath = null;
            if (!empty($files['resume'])) {
                $resumeResult = $this->processResumeUpload($files['resume'], $data['full_name']);
                if (!$resumeResult['success']) {
                    return $resumeResult;
                }
                $resumePath = $resumeResult['file_path'];
            }

            // Create application record
            $applicationId = $this->createApplicationRecord($data, $resumePath);

            // Send confirmation email
            if ($this->config['auto_email_notifications']) {
                $this->sendApplicationConfirmation($data, $applicationId);
            }

            // Log application submission
            error_log("Job application submitted: ID {$applicationId}, Email {$data['email']}");

            return [
                'success' => true,
                'message' => 'Application submitted successfully',
                'application_id' => $applicationId
            ];
        } catch (\Exception $e) {
            error_log("Failed to submit application: {$email} - " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to submit application: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get application by ID
     */
    public function getApplication(int $id): ?array
    {
        try {
            $sql = "SELECT a.*, j.title as job_title, j.department 
                    FROM job_applications a 
                    LEFT JOIN job_postings j ON a.job_id = j.id 
                    WHERE a.id = ?";

            $application = $this->db->fetchOne($sql, [$id]);

            if ($application) {
                $application['attachments'] = $this->getApplicationAttachments($id);
                $application['interviews'] = $this->getApplicationInterviews($id);
                $application['notes'] = $this->getApplicationNotes($id);
            }

            return $application;
        } catch (\Exception $e) {
            error_log("Failed to get application ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get applications with filters
     */
    public function getApplications(array $filters = []): array
    {
        try {
            $sql = "SELECT a.*, j.title as job_title, j.department 
                    FROM job_applications a 
                    LEFT JOIN job_postings j ON a.job_id = j.id 
                    WHERE 1=1";
            $params = [];

            // Add filters
            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['job_id'])) {
                $sql .= " AND a.job_id = ?";
                $params[] = $filters['job_id'];
            }

            if (!empty($filters['department'])) {
                $sql .= " AND j.department = ?";
                $params[] = $filters['department'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND a.created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND a.created_at <= ?";
                $params[] = $filters['date_to'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (a.full_name LIKE ? OR a.email LIKE ? OR j.title LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY a.created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $applications = $this->db->fetchAll($sql, $params);

            foreach ($applications as &$app) {
                $app['attachments'] = $this->getApplicationAttachments($app['id']);
                $app['interviews'] = $this->getApplicationInterviews($app['id']);
            }

            return $applications;
        } catch (\Exception $e) {
            error_log("Failed to get applications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus(int $id, string $status, string $reason = ''): array
    {
        try {
            // Validate status
            if (!in_array($status, $this->getValidStatuses())) {
                return [
                    'success' => false,
                    'message' => 'Invalid status'
                ];
            }

            // Get current application
            $application = $this->getApplication($id);
            if (!$application) {
                return [
                    'success' => false,
                    'message' => 'Application not found'
                ];
            }

            // Update status
            $sql = "UPDATE job_applications 
                    SET status = ?, status_reason = ?, updated_at = NOW() 
                    WHERE id = ?";

            $this->db->execute($sql, [$status, $reason, $id]);

            // Send status update notification
            if ($this->config['auto_email_notifications']) {
                $this->sendStatusUpdateNotification($application, $status, $reason);
            }

            // Log status change
            error_log("Application status updated: ID {$id}, {$application['status']} -> {$status}");

            return [
                'success' => true,
                'message' => 'Application status updated successfully'
            ];
        } catch (\Exception $e) {
            error_log("Failed to update application status ID {$id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Schedule interview
     */
    public function scheduleInterview(int $applicationId, array $interviewData): array
    {
        try {
            $validation = $this->validateInterviewData($interviewData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Interview data validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Create interview record
            $interviewId = $this->createInterviewRecord($applicationId, $interviewData);

            // Update application status
            $this->updateApplicationStatus($applicationId, self::STATUS_INTERVIEW_SCHEDULED, 'Interview scheduled');

            // Send interview invitation
            if ($this->config['auto_email_notifications']) {
                $this->sendInterviewInvitation($applicationId, $interviewData);
            }

            error_log("Interview scheduled: Application ID {$applicationId}, Interview ID {$interviewId}, Type {$interviewData['type']}");

            return [
                'success' => true,
                'message' => 'Interview scheduled successfully',
                'interview_id' => $interviewId
            ];
        } catch (\Exception $e) {
            error_log("Failed to schedule interview for application ID {$applicationId}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to schedule interview: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get career statistics
     */
    public function getCareerStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total applications
            $sql = "SELECT COUNT(*) as total FROM job_applications";
            $params = [];

            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }

            $stats['total_applications'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Applications by status
            $statusSql = "SELECT status, COUNT(*) as count FROM job_applications";
            $statusParams = [];

            if (!empty($filters['date_from'])) {
                $statusSql .= " WHERE created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }

            $statusSql .= " GROUP BY status";

            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Applications by department
            $deptSql = "SELECT j.department, COUNT(*) as count 
                       FROM job_applications a 
                       LEFT JOIN job_postings j ON a.job_id = j.id";
            $deptParams = [];

            if (!empty($filters['date_from'])) {
                $deptSql .= " WHERE a.created_at >= ?";
                $deptParams[] = $filters['date_from'];
            }

            $deptSql .= " GROUP BY j.department";

            $deptStats = $this->db->fetchAll($deptSql, $deptParams);
            $stats['by_department'] = [];
            foreach ($deptStats as $stat) {
                $stats['by_department'][$stat['department']] = $stat['count'];
            }

            // Recent applications
            $stats['recent_applications'] = $this->getApplications(['limit' => 10]);

            return $stats;
        } catch (\Exception $e) {
            error_log("Failed to get career stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeCareerTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS job_applications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                job_id INT,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                cover_letter TEXT,
                resume_path VARCHAR(500),
                status ENUM('received', 'under_review', 'shortlisted', 'interview_scheduled', 'interview_completed', 'offered', 'rejected', 'withdrawn') DEFAULT 'received',
                status_reason TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_status (status),
                INDEX idx_job_id (job_id),
                INDEX idx_created_at (created_at)
            )",

            "CREATE TABLE IF NOT EXISTS application_interviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                application_id INT NOT NULL,
                type ENUM('phone', 'video', 'in_person') NOT NULL,
                scheduled_date DATETIME NOT NULL,
                duration_minutes INT DEFAULT 60,
                interviewer_name VARCHAR(255),
                interviewer_email VARCHAR(255),
                meeting_link VARCHAR(500),
                notes TEXT,
                status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
                INDEX idx_application_id (application_id),
                INDEX idx_scheduled_date (scheduled_date)
            )",

            "CREATE TABLE IF NOT EXISTS application_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                application_id INT NOT NULL,
                note TEXT NOT NULL,
                created_by VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
                INDEX idx_application_id (application_id)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function ensureStorageDirectory(): void
    {
        $storagePath = $this->config['resume_storage_path'];
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
    }

    private function validateApplicationData(array $data): array
    {
        $errors = [];

        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            $errors[] = 'Full name is required and must be at least 2 characters';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }

        if (empty($data['phone']) || !preg_match('/^[\d\s\-\+\(\)]+$/', $data['phone'])) {
            $errors[] = 'Valid phone number is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function hasRecentApplication(string $email, ?int $jobId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM job_applications 
                WHERE email = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $params = [$email];

        if ($jobId) {
            $sql .= " AND job_id = ?";
            $params[] = $jobId;
        }

        $count = $this->db->fetchOne($sql, $params) ?? 0;
        return $count > 0;
    }

    private function processResumeUpload(array $file, string $fullName): array
    {
        try {
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'message' => 'File upload error'
                ];
            }

            if ($file['size'] > $this->maxFileSize) {
                return [
                    'success' => false,
                    'message' => 'File size exceeds maximum limit'
                ];
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedExtensions)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Only PDF, DOC, and DOCX files are allowed'
                ];
            }

            // Generate secure filename
            $sanitizedName = preg_replace('/[^a-zA-Z0-9]/', '_', $fullName);
            $filename = $sanitizedName . '_' . uniqid() . '.' . $extension;
            $filepath = $this->config['resume_storage_path'] . $filename;

            // Move file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to save file'
                ];
            }

            return [
                'success' => true,
                'file_path' => $filepath,
                'filename' => $filename
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'File processing failed: ' . $e->getMessage()
            ];
        }
    }

    private function createApplicationRecord(array $data, ?string $resumePath): string
    {
        $sql = "INSERT INTO job_applications 
                (job_id, full_name, email, phone, cover_letter, resume_path, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'received', NOW())";

        $this->db->execute($sql, [
            $data['job_id'] ?? null,
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['cover_letter'] ?? null,
            $resumePath
        ]);

        return $this->db->lastInsertId();
    }

    private function sendApplicationConfirmation(array $data, int $applicationId): void
    {
        // Mock email sending - would integrate with actual email service
        error_log("Application confirmation sent to {$data['email']}, Application ID {$applicationId}");
    }

    private function getApplicationAttachments(int $applicationId): array
    {
        $sql = "SELECT * FROM application_attachments WHERE application_id = ?";
        return $this->db->fetchAll($sql, [$applicationId]);
    }

    private function getApplicationInterviews(int $applicationId): array
    {
        $sql = "SELECT * FROM application_interviews WHERE application_id = ? ORDER BY scheduled_date ASC";
        return $this->db->fetchAll($sql, [$applicationId]);
    }

    private function getApplicationNotes(int $applicationId): array
    {
        $sql = "SELECT * FROM application_notes WHERE application_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$applicationId]);
    }

    private function getValidStatuses(): array
    {
        return [
            self::STATUS_RECEIVED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_SHORTLISTED,
            self::STATUS_INTERVIEW_SCHEDULED,
            self::STATUS_INTERVIEW_COMPLETED,
            self::STATUS_OFFERED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ];
    }

    private function sendStatusUpdateNotification(array $application, string $status, string $reason): void
    {
        // Mock email sending
        error_log("Status update notification sent to {$application['email']}: {$status}, Reason: {$reason}");
    }

    private function validateInterviewData(array $data): array
    {
        $errors = [];

        if (empty($data['type']) || !in_array($data['type'], ['phone', 'video', 'in_person'])) {
            $errors[] = 'Valid interview type is required';
        }

        if (empty($data['scheduled_date'])) {
            $errors[] = 'Interview date is required';
        }

        if (empty($data['interviewer_name'])) {
            $errors[] = 'Interviewer name is required';
        }

        if (empty($data['interviewer_email']) || !filter_var($data['interviewer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid interviewer email is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function createInterviewRecord(int $applicationId, array $interviewData): string
    {
        $sql = "INSERT INTO application_interviews 
                (application_id, type, scheduled_date, duration_minutes, interviewer_name, interviewer_email, meeting_link, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $this->db->execute($sql, [
            $applicationId,
            $interviewData['type'],
            $interviewData['scheduled_date'],
            $interviewData['duration_minutes'] ?? 60,
            $interviewData['interviewer_name'],
            $interviewData['interviewer_email'],
            $interviewData['meeting_link'] ?? null,
            $interviewData['notes'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    private function sendInterviewInvitation(int $applicationId, array $interviewData): void
    {
        // Mock email sending
        error_log("Interview invitation sent for application ID {$applicationId}, Type {$interviewData['type']}, Date {$interviewData['scheduled_date']}");
    }
}
