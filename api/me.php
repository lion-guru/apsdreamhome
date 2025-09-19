<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Auth.php';

$auth = Auth::getInstance();
$user = $auth->requireAuth();

// If we get here, the user is authenticated
$response = [
    'success' => true,
    'user' => [
        'id' => $user['sub'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
];

echo json_encode($response);
