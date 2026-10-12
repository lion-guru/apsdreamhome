<?php
// Idempotent: allow 'sajra_scan' in colony_media.media_type (revenue map scans).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $cur = $db->fetchOne("SHOW COLUMNS FROM colony_media LIKE 'media_type'");
    preg_match_all("/'([^']+)'/", $cur['Type'], $m);
    $have = $m[1];
    if (in_array('sajra_scan', $have, true)) {
        echo "media_type already includes sajra_scan\n";
        exit(0);
    }
    $union = array_values(array_unique(array_merge($have, ['sajra_scan'])));
    $enumList = "'" . implode("','", $union) . "'";
    $db->execute("ALTER TABLE colony_media MODIFY COLUMN media_type ENUM($enumList) NOT NULL");
    echo "extended with: sajra_scan\n";
} catch (\Throwable $e) {
    echo 'MIGRATE-FAILED: ' . substr($e->getMessage(), 0, 200) . PHP_EOL;
    exit(1);
}
