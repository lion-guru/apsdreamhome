<?php
/**
 * Process Visit Scheduling Form
 * 
 * Handles the submission of the property visit scheduling form.
 * Validates input, saves to database, and sends notifications.
 *
 * @package APS Dream Homes
 * @since 1.0.0
 */

// Include necessary files
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/config/DatabaseConfig.php';
require_once __DIR__ . '/includes/config/constants.php';

// Start session and include required files
require_once __DIR__ . '/includes/functions.php';

// Start secure session
start_secure_session('aps_dream_home');

// Set default response
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    send_json_response($response, 405);
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $response['message'] = 'Invalid or expired CSRF token. Please refresh the page and try again.';
    send_json_response($response, 403);
}

// Validate required fields
$required_fields = [
    'property_id' => 'Property ID',
    'visit_date' => 'Visit date',
    'visit_time' => 'Visit time',
    'visitor_name' => 'Your name',
    'visitor_email' => 'Email address',
    'visitor_phone' => 'Phone number'
];

$errors = [];
$input = [];

foreach ($required_fields as $field => $label) {
    if (empty(trim($_POST[$field] ?? ''))) {
        $errors[$field] = "$label is required.";
    } else {
        $input[$field] = trim($_POST[$field]);
    }
}

// Validate email
if (!filter_var($input['visitor_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
    $errors['visitor_email'] = 'Please enter a valid email address.';
}

// Validate phone number (basic validation)
if (!preg_match('/^[0-9\-\+\(\)\s]{10,20}$/', $input['visitor_phone'] ?? '')) {
    $errors['visitor_phone'] = 'Please enter a valid phone number.';
}

// Validate date format and ensure it's in the future
$visit_date = DateTime::createFromFormat('Y-m-d', $input['visit_date'] ?? '');
$today = new DateTime('today');

if (!$visit_date) {
    $errors['visit_date'] = 'Invalid date format.';
} elseif ($visit_date < $today) {
    $errors['visit_date'] = 'Visit date must be today or in the future.';
}

// Validate time format
$visit_time = DateTime::createFromFormat('H:i', $input['visit_time'] ?? '');
if (!$visit_time) {
    $errors['visit_time'] = 'Invalid time format.';
}

// If there are validation errors, return them
if (!empty($errors)) {
    $response['errors'] = $errors;
    $response['message'] = 'Please correct the errors below.';
    send_json_response($response, 422);
}

try {
    // Get database connection
    $db = DatabaseConfig::getConnection();
    
    // Begin transaction
    $db->begin_transaction();
    
    // 1. Get property details
    $property_id = (int)$input['property_id'];
    $property_query = "SELECT p.*, u.id as agent_id, u.email as agent_email, u.name as agent_name 
                      FROM properties p 
                      LEFT JOIN users u ON p.agent_id = u.id 
                      WHERE p.id = ? AND p.status = 'active' 
                      LIMIT 1";
    
    $stmt = $db->prepare($property_query);
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $property_result = $stmt->get_result();
    
    if ($property_result->num_rows === 0) {
        throw new Exception('Property not found or not available for visits.');
    }
    
    $property = $property_result->fetch_assoc();
    
    // 2. Check if the selected time slot is available
    $visit_datetime = $visit_date->format('Y-m-d') . ' ' . $visit_time->format('H:i:00');
    
    $availability_query = "SELECT * FROM property_visits 
                          WHERE property_id = ? 
                          AND visit_datetime = ? 
                          AND status NOT IN ('cancelled', 'completed')";
    
    $stmt = $db->prepare($availability_query);
    $stmt->bind_param('is', $property_id, $visit_datetime);
    $stmt->execute();
    $existing_visit = $stmt->get_result()->fetch_assoc();
    
    if ($existing_visit) {
        $errors['visit_time'] = 'The selected time slot is no longer available. Please choose another time.';
        $response['errors'] = $errors;
        $response['message'] = 'The selected time slot is no longer available.';
        send_json_response($response, 422);
    }
    
    // 3. Create visit record
    $visitor_name = $input['visitor_name'];
    $visitor_email = $input['visitor_email'];
    $visitor_phone = $input['visitor_phone'];
    $notes = $input['visit_notes'] ?? '';
    $status = 'scheduled';
    $created_at = date('Y-m-d H:i:s');
    $token = bin2hex(random_bytes(16));
    
    $insert_query = "INSERT INTO property_visits 
                    (property_id, agent_id, visitor_name, visitor_email, visitor_phone, 
                     visit_datetime, status, notes, token, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bind_param('iisssssssss', 
        $property_id,
        $property['agent_id'],
        $visitor_name,
        $visitor_email,
        $visitor_phone,
        $visit_datetime,
        $status,
        $notes,
        $token,
        $created_at,
        $created_at
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to schedule visit. Please try again.');
    }
    
    $visit_id = $db->insert_id;
    
    // 4. Create notification for agent
    $notification_type = 'new_visit';
    $notification_message = "New visit scheduled for {$property['title']} on " . $visit_date->format('M j, Y') . " at " . $visit_time->format('g:i A');
    $notification_link = "/admin/visits.php?action=view&id={$visit_id}";
    
    $notification_query = "INSERT INTO notifications 
                          (user_id, type, message, link, is_read, created_at, updated_at)
                          VALUES (?, ?, ?, ?, 0, NOW(), NOW())";
    
    $stmt = $db->prepare($notification_query);
    $stmt->bind_param('isss', $property['agent_id'], $notification_type, $notification_message, $notification_link);
    $stmt->execute();
    
    // 5. Send confirmation email to visitor
    $to = $visitor_email;
    $subject = "Visit Scheduled: {$property['title']}";
    
    $email_content = "
        <h2>Your Property Visit Has Been Scheduled</h2>
        <p>Hello {$visitor_name},</p>
        <p>Your visit has been successfully scheduled with the following details:</p>
        
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
            <h3 style='margin-top: 0;'>{$property['title']}</h3>
            <p><strong>Date:</strong> {$visit_date->format('l, F j, Y')}</p>
            <p><strong>Time:</strong> {$visit_time->format('g:i A')}</p>
            <p><strong>Address:</strong> {$property['address']}, {$property['city']}, {$property['state']} {$property['zip_code']}</p>
            <p><strong>Agent:</strong> {$property['agent_name']}</p>
        </div>
        
        <p>You can manage your visit using the following link:</p>
        <p><a href='" . SITE_URL . "/my-visits.php?token={$token}' style='display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Manage Visit</a></p>
        
        <p>If you need to reschedule or cancel your visit, please contact us at <a href='mailto:" . SITE_EMAIL . "'>" . SITE_EMAIL . "</a> or call us at " . SITE_PHONE . ".</p>
        
        <p>Thank you for choosing " . SITE_NAME . "!</p>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . SITE_NAME . ' <' . SITE_EMAIL . '>',
        'Reply-To: ' . SITE_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Uncomment to send email in production
    // mail($to, $subject, $email_content, implode("\r\n", $headers));
    
    // 6. Commit transaction
    $db->commit();
    
    // Log successful visit scheduling
    error_log("Visit scheduled successfully - Visit ID: {$visit_id}, Property ID: {$property_id}");
    
    // Return success response
    $response['success'] = true;
    $response['message'] = 'Your visit has been scheduled successfully!';
    $response['visit_id'] = $visit_id;
    
    send_json_response($response);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db) && $db instanceof mysqli) {
        $db->rollback();
    }
    
    // Log error
    error_log('Error scheduling visit: ' . $e->getMessage());
    
    // Return error response
    $response['message'] = 'An error occurred while scheduling your visit. Please try again later.';
    
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        $response['debug'] = $e->getMessage();
    }
    
    send_json_response($response, 500);
}

/**
 * Send JSON response and exit
 * 
 * @param array $data Response data
 * @param int $status_code HTTP status code
 * @return void
 */
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
