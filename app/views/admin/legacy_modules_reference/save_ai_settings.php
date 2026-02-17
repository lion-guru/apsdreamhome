<?php
/**
 * Save AI Settings - Secured version
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/SecurityUtility.php';

// Access control
if (!SecurityUtility::hasRole(['superadmin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// CSRF check
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$file = __DIR__ . '/../includes/config/ai_settings.php';

if (isset($_POST['settings'])) {
    $settings = $_POST['settings'];
    
    if (is_array($settings)) {
        // Basic sanitization: ensuring keys and values are treated safely
        // Since we're writing to a PHP file via var_export, we need to be careful
        // about any executable code within the arrays.
        
        $safe_settings = [];
        foreach ($settings as $key => $value) {
            $clean_key = SecurityUtility::sanitizeInput($key, 'string');
            if (is_array($value)) {
                $clean_value = [];
                foreach ($value as $k => $v) {
                    $clean_value[SecurityUtility::sanitizeInput($k, 'string')] = SecurityUtility::sanitizeInput($v, 'string');
                }
            } else {
                $clean_value = SecurityUtility::sanitizeInput($value, 'string');
            }
            $safe_settings[$clean_key] = $clean_value;
        }

        $content = "<?php\n/**\n * Auto-generated AI settings file\n * Updated by: " . ($_SESSION['username'] ?? 'unknown') . "\n * Updated at: " . date('Y-m-d H:i:s') . "\n */\nreturn " . var_export($safe_settings, true) . ";\n";
        
        if (file_put_contents($file, $content) !== false) {
            echo json_encode(['success' => true, 'message' => 'AI/Automation settings updated successfully']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid AI/Automation settings data']);
