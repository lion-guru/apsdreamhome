<?php
/**
 * टूटे हुए लिंक्स चेकर स्क्रिप्ट
 * 
 * यह स्क्रिप्ट रिऑर्गनाइजेशन के बाद टूटे हुए लिंक्स और मिसिंग फाइल्स की जांच करती है।
 * इसे update_paths.php और handle_duplicates.php के बाद चलाएं।
 */

// स्क्रिप्ट शुरू होने का समय रिकॉर्ड करें
$start_time = microtime(true);

// रूट डायरेक्टरी सेट करें
$root_dir = __DIR__;

// लॉग फाइल सेट करें
$log_file = $root_dir . '/broken_links_report.txt';
file_put_contents($log_file, "टूटे हुए लिंक्स रिपोर्ट - " . date('Y-m-d H:i:s') . "\n\n");

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
 * फाइल में इंक्लूड पाथ और एसेट पाथ की जांच करें
 */
function check_paths_in_file($file_path) {
    global $root_dir, $log_file;
    
    if (!file_exists($file_path)) {
        log_message("त्रुटि: फाइल मौजूद नहीं है: $file_path");
        return false;
    }
    
    $content = file_get_contents($file_path);
    $issues = [];
    
    // 1. इंक्लूड पाथ की जांच करें
    preg_match_all('/include\s*\(\s*["\']([^"\']*)["\']\s*\)/i', $content, $includes);
    preg_match_all('/require\s*\(\s*["\']([^"\']*)["\']\s*\)/i', $content, $requires);
    preg_match_all('/include_once\s*\(\s*["\']([^"\']*)["\']\s*\)/i', $content, $include_onces);
    preg_match_all('/require_once\s*\(\s*["\']([^"\']*)["\']\s*\)/i', $content, $require_onces);
    
    $all_includes = array_merge($includes[1], $requires[1], $include_onces[1], $require_onces[1]);
    
    foreach ($all_includes as $include_path) {
        // अगर पाथ रूट से शुरू होता है
        if (strpos($include_path, '/') === 0) {
            $full_path = $root_dir . $include_path;
        } else {
            $full_path = dirname($file_path) . '/' . $include_path;
        }
        
        if (!file_exists($full_path)) {
            $issues[] = "मिसिंग इंक्लूड: $include_path";
        }
    }
    
    // 2. CSS पाथ की जांच करें
    preg_match_all('/href\s*=\s*["\']([^"\']*\.css[^"\']*)["\']/', $content, $css_files);
    
    foreach ($css_files[1] as $css_path) {
        // बाहरी लिंक्स को स्किप करें
        if (strpos($css_path, 'http') === 0 || strpos($css_path, '//') === 0) {
            continue;
        }
        
        // अगर पाथ रूट से शुरू होता है
        if (strpos($css_path, '/') === 0) {
            $full_path = $root_dir . $css_path;
        } else {
            $full_path = dirname($file_path) . '/' . $css_path;
        }
        
        if (!file_exists($full_path)) {
            $issues[] = "मिसिंग CSS: $css_path";
        }
    }
    
    // 3. JS पाथ की जांच करें
    preg_match_all('/src\s*=\s*["\']([^"\']*\.js[^"\']*)["\']/', $content, $js_files);
    
    foreach ($js_files[1] as $js_path) {
        // बाहरी लिंक्स को स्किप करें
        if (strpos($js_path, 'http') === 0 || strpos($js_path, '//') === 0) {
            continue;
        }
        
        // अगर पाथ रूट से शुरू होता है
        if (strpos($js_path, '/') === 0) {
            $full_path = $root_dir . $js_path;
        } else {
            $full_path = dirname($file_path) . '/' . $js_path;
        }
        
        if (!file_exists($full_path)) {
            $issues[] = "मिसिंग JS: $js_path";
        }
    }
    
    // 4. इमेज पाथ की जांच करें
    preg_match_all('/src\s*=\s*["\']([^"\']*\.(jpg|jpeg|png|gif|svg)[^"\']*)["\']/', $content, $image_files);
    
    foreach ($image_files[1] as $image_path) {
        // बाहरी लिंक्स को स्किप करें
        if (strpos($image_path, 'http') === 0 || strpos($image_path, '//') === 0 || strpos($image_path, 'data:') === 0) {
            continue;
        }
        
        // अगर पाथ रूट से शुरू होता है
        if (strpos($image_path, '/') === 0) {
            $full_path = $root_dir . $image_path;
        } else {
            $full_path = dirname($file_path) . '/' . $image_path;
        }
        
        if (!file_exists($full_path)) {
            $issues[] = "मिसिंग इमेज: $image_path";
        }
    }
    
    return $issues;
}

/**
 * डायरेक्टरी में सभी PHP फाइल्स में पाथ की जांच करें
 */
function check_paths_in_directory($directory) {
    global $root_dir, $log_file;
    
    $files = glob($directory . '/*.php');
    $issues_count = 0;
    $files_with_issues = 0;
    $total_count = count($files);
    
    log_message("डायरेक्टरी में $total_count PHP फाइल्स मिलीं: $directory");
    
    foreach ($files as $file) {
        $issues = check_paths_in_file($file);
        
        if (!empty($issues)) {
            $files_with_issues++;
            $issues_count += count($issues);
            
            log_message("\nफाइल में समस्याएं: " . basename($file));
            foreach ($issues as $issue) {
                log_message("  - $issue");
            }
        }
    }
    
    // सबडायरेक्टरीज में भी जांच करें
    $subdirectories = glob($directory . '/*', GLOB_ONLYDIR);
    foreach ($subdirectories as $subdir) {
        // कुछ फोल्डर्स को स्किप करें
        $skip_dirs = ['vendor', 'node_modules', '.git', 'backup_duplicates'];
        $dir_name = basename($subdir);
        
        if (!in_array($dir_name, $skip_dirs)) {
            $subdir_result = check_paths_in_directory($subdir);
            $issues_count += $subdir_result['issues_count'];
            $files_with_issues += $subdir_result['files_with_issues'];
        }
    }
    
    return [
        'issues_count' => $issues_count,
        'files_with_issues' => $files_with_issues
    ];
}

// मेन स्क्रिप्ट शुरू करें
log_message("टूटे हुए लिंक्स की जांच शुरू हो रही है...");

// रूट डायरेक्टरी में सभी PHP फाइल्स की जांच करें
$result = check_paths_in_directory($root_dir);

// समस्याओं की संख्या लॉग करें
log_message("\nजांच पूरी हुई!");
log_message("कुल {$result['files_with_issues']} फाइल्स में {$result['issues_count']} समस्याएं मिलीं।");

// सुझाव दें
log_message("\nसुझाव:");
log_message("==========================\n");
log_message("1. सभी टूटे हुए लिंक्स को ठीक करें, विशेष रूप से इंक्लूड पाथ।");
log_message("2. अगर कोई फाइल मिसिंग है, तो उसे सही लोकेशन पर कॉपी करें या पाथ अपडेट करें।");
log_message("3. अगर कोई एसेट (CSS, JS, इमेज) मिसिंग है, तो उसे सही फोल्डर में मूव करें।");
log_message("4. अगर बहुत सारी समस्याएं हैं, तो update_paths.php स्क्रिप्ट को फिर से चलाएं और पैटर्न अपडेट करें।");

// स्क्रिप्ट समाप्त होने का समय रिकॉर्ड करें
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

log_message("\nएक्जीक्यूशन टाइम: $execution_time सेकंड");
log_message("विस्तृत रिपोर्ट broken_links_report.txt फाइल में देखें।");