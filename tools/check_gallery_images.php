<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if gallery table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'gallery'");
    $galleryExists = $stmt->fetch();
    
    if ($galleryExists) {
        echo "✓ gallery table exists\n";
        
        // Get all gallery images
        $stmt = $conn->query("SELECT id, image_path, caption, status FROM gallery");
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nGallery images in database:\n";
        foreach ($images as $image) {
            echo "ID: {$image['id']}, Path: {$image['image_path']}, Caption: {$image['caption']}, Status: {$image['status']}\n";
            
            // Check if file exists
            $fullPath = __DIR__ . '/../public/' . $image['image_path'];
            $fileExists = file_exists($fullPath);
            echo "  File exists: " . ($fileExists ? "YES" : "NO") . "\n";
        }
        
        echo "\nTotal images: " . count($images) . "\n";
    } else {
        echo "✗ gallery table does NOT exist\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
