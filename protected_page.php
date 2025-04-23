<?php
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/config.php';

// Example: show logged-in user's info from DB
$access_token = $_SESSION['access_token'];

// Get Google user ID from access token
require_once __DIR__ . '/vendor/autoload.php';
$client = new Google_Client();
$client->setAccessToken($access_token);
$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();
$google_id = $userInfo->id;

// Fetch user from DB
$stmt = $conn->prepare('SELECT name, email, picture FROM users WHERE google_id = ?');
$stmt->bind_param('s', $google_id);
$stmt->execute();
$stmt->bind_result($name, $email, $picture);
$stmt->fetch();
$stmt->close();
$conn->close();

// Display user info
echo '<h2>Welcome, ' . htmlspecialchars($name) . '!</h2>';
echo 'Email: ' . htmlspecialchars($email) . '<br>';
echo '<img src="' . htmlspecialchars($picture) . '" alt="Profile Picture"><br>';
echo '<a href="google_login.php?logout=1">Logout</a>';
?>
