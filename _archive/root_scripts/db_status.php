<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "=== DATABASE STATUS ===\n";
    echo "Status: CONNECTED\n\n";
    
    $tables = ['users', 'properties', 'leads', 'bookings', 'associates', 'mlm_profiles', 'mlm_commissions', 'mlm_payouts', 'ai_chat_history', 'payments', 'emissions'];
    foreach($tables as $t) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as c FROM $t");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "$t: " . $row['c'] . " rows\n";
        } catch(PDOException $e) {
            echo "$t: TABLE NOT EXISTS\n";
        }
    }
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
