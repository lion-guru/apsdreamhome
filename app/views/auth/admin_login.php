<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$captcha_question = $captcha_question ?? '5 + 3 = ?';
$csrf_token = $csrf_token ?? '';
$error = $error ?? null;
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - APS Dream Home</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(99, 102, 241, 0.12) 0%, transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(139, 92, 246, 0.10) 0%, transparent 50%),
                        radial-gradient(circle at 50% 20%, rgba(79, 70, 229, 0.08) 0%, transparent 40%);
            animation: bgShift 20s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes bgShift {
            0%   { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-5%, 3%) rotate(3deg); }
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.06);
            padding: 2.75rem 2.5rem 2.5rem;
            backdrop-filter: blur(2px);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35);
        }

        .brand-icon i {
            font-size: 1.75rem;
            color: #fff;
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e1b4b;
            margin: 0;
            letter-spacing: -0.03em;
        }

        .brand-subtitle {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-top: 0.25rem;
        }

        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .error-alert i {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }

        .form-label i {
            width: 16px;
            text-align: center;
            color: #6366f1;
            margin-right: 0.25rem;
        }

        .input-group {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1.5px solid #e5e7eb;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-group:focus-within {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .input-group-text {
            background: #f9fafb;
            border: none;
            color: #9ca3af;
            padding: 0.7rem 0.85rem;
            font-size: 0.9rem;
        }

        .input-group .form-control {
            border: none;
            padding: 0.7rem 0.85rem;
            font-size: 0.925rem;
            color: #1f2937;
            background: #fff;
        }

        .input-group .form-control:focus {
            box-shadow: none;
            outline: none;
        }

        .input-group .form-control::placeholder {
            color: #b0b5be;
        }

        .btn-toggle-pass {
            background: #f9fafb;
            border: none;
            color: #9ca3af;
            padding: 0.7rem 0.85rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-toggle-pass:hover {
            color: #6366f1;
        }

        .captcha-box {
            background: #f5f3ff;
            border: 1.5px solid #ddd6fe;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .captcha-question {
            font-size: 1rem;
            font-weight: 700;
            color: #4c1d95;
            white-space: nowrap;
        }

        .captcha-box .form-control {
            border: none;
            background: #fff;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.925rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            max-width: 100px;
            text-align: center;
        }

        .captcha-box .form-control:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.35);
            letter-spacing: 0.01em;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(99, 102, 241, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 500;
        }

        .footer-text span {
            color: rgba(255, 255, 255, 0.6);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem 1.75rem;
                border-radius: 16px;
            }

            .brand-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">

            <!-- Brand -->
            <div class="brand-section">
                <div class="brand-icon">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <h1 class="brand-title">APS Dream Home</h1>
                <p class="brand-subtitle">Admin Portal</p>
            </div>

            <!-- Error -->
            <?php
            $displayError = $error;
            if (empty($displayError) && isset($_SESSION['error'])) {
                $displayError = $_SESSION['error'];
                unset($_SESSION['error']);
            }
            ?>
            <?php if (!empty($displayError)): ?>
                <div class="error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($displayError); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?php echo BASE_URL; ?>/admin/login" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Email / Username -->
                <div class="mb-3">
                    <label class="form-label" for="username">
                        <i class="fa-solid fa-user"></i> Username or Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            placeholder="Enter your username or email"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label" for="password">
                        <i class="fa-solid fa-lock"></i> Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword()" tabindex="-1">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Captcha -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fa-solid fa-shield-halved"></i> Security Check
                    </label>
                    <div class="captcha-box">
                        <span class="captcha-question"><?php echo htmlspecialchars($captcha_question); ?></span>
                        <input
                            type="text"
                            class="form-control"
                            name="captcha_answer"
                            placeholder="?"
                            required
                            autocomplete="off"
                        >
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                </button>
            </form>

        </div>

        <p class="footer-text">
            &copy; <?php echo date('Y'); ?> <span>APS Dream Home</span>. All rights reserved.
        </p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>
