<?php

// TODO: Add proper error handling with try-catch blocks

?>
<style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
    <div class="login-container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo h($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="login-header" class="style-65235">
            <div class="panel-title" class="style-52663">APS Dream Homes</div>
            <div class="style-90235">Admin Panel Login</div>
            <div class="panel-desc" class="style-55278">Welcome! Only authorized personnel may proceed.</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error ?? ''); ?></div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/admin/login" method="post" id="adminLoginForm" class="admin-login-form" autocomplete="off" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>

            <?php if (isset($captcha_question)): ?>
                <div class="mb-3">
                    <label for="captcha_answer" class="form-label">Security Question: <?php echo htmlspecialchars($captcha_question ?? ''); ?></label>
                    <input type="number" class="form-control" id="captcha_answer" name="captcha_answer" required>
                </div>
            <?php endif; ?>

            <?php if (isset($csrf_token)): ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
            <div class="text-center">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>