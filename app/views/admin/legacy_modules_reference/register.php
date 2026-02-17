<?php
/**
 * Admin Registration Page
 * Allows superadmins to create new admin accounts
 */

require_once __DIR__ . '/core/init.php';

// Check for superadmin permission
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=' . urlencode('Access Denied: Superadmin privilege required to register new admins.'));
    exit();
}

$error = "";
$msg = "";

if (isset($_REQUEST['insert'])) {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        $error = "Invalid CSRF token. Action blocked.";
        log_admin_action_db('create_admin_failed', 'CSRF token validation failed for admin registration');
    } else {
        require_once __DIR__ . '/../includes/log_admin_action_db.php';
    $name = $_REQUEST['name'];
    $email = $_REQUEST['email'];
    $pass = $_REQUEST['pass'];
    $dob = $_REQUEST['dob'];
    $phone = $_REQUEST['phone'];

    if (!empty($name) && !empty($email) && !empty($pass) && !empty($dob) && !empty($phone)) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "* Invalid email format!";
            log_admin_action_db('create_admin_failed', 'Invalid email format: ' . $email);
        } else {
            // Hash the password
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

            // Use centralized ORM for insertion
            $result = $db->insert('admin', [
                'auser' => $name,
                'username' => $name,
                'email' => $email,
                'apass' => $hashed_pass,
                'password' => $hashed_pass,
                'phone' => $phone,
                'role' => 'admin',
                'status' => 'active'
            ]);

            if ($result) {
                $msg = 'Admin Registered Successfully';
                log_admin_action_db('create_admin', 'Registered admin: ' . $name . ' (' . $email . ')');
            } else {
                $error = '* Registration failed, please try again';
                log_admin_action_db('create_admin_failed', 'Registration failed for: ' . $name . ' (' . $email . ')');
            }
        }
    } else {
        $error = "* Please fill all the fields!";
        log_admin_action_db('create_admin_failed', 'Missing fields during registration');
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS Dream Homes Admin - Register</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo get_admin_asset_url('favicon.png', 'img'); ?>">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_admin_asset_url('bootstrap.min.css', 'css'); ?>">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo get_admin_asset_url('font-awesome.min.css', 'css'); ?>">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo get_admin_asset_url('style.css', 'css'); ?>">

    <!--[if lt IE 9]>
        <script src="<?php echo get_admin_asset_url('html5shiv.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('respond.min.js', 'js'); ?>"></script>
    <![endif]-->
</head>

<body>

    <!-- Main Wrapper -->
    <div class="page-wrappers login-body">
        <div class="login-wrapper">
            <div class="container">
                <div class="loginbox">

                    <div class="login-right">
                        <div class="login-right-wrap">
                            <h1>Register</h1>
                            <p class="account-subtitle">Access to our dashboard</p>
                            <p style="color:red;"><?php echo h($error); ?></p>
                            <p style="color:green;"><?php echo h($msg); ?></p>
                            <!-- Form -->
                            <form method="post">
                                <?php echo getCsrfField(); ?>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="email" placeholder="Email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="password" placeholder="Password" name="pass" required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="date" placeholder="Date of Birth" name="dob" required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Phone" name="phone" maxlength="10" required>
                                </div>
                                <div class="form-group mb-0">
                                    <input class="btn btn-primary btn-block" type="submit" name="insert" value="Register">
                                </div>
                            </form>
                            <!-- /Form -->

                            <div class="login-or">
                                <span class="or-line"></span>
                                <span class="span-or">or</span>
                            </div>

                            <!-- Social Login -->
                            <div class="social-login">
                                <span>Register with</span>
                                <a href="#" class="facebook"><i class="fa fa-facebook"></i></a>
                                <a href="#" class="google"><i class="fa fa-google"></i></a>
                                <a href="#" class="twitter"><i class="fa fa-twitter"></i></a>
                                <a href="#" class="instagram"><i class="fa fa-instagram"></i></a>
                            </div>
                            <!-- /Social Login -->

                            <div class="text-center dont-have">Already have an account? <a href="index.php">Login</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery -->
    <script src="<?php echo get_admin_asset_url('jquery.min.js', 'js'); ?>"></script>

    <!-- Bootstrap Core JS -->
    <script src="<?php echo get_admin_asset_url('popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_admin_asset_url('bootstrap.min.js', 'js'); ?>"></script>

    <!-- Custom JS -->
    <script src="<?php echo get_admin_asset_url('script.js', 'js'); ?>"></script>

</body>

</html>
