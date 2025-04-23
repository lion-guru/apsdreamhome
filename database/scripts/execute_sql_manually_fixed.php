<?php
// Database Migration Script for APS Dream Homes
// This script manually executes the SQL statements from enhanced_password_migration.sql
// Fixed version with improved error handling and procedure execution

// Include database configuration
require_once('config.php');

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTML header
echo "<html><head><title>MySQL Migration</title>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }
       h1, h2 { color: #333; }
       pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 300px; }
       .success { color: green; }
       .warning { color: orange; }
       .error { color: red; }
       table { border-collapse: collapse; margin: 15px 0; }
       th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
       th { background-color: #f2f2f2; }
       .code-block { background-color: #f8f8f8; padding: 10px; border-left: 4px solid #ddd; margin: 10px 0; }
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
                         AND table_name = '" . $con->real_escape_string($tableName) . "'");
    if (!$result) {
        return false;
    }
    $row = $result->fetch_assoc();
    return ($row['count'] > 0);
}

// Function to check if a procedure exists
function procedureExists($con, $procedureName) {
    $result = $con->query("SELECT COUNT(*) as count FROM information_schema.routines 
                         WHERE routine_schema = DATABASE() 
                         AND routine_name = '" . $con->real_escape_string($procedureName) . "'");
    if (!$result) {
        return false;
    }
    $row = $result->fetch_assoc();
    return ($row['count'] > 0);
}

// Function to check if users table has required columns
function checkUsersTableStructure($con) {
    if (!tableExists($con, 'users')) {
        return false;
    }
    
    $requiredColumns = ['id', 'name', 'email', 'password', 'phone', 'user_type', 'profile_image', 'status'];
    $missingColumns = [];
    
    foreach ($requiredColumns as $column) {
        $result = $con->query("SELECT COUNT(*) as count FROM information_schema.columns 
                             WHERE table_schema = DATABASE() 
                             AND table_name = 'users' 
                             AND column_name = '" . $con->real_escape_string($column) . "'");
        if (!$result || $result->fetch_assoc()['count'] == 0) {
            $missingColumns[] = $column;
        }
    }
    
    return empty($missingColumns) ? true : $missingColumns;
}

// Function to execute a SQL query with proper error handling
function executeQuery($con, $query, $description) {
    echo "<h3>$description</h3>";
    
    if ($con->multi_query($query)) {
        echo "<p class='success'>Query executed successfully.</p>";
        
        // Process all result sets
        do {
            if ($result = $con->store_result()) {
                echo "<table>";
                
                // Print header row
                $fields = $result->fetch_fields();
                echo "<tr>";
                foreach ($fields as $field) {
                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                }
                echo "</tr>";
                
                // Print data rows
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
                $result->free();
            }
        } while ($con->more_results() && $con->next_result());
        
        return true;
    } else {
        echo "<p class='error'>Error executing query: " . $con->error . "</p>";
        echo "<div class='code-block'><pre>" . htmlspecialchars($query) . "</pre></div>";
        return false;
    }
}

// Check if database tables exist and create them if needed
echo "<h2>Checking database structure...</h2>";

// Check if user_backup table exists
echo "<h3>Checking if user_backup table exists...</h3>";
if (!tableExists($con, 'user_backup')) {
    echo "<p class='warning'>Warning: user_backup table does not exist.</p>";
    
    // Check if user table exists
    if (!tableExists($con, 'user')) {
        echo "<p class='error'>Error: Neither user nor user_backup table exists. Cannot proceed with migration.</p>";
        exit;
    }
    
    // Create backup of user table
    echo "<p>Creating backup of user table...</p>";
    if ($con->query("CREATE TABLE IF NOT EXISTS user_backup AS SELECT * FROM user")) {
        echo "<p class='success'>Created user_backup table successfully.</p>";
    } else {
        echo "<p class='error'>Error creating user_backup table: " . $con->error . "</p>";
        exit;
    }
} else {
    echo "<p class='success'>user_backup table already exists.</p>";
}

// Check if users table exists with proper structure
echo "<h3>Checking if users table exists with proper structure...</h3>";
$usersCheck = checkUsersTableStructure($con);

if ($usersCheck === true) {
    echo "<p class='success'>users table exists with all required columns.</p>";
} elseif ($usersCheck === false) {
    echo "<p class='warning'>users table does not exist. It will be created during migration.</p>";
} else {
    echo "<p class='warning'>users table exists but is missing columns: " . implode(', ', $usersCheck) . ". These will be added during migration.</p>";
}

// Check if associates table exists
echo "<h3>Checking if associates table exists...</h3>";
if (!tableExists($con, 'associates')) {
    echo "<p class='warning'>associates table does not exist. It will be created during migration.</p>";
} else {
    echo "<p class='success'>associates table already exists.</p>";
}

// Manually execute the SQL statements
echo "<h2>Executing SQL migration statements...</h2>";

// Create the migrate_user_data_with_password_handling procedure
$createProcedure = "CREATE PROCEDURE migrate_user_data_with_password_handling()
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
$createResetProcedure = "CREATE PROCEDURE reset_migrated_passwords()
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
if (procedureExists($con, 'migrate_user_data_with_password_handling')) {
    if ($con->query("DROP PROCEDURE IF EXISTS migrate_user_data_with_password_handling")) {
        echo "<p class='success'>Dropped existing migrate_user_data_with_password_handling procedure.</p>";
    } else {
        echo "<p class='error'>Error dropping procedure: " . $con->error . "</p>";
    }
}

if (procedureExists($con, 'reset_migrated_passwords')) {
    if ($con->query("DROP PROCEDURE IF EXISTS reset_migrated_passwords")) {
        echo "<p class='success'>Dropped existing reset_migrated_passwords procedure.</p>";
    } else {
        echo "<p class='error'>Error dropping procedure: " . $con->error . "</p>";
    }
}

// Create the procedures using DELIMITER handling in PHP
echo "<h3>Creating migrate_user_data_with_password_handling procedure...</h3>";

// First, create the procedure without DELIMITER statements
$createProcedureQuery = str_replace('DELIMITER //', '', $createProcedure);
$createProcedureQuery = str_replace('END //', 'END', $createProcedureQuery);
$createProcedureQuery = str_replace('DELIMITER ;', '', $createProcedureQuery);

if ($con->multi_query($createProcedureQuery)) {
    echo "<p class='success'>Created migrate_user_data_with_password_handling procedure successfully.</p>";
    // Process all result sets to clear the connection
    while ($con->more_results() && $con->next_result()) {
        if ($result = $con->store_result()) {
            $result->free();
        }
    }
} else {
    echo "<p class='error'>Error creating procedure: " . $con->error . "</p>";
    echo "<div class='code-block'><pre>" . htmlspecialchars($createProcedureQuery) . "</pre></div>";
}

echo "<h3>Creating reset_migrated_passwords procedure...</h3>";
$createResetProcedureQuery = str_replace('DELIMITER //', '', $createResetProcedure);
$createResetProcedureQuery = str_replace('END //', 'END', $createResetProcedureQuery);
$createResetProcedureQuery = str_replace('DELIMITER ;', '', $createResetProcedureQuery);

if ($con->multi_query($createResetProcedureQuery)) {
    echo "<p class='success'>Created reset_migrated_passwords procedure successfully.</p>";
    // Process all result sets to clear the connection
    while ($con->more_results() && $con->next_result()) {
        if ($result = $con->store_result()) {
            $result->free();
        }
    }
} else {
    echo "<p class='error'>Error creating procedure: " . $con->error . "</p>";
    echo "<div class='code-block'><pre>" . htmlspecialchars($createResetProcedureQuery) . "</pre></div>";
}

// Call the migration procedure
echo "<h2>Calling migration procedure...</h2>";

// Check if users table exists, if not create it
if (!tableExists($con, 'users')) {
    echo "<p class='warning'>Users table does not exist. Creating it before migration...</p>";
    $createUsersTable = "CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `phone` varchar(20) DEFAULT NULL,
      `user_type` enum('user','agent','builder','associate','admin') NOT NULL DEFAULT 'user',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      `status` enum('active','inactive') NOT NULL DEFAULT 'active',
      `last_login` timestamp NULL DEFAULT NULL,
      `profile_image` varchar(300) DEFAULT 'default-user.png',
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      KEY `idx_user_email` (`email`),
      KEY `idx_user_status` (`status`),
      KEY `idx_user_type` (`user_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if ($con->query($createUsersTable)) {
        echo "<p class='success'>Created users table successfully.</p>";
    } else {
        echo "<p class='error'>Error creating users table: " . $con->error . "</p>";
        exit;
    }
}

// Check if associates table exists, if not create it
if (!tableExists($con, 'associates')) {
    echo "<p class='warning'>Associates table does not exist. Creating it before migration...</p>";
    $createAssociatesTable = "CREATE TABLE IF NOT EXISTS `associates` (
      `associate_id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` varchar(10) UNIQUE NOT NULL,
      `user_id` int(11) NOT NULL,
      `sponsor_id` varchar(10) DEFAULT NULL,
      `referral_code` varchar(10) UNIQUE NOT NULL,
      `level` int(11) DEFAULT 1,
      `total_business` decimal(12,2) DEFAULT 0.00,
      `current_month_business` decimal(12,2) DEFAULT 0.00,
      `team_business` decimal(12,2) DEFAULT 0.00,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`associate_id`),
      KEY `idx_associate_uid` (`uid`),
      KEY `idx_associate_sponsor` (`sponsor_id`),
      KEY `idx_associate_referral` (`referral_code`),
      CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if ($con->query($createAssociatesTable)) {
        echo "<p class='success'>Created associates table successfully.</p>";
    } else {
        echo "<p class='error'>Error creating associates table: " . $con->error . "</p>";
        exit;
    }
}

// Execute the migration procedure with proper error handling
echo "<h3>Executing migration procedure...</h3>";
try {
    // Start a transaction for data integrity
    $con->begin_transaction();
    
    // Call the migration procedure
    if (!$con->query("CALL migrate_user_data_with_password_handling()")) {
        throw new Exception("Error executing migration procedure: " . $con->error);
    }
    
    echo "<p class='success'>Migration procedure executed successfully.</p>";
    
    // Check for users needing password reset
    echo "<h3>Checking for users needing password reset...</h3>";
    if (!$con->query("CALL reset_migrated_passwords()")) {
        throw new Exception("Error checking for password resets: " . $con->error);
    }
    
    echo "<p class='success'>Password reset check completed.</p>";
    
    // Commit the transaction if everything succeeded
    $con->commit();
    
} catch (Exception $e) {
    // Rollback the transaction if an error occurred
    $con->rollback();
    echo "<p class='error'>" . $e->getMessage() . "</p>";
    echo "<p class='warning'>Transaction rolled back to prevent data corruption.</p>";
}

// Create the update_passwords.php file if it doesn't exist
$updatePasswordsScript = '<?php
// Script to update passwords for migrated users
require_once("config.php");

// Set error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Get users needing password reset
$sql = "SELECT id, email FROM users WHERE password LIKE \'RESET_REQUIRED:%\'";
$result = $con->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<h2>Users Needing Password Reset:</h2>";
    echo "<table border=\'1\'>";
    echo "<tr><th>ID</th><th>Email</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No users need password reset.</p>";
}
filenamePasswordsScript<?php
// Script to   for migrated users
require_once("config.php");
// Set error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Get users needing password reset
$sql = "SELECT id, email FROM users WHERE password LIKE \'RESET_REQUIRED:%\'";
$result = $con->query($sql);
$con$result && $result->num_rows > 0) {
    echo "<h2>Users Needing Password Reset:</h2>";
    echo "<table border=\'1\'>";
    echo "<tr><th>ID</th><th>Email</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No users need password reset.</p>";
}
$con->close();
?>';
// Create update_passwords.php file
$filename = 'update_passwords.php';
$updatePasswordsScript = '<?php
// Script to update passwords for migrated users
require_once("config.php");
// Set error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Get users needing password reset
$sql = "SELECT id, email FROM users WHERE password LIKE \'RESET_REQUIRED:%\'";
$result = $con->query($sql);
if ($result && $result->num_rows > 0) {
    echo "<h2>Users Needing Password Reset:</h2>";
    echo "<table border=\'1\'>";
    echo "<tr><th>ID</th><th>Email</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No users need password reset.</p>";
}
$con->close();
?>';
// Create update_passwords.php file
$filename = 'update_passwords.php';
$updatePasswordsScript = '<?php
// Script to update passwords for migrated users
require_once("config.php");
// Set error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Get users needing password reset
$sql = "SELECT id, email FROM users WHERE password LIKE \'RESET_REQUIRED:%\'";
$result = $con->query($sql);
if ($result && $result->num_rows > 0) {
    echo "<h2>Users Needing Password Reset:</h2>";
    echo "<table border=\'1\'>";
    echo "<tr><th>ID</th><th>Email</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No users need password reset.</p>";
}
$con->close();
?>';
// Create update_passwords.php file
$filename = 'update_passwords.php';
$updatePasswordsScript = '<?php
// Script to update passwords for migrated users
require_once("config.php");
// Set error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Get users needing password reset
$sql = "SELECT id, email FROM users WHERE password LIKE \'RESET_REQUIRED:%\'";
$result = $con->query($sql);
if ($result && $result->num_rows > 0) {
    echo "<h2>Users Needing Password Reset:</h2>";
    echo "<table border=\'1\'>";
    echo "<tr><th>ID</th><th>Email</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No users need password reset.</p>";
}
$con->close();
?>';
// Create update_passwords.php file
$filename = 'update_passwords.php';

// Create update_passwords.php file
$filename = 'update_passwords.php';
if (!file_exists($filename)) {
    if (file_put_contents($filename, $updatePasswordsScript) !== false) {
        echo "<p class='success'>Created update_passwords.php script successfully.</p>";
    } else {
        echo "<p class='error'>Error creating update_passwords.php script.</p>";
    }
} else {
    echo "<p class='warning'>update_passwords.php already exists.</p>";
}
