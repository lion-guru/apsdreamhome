// Security constants (for login rate limiting)
if (!isset($GLOBALS['max_login_attempts'])) {
    $GLOBALS['max_login_attempts'] = 5; // Maximum failed login attempts before lockout
}

if (!isset($GLOBALS['lockout_duration'])) {
    $GLOBALS['lockout_duration'] = 900; // 15 minutes lockout duration
}

    /**
     * Log admin actions
     */
    if (!function_exists('logAdminAction')) {
        function logAdminAction($data) {
            $log_file = __DIR__ . '/admin/logs/admin_actions.log';
            $log_entry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $data
            ];

            if (is_writable(dirname($log_file))) {
                file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }
    }

    /**
     * Enhanced input validation and sanitization
     */
    if (!function_exists('validateInput')) {
        function validateInput($input, $type = 'string', $max_length = null, $required = true) {
            if ($required && empty($input)) {
                return false;
            }

            if (!$required && empty($input)) {
                return '';
            }

            switch ($type) {
                case 'username':
                    $input = trim($input);
                    if (strlen($input) < 3 || strlen($input) > 50) {
                        return false;
                    }
                    if (!preg_match('/^[a-zA-Z0-9@._-]+$/', $input)) {
                        return false;
                    }
                    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

                case 'email':
                    $input = filter_var($input, FILTER_SANITIZE_EMAIL);
                    return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : false;

                case 'password':
                    return $input; // Don't sanitize passwords

                case 'captcha':
                    $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                    return is_numeric($input) ? (int)$input : false;

                case 'string':
                default:
                    $input = trim($input);
                    if ($max_length && strlen($input) > $max_length) {
                        return false;
                    }
                    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            }
        }
    }

    /**
     * Validate request headers for security
     */
    if (!function_exists('validateRequestHeaders')) {
        function validateRequestHeaders() {
            // Check Content-Type for POST requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
                if (strpos($content_type, 'application/x-www-form-urlencoded') === false &&
                    strpos($content_type, 'multipart/form-data') === false) {
                    return false;
                }
            }

            // Check User-Agent
            if (empty($_SERVER['HTTP_USER_AGENT'])) {
                return false;
            }

            return true;
        }
    }

    /**
     * Send security response
     */
    if (!function_exists('sendSecurityResponse')) {
        function sendSecurityResponse($status_code, $message, $data = null) {
            http_response_code($status_code);
            header('Content-Type: application/json');
            $response = [
                'status' => 'error',
                'message' => $message
            ];
            if ($data !== null) {
                $response['data'] = $data;
            }
            echo json_encode($response);
            exit();
        }
    }

    /**
     * Initialize admin session with proper security settings
     */
    if (!function_exists('initAdminSession')) {
        function initAdminSession() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.gc_maxlifetime', 1800); // 30 minutes

            // Generate CSRF token if not exists
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        }
    }

    /**
     * Get asset URL helper
     */
    if (!function_exists('get_asset_url')) {
        function get_asset_url($filename, $folder = 'assets') {
            $base_url = BASE_URL ?? 'http://localhost/apsdreamhomefinal/';
            return $base_url . $folder . '/' . $filename;
        }
    }

    /**
     * Get current URL helper
     */
    if (!function_exists('getCurrentUrl')) {
        function getCurrentUrl() {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
    }

    /**
     * Check if file exists and is readable
     */
    if (!function_exists('safe_file_exists')) {
        function safe_file_exists($filepath) {
            return file_exists($filepath) && is_readable($filepath);
        }
    }

    /**
     * Safe redirect function
     */
    if (!function_exists('safe_redirect')) {
        function safe_redirect($url, $permanent = false) {
            if (!headers_sent()) {
                header('Location: ' . $url, true, $permanent ? 301 : 302);
                exit();
            } else {
                echo '<script>window.location.href = "' . htmlspecialchars($url) . '";</script>';
                exit();
            }
        }
    }

    /**
     * Format phone number
     */
    if (!function_exists('format_phone_number')) {
        function format_phone_number($phone) {
            // Remove all non-digit characters
            $phone = preg_replace('/\D/', '', $phone);

            // Add country code if not present (assuming India)
            if (strlen($phone) === 10) {
                $phone = '91' . $phone;
            }

            return $phone;
        }
    }

    /**
     * Validate phone number
     */
    if (!function_exists('is_valid_phone_number')) {
        function is_valid_phone_number($phone) {
            // Basic validation - should be 10-15 digits
            return preg_match('/^\d{10,15}$/', $phone);
        }
    }

    /**
     * Generate random string
     */
    if (!function_exists('generate_random_string')) {
        function generate_random_string($length = 16) {
            return bin2hex(random_bytes($length));
        }
    }

    /**
     * Check if user is authenticated
     */
    if (!function_exists('is_authenticated')) {
        function is_authenticated() {
            return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
        }
    }

    /**
     * Get user role
     */
    if (!function_exists('get_user_role')) {
        function get_user_role() {
            return $_SESSION['admin_role'] ?? null;
        }
    }

    /**
     * Check if user has permission
     */
    if (!function_exists('has_permission')) {
        function has_permission($permission) {
            // For development, assume all permissions granted
            return true;
        }
    }

    /**
     * Format currency
     */
    if (!function_exists('format_currency')) {
        function format_currency($amount, $currency = '₹') {
            return $currency . number_format($amount, 2);
        }
    }

    /**
     * Format date
     */
    if (!function_exists('format_date')) {
        function format_date($date, $format = 'Y-m-d H:i:s') {
            return date($format, strtotime($date));
        }
    }

    /**
     * Sanitize filename
     */
    if (!function_exists('sanitize_filename')) {
        function sanitize_filename($filename) {
            return preg_replace('/[^a-zA-Z0-9\-_.]/', '', $filename);
        }
    }

    /**
     * Create directory if not exists
     */
    if (!function_exists('ensure_directory_exists')) {
        function ensure_directory_exists($dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Get file extension
     */
    if (!function_exists('get_file_extension')) {
        function get_file_extension($filename) {
            return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        }
    }

    /**
     * Check if file is image
     */
    if (!function_exists('is_image_file')) {
        function is_image_file($filename) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            return in_array(get_file_extension($filename), $allowed_extensions);
        }
    }

    /**
     * Resize and compress image
     */
    if (!function_exists('resize_image')) {
        function resize_image($source_path, $destination_path, $max_width = 800, $max_height = 600, $quality = 85) {
            if (!extension_loaded('gd')) {
                return false;
            }

            list($width, $height, $type) = getimagesize($source_path);

            // Calculate new dimensions
            $ratio = min($max_width / $width, $max_height / $height);
            $new_width = round($width * $ratio);
            $new_height = round($height * $ratio);

            // Create image resource based on type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case IMAGETYPE_PNG:
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case IMAGETYPE_GIF:
                    $source_image = imagecreatefromgif($source_path);
                    break;
                default:
                    return false;
            }

            // Create new image
            $new_image = imagecreatetruecolor($new_width, $new_height);

            // Preserve transparency for PNG/GIF
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
            }

            // Resize
            imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            // Save based on original type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    return imagejpeg($new_image, $destination_path, $quality);
                case IMAGETYPE_PNG:
                    return imagepng($new_image, $destination_path, 9);
                case IMAGETYPE_GIF:
                    return imagegif($new_image, $destination_path);
            }

            // Clean up
            imagedestroy($source_image);
            imagedestroy($new_image);

            return false;
        }
    }

    /**
     * Generate slug from string
     */
    if (!function_exists('generate_slug')) {
        function generate_slug($string) {
            $string = strtolower(trim($string));
            $string = preg_replace('/[^a-z0-9-]/', '-', $string);
            $string = preg_replace('/-+/', '-', $string);
            return trim($string, '-');
        }
    }

    /**
     * Truncate text
     */
    if (!function_exists('truncate_text')) {
        function truncate_text($text, $length = 100, $suffix = '...') {
            if (strlen($text) <= $length) {
                return $text;
            }
            return substr($text, 0, $length) . $suffix;
        }
    }

    /**
     * Get client IP address
     */
    if (!function_exists('get_client_ip')) {
        function get_client_ip() {
            $ip_headers = [
                'HTTP_CF_CONNECTING_IP',
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR'
            ];

            foreach ($ip_headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ip = $_SERVER[$header];

                    // Handle comma-separated IPs (like X-Forwarded-For)
                    if (strpos($ip, ',') !== false) {
                        $ip = trim(explode(',', $ip)[0]);
                    }

                    // Validate IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }

            return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
    }

    /**
     * Rate limiting check
     */
    if (!function_exists('check_rate_limit')) {
        function check_rate_limit($key, $max_attempts = 5, $time_window = 300) {
            $cache_file = __DIR__ . '/cache/rate_limit_' . md5($key) . '.json';

            $data = [];
            if (file_exists($cache_file)) {
                $data = json_decode(file_get_contents($cache_file), true) ?? [];
            }

            $now = time();
            $data['attempts'] = array_filter($data['attempts'] ?? [], function($timestamp) use ($now, $time_window) {
                return ($now - $timestamp) < $time_window;
            });

            $data['attempts'][] = $now;

            if (count($data['attempts']) > $max_attempts) {
                file_put_contents($cache_file, json_encode($data));
                return false; // Rate limited
            }

            file_put_contents($cache_file, json_encode($data));
            return true; // Not rate limited
        }
    }

    /**
     * Send JSON response
     */
    if (!function_exists('send_json_response')) {
        function send_json_response($data, $status_code = 200) {
            http_response_code($status_code);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
    }

    /**
     * Check if request is AJAX
     */
    if (!function_exists('is_ajax_request')) {
        function is_ajax_request() {
            return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }
    }

    /**
     * Get WhatsApp templates
     */
    if (!function_exists('getWhatsAppTemplates')) {
        function getWhatsAppTemplates() {
            $templates_file = __DIR__ . '/whatsapp_templates.php';
            if (file_exists($templates_file)) {
                return require $templates_file;
            }
            return [];
        }
    }

    /**
     * Hash password securely
     */
    if (!function_exists('hash_password')) {
        function hash_password($password) {
            return password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
        }
    }

    /**
     * Verify password hash
     */
    if (!function_exists('verify_password_hash')) {
        function verify_password_hash($password, $hash) {
            return password_verify($password, $hash);
        }
    }
}
?>
