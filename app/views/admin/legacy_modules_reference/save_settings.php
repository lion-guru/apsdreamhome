<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['superadmin']);
$file = __DIR__ . '/../includes/config/site_settings.php';
if(isset($_POST['settings'])) {
    $settings = $_POST['settings'];
    if(is_array($settings)) {
        $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
        file_put_contents($file, $content);
        echo json_encode(['success'=>true,'message'=>'Settings updated successfully']);
        exit;
    }
}
echo json_encode(['success'=>false,'message'=>'Invalid settings data']);
