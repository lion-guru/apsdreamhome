<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Key pincodes for Gorakhpur/Lucknow/UP area where APS Dream Home operates
$pincodes = [
    // Gorakhpur district
    ['273001', 'Gorakhpur H.O', 'Head Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273002', 'Gorakhpur City', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273003', 'Gorakhpur University', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273004', 'Gorakhpur Cantt', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273005', 'Railway Colony', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273006', 'Civil Lines', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273007', 'Gorakhpur Kutchery', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273008', 'Gorakhpur Medical College', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273009', 'Gorakhpur Industrial Estate', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273010', 'Gorakhpur Airport', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273011', 'Purdilpur', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273012', 'Rustampur', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273013', 'Shahpur', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273014', 'Basharatpur', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273015', 'Gorakhpur Gida', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    ['273016', 'Gorakhpur Pichha', 'Sub Office', 'Delivery', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', 'UP', 26.7606, 83.3732],
    
    // Deoria district (nearby)
    ['274001', 'Deoria H.O', 'Head Office', 'Delivery', 'Deoria', 'Deoria', 'Uttar Pradesh', 'UP', 26.5016, 83.7676],
    ['274002', 'Deoria City', 'Sub Office', 'Delivery', 'Deoria', 'Deoria', 'Uttar Pradesh', 'UP', 26.5016, 83.7676],
    ['274003', 'Deoria Sadar', 'Sub Office', 'Delivery', 'Deoria', 'Deoria', 'Uttar Pradesh', 'UP', 26.5016, 83.7676],
    ['274004', 'Bhatni', 'Sub Office', 'Delivery', 'Deoria', 'Deoria', 'Uttar Pradesh', 'UP', 26.1234, 83.7676],
    ['274005', 'Salempur', 'Sub Office', 'Delivery', 'Deoria', 'Deoria', 'Uttar Pradesh', 'UP', 26.1234, 83.7676],
    
    // Kushinagar district
    ['274304', 'Padrauna H.O', 'Head Office', 'Delivery', 'Padrauna', 'Kushinagar', 'Uttar Pradesh', 'UP', 26.7333, 83.9667],
    ['274305', 'Kushinagar', 'Sub Office', 'Delivery', 'Kushinagar', 'Kushinagar', 'Uttar Pradesh', 'UP', 26.7333, 83.9667],
    ['274306', 'Hata', 'Sub Office', 'Delivery', 'Kushinagar', 'Kushinagar', 'Uttar Pradesh', 'UP', 26.7333, 83.9667],
    
    // Lucknow district
    ['226001', 'Lucknow G.P.O', 'Head Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226002', 'Lucknow Chowk', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226003', 'Lucknow Aminabad', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226004', 'Lucknow Hazratganj', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226005', 'Lucknow Aliganj', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226006', 'Lucknow Indira Nagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226007', 'Lucknow Gomti Nagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226008', 'Lucknow Vikas Nagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226009', 'Lucknow Rajajipuram', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226010', 'Lucknow Alambagh', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226011', 'Lucknow Chowk', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226012', 'Lucknow Mahanagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226013', 'Lucknow Nirala Nagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226014', 'Lucknow Aashiana', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226015', 'Lucknow Transport Nagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226016', 'Lucknow Sarojini Nagar', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226017', 'Lucknow Amausi', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226018', 'Lucknow Kakori', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226019', 'Lucknow Bakshi Ka Talab', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    ['226020', 'Lucknow Mohanlalganj', 'Sub Office', 'Delivery', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 'UP', 26.8467, 80.9462],
    
    // Varanasi district
    ['221001', 'Varanasi H.O', 'Head Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221002', 'Varanasi Cantt', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221003', 'Varanasi B.H.U', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221004', 'Varanasi Lanka', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221005', 'Varanasi Sigra', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221006', 'Varanasi Maldahia', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221007', 'Varanasi Chetganj', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221008', 'Varanasi Orderly Bazaar', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221009', 'Varanasi Jaitpura', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
    ['221010', 'Varanasi Kashi Vidyapeeth', 'Sub Office', 'Delivery', 'Varanasi', 'Varanasi', 'Uttar Pradesh', 'UP', 25.3176, 82.9739],
];

$stmt = $pdo->prepare("
    INSERT INTO pincodes (pincode, office_name, office_type, delivery_status, division_name, district_name, state_name, state_code, latitude, longitude)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        office_name = VALUES(office_name),
        office_type = VALUES(office_type),
        delivery_status = VALUES(delivery_status),
        division_name = VALUES(division_name),
        district_name = VALUES(district_name),
        state_name = VALUES(state_name),
        state_code = VALUES(state_code),
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($pincodes as $p) {
    try {
        $stmt->execute($p);
        $inserted++;
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "Inserted/Updated $inserted pincodes\n";

// Verify
$count = $pdo->query("SELECT COUNT(*) FROM pincodes")->fetchColumn();
echo "Total pincodes in table: $count\n";?>