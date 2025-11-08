<?php
/**
 * Enhanced Error Pages for MVC Structure
 * Provides beautiful, user-friendly error pages
 */

class ErrorPages {
    /**
     * Display 404 Not Found page
     */
    public static function show404() {
        http_response_code(404);

        // Set page data
        $data = [
            'page_title' => 'Page Not Found - ' . APP_NAME,
            'error_code' => '404',
            'error_title' => 'Page Not Found',
            'error_message' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
            'suggested_actions' => [
                'Go back to the homepage',
                'Check the URL for typos',
                'Contact us if you think this is an error'
            ]
        ];

        // Render 404 page
        self::renderErrorPage($data);
    }

    /**
     * Display 500 Internal Server Error page
     */
    public static function show500() {
        http_response_code(500);

        $data = [
            'page_title' => 'Server Error - ' . APP_NAME,
            'error_code' => '500',
            'error_title' => 'Internal Server Error',
            'error_message' => 'Something went wrong on our end. We are working to fix the issue. Please try again later.',
            'suggested_actions' => [
                'Try refreshing the page',
                'Come back later',
                'Contact us if the problem persists'
            ]
        ];

        self::renderErrorPage($data);
    }

    /**
     * Display 403 Forbidden page
     */
    public static function show403() {
        http_response_code(403);

        $data = [
            'page_title' => 'Access Denied - ' . APP_NAME,
            'error_code' => '403',
            'error_title' => 'Access Forbidden',
            'error_message' => 'You don\'t have permission to access this page.',
            'suggested_actions' => [
                'Contact us if you need access',
                'Go back to the homepage'
            ]
        ];

        self::renderErrorPage($data);
    }

    /**
     * Render error page template
     */
    private static function renderErrorPage($data) {
        extract($data);

        // Start output buffering
        ob_start();
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $page_title; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }
                .error-container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    padding: 60px 40px;
                    text-align: center;
                    max-width: 600px;
                    margin: 0 auto;
                }
                .error-code {
                    font-size: 120px;
                    font-weight: bold;
                    color: #667eea;
                    margin-bottom: 20px;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                }
                .error-title {
                    font-size: 32px;
                    color: #333;
                    margin-bottom: 20px;
                }
                .error-message {
                    color: #666;
                    font-size: 18px;
                    margin-bottom: 30px;
                    line-height: 1.6;
                }
                .suggested-actions {
                    background: #f8f9fa;
                    border-radius: 15px;
                    padding: 25px;
                    margin-bottom: 30px;
                    text-align: left;
                }
                .suggested-actions h5 {
                    color: #667eea;
                    margin-bottom: 15px;
                }
                .suggested-actions ul {
                    list-style: none;
                    padding: 0;
                }
                .suggested-actions li {
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                .suggested-actions li:last-child {
                    border-bottom: none;
                }
                .suggested-actions li i {
                    color: #667eea;
                    margin-right: 10px;
                    width: 16px;
                }
                .home-btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px 40px;
                    border-radius: 50px;
                    text-decoration: none;
                    font-size: 16px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    display: inline-block;
                }
                .home-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                    color: white;
                }
                .back-btn {
                    background: #6c757d;
                    color: white;
                    padding: 12px 25px;
                    border-radius: 50px;
                    text-decoration: none;
                    font-size: 14px;
                    margin-left: 10px;
                    transition: all 0.3s ease;
                }
                .back-btn:hover {
                    color: white;
                    background: #5a6268;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-container">
                    <div class="error-code"><?php echo $error_code; ?></div>
                    <h1 class="error-title"><?php echo $error_title; ?></h1>
                    <p class="error-message"><?php echo $error_message; ?></p>

                    <?php if (isset($suggested_actions) && !empty($suggested_actions)): ?>
                    <div class="suggested-actions">
                        <h5><i class="fas fa-lightbulb"></i> What can you do?</h5>
                        <ul>
                            <?php foreach ($suggested_actions as $action): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <?php echo $action; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="action-buttons">
                        <a href="<?php echo BASE_URL; ?>" class="home-btn">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                        <button onclick="history.back()" class="back-btn">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </button>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            If you continue to experience issues, please
                            <a href="<?php echo BASE_URL; ?>contact" class="text-decoration-none">contact our support team</a>.
                        </small>
                    </div>
                </div>
            </div>
        </body>
        </html>

        <?php
        echo ob_get_clean();
        exit();
    }
}

?>
