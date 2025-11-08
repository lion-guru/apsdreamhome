<?php
// Set headers for CORS and JSON response
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database connection
require_once '../includes/db_connection.php';

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    $errors = [];
    
    // Name validation
    $name = isset($data['name']) ? sanitizeInput($data['name']) : '';
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name cannot exceed 100 characters';
    }
    
    // Email validation
    $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : '';
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email cannot exceed 100 characters';
    }
    
    // Rating validation
    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = 'Please select a valid rating';
    }
    
    // Testimonial validation
    $testimonial = isset($data['testimonial']) ? sanitizeInput($data['testimonial']) : '';
    if (empty($testimonial)) {
        $errors['testimonial'] = 'Testimonial is required';
    } elseif (strlen($testimonial) > 2000) {
        $errors['testimonial'] = 'Testimonial cannot exceed 2000 characters';
    }
    
    // Consent validation
    $consent = isset($data['consent']) ? (bool)$data['consent'] : false;
    if (!$consent) {
        $errors['consent'] = 'You must agree to share your testimonial';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new Exception('Validation failed');
    }
    
    // Get database connection
    $conn = getDbConnection();
    
    // Generate a unique filename for the avatar
    $avatarPath = '';
    $initials = '';
    $nameParts = explode(' ', $name);
    foreach ($nameParts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
        if (strlen($initials) >= 2) break;
    }
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare("INSERT INTO testimonials 
        (client_name, email, rating, testimonial, client_photo, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')");
    
    // Create a simple avatar with initials
    $avatarHtml = '<div class="avatar-circle" style="background: #' . substr(md5($email), 0, 6) . '; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24px;">' . $initials . '</div>';
    
    $stmt->bind_param('ssiss', $name, $email, $rating, $testimonial, $avatarHtml);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Thank you for your testimonial! It has been submitted for review.';
        
        // Send notification email to admin (you can uncomment and configure this)
        /*
        $to = 'admin@example.com';
        $subject = 'New Testimonial Submission';
        $message = "A new testimonial has been submitted:\n\n";
        $message .= "Name: $name\n";
        $message .= "Email: $email\n";
        $message .= "Rating: $rating/5\n";
        $message .= "Testimonial: $testimonial\n";
        $headers = 'From: noreply@yourdomain.com' . "\r\n" .
                   'Reply-To: noreply@yourdomain.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        mail($to, $subject, $message, $headers);
        */
    } else {
        throw new Exception('Failed to save testimonial. Please try again later.');
    }
    
} catch (Exception $e) {
    if (empty($response['message'])) {
        $response['message'] = $e->getMessage();
    }
    http_response_code(400); // Bad request
}

// Return JSON response
echo json_encode($response);
?>
