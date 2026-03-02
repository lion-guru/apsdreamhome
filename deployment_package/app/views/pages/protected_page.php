<?php
require_once __DIR__ . '/init.php';

// Example: show logged-in user's info from DB
$user_id = $_SESSION['user_id'] ?? null; // Safely retrieve user ID

// Get Google user ID from access token
require_once __DIR__ . '/../../../vendor/autoload.php';
$client = new Google_Client();
$access_token = $_SESSION['access_token'] ?? null;
$client->setAccessToken($access_token);
$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();
$google_id = $userInfo->id;

// Fetch user from DB
$stmt = $conn->prepare('SELECT name, email, picture FROM users WHERE google_id = ?');
$stmt->execute([$google_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Display user info
echo '<h2>Welcome, ' . h($user['name'] ?? '') . '!</h2>';
echo 'Email: ' . h($user['email'] ?? '') . '<br>';
echo '<img src="' . h($user['picture'] ?? '') . '" alt="Profile Picture"><br>';
echo '<a href="google_login.php?logout=1">Logout</a>';
?>

