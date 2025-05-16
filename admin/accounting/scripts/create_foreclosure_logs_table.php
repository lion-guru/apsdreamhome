<?php
require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once dirname(__DIR__, 3) . '/includes/db_connection.php';

try {
    $conn = getDbConnection();
    
    // SQL to create foreclosure logs table
    $sql = "CREATE TABLE IF NOT EXISTS foreclosure_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        emi_plan_id INT NOT NULL,
        status ENUM('success', 'failed') NOT NULL,
        message TEXT,
        attempted_by INT NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (emi_plan_id) REFERENCES emi_plans(id),
        FOREIGN KEY (attempted_by) REFERENCES users(id),
        INDEX idx_emi_plan (emi_plan_id),
        INDEX idx_status (status),
        INDEX idx_attempted_at (attempted_at)
    )";
    
    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "Foreclosure logs table created successfully.\n";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Foreclosure Logs Table Creation Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
?>
