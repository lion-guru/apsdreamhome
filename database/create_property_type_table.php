<?php
// Script to create property_type table in the database

// Database connection parameters
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'apsdreamhome';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully.\n";

// Check if property_type table already exists
$tableExists = $conn->query("SHOW TABLES LIKE 'property_type'")->num_rows > 0;

if ($tableExists) {
    echo "Property type table already exists.\n";
} else {
    // SQL to create property_type table
    $sql = "CREATE TABLE IF NOT EXISTS `property_type` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `type` varchar(100) NOT NULL,
      `description` text,
      `status` tinyint(4) NOT NULL DEFAULT '1',
      `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        echo "Property type table created successfully.\n";
        
        // Insert default property types
        $insertSql = "INSERT INTO `property_type` (`type`, `description`, `status`) VALUES
        ('Residential Plot', 'Land for residential building construction', 1),
        ('Commercial Plot', 'Land for commercial building construction', 1),
        ('Villa', 'Independent luxury house with garden', 1),
        ('Apartment', 'Unit in multi-dwelling building', 1),
        ('Shop', 'Commercial retail space', 1),
        ('Office Space', 'Commercial office space', 1)";
        
        if ($conn->query($insertSql) === TRUE) {
            echo "Default property types inserted successfully.\n";
        } else {
            echo "Error inserting default property types: " . $conn->error . "\n";
        }
    } else {
        echo "Error creating property_type table: " . $conn->error . "\n";
    }
}

// Close connection
$conn->close();
echo "Database connection closed.\n";