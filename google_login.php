<?php
// Google OAuth 2.0 Login Sample
// Requires: composer require google/apiclient
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

session_start();

$client_id = getenv('GOOGLE_CLIENT_ID');
$client_secret = getenv('GOOGLE_CLIENT_SECRET');
$redirect_uri = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/google_login.php';

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['logout'])) {
    unset($_SESSION['access_token']);
    header('Location: google_login.php');
    exit;
}

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $token;
    header('Location: google_login.php');
    exit;
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // Store user in database
    require_once __DIR__ . '/config.php';
    $google_id = $userInfo->id;
    $name = $userInfo->name;
    $email = $userInfo->email;
    $picture = $userInfo->picture;
    // Insert or update user
    $stmt = $conn->prepare("INSERT INTO users (google_id, name, email, picture) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), email=VALUES(email), picture=VALUES(picture)");
    $stmt->bind_param("ssss", $google_id, $name, $email, $picture);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo '<h2>Logged in with Google!</h2>';
    echo 'Name: ' . htmlspecialchars($name) . '<br>';
    echo 'Email: ' . htmlspecialchars($email) . '<br>';
    echo '<img src="' . htmlspecialchars($picture) . '" alt="Profile Picture"><br>';
    echo '<a href="?logout=1">Logout</a>';
} else {
    $auth_url = $client->createAuthUrl();
    echo '<a href="' . htmlspecialchars($auth_url) . '">Login with Google</a>';
}
?>
