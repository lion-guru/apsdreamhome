<?php
/**
 * प्रोजेक्ट रिऑर्गनाइजेशन स्क्रिप्ट
 * 
 * यह स्क्रिप्ट प्रोजेक्ट की फाइल स्ट्रक्चर को रिऑर्गनाइज करती है।
 * इसे चलाने से पहले reorganization_plan.md फाइल को पढ़ें और बैकअप लें।
 */

// स्क्रिप्ट शुरू होने का समय रिकॉर्ड करें
$start_time = microtime(true);

// रूट डायरेक्टरी सेट करें
$root_dir = __DIR__;

// लॉग फाइल सेट करें
$log_file = $root_dir . '/reorganization_log.txt';
file_put_contents($log_file, "प्रोजेक्ट रिऑर्गनाइजेशन लॉग - " . date('Y-m-d H:i:s') . "\n\n");

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

// 1. डेटाबेस फोल्डर स्ट्रक्चर
create_directory($root_dir . '/database');
create_directory($root_dir . '/database/migrations');
create_directory($root_dir . '/database/backups');
create_directory($root_dir . '/database/scripts');

// 2. इंक्लूड्स फोल्डर स्ट्रक्चर
create_directory($root_dir . '/includes/config');
create_directory($root_dir . '/includes/classes');
create_directory($root_dir . '/includes/functions');
create_directory($root_dir . '/includes/templates');

// 3. एसेट्स फोल्डर स्ट्रक्चर (अगर पहले से मौजूद नहीं है)
create_directory($root_dir . '/assets/css');
create_directory($root_dir . '/assets/js');
create_directory($root_dir . '/assets/images');
create_directory($root_dir . '/assets/fonts');
create_directory($root_dir . '/assets/vendor');

// 4. अपलोड्स फोल्डर स्ट्रक्चर
create_directory($root_dir . '/uploads/property');
create_directory($root_dir . '/uploads/users');

// 5. डॉक्स फोल्डर
create_directory($root_dir . '/docs');

// डेटाबेस फाइल्स को मूव करें
log_message("डेटाबेस फाइल्स को मूव कर रहे हैं...");
$database_files = glob($root_dir . '/DATABASE FILE/*.sql');
foreach ($database_files as $file) {
    $filename = basename($file);
    if (strpos($filename, 'migration') !== false) {
        move_file($file, $root_dir . '/database/migrations/' . $filename);
    } else {
        move_file($file, $root_dir . '/database/' . $filename);
    }
}

// रूट में मौजूद SQL फाइल्स को मूव करें
$root_sql_files = glob($root_dir . '/*.sql');
foreach ($root_sql_files as $file) {
    $filename = basename($file);
    if (strpos($filename, 'migration') !== false) {
        move_file($file, $root_dir . '/database/migrations/' . $filename);
    } else {
        move_file($file, $root_dir . '/database/' . $filename);
    }
}

// डेटाबेस स्क्रिप्ट्स को मूव करें
log_message("डेटाबेस स्क्रिप्ट्स को मूव कर रहे हैं...");
$db_scripts = glob($root_dir . '/execute_*.php');
foreach ($db_scripts as $file) {
    move_file($file, $root_dir . '/database/scripts/' . basename($file));
}

// CSS फाइल्स को मूव करें
log_message("CSS फाइल्स को मूव कर रहे हैं...");
$css_files = glob($root_dir . '/css/*.css');
foreach ($css_files as $file) {
    move_file($file, $root_dir . '/assets/css/' . basename($file));
}

// JS फाइल्स को मूव करें
log_message("JS फाइल्स को मूव कर रहे हैं...");
$js_files = glob($root_dir . '/js/*.js');
foreach ($js_files as $file) {
    if (basename($file) !== 'tinymce.min.js') { // tinymce फोल्डर को छोड़ दें
        move_file($file, $root_dir . '/assets/js/' . basename($file));
    }
}

// इंक्लूड फाइल्स को मूव करें
log_message("इंक्लूड फाइल्स को मूव कर रहे हैं...");
if (file_exists($root_dir . '/include')) {
    $include_files = glob($root_dir . '/include/*.php');
    foreach ($include_files as $file) {
        $filename = basename($file);
        // अगर फाइल पहले से includes फोल्डर में मौजूद नहीं है
        if (!file_exists($root_dir . '/includes/' . $filename)) {
            move_file($file, $root_dir . '/includes/' . $filename);
        } else {
            log_message("फाइल पहले से मौजूद है, स्किप कर रहे हैं: $filename");
        }
    }
}

// इंक्लूड्स फाइल्स को उचित सबफोल्डर्स में मूव करें
log_message("इंक्लूड्स फाइल्स को उचित सबफोल्डर्स में मूव कर रहे हैं...");
$includes_files = glob($root_dir . '/includes/*.php');
foreach ($includes_files as $file) {
    $filename = basename($file);
    
    // कॉन्फिगरेशन फाइल्स
    if (strpos($filename, 'config') !== false || strpos($filename, 'Config') !== false) {
        copy($file, $root_dir . '/includes/config/' . $filename);
        log_message("फाइल कॉपी की गई: $filename -> includes/config/");
    }
    // क्लास फाइल्स
    elseif (preg_match('/^[A-Z][a-zA-Z0-9]+\.php$/', $filename)) {
        copy($file, $root_dir . '/includes/classes/' . $filename);
        log_message("फाइल कॉपी की गई: $filename -> includes/classes/");
    }
    // फंक्शन फाइल्स
    elseif (strpos($filename, 'functions') !== false || strpos($filename, 'helper') !== false) {
        copy($file, $root_dir . '/includes/functions/' . $filename);
        log_message("फाइल कॉपी की गई: $filename -> includes/functions/");
    }
    // टेम्पलेट फाइल्स
    elseif (strpos($filename, 'header') !== false || strpos($filename, 'footer') !== false || strpos($filename, 'navigation') !== false) {
        copy($file, $root_dir . '/includes/templates/' . $filename);
        log_message("फाइल कॉपी की गई: $filename -> includes/templates/");
    }
}

// डुप्लिकेट फाइल्स की पहचान करें
log_message("डुप्लिकेट फाइल्स की पहचान कर रहे हैं...");
$duplicate_files = [];

// updated- प्रीफिक्स वाली फाइल्स
$updated_files = glob($root_dir . '/updated-*.php');
foreach ($updated_files as $file) {
    $original_file = $root_dir . '/' . str_replace('updated-', '', basename($file));
    if (file_exists($original_file)) {
        $duplicate_files[] = [
            'type' => 'updated',
            'original' => $original_file,
            'duplicate' => $file
        ];
    }
}

// *_backup.php फाइल्स
$backup_files = glob($root_dir . '/*_backup.php');
foreach ($backup_files as $file) {
    $original_file = $root_dir . '/' . str_replace('_backup', '', basename($file));
    if (file_exists($original_file)) {
        $duplicate_files[] = [
            'type' => 'backup',
            'original' => $original_file,
            'duplicate' => $file
        ];
    }
}

// *.php.bak फाइल्स
$bak_files = glob($root_dir . '/*.php.bak');
foreach ($bak_files as $file) {
    $original_file = $root_dir . '/' . str_replace('.bak', '', basename($file));
    if (file_exists($original_file)) {
        $duplicate_files[] = [
            'type' => 'bak',
            'original' => $original_file,
            'duplicate' => $file
        ];
    }
}

// Copy फाइल्स
$copy_files = glob($root_dir . '/* - Copy.php');
foreach ($copy_files as $file) {
    $original_file = $root_dir . '/' . str_replace(' - Copy', '', basename($file));
    if (file_exists($original_file)) {
        $duplicate_files[] = [
            'type' => 'copy',
            'original' => $original_file,
            'duplicate' => $file
        ];
    }
}

// डुप्लिकेट फाइल्स की रिपोर्ट बनाएं
if (!empty($duplicate_files)) {
    log_message("\nडुप्लिकेट फाइल्स की रिपोर्ट:");
    log_message("==========================\n");
    
    foreach ($duplicate_files as $index => $file) {
        log_message(($index + 1) . ". टाइप: {$file['type']}");
        log_message("   ओरिजिनल: " . basename($file['original']));
        log_message("   डुप्लिकेट: " . basename($file['duplicate']));
        log_message("");
    }
    
    // डुप्लिकेट फाइल्स की रिपोर्ट फाइल बनाएं
    $report_file = $root_dir . '/duplicate_files_report.txt';
    $report_content = "डुप्लिकेट फाइल्स की रिपोर्ट - " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($duplicate_files as $index => $file) {
        $report_content .= ($index + 1) . ". टाइप: {$file['type']}\n";
        $report_content .= "   ओरिजिनल: " . basename($file['original']) . "\n";
        $report_content .= "   डुप्लिकेट: " . basename($file['duplicate']) . "\n\n";
    }
    
    file_put_contents($report_file, $report_content);
    log_message("डुप्लिकेट फाइल्स की रिपोर्ट बनाई गई: $report_file");
}

// स्क्रिप्ट समाप्त होने का समय रिकॉर्ड करें
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

log_message("\nरिऑर्गनाइजेशन पूरा हुआ!");
log_message("एक्जीक्यूशन टाइम: $execution_time सेकंड");
log_message("\nनोट: यह स्क्रिप्ट केवल फाइल्स को मूव करती है, पाथ अपडेट नहीं करती है।");
log_message("कृपया सभी PHP फाइल्स में इंक्लूड पाथ और एसेट पाथ मैन्युअली अपडेट करें।");
log_message("डुप्लिकेट फाइल्स को हटाने से पहले उनकी समीक्षा करें और बैकअप लें।");