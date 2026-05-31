<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop existing tables
    $db->exec("DROP TABLE IF EXISTS colonies");
    $db->exec("DROP TABLE IF EXISTS districts");
    $db->exec("DROP TABLE IF EXISTS states");
    
    // Create tables
    $db->exec("CREATE TABLE states (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, is_active TINYINT DEFAULT 1)");
    $db->exec("CREATE TABLE districts (id INT AUTO_INCREMENT PRIMARY KEY, state_id INT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, is_active TINYINT DEFAULT 1, FOREIGN KEY (state_id) REFERENCES states(id))");
    $db->exec("CREATE TABLE colonies (id INT AUTO_INCREMENT PRIMARY KEY, district_id INT NOT NULL, name VARCHAR(150) NOT NULL, description TEXT, amenities TEXT, map_link VARCHAR(500), total_plots INT DEFAULT 0, available_plots INT DEFAULT 0, starting_price DECIMAL(12,2) DEFAULT 0, image_path VARCHAR(500), brochure_path VARCHAR(500), is_featured TINYINT DEFAULT 0, is_active TINYINT DEFAULT 1, FOREIGN KEY (district_id) REFERENCES districts(id))");
    
    // Insert sample data
    $db->exec("INSERT INTO states (name, code) VALUES ('Uttar Pradesh', 'UP'), ('Madhya Pradesh', 'MP'), ('Rajasthan', 'RJ')");
    $db->exec("INSERT INTO districts (state_id, name, code) VALUES (1, 'Gorakhpur', 'GKP'), (1, 'Lucknow', 'LKO'), (2, 'Bhopal', 'BPL'), (3, 'Jaipur', 'JPR')");
    $db->exec("INSERT INTO colonies (district_id, name, description, amenities, map_link, total_plots, available_plots, starting_price, image_path, brochure_path, is_featured) VALUES (1, 'Kushinagar Colony', 'Premium plots', 'Park,Temple,School', 'https://maps.google.com/?q=Kushinagar', 150, 45, 2500000, 'assets/kushinagar.jpg', 'assets/kushinagar.pdf', 1)");
    
    echo "✅ Location hierarchy setup complete!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
