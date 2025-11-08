<?php
/**
 * API - Inquiry Submission Endpoint
 * Handles property inquiry submissions from mobile app
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    // Validate required fields
    $required_fields = ['property_id', 'name', 'email', 'phone', 'message'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $errors[] = "Field '{$field}' is required";
        }
    }

    if (!empty($errors)) {
        sendJsonResponse([
            'success' => false,
            'error' => 'Validation failed',
            'validation_errors' => $errors
        ], 400);
    }

    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse([
            'success' => false,
            'error' => 'Invalid email format'
        ], 400);
    }

    // Validate phone number (basic validation)
    $phone = preg_replace('/\D/', '', $input['phone']);
    if (strlen($phone) < 10) {
        sendJsonResponse([
            'success' => false,
            'error' => 'Invalid phone number'
        ], 400);
    }

    // Check if property exists
    $property_sql = "SELECT id, title FROM properties WHERE id = ? AND status = 'available'";
    $property_stmt = $pdo->prepare($property_sql);
    $property_stmt->execute([$input['property_id']]);
    $property = $property_stmt->fetch();

    if (!$property) {
        sendJsonResponse([
            'success' => false,
            'error' => 'Property not found or not available'
        ], 404);
    }

    // Create inquiry
    $inquiry_data = [
        'property_id' => $input['property_id'],
        'guest_name' => trim($input['name']),
        'guest_email' => trim($input['email']),
        'guest_phone' => trim($input['phone']),
        'message' => trim($input['message']),
        'inquiry_type' => $input['inquiry_type'] ?? 'general',
        'status' => 'new'
    ];

    $insert_sql = "INSERT INTO property_inquiries
                   (property_id, guest_name, guest_email, guest_phone, message, inquiry_type, status, created_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->execute([
        $inquiry_data['property_id'],
        $inquiry_data['guest_name'],
        $inquiry_data['guest_email'],
        $inquiry_data['guest_phone'],
        $inquiry_data['message'],
        $inquiry_data['inquiry_type'],
        $inquiry_data['status']
    ]);

    $inquiry_id = $pdo->lastInsertId();

    // Send email notification if email system is available
    try {
        if (class_exists('\App\Core\EmailNotification')) {
            $emailNotification = new \App\Core\EmailNotification();
            $emailNotification->sendInquiryNotification($inquiry_id);
        }
    } catch (Exception $e) {
        error_log('Email notification failed: ' . $e->getMessage());
        // Don't fail the inquiry if email fails
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Inquiry submitted successfully',
        'data' => [
            'inquiry_id' => $inquiry_id,
            'property_title' => $property['title'],
            'submitted_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    error_log('API Inquiry Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
