<?php
/**
 * Plot Selling Database Tables
 * Creates tables for associate plot selling features
 */

// Include database connection
require_once 'includes/db_connection.php';

try {
    $conn = getDbConnection();

    echo "<h2>🏗️ Creating Plot Selling Database Tables</h2>\n";

    // Plot Bookings table
    $sql = "CREATE TABLE IF NOT EXISTS plot_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        plot_id INT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(15) NOT NULL,
        customer_email VARCHAR(100),
        booking_type ENUM('hold', 'book') DEFAULT 'hold',
        status ENUM('active', 'cancelled', 'expired', 'converted') DEFAULT 'active',
        expires_at DATETIME NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE,
        FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql)) {
        echo "<p>✅ Plot Bookings table created successfully</p>\n";
    } else {
        echo "<p>❌ Error creating plot_bookings table: " . $conn->error . "</p>\n";
    }

    // Customer Referrals table
    $sql = "CREATE TABLE IF NOT EXISTS customer_referrals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(15) NOT NULL,
        customer_email VARCHAR(100),
        preferred_location VARCHAR(255),
        budget_range VARCHAR(50),
        plot_type ENUM('residential', 'commercial', 'industrial') DEFAULT 'residential',
        status ENUM('new', 'contacted', 'interested', 'converted', 'rejected') DEFAULT 'new',
        notes TEXT,
        referred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        contacted_at TIMESTAMP NULL,
        converted_at TIMESTAMP NULL,
        commission_earned DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql)) {
        echo "<p>✅ Customer Referrals table created successfully</p>\n";
    } else {
        echo "<p>❌ Error creating customer_referrals table: " . $conn->error . "</p>\n";
    }

    // Plot Sales table
    $sql = "CREATE TABLE IF NOT EXISTS plot_sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        plot_id INT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(15) NOT NULL,
        sale_price DECIMAL(15,2) NOT NULL,
        commission_earned DECIMAL(10,2) NOT NULL,
        commission_paid BOOLEAN DEFAULT FALSE,
        sale_date DATE NOT NULL,
        payment_status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
        documents JSON,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE,
        FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql)) {
        echo "<p>✅ Plot Sales table created successfully</p>\n";
    } else {
        echo "<p>❌ Error creating plot_sales table: " . $conn->error . "</p>\n";
    }

    // Associate Plot Allocation table
    $sql = "CREATE TABLE IF NOT EXISTS associate_plot_allocations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        plot_id INT NOT NULL,
        allocated_by INT,
        allocation_date DATE NOT NULL,
        allocation_type ENUM('permanent', 'temporary') DEFAULT 'permanent',
        status ENUM('active', 'returned', 'expired') DEFAULT 'active',
        expiry_date DATE,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE,
        FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
        FOREIGN KEY (allocated_by) REFERENCES users(id) ON DELETE SET NULL
    )";

    if ($conn->query($sql)) {
        echo "<p>✅ Associate Plot Allocations table created successfully</p>\n";
    } else {
        echo "<p>❌ Error creating associate_plot_allocations table: " . $conn->error . "</p>\n";
    }

    // Commission Tracking table
    $sql = "CREATE TABLE IF NOT EXISTS associate_commission_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        sale_id INT,
        booking_id INT,
        commission_type ENUM('direct_sale', 'referral', 'team_bonus', 'override') NOT NULL,
        commission_amount DECIMAL(10,2) NOT NULL,
        commission_percentage DECIMAL(5,2),
        calculation_basis DECIMAL(15,2),
        level_affected INT DEFAULT 1,
        payout_status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
        payout_date DATE,
        transaction_id VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE,
        FOREIGN KEY (sale_id) REFERENCES plot_sales(id) ON DELETE CASCADE,
        FOREIGN KEY (booking_id) REFERENCES plot_bookings(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql)) {
        echo "<p>✅ Associate Commission Tracking table created successfully</p>\n";
    } else {
        echo "<p>❌ Error creating associate_commission_tracking table: " . $conn->error . "</p>\n";
    }

    echo "<hr>\n";
    echo "<h3>📋 Database Tables Summary:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>plot_bookings:</strong> Track plot bookings and holds by associates</li>\n";
    echo "<li><strong>customer_referrals:</strong> Customer referrals submitted by associates</li>\n";
    echo "<li><strong>plot_sales:</strong> Completed plot sales with commission tracking</li>\n";
    echo "<li><strong>associate_plot_allocations:</strong> Track which plots are allocated to which associates</li>\n";
    echo "<li><strong>associate_commission_tracking:</strong> Detailed commission tracking for all transactions</li>\n";
    echo "</ul>\n";

    echo "<div class='alert alert-success mt-4'>\n";
    echo "<h6>✅ Database Setup Complete!</h6>\n";
    echo "<p>All necessary tables for the plot selling system have been created successfully.</p>\n";
    echo "</div>\n";

    echo "<div class='mt-4'>\n";
    echo "<a href='associate/plot-inventory' class='btn btn-primary me-2'>View Plot Inventory</a>\n";
    echo "<a href='associate/commission-calculator' class='btn btn-success me-2'>Commission Calculator</a>\n";
    echo "<a href='associate/plot-booking' class='btn btn-warning'>Plot Booking</a>\n";
    echo "</div>\n";

} catch (Exception $e) {
    echo "<h3>❌ Database Error:</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plot Selling Database Setup - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🏗️ Plot Selling Database Setup</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Setting up database tables for the complete plot selling system through MLM.</p>

                        <div class="alert alert-info">
                            <h6>📋 Tables Being Created:</h6>
                            <ul>
                                <li><strong>plot_bookings:</strong> Track plot bookings and holds</li>
                                <li><strong>customer_referrals:</strong> Customer referrals by associates</li>
                                <li><strong>plot_sales:</strong> Completed sales with commission tracking</li>
                                <li><strong>associate_plot_allocations:</strong> Plot allocation management</li>
                                <li><strong>associate_commission_tracking:</strong> Detailed commission tracking</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6>🔄 System Features:</h6>
                            <p>This setup enables associates to:</p>
                            <ul>
                                <li>View their allocated plot inventory</li>
                                <li>Calculate real-time commissions</li>
                                <li>Book plots for customers</li>
                                <li>Track sales performance</li>
                                <li>Manage customer referrals</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
