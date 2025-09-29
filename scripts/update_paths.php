<?php
/**
 * पाथ अपडेट स्क्रिप्ट
 * 
 * यह स्क्रिप्ट रिओर्गनाइजेशन के बाद फाइल्स में पाथ अपडेट करती है।
 * इसे चलाने से पहले reorganize.php स्क्रिप्ट चला लें और बैकअप लें।
 */

// स्क्रिप्ट शुरू होने का समय रिकॉर्ड करें
$start_time = microtime(true);

// रूट डायरेक्टरी सेट करें
$root_dir = __DIR__;

// लॉग फाइल सेट करें
$log_file = $root_dir . '/path_update_log.txt';
file_put_contents($log_file, "पाथ अपडेट लॉग - " . date('Y-m-d H:i:s') . "\n\n");

/**
 * लॉग मैसेज को लॉग फाइल में लिखें और स्क्रीन पर प्रिंट करें
 */
if (!function_exists('log_message')) {
    function log_message($message) {
        global $log_file;
        $log_entry = date('Y-m-d H:i:s') . " - " . $message . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        echo $log_entry;
    }
}

/**
 * फाइल में पाथ अपडेट करें
 */
function update_paths_in_file($file_path) {
    if (!file_exists($file_path)) {
        log_message("त्रुटि: फाइल मौजूद नहीं है: $file_path");
        return false;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    // पाथ अपडेट करें
    
    // 1. इंक्लूड पाथ अपडेट करें
    $content = preg_replace(
        '/include\s*\(\s*["\'](?:include|includes)\/([^"\']*)["\']\s*\)/i',
        'include(__DIR__ . \'/includes/$1\')',
        $content
    );
    $content = preg_replace(
        '/require\s*\(\s*["\'](?:include|includes)\/([^"\']*)["\']\s*\)/i',
        'require(__DIR__ . \'/includes/$1\')',
        $content
    );
    $content = preg_replace(
        '/include_once\s*\(\s*["\'](?:include|includes)\/([^"\']*)["\']\s*\)/i',
        'include_once(__DIR__ . \'/includes/$1\')',
        $content
    );
    $content = preg_replace(
        '/require_once\s*\(\s*["\'](?:include|includes)\/([^"\']*)["\']\s*\)/i',
        'require_once(__DIR__ . \'/includes/$1\')',
        $content
    );
    
    // 2. CSS पाथ अपडेट करें
    $content = preg_replace(
        '/<link[^>]*href=["\'](css\/[^"\']*\.css)["\']/i',
        '<link rel="stylesheet" href="<?php echo get_asset_url(\'$1\', \'css\'); ?>"',
        $content
    );
    
    // 3. JS पाथ अपडेट करें
    $content = preg_replace(
        '/<script[^>]*src=["\'](js\/[^"\']*\.js)["\']/i',
        '<script src="<?php echo get_asset_url(\'$1\', \'js\'); ?>"',
        $content
    );
    
    // 4. इमेज पाथ अपडेट करें
    $content = preg_replace(
        '/(?:images|img)\/([^"\']*\.(jpg|jpeg|png|gif|svg))/i',
        '<?php echo get_asset_url(\'$1\', \'images\'); ?>',
        $content
    );
    
    // 5. फ़ॉन्ट पाथ अपडेट करें
    $content = preg_replace(
        '/(?:fonts|webfonts)\/([^"\']*\.(woff2?|ttf|eot|svg))/i',
        '<?php echo get_asset_url(\'$1\', \'webfonts\'); ?>',
        $content
    );
    
    // अगर कोई परिवर्तन हुआ है तो फाइल अपडेट करें
    if ($content !== $original_content) {
        if (file_put_contents($file_path, $content)) {
            log_message("फाइल अपडेट की गई: $file_path");
            return true;
        } else {
            log_message("त्रुटि: फाइल अपडेट नहीं कर सकते: $file_path");
            return false;
        }
    } else {
        log_message("कोई परिवर्तन नहीं: $file_path");
        return true;
    }
}

/**
 * डायरेक्टरी में सभी PHP फाइल्स में पाथ अपडेट करें
 */
function update_paths_in_directory($directory) {
    if (!is_dir($directory)) {
        log_message("त्रुटि: डायरेक्टरी मौजूद नहीं है: $directory");
        return;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $php_files = [];
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $php_files[] = $file->getPathname();
        }
    }
    
    log_message("डायरेक्टरी में " . count($php_files) . " PHP फाइल्स मिलीं: $directory");
    
    foreach ($php_files as $file) {
        update_paths_in_file($file);
    }
}

// मेन स्क्रिप्ट शुरू करें
log_message("पाथ अपडेट प्रक्रिया शुरू हो रही है...");

// पहले एसेट हेल्पर को लोड करें
// require_once __DIR__ . '/includes/functions/asset_helper.php'; // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead

// रूट डायरेक्टरी में सभी PHP फाइल्स अपडेट करें
update_paths_in_directory($root_dir);

// एक्जीक्यूशन टाइम कैलकुलेट करें
$execution_time = microtime(true) - $start_time;
log_message("पाथ अपडेट प्रक्रिया पूरी हुई! एक्जीक्यूशन टाइम: " . number_format($execution_time, 2) . " सेकंड");