<?php
/**
 * Quick Company Information Updater
 * This script helps you quickly update your company details
 */

// Get user input (you can modify these values)
$company_name = "APS Dream Home"; // Change this to your company name
$company_phone = "+91-9000000001"; // Change to your phone number
$company_email = "info@apsdreamhome.com"; // Change to your email
$company_address = "Gorakhpur, Uttar Pradesh, India"; // Change to your address
$company_description = "Your trusted partner in real estate solutions. We provide comprehensive property services with modern technology and personalized approach."; // Your description

require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    echo "<h2>🏢 Updating Company Information</h2>\n";

    // Update site settings
    $updates = [
        ['setting_name' => 'company_name', 'setting_value' => $company_name],
        ['setting_name' => 'company_phone', 'setting_value' => $company_phone],
        ['setting_name' => 'company_email', 'setting_value' => $company_email],
        ['setting_name' => 'company_address', 'setting_value' => $company_address],
        ['setting_name' => 'company_description', 'setting_value' => $company_description]
    ];

    foreach ($updates as $update) {
        // Check if setting exists
        $check_sql = "SELECT id FROM site_settings WHERE setting_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$update['setting_name']]);

        if ($check_stmt->rowCount() > 0) {
            // Update existing setting
            $sql = "UPDATE site_settings SET setting_value = ? WHERE setting_name = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$update['setting_value'], $update['setting_name']]);
        } else {
            // Insert new setting
            $sql = "INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$update['setting_name'], $update['setting_value']]);
        }

        if ($result) {
            echo "<p>✅ Updated: " . $update['setting_name'] . "</p>\n";
        } else {
            echo "<p>❌ Failed to update: " . $update['setting_name'] . "</p>\n";
        }
    }

    echo "<hr>\n";
    echo "<h3>📋 Updated Information:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>Company Name:</strong> $company_name</li>\n";
    echo "<li><strong>Phone:</strong> $company_phone</li>\n";
    echo "<li><strong>Email:</strong> $company_email</li>\n";
    echo "<li><strong>Address:</strong> $company_address</li>\n";
    echo "<li><strong>Description:</strong> $company_description</li>\n";
    echo "</ul>\n";

    echo "<div class='mt-4'>\n";
    echo "<a href='index.php' class='btn btn-primary'>View Homepage</a>\n";
    echo "<a href='about.php' class='btn btn-secondary'>View About Page</a>\n";
    echo "<a href='contact.php' class='btn btn-success'>View Contact Page</a>\n";
    echo "</div>\n";

} catch (PDOException $e) {
    echo "<h3>❌ Database Error:</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Please make sure your database is set up correctly.</p>\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Company Info - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🔧 Customize Your Company Information</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">To customize, edit the variables at the top of this file and refresh the page.</p>

                        <div class="alert alert-info">
                            <h6>📝 Edit these lines in the file:</h6>
                            <code>
                                $company_name = "Your Company Name";<br>
                                $company_phone = "+91-YourPhoneNumber";<br>
                                $company_email = "info@yourcompany.com";<br>
                                $company_address = "Your City, State, India";<br>
                                $company_description = "Your company description...";
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
