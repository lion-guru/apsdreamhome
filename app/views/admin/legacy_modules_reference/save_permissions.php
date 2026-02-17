<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['superadmin']);
$file = __DIR__ . '/../includes/config/role_permissions.php';
if(isset($_POST['permissions'])) {
    $permissions = $_POST['permissions'];
    // Validate structure (simple check)
    if(is_array($permissions)) {
        $content = "<?php\nreturn " . var_export($permissions, true) . ";\n";
        file_put_contents($file, $content);
        echo json_encode(['success'=>true,'message'=>'Permissions updated successfully']);
        exit;
    }
}
echo json_encode(['success'=>false,'message'=>'Invalid permissions data']);
