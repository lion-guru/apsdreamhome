<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/config/config.php');
require_once(__DIR__ . '/includes/config/base_url.php');
include(__DIR__ . '/includes/config-paths.php');
include(__DIR__ . '/includes/functions.php');
require_once(__DIR__ . '/includes/functions/role_helper.php');
require_once __DIR__ . '/includes/log_admin_activity.php';
require_once __DIR__ . '/src/Database/Database.php';
require_once __DIR__ . '/includes/classes/User.php';
require_once __DIR__ . '/includes/classes/Associate.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any existing session data if user is not logged in
if (!isset($_SESSION['uid'])) {
    session_unset();
    session_regenerate_id(true);
}

// Set session variables for security tracking
$_SESSION['last_activity'] = time();
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

$error = "";
$msg = "";

$db = new Database();
$userModel = new User($db);
$associateModel = new Associate($db);

if (isset($_REQUEST['reg'])) 
{ 
    log_admin_activity('register', 'Registration submitted by: ' . $_REQUEST['name'] . ', email: ' . $_REQUEST['email']);
    
    $name = trim($_REQUEST['name']);
    $email = trim($_REQUEST['email']);
    $phone = trim($_REQUEST['phone']);
    $pass = trim($_REQUEST['pass']);
    $utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : 'user';
    $sponsor_id = isset($_REQUEST['sponser_id']) ? strtoupper(trim($_REQUEST['sponser_id'])) : '';
    
    // Validate name (3-50 characters, letters, spaces, dots, apostrophes)
    if (!preg_match("/^[A-Za-z\s.']{3,50}$/", $name)) {
        $error = "<div class='alert alert-danger'>
            <p><strong>Registration Failed!</strong></p>
            <p>Name must be 3-50 characters long and can contain letters, spaces, dots, and apostrophes</p>
        </div>";
    }
    // Validate email format
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "<div class='alert alert-danger'>
            <p><strong>Registration Failed!</strong></p>
            <p>Please enter a valid email address</p>
        </div>";
    }
    // Validate phone number (10 digits)
    else if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "<div class='alert alert-danger'>
            <p><strong>Registration Failed!</strong></p>
            <p>Please enter a valid 10-digit phone number</p>
        </div>";
    }
    // Validate password strength using model
    else if (!$userModel->validatePassword($pass)) {
        $error = "<div class='alert alert-danger'>
            <p><strong>Registration Failed!</strong></p>
            <p>Password must be at least 8 characters long and contain uppercase, lowercase, and number</p>
        </div>";
    }
    // Validate sponsor ID for associates
    else if ($utype == 'associate' && empty($sponsor_id)) {
        $error = "<div class='alert alert-danger'>
            <p><strong>Registration Failed!</strong></p>
            <p>Sponsor ID is required for Associate registration</p>
        </div>";
    }
    else if ($utype == 'associate' && !preg_match('/^APS\d{6}$/', $sponsor_id)) {
        $error = "<div class='alert alert-danger'>
            <p><strong>Registration Failed!</strong></p>
            <p>Please enter a valid Sponsor ID (Format: APS followed by 6 digits)</p>
        </div>";
    }
    else {
        try {
            // Check for existing email/phone
            $existing = $userModel->getByEmail($email);
            if ($existing) {
                throw new Exception("Email already exists");
            }
            // Prepare user data
            $userData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $pass,
                'utype' => $utype
            ];
            $result = $userModel->create($userData);
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            $user_id = $result['user_id'];
            if ($utype == 'associate') {
                // Validate sponsor ID exists
                $sponsor = $associateModel->getById($sponsor_id);
                if (!$sponsor) {
                    throw new Exception("Invalid Sponsor ID. Please enter a valid Sponsor ID.");
                }
                // Prepare associate data
                $associateData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $pass,
                    'sponsor_id' => $sponsor_id
                ];
                $assocResult = $associateModel->create($associateData);
                if (!$assocResult['success']) {
                    throw new Exception($assocResult['message']);
                }
                $msg = "<div class='alert alert-success'>
                    <p><strong>Associate Registration Successful!</strong></p>
                    <p>Your account has been created successfully.</p>
                    <p>Login Credentials:</p>
                    <ul>
                        <li>Email: {$email}</li>
                        <li>Associate ID: {$assocResult['uid']}</li>
                        <li>Referral Code: {$assocResult['associate_id']}</li>
                    </ul>
                    <p><a href='<?php echo $base_url; ?>login.php' class='alert-link'>Click here to Login</a></p>
                </div>";
            } else {
                $msg = "<div class='alert alert-success'>
                    <p><strong>Registration Successful!</strong></p>
                    <p>Your account has been created successfully.</p>
                    <p><a href='<?php echo $base_url; ?>login.php' class='alert-link'>Click here to Login</a></p>
                </div>";
            }
            // Set session variables as per role
            if ($utype == 'associate') {
                $_SESSION['aid'] = $assocResult['uid'];
                $_SESSION['associate_id'] = $assocResult['associate_id'];
            } else {
                $_SESSION['uid'] = $user_id;
            }
            // After successful registration:
            if (isset($_SESSION['uid']) || isset($_SESSION['aid'])) {
                redirectToDashboardByRole();
            }
        } catch (Exception $e) {
            $error = "<div class='alert alert-danger'>
                <p><strong>Registration Failed!</strong></p>
                <p>{$e->getMessage()}</p>
            </div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registration - APS DREAM HOMES</title>
    <link rel="shortcut icon" href="assets/<?php echo get_asset_url('favicon.ico', 'images'); ?>">
    <link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" type="text/css" href="assets/fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/login.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/home.css', 'css'); ?>">
    <style>
        .gradient-bg {background: linear-gradient(135deg, #f6f8fa 0%, #e9eafc 100%); min-height: 100vh;}
        .loginbox {display: flex; border-radius: 18px; overflow: hidden; background: #fff;}
        .login-left {background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; width: 360px; min-width: 300px;}
        .login-left-wrap {padding: 48px 20px;}
        .login-overlay h2 {font-size: 2.1rem; font-weight: 700;}
        .login-overlay p {font-size: 1.2rem;}
        .login-right {flex: 1; background: #fff;}
        .login-right-wrap {max-width: 400px; margin: 0 auto; padding: 48px 0;}
        .input-group-text {background: #fff; border-right: 0;}
        .form-control {border-radius: 0 8px 8px 0; border-left: 0;}
        .input-group .form-control:focus {box-shadow: 0 0 0 2px #1e3c7222; border-color: #1e3c72;}
        .btn-google {background: #ea4335; color: #fff;}
        .btn-google:hover {background: #c5221f; color: #fff;}
        .login-footer a {color: #1e3c72;}
        .login-footer a:hover {color: #2a5298;}
        @media (max-width: 900px) {.loginbox {flex-direction: column;}.login-left {display:none!important;}.login-right {width: 100%; min-width: unset;}}
    </style>
</head>
<body>
    <div id="page-wrapper">
        <!-- Header start -->
        <?php include(__DIR__ . '/includes/dynamic_header.php'); ?>
        <!-- Header end -->  

        <section class="register-section section-padding bg-light">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="register-form shadow-lg p-5 bg-white rounded-4">
                            <h2 class="fw-bold text-primary mb-4">Create Your Account</h2>
                            <?php if($msg) echo $msg; if($error) echo $error; ?>
                            <form method="post" autocomplete="off">
                                <div class="mb-3">
                                    <input type="text" name="name" class="form-control form-control-lg rounded-3" placeholder="Full Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control form-control-lg rounded-3" placeholder="Email Address" required>
                                </div>
                                <div class="mb-3">
                                    <input type="tel" name="phone" class="form-control form-control-lg rounded-3" placeholder="Phone Number" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="pass" class="form-control form-control-lg rounded-3" placeholder="Password" required>
                                </div>
                                <div class="form-group user-type-group mb-3">
                                    <label class="form-label">Select User Type</label>
                                    <div class="user-type-options d-flex gap-3">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="utype" value="user" id="user_radio" checked />
                                            <label class="form-check-label" for="user_radio">User</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="utype" value="associate" id="associate_radio" />
                                            <label class="form-check-label" for="associate_radio">Associate</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3" id="sponser_id_field" style="display:none;">
                                    <input type="text" name="sponser_id" class="form-control form-control-lg rounded-3" placeholder="Sponsor ID (Associates Only)">
                                </div>
                                <!-- Honeypot for spam protection -->
                                <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                                <button type="submit" name="reg" class="btn btn-primary btn-lg rounded-pill px-5">Register</button>
                            </form>
                            <p class="mt-3 mb-0 text-secondary small">Already have an account? <a href="<?php echo $base_url; ?>login.php" class="text-primary">Login here</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer start -->
        <?php include(__DIR__ . '/includes/dynamic_footer.php'); ?>
        <!-- Footer end -->

    </div>

    <!-- JavaScript Libraries -->
    <script src="<?php echo get_asset_url('js/jquery-3.6.0.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/jquery.validate.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/form-validation.js', 'js'); ?>"></script>

    <script>
        // Show/hide sponsor ID field based on user type selection
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeRadios = document.querySelectorAll('input[name="utype"]');
            const sponserIdField = document.getElementById('sponser_id_field');
            
            userTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    sponserIdField.style.display = this.value === 'associate' ? 'block' : 'none';
                    if (this.value === 'associate') {
                        document.getElementById('sponser_id').setAttribute('required', '');
                    } else {
                        document.getElementById('sponser_id').removeAttribute('required');
                    }
                });
            });

            // Initial check for sponsor field visibility
            const checkedRadio = document.querySelector('input[name="utype"]:checked');
            if (checkedRadio && checkedRadio.value === 'associate') {
                sponserIdField.style.display = 'block';
                document.getElementById('sponser_id').setAttribute('required', '');
            } else {
                sponserIdField.style.display = 'none';
                document.getElementById('sponser_id').removeAttribute('required');
            }
        });
    </script>
</body>
</html>