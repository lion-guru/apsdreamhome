<?php
$conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Check mlm_rank_benefits
    $stmt = $conn->query("SHOW COLUMNS FROM mlm_rank_benefits LIKE 'tenant_id'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE mlm_rank_benefits ADD COLUMN tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER id");
        echo "Added tenant_id to mlm_rank_benefits.\n";
    } else {
        echo "tenant_id already exists in mlm_rank_benefits.\n";
    }

    // Check mlm_settings
    $stmt = $conn->query("SHOW COLUMNS FROM mlm_settings LIKE 'tenant_id'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE mlm_settings ADD COLUMN tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER id");
        echo "Added tenant_id to mlm_settings.\n";
        
        // Update unique index for mlm_settings
        // We need to drop the old unique index on setting_key, if it exists
        $indexes = $conn->query("SHOW INDEX FROM mlm_settings WHERE Key_name = 'setting_key'")->fetchAll();
        if (count($indexes) > 0) {
            $conn->exec("ALTER TABLE mlm_settings DROP INDEX setting_key");
        }
        
        // Add new unique index on (tenant_id, setting_key)
        $conn->exec("ALTER TABLE mlm_settings ADD UNIQUE INDEX tenant_setting_key (tenant_id, setting_key)");
        echo "Updated unique index on mlm_settings.\n";
    } else {
        echo "tenant_id already exists in mlm_settings.\n";
    }
    
    // Seed initial tenant settings if none exist
    $stmt = $conn->query("SELECT COUNT(*) FROM mlm_settings WHERE tenant_id = 1");
    if ($stmt->fetchColumn() == 0) {
        // We need to populate default settings for tenant 1
        $defaults = [
            'global_cap_pct' => '20',
            'track_a_cap_pct' => '15',
            'track_b_cap_pct' => '3',
            'track_c_cap_pct' => '2',
            'royalty_pool_pct' => '2',
            'gen1_match_pct' => '100',
            'gen2_match_pct' => '50',
            'gen3_match_pct' => '25',
        ];
        
        $insert = $conn->prepare("INSERT INTO mlm_settings (tenant_id, setting_key, setting_value) VALUES (1, ?, ?)");
        foreach ($defaults as $k => $v) {
            $insert->execute([$k, $v]);
        }
        echo "Seeded default mlm_settings for tenant 1.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}?>