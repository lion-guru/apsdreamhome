<?php
header('Content-Type: text/plain');
try {
    $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
    $db = new PDO($dsn, 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking marketing_campaign_recipients columns:\n";
    $stmt = $db->query("DESCRIBE marketing_campaign_recipients");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\nChecking marketing_campaigns columns:\n";
    $stmt = $db->query("DESCRIBE marketing_campaigns");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\nDropping old foreign key constraint if it exists:\n";
    try {
        $db->exec("ALTER TABLE marketing_campaign_recipients DROP FOREIGN KEY fk_marketing_campaign_recipients_campaign_id");
        echo "Dropped fk_marketing_campaign_recipients_campaign_id successfully.\n";
    } catch (PDOException $e) {
        echo "Error or already dropped: " . $e->getMessage() . "\n";
    }

    echo "\nAdding new foreign key constraint:\n";
    try {
        $db->exec("ALTER TABLE marketing_campaign_recipients ADD CONSTRAINT fk_marketing_campaign_recipients_campaign_id FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns (id) ON DELETE CASCADE");
        echo "Added new FK constraint successfully.\n";
    } catch (PDOException $e) {
        echo "Error adding new FK constraint: " . $e->getMessage() . "\n";
    }

} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
