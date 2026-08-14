<?php

/**
 * Fix District Data Bug
 * 
 * Issue: Kerala cities (Kochi, Thiruvananthapuram) are assigned to Uttar Pradesh
 * Fix: 
 * 1. Add Kerala state to states table
 * 2. Update Kochi and Thiruvananthapuram to belong to Kerala
 * 3. Add actual Uttar Pradesh districts
 */

require __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== Starting District Data Fix ===\n\n";

// Step 1: Add Kerala state if it doesn't exist
echo "Step 1: Checking for Kerala state...\n";
$keralaState = $db->fetch('SELECT id, name FROM states WHERE name = ?', ['Kerala']);
if (!$keralaState) {
    echo "Kerala state not found. Adding it...\n";
    $db->execute('INSERT INTO states (name, code, country_id, is_active) VALUES (?, ?, ?, ?)', [
        'Kerala',
        'KL',
        1, // India
        1
    ]);
    $keralaState = $db->fetch('SELECT id, name FROM states WHERE name = ?', ['Kerala']);
    echo "Kerala state added with ID: " . $keralaState['id'] . "\n";
} else {
    echo "Kerala state already exists with ID: " . $keralaState['id'] . "\n";
}

// Step 2: Update Kochi and Thiruvananthapuram to belong to Kerala
echo "\nStep 2: Updating Kochi and Thiruvananthapuram to Kerala...\n";
$db->execute('UPDATE districts SET state_id = ? WHERE name IN (?, ?)', [
    $keralaState['id'],
    'Kochi',
    'Thiruvananthapuram'
]);
echo "Updated districts to Kerala\n";

// Step 3: Add actual Uttar Pradesh districts
echo "\nStep 3: Adding Uttar Pradesh districts...\n";
$upState = $db->fetch('SELECT id, name FROM states WHERE name = ?', ['Uttar Pradesh']);
$upDistricts = [
    'Agra',
    'Aligarh',
    'Allahabad',
    'Ambedkar Nagar',
    'Amethi',
    'Amroha',
    'Auraiya',
    'Azamgarh',
    'Baghpat',
    'Bahraich',
    'Ballia',
    'Balrampur',
    'Banda',
    'Barabanki',
    'Bareilly',
    'Basti',
    'Bhadohi',
    'Bijnor',
    'Budaun',
    'Bulandshahr',
    'Chandauli',
    'Chitrakoot',
    'Deoria',
    'Etah',
    'Etawah',
    'Faizabad',
    'Farrukhabad',
    'Fatehpur',
    'Firozabad',
    'Gautam Buddha Nagar',
    'Ghaziabad',
    'Ghazipur',
    'Gonda',
    'Gorakhpur',
    'Hamirpur',
    'Hapur',
    'Hardoi',
    'Hathras',
    'Jalaun',
    'Jaunpur',
    'Jhansi',
    'Kannauj',
    'Kanpur Dehat',
    'Kanpur Nagar',
    'Kanshi Ram Nagar',
    'Kaushambi',
    'Kushinagar',
    'Lakhimpur Kheri',
    'Lalitpur',
    'Lucknow',
    'Maharajganj',
    'Mahoba',
    'Mainpuri',
    'Mathura',
    'Mau',
    'Meerut',
    'Mirzapur',
    'Moradabad',
    'Muzaffarnagar',
    'Pilibhit',
    'Pratapgarh',
    'Rae Bareli',
    'Rampur',
    'Saharanpur',
    'Sambhal',
    'Sant Kabir Nagar',
    'Sant Ravidas Nagar',
    'Shahjahanpur',
    'Shamli',
    'Shravasti',
    'Siddharthnagar',
    'Sitapur',
    'Sonbhadra',
    'Sultanpur',
    'Unnao',
    'Varanasi'
];

$addedCount = 0;
foreach ($upDistricts as $districtName) {
    // Check if district already exists
    $existing = $db->fetch('SELECT id, name, state_id FROM districts WHERE name = ?', [$districtName]);
    if ($existing) {
        // Update if it belongs to wrong state
        if ($existing['state_id'] != $upState['id']) {
            $db->execute('UPDATE districts SET state_id = ? WHERE id = ?', [$upState['id'], $existing['id']]);
            echo "Updated: $districtName (moved to Uttar Pradesh)\n";
        } else {
            echo "Skipped: $districtName (already in Uttar Pradesh)\n";
        }
    } else {
        // Add new district
        $db->execute('INSERT INTO districts (name, state_id, is_active) VALUES (?, ?, ?)', [
            $districtName,
            $upState['id'],
            1
        ]);
        $addedCount++;
        echo "Added: $districtName\n";
    }
}

echo "\n=== Verification ===\n";
echo "Uttar Pradesh districts count: ";
$upCount = $db->fetch('SELECT COUNT(*) as count FROM districts WHERE state_id = ?', [$upState['id']]);
echo $upCount['count'] . "\n";

echo "Kerala districts count: ";
$klCount = $db->fetch('SELECT COUNT(*) as count FROM districts WHERE state_id = ?', [$keralaState['id']]);
echo $klCount['count'] . "\n";

echo "\n=== Fix Complete ===\n";
echo "Added $addedCount new Uttar Pradesh districts\n";?>