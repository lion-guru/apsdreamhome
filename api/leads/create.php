<?php
/**
 * Lead Creation API
 * 
 * This endpoint allows creating new leads in the system from various sources
 * such as property inquiries, contact forms, and visit requests.
 * 
 * @description Create a new lead in the system with customer information and source tracking.
 * @methods POST
 * 
 * @param {string} name Customer's full name
 * @param {string} email Customer's email address
 * @param {string} phone [optional] Customer's phone number
 * @param {string} source Lead source (website, referral, social, etc.)
 * @param {number} property_id [optional] ID of the property the lead is interested in
 * @param {string} message [optional] Additional message or notes from the customer
 * @param {string} status [optional] Initial status of the lead (default: new)
 * @param {number} agent_id [optional] ID of the agent to assign the lead to
 * 
 * @response {201} Lead created successfully with lead ID
 * @response {400} Invalid or missing required parameters
 * @response {401} API key is missing or invalid
 * @response {403} API key does not have permission to access this endpoint
 * @response {429} Rate limit exceeded
 * @response {500} Server error occurred
 * 
 * @example
 * // Create a new lead from website contact form
 * POST /api/leads/create.php
 * {
 *   "name": "John Smith",
 *   "email": "john.smith@example.com",
 *   "phone": "555-123-4567",
 *   "source": "website",
 *   "message": "I'm interested in properties in downtown area",
 *   "status": "new"
 * }
 * 
 * // Response:
 * {
 *   "status": "success",
 *   "message": "Lead created successfully",
 *   "lead_id": 123
 * }
 */

// Include authentication middleware
require_once __DIR__ . '/../auth/middleware.php';

// Set header for JSON response
header('Content-Type: application/json');

// Handle CORS for cross-origin requests
handleCors();

// Authenticate API request (required for lead creation)
$auth = authenticateApiRequest(true);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Define validation rules for parameters
$rules = [
    'name' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
    'email' => ['required' => true, 'type' => 'email'],
    'phone' => ['required' => false, 'type' => 'string'],
    'source' => ['required' => true, 'type' => 'string'],
    'property_id' => ['required' => false, 'type' => 'integer', 'min' => 1],
    'message' => ['required' => false, 'type' => 'string'],
    'status' => ['required' => false, 'type' => 'string', 'default' => 'new'],
    'agent_id' => ['required' => false, 'type' => 'integer', 'min' => 1]
];

// Validate and sanitize request parameters
$input = validateRequestParams($rules, 'JSON');

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
if (empty($input['name']) || empty($input['email']) || empty($input['source'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters: name, email, and source are required'
    ]);
    exit;
}

// Sanitize and prepare data
$name = $conn->real_escape_string($input['name']);
$email = $conn->real_escape_string($input['email']);
$phone = isset($input['phone']) ? $conn->real_escape_string($input['phone']) : '';
$source = $conn->real_escape_string($input['source']);
$propertyId = isset($input['property_id']) ? (int)$input['property_id'] : 0;
$message = isset($input['message']) ? $conn->real_escape_string($input['message']) : '';
$status = isset($input['status']) ? $conn->real_escape_string($input['status']) : 'new';
$agentId = isset($input['agent_id']) ? (int)$input['agent_id'] : 0;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Check if customer exists
$customerId = 0;
$customerQuery = "SELECT id FROM customers WHERE email = '$email' LIMIT 1";
$customerResult = $conn->query($customerQuery);

if ($customerResult && $customerResult->num_rows > 0) {
    // Customer exists, get ID
    $row = $customerResult->fetch_assoc();
    $customerId = (int)$row['id'];
    
    // Update customer information
    $updateCustomer = "UPDATE customers SET 
        name = '$name',
        phone = '$phone',
        updated_at = NOW()
        WHERE id = $customerId";
    
    $conn->query($updateCustomer);
} else {
    // Create new customer
    $createCustomer = "INSERT INTO customers (name, email, phone, created_at, updated_at)
        VALUES ('$name', '$email', '$phone', NOW(), NOW())";
    
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

// If no agent is specified but property is, assign to property's agent
if ($agentId === 0 && $propertyId > 0) {
    $agentQuery = "SELECT owner_id FROM properties WHERE id = $propertyId LIMIT 1";
    $agentResult = $conn->query($agentQuery);
    
    if ($agentResult && $agentResult->num_rows > 0) {
        $row = $agentResult->fetch_assoc();
        $agentId = (int)$row['owner_id'];
    }
}

// If still no agent, assign to a random active agent
if ($agentId === 0) {
    $randomAgentQuery = "SELECT id FROM users WHERE role = 'agent' AND status = 'active' ORDER BY RAND() LIMIT 1";
    $randomAgentResult = $conn->query($randomAgentQuery);
    
    if ($randomAgentResult && $randomAgentResult->num_rows > 0) {
        $row = $randomAgentResult->fetch_assoc();
        $agentId = (int)$row['id'];
    }
}

// Create lead
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
    " . ($propertyId > 0 ? $propertyId : "NULL") . ",
    " . ($agentId > 0 ? $agentId : "NULL") . ",
    '$source',
    '$status',
    '$message',
    NOW(),
    NOW()
)";

if ($conn->query($createLead)) {
    $leadId = $conn->insert_id;
    
    // Create notification for assigned agent
    if ($agentId > 0) {
        $notificationTitle = "New Lead Assigned";
        $notificationMessage = "A new lead ($name) has been assigned to you from $source.";
        
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
            'lead',
            '" . $conn->real_escape_string($notificationTitle) . "',
            '" . $conn->real_escape_string($notificationMessage) . "',
            '/admin/leads.php?id=$leadId',
            NOW(),
            'unread'
        )";
        
        $conn->query($createNotification);
    }
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => 'Lead created successfully',
        'lead_id' => $leadId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to create lead: ' . $conn->error
    ]);
}

// Close connection
$conn->close();
?>
