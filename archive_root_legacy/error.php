<?php
/**
 * Custom Error Page for APS Dream Home
 * Handles all HTTP error codes with user-friendly messages
 */

// Define security constant if not already defined
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default error code
$error_code = isset($_GET['code']) ? (int)$_GET['code'] : 404;
$is_internal = isset($_GET['internal']) && $_GET['internal'] == 1;

// Set HTTP response code
http_response_code($error_code);

// Define error messages
$error_messages = [
    400 => [
        'title' => 'Bad Request',
        'message' => 'The server cannot process the request due to a client error.'
    ],
    401 => [
        'title' => 'Unauthorized',
        'message' => 'Authentication is required to access this page.'
    ],
    403 => [
        'title' => 'Access Denied',
        'message' => 'You do not have permission to access this page.'
    ],
    404 => [
        'title' => 'Page Not Found',
        'message' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'
    ],
    500 => [
        'title' => 'Internal Server Error',
        'message' => 'The server encountered an internal error. Please try again later.'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'message' => 'The server is currently unavailable. Please try again later.'
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
    <title><?php echo htmlspecialchars($page_title); ?></title>
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
            color: var(--primary-color);
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
            color: var(--secondary-color);
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
            background-color: var(--primary-color);
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
            background: var(--primary-color);
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
            <div class="error-code"><?php echo $error_code; ?></div>
            
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
            
            <h1 class="error-title"><?php echo htmlspecialchars($error['title']); ?></h1>
            <p class="error-message"><?php echo htmlspecialchars($error['message']); ?></p>
            
            <?php if ($is_internal && $error_details && ($is_development || (function_exists('is_admin') && is_admin()))): ?>
            <div class="alert alert-warning mt-4">
                <h5 class="alert-heading"><i class="fas fa-bug me-2"></i>Technical Details</h5>
                <hr>
                <div class="technical-details">
                    <dl class="row">
                        <dt class="col-sm-3">Error Type:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($error_details['type'] ?? 'Unknown'); ?></dd>
                        
                        <dt class="col-sm-3">Message:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($error_details['message'] ?? 'No message available'); ?></dd>
                        
                        <dt class="col-sm-3">File:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($error_details['file'] ?? 'Unknown'); ?></dd>
                        
                        <dt class="col-sm-3">Line:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($error_details['line'] ?? 'Unknown'); ?></dd>
                        
                        <dt class="col-sm-3">Time:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($error_details['time'] ?? date('Y-m-d H:i:s')); ?></dd>
                        
                        <?php if (isset($error_details['trace'])): ?>
                        <dt class="col-sm-3">Stack Trace:</dt>
                        <dd class="col-sm-9">
                            <pre class="bg-light p-3 small" style="max-height: 200px; overflow-y: auto;"><?php echo htmlspecialchars($error_details['trace']); ?></pre>
                        </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error_code === 404): ?>
                <div class="error-search">
                    <form action="/search" method="get">
                        <input type="text" name="q" placeholder="Search our website..." aria-label="Search">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <a href="index.php" class="btn-home">
                <i class="fas fa-home"></i> Back to Homepage
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any necessary JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Error page loaded');
        });
    </script>
</body>
</html>
