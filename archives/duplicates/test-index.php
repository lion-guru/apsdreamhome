<?php
// Start session and include configuration

require_once 'includes/db_connection.php'; // बेहतर डेटाबेस कनेक्शन फाइल का उपयोग करें

// Get database connection
$conn = getMysqliConnection();

// Get featured properties
$featured_properties = [];
try {
    $result = $conn->query("SELECT p.*, pt.purpose, pt.type_name, pi.image_path
                           FROM properties p
                           LEFT JOIN property_types pt ON p.property_type_id = pt.id
                           LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
                           WHERE p.status = 'available' AND p.is_featured = 1
                           ORDER BY p.created_at DESC
                           LIMIT 6");

    if ($result) {
        $featured_properties = $result->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log('Error fetching featured properties: ' . $e->getMessage());
    $featured_properties = [];
}

// Get property counts
$counts = [
    'total' => 0,
    'sale' => 0,
    'rent' => 0,
    'agents' => 0
];

try {
    // Get counts
    $result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $counts['total'] = $row ? (int)$row['count'] : 0;
    }

    // Properties for sale
    $result = $conn->query("SELECT COUNT(*) as count FROM properties p
                           INNER JOIN property_types pt ON p.property_type_id = pt.id
                           WHERE pt.purpose = 'sale' AND p.status = 'available'");
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $counts['sale'] = $row ? (int)$row['count'] : 0;
    }

    // Properties for rent
    $result = $conn->query("SELECT COUNT(*) as count FROM properties p
                           INNER JOIN property_types pt ON p.property_type_id = pt.id
                           WHERE pt.purpose = 'rent' AND p.status = 'available'");
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $counts['rent'] = $row ? (int)$row['count'] : 0;
    }

    // Total agents
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'agent' AND status = 'active'");
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $counts['agents'] = $row ? (int)$row['count'] : 0;
    }
} catch (Exception $e) {
    error_log('Error fetching counts: ' . $e->getMessage());
}
?>
