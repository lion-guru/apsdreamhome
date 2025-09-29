<?php
require_once __DIR__ . '/includes/db_settings.php';
require_once __DIR__ . '/includes/notification_manager.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Validate and sanitize input
$name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$visit_date = htmlspecialchars(trim($_POST['visit_date'] ?? ''), ENT_QUOTES, 'UTF-8');
$visit_time = htmlspecialchars(trim($_POST['visit_time'] ?? ''), ENT_QUOTES, 'UTF-8');
$notes = htmlspecialchars(trim($_POST['notes'] ?? ''), ENT_QUOTES, 'UTF-8');

// Additional validation
if (!$name || strlen($name) < 2) {
    $response['message'] = 'Please enter a valid name';
    sendResponse($response, $isAjax);
}

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address';
    sendResponse($response, $isAjax);
}

if (!$phone || strlen($phone) < 10) {
    $response['message'] = 'Please enter a valid phone number';
    sendResponse($response, $isAjax);
}

if (!$property_id || $property_id <= 0) {
    $response['message'] = 'Invalid property selected';
    sendResponse($response, $isAjax);
}

if (!$visit_date || !strtotime($visit_date)) {
    $response['message'] = 'Please select a valid visit date';
    sendResponse($response, $isAjax);
}

if (!$visit_time || !preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $visit_time)) {
    $response['message'] = 'Please select a valid visit time';
    sendResponse($response, $isAjax);
}

// Validate date is not in the past
$visit_datetime = strtotime("$visit_date $visit_time");
if ($visit_datetime < strtotime('today')) {
    $response['message'] = 'Please select a future date';
    sendResponse($response, $isAjax);
}

// Helper function to send response
function sendResponse($response, $isAjax) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        if ($response['success']) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?success=' . urlencode($response['message']));
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?error=' . urlencode($response['message']));
        }
    }
    exit;
}

// Get database connection
$conn = get_db_connection();
if (!$conn) {
    $response['message'] = 'Database connection failed';
    sendResponse($response, $isAjax);
}

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Create customer if doesn't exist
    $stmt = $conn->prepare("INSERT IGNORE INTO customers (name, email, phone, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('sss', $name, $email, $phone);
    $stmt->execute();
    $customer_id = $conn->insert_id ?: $conn->query("SELECT id FROM customers WHERE email = '$email' LIMIT 1")->fetch_object()->id;
    
    // Get property details
    $stmt = $conn->prepare("SELECT p.title, p.owner_id, CONCAT(u.first_name, ' ', u.last_name) as agent_name FROM properties p LEFT JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $property_result = $stmt->get_result()->fetch_object();
    $property_title = $property_result->title ?? 'Unknown Property';
    $agent_id = $property_result->owner_id;
    $agent_name = $property_result->agent_name;
    
    // Check if there's already a visit scheduled at this time
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM property_visits WHERE property_id = ? AND visit_date = ? AND visit_time = ? AND status != 'cancelled'");
    $stmt->bind_param('iss', $property_id, $visit_date, $visit_time);
    $stmt->execute();
    if ($stmt->get_result()->fetch_object()->count > 0) {
        throw new Exception('This time slot is already booked. Please select another time.');
    }
    
    // Schedule the visit
    $stmt = $conn->prepare("INSERT INTO property_visits (customer_id, property_id, visit_date, visit_time, notes, status, created_at) VALUES (?, ?, ?, ?, ?, 'scheduled', NOW())");
    $stmt->bind_param('iisss', $customer_id, $property_id, $visit_date, $visit_time, $notes);
    $stmt->execute();
    $visit_id = $conn->insert_id;
    
    // Create a lead for this visit
    $stmt = $conn->prepare("INSERT INTO leads (customer_id, property_id, source, status, notes) VALUES (?, ?, 'visit_schedule', 'new', ?)");
    $lead_notes = "Visit scheduled for $visit_date at $visit_time. " . ($notes ? "Notes: $notes" : "");
    $stmt->bind_param('iis', $customer_id, $property_id, $lead_notes);
    $stmt->execute();
    $lead_id = $conn->insert_id;
    
    // Link visit to lead
    $stmt = $conn->prepare("UPDATE property_visits SET lead_id = ? WHERE id = ?");
    $stmt->bind_param('ii', $lead_id, $visit_id);
    $stmt->execute();
    
    // Send notification to agent
    if ($agent_id) {
        $notification_data = [
            'type' => 'visit_scheduled',
            'user_id' => $agent_id,
            'title' => 'New Visit Scheduled',
            'message' => "Visit scheduled by $name for $property_title on $visit_date at $visit_time",
            'link' => "/admin/visits.php?id=$visit_id"
        ];
        $notification = new NotificationManager();
        $notification->send($notification_data);
    }
    
    // Send confirmation email to customer
    $to = $email;
    $subject = "Visit Confirmation - $property_title";
    $message = "Dear $name,\n\n";
    $message .= "Your visit has been scheduled for $property_title on $visit_date at $visit_time.\n\n";
    $message .= "Property Details:\n";
    $message .= "Title: $property_title\n";
    $message .= "Agent: $agent_name\n\n";
    $message .= "Visit Details:\n";
    $message .= "Date: $visit_date\n";
    $message .= "Time: $visit_time\n\n";
    if ($notes) {
        $message .= "Your Notes: $notes\n\n";
    }
    $message .= "If you need to reschedule or cancel your visit, please contact us.\n\n";
    $message .= "Best regards,\nAPS Dream Homes Team";
    
    $headers = "From: noreply@apsdreamhomes.com\r\n";
    $headers .= "Reply-To: $agent_name <$email>\r\n";
    mail($to, $subject, $message, $headers);
    
    // Commit transaction
    $conn->commit();
    
    // Send success response
    $response['success'] = true;
    $response['message'] = 'Your visit has been scheduled successfully! Check your email for confirmation.';
    sendResponse($response, $isAjax);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Send error response
    $response['message'] = $e->getMessage();
    sendResponse($response, $isAjax);
}
