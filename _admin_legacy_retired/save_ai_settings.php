<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['superadmin']);
$file = __DIR__ . '/../includes/config/ai_settings.php';
if(isset($_POST['settings'])) {
    $settings = $_POST['settings'];
    if(is_array($settings)) {
        $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
        file_put_contents($file, $content);
        echo json_encode(['success'=>true,'message'=>'AI/Automation settings updated successfully']);
        exit;
    }
}
echo json_encode(['success'=>false,'message'=>'Invalid AI/Automation settings data']);
