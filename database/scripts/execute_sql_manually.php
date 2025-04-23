<?php
// Database Migration Script for APS Dream Homes
// This script manually executes the SQL statements from enhanced_password_migration.sql

// Include database configuration
require_once('config.php');

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTML header
echo "<html><head><title>MySQL Migration</title>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }
       h1, h2 { color: #333; }
       pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; }
       .success { color: green; }
       .error { color: red; }
       table { border-collapse: collapse; margin: 15px 0; }
       th, td { padding: 8px; text-align: left; }
       th { background-color: #f2f2f2; }
       </style>";
echo "</head><body>";
echo "<h1>MySQL Migration for APS Dream Homes</h1>";

// Check database connection
if ($con->connect_error) {
    echo "<p class='error'>Connection failed: " . $con->connect_error . "</p>";
    exit;
}

echo "<p class='success'>Database connection successful.</p>";

// Function to check if a table exists
function tableExists($con, $tableName) {
    $result = $con->query("SELECT COUNT(*) as count FROM information_schema.tables 
                         WHERE table_schema = DATABASE() 
                         AND table_name = '$tableName'");
    if (!$result) {
        return false;
    }
    $row = $result->fetch_assoc();
    return ($row['count'] > 0);
}

// Check if user_backup table exists
echo "<h2>Checking if user_backup table exists...</h2>";
if (!tableExists($con, 'user_backup')) {
    echo "<p class='warning'>Warning: user_backup table does not exist. Creating backup of user table...</p>";
    
    // Check if user table exists
    if (!tableExists($con, 'user')) {
        echo "<p class='error'>Error: user table does not exist. Cannot proceed with migration.</p>";
        exit;
    }
    
    // Create backup of user table
    if ($con->query("CREATE TABLE IF NOT EXISTS user_backup AS SELECT * FROM user")) {
        echo "<p class='success'>Created user_backup table successfully.</p>";
    } else {
        echo "<p class='error'>Error creating user_backup table: " . $con->error . "</p>";
        exit;
    }
} else {
    echo "<p class='success'>user_backup table already exists.</p>";
}

// Manually execute the SQL statements
echo "<h2>Executing SQL migration statements...</h2>";

// Create the migrate_user_data_with_password_handling procedure
$createProcedure = "CREATE PROCEDURE IF NOT EXISTS migrate_user_data_with_password_handling()

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
 END";

// Create the reset_migrated_passwords procedure
$createResetProcedure = "CREATE PROCEDURE IF NOT EXISTS reset_migrated_passwords()

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
END";

// Drop existing procedures if they exist
if ($con->query("DROP PROCEDURE IF EXISTS migrate_user_data_with_password_handling")) {
    echo "<p class='success'>Dropped existing migrate_user_data_with_password_handling procedure.</p>";
} else {
    echo "<p class='error'>Error dropping procedure: " . $con->error . "</p>";
}

if ($con->query("DROP PROCEDURE IF EXISTS reset_migrated_passwords")) {
    echo "<p class='success'>Dropped existing reset_migrated_passwords procedure.</p>";
} else {
    echo "<p class='error'>Error dropping procedure: " . $con->error . "</p>";
}

// Create the procedures
if ($con->query($createProcedure)) {
    echo "<p class='success'>Created migrate_user_data_with_password_handling procedure successfully.</p>";
} else {
    echo "<p class='error'>Error creating procedure: " . $con->error . "</p>";
    echo "<pre>" . htmlspecialchars($createProcedure) . "</pre>";
}

if ($con->query($createResetProcedure)) {
    echo "<p class='success'>Created reset_migrated_passwords procedure successfully.</p>";
} else {
    echo "<p class='error'>Error creating procedure: " . $con->error . "</p>";
    echo "<pre>" . htmlspecialchars($createResetProcedure) . "</pre>";
}

// Call the migration procedure
echo "<h2>Calling migration procedure...</h2>";
if ($con->query("CALL migrate_user_data_with_password_handling()")) {
    echo "<p class='success'>Migration procedure executed successfully.</p>";
    
    // Check for users needing password reset
    echo "<h2>Checking for users needing password reset...</h2>";
    if ($con->query("CALL reset_migrated_passwords()")) {
        echo "<p class='success'>Password reset check completed.</p>";
    } else {
        echo "<p class='error'>Error checking for password resets: " . $con->error . "</p>";
    }
} else {
    echo "<p class='error'>Error executing migration procedure: " . $con->error . "</p>";
}

// Create the update_passwords.php file if it doesn't exist
$updatePasswordsFile = __DIR__ . "/update_passwords.php";
if (!file_exists($updatePasswordsFile)) {
    $updatePasswordsContent = '<?php
require_once("config.php");

// Get users with passwords needing reset
$query = "SELECT id, email, password FROM users WHERE password LIKE \'RESET_REQUIRED:%\'"; 
$result = $con->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h2>Password Migration Report</h2>";
    echo "<table border=\'1\'><tr><th>Email</th><th>Status</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        // Extract the original hash
        $original_hash = substr($row["password"], 15);
        
        // Generate a secure random password
        $temp_password = bin2hex(random_bytes(8));
        
        // Hash the new password with bcrypt
        $new_hash = password_hash($temp_password, PASSWORD_DEFAULT);
        
        // Update the user\'s password
        $update = "UPDATE users SET password = ?, status = \'active\' WHERE id = ?";
        $stmt = $con->prepare($update);
        $stmt->bind_param("si", $new_hash, $row["id"]);
        
        if ($stmt->execute()) {
            echo "<tr><td>{$row["email"]}</td><td>Password reset to: {$temp_password}</td></tr>";
            
            // In production, you would send an email with reset instructions
            // sendPasswordResetEmail($row["email"], $temp_password);
        } else {
            echo "<tr><td>{$row["email"]}</td><td>Failed to reset password</td></tr>";
        }
    }
    
    echo "</table>";
    echo "<p>Important: In a production environment, you should email these temporary passwords to users.</p>";
} else {
    echo "<p>No passwords need migration.</p>";
}
?>';
    
    file_put_contents($updatePasswordsFile, $updatePasswordsContent);
    echo "<p class='success'>Created update_passwords.php file for resetting passwords.</p>";
    echo "<p>After reviewing the migration results, you can <a href='update_passwords.php'>run the password update script</a> to reset passwords for migrated users.</p>";
}

echo "<p>Migration process completed.</p>";