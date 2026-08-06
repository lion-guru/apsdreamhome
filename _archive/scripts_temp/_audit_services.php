<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$dir = 'app/Services';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$results = [];

foreach ($it as $f) {
    if ($f->getExtension() !== 'php') continue;
    $path = $f->getPathname();
    $content = file_get_contents($path);

    if (strpos($content, 'INSERT INTO') === false &&
        strpos($content, 'UPDATE ') === false &&
        strpos($content, 'DELETE FROM') === false &&
        stripos($content, 'UPDATE ') === false) continue;

    if (strpos($content, 'ServiceTenantTrait') !== false ||
        strpos($content, 'TenantContext') !== false ||
        strpos($content, 'tenant_id') !== false) continue;

    preg_match_all('/(?:INSERT INTO|UPDATE|DELETE FROM)\s+`?(\w+)/i', $content, $matches);
    $tables = array_unique($matches[1]);

    $writeTables = [];
    foreach ($tables as $t) {
        $r = $db->query("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$t'");
        $row = $r->fetch();
        if ($row['c'] > 0) {
            $cols = $db->query("SHOW COLUMNS FROM `$t` WHERE Field='tenant_id'");
            $hasTid = $cols->fetch() !== false;
            $writeTables[] = "$t" . ($hasTid ? "[HAS_TID]" : "[NO_TID]");
        }
    }

    if (!empty($writeTables)) {
        $results[] = basename($path) . ": " . implode(", ", $writeTables);
    }
}

foreach ($results as $r) echo $r . "\n";
echo "\nTotal with real writes to existing tables: " . count($results) . "\n";
