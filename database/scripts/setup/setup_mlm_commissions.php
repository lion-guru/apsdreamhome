<?php
/**
 * Script to set up MLM commission structure
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
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
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Start transaction
    $pdo->beginTransaction();

    // 1. Create mlm_commission_plans table if not exists
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlm_commission_plans` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Create mlm_commission_levels table if not exists
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlm_commission_levels` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `plan_id` INT NOT NULL,
        `level` INT NOT NULL,
        `direct_percentage` DECIMAL(5,2) NOT NULL,
        `min_business` DECIMAL(15,2) DEFAULT 0,
        `max_business` DECIMAL(15,2) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `plan_level` (`plan_id`, `level`),
        CONSTRAINT `fk_plan_levels` FOREIGN KEY (`plan_id`) 
            REFERENCES `mlm_commission_plans`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Add columns to associates table if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM `associates` LIKE 'commission_plan_id'")->fetch();
    if (!$columns) {
        $pdo->exec("
        ALTER TABLE `associates` 
        ADD COLUMN `commission_plan_id` INT NULL AFTER `status`,
        ADD COLUMN `current_level` INT DEFAULT 1,
        ADD COLUMN `total_business` DECIMAL(15,2) DEFAULT 0.00,
        ADD COLUMN `direct_business` DECIMAL(15,2) DEFAULT 0.00,
        ADD COLUMN `team_business` DECIMAL(15,2) DEFAULT 0.00,
        ADD CONSTRAINT `fk_associate_plan` 
            FOREIGN KEY (`commission_plan_id`) 
            REFERENCES `mlm_commission_plans`(`id`) 
            ON DELETE SET NULL;");
    }

    // 4. Add columns to mlm_commissions table if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM `mlm_commissions` LIKE 'commission_plan_id'")->fetch();
    if (!$columns) {
        $pdo->exec("
        ALTER TABLE `mlm_commissions`
        ADD COLUMN `commission_plan_id` INT NULL AFTER `id`,
        ADD COLUMN `level` INT NOT NULL DEFAULT 1,
        ADD COLUMN `direct_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        ADD COLUMN `difference_percentage` DECIMAL(5,2) DEFAULT 0.00,
        ADD COLUMN `upline_id` INT NULL,
        ADD COLUMN `is_direct` TINYINT(1) DEFAULT 0,
        ADD CONSTRAINT `fk_commission_plan` 
            FOREIGN KEY (`commission_plan_id`) 
            REFERENCES `mlm_commission_plans`(`id`) 
            ON DELETE SET NULL,
        ADD CONSTRAINT `fk_commission_upline` 
            FOREIGN KEY (`upline_id`) 
            REFERENCES `users`(`id`) 
            ON DELETE SET NULL;");
    }

    // 5. Create UpdateTeamBusiness procedure if not exists
    $procedureExists = $pdo->query("
        SELECT COUNT(*) as count 
        FROM information_schema.ROUTINES 
        WHERE ROUTINE_SCHEMA = '{$dbConfig['dbname']}' 
        AND ROUTINE_TYPE = 'PROCEDURE' 
        AND ROUTINE_NAME = 'UpdateTeamBusiness'
    ")->fetch()['count'] > 0;

    if (!$procedureExists) {
        // Drop the procedure if it exists (handling the case where it might exist but with different parameters)
        $pdo->exec("DROP PROCEDURE IF EXISTS UpdateTeamBusiness");
        
        // Create the procedure without using DELIMITER
        $pdo->exec("
        CREATE PROCEDURE UpdateTeamBusiness(
            IN p_associate_id INT,
            IN p_amount DECIMAL(15,2)
        )
        BEGIN
            DECLARE v_parent_id INT;
            
            -- Get immediate parent
            SELECT sponsor_id INTO v_parent_id
            FROM associates
            WHERE id = p_associate_id;
            
            -- Recursively update team business up the chain
            WHILE v_parent_id IS NOT NULL DO
                UPDATE associates 
                SET team_business = team_business + p_amount,
                    total_business = direct_business + (team_business + p_amount)
                WHERE id = v_parent_id;
                
                -- Move up to next upline
                SELECT sponsor_id INTO v_parent_id
                FROM associates
                WHERE id = v_parent_id;
            END WHILE;
        END");
    }

    // 6. Create CalculateMLMCommission procedure if not exists
    $procedureExists = $pdo->query("
        SELECT COUNT(*) as count 
        FROM information_schema.ROUTINES 
        WHERE ROUTINE_SCHEMA = '{$dbConfig['dbname']}' 
        AND ROUTINE_TYPE = 'PROCEDURE' 
        AND ROUTINE_NAME = 'CalculateMLMCommission'
    ")->fetch()['count'] > 0;

    if (!$procedureExists) {
        // Drop the procedure if it exists
        $pdo->exec("DROP PROCEDURE IF EXISTS CalculateMLMCommission");
        
        // Create the procedure without using DELIMITER
        $pdo->exec("
        CREATE PROCEDURE CalculateMLMCommission(
            IN p_transaction_id INT,
            IN p_amount DECIMAL(15,2)
        )
        BEGIN
            DECLARE v_associate_id INT;
            DECLARE v_plan_id INT;
            DECLARE v_level INT;
            DECLARE v_direct_percent DECIMAL(5,2);
            DECLARE v_parent_id INT;
            DECLARE v_parent_level INT;
            DECLARE v_parent_percent DECIMAL(5,2);
            DECLARE v_difference_percent DECIMAL(5,2);
            
            -- Get associate details
            SELECT a.id, a.commission_plan_id, a.current_level, a.sponsor_id
            INTO v_associate_id, v_plan_id, v_level, v_parent_id
            FROM transactions t
            JOIN associates a ON t.associate_id = a.id
            WHERE t.id = p_transaction_id;
            
            -- Get direct commission percentage based on business volume
            SELECT direct_percentage INTO v_direct_percent
            FROM mlm_commission_levels
            WHERE plan_id = v_plan_id 
            AND level = v_level
            AND (min_business IS NULL OR p_amount >= min_business)
            AND (max_business IS NULL OR p_amount <= max_business)
            ORDER BY level DESC
            LIMIT 1;
            
            -- If no specific level found, get default for the level
            IF v_direct_percent IS NULL THEN
                SELECT direct_percentage INTO v_direct_percent
                FROM mlm_commission_levels
                WHERE plan_id = v_plan_id AND level = v_level
                ORDER BY min_business
                LIMIT 1;
            END IF;
            
            -- Insert direct commission
            INSERT INTO mlm_commissions (
                user_id, transaction_id, commission_amount, 
                commission_type, status, level, 
                direct_percentage, is_direct, created_at,
                commission_plan_id
            ) VALUES (
                v_associate_id, p_transaction_id, 
                (p_amount * IFNULL(v_direct_percent, 0) / 100),
                'direct_commission', 'pending', v_level,
                IFNULL(v_direct_percent, 0), 1, NOW(),
                v_plan_id
            );
            
            -- Calculate difference for upline
            IF v_parent_id IS NOT NULL THEN
                -- Get parent's level and percentage
                SELECT a.current_level, cl.direct_percentage
                INTO v_parent_level, v_parent_percent
                FROM associates a
                LEFT JOIN mlm_commission_levels cl ON cl.plan_id = a.commission_plan_id 
                    AND cl.level = a.current_level
                WHERE a.id = v_parent_id;
                
                -- If parent has higher level, calculate difference
                IF v_parent_level > v_level AND v_parent_percent > IFNULL(v_direct_percent, 0) THEN
                    SET v_difference_percent = v_parent_percent - IFNULL(v_direct_percent, 0);
                    
                    -- Insert difference commission for upline
                    INSERT INTO mlm_commissions (
                        user_id, transaction_id, commission_amount, 
                        commission_type, status, level, 
                        direct_percentage, difference_percentage,
                        upline_id, is_direct, created_at,
                        commission_plan_id
                    ) VALUES (
                        v_parent_id, p_transaction_id, 
                        (p_amount * v_difference_percent / 100),
                        'difference_commission', 'pending', v_parent_level,
                        v_parent_percent, v_difference_percent,
                        v_associate_id, 0, NOW(),
                        v_plan_id
                    );
                END IF;
            END IF;
            
            -- Update associate's business volume
            UPDATE associates 
            SET direct_business = direct_business + p_amount,
                total_business = direct_business + p_amount + team_business
            WHERE id = v_associate_id;
            
            -- Update team business for upline chain
            CALL UpdateTeamBusiness(v_associate_id, p_amount);
        END");
    }

    // 7. Insert default commission plan if none exists
    $planExists = $pdo->query("SELECT COUNT(*) as count FROM mlm_commission_plans")->fetch()['count'] > 0;
    
    if (!$planExists) {
        // Insert default plan
        $pdo->exec("
        INSERT INTO mlm_commission_plans (name, description) 
        VALUES ('Standard Plan', 'Standard MLM commission structure with 13 levels');");
        
        $planId = $pdo->lastInsertId();
        
        // Insert commission levels
        $pdo->exec("
        INSERT INTO mlm_commission_levels 
            (plan_id, level, direct_percentage, min_business, max_business)
        VALUES 
            ($planId, 1, 5.00, 0, 50000),
            ($planId, 2, 7.00, 50001, 100000),
            ($planId, 3, 8.00, 100001, 200000),
            ($planId, 4, 9.00, 200001, 300000),
            ($planId, 5, 10.00, 300001, 500000),
            ($planId, 6, 11.00, 500001, 1000000),
            ($planId, 7, 12.00, 1000001, 1500000),
            ($planId, 8, 13.00, 1500001, 2000000),
            ($planId, 9, 14.00, 2000001, 3000000),
            ($planId, 10, 15.00, 3000001, 5000000),
            ($planId, 11, 16.00, 5000001, 10000000),
            ($planId, 12, 17.00, 10000001, 20000000),
            ($planId, 13, 18.00, 20000001, NULL);");
        
        // Update all existing associates to use this plan
        $pdo->exec("UPDATE associates SET commission_plan_id = $planId WHERE commission_plan_id IS NULL");
    }

    // Commit transaction
    $pdo->commit();
    
    echo "MLM commission structure has been set up successfully!\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    die("Error: " . $e->getMessage() . "\n");
}
?>
