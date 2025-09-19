<?php
/**
 * Template rendering functions
 */

/**
 * Render a template with the given data
 * 
 * @param string $template The template file path relative to the templates directory
 * @param array $data Associative array of data to extract into the template
 * @return string The rendered template
 * @throws Exception If the template file is not found
 */
function render(string $template, array $data = []): string {
    $templatePath = __DIR__ . '/../../templates/' . ltrim($template, '/');
    
    if (!file_exists($templatePath)) {
        throw new Exception("Template not found: {$template}");
    }
    
    // Extract data to variables
    extract($data, EXTR_SKIP);
    
    // Start output buffering
    ob_start();
    
    try {
        // Include the template
        include $templatePath;
    } catch (Throwable $e) {
        // Clean the output buffer and rethrow the exception
        ob_end_clean();
        throw $e;
    }
    
    // Get the buffer contents and clean it
    return ob_get_clean();
}

/**
 * Get the current URL path
 * 
 * @return string The current URL path
 */
function current_path(): string {
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
}

/**
 * Check if the current path matches the given pattern
 * 
 * @param string $pattern The pattern to match against
 * @return bool True if the current path matches the pattern, false otherwise
 */
function is_current_path(string $pattern): bool {
    $currentPath = current_path();
    
    // Convert URL pattern to regex
    $pattern = str_replace('/', '\/', $pattern);
    $pattern = '/^' . str_replace('*', '.*', $pattern) . '$/';
    
    return (bool)preg_match($pattern, $currentPath);
}

/**
 * Get the current route name
 * 
 * @return string The current route name
 */
function current_route(): string {
    return $_SERVER['REQUEST_URI'] ?? '/';
}

/**
 * Check if the current route matches the given route
 * 
 * @param string $route The route to check against
 * @return bool True if the current route matches, false otherwise
 */
function is_route(string $route): bool {
    return current_route() === $route;
}

/**
 * Get the URL for a route
 * 
 * @param string $path The path to generate URL for
 * @param array $params Optional query parameters
 * @return string The generated URL
 */
function route(string $path, array $params = []): string {
    $baseUrl = rtrim(SITE_URL, '/');
    $path = ltrim($path, '/');
    $url = "{$baseUrl}/{$path}";
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Include a partial template
 * 
 * @param string $partial The partial template path relative to the partials directory
 * @param array $data Optional data to pass to the partial
 * @return void
 */
function partial(string $partial, array $data = []): void {
    $partialPath = __DIR__ . '/../../templates/partials/' . ltrim($partial, '/');
    
    if (!file_exists($partialPath)) {
        throw new Exception("Partial not found: {$partial}");
    }
    
    // Extract data to variables
    extract($data, EXTR_SKIP);
    
    // Include the partial
    include $partialPath;
}

/**
 * Escape a string for JavaScript output
 * 
 * @param string $string The string to escape
 * @return string The escaped string
 */
function escape_js(string $string): string {
    return addcslashes($string, "'\"\n\r\t\\");
}

/**
 * Output a CSRF token input field
 * 
 * @return void
 */
function csrf_field(): void {
    $token = $_SESSION['csrf_token'] ?? '';
    echo '<input type="hidden" name="_token" value="' . e($token) . '">';
}

/**
 * Get the CSRF token
 * 
 * @return string The CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if the token is valid, false otherwise
 */
function verify_csrf_token(string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Include a CSS file
 * 
 * @param string $path The path to the CSS file
 * @param array $attributes Additional HTML attributes
 * @return void
 */
function css(string $path, array $attributes = []): void {
    $defaults = [
        'rel' => 'stylesheet',
        'href' => '/assets/css/' . ltrim($path, '/'),
        'type' => 'text/css'
    ];
    
    $attributes = array_merge($defaults, $attributes);
    
    echo '<link';
    foreach ($attributes as $key => $value) {
        echo ' ' . $key . '="' . e($value) . '"';
    }
    echo '>' . "\n";
}

/**
 * Include a JavaScript file
 * 
 * @param string $path The path to the JavaScript file
 * @param array $attributes Additional HTML attributes
 * @return void
 */
function js(string $path, array $attributes = []): void {
    $defaults = [
        'src' => '/assets/js/' . ltrim($path, '/'),
        'type' => 'application/javascript'
    ];
    
    $attributes = array_merge($defaults, $attributes);
    
    echo '<script';
    foreach ($attributes as $key => $value) {
        echo ' ' . $key . '="' . e($value) . '"';
    }
    echo '></script>' . "\n";
}

/**
 * Include an image
 * 
 * @param string $path The path to the image
 * @param string $alt The alt text
 * @param array $attributes Additional HTML attributes
 * @return void
 */
function img(string $path, string $alt = '', array $attributes = []): void {
    $defaults = [
        'src' => '/assets/images/' . ltrim($path, '/'),
        'alt' => $alt,
        'loading' => 'lazy'
    ];
    
    $attributes = array_merge($defaults, $attributes);
    
    echo '<img';
    foreach ($attributes as $key => $value) {
        echo ' ' . $key . '="' . e($value) . '"';
    }
    echo '>';
}

/**
 * Format a date
 * 
 * @param string|int $date The date string or timestamp
 * @param string $format The date format (default: 'Y-m-d H:i:s')
 * @return string The formatted date
 */
function format_date($date, string $format = 'Y-m-d H:i:s'): string {
    if (is_numeric($date)) {
        return date($format, (int)$date);
    }
    return date($format, strtotime($date));
}

/**
 * Truncate a string to a specified length
 * 
 * @param string $string The string to truncate
 * @param int $length The maximum length
 * @param string $suffix The suffix to append if truncated (default: '...')
 * @return string The truncated string
 */
function truncate(string $string, int $length = 100, string $suffix = '...'): string {
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    
    return rtrim(mb_substr($string, 0, $length - mb_strlen($suffix))) . $suffix;
}
