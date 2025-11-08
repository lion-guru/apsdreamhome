<?php
/**
 * Visit Scheduling API
 * 
 * This endpoint allows customers to schedule property visits with agents.
 * It checks availability, creates visit records, and sends notifications.
 * 
 * @description Schedule a property visit with automatic availability checking and notification.
 * @methods POST
 * 
 * @param {number} property_id ID of the property to visit
 * @param {string} visit_date Date of the visit (YYYY-MM-DD format)
 * @param {string} visit_time Time of the visit (HH:MM format, 24-hour)
 * @param {string} customer_name Customer's full name
 * @param {string} customer_email Customer's email address
 * @param {string} customer_phone [optional] Customer's phone number
 * @param {string} notes [optional] Additional notes or special requests
 * 
 * @response {201} Visit scheduled successfully with visit ID
 * @response {400} Invalid or missing required parameters
 * @response {409} Time slot not available
 * @response {500} Server error occurred
 * 
 * @example
 * // Schedule a property visit
 * POST /api/visits/schedule.php
 * {
 *   "property_id": 42,
 *   "visit_date": "2025-06-15",
 *   "visit_time": "14:30",
 *   "customer_name": "Jane Doe",
 *   "customer_email": "jane.doe@example.com",
 *   "customer_phone": "555-987-6543",
 *   "notes": "Interested in financing options"
 * }
 * 
 * // Response:
 * {
 *   "status": "success",
 *   "message": "Visit scheduled successfully",
 *   "visit_id": 456,
 *   "confirmation_code": "V-456-2025"
 * }
 */

// Set header for JSON response
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// If JSON parsing failed, try POST data
if (json_last_error() !== JSON_ERROR_NONE) {
    $input = $_POST;
}

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Validate required parameters
$requiredParams = ['property_id', 'visit_date', 'visit_time', 'customer_name', 'customer_email'];
$missingParams = [];

foreach ($requiredParams as $param) {
    if (empty($input[$param])) {
        $missingParams[] = $param;
    }
}

if (!empty($missingParams)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters: ' . implode(', ', $missingParams)
    ]);
    exit;
}

// Sanitize and prepare data
$propertyId = (int)$input['property_id'];
$visitDate = $conn->real_escape_string($input['visit_date']);
$visitTime = $conn->real_escape_string($input['visit_time']);
$customerName = $conn->real_escape_string($input['customer_name']);
$customerEmail = $conn->real_escape_string($input['customer_email']);
$customerPhone = isset($input['customer_phone']) ? $conn->real_escape_string($input['customer_phone']) : '';
$notes = isset($input['notes']) ? $conn->real_escape_string($input['notes']) : '';

// Validate date and time format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid date format. Use YYYY-MM-DD.'
    ]);
    exit;
}

if (!preg_match('/^\d{2}:\d{2}$/', $visitTime)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid time format. Use HH:MM (24-hour).'
    ]);
    exit;
}

// Validate email format
if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Check if property exists
$propertyQuery = "SELECT id, title, owner_id FROM properties WHERE id = $propertyId LIMIT 1";
$propertyResult = $conn->query($propertyQuery);

if (!$propertyResult || $propertyResult->num_rows === 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Property not found'
    ]);
    exit;
}

$property = $propertyResult->fetch_assoc();
$agentId = (int)$property['owner_id'];
$propertyTitle = $property['title'];

// Check if the requested time is in the future
$visitDateTime = new DateTime($visitDate . ' ' . $visitTime);
$now = new DateTime();

if ($visitDateTime <= $now) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Visit time must be in the future'
    ]);
    exit;
}

// Check if the time slot is available
$dayOfWeek = date('w', strtotime($visitDate));
$timeCheck = "SELECT * FROM visit_availability 
    WHERE property_id = $propertyId 
    AND day_of_week = $dayOfWeek
    AND '$visitTime' BETWEEN start_time AND end_time";

$availabilityResult = $conn->query($timeCheck);

if (!$availabilityResult || $availabilityResult->num_rows === 0) {
    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'message' => 'The selected time slot is not available for this property'
    ]);
    exit;
}

$availability = $availabilityResult->fetch_assoc();
$maxVisitsPerSlot = (int)$availability['max_visits_per_slot'];

// Check if slot is already at capacity
$slotCapacityCheck = "SELECT COUNT(*) as visit_count FROM property_visits 
    WHERE property_id = $propertyId 
    AND visit_date = '$visitDate' 
    AND visit_time = '$visitTime'
    AND status != 'cancelled'";

$capacityResult = $conn->query($slotCapacityCheck);
$capacity = $capacityResult->fetch_assoc();

if ((int)$capacity['visit_count'] >= $maxVisitsPerSlot) {
    http_response_code(409);
    echo json_encode([
        'status' => 'error',
        'message' => 'This time slot is already fully booked'
    ]);
    exit;
}

// Check if customer exists or create new one
$customerId = 0;
$customerQuery = "SELECT id FROM customers WHERE email = '$customerEmail' LIMIT 1";
$customerResult = $conn->query($customerQuery);

if ($customerResult && $customerResult->num_rows > 0) {
    // Customer exists, get ID
    $row = $customerResult->fetch_assoc();
    $customerId = (int)$row['id'];
    
    // Update customer information
    $updateCustomer = "UPDATE customers SET 
        name = '$customerName',
        phone = '$customerPhone',
        updated_at = NOW()
        WHERE id = $customerId";
    
    $conn->query($updateCustomer);
} else {
    // Create new customer
    $createCustomer = "INSERT INTO customers (name, email, phone, created_at, updated_at)
        VALUES ('$customerName', '$customerEmail', '$customerPhone', NOW(), NOW())";
    
    if ($conn->query($createCustomer)) {
        $customerId = $conn->insert_id;
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create customer: ' . $conn->error
        ]);
        exit;
    }
}

// Create lead for this visit
$createLead = "INSERT INTO leads (
    customer_id,
    property_id,
    agent_id,
    source,
    status,
    notes,
    created_at,
    updated_at
) VALUES (
    $customerId,
    $propertyId,
    $agentId,
    'visit_request',
    'new',
    'Visit request for $visitDate at $visitTime',
    NOW(),
    NOW()
)";

$conn->query($createLead);
$leadId = $conn->insert_id;

// Schedule the visit
$scheduleVisit = "INSERT INTO property_visits (
    customer_id,
    property_id,
    lead_id,
    visit_date,
    visit_time,
    status,
    notes,
    created_at,
    updated_at
) VALUES (
    $customerId,
    $propertyId,
    $leadId,
    '$visitDate',
    '$visitTime',
    'scheduled',
    '$notes',
    NOW(),
    NOW()
)";

if ($conn->query($scheduleVisit)) {
    $visitId = $conn->insert_id;
    $confirmationCode = 'V-' . $visitId . '-' . date('Y');
    
    // Update visit with confirmation code
    $conn->query("UPDATE property_visits SET confirmation_code = '$confirmationCode' WHERE id = $visitId");
    
    // Schedule reminders
    $visitTimestamp = strtotime($visitDate . ' ' . $visitTime);
    $reminder24h = date('Y-m-d H:i:s', $visitTimestamp - 86400); // 24 hours before
    $reminder1h = date('Y-m-d H:i:s', $visitTimestamp - 3600);   // 1 hour before
    
    $createReminders = "INSERT INTO visit_reminders (visit_id, reminder_type, status, scheduled_at)
        VALUES 
        ($visitId, '24h', 'pending', '$reminder24h'),
        ($visitId, '1h', 'pending', '$reminder1h')";
    
    $conn->query($createReminders);
    
    // Create notification for agent
    $notificationTitle = "New Visit Scheduled";
    $notificationMessage = "A new visit has been scheduled for $propertyTitle on $visitDate at $visitTime.";
    
    $createNotification = "INSERT INTO notifications (
        user_id,
        type,
        title,
        message,
        link,
        created_at,
        status
    ) VALUES (
        $agentId,
        'visit',
        '" . $conn->real_escape_string($notificationTitle) . "',
        '" . $conn->real_escape_string($notificationMessage) . "',
        '/admin/visits.php?id=$visitId',
        NOW(),
        'unread'
    )";
    
    $conn->query($createNotification);
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => 'Visit scheduled successfully',
        'visit_id' => $visitId,
        'confirmation_code' => $confirmationCode
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to schedule visit: ' . $conn->error
    ]);
}

// Close connection
$conn->close();
?>
