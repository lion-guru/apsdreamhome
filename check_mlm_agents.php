<?php
// Check mlm_agents table structure
require_once 'includes/config.php';

// Get table structure
$result = $db_connection->query("DESCRIBE mlm_agents");

if ($result) {
    echo "mlm_agents table structure:\n";
    echo "Field\t\t\tType\t\t\tNull\tKey\tDefault\tExtra\n";
    echo "-----\t\t\t----\t\t\t----\t---\t-------\t-----\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-20s\t%-20s\t%-5s\t%-3s\t%-10s\t%s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL', 
            $row['Extra']
        );
    }
} else {
    echo "Error: " . $db_connection->error;
}

// Check if table exists
$table_check = $db_connection->query("SHOW TABLES LIKE 'mlm_agents'");
if ($table_check->num_rows > 0) {
    echo "\nTable 'mlm_agents' exists.\n";
} else {
    echo "\nTable 'mlm_agents' does not exist.\n";
}
?>