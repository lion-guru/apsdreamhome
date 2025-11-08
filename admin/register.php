<?php
include("config.php");
$error = "";
$msg = "";

if (isset($_REQUEST['insert'])) {
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

            // Use prepared statements to prevent SQL injection
            $stmt = $con->prepare("INSERT INTO admin (auser, aemail, apass, adob, aphone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_pass, $dob, $phone);
            $result = $stmt->execute();

            if ($result) {
                $msg = 'Admin Registered Successfully';
                log_admin_action_db('create_admin', 'Registered admin: ' . $name . ' (' . $email . ')');
            } else {
                $error = '* Registration failed, please try again';
                log_admin_action_db('create_admin_failed', 'Registration failed for: ' . $name . ' (' . $email . ')');
            }
            $stmt->close();
        }
    } else {
        $error = "* Please fill all the fields!";
        log_admin_action_db('create_admin_failed', 'Missing fields during registration');
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
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="css/font-awesome.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!--[if lt IE 9]>
        <script src="js/html5shiv.min.js"></script>
        <script src="js/respond.min.js"></script>
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
                            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                            <p style="color:green;"><?php echo htmlspecialchars($msg); ?></p>
                            <!-- Form -->
                            <form method="post">
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
    <script src="js/jquery-3.2.1.min.js"></script>

    <!-- Bootstrap Core JS -->
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Custom JS -->
    <script src="js/script.js"></script>

</body>

</html>
