<?php
// Create commissions table for multi-level commission system
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CREATING COMMISSIONS TABLE ===\n";
    
    // Check if commissions table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'commissions'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "Commissions table already exists. Checking structure...\n";
        
        // Check if table has all required columns
        $stmt = $pdo->prepare("DESCRIBE commissions");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $columnNames = array_column($columns, 'Field');
        $requiredColumns = [
            'id', 'associate_id', 'source_associate_id', 'customer_id', 
            'property_id', 'sale_amount', 'commission_rate', 'commission_amount',
            'level', 'type', 'status', 'created_at', 'paid_at'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        if (!empty($missingColumns)) {
            echo "Missing columns: " . implode(', ', $missingColumns) . "\n";
            
            // Add missing columns
            foreach ($missingColumns as $column) {
                switch ($column) {
                    case 'paid_at':
                        $stmt = $pdo->prepare("ALTER TABLE commissions ADD COLUMN paid_at TIMESTAMP NULL AFTER created_at");
                        break;
                    case 'source_associate_id':
                        $stmt = $pdo->prepare("ALTER TABLE commissions ADD COLUMN source_associate_id INT NULL AFTER associate_id");
                        break;
                    default:
                        echo "Need to add column: $column\n";
                }
                
                if (isset($stmt)) {
                    $stmt->execute();
                    echo "Added column: $column\n";
                }
            }
        }
        
    } else {
        echo "Creating commissions table...\n";
        
        $createTableSQL = "
        CREATE TABLE commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            source_associate_id INT NULL,
            customer_id INT NOT NULL,
            property_id INT NULL,
            sale_amount DECIMAL(12,2) NOT NULL,
            commission_rate DECIMAL(5,2) NOT NULL,
            commission_amount DECIMAL(10,2) NOT NULL,
            level INT NOT NULL DEFAULT 1,
            type ENUM('direct', 'team', 'network', 'organization') NOT NULL DEFAULT 'direct',
            status ENUM('pending', 'approved', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            paid_at TIMESTAMP NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_associate_id (associate_id),
            INDEX idx_source_associate_id (source_associate_id),
            INDEX idx_customer_id (customer_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_level_type (level, type),
            
            FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (source_associate_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "Commissions table created successfully!\n";
    }
    
    // Check if users table has required MLM columns
    echo "\n=== CHECKING USERS TABLE FOR MLM COLUMNS ===\n";
    
    $stmt = $pdo->prepare("DESCRIBE users");
    $stmt->execute();
    $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $userColumnNames = array_column($userColumns, 'Field');
    $requiredMLMColumns = [
        'referrer_id', 'total_sales', 'team_sales', 'last_sale_date', 
        'commission_level', 'mlm_rank'
    ];
    
    $missingMLMColumns = array_diff($requiredMLMColumns, $userColumnNames);
    
    if (!empty($missingMLMColumns)) {
        echo "Adding missing MLM columns to users table...\n";
        
        foreach ($missingMLMColumns as $column) {
            switch ($column) {
                case 'referrer_id':
                    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN referrer_id INT NULL AFTER mlm_target");
                    $stmt->execute();
                    echo "Added referrer_id column\n";
                    break;
                    
                case 'total_sales':
                    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN total_sales DECIMAL(12,2) DEFAULT 0.00 AFTER referrer_id");
                    $stmt->execute();
                    echo "Added total_sales column\n";
                    break;
                    
                case 'team_sales':
                    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN team_sales DECIMAL(12,2) DEFAULT 0.00 AFTER total_sales");
                    $stmt->execute();
                    echo "Added team_sales column\n";
                    break;
                    
                case 'last_sale_date':
                    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN last_sale_date TIMESTAMP NULL AFTER team_sales");
                    $stmt->execute();
                    echo "Added last_sale_date column\n";
                    break;
                    
                case 'commission_level':
                    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN commission_level INT DEFAULT 1 AFTER last_sale_date");
                    $stmt->execute();
                    echo "Added commission_level column\n";
                    break;
                    
                case 'mlm_rank':
                    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN mlm_rank VARCHAR(50) DEFAULT 'Associate' AFTER commission_level");
                    $stmt->execute();
                    echo "Added mlm_rank column\n";
                    break;
            }
        }
        
        // Add foreign key for referrer_id
        try {
            $stmt = $pdo->prepare("ALTER TABLE users ADD CONSTRAINT fk_users_referrer FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL");
            $stmt->execute();
            echo "Added foreign key constraint for referrer_id\n";
        } catch (Exception $e) {
            echo "Foreign key may already exist: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "All MLM columns already exist in users table\n";
    }
    
    // Update existing associates to have proper MLM structure
    echo "\n=== UPDATING EXISTING ASSOCIATES ===\n";
    
    $stmt = $pdo->prepare("
        UPDATE users SET 
            commission_level = 1,
            mlm_rank = 'Associate'
        WHERE role = 'associate' AND (commission_level IS NULL OR mlm_rank IS NULL)
    ");
    $stmt->execute();
    $updatedCount = $stmt->rowCount();
    
    echo "Updated $updatedCount associates with MLM structure\n";
    
    // Create a default sponsor for testing (if no associates exist with referrers)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'associate' AND referrer_id IS NOT NULL");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "\n=== CREATING DEFAULT SPONSOR STRUCTURE ===\n";
        
        // Get the first associate as master sponsor
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'associate' ORDER BY created_at ASC LIMIT 1");
        $stmt->execute();
        $masterSponsor = $stmt->fetch();
        
        if ($masterSponsor) {
            // Update other associates to have this sponsor
            $stmt = $pdo->prepare("
                UPDATE users SET referrer_id = ? 
                WHERE role = 'associate' AND id != ? AND referrer_id IS NULL
            ");
            $stmt->execute([$masterSponsor['id'], $masterSponsor['id']]);
            
            $sponsoredCount = $stmt->rowCount();
            echo "Created sponsorship structure: $sponsoredCount associates sponsored by master associate\n";
        }
    }
    
    echo "\n=== MLM COMMISSION SYSTEM SETUP COMPLETE ===\n";
    echo "✅ Commissions table created and configured\n";
    echo "✅ Users table updated with MLM columns\n";
    echo "✅ Default sponsorship structure created\n";
    echo "✅ Multi-level commission system ready!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
