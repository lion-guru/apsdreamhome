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

// Fix missing names in leads using prepared statements
echo "=== Fixing Missing Names in Leads ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE name IS NULL OR name = ''");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing names. Fixing...\n";

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $name = "Lead " . $id;

            $update_stmt = $conn->prepare("UPDATE leads SET name = ? WHERE id = ?");
            $update_stmt->bind_param("si", $name, $id);
            $update_stmt->execute();
            $update_stmt->close();
            echo "- Updated lead ID $id with name '$name'\n";
        }
    } else {
        echo "No leads with missing names found.\n";
    }
} else {
    echo "Error querying leads with missing names: " . $conn->error . "\n";
}
$stmt->close();

// Fix missing contact information using prepared statements
echo "\n=== Fixing Missing Contact Information ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE contact IS NULL OR contact = ''");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing contact info. Fixing...\n";

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $contact = "contact" . $id . "@example.com";

            $update_stmt = $conn->prepare("UPDATE leads SET contact = ? WHERE id = ?");
            $contact = "+91 98" . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $update_stmt->bind_param("si", $contact, $id);
            $update_stmt->execute();
            $update_stmt->close();
            echo "- Updated lead ID $id with contact '$contact'\n";
        }
    } else {
        echo "No leads with missing contact info found.\n";
    }
    $stmt->close();
} else {
    echo "Error querying leads with missing contact info: " . $conn->error . "\n";
}

// Fix missing source information
echo "\n=== Fixing Missing Source Information ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE source IS NULL OR source = ''");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing source. Fixing...\n";
        $sources = ['Website', 'Referral', 'Walk-in', 'Advertisement', 'Social Media'];
        $update_stmt = $conn->prepare("UPDATE leads SET source = ? WHERE id = ?");
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $source = $sources[array_rand($sources)];
            
            $update_stmt->bind_param("si", $source, $id);
            $update_stmt->execute();
            echo "- Updated lead ID $id with source '$source'\n";
        }
        $update_stmt->close();
    } else {
        echo "No leads with missing source found.\n";
    }
    $stmt->close();
} else {
    echo "Error querying leads with missing source: " . $conn->error . "\n";
}

// Fix missing status information
echo "\n=== Fixing Missing Status Information ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE status IS NULL OR status = ''");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing status. Fixing...\n";
        $statuses = ['New', 'Contacted', 'Qualified', 'Proposal Sent', 'Negotiation', 'Closed Won', 'Closed Lost'];
        $update_stmt = $conn->prepare("UPDATE leads SET status = ? WHERE id = ?");
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $status = $statuses[array_rand($statuses)];
            
            $update_stmt->bind_param("si", $status, $id);
            $update_stmt->execute();
            echo "- Updated lead ID $id with status '$status'\n";
        }
        $update_stmt->close();
    } else {
        echo "No leads with missing status found.\n";
    }
    $stmt->close();
} else {
    echo "Error querying leads with missing status: " . $conn->error . "\n";
}

// Fix missing created_at information
echo "\n=== Fixing Missing Created At Information ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE created_at IS NULL");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing created_at. Fixing...\n";
        $update_stmt = $conn->prepare("UPDATE leads SET created_at = ? WHERE id = ?");
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $created_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 90) . ' days'));
            
            $update_stmt->bind_param("si", $created_at, $id);
            $update_stmt->execute();
            echo "- Updated lead ID $id with created_at '$created_at'\n";
        }
        $update_stmt->close();
    } else {
        echo "No leads with missing created_at found.\n";
    }
    $stmt->close();
} else {
    echo "Error querying leads with missing created_at: " . $conn->error . "\n";
}

// Fix missing assigned_to information
echo "\n=== Fixing Missing Assigned To Information ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE assigned_to IS NULL");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing assignment. Fixing...\n";
        
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
$stmt = $conn->prepare("SELECT id FROM leads WHERE status IS NULL OR status = ''");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing status. Fixing...\n";
        $statuses = ['New', 'Contacted', 'Qualified', 'Proposal Sent', 'Negotiation', 'Closed Won', 'Closed Lost'];
        $update_stmt = $conn->prepare("UPDATE leads SET status = ? WHERE id = ?");
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $status = $statuses[array_rand($statuses)];
            
            $update_stmt->bind_param("si", $status, $id);
            $update_stmt->execute();
            echo "- Updated lead ID $id with status '$status'\n";
        }
        $update_stmt->close();
    } else {
        echo "No leads with missing status found.\n";
    }
    $stmt->close();
} else {
    echo "Error querying leads with missing status: " . $conn->error . "\n";
}

// Add missing assigned_to values if needed
echo "\n=== Fixing Missing Assigned To Information ===\n";
$stmt = $conn->prepare("SELECT id FROM leads WHERE assigned_to IS NULL");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " leads with missing assignment. Fixing...\n";
        
        // Check if users table exists and get its structure
        $userTableExists = false;
        $userColumns = [];
        $tableCheck = $conn->prepare("SHOW TABLES LIKE ?");
        $usersTable = 'users';
        $tableCheck->bind_param("s", $usersTable);
        $tableCheck->execute();
        $tableResult = $tableCheck->get_result();
        
        if ($tableResult && $tableResult->num_rows > 0) {
            $userTableExists = true;
            $columnsResult = $conn->query("DESCRIBE users");
            if ($columnsResult) {
                while ($columnRow = $columnsResult->fetch_assoc()) {
                    $userColumns[] = $columnRow['Field'];
                }
                $columnsResult->close();
            }
        }
        $tableCheck->close();
        
        // Get available agent IDs
        $agentIds = [];
        
        if ($userTableExists) {
            // Try to find agents based on available columns
            $roleColumnExists = in_array('role', $userColumns);
            $idColumnExists = in_array('id', $userColumns);
            
            if ($roleColumnExists && $idColumnExists) {
                $agentQuery = "SELECT id FROM users WHERE role IN ('agent', 'admin', 'staff') ORDER BY id";
                $agentResult = $conn->query($agentQuery);
                if ($agentResult && $agentResult->num_rows > 0) {
                    while ($agentRow = $agentResult->fetch_assoc()) {
                        $agentIds[] = (int)$agentRow['id'];
                    }
                }
                if (isset($agentResult)) {
                    $agentResult->close();
                }
            }
            
            // Default to admin ID 1 if no agents found
            if (empty($agentIds)) {
                $agentIds = [1];
            }
        } else {
            // Default to IDs 1-5 if users table doesn't exist
            $agentIds = [1, 2, 3, 4, 5];
            echo "Users table not found. Using default agent IDs 1-5.\n";
        }
        
        $update_stmt = $conn->prepare("UPDATE leads SET assigned_to = ? WHERE id = ?");
        
        if ($update_stmt) {
            while ($row = $result->fetch_assoc()) {
                $id = (int)$row['id'];
                $assignedTo = $agentIds[array_rand($agentIds)];
                
                $update_stmt->bind_param("ii", $assignedTo, $id);
                if ($update_stmt->execute()) {
                    echo "- Updated lead ID $id with assigned_to $assignedTo\n";
                } else {
                    echo "- Error updating lead ID $id: " . $conn->error . "\n";
                }
            }
            $update_stmt->close();
        } else {
            echo "Error preparing update statement: " . $conn->error . "\n";
        }
    } else {
        echo "No leads with missing assignment found.\n";
    }
    $stmt->close();
} else {
    echo "Error querying leads with missing assignment: " . $conn->error . "\n";
}

// Close connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

echo "\n=== Lead Data Fix Complete ===\n";
?>
