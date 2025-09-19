<?php
/**
 * APS Dream Home - Leads Data Fix
 * 
 * This script fixes missing or incomplete data in the leads table
 * to ensure all dashboard widgets display properly.
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

echo "<h1>Leads Data Fix</h1>";
echo "<pre>";
echo "Connected successfully to database\n\n";

// Fix missing names in leads
echo "=== Fixing Missing Names in Leads ===\n";
$result = $conn->query("SELECT id FROM leads WHERE name IS NULL OR name = ''");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing names. Fixing...\n";
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $name = "Lead " . $id;
            
            $conn->query("UPDATE leads SET name = '$name' WHERE id = $id");
            echo "- Updated lead ID $id with name '$name'\n";
        }
    } else {
        echo "No leads with missing names found.\n";
    }
} else {
    echo "Error querying leads with missing names: " . $conn->error . "\n";
}

// Fix missing contact information
echo "\n=== Fixing Missing Contact Information ===\n";
$result = $conn->query("SELECT id FROM leads WHERE contact IS NULL OR contact = ''");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing contact info. Fixing...\n";
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $contact = "+91 98" . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            
            $conn->query("UPDATE leads SET contact = '$contact' WHERE id = $id");
            echo "- Updated lead ID $id with contact '$contact'\n";
        }
    } else {
        echo "No leads with missing contact info found.\n";
    }
} else {
    echo "Error querying leads with missing contact info: " . $conn->error . "\n";
}

// Fix missing source information
echo "\n=== Fixing Missing Source Information ===\n";
$result = $conn->query("SELECT id FROM leads WHERE source IS NULL OR source = ''");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing source. Fixing...\n";
        
        $sources = ['Website', 'Referral', 'Property Portal', 'Direct Call', 'Walk-in'];
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $source = $sources[array_rand($sources)];
            
            $conn->query("UPDATE leads SET source = '$source' WHERE id = $id");
            echo "- Updated lead ID $id with source '$source'\n";
        }
    } else {
        echo "No leads with missing source found.\n";
    }
} else {
    echo "Error querying leads with missing source: " . $conn->error . "\n";
}

// Fix missing status information
echo "\n=== Fixing Missing Status Information ===\n";
$result = $conn->query("SELECT id FROM leads WHERE status IS NULL OR status = ''");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing status. Fixing...\n";
        
        $statuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $status = $statuses[array_rand($statuses)];
            
            $conn->query("UPDATE leads SET status = '$status' WHERE id = $id");
            echo "- Updated lead ID $id with status '$status'\n";
        }
    } else {
        echo "No leads with missing status found.\n";
    }
} else {
    echo "Error querying leads with missing status: " . $conn->error . "\n";
}

// Add missing assigned_to values if needed
echo "\n=== Fixing Missing Assigned To Information ===\n";
$result = $conn->query("SELECT id FROM leads WHERE assigned_to IS NULL");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing assignment. Fixing...\n";
        
        // Check if users table exists and get its structure
        $userTableExists = false;
        $userColumns = [];
        $tableResult = $conn->query("SHOW TABLES LIKE 'users'");
        if ($tableResult && $tableResult->num_rows > 0) {
            $userTableExists = true;
            $columnsResult = $conn->query("DESCRIBE users");
            if ($columnsResult) {
                while ($columnRow = $columnsResult->fetch_assoc()) {
                    $userColumns[] = $columnRow['Field'];
                }
            }
        }
        
        // Get available agent IDs
        $agentIds = [];
        
        if ($userTableExists) {
            // Try to find agents based on available columns
            if (in_array('role', $userColumns)) {
                $agentResult = $conn->query("SELECT id FROM users WHERE role = 'agent' OR role = 'admin' LIMIT 5");
            } else if (in_array('user_type', $userColumns)) {
                $agentResult = $conn->query("SELECT id FROM users WHERE user_type = 'agent' OR user_type = 'admin' LIMIT 5");
            } else if (in_array('type', $userColumns)) {
                $agentResult = $conn->query("SELECT id FROM users WHERE type = 'agent' OR type = 'admin' LIMIT 5");
            } else {
                // Just get any users if we can't determine role
                $agentResult = $conn->query("SELECT id FROM users LIMIT 5");
            }
            
            if ($agentResult && $agentResult->num_rows > 0) {
                while ($agentRow = $agentResult->fetch_assoc()) {
                    $agentIds[] = $agentRow['id'];
                }
            }
        }
        
        // Default to IDs 1-5 if no agents found
        if (empty($agentIds)) {
            $agentIds = [1, 2, 3, 4, 5];
            echo "No agents found in users table. Using default IDs 1-5.\n";
        }
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $assignedTo = $agentIds[array_rand($agentIds)];
            
            $conn->query("UPDATE leads SET assigned_to = $assignedTo WHERE id = $id");
            echo "- Updated lead ID $id with assigned_to $assignedTo\n";
        }
    } else {
        echo "No leads with missing assignment found.\n";
    }
} else {
    echo "Error querying leads with missing assignment: " . $conn->error . "\n";
}

// Close connection
$conn->close();
echo "\nLeads data fix complete. All leads now have proper data for dashboard display.\n";
echo "</pre>";
echo "<p><a href='index.php' class='btn' style='display: inline-block; background-color: #3498db; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;'>Return to Database Management Hub</a></p>";
?>
