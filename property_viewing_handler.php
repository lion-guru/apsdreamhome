<?php
/**
 * Property Viewing Request Handler - APS Dream Homes
 * Handles property viewing appointment requests
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/db_connection.php';
        $pdo = getDbConnection();

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $property_id = (int)($_POST['property_id'] ?? 0);
        $property_title = trim($_POST['property_title'] ?? '');
        $preferred_date = trim($_POST['preferred_date'] ?? '');
        $preferred_time = trim($_POST['preferred_time'] ?? '');
        $alternate_date = trim($_POST['alternate_date'] ?? '');
        $special_requests = trim($_POST['special_requests'] ?? '');
        $buyer_type = trim($_POST['buyer_type'] ?? '');
        $budget_range = trim($_POST['budget_range'] ?? '');
        $financing_needed = isset($_POST['financing_needed']) ? 1 : 0;

        // Validation
        $errors = [];

        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($property_id) && empty($property_title)) $errors[] = 'Property selection is required';
        if (empty($preferred_date)) $errors[] = 'Preferred date is required';
        if (empty($preferred_time)) $errors[] = 'Preferred time is required';
        if (empty($buyer_type)) $errors[] = 'Buyer type is required';

        // Validate date is not in the past
        if (!empty($preferred_date)) {
            $selected_date = strtotime($preferred_date);
            $today = strtotime(date('Y-m-d'));
            if ($selected_date < $today) {
                $errors[] = 'Preferred date cannot be in the past';
            }
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Insert viewing request
        $stmt = $pdo->prepare("
            INSERT INTO property_viewings
            (name, email, phone, property_id, property_title, preferred_date, preferred_time,
             alternate_date, special_requests, buyer_type, budget_range, financing_needed,
             status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $name, $email, $phone, $property_id, $property_title, $preferred_date,
            $preferred_time, $alternate_date, $special_requests, $buyer_type,
            $budget_range, $financing_needed
        ]);

        // Send email notification
        $to = 'viewings@apsdreamhomes.com';
        $email_subject = 'New Property Viewing Request - APS Dream Homes';
        $email_body = "
New property viewing request received:

Client Details:
Name: $name
Email: $email
Phone: $phone
Buyer Type: $buyer_type
Budget Range: $budget_range
Financing Needed: " . ($financing_needed ? 'Yes' : 'No') . "

Viewing Request:
Property: " . ($property_title ?: 'Property ID: ' . $property_id) . "
Preferred Date: $preferred_date
Preferred Time: $preferred_time
Alternate Date: " . ($alternate_date ?: 'Not specified') . "
Special Requests: " . ($special_requests ?: 'None') . "
        ";

        $headers = "From: noreply@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your viewing request! Our team will confirm your appointment within 24 hours.'
        ]);

    } catch (Exception $e) {
        error_log('Property viewing request error: ' . $e->getMessage());
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
