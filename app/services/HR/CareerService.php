<?php

namespace App\Services\HR;

use App\Core\Database\Database;
use App\Services\LoggingService;
use App\Services\EmailService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Career and Job Application Management Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class CareerService
{
    private $db;
    private $logger;
    private $allowedExtensions = ['pdf', 'doc', 'docx'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $uploadDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new LoggingService();
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/resumes/';
        $this->ensureUploadDirectory();
    }

    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory()
    {
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Submit a job application
     */
    public function submitApplication($data, $files)
    {
        try {
            // Validate and sanitize input
            $applicationData = $this->validateApplicationData($data, $files);

            // Handle file upload
            $resumeFile = $this->handleResumeUpload($files['resume'] ?? null);

            // Insert application into database
            $applicationId = $this->createApplicationRecord($applicationData, $resumeFile);

            // Send notifications
            $this->sendApplicationNotifications($applicationData, $resumeFile);

            // Log the application
            $this->logger->info('Job application submitted', [
                'application_id' => $applicationId,
                'email' => $applicationData['email'],
                'position' => $applicationData['position']
            ]);

            return [
                'success' => true,
                'application_id' => $applicationId,
                'message' => 'Application submitted successfully'
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to submit job application', [
                'email' => $data['email'] ?? '',
                'position' => $data['position'] ?? '',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate application data
     */
    private function validateApplicationData($data, $files)
    {
        $errors = [];

        // Required fields validation
        $requiredFields = ['full_name', 'email', 'phone', 'position'];
        foreach ($requiredFields as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        // Email validation
        $email = trim($data['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }

        // Phone validation
        $phone = trim($data['phone'] ?? '');
        if (!empty($phone) && !$this->isValidPhone($phone)) {
            $errors[] = 'Valid phone number is required';
        }

        // Position validation
        $position = trim($data['position'] ?? '');
        if (!empty($position) && $position === 'Select Position') {
            $errors[] = 'Please select a valid position';
        }

        // Resume file validation
        $resumeFile = $files['resume'] ?? null;
        if (!$resumeFile || $resumeFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Resume file is required';
        } else {
            $fileErrors = $this->validateResumeFile($resumeFile);
            $errors = array_merge($errors, $fileErrors);
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        // Sanitize data
        return [
            'full_name' => $this->sanitizeString($data['full_name']),
            'email' => $this->sanitizeEmail($data['email']),
            'phone' => $this->sanitizePhone($data['phone']),
            'position' => $this->sanitizeString($data['position']),
            'experience' => $this->sanitizeString($data['experience'] ?? ''),
            'cover_letter' => $this->sanitizeString($data['cover_letter'] ?? ''),
            'availability' => $this->sanitizeString($data['availability'] ?? ''),
            'salary_expectation' => $this->sanitizeString($data['salary_expectation'] ?? ''),
            'location' => $this->sanitizeString($data['location'] ?? '')
        ];
    }

    /**
     * Validate resume file
     */
    private function validateResumeFile($file)
    {
        $errors = [];

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $errors[] = 'Resume file size must be less than 5MB';
        }

        // Check file extension
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $this->allowedExtensions)) {
            $errors[] = 'Resume must be PDF, DOC, or DOCX format';
        }

        // Security checks
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload source';
        }

        // Additional MIME type check
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $errors[] = 'Invalid file type detected';
        }

        return $errors;
    }

    /**
     * Handle resume file upload
     */
    private function handleResumeUpload($file)
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Resume file is required');
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Generate secure filename
        $fileName = 'resume_' . bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $filePath = $this->uploadDir . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new RuntimeException('Failed to upload resume file');
        }

        return $fileName;
    }

    /**
     * Create application record in database
     */
    private function createApplicationRecord($data, $resumeFile)
    {
        $sql = "INSERT INTO job_applications 
                (full_name, email, phone, position, experience, cover_letter, availability, 
                 salary_expectation, location, resume_file, created_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

        $params = [
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['position'],
            $data['experience'],
            $data['cover_letter'],
            $data['availability'],
            $data['salary_expectation'],
            $data['location'],
            $resumeFile
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Send application notifications
     */
    private function sendApplicationNotifications($data, $resumeFile)
    {
        try {
            // Send to HR team
            $this->sendHRNotification($data, $resumeFile);

            // Send confirmation to applicant
            $this->sendApplicantConfirmation($data);
        } catch (Exception $e) {
            $this->logger->error('Failed to send application notifications', [
                'email' => $data['email'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to HR team
     */
    private function sendHRNotification($data, $resumeFile)
    {
        $to = 'careers@apsdreamhomes.com';
        $subject = 'New Job Application - APS Dream Homes';

        $body = $this->generateHRNotificationEmail($data, $resumeFile);

        // Send email using email service
        $emailService = new EmailService();
        $emailService->send($to, $subject, $body);
    }

    /**
     * Send confirmation to applicant
     */
    private function sendApplicantConfirmation($data)
    {
        $to = $data['email'];
        $subject = 'Application Received - APS Dream Homes';

        $body = $this->generateApplicantConfirmationEmail($data);

        // Send email using email service
        $emailService = new EmailService();
        $emailService->send($to, $subject, $body);
    }

    /**
     * Generate HR notification email
     */
    private function generateHRNotificationEmail($data, $resumeFile)
    {
        return "
            <h2>New job application received:</h2>
            <p><strong>Name:</strong> {$data['full_name']}</p>
            <p><strong>Email:</strong> {$data['email']}</p>
            <p><strong>Phone:</strong> {$data['phone']}</p>
            <p><strong>Position:</strong> {$data['position']}</p>
            <p><strong>Experience:</strong> {$data['experience']}</p>
            <p><strong>Availability:</strong> {$data['availability']}</p>
            <p><strong>Salary Expectation:</strong> {$data['salary_expectation']}</p>
            <p><strong>Location:</strong> {$data['location']}</p>
            <p><strong>Cover Letter:</strong><br>" . nl2br($data['cover_letter']) . "</p>
            <p><strong>Resume:</strong> Available in uploads/resumes/$resumeFile</p>
            <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
    }

    /**
     * Generate applicant confirmation email
     */
    private function generateApplicantConfirmationEmail($data)
    {
        return "
            <h2>Application Received - APS Dream Homes</h2>
            <p>Dear {$data['full_name']},</p>
            <p>Thank you for your interest in the <strong>{$data['position']}</strong> position at APS Dream Homes.</p>
            <p>We have received your application and will review it carefully. Our HR team will contact you if your profile matches our requirements.</p>
            <p><strong>Application Details:</strong></p>
            <ul>
                <li>Position: {$data['position']}</li>
                <li>Email: {$data['email']}</li>
                <li>Phone: {$data['phone']}</li>
                <li>Submitted: " . date('Y-m-d H:i:s') . "</li>
            </ul>
            <p>For any inquiries, please contact us at careers@apsdreamhomes.com</p>
            <p>Best regards,<br>APS Dream Homes HR Team</p>
        ";
    }

    /**
     * Get applications by status
     */
    public function getApplicationsByStatus($status = null, $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM job_applications";
            $params = [];

            if ($status) {
                $sql .= " WHERE status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->error('Failed to get applications by status', [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get application by ID
     */
    public function getApplicationById($id)
    {
        try {
            $result = $this->db->fetchOne(
                "SELECT * FROM job_applications WHERE id = ?",
                [$id]
            );
            return $result;
        } catch (Exception $e) {
            $this->logger->error('Failed to get application by ID', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus($id, $status, $notes = '')
    {
        try {
            $validStatuses = ['pending', 'reviewing', 'shortlisted', 'rejected', 'hired'];
            if (!in_array($status, $validStatuses)) {
                throw new InvalidArgumentException('Invalid application status');
            }

            $sql = "UPDATE job_applications SET status = ?, updated_at = NOW()";
            $params = [$status];

            if (!empty($notes)) {
                $sql .= ", notes = ?";
                $params[] = $notes;
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $this->db->query($sql, $params);

            $this->logger->info('Application status updated', [
                'application_id' => $id,
                'status' => $status,
                'notes' => $notes
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to update application status', [
                'application_id' => $id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get application statistics
     */
    public function getApplicationStats()
    {
        try {
            $stats = [];

            // Total applications
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM job_applications");
            $stats['total_applications'] = $result['count'] ?? 0;

            // Applications by status
            $stats['by_status'] = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM job_applications GROUP BY status"
            );

            // Applications by position
            $stats['by_position'] = $this->db->fetchAll(
                "SELECT position, COUNT(*) as count FROM job_applications GROUP BY position ORDER BY count DESC"
            );

            // Recent applications (last 30 days)
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM job_applications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stats['recent_applications'] = $result['count'] ?? 0;

            return $stats;
        } catch (Exception $e) {
            $this->logger->error('Failed to get application statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Validate phone number
     */
    private function isValidPhone($phone)
    {
        // Remove all non-digit characters
        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Check if it's a valid phone number (10-15 digits)
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString($input)
    {
        return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Sanitize email
     */
    private function sanitizeEmail($email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize phone number
     */
    private function sanitizePhone($phone)
    {
        // Remove all non-digit characters except + for international numbers
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }

    /**
     * Delete application
     */
    public function deleteApplication($id)
    {
        try {
            // Get application details first
            $application = $this->getApplicationById($id);
            if (!$application) {
                throw new InvalidArgumentException('Application not found');
            }

            // Delete resume file if exists
            if (!empty($application['resume_file'])) {
                $resumePath = $this->uploadDir . $application['resume_file'];
                if (file_exists($resumePath)) {
                    unlink($resumePath);
                }
            }

            // Delete application record
            $this->db->query("DELETE FROM job_applications WHERE id = ?", [$id]);

            $this->logger->info('Application deleted', [
                'application_id' => $id,
                'email' => $application['email']
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to delete application', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
