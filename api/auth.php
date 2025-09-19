<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Auth.php';

$auth = Auth::getInstance();
$data = json_decode(file_get_contents('php://input'), true);
$response = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception('Email and password are required');
        }

        $token = $auth->login($data['email'], $data['password']);
        
        if ($token) {
            $response = [
                'success' => true,
                'token' => $token,
                'message' => 'Login successful',
                'user' => [
                    'email' => $data['email']
                    // Add other user fields as needed
                ]
            ];
        } else {
            throw new Exception('Invalid email or password');
        }
    } else {
        throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
