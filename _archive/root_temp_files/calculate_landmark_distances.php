<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Landmarks table
$pdo->exec("
CREATE TABLE IF NOT EXISTS landmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('school', 'hospital', 'mall', 'metro_station', 'railway_station', 'bus_stand', 'airport', 'university', 'market', 'park', 'temple', 'bank', 'police_station', 'fire_station', 'post_office', 'court', 'government_office') NOT NULL,
    sub_type VARCHAR(100) DEFAULT '',
    address TEXT DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    state VARCHAR(100) DEFAULT '',
    pincode VARCHAR(10) DEFAULT '',
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    rating DECIMAL(3,2) DEFAULT NULL,
    contact VARCHAR(50) DEFAULT '',
    website VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_type (type),
    KEY idx_city (city),
    KEY idx_location (latitude, longitude),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "landmarks table created/verified\n";

// Colony landmark distances table
$pdo->exec("
CREATE TABLE IF NOT EXISTS colony_landmark_distances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colony_id INT UNSIGNED NOT NULL,
    landmark_id BIGINT UNSIGNED NOT NULL,
    distance_km DECIMAL(8,3) NOT NULL COMMENT 'Straight-line distance in km',
    driving_distance_km DECIMAL(8,3) DEFAULT NULL COMMENT 'Actual driving distance in km',
    driving_time_min INT DEFAULT NULL COMMENT 'Driving time in minutes',
    walking_time_min INT DEFAULT NULL COMMENT 'Walking time in minutes',
    transport_options JSON DEFAULT NULL COMMENT 'Available transport: bus, metro, auto, cab',
    notes TEXT DEFAULT '',
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_colony_landmark (colony_id, landmark_id),
    KEY idx_colony (colony_id),
    KEY idx_landmark (landmark_id),
    KEY idx_distance (distance_km)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "colony_landmark_distances table created/verified\n";

// Insert major landmarks for Gorakhpur/Lucknow area
$landmarks = [
    // Gorakhpur - Schools
    ['St. Joseph\'s School', 'school', 'senior_secondary', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7606, 83.3732, 4.5, '0551-2331234', ''],
    ['Delhi Public School', 'school', 'senior_secondary', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', 26.7500, 83.3800, 4.3, '0551-2581234', ''],
    ['Kendriya Vidyalaya', 'school', 'senior_secondary', 'Air Force Station, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7400, 83.3600, 4.2, '0551-2332233', ''],
    ['Sunbeam School', 'school', 'senior_secondary', 'Rustampur, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273016', 26.7300, 83.3500, 4.0, '0551-2781234', ''],
    
    // Gorakhpur - Hospitals
    ['BRD Medical College', 'hospital', 'government', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', 26.7450, 83.3850, 4.1, '0551-2581234', ''],
    ['District Hospital', 'hospital', 'government', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7600, 83.3700, 4.0, '0551-2333333', ''],
    ['Fatima Hospital', 'hospital', 'private', 'Padri Bazar, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273014', 26.7550, 83.3900, 4.3, '0551-2785678', ''],
    ['Railway Hospital', 'hospital', 'government', 'Railway Colony, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273012', 26.7650, 83.3650, 4.2, '0551-2334444', ''],
    ['Star Hospital', 'hospital', 'private', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', 26.7480, 83.3880, 4.4, '0551-2589999', ''],
    
    // Gorakhpur - Malls/Shopping
    ['Crossroad Mall', 'mall', 'shopping', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', 26.7490, 83.3870, 4.2, '0551-2587777', ''],
    ['City Mall', 'mall', 'shopping', 'Golghar, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7620, 83.3720, 4.0, '0551-2335555', ''],
    ['V-Mart', 'mall', 'retail', 'Bank Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7580, 83.3750, 3.9, '0551-2336666', ''],
    
    // Gorakhpur - Transport
    ['Gorakhpur Railway Station', 'railway_station', 'major', 'Railway Station Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7610, 83.3680, 4.5, '0551-2331234', 'https://indianrail.gov.in'],
    ['Gorakhpur Cantt Railway Station', 'railway_station', 'minor', 'Cantt Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7550, 83.3750, 4.3, '0551-2332222', ''],
    ['Mahayogi Gorakhnath Airport', 'airport', 'domestic', 'Airport Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273008', 26.7300, 83.4000, 4.4, '0551-2201234', 'https://aai.aero'],
    ['Gorakhpur Bus Stand', 'bus_stand', 'major', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7600, 83.3720, 4.5, '0551-2339999', ''],
    
    // Gorakhpur - Universities
    ['Deen Dayal Upadhyay Gorakhpur University', 'university', 'government', 'University Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273009', 26.7550, 83.3780, 4.2, '0551-2281234', 'https://ddugu.ac.in'],
    ['Madhav Institute of Technology', 'university', 'private', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', 26.7470, 83.3870, 4.0, '0551-2581111', ''],
    
    // Gorakhpur - Markets
    ['Golghar Market', 'market', 'wholesale', 'Golghar, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7620, 83.3720, 4.1, '0551-2337777', ''],
    ['Nauka Vihar Market', 'market', 'retail', 'Nauka Vihar, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273008', 26.7500, 83.3900, 3.8, '0551-2788888', ''],
    
    // Gorakhpur - Parks/Recreation
    ['Ramgarh Tal Lake', 'park', 'lake', 'Ramgarh Tal, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273008', 26.7400, 83.3950, 4.5, '', ''],
    ['Nehru Park', 'park', 'city_park', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7600, 83.3710, 4.0, '0551-2338888', ''],
    ['Vinod Van Zoo', 'park', 'zoo', 'Kusmhi, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', 26.7200, 83.3800, 4.2, '0551-2789999', ''],
    
    // Gorakhpur - Banks/Financial
    ['SBI Main Branch', 'bank', 'sbi', 'Bank Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7600, 83.3700, 4.5, '0551-2333233', ''],
    ['PNB Main Branch', 'bank', 'pnb', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7600, 83.3710, 4.3, '0551-2331122', ''],
    ['HDFC Bank', 'bank', 'hdfc', 'Park Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 26.7580, 83.3740, 4.4, '0551-2332233', ''],
    
    // Lucknow - Major Landmarks
    ['Hazratganj Market', 'market', 'premium', 'Hazratganj, Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', 26.8467, 80.9462, 4.5, '0522-2233445', ''],
    ['Phoenix Palassio Mall', 'mall', 'premium', 'Gomti Nagar, Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', 26.8500, 80.9800, 4.6, '0522-2301234', ''],
    ['Lulu Mall', 'mall', 'premium', 'Amar Shaheed Path, Lucknow', 'Lucknow', 'Uttar Pradesh', '226002', 26.8400, 80.9700, 4.7, '0522-2305678', ''],
    ['Sahara Ganj Mall', 'mall', 'premium', 'Gomti Nagar, Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', 26.8550, 80.9850, 4.4, '0522-2309999', ''],
    ['King George Medical University', 'hospital', 'government', 'Chowk, Lucknow', 'Lucknow', 'Uttar Pradesh', '226003', 26.8500, 80.9300, 4.6, '0522-2255555', ''],
    ['Sanjay Gandhi PGI', 'hospital', 'government', 'Raebareli Road, Lucknow', 'Lucknow', 'Uttar Pradesh', '226014', 26.7800, 80.9200, 4.7, '0522-2499999', ''],
    ['Medanta Hospital', 'hospital', 'private', 'Gomti Nagar, Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', 26.8550, 80.9900, 4.8, '0522-2308888', ''],
    ['Amity University', 'university', 'private', 'Gomti Nagar, Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', 26.8600, 80.9950, 4.5, '0522-2307777', ''],
    ['Babu Banarasi Das University', 'university', 'private', 'Faizabad Road, Lucknow', 'Lucknow', 'Uttar Pradesh', '226028', 26.9000, 81.0200, 4.3, '0522-2306666', ''],
    ['Charbagh Railway Station', 'railway_station', 'major', 'Charbagh, Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', 26.8350, 80.9250, 4.6, '0522-2233444', 'https://indianrail.gov.in'],
    ['Lucknow Metro - Hazratganj', 'metro_station', 'interchange', 'Hazratganj, Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', 26.8467, 80.9462, 4.5, '0522-2233555', ''],
    ['Lucknow Metro - Gomti Nagar', 'metro_station', 'terminal', 'Gomti Nagar, Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', 26.8550, 80.9900, 4.4, '0522-2233666', ''],
    ['Chaudhary Charan Singh Airport', 'airport', 'domestic', 'Amausi, Lucknow', 'Lucknow', 'Uttar Pradesh', '226009', 26.7600, 80.8800, 4.6, '0522-2433333', 'https://lucknowairport.aero'],
    ['Kaiserbagh Bus Stand', 'bus_stand', 'major', 'Kaiserbagh, Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', 26.8400, 80.9400, 4.4, '0522-2233777', ''],
    ['Alambagh Bus Stand', 'bus_stand', 'major', 'Alambagh, Lucknow', 'Lucknow', 'Uttar Pradesh', '226005', 26.8200, 80.9300, 4.3, '0522-2233888', ''],
];

$stmt = $pdo->prepare("
    INSERT INTO landmarks 
    (name, type, sub_type, address, city, state, pincode, latitude, longitude, rating, contact, website)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        type = VALUES(type),
        sub_type = VALUES(sub_type),
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        rating = VALUES(rating),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($landmarks as $l) {
    try {
        $stmt->execute($l);
        $inserted++;
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "Inserted $inserted landmarks\n";

// Calculate distances for all colonies
$colonies = $pdo->query("SELECT id, name, latitude, longitude FROM colonies WHERE is_active = 1 AND latitude IS NOT NULL AND longitude IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
$landmarksDb = $pdo->query("SELECT id, name, type, latitude, longitude FROM landmarks WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

echo "Calculating distances for " . count($colonies) . " colonies to " . count($landmarksDb) . " landmarks...\n";

$distanceStmt = $pdo->prepare("
    INSERT INTO colony_landmark_distances 
    (colony_id, landmark_id, distance_km, driving_distance_km, driving_time_min, walking_time_min, transport_options)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        distance_km = VALUES(distance_km),
        driving_distance_km = VALUES(driving_distance_km),
        driving_time_min = VALUES(driving_time_min),
        walking_time_min = VALUES(walking_time_min),
        transport_options = VALUES(transport_options),
        calculated_at = CURRENT_TIMESTAMP
");

$calculated = 0;
foreach ($colonies as $colony) {
    $colonyLat = (float)$colony['latitude'];
    $colonyLng = (float)$colony['longitude'];
    
    if ($colonyLat === 0 && $colonyLng === 0) continue;
    
    foreach ($landmarksDb as $landmark) {
        $lmLat = (float)$landmark['latitude'];
        $lmLng = (float)$landmark['longitude'];
        
        if ($lmLat === 0 && $lmLng === 0) continue;
        
        // Haversine formula for straight-line distance
        $distanceKm = haversineDistance($colonyLat, $colonyLng, $lmLat, $lmLng);
        
        // Estimate driving distance (1.3x straight line for urban areas)
        $drivingDistance = round($distanceKm * 1.3, 2);
        $drivingTime = round($drivingDistance * 3); // ~20 km/h average
        $walkingTime = round($distanceKm * 15); // ~4 km/h walking
        
        // Transport options based on distance
        $transport = ['auto', 'cab'];
        if ($distanceKm < 5) $transport[] = 'walking';
        if ($distanceKm < 15) $transport[] = 'bus';
        if (strpos($landmark['type'], 'metro') !== false || strpos($landmark['type'], 'railway') !== false) {
            $transport[] = 'metro';
        }
        
        $distanceStmt->execute([
            $colony['id'],
            $landmark['id'],
            round($distanceKm, 3),
            $drivingDistance,
            $drivingTime,
            $walkingTime,
            json_encode($transport)
        ]);
        $calculated++;
    }
}

echo "Calculated $calculated colony-landmark distances\n";

function haversineDistance($lat1, $lon1, $lat2, $lon2): float {
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

echo "\n=== Distance to Landmarks calculation completed ===\n";?>