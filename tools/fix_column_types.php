<?php
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$alters = [
    "ALTER TABLE associates MODIFY user_id BIGINT(20) UNSIGNED NOT NULL",
    "ALTER TABLE associates MODIFY sponsor_id BIGINT(20) UNSIGNED DEFAULT NULL",
    "ALTER TABLE banking_details MODIFY user_id BIGINT(20) UNSIGNED NOT NULL",
    "ALTER TABLE customers MODIFY user_id BIGINT(20) UNSIGNED DEFAULT NULL",
    "ALTER TABLE kyc_details MODIFY user_id BIGINT(20) UNSIGNED NOT NULL",
    "ALTER TABLE kyc_documents MODIFY user_id BIGINT(20) UNSIGNED NOT NULL",
    "ALTER TABLE kyc_verification MODIFY associate_id BIGINT(20) UNSIGNED NOT NULL"
];

foreach ($alters as $sql) {
    try {
        echo "Executing: $sql\n";
        $pdo->exec($sql);
        echo "  Success.\n";
    } catch (PDOException $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}

echo "\nColumn types updated. Now attempting to fix foreign keys.\n";

// Now run the foreign key fix again (reusing logic from fix_foreign_keys.php but refined)
$constraints = [
    ['table' => 'associates', 'constraint' => 'fk_associates_user', 'column' => 'user_id', 'ref_table' => 'users', 'ref_col' => 'id'],
    ['table' => 'banking_details', 'constraint' => 'fk_banking_details_user', 'column' => 'user_id', 'ref_table' => 'users', 'ref_col' => 'id'],
    ['table' => 'customers', 'constraint' => 'fk_customers_user', 'column' => 'user_id', 'ref_table' => 'users', 'ref_col' => 'id'],
    ['table' => 'kyc_details', 'constraint' => 'fk_kyc_details_user', 'column' => 'user_id', 'ref_table' => 'users', 'ref_col' => 'id'],
    ['table' => 'kyc_documents', 'constraint' => 'fk_kyc_documents_user', 'column' => 'user_id', 'ref_table' => 'users', 'ref_col' => 'id'],
    ['table' => 'kyc_verification', 'constraint' => 'fk_kyc_verification_associate', 'column' => 'associate_id', 'ref_table' => 'associates', 'ref_col' => 'id']
];

foreach ($constraints as $c) {
    $table = $c['table'];
    $constraint = $c['constraint'];
    $column = $c['column'];
    $ref_table = $c['ref_table'];
    $ref_col = $c['ref_col'];
    
    echo "Adding FK to $table...\n";
    
    try {
        // Drop if exists (might have different name, try generic or standard names)
        // We already dropped some in previous step, but let's be sure.
        // Or we can just try adding.
        $sql = "ALTER TABLE $table ADD CONSTRAINT $constraint FOREIGN KEY ($column) REFERENCES $ref_table($ref_col) ON DELETE CASCADE";
        $pdo->exec($sql);
        echo "  Added constraint $constraint.\n";
    } catch (PDOException $e) {
        echo "  Error adding FK (might already exist or other issue): " . $e->getMessage() . "\n";
    }
}

echo "\nNow dropping legacy tables.\n";

try {
    $pdo->exec("DROP TABLE IF EXISTS user");
    echo "Dropped 'user' table.\n";
} catch (Exception $e) {
    echo "Error dropping 'user': " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("DROP TABLE IF EXISTS agents");
    echo "Dropped 'agents' table.\n";
} catch (Exception $e) {
    echo "Error dropping 'agents': " . $e->getMessage() . "\n";
}
?>
