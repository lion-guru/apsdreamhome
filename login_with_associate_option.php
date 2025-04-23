<?php
require_once(__DIR__ . '/includes/session.php');
include("config.php");
require_once("google_auth.php");
require_once("google_auth_associate.php");

// Enforce HTTPS for security
if (function_exists('enforceHTTPS')) {
    enforceHTTPS();
}

// Initialize session
session_start();

// Clear any existing session data if user is not logged in
if (!isset($_SESSION['uid'])) {
    session_unset();
    session_regenerate_id(true);
}

// Get Google login URL for the button
$google_login_url = getGoogleLoginUrl();

$error = "";
$msg = "";

if(isset($_POST['login'])) {
    // Rate limiting check (3 attempts per minute)
    if (isset($_SESSION['login_attempts']) && isset($_SESSION['last_attempt_time'])) {
        if ($_SESSION['login_attempts'] >= 3 && (time() - $_SESSION['last_attempt_time']) < 60) {
            $error = "<div class='alert alert-danger'>Too many login attempts. Please try again in a minute.</div>";
            header("Refresh: 60");
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
        } else {
            // Reset attempts if more than a minute has passed
            if ((time() - $_SESSION['last_attempt_time']) >= 60) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = time();
            }
        }
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    // Validate and sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "<div class='alert alert-danger'>Please enter a valid email address!</div>";
    } else {
        $email = mysqli_real_escape_string($conn, $email);
        $pass = trim($_POST['pass']);
        
        if(empty($pass)) {
            $error = "<div class='alert alert-danger'>Password cannot be empty!</div>";
        }
    }
    
    // First check in users table for associates
    $query = "SELECT u.*, a.uid as associate_uid, a.referral_code 
              FROM users u 
              LEFT JOIN associates a ON u.id = a.user_id 
              WHERE u.email = ? AND u.user_type = 'associate'";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if(password_verify($pass, $row['password'])) {
            // Set session variables for associates
            $_SESSION['uid'] = $row['id'];
            $_SESSION['associate_uid'] = $row['associate_uid'];
            $_SESSION['user'] = htmlspecialchars($row['name']);
            $_SESSION['email'] = $row['email'];
            $_SESSION['utype'] = 'associate';
            $_SESSION['referral_code'] = $row['referral_code'];
            $_SESSION['last_activity'] = time();
            
            // Set secure session cookie parameters
            $secure = true;
            $httponly = true;
            session_set_cookie_params(0, '/', '', $secure, $httponly);
            
            // Regenerate session ID
            session_regenerate_id(true);
            
            // Use HTTPS for secure redirects
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/march2025apssite/';
            header("Location: " . $base_url . "associate_dashboard.php");
            exit();
        } else {
            $error = "<div class='alert alert-danger'>Invalid Password!</div>";
        }
    } else {
        // If not found as associate, check for other user types
        $query = "SELECT * FROM users WHERE email = ? AND user_type != 'associate'";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if(password_verify($pass, $row['password'])) {
                // Set session variables consistently
                $_SESSION['uid'] = $row['id'];
                $_SESSION['user'] = htmlspecialchars($row['name']);
                $_SESSION['email'] = $row['email'];
                $_SESSION['utype'] = $row['user_type'];
                $_SESSION['last_activity'] = time();
                
                // Set session cookie parameters
                $secure = true; // Only transmit over HTTPS
                $httponly = true; // Prevent JavaScript access
                session_set_cookie_params(0, '/', '', $secure, $httponly);
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Use HTTPS for secure redirects
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/march2025apssite/';
                
                // Redirect based on user type with consistent dashboard URLs
                $redirect_url = '';
                switch(strtolower($_SESSION['utype'])) { // Case-insensitive comparison
                    case 'agent':
                        $redirect_url = $base_url . 'agent_dashboard.php';
                        break;
                    case 'builder':
                        $redirect_url = $base_url . 'builder_dashboard.php';
                        break;
                    case 'user':
                        $redirect_url = $base_url . 'user_dashboard.php';
                        break;
                    default:
                        $redirect_url = $base_url . 'dashboard.php';
                }
                
                header("Location: " . $redirect_url);
                exit();
            } else {
                $error = "<div class='alert alert-danger'>Invalid Password!</div>";
            }
        } else {
            $error = "<div class='alert alert-danger'>Invalid Email Address!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - APS Dream Homes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/responsive.css', 'css'); ?>">
    <style>
    .login-body {
        min-height: 100vh;
        background: linear-gradient(135deg, #0143a3, #0273d4);
        padding: 50px 0;
    }

    .loginbox {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 1000px;
        display: flex;
    }

    .login-left {
        width: 50%;
        background: url('assets/<?php echo get_asset_url('property-bg.jpg', 'images'); ?>') center/cover;
        position: relative;
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-overlay {
        background: rgba(2, 115, 212, 0.8);
        padding: 30px;
        border-radius: 10px;
        text-align: center;
    }
    
    .google-btn {
        width: 100%;
        background: #fff;
        color: #757575;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px 15px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .google-btn:hover {
        background: #f5f5f5;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .google-btn img {
        margin-right: 10px;
        width: 20px;
        height: 20px;
    }
    .or-divider {
        text-align: center;
        margin: 20px 0;
        position: relative;
    }
    .or-divider:before, .or-divider:after {
        content: "";
        position: absolute;
        top: 50%;
        width: 45%;
        height: 1px;
        background: #ddd;
    }
    .or-divider:before {
        left: 0;
    }
    .or-divider:after {
        right: 0;
    }
    .register-options {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .register-options h5 {
        margin-bottom: 15px;
        color: #333;
    }
    .register-btn {
        display: block;
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        text-align: center;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    .register-btn.user {
        background: #28a745;
        color: white;
    }
    .register-btn.associate {
        background: #fd7e14;
        color: white;
    }
    .register-btn:hover {
        opacity: 0.9;
        text-decoration: none;
        color: white;
    }
    </style>
</head>
<body class="login-body">

    <div class="container">
        <div class="loginbox">
            <div class="login-left">