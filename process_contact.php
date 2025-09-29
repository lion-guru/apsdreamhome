<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and sanitize form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? sanitizeInput($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $googleId = isset($_POST['google_id']) ? sanitizeInput($_POST['google_id']) : null;
    
    // Server-side validation
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors['subject'] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        $response['errors'] = $errors;
        $response['message'] = 'Please correct the errors in the form';
        echo json_encode($response);
        exit;
    }
    
    // If we reach here, the data is valid
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "apsdreamhome";

    try {
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO contact_inquiries (name, email, phone, subject, message, google_id, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())");
        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $googleId);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your message! We will get back to you soon.';
            $response['inquiryId'] = $conn->insert_id;
        } else {
            throw new Exception("Error saving to database: " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Error processing your request. Please try again later.';
        $response['error'] = $e->getMessage();
    }
    
    // In a real application, you would do something like:
    // $to = 'your-email@example.com';
    // $email_subject = "New Contact Form Submission: $subject";
    // $email_body = "You have received a new message from your website contact form.\n\n".
    //               "Email: $email\n".
    //               "Phone: $phone\n\n".
    //               "Message:\n$message";
    // $headers = "From: noreply@yourdomain.com\r\n";
    // $headers .= "Reply-To: $email\r\n";
    // mail($to, $email_subject, $email_body, $headers);
    
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
