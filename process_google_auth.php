<?php
require_once 'vendor/autoload.php';

// Start session
session_start();

// Initialize the Google Client
$client = new Google_Client(['client_id' => 'YOUR_GOOGLE_CLIENT_ID']);

// Verify the ID token
$id_token = $_POST['credential'];

try {
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        // User is authenticated
        $userid = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];
        $picture = $payload['picture'] ?? '';
        
        // Store user info in session
        $_SESSION['user'] = [
            'id' => $userid,
            'email' => $email,
            'name' => $name,
            'picture' => $picture
        ];
        
        // Set a cookie that expires in 30 days
        setcookie('google_auth', json_encode([
            'id' => $userid,
            'email' => $email,
            'name' => $name,
            'picture' => $picture
        ]), time() + (86400 * 30), "/"); // 86400 = 1 day
        
        // Return success response
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userid,
                'email' => $email,
                'name' => $name,
                'picture' => $picture
            ]
        ]);
    } else {
        // Invalid ID token
        throw new Exception('Invalid ID token');
    }
} catch (Exception $e) {
    // Failed to verify token
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Authentication failed: ' . $e->getMessage()
    ]);
}
?>
