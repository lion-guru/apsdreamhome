<?php
$archiveDir = __DIR__ . '/../_archive/auth';
if (!is_dir($archiveDir)) {
    mkdir($archiveDir, 0777, true);
}

$filesToArchive = [
    // Controllers
    'app/Http/Controllers/Auth/AdminAuthController.php',
    'app/Http/Controllers/Auth/AgentAuthController.php',
    'app/Http/Controllers/Auth/AssociateAuthController.php',
    'app/Http/Controllers/Auth/CustomerAuthController.php',
    'app/Http/Controllers/Auth/UnifiedRegisterController.php',
    // Views
    'app/views/auth/admin_login.php',
    'app/views/auth/agent_login.php',
    'app/views/auth/agent_register.php',
    'app/views/auth/associate_login.php',
    'app/views/auth/associate_register.php',
    'app/views/auth/customer_login.php',
    'app/views/auth/customer_register.php',
    'app/views/auth/unified_register.php'
];

$output = "";
foreach ($filesToArchive as $file) {
    $src = __DIR__ . '/../' . $file;
    $dst = $archiveDir . '/' . basename($file);
    if (file_exists($src)) {
        if (rename($src, $dst)) {
            $output .= "Archived: $file\n";
        } else {
            $output .= "Failed to archive: $file\n";
        }
    } else {
        $output .= "Not found: $file\n";
    }
}
echo $output;
@unlink(__FILE__);
