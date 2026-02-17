-- Enhanced Password Migration Script for APS Dream Homes
-- This script provides an improved migration procedure that properly handles password conversion

DELIMITER //

-- Create procedure for enhanced password migration
CREATE PROCEDURE migrate_user_data_with_password_handling()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_uid INT;
    DECLARE v_uname VARCHAR(100);
    DECLARE v_uemail VARCHAR(100);
    DECLARE v_uphone VARCHAR(20);
    DECLARE v_upass VARCHAR(255); -- Increased length to accommodate bcrypt hashes
    DECLARE v_utype VARCHAR(50);
    DECLARE v_uimage VARCHAR(300);
    DECLARE v_new_user_id INT;
    DECLARE v_referral_code VARCHAR(10);
    DECLARE user_backup_exists INT;
    DECLARE v_temp_password VARCHAR(20);
    
    -- Declare cursor and handler before any executable statements
    DECLARE user_cursor CURSOR FOR 
        SELECT uid, uname, uemail, uphone, upass, utype, IFNULL(uimage, 'default-user.png') 
        FROM user_backup;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Check if user_backup table exists
    SELECT COUNT(*) INTO user_backup_exists 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'user_backup';
    
    -- Only proceed with migration if backup exists
    IF user_backup_exists > 0 THEN
        -- Determine the column names in user_backup
        SET @has_uid = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_backup' 
                        AND column_name = 'uid');
        
        SET @has_uname = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_backup' 
                        AND column_name = 'uname');
        
        -- Adjust cursor based on available columns
        IF @has_uid > 0 AND @has_uname > 0 THEN
            
            -- Start transaction for data integrity
            START TRANSACTION;
            
            -- Create a temporary table to store migration results for reporting
            CREATE TEMPORARY TABLE IF NOT EXISTS migration_results (
                email VARCHAR(100),
                status VARCHAR(50),
                message VARCHAR(255)
            );
            
            OPEN user_cursor;
            
            read_loop: LOOP
                FETCH user_cursor INTO v_uid, v_uname, v_uemail, v_uphone, v_upass, v_utype, v_uimage;
                
                IF done THEN
                    LEAVE read_loop;
                END IF;
                
                -- Check password format to determine if it needs conversion
                -- Most legacy systems use MD5 or SHA1 (32 or 40 chars)
                IF LENGTH(v_upass) = 32 OR LENGTH(v_upass) = 40 THEN
                    -- For passwords that are already hashed but not in bcrypt format
                    -- We can't convert directly, so we'll set a temporary password
                    -- and flag the account for password reset
                    SET v_temp_password = CONCAT('Temp', FLOOR(RAND() * 1000000));
                    
                    -- Insert into new users table with temporary password
                    INSERT INTO users (name, email, phone, password, user_type, profile_image, created_at, status)
                    VALUES (v_uname, v_uemail, v_uphone, 
                            -- Use PHP password_hash equivalent in MySQL (if available)
                            -- Otherwise, store with a flag for reset
                            CONCAT('RESET_REQUIRED:', v_upass), 
                            -- Convert user type if needed
                            CASE 
                                WHEN v_utype = 'assosiate' THEN 'associate'
                                ELSE v_utype
                            END,
                            v_uimage, NOW(), 'inactive');
                    
                    -- Log the migration status
                    INSERT INTO migration_results (email, status, message)
                    VALUES (v_uemail, 'password_reset_required', 'Legacy password format detected, reset required');
                ELSE
                    -- For passwords that might be in plaintext or already in bcrypt format
                    INSERT INTO users (name, email, phone, password, user_type, profile_image, created_at)
                    VALUES (v_uname, v_uemail, v_uphone, 
                            -- Use original password
                            v_upass, 
                            -- Convert user type if needed
                            CASE 
                                WHEN v_utype = 'assosiate' THEN 'associate'
                                ELSE v_utype
                            END,
                            v_uimage, NOW());
                    
                    -- Log the migration status
                    INSERT INTO migration_results (email, status, message)
                    VALUES (v_uemail, 'migrated', 'User migrated successfully');
                END IF;
                
                SET v_new_user_id = LAST_INSERT_ID();
                
                -- Generate referral code (simple implementation)
                SET v_referral_code = CONCAT('REF', LPAD(v_new_user_id, 7, '0'));
                
                -- If user is an associate, add to associates table
                IF v_utype = 'assosiate' OR v_utype = 'associate' THEN
                    INSERT INTO associates (uid, user_id, referral_code)
                    VALUES (CONCAT('A', LPAD(v_uid, 9, '0')), v_new_user_id, v_referral_code);
                END IF;
            END LOOP;
            
            CLOSE user_cursor;
            
            -- Output migration results
            SELECT * FROM migration_results;
            
            -- Drop temporary table
            DROP TEMPORARY TABLE IF EXISTS migration_results;
            
            -- Commit the transaction
            COMMIT;
            
            -- Output summary
            SELECT 'Migration completed. Some accounts may require password reset.' AS message;
        ELSE
            -- Handle case where column names are different
            SELECT 'User backup table has different column structure than expected' AS message;
        END IF;
    ELSE
        SELECT 'No user backup table found, skipping migration' AS message;
    END IF;
 END //
DELIMITER ;

-- Create a procedure to handle password resets for migrated users
DELIMITER //
CREATE PROCEDURE reset_migrated_passwords()
BEGIN
    -- Create a temporary table to store users needing password reset
    CREATE TEMPORARY TABLE users_to_reset AS
    SELECT id, email 
    FROM users 
    WHERE password LIKE 'RESET_REQUIRED:%';
    
    -- Output the list of users needing password reset
    SELECT * FROM users_to_reset;
    
    -- Drop temporary table
    DROP TEMPORARY TABLE IF EXISTS users_to_reset;
    
    -- Output instructions
    SELECT 'To complete migration, send password reset emails to these users' AS next_steps;
END //
DELIMITER ;

-- Create a PHP helper script to update passwords after migration
-- This would be executed separately after the SQL migration

/*
-- Example PHP code for password reset (save as update_passwords.php):

<?php
require_once('config.php');

// Get users with passwords needing reset
$query = "SELECT id, email, password FROM users WHERE password LIKE 'RESET_REQUIRED:%'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h2>Password Migration Report</h2>";
    echo "<table border='1'><tr><th>Email</th><th>Status</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        // Extract the original hash
        $original_hash = substr($row['password'], 15);
        
        // Generate a secure random password
        $temp_password = bin2hex(random_bytes(8));
        
        // Hash the new password with bcrypt
        $new_hash = password_hash($temp_password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $update = "UPDATE users SET password = ?, status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("si", $new_hash, $row['id']);
        
        if ($stmt->execute()) {
            echo "<tr><td>{$row['email']}</td><td>Password reset to: {$temp_password}</td></tr>";
            
            // In production, you would send an email with reset instructions
            // sendPasswordResetEmail($row['email'], $temp_password);
        } else {
            echo "<tr><td>{$row['email']}</td><td>Failed to reset password</td></tr>";
        }
    }
    
    echo "</table>";
    echo "<p>Important: In a production environment, you should email these temporary passwords to users.</p>";
} else {
    echo "<p>No passwords need migration.</p>";
}
?>
*/

-- Instructions for using this migration script:
-- 1. First backup your database
-- 2. Run the original migration script to create the new table structure
-- 3. Execute this enhanced password migration: CALL migrate_user_data_with_password_handling();
-- 4. Check which users need password resets: CALL reset_migrated_passwords();
-- 5. Create and run the PHP script to handle password resets
-- 6. Update your application code to use the new table structure

-- Note: This script provides a safer approach to password migration by:
-- 1. Preserving original password hashes for reference
-- 2. Flagging accounts that need password resets
-- 3. Providing a mechanism to generate and distribute new passwords