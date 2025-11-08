<?php
/**
 * सभी अपडेट स्क्रिप्ट्स चलाने वाला मास्टर स्क्रिप्ट
 * 
 * यह स्क्रिप्ट सभी अपडेट स्क्रिप्ट्स को एक क्रम में चलाती है:
 * 1. update_paths.php - पाथ अपडेट करना
 * 2. handle_duplicates.php - डुप्लिकेट फाइल्स को हैंडल करना
 * 3. check_broken_links.php - टूटे हुए लिंक्स की जांच करना
 */

// स्क्रिप्ट शुरू होने का समय रिकॉर्ड करें
$start_time = microtime(true);

// रूट डायरेक्टरी सेट करें
$root_dir = __DIR__;

// लॉग फाइल सेट करें
$log_file = $root_dir . '/master_update_log.txt';
file_put_contents($log_file, "मास्टर अपडेट लॉग - " . date('Y-m-d H:i:s') . "\n\n");

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
 * स्क्रिप्ट चलाएं और आउटपुट कैप्चर करें
 */
function run_script($script_path) {
    global $root_dir;
    
    if (!file_exists($script_path)) {
        log_message("त्रुटि: स्क्रिप्ट फाइल मौजूद नहीं है: $script_path");
        return false;
    }
    
    log_message("स्क्रिप्ट चला रहे हैं: " . basename($script_path));
    log_message("==========================\n");
    
    // स्क्रिप्ट चलाएं और आउटपुट कैप्चर करें
    ob_start();
    include($script_path);
    $output = ob_get_clean();
    
    // आउटपुट को लॉग फाइल में लिखें
    file_put_contents($log_file, $output, FILE_APPEND);
    
    // आउटपुट को स्क्रीन पर प्रिंट करें
    echo $output;
    
    log_message("\nस्क्रिप्ट पूरी हुई: " . basename($script_path));
    log_message("==========================\n");
    
    return true;
}

// मेन स्क्रिप्ट शुरू करें
log_message("सभी अपडेट स्क्रिप्ट्स चलाने की प्रक्रिया शुरू हो रही है...");
log_message("कृपया प्रक्रिया पूरी होने तक प्रतीक्षा करें...\n");

// 1. पाथ अपडेट स्क्रिप्ट चलाएं
log_message("स्टेप 1: पाथ अपडेट करना");
if (!run_script($root_dir . '/update_paths.php')) {
    log_message("त्रुटि: पाथ अपडेट स्क्रिप्ट चलाने में समस्या आई।");
}

// 2. डुप्लिकेट फाइल हैंडलर स्क्रिप्ट चलाएं
log_message("\nस्टेप 2: डुप्लिकेट फाइल्स को हैंडल करना");
if (!run_script($root_dir . '/handle_duplicates.php')) {
    log_message("त्रुटि: डुप्लिकेट फाइल हैंडलर स्क्रिप्ट चलाने में समस्या आई।");
}

// 3. टूटे हुए लिंक्स चेकर स्क्रिप्ट चलाएं
log_message("\nस्टेप 3: टूटे हुए लिंक्स की जांच करना");
if (!run_script($root_dir . '/check_broken_links.php')) {
    log_message("त्रुटि: टूटे हुए लिंक्स चेकर स्क्रिप्ट चलाने में समस्या आई।");
}

// स्क्रिप्ट समाप्त होने का समय रिकॉर्ड करें
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

log_message("\nसभी अपडेट स्क्रिप्ट्स पूरी हो गईं!");
log_message("कुल एक्जीक्यूशन टाइम: $execution_time सेकंड");
log_message("\nनिम्नलिखित लॉग फाइल्स में विस्तृत जानकारी देखें:");
log_message("1. path_update_log.txt - पाथ अपडेट लॉग");
log_message("2. duplicate_handler_log.txt - डुप्लिकेट फाइल हैंडलर लॉग");
log_message("3. broken_links_report.txt - टूटे हुए लिंक्स रिपोर्ट");
log_message("4. master_update_log.txt - मास्टर अपडेट लॉग");

log_message("\nकृपया README_AFTER_REORGANIZATION.md फाइल पढ़ें अधिक जानकारी के लिए।");
log_message("अब आप वेबसाइट की जांच कर सकते हैं और सुनिश्चित कर सकते हैं कि सब कुछ सही काम कर रहा है।");