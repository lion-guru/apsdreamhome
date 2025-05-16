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
$google_associate_login_url = getAssociateGoogleLoginUrl('');

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
        $email = mysqli_real_escape_string($con, $email);
        $pass = trim($_POST['pass']);
        
        if(empty($pass)) {
            $error = "<div class='alert alert-danger'>Password cannot be empty!</div>";
        }
    }
    
    // Check login type (user or associate)
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';
    
    if ($login_type == 'associate') {
        // First check in users table for associates
        $query = "SELECT u.*, a.uid as associate_uid, a.referral_code, a.sponsor_id 
                  FROM users u 
                  JOIN associates a ON u.id = a.user_id 
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
                $_SESSION['sponsor_id'] = $row['sponsor_id'];
                $_SESSION['last_activity'] = time();
                
                // Set secure session cookie parameters
                $secure = true;
                $httponly = true;
                session_set_cookie_params(0, '/', '', $secure, $httponly);
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                // Update last login timestamp
                $update_login = $con->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_login->bind_param("i", $row['id']);
                $update_login->execute();
                
                // Use HTTPS for secure redirects
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/march2025apssite/';
                header("Location: " . $base_url . "associate_dashboard.php");
                exit();
            } else {
                $error = "<div class='alert alert-danger'>Invalid Password!</div>";
            }
        } else {
            $error = "<div class='alert alert-danger'>Invalid Email Address or Not Registered as Associate!</div>";
        }
    } else {
        // Check for regular users
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
                
                // Update last login timestamp
                $update_login = $con->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_login->bind_param("i", $row['id']);
                $update_login->execute();
                
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
    .login-type-selector {
        margin-bottom: 20px;
    }
    .login-type-selector .btn {
        width: 50%;
        border-radius: 0;
    }
    .login-type-selector .btn.active {
        background-color: #0273d4;
        color: white;
    }
    </style>
</head>
<body class="login-body">

    <div class="container">
        <div class="loginbox">
            <div class="login-left">
                <div class="login-overlay">
                    <h2 class="text-white mb-4">Welcome to APS Dream Homes</h2>
                    <p class="text-white">Find your dream property with us. We offer a wide range of properties to suit your needs and budget.</p>
                </div>
            </div>
            <div class="login-right p-5">
                <h3 class="mb-4">Login to Your Account</h3>
                
                <?php echo $error; echo $msg; ?>
                
                <div class="login-type-selector btn-group btn-group-toggle w-100" data-toggle="buttons">
                    <label class="btn btn-outline-primary active">
                        <input type="radio" name="login_type" value="user" checked> User Login
                    </label>
                    <label class="btn btn-outline-primary">
                        <input type="radio" name="login_type" value="associate"> Associate Login
                    </label>
                </div>
                
                <div id="userLoginSection">
                    <a href="<?php echo $google_login_url; ?>" class="google-btn">
                        <img src="assets/<?php echo get_asset_url('google-icon.png', 'images'); ?>" alt="Google"> Login with Google
                    </a>
                    
                    <div class="or-divider">
                        <span>OR</span>
                    </div>
                </div>
                
                <div id="associateLoginSection" style="display:none;">
                    <a href="#" id="googleAssociateLogin" class="google-btn">
                        <img src="assets/<?php echo get_asset_url('google-icon.png', 'images'); ?>" alt="Google"> Login with Google
                    </a>
                    
                    <div class="or-divider">
                        <span>OR</span>
                    </div>
                </div>
                
                <form method="post">
                    <input type="hidden" name="login_type" id="loginTypeInput" value="user">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" class="form-control" id="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pass">Password</label>
                        <input type="password" name="pass" class="form-control" id="pass" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                    </form>
                    <div class="register-options">
                        <h5>Don't have an account?</h5>
                        <a href="register.php" class="register-btn user">Register as User</a>
                        <a href="register_associate.php" class="register-btn associate">Register as Associate</a>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
                    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                    <script>
                    $(document).ready(function() {
                        $('.login-type-selector .btn').click(function() {
                            $('.login-type-selector .btn').removeClass('active');
                            $(this).addClass('active');
                            var loginType = $(this).find('input').val();
                            $('#loginTypeInput').val(loginType);
                            if (loginType === 'associate') {
                                $('#userLoginSection').hide();
                                $('#associateLoginSection').show();
                            } else {
                                $('#associateLoginSection').hide();
                                $('#userLoginSection').show();
                            }
                        });
                        $('#googleAssociateLogin').click(function(e) {
                            e.preventDefault();
                            window.location.href = '<?php echo $google_associate_login_url; ?>';
                        });
                    });
                    </script>
                    </body>
                    </html>