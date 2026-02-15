<?php
/**
 * Contact Form Handler - APS Dream Homes
 * Handles all contact form submissions
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/db_connection.php';
        $pdo = getMysqliConnection();

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $form_type = trim($_POST['form_type'] ?? 'general');

        // Validation
        $errors = [];

        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($subject)) $errors[] = 'Subject is required';
        if (empty($message)) $errors[] = 'Message is required';

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Additional data for specific form types
        $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : null;
        $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
        $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : null;

        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO contact_inquiries
            (name, email, phone, subject, message, form_type, property_id, service_type, job_id, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')
        ");

        $stmt->execute([$name, $email, $phone, $subject, $message, $form_type, $property_id, $service_type, $job_id]);

        // Send email notification (you can customize this)
        $to = 'info@apsdreamhomes.com'; // Change this to your email
        $email_subject = 'New Contact Inquiry - APS Dream Homes';
        $email_body = "
New inquiry received:

Name: $name
Email: $email
Phone: $phone
Subject: $subject
Form Type: $form_type
Message: $message

Property ID: " . ($property_id ?? 'N/A') . "
Service Type: " . ($service_type ?? 'N/A') . "
Job ID: " . ($job_id ?? 'N/A') . "
        ";

        $headers = "From: noreply@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        // Uncomment the line below to enable email sending
        // mail($to, $email_subject, $email_body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your inquiry! We will get back to you soon.'
        ]);

    } catch (Exception $e) {
        error_log('Contact form error: ' . $e->getMessage());
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
