<?php

namespace App\Services\Legacy;
/**
 * Career and Job Application Management System
 */

class CareerManager {
    private $db;
    private $emailProcessor;
    private $allowedExtensions = ['pdf', 'doc', 'docx'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        require_once __DIR__ . '/EmailProcessor.php';
        $this->emailProcessor = new EmailProcessor($this->db);
    }

    /**
     * Submit a job application
     */
    public function submitApplication($data, $files) {
        $full_name = trim($data['full_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $position = trim($data['position'] ?? '');
        $experience = trim($data['experience'] ?? '');
        $cover_letter = trim($data['cover_letter'] ?? '');
        $availability = trim($data['availability'] ?? '');
        $resume_file = $files['resume'] ?? null;

        // Validation
        $errors = [];
        if (empty($full_name)) $errors[] = 'Full name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($position) || $position === 'Select Position') $errors[] = 'Position selection is required';

        // Resume file validation
        if (!$resume_file || $resume_file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Resume file is required';
        } else {
            $file_extension = strtolower(pathinfo($resume_file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $this->allowedExtensions)) {
                $errors[] = 'Resume must be PDF, DOC, or DOCX format';
            }
            if ($resume_file['size'] > $this->maxFileSize) {
                $errors[] = 'Resume file size must be less than 5MB';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        // Handle file upload
        $upload_dir = dirname(__DIR__) . '/uploads/resumes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($resume_file['name'], PATHINFO_EXTENSION);
        $file_name = 'resume_' . \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($resume_file['tmp_name'], $file_path)) {
            throw new \Exception('Failed to upload resume file');
        }

        // Insert application
        $sql = "INSERT INTO job_applications
                (full_name, email, phone, position, experience, cover_letter, availability, resume_file, created_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

        $this->db->execute($sql, [$full_name, $email, $phone, $position, $experience, $cover_letter, $availability, $file_name]);

        // Send email notification
        $this->sendApplicationNotification($full_name, $email, $phone, $position, $experience, $availability, $cover_letter, $file_name);

        return true;
    }

    private function sendApplicationNotification($name, $email, $phone, $position, $exp, $avail, $letter, $file) {
        $to = 'careers@apsdreamhomes.com';
        $subject = 'New Job Application - APS Dream Homes';
        $body = "
            <h2>New job application received:</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Position:</strong> $position</p>
            <p><strong>Experience:</strong> $exp</p>
            <p><strong>Availability:</strong> $avail</p>
            <p><strong>Cover Letter:</strong><br>$letter</p>
            <p><strong>Resume:</strong> Available in uploads/resumes/$file</p>
        ";

        try {
            $this->emailProcessor->sendEmail($to, $subject, $body);
        } catch (\Exception $e) {
            error_log("Failed to send application notification: " . $e->getMessage());
        }
    }
}
