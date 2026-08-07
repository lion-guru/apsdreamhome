<?php
$conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->query("SHOW COLUMNS FROM mlm_rank_benefits");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('tenant_id', $cols)) {
    $conn->exec("ALTER TABLE mlm_rank_benefits ADD COLUMN tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER id");
    echo "Added tenant_id to mlm_rank_benefits\n";
} else {
    echo "tenant_id already in mlm_rank_benefits\n";
}

$stmt = $conn->query("SHOW COLUMNS FROM mlm_settings");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('tenant_id', $cols)) {
    $conn->exec("ALTER TABLE mlm_settings ADD COLUMN tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER id");
    echo "Added tenant_id to mlm_settings\n";
    $conn->exec("ALTER TABLE mlm_settings DROP INDEX setting_key");
    $conn->exec("ALTER TABLE mlm_settings ADD UNIQUE INDEX tenant_setting_key (tenant_id, setting_key)");
} else {
    echo "tenant_id already in mlm_settings\n";
}
