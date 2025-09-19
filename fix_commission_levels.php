<?php
/**
 * Script to fix MLM commission levels
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhomefinal',
    'user' => 'root',
    'pass' => ''
];

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "=== Fixing MLM Commission Levels ===\n\n";

    // Ensure we're not in a transaction
    while ($pdo->inTransaction()) {
        try {
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            break;
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // First, backup existing levels
        echo "🔍 Backing up existing commission levels...\n";
        $pdo->exec("DROP TABLE IF EXISTS mlm_commission_levels_backup");
        $pdo->exec("CREATE TABLE mlm_commission_levels_backup LIKE mlm_commission_levels");
        $pdo->exec("INSERT INTO mlm_commission_levels_backup SELECT * FROM mlm_commission_levels");
        
        // Clear existing levels for plan 1
        echo "🧹 Clearing existing commission levels for plan 1...\n";
        $pdo->exec("DELETE FROM mlm_commission_levels WHERE plan_id = 1");
        
        // Define commission levels (level => [min, max, percentage])
        $levels = [
            [1, 0, 500000, 5],      // 5% for first 500,000
            [2, 500001, 1000000, 6], // 6% for 500,001 - 1,000,000
            [3, 1000001, 2000000, 7],
            [4, 2000001, 3000000, 8],
            [5, 3000001, 4000000, 9],
            [6, 4000001, 5000000, 10],
            [7, 5000001, 7500000, 11],
            [8, 7500001, 10000000, 12],
            [9, 10000001, 15000000, 13],
            [10, 15000001, 20000000, 14],
            [11, 20000001, 30000000, 15],
            [12, 30000001, 50000000, 16],
            [13, 50000001, null, 17] // 17% for 5,000,001 and above
        ];
        
        // Insert new levels
        echo "🔄 Inserting updated commission levels...\n";
        $stmt = $pdo->prepare("
            INSERT INTO mlm_commission_levels 
            (plan_id, level, min_business, max_business, direct_percentage, created_at)
            VALUES (1, ?, ?, ?, ?, NOW())
        ");
        
        $count = 0;
        foreach ($levels as $level) {
            list($levelNum, $min, $max, $percent) = $level;
            $stmt->execute([
                $levelNum,
                $min,
                $max,
                $percent
            ]);
            $count++;
        }
        
        // Commit changes
        $pdo->commit();
        
        // Verify the count
        $count = $pdo->query("SELECT COUNT(*) as cnt FROM mlm_commission_levels WHERE plan_id = 1")->fetch()['cnt'];
        
        if ($count === 0) {
            throw new Exception("No commission levels were inserted!");
        }
        
        echo "\n✅ Successfully updated $count commission levels for plan 1\n";
        echo "📊 New commission structure:\n";
        
        // Display the new levels
        $result = $pdo->query("
            SELECT level, 
                   CONCAT('₹', FORMAT(min_business, 2)) as min_business,
                   IFNULL(CONCAT('₹', FORMAT(max_business, 2)), '∞') as max_business,
                   CONCAT(direct_percentage, '%') as commission_rate
            FROM mlm_commission_levels
            WHERE plan_id = 1
            ORDER BY level
        ");
        
        echo "\n" . str_repeat("-", 70) . "\n";
        echo str_pad("Level", 8) . str_pad("Min Business", 20) . 
             str_pad("Max Business", 20) . "Commission Rate\n";
        echo str_repeat("-", 70) . "\n";
        
        foreach ($result as $row) {
            echo str_pad($row['level'], 8) . 
                 str_pad($row['min_business'], 20) . 
                 str_pad($row['max_business'], 20) . 
                 $row['commission_rate'] . "\n";
        }
        
        echo str_repeat("-", 70) . "\n";
        
        // Verify the changes
        echo "\n🔍 Verifying commission levels...\n";
        $testAmounts = [
            250000,   // Level 1
            750000,   // Level 2
            1500000,  // Level 3
            2500000,  // Level 4
            3500000,  // Level 5
            4500000,  // Level 6
            6000000,  // Level 7
            9000000,  // Level 8
            12000000, // Level 9
            18000000, // Level 10
            25000000, // Level 11
            40000000, // Level 12
            60000000  // Level 13
        ];
        
        $stmt = $pdo->prepare("
            SELECT direct_percentage 
            FROM mlm_commission_levels 
            WHERE plan_id = 1 
            AND min_business <= ? 
            AND (max_business >= ? OR max_business IS NULL)
            ORDER BY level
            LIMIT 1
        ");
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "COMMISSION RATE VERIFICATION\n";
        echo str_repeat("=", 70) . "\n\n";
        
        foreach ($testAmounts as $amount) {
            $stmt->execute([$amount, $amount]);
            $row = $stmt->fetch();
            
            if ($row) {
                $commission = ($amount * $row['direct_percentage']) / 100;
                echo "Amount: ₹" . number_format($amount, 2) . " -> " . 
                     $row['direct_percentage'] . "% = ₹" . 
                     number_format($commission, 2) . "\n";
            } else {
                echo "❌ No commission rate found for ₹" . number_format($amount, 2) . "\n";
            }
        }
        
        echo "\n✅ Commission levels have been successfully updated and verified!\n";
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            try {
                $pdo->rollBack();
                echo "❌ Transaction rolled back due to error\n";
            } catch (Exception $rollbackEx) {
                echo "❌ Error during rollback: " . $rollbackEx->getMessage() . "\n";
            }
        }
        throw new Exception("Failed to update commission levels: " . $e->getMessage());
    }
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
