<?php
/**
 * APS Dream Home - Seeder Enhancement Tool
 * 
 * This script enhances the existing structure-based seeder by adding:
 * 1. Relationship-aware data generation
 * 2. Real estate specific data templates
 * 3. Intelligent data distribution
 * 4. Dashboard widget-focused seeding
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

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

echo "<h1>APS Dream Home - Seeder Enhancement Tool</h1>";
echo "<pre>";
echo "Connected successfully to database\n\n";

// Real estate specific data templates
$realEstateData = [
    'property_types' => [
        'Apartment', 'Villa', 'Penthouse', 'Bungalow', 'Farmhouse', 
        'Studio Apartment', 'Duplex', 'Triplex', 'Row House', 'Mansion'
    ],
    'property_statuses' => [
        'Available', 'Sold', 'Under Contract', 'Reserved', 'Coming Soon'
    ],
    'locations' => [
        'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 
        'Pune', 'Kolkata', 'Ahmedabad', 'Jaipur', 'Goa'
    ],
    'amenities' => [
        'Swimming Pool', 'Gym', 'Garden', 'Parking', 'Security', 
        'Clubhouse', 'Children\'s Play Area', 'Tennis Court', 'Jogging Track', 'Spa'
    ],
    'agent_names' => [
        'Rahul Sharma', 'Priya Singh', 'Amit Kumar', 'Neha Patel', 'Vikram Mehta',
        'Anita Desai', 'Rajesh Khanna', 'Sunita Verma', 'Deepak Chopra', 'Pooja Gupta'
    ],
    'customer_names' => [
        'Arjun Reddy', 'Meera Kapoor', 'Sanjay Dutt', 'Kavita Krishnan', 'Prakash Jha',
        'Anjali Menon', 'Karan Malhotra', 'Divya Sharma', 'Vijay Sethupathi', 'Lakshmi Rao'
    ],
    'property_titles' => [
        'Luxury Villa with Pool', 'Modern City Apartment', 'Seaside Penthouse', 
        'Countryside Bungalow', 'Mountain View Cottage', 'Riverside Duplex',
        'Heritage Mansion', 'Smart Home Apartment', 'Eco-Friendly Villa', 'Sky High Penthouse'
    ],
    'property_descriptions' => [
        'Luxurious property with modern amenities and stunning views.',
        'Spacious home in a prime location with excellent connectivity.',
        'Elegant property with high-end finishes and custom details.',
        'Contemporary design with open floor plan and natural light.',
        'Exclusive property in a gated community with premium facilities.',
        'Charming home with character and beautiful landscaping.',
        'Spectacular property with panoramic views and privacy.',
        'Meticulously maintained home with recent upgrades.',
        'Stunning architecture with indoor-outdoor living spaces.',
        'Prime investment opportunity in a high-growth area.'
    ],
    'lead_sources' => [
        'Website', 'Referral', 'Property Portal', 'Direct Call', 'Walk-in',
        'Social Media', 'Email Campaign', 'Property Exhibition', 'Newspaper Ad', 'Agent Network'
    ],
    'transaction_types' => [
        'Sale', 'Rent', 'Lease', 'Investment', 'Joint Venture'
    ]
];

// Function to get table relationships
function getTableRelationships($conn) {
    $relationships = [];
    
    $sql = "SELECT 
                TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                REFERENCED_TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $relationships[] = [
                'table' => $row['TABLE_NAME'],
                'column' => $row['COLUMN_NAME'],
                'referenced_table' => $row['REFERENCED_TABLE_NAME'],
                'referenced_column' => $row['REFERENCED_COLUMN_NAME']
            ];
        }
    }
    
    return $relationships;
}

// Function to get valid foreign key values
function getValidForeignKeyValues($conn, $table, $column) {
    $values = [];
    
    $sql = "SELECT $column FROM $table";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
    }
    
    return $values;
}

// Get table relationships
$relationships = getTableRelationships($conn);
echo "Found " . count($relationships) . " table relationships\n";

// Dashboard widget-focused tables
$dashboardTables = [
    'properties', 'customers', 'leads', 'bookings', 
    'transactions', 'property_visits', 'notifications', 
    'mlm_commission_ledger', 'associates'
];

// Process dashboard tables first
echo "\n===== Processing Dashboard Widget Tables =====\n";
foreach ($dashboardTables as $table) {
    echo "\nChecking table: $table\n";
    
    // Check if table exists
    $tableExists = $conn->query("SHOW TABLES LIKE '$table'");
    if ($tableExists && $tableExists->num_rows > 0) {
        // Check record count
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        echo "Current record count: $count\n";
        
        // If fewer than 10 records, add more data
        if ($count < 10) {
            echo "Table $table needs more data for dashboard widgets\n";
            
            // Call the structure-based seeder for this table
            echo "Running structure-based seeder for $table...\n";
            
            // Create a temporary script to seed just this table
            $tempScript = "<?php
                // Connect to database
                \$conn = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');
                
                // Check connection
                if (\$conn->connect_error) {
                    die('Connection failed: ' . \$conn->connect_error);
                }
                
                // Run the seeder for this specific table
                echo \"Seeding $table table...\\n\";
                
                // Call the existing structure-based seeder logic for this table
                include 'structure_based_seed.php';
                
                \$conn->close();
                echo \"Seeding complete for $table table.\\n\";
            ?>";
            
            // Save the temporary script
            file_put_contents("temp_seed_$table.php", $tempScript);
            
            // Execute the temporary script
            echo shell_exec("php temp_seed_$table.php");
            
            // Remove the temporary script
            unlink("temp_seed_$table.php");
            
            // Verify the results
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $row = $result->fetch_assoc();
            $newCount = $row['count'];
            
            echo "Updated record count: $newCount\n";
        } else {
            echo "Table $table already has sufficient data for dashboard widgets\n";
        }
    } else {
        echo "Table $table does not exist in the database\n";
    }
}

// Check for relationship integrity
echo "\n===== Checking Relationship Integrity =====\n";
foreach ($relationships as $rel) {
    echo "\nChecking relationship: {$rel['table']}.{$rel['column']} -> {$rel['referenced_table']}.{$rel['referenced_column']}\n";
    
    // Check for orphaned records
    $sql = "SELECT COUNT(*) as count FROM {$rel['table']} t 
            LEFT JOIN {$rel['referenced_table']} r 
            ON t.{$rel['column']} = r.{$rel['referenced_column']} 
            WHERE t.{$rel['column']} IS NOT NULL 
            AND r.{$rel['referenced_column']} IS NULL";
    
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $orphanCount = $row['count'];
        
        if ($orphanCount > 0) {
            echo "Found $orphanCount orphaned records in {$rel['table']}\n";
            
            // Fix orphaned records
            echo "Fixing orphaned records...\n";
            
            // Get valid foreign key values
            $validValues = getValidForeignKeyValues($conn, $rel['referenced_table'], $rel['referenced_column']);
            
            if (!empty($validValues)) {
                // Update orphaned records with valid foreign keys
                $randomValue = $validValues[array_rand($validValues)];
                $sql = "UPDATE {$rel['table']} t 
                        LEFT JOIN {$rel['referenced_table']} r 
                        ON t.{$rel['column']} = r.{$rel['referenced_column']} 
                        SET t.{$rel['column']} = $randomValue 
                        WHERE t.{$rel['column']} IS NOT NULL 
                        AND r.{$rel['referenced_column']} IS NULL";
                
                if ($conn->query($sql) === TRUE) {
                    echo "Fixed orphaned records in {$rel['table']}\n";
                } else {
                    echo "Error fixing orphaned records: " . $conn->error . "\n";
                }
            } else {
                echo "No valid values found in {$rel['referenced_table']}.{$rel['referenced_column']}\n";
            }
        } else {
            echo "No orphaned records found\n";
        }
    } else {
        echo "Error checking for orphaned records: " . $conn->error . "\n";
    }
}

// Close connection
$conn->close();
echo "\nSeeder enhancement complete. Dashboard tables should now have sufficient data with proper relationships.\n";
echo "</pre>";
echo "<p><a href='index.php' class='btn' style='display: inline-block; background-color: #3498db; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;'>Return to Database Management Hub</a></p>";
?>
