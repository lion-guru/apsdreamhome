<?php
$conn = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');

echo "=== ENTERPRISE FEATURES STATUS ===\n";

$tables = [
    'marketing_campaigns', 
    'customer_documents', 
    'payments', 
    'payment_gateway_config', 
    'third_party_integrations', 
    'role_change_approvals', 
    'feedback_tickets'
];

foreach($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if($result && $result->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as c FROM $table")->fetch_assoc()['c'];
        echo "✅ $table - EXISTS ($count records)\n";
    } else {
        echo "❌ $table - MISSING\n";
    }
}

echo "\n=== ENHANCED FEATURES IN YOUR NEW DATABASE ===\n";

// Check enhanced customer_documents table
$result = $conn->query("DESCRIBE customer_documents");
if ($result) {
    echo "✅ customer_documents table structure:\n";
    while($row = $result->fetch_assoc()) {
        echo "   - {$row['Field']} ({$row['Type']})\n";
    }
}

echo "\n=== PAYMENT GATEWAY CONFIGURATIONS ===\n";
$result = $conn->query("SELECT * FROM payment_gateway_config LIMIT 3");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "✅ Gateway: {$row['provider']} - Status: {$row['status']}\n";
    }
} else {
    echo "ℹ️  No payment gateways configured yet\n";
}

$conn->close();
?>