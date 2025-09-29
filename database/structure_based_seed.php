<?php
// Structure-based seeding script for apsdreamhomefinal database
// This script first examines the exact structure of each table and then fills it with appropriate data

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database\n";

// Get all tables in the database using prepared statement
$tables = array();
$result = $conn->prepare("SHOW TABLES");
$result->execute();
$table_result = $result->get_result();
if ($table_result) {
    while ($row = $table_result->fetch_row()) {
        $tables[] = $row[0];
    }
    $result->close();
    echo "Found " . count($tables) . " tables in database\n";
} else {
    echo "Error getting tables: " . $conn->error . "\n";
}

// Function to get detailed table structure using prepared statement
function getTableStructure($conn, $tableName) {
    $structure = array();
    $stmt = $conn->prepare("DESCRIBE `$tableName`");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $structure[] = $row;
        }
    }
    $stmt->close();

    return $structure;
}

// Process each table
foreach ($tables as $table) {
    echo "\n===== Processing table: $table =====\n";
    
    // Get detailed structure
    $structure = getTableStructure($conn, $table);
    
    if (empty($structure)) {
        echo "Could not get structure for table $table, skipping\n";
        continue;
    }
    
    echo "Table structure:\n";
    foreach ($structure as $column) {
        echo "- {$column['Field']} ({$column['Type']})" . 
             ($column['Key'] == 'PRI' ? " [PRIMARY KEY]" : "") . 
             ($column['Extra'] == 'auto_increment' ? " [AUTO_INCREMENT]" : "") . 
             ($column['Null'] == 'NO' ? " [NOT NULL]" : "") . 
             "\n";
    }
    
    // Check if table already has data
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    echo "Current record count: $count\n";
    
    // Only add data if table has fewer than 5 records
    if ($count < 5) {
        echo "Adding demo data to $table based on its structure\n";
        
        // Build column list and values based on structure
        $columns = array();
        $columnTypes = array();
        $hasPrimaryKey = false;
        $primaryKeyColumn = '';
        
        foreach ($structure as $column) {
            // Skip auto_increment columns in the insert
            if ($column['Extra'] != 'auto_increment') {
                $columns[] = $column['Field'];
                $columnTypes[$column['Field']] = $column['Type'];
            }
            
            if ($column['Key'] == 'PRI') {
                $hasPrimaryKey = true;
                $primaryKeyColumn = $column['Field'];
            }
        }
        
        // If no columns to insert (only auto_increment primary key), skip
        if (empty($columns)) {
            echo "Table only has auto_increment primary key, skipping\n";
            continue;
        }
        
        // Generate appropriate values based on table and column names
        $values = array();
        
        // Generate 5 rows of data
        for ($i = 1; $i <= 5; $i++) {
            $rowValues = array();
            
            foreach ($columns as $column) {
                $type = $columnTypes[$column];
                $value = null;
                
                // Determine appropriate value based on column name and type
                if (preg_match('/^id$/i', $column) && !preg_match('/auto_increment/i', $type)) {
                    $value = $i;
                }
                // Name fields
                else if (preg_match('/(^|_)name$/i', $column)) {
                    $names = ['Rahul Sharma', 'Priya Singh', 'Amit Kumar', 'Neha Patel', 'Vikram Mehta'];
                    $value = "'" . $names[$i-1] . "'";
                }
                else if (preg_match('/first_name$/i', $column)) {
                    $firstNames = ['Rahul', 'Priya', 'Amit', 'Neha', 'Vikram'];
                    $value = "'" . $firstNames[$i-1] . "'";
                }
                else if (preg_match('/last_name$/i', $column)) {
                    $lastNames = ['Sharma', 'Singh', 'Kumar', 'Patel', 'Mehta'];
                    $value = "'" . $lastNames[$i-1] . "'";
                }
                // Title fields
                else if (preg_match('/title$/i', $column)) {
                    $titles = ['Luxury Villa', 'City Apartment', 'Suburban House', 'Beach Property', 'Penthouse'];
                    $value = "'" . $titles[$i-1] . "'";
                }
                // Email fields
                else if (preg_match('/email$/i', $column)) {
                    $emails = ['rahul@example.com', 'priya@example.com', 'amit@example.com', 'neha@example.com', 'vikram@example.com'];
                    $value = "'" . $emails[$i-1] . "'";
                }
                // Phone fields
                else if (preg_match('/phone$/i', $column)) {
                    $value = "'98765432" . sprintf('%02d', $i) . "'";
                }
                // Address fields
                else if (preg_match('/address$/i', $column)) {
                    $addresses = ['Delhi', 'Mumbai', 'Bangalore', 'Ahmedabad', 'Pune'];
                    $value = "'" . $addresses[$i-1] . "'";
                }
                // Description fields
                else if (preg_match('/description$/i', $column)) {
                    $descriptions = [
                        'Beautiful luxury villa with garden and pool',
                        'Modern apartment in city center with great amenities',
                        'Spacious family home in quiet neighborhood',
                        'Beachfront luxury home with amazing views',
                        'Luxury penthouse with terrace and city views'
                    ];
                    $value = "'" . $descriptions[$i-1] . "'";
                }
                // Price/Amount fields
                else if (preg_match('/(price|amount|cost)$/i', $column)) {
                    $prices = [15000000, 7000000, 9000000, 20000000, 25000000];
                    $value = $prices[$i-1];
                }
                // Date fields
                else if (preg_match('/(date|_at)$/i', $column)) {
                    $dates = [
                        "'2025-05-" . sprintf('%02d', $i) . "'",
                        "'2025-05-" . sprintf('%02d', $i+5) . "'",
                        "'2025-05-" . sprintf('%02d', $i+10) . "'",
                        "'2025-05-" . sprintf('%02d', $i+15) . "'",
                        "'2025-05-" . sprintf('%02d', $i+20) . "'"
                    ];
                    $value = $dates[$i-1];
                }
                // Status fields
                else if (preg_match('/status$/i', $column)) {
                    $statuses = ['active', 'pending', 'completed', 'cancelled', 'active'];
                    $value = "'" . $statuses[$i-1] . "'";
                }
                // Type fields
                else if (preg_match('/type$/i', $column)) {
                    if ($table == 'users') {
                        $types = ['admin', 'agent', 'user', 'agent', 'user'];
                        $value = "'" . $types[$i-1] . "'";
                    } else {
                        $types = ['villa', 'apartment', 'house', 'villa', 'penthouse'];
                        $value = "'" . $types[$i-1] . "'";
                    }
                }
                // Rating fields
                else if (preg_match('/rating$/i', $column)) {
                    $ratings = [5, 4, 5, 4, 5];
                    $value = $ratings[$i-1];
                }
                // Message/Text fields
                else if (preg_match('/(message|text|notes|content)$/i', $column)) {
                    $messages = [
                        "'This is a sample message for record 1.'",
                        "'This is a sample message for record 2.'",
                        "'This is a sample message for record 3.'",
                        "'This is a sample message for record 4.'",
                        "'This is a sample message for record 5.'"
                    ];
                    $value = $messages[$i-1];
                }
                // Foreign key fields
                else if (preg_match('/_id$/i', $column)) {
                    $value = $i;
                }
                // Default for other fields
                else {
                    if (strpos($type, 'int') !== false) {
                        $value = $i;
                    } else if (strpos($type, 'varchar') !== false || strpos($type, 'text') !== false) {
                        $value = "'Value for $column $i'";
                    } else if (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false) {
                        $value = $i * 1000;
                    } else if (strpos($type, 'date') !== false) {
                        $value = "'2025-05-" . sprintf('%02d', $i) . "'";
                    } else if (strpos($type, 'time') !== false) {
                        $value = "'10:" . sprintf('%02d', $i) . ":00'";
                    } else if (strpos($type, 'enum') !== false || strpos($type, 'set') !== false) {
                        // Extract enum values
                        preg_match("/enum\('(.+?)'\)/", $type, $matches);
                        if (isset($matches[1])) {
                            $enumValues = explode("','", $matches[1]);
                            $value = "'" . $enumValues[$i % count($enumValues)] . "'";
                        } else {
                            $value = "''";
                        }
                    } else {
                        $value = "NULL";
                    }
                }
                
                $rowValues[] = $value;
            }
            
            $values[] = "(" . implode(", ", $rowValues) . ")";
        }
        
        // Build and execute the SQL query
        $sql = "INSERT IGNORE INTO $table (" . implode(", ", $columns) . ") VALUES " . implode(", ", $values);
        
        try {
            if($conn->query($sql) === TRUE) {
                echo "Data added successfully to $table\n";
            } else {
                echo "Error adding data to $table: " . $conn->error . "\n";
                echo "SQL: $sql\n";
            }
        } catch (Exception $e) {
            echo "Exception adding data to $table: " . $e->getMessage() . "\n";
            
            // Try a minimal approach if the specific approach failed
            try {
                // Find a non-auto-increment column for minimal insert
                $minimalColumn = null;
                foreach ($structure as $column) {
                    if ($column['Extra'] != 'auto_increment') {
                        $minimalColumn = $column['Field'];
                        break;
                    }
                }
                
                if ($minimalColumn) {
                    $minimalSql = "INSERT IGNORE INTO $table ($minimalColumn) VALUES ('1'), ('2'), ('3'), ('4'), ('5')";
                    if($conn->query($minimalSql) === TRUE) {
                        echo "Minimal data added successfully to $table\n";
                    } else {
                        echo "Error adding minimal data to $table: " . $conn->error . "\n";
                    }
                }
            } catch (Exception $e2) {
                echo "Exception adding minimal data to $table: " . $e2->getMessage() . "\n";
            }
        }
    } else {
        echo "Table $table already has data ($count records), skipping\n";
    }
}

// Close connection
$conn->close();
echo "\nDatabase seeding complete. All tables should now have data based on their exact structure.\n";
?>
