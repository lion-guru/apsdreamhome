<?php
// This script will check your database structure and seed appropriate demo data

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

// Function to get table columns
function getTableColumns($conn, $tableName) {
    $columns = array();
    $sql = "SHOW COLUMNS FROM $tableName";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    
    return $columns;
}

// Check if properties table exists and seed it
$result = $conn->query("SHOW TABLES LIKE 'properties'");
if($result && $result->num_rows > 0) {
    echo "Properties table exists\n";
    
    // Get columns
    $columns = getTableColumns($conn, "properties");
    echo "Properties table columns: " . implode(", ", $columns) . "\n";
    
    // Check if we have enough data
    $result = $conn->query("SELECT COUNT(*) as count FROM properties");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    if($count < 5) {
        echo "Adding demo data to properties table\n";
        
        // Create an insert query based on actual columns
        $columnsToUse = array();
        $values = array();
        
        // Check for common column names
        if(in_array("id", $columns)) $columnsToUse[] = "id";
        if(in_array("title", $columns)) $columnsToUse[] = "title";
        if(in_array("name", $columns)) $columnsToUse[] = "name";
        if(in_array("property_name", $columns)) $columnsToUse[] = "property_name";
        if(in_array("description", $columns)) $columnsToUse[] = "description";
        if(in_array("address", $columns)) $columnsToUse[] = "address";
        if(in_array("price", $columns)) $columnsToUse[] = "price";
        
        // Create values based on available columns
        if(count($columnsToUse) > 0) {
            $sql = "INSERT IGNORE INTO properties (" . implode(", ", $columnsToUse) . ") VALUES ";
            
            // Add values
            $valuesList = array();
            
            // Property 1
            $propertyValues = array();
            if(in_array("id", $columnsToUse)) $propertyValues[] = "101";
            if(in_array("title", $columnsToUse)) $propertyValues[] = "'Luxury Villa'";
            if(in_array("name", $columnsToUse)) $propertyValues[] = "'Luxury Villa'";
            if(in_array("property_name", $columnsToUse)) $propertyValues[] = "'Luxury Villa'";
            if(in_array("description", $columnsToUse)) $propertyValues[] = "'Beautiful luxury villa with garden'";
            if(in_array("address", $columnsToUse)) $propertyValues[] = "'Delhi Premium Enclave'";
            if(in_array("price", $columnsToUse)) $propertyValues[] = "15000000";
            $valuesList[] = "(" . implode(", ", $propertyValues) . ")";
            
            // Property 2
            $propertyValues = array();
            if(in_array("id", $columnsToUse)) $propertyValues[] = "102";
            if(in_array("title", $columnsToUse)) $propertyValues[] = "'City Apartment'";
            if(in_array("name", $columnsToUse)) $propertyValues[] = "'City Apartment'";
            if(in_array("property_name", $columnsToUse)) $propertyValues[] = "'City Apartment'";
            if(in_array("description", $columnsToUse)) $propertyValues[] = "'Modern apartment in city center'";
            if(in_array("address", $columnsToUse)) $propertyValues[] = "'Mumbai Heights'";
            if(in_array("price", $columnsToUse)) $propertyValues[] = "7000000";
            $valuesList[] = "(" . implode(", ", $propertyValues) . ")";
            
            // Add more properties as needed
            
            // Complete the SQL query
            $sql .= implode(", ", $valuesList);
            
            // Execute the query
            if($conn->query($sql) === TRUE) {
                echo "Properties data added successfully\n";
            } else {
                echo "Error adding properties data: " . $conn->error . "\n";
            }
        } else {
            echo "Could not find suitable columns in properties table\n";
        }
    } else {
        echo "Properties table already has data ($count records)\n";
    }
} else {
    echo "Properties table does not exist\n";
}

// Similar checks and seeding for other tables
// Customers table
$result = $conn->query("SHOW TABLES LIKE 'customers'");
if($result && $result->num_rows > 0) {
    echo "Customers table exists\n";
    
    // Get columns
    $columns = getTableColumns($conn, "customers");
    echo "Customers table columns: " . implode(", ", $columns) . "\n";
    
    // Check if we have enough data
    $result = $conn->query("SELECT COUNT(*) as count FROM customers");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    if($count < 5) {
        echo "Adding demo data to customers table\n";
        
        // Just add IDs if nothing else works
        $sql = "INSERT IGNORE INTO customers (id) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10)";
        
        // Execute the query
        if($conn->query($sql) === TRUE) {
            echo "Customers data added successfully\n";
        } else {
            echo "Error adding customers data: " . $conn->error . "\n";
        }
    } else {
        echo "Customers table already has data ($count records)\n";
    }
} else {
    echo "Customers table does not exist\n";
}

// Leads table
$result = $conn->query("SHOW TABLES LIKE 'leads'");
if($result && $result->num_rows > 0) {
    echo "Leads table exists\n";
    
    // Get columns
    $columns = getTableColumns($conn, "leads");
    echo "Leads table columns: " . implode(", ", $columns) . "\n";
    
    // Check if we have enough data
    $result = $conn->query("SELECT COUNT(*) as count FROM leads");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    if($count < 5) {
        echo "Adding demo data to leads table\n";
        
        // Just add IDs if nothing else works
        $sql = "INSERT IGNORE INTO leads (id) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10)";
        
        // Execute the query
        if($conn->query($sql) === TRUE) {
            echo "Leads data added successfully\n";
        } else {
            echo "Error adding leads data: " . $conn->error . "\n";
        }
    } else {
        echo "Leads table already has data ($count records)\n";
    }
} else {
    echo "Leads table does not exist\n";
}

// Close connection
$conn->close();
echo "Database seeding complete\n";
?>
