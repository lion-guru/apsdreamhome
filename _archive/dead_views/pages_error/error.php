ï»¿<?php
/**
 * Custom Error Page for APS Dream Home
 * Handles all HTTP error codes with user-friendly messages
 */

// Define security constant if not already defined
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

require_once __DIR__ . '/init.php';

// Default error code
$error_code = Security::sanitize($_GET['code']) !== null ? (int)Security::sanitize($_GET['code']) : 404;
$is_internal = Security::sanitize($_GET['internal']) !== null && Security::sanitize($_GET['internal']) == 1;

// Set HTTP response code
http_response_code($error_code);

// Define error messages
$error_messages = [
    400 => [
        'title' => __('error_400_title'),
        'message' => __('error_400_message')
    ],
    401 => [
        'title' => __('error_401_title'),
        'message' => __('error_401_message')
    ],
    403 => [
        'title' => __('error_403_title'),
        'message' => __('error_403_message')
    ],
    404 => [
        'title' => __('error_404_title'),
        'message' => __('error_404_message')
    ],
    500 => [
        'title' => __('error_500_title'),
        'message' => __('error_500_message')
    ],
    503 => [
        'title' => __('error_503_title'),
        'message' => __('error_503_message')
    ]
];

// Get error details from session if available
$error_details = isset($_SESSION['last_error']) ? $_SESSION['last_error'] : null;

// Clear error from session after retrieving
if (isset($_SESSION['last_error'])) {
    unset($_SESSION['last_error']);
}

// Check if we're in development mode
$is_development = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
if (!defined('ENVIRONMENT')) {
    // Default to production if not defined
    $is_development = false;
}

// Get error details or use default 404
$error = $error_messages[$error_code] ?? $error_messages[404];

// Set page title
$page_title = "Error $error_code - " . $error['title'] . " | APS Dream Home";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .error-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: var(--primary);
            margin: 0;
            line-height: 1;
            opacity: 0.1;
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 0;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 2.5rem;
            color: var(--dark-color);
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .error-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .btn-home {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .btn-home:hover {
            background-color: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .error-search {
            max-width: 500px;
            margin: 30px auto 0;
            position: relative;
            z-index: 1;
        }

        .error-search input {
            border-radius: 50px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            width: 100%;
            font-size: 1rem;
        }

        .error-search button {
            position: absolute;
            right: 5px;
            top: 5px;
            bottom: 5px;
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 50px;
            padding: 0 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .error-search button:hover {
            background: #1a252f;
        }

        @media (max-width: 576px) {
            .error-card {
                padding: 30px 20px;
            }

            .error-code {
                font-size: 6rem;
            }

            .error-title {
                font-size: 2rem;
            }

            .error-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-code"><?php echo h($error_code); ?></div>

            <?php if ($error_code === 404): ?>
                <div class="error-icon">
                    <i class="fas fa-map-marker-slash"></i>
                </div>
            <?php elseif ($error_code === 403): ?>
                <div class="error-icon">
                    <i class="fas fa-ban"></i>
                </div>
            <?php elseif ($error_code === 500): ?>
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            <?php else: ?>
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            <?php endif; ?>

            <h1 class="error-title"><?php echo h($error['title']); ?></h1>
            <p class="error-message"><?php echo h($error['message']); ?></p>

            <?php if ($is_internal && $error_details && ($is_development || (function_exists('isAdmin') && isAdmin()))): ?>
            <div class="alert alert-warning mt-4">
                <h5 class="alert-heading"><i class="fas fa-bug me-2"></i><?= __('error_technical_details') ?></h5>
                <hr>
                <div class="technical-details">
                    <dl class="row">
                        <dt class="col-sm-3"><?= __('error_type') ?>:</dt>
                        <dd class="col-sm-9"><?php echo h($error_details['type'] ?? __('error_unknown')); ?></dd>

                        <dt class="col-sm-3"><?= __('common_message') ?>:</dt>
                        <dd class="col-sm-9"><?php echo h($error_details['message'] ?? __('error_no_message')); ?></dd>

                        <dt class="col-sm-3"><?= __('error_file') ?>:</dt>
                        <dd class="col-sm-9"><?php echo h($error_details['file'] ?? __('error_unknown')); ?></dd>

                        <dt class="col-sm-3"><?= __('error_line') ?>:</dt>
                        <dd class="col-sm-9"><?php echo h($error_details['line'] ?? __('error_unknown')); ?></dd>

                        <dt class="col-sm-3"><?= __('error_time') ?>:</dt>
                        <dd class="col-sm-9"><?php echo h($error_details['time'] ?? date('Y-m-d H:i:s')); ?></dd>

                        <?php if (isset($error_details['trace'])): ?>
                        <dt class="col-sm-3"><?= __('error_stack_trace') ?>:</dt>
                        <dd class="col-sm-9">
                            <pre class="bg-light p-3 small" class="style-35010"><?php echo h($error_details['trace']); ?></pre>
                        </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error_code === 404): ?>
                <div class="error-search">
                    <form action="<?= BASE_URL ?>/search" method="get">
    <?php echo CSRFProtection::csrfField(); ?>
                        <input type="text" name="q" placeholder="<?= __('error_search_placeholder') ?>" aria-label="Search">
                        <button type="submit"><i class="fas fa-search"></i> <?= __('common_search') ?></button>
                    </form>
                </div>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/" class="btn-home">
                <i class="fas fa-home"></i> <?= __('error_back_to_home') ?>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any necessary JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Error page loaded
        });
    </script>
</body>
</html>
