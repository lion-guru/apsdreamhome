<?php
// Script to execute Google OAuth migration
require_once(__DIR__ . "/config.php");

// Check if migration has already been executed
$check_query = "SHOW COLUMNS FROM users LIKE 'google_id'"; 
$check_result = $con->query($check_query);

if ($check_result->num_rows > 0) {
    echo "<p>Migration has already been executed.</p>";
} else {
    // Read the SQL file
    $sql_file = __DIR__ . "/DATABASE FILE/google_auth_migration.sql";
    $sql_content = file_get_contents($sql_file);
    
    if ($sql_content === false) {
        die("Error reading migration file.");
    }
    
    // Split SQL statements
    $sql_statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    // Execute each statement
    $con->begin_transaction();
    try {
        foreach ($sql_statements as $statement) {
            if (!empty($statement)) {
                if (!$con->query($statement)) {
                    throw new Exception("Error executing statement: " . $con->error);
                }
            }
        }
        
        $con->commit();
        echo "<p>Google OAuth migration executed successfully!</p>";
    } catch (Exception $e) {
        $con->rollback();
        echo "<p>Migration failed: " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='index.php'>Return to homepage</a></p>";