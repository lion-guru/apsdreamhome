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

$error_message = "";
$success_message = "";
$db = new Database();
$userModel = new User($db);
$associateModel = new Associate($db);

if (isset($_REQUEST['reg'])) {
    log_admin_activity('register', 'Registration submitted by: ' . $_REQUEST['name'] . ', email: ' . $_REQUEST['email']);
    $name = trim($_REQUEST['name']);
    $email = trim($_REQUEST['email']);
    $phone = trim($_REQUEST['phone']);
    $pass = trim($_REQUEST['pass']);
    $utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : 'user';
    $sponsor_id = isset($_REQUEST['sponser_id']) ? strtoupper(trim($_REQUEST['sponser_id'])) : '';
    if (!preg_match("/^[A-Za-z\s.']{3,50}$/", $name)) {
        $error_message = "Name must be 3-50 characters long and can contain letters, spaces, dots, and apostrophes.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error_message = "Please enter a valid 10-digit phone number.";
    } else if (!$userModel->validatePassword($pass)) {
        $error_message = "Password must be at least 8 characters long and contain uppercase, lowercase, and number.";
    } else if ($utype == 'associate' && empty($sponsor_id)) {
        $error_message = "Sponsor ID is required for Associate registration.";
    } else if ($utype == 'associate' && !preg_match('/^APS\d{6}$/', $sponsor_id)) {
        $error_message = "Please enter a valid Sponsor ID (Format: APS followed by 6 digits).";
    } else {
        try {
            $existing = $userModel->getByEmail($email);
            if ($existing) {
                throw new Exception("Email already exists");
            }
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
                $sponsor = $associateModel->getById($sponsor_id);
                if (!$sponsor) {
                    throw new Exception("Invalid Sponsor ID. Please enter a valid Sponsor ID.");
                }
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
                $success_message = "Associate Registration Successful! Your account has been created successfully.";
            } else {
                $success_message = "Registration Successful! Your account has been created successfully.";
            }
            if ($utype == 'associate') {
                $_SESSION['aid'] = $assocResult['uid'];
                $_SESSION['associate_id'] = $assocResult['associate_id'];
            } else {
                $_SESSION['uid'] = $user_id;
            }
            if (isset($_SESSION['uid']) || isset($_SESSION['aid'])) {
                redirectToDashboardByRole();
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            color: #222;
            position: relative;
        }
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2e8b57;
            letter-spacing: 2px;
            animation: fadeInDown 1.2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 18px;
            font-weight: 600;
            color: #2e8b57;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container input, .register-container select {
            margin-bottom: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.85);
            font-size: 1rem;
            outline: none;
        }
        .register-container input:focus, .register-container select:focus {
            background: #e6f7ef;
        }
        .register-container button {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-container button:hover {
            background: #246b43;
        }
        .register-container .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .register-container .login-link a {
            color: #2e8b57;
            text-decoration: none;
            font-weight: 500;
        }
        .register-container .login-link a:hover {
            text-decoration: underline;
        }
        .register-container .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2e8b57;
        }
        .register-container .input-group {
            position: relative;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <span class="logo"><i class="fa-solid fa-building"></i> APS Dream Homes</span>
        </div>
        <h2>Create Your Account</h2>
        <?php if (!empty($error_message)) { echo '<div style="color:red;text-align:center;margin-bottom:10px;">'.$error_message.'</div>'; } ?>
        <?php if (!empty($success_message)) { echo '<div style="color:green;text-align:center;margin-bottom:10px;">'.$success_message.'</div>'; } ?>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fa fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <i class="fa fa-lock form-icon"></i>
                <input type="password" name="pass" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fa fa-users form-icon"></i>
                <select name="utype" id="user_type" required>
                    <option value="user">User</option>
                    <option value="associate">Associate</option>
                </select>
            </div>
            <div class="input-group" id="sponsor_id_group" style="display:none;">
                <i class="fa fa-id-badge form-icon"></i>
                <input type="text" name="sponser_id" placeholder="Sponsor ID (Associates Only)">
            </div>
            <button type="submit" name="reg">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            var sponsorGroup = document.getElementById('sponsor_id_group');
            if (this.value === 'associate') {
                sponsorGroup.style.display = 'block';
            } else {
                sponsorGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APS Dream Homes</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:7