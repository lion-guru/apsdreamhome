<?php
$sourcePath = __DIR__ . '/../app/Http/Controllers/Auth/';
$archivePath = __DIR__ . '/../_archive/Controllers/Auth/';

if (!is_dir($archivePath)) {
    mkdir($archivePath, 0777, true);
}

$files = [
    'UnifiedRegisterController.php',
    'CoreAuthController.php',
    'SmartRegistrationController.php'
];

$success = [];
$errors = [];

foreach ($files as $file) {
    if (file_exists($sourcePath . $file)) {
        if (rename($sourcePath . $file, $archivePath . $file)) {
            $success[] = "Archived $file";
        } else {
            $errors[] = "Failed to archive $file";
        }
    } else {
        $errors[] = "File $file not found";
    }
}

echo json_encode(['success' => $success, 'errors' => $errors]);
unlink(__FILE__);?>