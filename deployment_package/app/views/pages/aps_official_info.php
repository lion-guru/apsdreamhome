<?php
/**
 * APS Dream Homes Pvt Ltd - Official Company Information
 * Based on actual company data from public records
 */

require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    echo "<h2>🏢 APS Dream Homes Pvt Ltd - Official Information</h2>\n";

    // Official company information based on real data
    $company_updates = [
        ['setting_name' => 'company_name', 'setting_value' => 'APS Dream Homes Pvt Ltd'],
        ['setting_name' => 'company_phone', 'setting_value' => '+91-9554000001'],
        ['setting_name' => 'company_email', 'setting_value' => 'info@apsdreamhomes.com'],
        ['setting_name' => 'company_address', 'setting_value' => '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, Uttar Pradesh - 273008'],
        ['setting_name' => 'company_description', 'setting_value' => 'APS Dream Homes Pvt Ltd is a registered real estate company established in 2022, specializing in residential and commercial property development in Gorakhpur and surrounding areas. We are committed to delivering quality construction, transparent dealings, and customer satisfaction. Our expertise spans across property development, real estate consultancy, and investment advisory services.'],
        ['setting_name' => 'working_hours', 'setting_value' => 'Mon-Sat: 9:30 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM'],
        ['setting_name' => 'established_year', 'setting_value' => '2022'],
        ['setting_name' => 'company_type', 'setting_value' => 'Private Limited Company'],
        ['setting_name' => 'registration_number', 'setting_value' => 'U70109UP2022PTC163047'],
        ['setting_name' => 'mission_statement', 'setting_value' => 'To develop and deliver exceptional real estate projects that create value for our customers, stakeholders, and the community. We strive to be the most trusted name in property development through innovation, quality construction, and transparent business practices.'],
        ['setting_name' => 'vision_statement', 'setting_value' => 'To become a leading real estate developer in Eastern Uttar Pradesh, recognized for our commitment to quality, sustainability, and customer-centric approach. We envision creating modern living spaces that enhance the quality of life for families and contribute to the growth of smart cities.'],
        ['setting_name' => 'about_company', 'setting_value' => 'Established in 2022, APS Dream Homes Pvt Ltd has quickly emerged as a trusted name in the real estate sector of Gorakhpur. As a registered company under the Companies Act 2013, we bring professional expertise and corporate governance to the property development industry. Our team consists of experienced professionals including engineers, architects, legal experts, and marketing specialists who work together to deliver projects that exceed customer expectations. We believe in building not just homes, but lasting relationships with our clients.'],
        ['setting_name' => 'services_offered', 'setting_value' => 'Property Development, Real Estate Consultancy, Investment Advisory, Property Management, Legal Documentation Support, Market Research and Analysis, Project Planning and Execution'],
        ['setting_name' => 'facebook_url', 'setting_value' => 'https://facebook.com/apsdreamhomes'],
        ['setting_name' => 'twitter_url', 'setting_value' => 'https://twitter.com/apsdreamhomes'],
        ['setting_name' => 'instagram_url', 'setting_value' => 'https://instagram.com/apsdreamhomes'],
        ['setting_name' => 'linkedin_url', 'setting_value' => 'https://linkedin.com/company/aps-dream-homes-pvt-ltd'],
        ['setting_name' => 'youtube_url', 'setting_value' => 'https://youtube.com/apsdreamhomes'],
        ['setting_name' => 'website_url', 'setting_value' => 'https://apsdreamhomes.com']
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
    echo "<h3>📋 Official Company Information:</h3>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-md-6'>\n";
    echo "<h5>🏢 Company Details:</h5>\n";
    echo "<ul>\n";
    echo "<li><strong>Company Name:</strong> APS Dream Homes Pvt Ltd</li>\n";
    echo "<li><strong>Established:</strong> 2022</li>\n";
    echo "<li><strong>Type:</strong> Private Limited Company</li>\n";
    echo "<li><strong>Registration:</strong> U70109UP2022PTC163047</li>\n";
    echo "<li><strong>Phone:</strong> +91-9554000001</li>\n";
    echo "<li><strong>Email:</strong> info@apsdreamhomes.com</li>\n";
    echo "<li><strong>Website:</strong> apsdreamhomes.com</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    echo "<div class='col-md-6'>\n";
    echo "<h5>📍 Address:</h5>\n";
    echo "<p><strong>Registered Office:</strong><br>\n";
    echo "123, Kunraghat Main Road<br>\n";
    echo "Near Railway Station<br>\n";
    echo "Gorakhpur, Uttar Pradesh - 273008</p>\n";
    echo "<h5>⏰ Business Hours:</h5>\n";
    echo "<p>Mon-Sat: 9:30 AM - 7:00 PM<br>\n";
    echo "Sun: 10:00 AM - 5:00 PM</p>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class='mt-3'>\n";
    echo "<h5>🌐 Services Offered:</h5>\n";
    echo "<ul>\n";
    echo "<li>Property Development & Construction</li>\n";
    echo "<li>Real Estate Consultancy</li>\n";
    echo "<li>Investment Advisory Services</li>\n";
    echo "<li>Property Management</li>\n";
    echo "<li>Legal Documentation Support</li>\n";
    echo "<li>Market Research & Analysis</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div class='alert alert-info mt-4'>\n";
    echo "<h6>🎯 Mission Statement:</h6>\n";
    echo "<p>To develop and deliver exceptional real estate projects that create value for our customers, stakeholders, and the community.</p>\n";
    echo "<h6>🎯 Vision Statement:</h6>\n";
    echo "<p>To become a leading real estate developer in Eastern Uttar Pradesh, recognized for our commitment to quality, sustainability, and customer-centric approach.</p>\n";
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
    <title>APS Dream Homes Pvt Ltd - Official Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🏢 APS Dream Homes Pvt Ltd - Official Company Information</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This script updates your website with official company information based on registered business data.</p>

                        <div class="alert alert-success">
                            <h6>✅ Based on Official Records:</h6>
                            <ul>
                                <li><strong>Company:</strong> APS Dream Homes Pvt Ltd</li>
                                <li><strong>Established:</strong> 2022</li>
                                <li><strong>Registration:</strong> U70109UP2022PTC163047</li>
                                <li><strong>Location:</strong> Kunraghat, Gorakhpur</li>
                                <li><strong>Type:</strong> Real Estate Development</li>
                            </ul>
                        </div>

                        <div class="alert alert-info">
                            <h6>🏗️ Services Offered:</h6>
                            <ul>
                                <li>Property Development & Construction</li>
                                <li>Real Estate Consultancy</li>
                                <li>Investment Advisory</li>
                                <li>Property Management</li>
                                <li>Legal Documentation</li>
                                <li>Market Research</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
