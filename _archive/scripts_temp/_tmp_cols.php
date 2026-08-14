<?php
define('APP_ROOT', __DIR__ . '/../');
require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();
use App\Core\Database\Database;
$db = Database::getInstance();
echo "=== ai_call_sessions columns ===\n";
foreach ($db->fetchAll('SHOW COLUMNS FROM ai_call_sessions') as $c) {
    echo $c['Field'] . ' ' . $c['Type'] . ($c['Null'] === 'NO' ? ' NOT NULL' : '') . PHP_EOL;
}
echo "=== existing voice chat routes? ===\n";
$chk = $db->fetch("SELECT COUNT(*) c FROM ai_call_sessions WHERE 1=0");?>