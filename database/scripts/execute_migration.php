<?php
// Database Migration Script for APS Dream Homes
// This script executes the enhanced_password_migration.sql file

// Include database configuration
require_once('config.php');

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to execute SQL file
function executeSqlFile($con, $filePath) {
    echo "<h2>Executing SQL file: $filePath</h2>";
    
    if (!file_exists($filePath)) {
        echo "<p style='color:red'>Error: SQL file not found at $filePath</p>";
        return false;
    }
    
    // Read the SQL file
    $sql = file_get_contents($filePath);
    
    // Split the SQL file by delimiter statements
    $delimiter = ';';
    $sqlPieces = array();
    $currentDelimiter = ';';
    
    // Split the SQL into statements based on delimiter
    $lines = explode("\n", $sql);
    $statement = '';
    $inProcedure = false;
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Skip comments and empty lines
        if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '/*') === 0) {
            continue;
        }
        
        // Check for DELIMITER statements
        if (strpos($trimmedLine, 'DELIMITER') === 0) {
            if ($statement !== '') {
                $sqlPieces[] = $statement;
                $statement = '';
            }
            
            // Extract new delimiter
            $newDelimiter = str_replace('DELIMITER ', '', $trimmedLine);
            
            if ($newDelimiter === ';') {
                $inProcedure = false;
                $currentDelimiter = ';';
            } else {
                $inProcedure = true;
                $currentDelimiter = $newDelimiter;
            }
            
            continue;
        }
        
        // Add the line to the current statement
        $statement .= $line . "\n";
        
        // If the line ends with the current delimiter
        if (!$inProcedure && substr(rtrim($line), -strlen($currentDelimiter)) === $currentDelimiter) {
            $sqlPieces[] = $statement;
            $statement = '';
        } else if ($inProcedure && strpos($trimmedLine, $currentDelimiter) !== false) {
            $sqlPieces[] = $statement;
            $statement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty($statement)) {
        $sqlPieces[] = $statement;
    }
    
    // Execute each statement
    $totalStatements = count($sqlPieces);
    $successCount = 0;
    
    echo "<p>Found $totalStatements SQL statements to execute.</p>";
    
    foreach ($sqlPieces as $i => $statement) {
        $statement = trim($statement);
        
        // Skip empty statements
        if (empty($statement)) {
            $successCount++;
            continue;
        }
        
        echo "<p>Executing statement " . ($i + 1) . " of $totalStatements...</p>";
        
        // Replace DELIMITER statements for procedure execution
        $statement = str_replace('DELIMITER //', '', $statement);
        $statement = str_replace('DELIMITER ;', '', $statement);
        
        // Execute the statement
        try {
            if ($con->multi_query($statement)) {
                // Handle multiple result sets
                do {
                    if ($result = $con->store_result()) {
                        // Display result if it's a SELECT
                        if (strpos(strtoupper($statement), 'SELECT') !== false) {
                            echo "<table border='1'>";
                            echo "<tr>";
                            
                            // Output column names
                            $fieldInfo = $result->fetch_fields();
                            foreach ($fieldInfo as $field) {
                                echo "<th>{$field->name}</th>";
                            }
                            echo "</tr>";
                            
                            // Output data
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                foreach ($row as $value) {
                                    echo "<td>$value</td>";
                                }
                                echo "</tr>";
                            }
                            
                            echo "</table>";
                        }
                        $result->free();
                    }
                } while ($con->next_result());
                
                $successCount++;
                echo "<p style='color:green'>Statement executed successfully.</p>";
            } else {
                echo "<p style='color:red'>Error executing statement: " . $con->error . "</p>";
                echo "<pre>" . htmlspecialchars($statement) . "</pre>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
            echo "<pre>" . htmlspecialchars($statement) . "</pre>";
        }
    }
    
    echo "<p>Completed $successCount of $totalStatements statements successfully.</p>";
    
    return ($successCount === $totalStatements);
}

// Function to check if a table exists
function tableExists($con, $tableName) {
    $result = $con->query("SELECT COUNT(*) as count FROM information_schema.tables 
                         WHERE table_schema = DATABASE() 
                         AND table_name = '$tableName'");
    $row = $result->fetch_assoc();
    return ($row['count'] > 0);
}

// Main execution
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

// Check if user_backup table exists
echo "<h2>Checking if user_backup table exists...</h2>";
if (!tableExists($con, 'user_backup')) {
    echo "<p class='error'>Warning: user_backup table does not exist. Creating backup of user table...</p>";
    
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

// Execute the SQL file
$sqlFilePath = __DIR__ . "/DATABASE FILE/enhanced_password_migration.sql";
echo "<h2>Executing SQL migration file...</h2>";

if (executeSqlFile($con, $sqlFilePath)) {
    echo "<p class='success'>SQL migration executed successfully.</p>";
    
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
} else {
    echo "<p class='error'>Error executing SQL migration file.</p>";
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
echo "</body></html>";
?>