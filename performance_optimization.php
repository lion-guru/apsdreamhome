<?php
/**
 * APS Dream Home - Performance Optimization Script
 * Adds caching, optimization, and performance enhancements
 */

echo "=== APS Dream Home - Performance Optimization ===\n\n";

// Create performance optimization file
$optimizationFile = '<?php
/**
 * APS Dream Home - Performance Optimization Functions
 * Adds caching, compression, and performance enhancements
 */

// Enable output compression
if (!ob_start("ob_gzhandler")) {
    ob_start();
}

// Set cache headers for static assets
function set_cache_headers($filename) {
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $cache_times = [
        "css" => 86400 * 30,    // 30 days for CSS
        "js" => 86400 * 30,     // 30 days for JS
        "jpg" => 86400 * 7,     // 7 days for images
        "jpeg" => 86400 * 7,    // 7 days for images
        "png" => 86400 * 7,     // 7 days for images
        "gif" => 86400 * 7,     // 7 days for images
        "ico" => 86400 * 30,    // 30 days for icons
        "svg" => 86400 * 30,    // 30 days for SVG
    ];

    $max_age = $cache_times[$file_extension] ?? 86400; // Default 1 day

    header("Cache-Control: public, max-age=" . $max_age);
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $max_age) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($filename)) . " GMT");
}

// Database query caching
function get_cached_query($query_key, $query, $params = [], $cache_time = 300) {
    $cache_file = __DIR__ . "/cache/queries/" . md5($query_key) . ".json";

    // Check if cache exists and is valid
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        return json_decode(file_get_contents($cache_file), true);
    }

    // Execute query and cache result using singleton
    try {
        $db = \App\Core\App::database();
        $data = $db->fetch($query, $params);
        
        // Create cache directory if it doesn\'t exist
        if (!is_dir(dirname($cache_file))) {
            mkdir(dirname($cache_file), 0755, true);
        }

        file_put_contents($cache_file, json_encode($data));
        return $data;
    } catch (Exception $e) {
        error_log("Cache query error: " . $e->getMessage());
        return [];
    }
}

// Image optimization function
function optimize_image($source_path, $destination_path, $quality = 85) {
    $image_info = getimagesize($source_path);
    if (!$image_info) return false;

    $width = $image_info[0];
    $height = $image_info[1];

    switch ($image_info["mime"]) {
        case "image/jpeg":
            $image = imagecreatefromjpeg($source_path);
            break;
        case "image/png":
            $image = imagecreatefrompng($source_path);
            break;
        case "image/gif":
            $image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }

    // Resize if too large (max 1920px width)
    if ($width > 1920) {
        $new_width = 1920;
        $new_height = ($height * 1920) / $width;
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $image = $resized_image;
    }

    // Save optimized image
    switch ($image_info["mime"]) {
        case "image/jpeg":
            return imagejpeg($image, $destination_path, $quality);
        case "image/png":
            return imagepng($image, $destination_path, 8);
        case "image/gif":
            return imagegif($image, $destination_path);
    }

    return false;
}

// CSS and JS minification
function minify_css($css_content) {
    // Remove comments
    $css_content = preg_replace("!/\*[^*]*\*+([^/*][^*]*\*+)*/!", "", $css_content);
    // Remove unnecessary whitespace
    $css_content = preg_replace("/\s+/", " ", $css_content);
    // Remove spaces around certain characters
    $css_content = preg_replace("/\s*([{}:;,>+~])\s*/", "$1", $css_content);

    return trim($css_content);
}

function minify_js($js_content) {
    // Remove single-line comments
    $js_content = preg_replace("/\/\/[^\n]*/", "", $js_content);
    // Remove multi-line comments
    $js_content = preg_replace("/\/\*[\s\S]*?\*\//", "", $js_content);
    // Remove unnecessary whitespace
    $js_content = preg_replace("/\s+/", " ", $js_content);

    return trim($js_content);
}

// Performance monitoring
function log_performance($operation, $start_time, $additional_data = null) {
    $end_time = microtime(true);
    $execution_time = $end_time - $start_time;

    $log_entry = [
        "operation" => $operation,
        "execution_time" => round($execution_time, 4),
        "timestamp" => date("Y-m-d H:i:s"),
        "memory_usage" => memory_get_peak_usage(true),
        "additional_data" => $additional_data
    ];

    $log_file = __DIR__ . "/logs/performance.log";
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND);
}

// Database connection optimization
function get_db_connection() {
    static $connection = null;

    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($connection->connect_error) {
            die("Database connection failed: " . $connection->connect_error);
        }

        $connection->set_charset("utf8mb4");
    }

    return $connection;
}

// Lazy loading for images
function lazy_image($src, $alt, $class = "lazy", $width = null, $height = null) {
    $style = "";
    if ($width) $style .= "width:" . $width . "px;";
    if ($height) $style .= "height:" . $height . "px;";

    return "<img src=\"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7\"
                data-src=\"" . $src . "\"
                alt=\"" . $alt . "\"
                class=\"" . $class . "\"
                style=\"" . $style . "\"
                loading=\"lazy\">";
}

// Preload critical resources
function preload_critical_resources() {
    $critical_css = "/assets/css/bootstrap.min.css";
    $critical_js = "/assets/js/jquery.min.js";
    $logo = "/assets/images/aps-logo.png";

    echo "<!-- Preload critical resources -->";
    echo "<link rel=\"preload\" href=\"" . $critical_css . "\" as=\"style\">";
    echo "<link rel=\"preload\" href=\"" . $critical_js . "\" as=\"script\">";
    echo "<link rel=\"preload\" href=\"" . $logo . "\" as=\"image\">";
    echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">";
    echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>";
}

// Generate sitemap for SEO
function generate_sitemap() {
    $base_url = "https://" . $_SERVER["HTTP_HOST"];
    $sitemap_path = __DIR__ . "/../sitemap.xml";

    $urls = [
        ["loc" => $base_url . "/", "priority" => "1.0", "changefreq" => "daily"],
        ["loc" => $base_url . "/about.php", "priority" => "0.8", "changefreq" => "monthly"],
        ["loc" => $base_url . "/properties.php", "priority" => "0.9", "changefreq" => "daily"],
        ["loc" => $base_url . "/contact.php", "priority" => "0.7", "changefreq" => "monthly"],
        ["loc" => $base_url . "/projects.php", "priority" => "0.8", "changefreq" => "weekly"],
    ];

    $sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
    $sitemap .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

    foreach ($urls as $url) {
        $sitemap .= "<url>";
        $sitemap .= "<loc>" . $url["loc"] . "</loc>";
        $sitemap .= "<priority>" . $url["priority"] . "</priority>";
        $sitemap .= "<changefreq>" . $url["changefreq"] . "</changefreq>";
        $sitemap .= "<lastmod>" . date("Y-m-d") . "</lastmod>";
        $sitemap .= "</url>";
    }

    $sitemap .= "</urlset>";

    file_put_contents($sitemap_path, $sitemap);
    return true;
}

echo "✅ Performance optimization functions created\n";
echo "🚀 Features added: Caching, Compression, Image optimization, Lazy loading\n";
echo "📊 Monitoring: Performance logging and analytics\n";
echo "🔍 SEO: Sitemap generation and resource preloading\n";
echo "⚡ Database: Connection pooling and query caching\n";

?>';

// Write the optimization file
file_put_contents('performance_optimizer.php', $optimizationFile);

echo "✅ Performance optimization functions created\n";
echo "🚀 Features added: Caching, Compression, Image optimization, Lazy loading\n";
echo "📊 Monitoring: Performance logging and analytics\n";
echo "🔍 SEO: Sitemap generation and resource preloading\n";
echo "⚡ Database: Connection pooling and query caching\n";

?>
