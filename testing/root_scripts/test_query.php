<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$sql = "SELECT p.id, p.title, p.price, p.location, p.area_sqft, 
               p.bedrooms, p.bathrooms, p.status,
               pi.image_path as primary_image
        FROM properties p
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
        WHERE p.status IN ('available', 'under_construction')
        ORDER BY p.created_at DESC
        LIMIT 50";

try {
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "SUCCESS - Found " . count($results) . " properties\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
