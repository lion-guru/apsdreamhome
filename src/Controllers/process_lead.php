<?php
require_once __DIR__ . '/includes/db_settings.php';
require_once __DIR__ . '/includes/notification_manager.php';

// Helper functions
function sendResponse($response, $isAjax)
{
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

function send_notification($data)
{
    try {
        $notification = new NotificationManager();
        return $notification->send($data);
    } catch (Exception $e) {
        error_log('Notification error: ' . $e->getMessage());
        return false;
    }
}

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
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);

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

if (!$message || strlen($message) < 10) {
    $response['message'] = 'Please enter a detailed message (minimum 10 characters)';
    sendResponse($response, $isAjax);
}

if (!$property_id || $property_id <= 0) {
    $response['message'] = 'Invalid property selected';
    sendResponse($response, $isAjax);
}

// Get database connection
require_once __DIR__ . '/../../includes/db_connection.php';
$pdo = getPdoConnection();
if (!$pdo) {
    $response['message'] = 'Database connection failed';
    sendResponse($response, $isAjax);
}

// Duplicate sendResponse function removed

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Insert into leads table
    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, notes) VALUES (:name, :email, :phone, 'property_inquiry', 'new', :notes)");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'notes' => $message
    ]);
    $lead_id = $pdo->lastInsertId();

    // Create customer if doesn't exist
    $stmt = $pdo->prepare("INSERT IGNORE INTO customers (name, email, phone, created_at) VALUES (:name, :email, :phone, NOW())");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone
    ]);
    $customer_id = $pdo->lastInsertId();

    if (!$customer_id) {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $customer_id = $stmt->fetchColumn();
    }

    // Link lead to property
    $stmt = $pdo->prepare("INSERT INTO customer_journeys (customer_id, lead_id, property_id, interaction_type, notes) VALUES (:customer_id, :lead_id, :property_id, 'inquiry', :notes)");
    $stmt->execute([
        'customer_id' => $customer_id,
        'lead_id' => $lead_id,
        'property_id' => $property_id,
        'notes' => $message
    ]);

    // Auto-assign to agent based on property
    $stmt = $pdo->prepare("SELECT owner_id, title FROM properties WHERE id = :property_id");
    $stmt->execute(['property_id' => $property_id]);
    $property_result = $stmt->fetch(PDO::FETCH_OBJ);
    $agent_id = $property_result->owner_id ?? null;
    $property_title = $property_result->title ?? 'Unknown Property';

    if ($agent_id) {
        $stmt = $pdo->prepare("UPDATE leads SET assigned_to = :agent_id WHERE id = :lead_id");
        $stmt->execute([
            'agent_id' => $agent_id,
            'lead_id' => $lead_id
        ]);

        // Send notification to agent
        $notification_data = [
            'type' => 'new_lead',
            'user_id' => $agent_id,
            'title' => 'New Lead Assigned',
            'message' => "New inquiry from {$name} for {$property_title}",
            'link' => "/admin/lead.php?id={$lead_id}"
        ];
        send_notification($notification_data);
    }

    // Commit transaction
    $pdo->commit();

    // Send success response
    $response['success'] = true;
    $response['message'] = 'Thank you for your inquiry. Our agent will contact you soon!';
    sendResponse($response, $isAjax);
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=1');
    exit;
} catch (Exception $e) {
    // Rollback on error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Lead processing error: " . $e->getMessage());
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=system_error');
    exit;
}
