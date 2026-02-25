<?php
// Database Index Migration Script
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating performance indexes...\n\n";
    
    // Add indexes for leads table (using assigned_to instead of agent_id)
    $sql = [
        'CREATE INDEX IF NOT EXISTS leads_assigned_status_index ON leads (assigned_to, status)',
        'CREATE INDEX IF NOT EXISTS leads_priority_index ON leads (priority)',
        'CREATE INDEX IF NOT EXISTS leads_created_at_index ON leads (created_at)',
        'CREATE INDEX IF NOT EXISTS leads_lead_score_index ON leads (lead_score)',
        'CREATE INDEX IF NOT EXISTS leads_conversion_probability_index ON leads (conversion_probability)'
    ];
    
    // Add indexes for payouts table
    $sql = array_merge($sql, [
        'CREATE INDEX IF NOT EXISTS payouts_associate_status_index ON payouts (associate_id, status)',
        'CREATE INDEX IF NOT EXISTS payouts_created_at_index ON payouts (created_at)'
    ]);
    
    // Add indexes for users table (email index already exists)
    $sql = array_merge($sql, [
        'CREATE INDEX IF NOT EXISTS users_status_created_index ON users (status, created_at)',
        'CREATE INDEX IF NOT EXISTS users_role_status_index ON users (role, status)'
    ]);
    
    foreach ($sql as $query) {
        try {
            $pdo->exec($query);
            echo "✅ Index created: " . $query . "\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "⚠️  Index already exists: " . $query . "\n";
            } else {
                echo "❌ Error creating index: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n🎉 Database indexes migration completed successfully!\n";
    
    // Show final index status
    echo "\n📊 Final Index Status:\n";
    $tables = ['leads', 'payouts', 'users'];
    foreach ($tables as $table) {
        echo "\n$table table indexes:\n";
        $stmt = $pdo->prepare("SHOW INDEX FROM $table");
        $stmt->execute();
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $indexNames = [];
        foreach ($indexes as $index) {
            if (!in_array($index['Key_name'], $indexNames)) {
                $indexNames[] = $index['Key_name'];
                $columns = array_filter($indexes, function($idx) use ($index) {
                    return $idx['Key_name'] === $index['Key_name'];
                });
                $columnNames = array_map(function($col) {
                    return $col['Column_name'];
                }, $columns);
                echo "  - {$index['Key_name']}: " . implode(', ', $columnNames) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
