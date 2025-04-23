<?php
require_once __DIR__ . '/includes/classes/Database.php';
require_once __DIR__ . '/includes/classes/User.php';

session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = new Database();
    $userObj = new User($db);

    $user = $userObj->getByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['uid'] = $user['id'];
        $_SESSION['utype'] = $user['utype'];
        $_SESSION['name'] = $user['name'];
        // Redirect based on user type
        switch (strtolower($user['utype'])) {
            case 'admin':
                header('Location: admin/index.php');
                break;
            case 'superadmin':
                header('Location: admin/superadmin_dashboard.php');
                break;
            case 'associate':
                header('Location: associate_dashboard.php');
                break;
            case 'agent':
                header('Location: agent_dashboard.php');
                break;
            case 'builder':
                header('Location: builder_dashboard.php');
                break;
            case 'customer':
                header('Location: customer_dashboard.php');
                break;
            case 'investor':
                header('Location: investor_dashboard.php');
                break;
            case 'tenant':
                header('Location: tenant_dashboard.php');
                break;
            case 'employee':
                header('Location: employee_dashboard.php');
                break;
            default:
                header('Location: user_dashboard.php');
        }
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | MLM Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f7fa; }
        .login-box { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); padding: 2rem; }
        .login-box h2 { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 class="text-center">MLM Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" name="email" id="email" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>