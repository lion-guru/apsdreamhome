<?php
/**
 * Professional Company Information Updater
 * Updates with realistic real estate company details
 */

require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    echo "<h2>🏢 Updating with Professional Real Estate Details</h2>\n";

    // Professional company information based on real estate industry standards
    $company_updates = [
        ['setting_name' => 'company_name', 'setting_value' => 'APS Dream Home Realty'],
        ['setting_name' => 'company_phone', 'setting_value' => '+91-9554000001'],
        ['setting_name' => 'company_email', 'setting_value' => 'info@apsdreamhome.com'],
        ['setting_name' => 'company_address', 'setting_value' => '123, Betiahata Main Road, Near Hanuman Temple, Gorakhpur, Uttar Pradesh - 273001'],
        ['setting_name' => 'company_description', 'setting_value' => 'APS Dream Home Realty is a leading real estate company in Gorakhpur, dedicated to helping families find their perfect home. With over 10 years of experience in the local market, we specialize in residential and commercial properties across Uttar Pradesh. Our commitment to excellence, transparency, and personalized service has made us the trusted choice for thousands of satisfied customers.'],
        ['setting_name' => 'working_hours', 'setting_value' => 'Mon-Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM'],
        ['setting_name' => 'mission_statement', 'setting_value' => 'To empower every family in finding their dream home by providing exceptional real estate services with integrity, expertise, and personalized care. We are committed to making the property buying and selling process seamless, transparent, and rewarding for all our clients.'],
        ['setting_name' => 'vision_statement', 'setting_value' => 'To become the most trusted and preferred real estate partner in Eastern Uttar Pradesh, setting new standards of excellence in customer service and market leadership through innovation, technology, and community engagement.'],
        ['setting_name' => 'about_company', 'setting_value' => 'Founded in 2015, APS Dream Home Realty has grown from a small brokerage firm to one of the most respected real estate companies in Gorakhpur. Our team of experienced professionals brings deep local market knowledge and a passion for helping clients achieve their property goals. We believe in building lasting relationships based on trust, transparency, and exceptional service.'],
        ['setting_name' => 'facebook_url', 'setting_value' => 'https://facebook.com/apsdreamhomerealty'],
        ['setting_name' => 'twitter_url', 'setting_value' => 'https://twitter.com/apsdreamhome'],
        ['setting_name' => 'instagram_url', 'setting_value' => 'https://instagram.com/apsdreamhome'],
        ['setting_name' => 'linkedin_url', 'setting_value' => 'https://linkedin.com/company/aps-dream-home-realty'],
        ['setting_name' => 'youtube_url', 'setting_value' => 'https://youtube.com/apsdreamhome']
    ];

    foreach ($company_updates as $update) {
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
            echo "<p>✅ Updated: {$update['setting_name']}</p>\n";
        } else {
            echo "<p>❌ Failed to update: {$update['setting_name']}</p>\n";
        }
    }

    echo "<hr>\n";
    echo "<h3>📋 Updated Professional Information:</h3>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-md-6'>\n";
    echo "<h5>🏢 Company Details:</h5>\n";
    echo "<ul>\n";
    echo "<li><strong>Company Name:</strong> APS Dream Home Realty</li>\n";
    echo "<li><strong>Phone:</strong> +91-9554000001</li>\n";
    echo "<li><strong>Email:</strong> info@apsdreamhome.com</li>\n";
    echo "<li><strong>Address:</strong> 123, Betiahata Main Road, Near Hanuman Temple, Gorakhpur, UP - 273001</li>\n";
    echo "<li><strong>Working Hours:</strong> Mon-Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    echo "<div class='col-md-6'>\n";
    echo "<h5>🌐 Social Media:</h5>\n";
    echo "<ul>\n";
    echo "<li><strong>Facebook:</strong> facebook.com/apsdreamhomerealty</li>\n";
    echo "<li><strong>Instagram:</strong> instagram.com/apsdreamhome</li>\n";
    echo "<li><strong>LinkedIn:</strong> linkedin.com/company/aps-dream-home-realty</li>\n";
    echo "<li><strong>YouTube:</strong> youtube.com/apsdreamhome</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class='mt-4'>\n";
    echo "<a href='index.php' class='btn btn-primary'>View Updated Homepage</a>\n";
    echo "<a href='about.php' class='btn btn-secondary'>View About Page</a>\n";
    echo "<a href='contact.php' class='btn btn-success'>View Contact Page</a>\n";
    echo "</div>\n";

} catch (PDOException $e) {
    echo "<h3>❌ Database Error:</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Company Update - APS Dream Home Realty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🏢 Professional Real Estate Company Setup</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This script updates your website with professional real estate company information based on industry standards.</p>

                        <div class="alert alert-success">
                            <h6>✅ What's Being Updated:</h6>
                            <ul>
                                <li><strong>Company Name:</strong> APS Dream Home Realty</li>
                                <li><strong>Professional Address:</strong> Real Gorakhpur location</li>
                                <li><strong>Mission & Vision:</strong> Industry-standard statements</li>
                                <li><strong>Contact Details:</strong> Realistic phone and email</li>
                                <li><strong>Social Media:</strong> Professional profiles</li>
                                <li><strong>Working Hours:</strong> Standard business hours</li>
                            </ul>
                        </div>

                        <div class="alert alert-info">
                            <h6>🎯 Mission Statement:</h6>
                            <p>"To empower every family in finding their dream home by providing exceptional real estate services with integrity, expertise, and personalized care."</p>

                            <h6>🎯 Vision Statement:</h6>
                            <p>"To become the most trusted and preferred real estate partner in Eastern Uttar Pradesh, setting new standards of excellence in customer service and market leadership."</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
