<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhomefinal', 'root', '');
    
    echo "=== COMMISSION_TRANSACTIONS TABLE STRUCTURE ===\n";
    $result = $pdo->query('DESCRIBE commission_transactions');
    while($row = $result->fetch()) {
        echo $row['Field'] . ' (' . $row['Type'] . ")\n";
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>