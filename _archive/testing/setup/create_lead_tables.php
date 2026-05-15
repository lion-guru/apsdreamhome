<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1:3307;dbname=apsdreamhome', 'root', '');
    
    // Create lead_statuses table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lead_statuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            color VARCHAR(20) DEFAULT 'primary',
            sort_order INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Created lead_statuses table\n";
    
    // Create lead_sources table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lead_sources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Created lead_sources table\n";
    
    // Create lead_activities table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lead_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_id INT,
            activity_type VARCHAR(50),
            description TEXT,
            created_by INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lead_id (lead_id)
        )
    ");
    echo "Created lead_activities table\n";
    
    // Insert default lead statuses
    $statuses = [
        ['New', 'info', 1],
        ['Contacted', 'primary', 2],
        ['Qualified', 'success', 3],
        ['Proposal', 'warning', 4],
        ['Negotiation', 'warning', 5],
        ['Closed Won', 'success', 6],
        ['Closed Lost', 'danger', 7]
    ];
    
    foreach ($statuses as $status) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO lead_statuses (name, color, sort_order) VALUES (?, ?, ?)");
            $stmt->execute($status);
        } catch (Exception $e) {}
    }
    echo "Added default lead statuses\n";
    
    // Insert default lead sources
    $sources = ['Website', 'Phone', 'Email', 'Social Media', 'Referral', 'Walk-in', 'Advertisement'];
    foreach ($sources as $source) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO lead_sources (name) VALUES (?)");
            $stmt->execute([$source]);
        } catch (Exception $e) {}
    }
    echo "Added default lead sources\n";
    
    // Check if leads table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'leads'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS leads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(50),
                company VARCHAR(255),
                status INT DEFAULT 1,
                source_id INT,
                assigned_to INT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "Created leads table\n";
    }
    
    // Add missing columns to leads if needed
    $stmt = $pdo->query("DESCRIBE leads");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    
    if (!in_array('source_id', $colNames)) {
        $pdo->exec("ALTER TABLE leads ADD COLUMN source_id INT AFTER status");
    }
    if (!in_array('assigned_to', $colNames)) {
        $pdo->exec("ALTER TABLE leads ADD COLUMN assigned_to INT AFTER source_id");
    }
    echo "Updated leads table structure\n";
    
    echo "\nDone!\n";
    
} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
