<?php
/**
 * Common functions used throughout the application
 */

/**
 * Start a secure session with proper configuration
 * 
 * @param string $name Session name
 * @param int $lifetime Session lifetime in seconds (default: 86400 = 24 hours)
 * @param string $path Cookie path (default: '/')
 * @param string $domain Cookie domain (default: current domain)
 * @param bool $secure Whether to only send cookies over HTTPS (default: auto-detect)
 * @param bool $httponly Whether to make the cookie HTTP only (default: true)
 * @param string $samesite SameSite cookie attribute (default: 'Strict')
 */
function start_secure_session(
    string $name,
    int $lifetime = 86400,
    string $path = '/',
    ?string $domain = null,
    ?bool $secure = null,
    bool $httponly = true,
    string $samesite = 'Strict'
): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Set secure flag based on request if not explicitly set
    if ($secure === null) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    // Set domain if not provided
    if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
        // Remove port if present
        $domain = strtok($_SERVER['HTTP_HOST'], ':');
    }

    // Set session cookie parameters
    $cookieParams = session_get_cookie_params();
    
    // For PHP 7.3.0+
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain ?? $cookieParams['domain'],
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    } else {
        // For older PHP versions
        session_set_cookie_params(
            $lifetime,
            $path . '; samesite=' . $samesite,
            $domain,
            $secure,
            $httponly
        );
    }

    // Set session name and start session
    session_name($name);
    
    // Prevent session fixation
    if (empty(session_id())) {
        session_start();
    }
    
    // Regenerate session ID periodically (every 30 minutes)
    $regenerateAfter = 1800; // 30 minutes in seconds
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > $regenerateAfter) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Log a message to the error log
 * 
 * @param string $message The message to log
 * @param string $level The log level (info, warning, error, debug)
 * @param string|null $file Custom log file (optional)
 * @return bool True on success, false on failure
 */
function log_message(string $message, string $level = 'info', ?string $file = null): bool {
    $logDir = __DIR__ . '/../../logs';
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Set default log file if not specified
    if ($file === null) {
        $file = sprintf('%s/app_%s.log', $logDir, date('Y-m-d'));
    } else {
        $file = $logDir . '/' . ltrim($file, '/');
    }
    
    // Format the log message
    $timestamp = date('Y-m-d H:i:s');
    $level = strtoupper($level);
    $logMessage = sprintf(
        "[%s] [%s] %s\n",
        $timestamp,
        $level,
        $message
    );
    
    // Write to log file
    return (bool)file_put_contents($file, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Get the canonical URL for the current page
 * 
 * @return string The canonical URL
 */
function get_canonical_url(): string {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    
    // Add the current path
    $url .= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Remove any query parameters
    $url = strtok($url, '?');
    
    // Remove trailing slash for consistency
    return rtrim($url, '/');
}

/**
 * Minify HTML output
 * 
 * @param string $buffer The HTML content to minify
 * @return string The minified HTML
 */
function minify_html(string $buffer): string {
    if (trim($buffer) === '') {
        return $buffer;
    }
    
    $search = [
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/', // Remove HTML comments
        '/\s+/',            // Remove extra whitespace
        '/>\s+</',          // Remove whitespace between tags
        '/\s+</',           // Remove whitespace before closing tags
        '/>\s+/'            // Remove whitespace after opening tags
    ];
    
    $replace = [
        '>',
        '<',
        '\\1',
        '',
        ' ',
        '><',
        '<',
        '>'
    ];
    
    $buffer = preg_replace($search, $replace, $buffer);
    
    return $buffer;
}

/**
 * Escape output to prevent XSS attacks
 * 
 * @param string $string The string to escape
 * @param bool $doubleEncode Whether to double encode existing HTML entities
 * @return string The escaped string
 */
function e(string $string, bool $doubleEncode = true): string {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', $doubleEncode);
}

/**
 * Check if the current request is an AJAX request
 * 
 * @return bool True if it's an AJAX request, false otherwise
 */
function is_ajax_request(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Redirect to a URL and exit
 * 
 * @param string $url The URL to redirect to
 * @param int $statusCode The HTTP status code to use (default: 302)
 */
function redirect(string $url, int $statusCode = 302): void {
    if (headers_sent()) {
        echo "<script>window.location.href='$url';</script>";
    } else {
        if (!in_array($statusCode, [301, 302, 303, 307, 308])) {
            $statusCode = 302;
        }
        header("Location: $url", true, $statusCode);
    }
    exit;
}

/**
 * Get the current URL
 * 
 * @param bool $withQueryString Whether to include the query string
 * @return string The current URL
 */
function current_url(bool $withQueryString = true): string {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    if (!$withQueryString) {
        $url = strtok($url, '?');
    }
    
    return $url;
}

/**
 * Get the current request method
 * 
 * @return string The request method (GET, POST, etc.)
 */
function request_method(): string {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Check if the current request is a POST request
 * 
 * @return bool True if it's a POST request, false otherwise
 */
function is_post(): bool {
    return request_method() === 'POST';
}

/**
 * Check if the current request is a GET request
 * 
 * @return bool True if it's a GET request, false otherwise
 */
function is_get(): bool {
    return request_method() === 'GET';
}

/**
 * Get a value from the $_GET array with optional filtering
 * 
 * @param string $key The key to get
 * @param mixed $default The default value if the key doesn't exist
 * @param int $filter The filter to apply
 * @param mixed $options Options for the filter
 * @return mixed The filtered value or default
 */
function get(string $key, $default = null, int $filter = FILTER_DEFAULT, $options = []) {
    return filter_input(INPUT_GET, $key, $filter, $options) ?? $default;
}

/**
 * Get a value from the $_POST array with optional filtering
 * 
 * @param string $key The key to get
 * @param mixed $default The default value if the key doesn't exist
 * @param int $filter The filter to apply
 * @param mixed $options Options for the filter
 * @return mixed The filtered value or default
 */
function post(string $key, $default = null, int $filter = FILTER_DEFAULT, $options = []) {
    return filter_input(INPUT_POST, $key, $filter, $options) ?? $default;
}

/**
 * Get a value from the $_REQUEST array with optional filtering
 * 
 * @param string $key The key to get
 * @param mixed $default The default value if the key doesn't exist
 * @param int $filter The filter to apply
 * @param mixed $options Options for the filter
 * @return mixed The filtered value or default
 */
function request(string $key, $default = null, int $filter = FILTER_DEFAULT, $options = []) {
    return filter_input(INPUT_REQUEST, $key, $filter, $options) ?? $default;
}

/**
 * Get a value from the $_SERVER array
 * 
 * @param string $key The key to get
 * @param mixed $default The default value if the key doesn't exist
 * @return mixed The value or default
 */
function server(string $key, $default = null) {
    return $_SERVER[$key] ?? $default;
}

/**
 * Get a value from the $_SESSION array
 * 
 * @param string $key The key to get
 * @param mixed $default The default value if the key doesn't exist
 * @return mixed The value or default
 */
function session(string $key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Set a value in the $_SESSION array
 * 
 * @param string $key The key to set
 * @param mixed $value The value to set
 */
function set_session(string $key, $value): void {
    $_SESSION[$key] = $value;
}

/**
 * Unset a value in the $_SESSION array
 * 
 * @param string $key The key to unset
 */
function unset_session(string $key): void {
    unset($_SESSION[$key]);
}

/**
 * Check if a session key exists
 * 
 * @param string $key The key to check
 * @return bool True if the key exists, false otherwise
 */
function has_session(string $key): bool {
    return isset($_SESSION[$key]);
}

/**
 * Add a flash message to the session
 * 
 * @param string $key The message key
 * @param string $message The message content
 */
function flash(string $key, string $message): void {
    if (!isset($_SESSION['_flash_messages'])) {
        $_SESSION['_flash_messages'] = [];
    }
    $_SESSION['_flash_messages'][$key] = $message;
}

/**
 * Get a flash message and remove it from the session
 * 
 * @param string $key The message key
 * @param mixed $default The default value if the key doesn't exist
 * @return mixed The message or default value
 */
function get_flash(string $key, $default = null) {
    $message = $_SESSION['_flash_messages'][$key] ?? $default;
    unset($_SESSION['_flash_messages'][$key]);
    return $message;
}

/**
 * Check if a flash message exists
 * 
 * @param string $key The message key
 * @return bool True if the message exists, false otherwise
 */
function has_flash(string $key): bool {
    return isset($_SESSION['_flash_messages'][$key]);
}

/**
 * Get all flash messages
 * 
 * @return array The flash messages
 */
function get_all_flash(): array {
    $messages = $_SESSION['_flash_messages'] ?? [];
    unset($_SESSION['_flash_messages']);
    return $messages;
}
