<?php
/**
 * Job Application Form Handler - APS Dream Homes
 * Handles job applications and career inquiries
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/db_connection.php';
        $pdo = getMysqliConnection();

        // Get form data
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $cover_letter = trim($_POST['cover_letter'] ?? '');
        $availability = trim($_POST['availability'] ?? '');
        $resume_file = $_FILES['resume'] ?? null;

        // Validation
        $errors = [];

        if (empty($full_name)) $errors[] = 'Full name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($position) || $position === 'Select Position') $errors[] = 'Position selection is required';

        // Resume file validation
        if (!$resume_file || $resume_file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Resume file is required';
        } else {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($resume_file['type'], $allowed_types)) {
                $errors[] = 'Resume must be PDF, DOC, or DOCX format';
            }

            if ($resume_file['size'] > $max_size) {
                $errors[] = 'Resume file size must be less than 5MB';
            }
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Handle file upload
        $upload_dir = '../uploads/resumes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($resume_file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('resume_') . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($resume_file['tmp_name'], $file_path)) {
            throw new Exception('Failed to upload resume file');
        }

        // Insert application
        $stmt = $pdo->prepare("
            INSERT INTO job_applications
            (full_name, email, phone, position, experience, cover_letter, availability,
             resume_file, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')
        ");

        $stmt->execute([
            $full_name, $email, $phone, $position, $experience,
            $cover_letter, $availability, $file_name
        ]);

        // Send email notification
        $to = 'careers@apsdreamhomes.com'; // Change this to your email
        $email_subject = 'New Job Application - APS Dream Homes';
        $email_body = "
New job application received:

Name: $full_name
Email: $email
Phone: $phone
Position: $position
Experience: $experience
Availability: $availability

Cover Letter:
$cover_letter

Resume: Available at /uploads/resumes/$file_name
        ";

        $headers = "From: noreply@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        // Uncomment to enable email sending
        // mail($to, $email_subject, $email_body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your application! We will review it and get back to you soon.'
        ]);

    } catch (Exception $e) {
        error_log('Job application error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
