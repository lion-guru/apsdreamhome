<?php
$dir = __DIR__ . '/_archive/dead_controllers/auth_legacy';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$files = [
    __DIR__ . '/app/Http/Controllers/Auth/CoreAuthController.php',
    __DIR__ . '/app/Http/Controllers/Auth/UnifiedRegisterController.php',
    __DIR__ . '/app/Http/Controllers/Auth/SmartRegistrationController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        rename($file, $dir . '/' . basename($file));
        echo "Archived: " . basename($file) . "\n";
    }
}
echo "Done.";?>