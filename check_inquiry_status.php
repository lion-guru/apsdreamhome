<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get email from query parameter
$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);

if (!$email) {
    echo json_encode(['error' => 'Email is required']);
    exit;
}

// Prepare response
$response = [
    'hasInquiries' => false,
    'pendingCount' => 0,
    'inquiries' => []
];

try {
    // Get all inquiries for this email
    $stmt = $conn->prepare("SELECT id, subject, message, status, created_at, updated_at FROM contact_inquiries WHERE email = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['hasInquiries'] = true;
        $response['inquiries'] = $result->fetch_all(MYSQLI_ASSOC);
        
        // Count pending inquiries
        $pendingStmt = $conn->prepare("SELECT COUNT(*) as count FROM contact_inquiries WHERE email = ? AND status = 'Pending'");
        $pendingStmt->bind_param("s", $email);
        $pendingStmt->execute();
        $pendingResult = $pendingStmt->get_result();
        $response['pendingCount'] = $pendingResult->fetch_assoc()['count'];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching inquiry status: ' . $e->getMessage()]);
}

$conn->close();
?>
