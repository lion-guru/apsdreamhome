<?php
$source = __DIR__ . '/../app/Http/Controllers/Auth/CoreAuthController.php';
$destLogin = __DIR__ . '/../app/Http/Controllers/Auth/LoginController.php';
$destRegister = __DIR__ . '/../app/Http/Controllers/Auth/RegisterController.php';

if (copy($source, $destLogin)) {
    echo "Copied to LoginController.php\n";
    // Also rename class in file
    $content = file_get_contents($destLogin);
    $content = str_replace('class CoreAuthController', 'class LoginController', $content);
    file_put_contents($destLogin, $content);
} else {
    echo "Failed to copy LoginController.php\n";
}

if (copy($source, $destRegister)) {
    echo "Copied to RegisterController.php\n";
    // Also rename class in file
    $content = file_get_contents($destRegister);
    $content = str_replace('class CoreAuthController', 'class RegisterController', $content);
    file_put_contents($destRegister, $content);
} else {
    echo "Failed to copy RegisterController.php\n";
}
