<?php
/**
 * Property Inquiry Form Handler - APS Dream Homes
 * Handles property-specific inquiries
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
        $property_id = (int)($_POST['property_id'] ?? 0);
        $inquiry_type = trim($_POST['inquiry_type'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $budget_min = isset($_POST['budget_min']) ? (float)$_POST['budget_min'] : null;
        $budget_max = isset($_POST['budget_max']) ? (float)$_POST['budget_max'] : null;
        $preferred_location = trim($_POST['preferred_location'] ?? '');

        // Validation
        $errors = [];

        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($property_id)) $errors[] = 'Property selection is required';
        if (empty($inquiry_type)) $errors[] = 'Inquiry type is required';

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Get property details for reference
        $property_stmt = $pdo->prepare("SELECT title, price FROM properties WHERE id = ? AND status = 'active'");
        $property_stmt->execute([$property_id]);
        $property = $property_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Property not found']);
            exit;
        }

        // Insert inquiry
        $stmt = $pdo->prepare("
            INSERT INTO property_inquiries
            (name, email, phone, property_id, inquiry_type, message, budget_min, budget_max,
             preferred_location, property_title, property_price, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')
        ");

        $stmt->execute([
            $name, $email, $phone, $property_id, $inquiry_type, $message,
            $budget_min, $budget_max, $preferred_location,
            $property['title'], $property['price']
        ]);

        // Send email notification
        $to = 'info@apsdreamhomes.com'; // Change this to your email
        $email_subject = 'New Property Inquiry - APS Dream Homes';
        $email_body = "
New property inquiry received:

Name: $name
Email: $email
Phone: $phone
Property: {$property['title']}
Inquiry Type: $inquiry_type
Budget Range: " . ($budget_min ? '₹' . number_format($budget_min) : 'Not specified') .
               ($budget_max ? ' - ₹' . number_format($budget_max) : '') . "
Preferred Location: " . ($preferred_location ?: 'Not specified') . "
Message: $message
        ";

        $headers = "From: noreply@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        // Uncomment to enable email sending
        // mail($to, $email_subject, $email_body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your property inquiry! Our team will contact you soon.'
        ]);

    } catch (Exception $e) {
        error_log('Property inquiry error: ' . $e->getMessage());
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
