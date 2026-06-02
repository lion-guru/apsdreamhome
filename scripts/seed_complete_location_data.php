<?php
/**
 * Complete India Location Seed Script
 * Seeds all states, districts, and major cities
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "Seeding Complete India Location Data...\n\n";

// Get India country ID
$india = $db->fetch("SELECT id FROM countries WHERE name = 'India'");
$indiaId = $india ? $india['id'] : 1;

echo "India ID: $indiaId\n\n";

// Check if we already have states
$stateCount = $db->fetch("SELECT COUNT(*) as cnt FROM states");
if ($stateCount['cnt'] > 5) {
    echo "⚠️  Already have {$stateCount['cnt']} states. Skipping...\n";
    exit;
}

// Complete India data
$indiaData = [
    // Uttar Pradesh
    ['Uttar Pradesh', 'UP', [
        'Gorakhpur' => ['Gorakhpur', 'Khalilabad', 'Bansi', 'Domariaganj', 'Sanjitpur', 'Bhathaha', 'Khorabar', 'Pipraich'],
        'Lucknow' => ['Lucknow', 'Unnao', 'Rae Bareli', 'Sitapur', 'Hardoi', 'Lakhimpur', 'Barabanki'],
        'Varanasi' => ['Varanasi', 'Mirzapur', 'Ghazipur', 'Jaunpur', 'Azamgarh', 'Ballia', 'Chandauli'],
        'Kushinagar' => ['Kushinagar', 'Padrauna', 'Hata', 'Gopalganj', 'Siwan', 'Maharajganj'],
        'Ayodhya' => ['Ayodhya', 'Faizabad', 'Ambedkar Nagar', 'Sultanpur', 'Basti', 'Siddharthnagar'],
        'Prayagraj' => ['Prayagraj', 'Fatehpur', 'Kaushambi', 'Pratapgarh', 'Firozpur', 'Hamirpur'],
        'Agra' => ['Agra', 'Firozabad', 'Mathura', 'Mainpuri', 'Etawah', 'Budaun', 'Aligarh'],
        'Meerut' => ['Meerut', 'Ghaziabad', 'Bulandshahr', 'Hapur', 'Modinagar', 'Baghpat', 'Gautam Buddha Nagar'],
        'Bareilly' => ['Bareilly', 'Pilibhit', 'Shahjahanpur', 'Budaun', 'Moradabad', 'Rampur', 'Sambhal'],
        'Kanpur' => ['Kanpur', 'Kannauj', 'Etawah', 'Auraiya', 'Hamirpur', 'Mahoba', 'Jalaun'],
        'Jhansi' => ['Jhansi', 'Jalaun', 'Lalitpur', 'Mahoba', 'Hamirpur', 'Banda'],
        'Aligarh' => ['Aligarh', 'Hathras', 'Kasganj', 'Etah', 'Bulandshahr'],
        'Saharanpur' => ['Saharanpur', 'Muzaffarnagar', 'Bijnor', 'Deoband', 'Nakur'],
    ]],
    // Bihar
    ['Bihar', 'BR', [
        'Patna' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Purnia', 'Biharsharif', 'Dhanbad'],
        'Gopalganj' => ['Gopalganj', 'Siwan', 'Chapra', 'Saran', 'Buxar', 'Bhabhua'],
        'Muzaffarpur' => ['Muzaffarpur', 'Samastipur', 'Begusarai', 'Hajipur', 'Vaishali', 'Sheohar'],
        'Gaya' => ['Gaya', 'Nalanda', 'Rohtas', 'Aurangabad', 'Jehanabad', 'Arwal'],
        'Bhagalpur' => ['Bhagalpur', 'Banka', 'Munger', 'Lakhisarai', 'Khagaria', 'Sheikhpura'],
    ]],
    // Madhya Pradesh
    ['Madhya Pradesh', 'MP', [
        'Bhopal' => ['Bhopal', 'Vidisha', 'Raisen', 'Sehore', 'Rajgarh', 'Harda'],
        'Indore' => ['Indore', 'Ujjain', 'Dewas', 'Ratlam', 'Shajapur', 'Jhabua', 'Alirajpur'],
        'Jabalpur' => ['Jabalpur', 'Katni', 'Sagar', 'Damoh', 'Chhindwara', 'Seoni', 'Narsinghpur'],
        'Gwalior' => ['Gwalior', 'Morena', 'Bhind', 'Sheopur', 'Datia', 'Shivpuri'],
        'Ujjain' => ['Ujjain', 'Mandsaur', 'Neemuch', 'Ratlam', 'Shajapur', 'Dewas'],
    ]],
    // Rajasthan
    ['Rajasthan', 'RJ', [
        'Jaipur' => ['Jaipur', 'Ajmer', 'Tonk', 'Sawai Madhopur', 'Dausa', 'Jhunjhunu', 'Sikar', 'Churu'],
        'Jodhpur' => ['Jodhpur', 'Pali', 'Jalore', 'Sirohi', 'Barmer', 'Bikaner', 'Nagaur'],
        'Udaipur' => ['Udaipur', 'Chittorgarh', 'Rajsamand', 'Banswara', 'Dungarpur', ' Pratapgarh'],
        'Kota' => ['Kota', 'Bundi', 'Jhalawar', 'Baran', 'Karauli', 'Dholpur'],
        'Ajmer' => ['Ajmer', 'Tonk', 'Kekri', 'Nasirabad', 'Beawar', 'Kishangarh'],
    ]],
    // Maharashtra
    ['Maharashtra', 'MH', [
        'Mumbai' => ['Mumbai', 'Thane', 'Navi Mumbai', 'Panvel', 'Kalyan', 'Dombivli', 'Mira Bhayandar'],
        'Pune' => ['Pune', 'Pimpri-Chinchwad', 'Satara', 'Solapur', 'Kolhapur', 'Sangli', 'Ratnagiri'],
        'Nagpur' => ['Nagpur', 'Amravati', 'Wardha', 'Chandrapur', 'Yavatmal', 'Gadchiroli', 'Bhandara'],
        'Nashik' => ['Nashik', 'Ahmednagar', 'Dhule', 'Jalgaon', 'Nandurbar', 'Malegaon'],
        'Aurangabad' => ['Aurangabad', 'Jalna', 'Parbhani', 'Hingoli', 'Nanded', 'Latur'],
    ]],
    // Delhi
    ['Delhi', 'DL', [
        'New Delhi' => ['New Delhi', 'Central Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Shahdara'],
    ]],
    // Uttarakhand
    ['Uttarakhand', 'UK', [
        'Dehradun' => ['Dehradun', 'Haridwar', 'Rishikesh', 'Tehri', 'Uttarkashi', 'Chamoli', 'Rudraprayag'],
        'Haldwani' => ['Haldwani', 'Nainital', 'Almora', 'Bageshwar', 'Champawat', 'Pithoragarh'],
        'Roorkee' => ['Roorkee', 'Haridwar', 'Dehradun', 'Mussoorie', 'Kashipur', 'Rudrapur'],
    ]],
    // Haryana
    ['Haryana', 'HR', [
        'Gurugram' => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala', 'Karnal', 'Sonipat', 'Rewari'],
        'Hisar' => ['Hisar', 'Rohtak', 'Jhajjar', 'Sonipat', 'Jind', 'Hisar', 'Fatehabad'],
        'Karnal' => ['Karnal', 'Panipat', 'Yamunanagar', 'Kurukshetra', 'Kaithal'],
    ]],
    // Punjab
    ['Punjab', 'PB', [
        'Ludhiana' => ['Ludhiana', 'Jalandhar', 'Amritsar', 'Patiala', 'Bathinda', 'Hoshiarpur', 'Moga'],
        'Mohali' => ['Mohali', 'Chandigarh', 'Fatehgarh Sahib', 'Rupnagar', 'Sangrur'],
    ]],
    // Gujarat
    ['Gujarat', 'GJ', [
        'Ahmedabad' => ['Ahmedabad', 'Gandhinagar', 'Mehsana', 'Patan', 'Banaskantha', 'Sabarkantha'],
        'Surat' => ['Surat', 'Navsari', 'Valsad', 'Tapi', 'Dang', 'Bharuch'],
        'Vadodara' => ['Vadodara', 'Anand', 'Kheda', 'Panchmahals', 'Chhota Udepur', 'Mahisagar'],
        'Rajkot' => ['Rajkot', 'Junagadh', 'Porbandar', 'Devbhumi Dwarka', 'Gir Somnath'],
        'Bhavnagar' => ['Bhavnagar', 'Amreli', 'Botad', 'Gariadhar', 'Palitana'],
    ]],
    // Jharkhand
    ['Jharkhand', 'JH', [
        'Ranchi' => ['Ranchi', 'Bokaro', 'Dhanbad', 'Jamshedpur', 'Hazaribagh', 'Deoghar', 'Dumka'],
        'Dhanbad' => ['Dhanbad', 'Bokaro', 'Chatra', 'Garhwa', 'Latehar', 'Palamu'],
    ]],
    // West Bengal
    ['West Bengal', 'WB', [
        'Kolkata' => ['Kolkata', 'Howrah', 'Hooghly', 'North 24 Parganas', 'South 24 Parganas', 'East Medinipur'],
        'Siliguri' => ['Siliguri', 'Darjeeling', 'Jalpaiguri', 'Cooch Behar', 'Alipurduar', 'Kalimpong'],
        'Durgapur' => ['Durgapur', 'Asansol', 'Bardhaman', 'Bankura', 'Purulia', 'Birbhum'],
    ]],
    // Karnataka
    ['Karnataka', 'KA', [
        'Bangalore' => ['Bangalore', 'Mysore', 'Mangalore', 'Hubli', 'Belgaum', 'Dharwad', 'Tumkur'],
        'Mysore' => ['Mysore', 'Chamrajnagar', 'Mandya', 'Hassan', 'Chikmagalur', 'Kodagu'],
    ]],
    // Tamil Nadu
    ['Tamil Nadu', 'TN', [
        'Chennai' => ['Chennai', 'Kanchipuram', 'Tiruvallur', 'Chengalpattu', 'Vellore', 'Kanyakumari'],
        'Coimbatore' => ['Coimbatore', 'Tiruppur', 'Erode', 'Salem', 'Namakkal', 'Dharmapuri'],
    ]],
    // Telangana
    ['Telangana', 'TS', [
        'Hyderabad' => ['Hyderabad', 'Secunderabad', 'Rangareddy', 'Medchal', 'Sangareddy', 'Mahbubnagar'],
        'Warangal' => ['Warangal', 'Karimnagar', 'Khammam', 'Nalgonda', 'Nizamabad', 'Adilabad'],
    ]],
    // Andhra Pradesh
    ['Andhra Pradesh', 'AP', [
        'Visakhapatnam' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Tirupati'],
        'Kakinada' => ['Kakinada', 'Rajahmundry', 'Amalapuram', 'Eluru', 'Machilipatnam', 'Kavali'],
    ]],
    // Kerala
    ['Kerala', 'KL', [
        'Thiruvananthapuram' => ['Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha', 'Kottayam'],
        'Kochi' => ['Kochi', 'Thrissur', 'Palakkad', 'Malappuram', 'Kozhikode', 'Kannur', 'Wayanad'],
    ]],
];

$totalStates = 0;
$totalDistricts = 0;
$totalCities = 0;

foreach ($indiaData as $stateData) {
    $stateName = $stateData[0];
    $stateCode = $stateData[1];
    $districts = $stateData[2];
    
    // Check if state exists
    $existing = $db->fetch("SELECT id FROM states WHERE name = ?", [$stateName]);
    
    if ($existing) {
        $stateId = $existing['id'];
        echo "⏭️  State exists: $stateName\n";
    } else {
        $db->execute("INSERT INTO states (country_id, name, code) VALUES (?, ?, ?)",
            [$indiaId, $stateName, $stateCode]);
        $stateId = $db->lastInsertId();
        $totalStates++;
        echo "✅ Added State: $stateName\n";
    }
    
    foreach ($districts as $districtName => $cities) {
        // Check if district exists
        $existingDist = $db->fetch("SELECT id FROM districts WHERE state_id = ? AND name = ?", [$stateId, $districtName]);
        
        if ($existingDist) {
            $districtId = $existingDist['id'];
        } else {
            $db->execute("INSERT INTO districts (state_id, name) VALUES (?, ?)",
                [$stateId, $districtName]);
            $districtId = $db->lastInsertId();
            $totalDistricts++;
        }
        
        foreach ($cities as $cityName) {
            // Check if city exists
            $existingCity = $db->fetch("SELECT id FROM cities WHERE district_id = ? AND name = ?", [$districtId, $cityName]);
            
            if (!$existingCity) {
                $type = 'city';
                if (stripos($cityName, 'nagar') !== false || stripos($cityName, 'abad') !== false) {
                    $type = 'town';
                }
                
                $db->execute("INSERT INTO cities (district_id, name, type) VALUES (?, ?, ?)",
                    [$districtId, $cityName, $type]);
                $totalCities++;
            }
        }
        
        echo "   ✅ District: $districtName (" . count($cities) . " cities)\n";
    }
    echo "\n";
}

echo "==========================================\n";
echo "✅ Completed!\n";
echo "   New States: $totalStates\n";
echo "   New Districts: $totalDistricts\n";
echo "   New Cities: $totalCities\n";
echo "==========================================\n";
