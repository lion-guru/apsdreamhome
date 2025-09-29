<?php
/**
 * फाइल्स को मूल स्ट्रक्चर में वापस लाने की स्क्रिप्ट
 * 
 * यह स्क्रिप्ट रिऑर्गनाइज की गई फाइल्स को उनके मूल स्थानों पर वापस मूव करती है।
 * इसे चलाने से पहले बैकअप लें।
 */

// स्क्रिप्ट शुरू होने का समय रिकॉर्ड करें
$start_time = microtime(true);

// रूट डायरेक्टरी सेट करें
$root_dir = __DIR__;

// लॉग फाइल सेट करें
$log_file = $root_dir . '/restore_structure_log.txt';
file_put_contents($log_file, "फाइल्स को मूल स्ट्रक्चर में वापस लाने का लॉग - " . date('Y-m-d H:i:s') . "\n\n");

/**
 * लॉग मैसेज को लॉग फाइल में लिखें और स्क्रीन पर प्रिंट करें
 */
function log_message($message) {
    global $log_file;
    $log_entry = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

/**
 * डायरेक्टरी बनाएं अगर वह मौजूद नहीं है
 */
function create_directory($dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            log_message("डायरेक्टरी बनाई गई: $dir");
            return true;
        } else {
            log_message("त्रुटि: डायरेक्टरी नहीं बना सकते: $dir");
            return false;
        }
    } else {
        log_message("डायरेक्टरी पहले से मौजूद है: $dir");
        return true;
    }
}

/**
 * फाइल को एक स्थान से दूसरे स्थान पर मूव करें
 */
function move_file($source, $destination) {
    if (!file_exists($source)) {
        log_message("त्रुटि: स्रोत फाइल मौजूद नहीं है: $source");
        return false;
    }
    
    // डेस्टिनेशन डायरेक्टरी बनाएं अगर वह मौजूद नहीं है
    $dest_dir = dirname($destination);
    if (!file_exists($dest_dir)) {
        if (!create_directory($dest_dir)) {
            return false;
        }
    }
    
    if (copy($source, $destination)) {
        if (unlink($source)) {
            log_message("फाइल मूव की गई: $source -> $destination");
            return true;
        } else {
            log_message("त्रुटि: स्रोत फाइल को हटा नहीं सकते: $source");
            return false;
        }
    } else {
        log_message("त्रुटि: फाइल को कॉपी नहीं कर सकते: $source -> $destination");
        return false;
    }
}

// आवश्यक डायरेक्टरी स्ट्रक्चर बनाएं
log_message("आवश्यक डायरेक्टरी स्ट्रक्चर बना रहे हैं...");

// 1. मूल फोल्डर स्ट्रक्चर बनाएं
create_directory($root_dir . '/include');
create_directory($root_dir . '/css');
create_directory($root_dir . '/js');

// फाइल्स को वापस मूव करें
log_message("फाइल्स को वापस मूव कर रहे हैं...");

// 1. टेम्पलेट फाइल्स को मूव करें
log_message("टेम्पलेट फाइल्स को मूव कर रहे हैं...");
$template_files = glob($root_dir . '/includes/templates/*.php');
foreach ($template_files as $file) {
    $filename = basename($file);
    // हेडर और फुटर फाइल्स को include फोल्डर में मूव करें
    if ($filename === 'header.php' || $filename === 'footer.php' || $filename === 'functions.php') {
        move_file($file, $root_dir . '/include/' . $filename);
    }
}

// 2. CSS फाइल्स को मूव करें
log_message("CSS फाइल्स को मूव कर रहे हैं...");
$css_files = glob($root_dir . '/assets/css/*.css');
foreach ($css_files as $file) {
    $filename = basename($file);
    // स्टाइल फाइल्स को css फोल्डर में मूव करें
    if ($filename === 'style.css' || $filename === 'styles.css' || $filename === 'demo.css') {
        move_file($file, $root_dir . '/css/' . $filename);
    }
}

// 3. JS फाइल्स को मूव करें
log_message("JS फाइल्स को मूव कर रहे हैं...");
$js_files = glob($root_dir . '/assets/js/*.js');
foreach ($js_files as $file) {
    $filename = basename($file);
    // स्क्रिप्ट फाइल्स को js फोल्डर में मूव करें
    if ($filename === 'script.js') {
        move_file($file, $root_dir . '/js/' . $filename);
    }
}

// 4. TinyMCE फाइल्स को मूव करें
log_message("TinyMCE फाइल्स को मूव कर रहे हैं...");
if (file_exists($root_dir . '/assets/js/tinymce')) {
    // TinyMCE फोल्डर को js फोल्डर में मूव करें
    $tinymce_files = glob($root_dir . '/assets/js/tinymce/*');
    foreach ($tinymce_files as $file) {
        $filename = basename($file);
        if (!file_exists($root_dir . '/js/tinymce')) {
            create_directory($root_dir . '/js/tinymce');
        }
        if (is_dir($file)) {
            // सबफोल्डर्स को भी मूव करें
            $subdir = basename($file);
            create_directory($root_dir . '/js/tinymce/' . $subdir);
            $subfiles = glob($file . '/*');
            foreach ($subfiles as $subfile) {
                if (!is_dir($subfile)) {
                    move_file($subfile, $root_dir . '/js/tinymce/' . $subdir . '/' . basename($subfile));
                }
            }
        } else {
            move_file($file, $root_dir . '/js/tinymce/' . $filename);
        }
    }
}

// 5. फाइल्स में पाथ अपडेट करें
log_message("फाइल्स में पाथ अपडेट कर रहे हैं...");

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
    
    // पाथ अपडेट करें - रिवर्स अपडेट
    
    // 1. इंक्लूड पाथ अपडेट करें
    $content = preg_replace('/include\("includes\/templates\/([^"]*)"/i', 'include("include/$1"', $content);
    $content = preg_replace('/require\("includes\/templates\/([^"]*)"/i', 'require("include/$1"', $content);
    $content = preg_replace('/include_once\("includes\/templates\/([^"]*)"/i', 'include_once("include/$1"', $content);
    $content = preg_replace('/require_once\("includes\/templates\/([^"]*)"/i', 'require_once("include/$1"', $content);
    
    // 2. CSS पाथ अपडेट करें
    $content = preg_replace('/href="assets\/css\/([^"]*)"/i', 'href="css/$1"', $content);
    
    // 3. JS पाथ अपडेट करें
    $content = preg_replace('/src="assets\/js\/([^"]*)"/i', 'src="js/$1"', $content);
    
    // 4. फंक्शन फाइल्स के लिए पाथ अपडेट करें
    $content = preg_replace('/include\("includes\/functions\/functions\.php"/i', 'include("functions.php"', $content);
    $content = preg_replace('/require\("includes\/functions\/functions\.php"/i', 'require("functions.php"', $content);
    
    // 5. कॉन्फिग फाइल्स के लिए पाथ अपडेट करें
    $content = preg_replace('/include\("includes\/config\/config\.php"/i', 'include("config.php"', $content);
    $content = preg_replace('/require\("includes\/config\/config\.php"/i', 'require("config.php"', $content);
    
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
    $files = glob($directory . '/*.php');
    $updated_count = 0;
    $total_count = count($files);
    
    log_message("डायरेक्टरी में $total_count PHP फाइल्स मिलीं: $directory");
    
    foreach ($files as $file) {
        if (update_paths_in_file($file)) {
            $updated_count++;
        }
    }
    
    // सबडायरेक्टरीज में भी अपडेट करें
    $subdirectories = glob($directory . '/*', GLOB_ONLYDIR);
    foreach ($subdirectories as $subdir) {
        update_paths_in_directory($subdir);
    }
    
    log_message("डायरेक्टरी में $updated_count फाइल्स अपडेट की गईं: $directory");
}

// रूट डायरेक्टरी में सभी PHP फाइल्स में पाथ अपडेट करें
log_message("रूट डायरेक्टरी में पाथ अपडेट कर रहे हैं...");
update_paths_in_directory($root_dir);

// एडमिन डायरेक्टरी में सभी PHP फाइल्स में पाथ अपडेट करें
if (file_exists($root_dir . '/admin')) {
    log_message("एडमिन डायरेक्टरी में पाथ अपडेट कर रहे हैं...");
    update_paths_in_directory($root_dir . '/admin');
}

// स्क्रिप्ट समाप्त होने का समय रिकॉर्ड करें
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

log_message("\nफाइल्स को मूल स्ट्रक्चर में वापस लाने का काम पूरा हुआ!");
log_message("एक्जीक्यूशन टाइम: $execution_time सेकंड");
log_message("\nनोट: यह स्क्रिप्ट फाइल्स को मूव करती है और पाथ अपडेट करती है।");
log_message("कृपया वेबसाइट को टेस्ट करें और अगर कोई समस्या हो तो मैन्युअली फिक्स करें।");