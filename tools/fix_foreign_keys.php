<?php
// Configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$constraints = [
    ['table' => 'associates', 'constraint' => 'fk_associates_user', 'column' => 'user_id'],
    ['table' => 'banking_details', 'constraint' => 'banking_details_ibfk_1', 'column' => 'user_id'],
    ['table' => 'customers', 'constraint' => 'fk_customers_user', 'column' => 'user_id'],
    ['table' => 'kyc_details', 'constraint' => 'kyc_details_ibfk_1', 'column' => 'user_id'],
    ['table' => 'kyc_documents', 'constraint' => 'kyc_documents_ibfk_1', 'column' => 'user_id'],
    ['table' => 'kyc_verification', 'constraint' => 'kyc_verification_ibfk_1', 'column' => 'associate_id'] // This one is tricky
];

foreach ($constraints as $c) {
    $table = $c['table'];
    $constraint = $c['constraint'];
    $column = $c['column'];
    
    echo "Processing $table...\n";
    
    try {
        // Drop FK
        $pdo->exec("ALTER TABLE $table DROP FOREIGN KEY $constraint");
        echo "  Dropped constraint $constraint.\n";
        
        // Add new FK to users
        // Note: kyc_verification uses associate_id, but we are repointing it to users.id
        // If it was referencing user.uid, and user.uid == users.id, then this is safe.
        $newConstraint = "fk_{$table}_users_" . rand(1000,9999);
        $pdo->exec("ALTER TABLE $table ADD CONSTRAINT $newConstraint FOREIGN KEY ($column) REFERENCES users(id) ON DELETE CASCADE");
        echo "  Added new constraint to users(id).\n";
        
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}

echo "\nForeign keys updated. Now dropping legacy tables.\n";

try {
    $pdo->exec("DROP TABLE user");
    echo "Dropped 'user' table.\n";
} catch (Exception $e) {
    echo "Error dropping 'user': " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("DROP TABLE agents");
    echo "Dropped 'agents' table.\n";
} catch (Exception $e) {
    echo "Error dropping 'agents': " . $e->getMessage() . "\n";
}

?>
