<?php
$file = 'c:/xampp/htdocs/apsdreamhome/app/views/layouts/admin_header.php';
$content = file_get_contents($file);

$baseUrlCode = <<<'PHP'

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    if (substr($script, -7) === '/public') {
        $script = substr($script, 0, -7);
    }
    $script = str_replace('/index.php', '', $script);
    define('BASE_URL', rtrim("$protocol://$host$script", '/'));
}
PHP;

// Insert after the comment block
$content = preg_replace('/(\*\/\n)/', '$1' . $baseUrlCode, $content, 1);

file_put_contents($file, $content);
echo "Fixed admin_header.php\n";
