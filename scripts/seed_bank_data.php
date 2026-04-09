<?php
/**
 * Seed Bank Master Data
 * Major Indian banks with sample IFSC codes
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "Seeding Bank Master Data...\n\n";

// Check if already seeded
$count = $db->fetch("SELECT COUNT(*) as cnt FROM banks");
if ($count['cnt'] > 0) {
    echo "⚠️  Data already exists. Skipping...\n";
    echo "   Banks: {$count['cnt']}\n";
    exit;
}

// Major Indian Banks
$banks = [
    ['State Bank of India', 'SBIN'],
    ['HDFC Bank', 'HDFC'],
    ['ICICI Bank', 'ICIC'],
    ['Punjab National Bank', 'PUNB'],
    ['Bank of Baroda', 'BARB'],
    ['Canara Bank', 'CNRB'],
    ['Union Bank of India', 'UBIN'],
    ['Bank of India', 'BKID'],
    ['Axis Bank', 'UTIB'],
    ['Kotak Mahindra Bank', 'KKBK'],
    ['Indian Bank', 'IDIB'],
    ['Central Bank of India', 'CBIN'],
    ['Indian Overseas Bank', 'IOBA'],
    ['UCO Bank', 'UCBA'],
    ['Bank of Maharashtra', 'MAHB'],
    ['Yes Bank', 'YESB'],
    ['IDFC First Bank', 'IDFB'],
    ['IndusInd Bank', 'INDB'],
    ['Federal Bank', 'FDRL'],
    ['Bandhan Bank', 'BDBL'],
    ['Jio Payments Bank', 'JIOB'],
    ['Paytm Payments Bank', 'PTM2'],
    ['Airtel Payments Bank', 'AIRP'],
];

$bankBranches = [
    'SBIN' => [
        ['SBIN0001234', 'Gorakhpur Main Branch', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['SBIN0005678', 'Lucknow Main Branch', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001'],
        ['SBIN0009012', 'Varanasi Main Branch', 'Varanasi', 'Varanasi', 'Uttar Pradesh', '221001'],
        ['SBIN0003456', 'Delhi Main Branch', 'New Delhi', 'Central Delhi', 'Delhi', '110001'],
        ['SBIN0007890', 'Mumbai Main Branch', 'Mumbai', 'Mumbai', 'Maharashtra', '400001'],
        ['SBIN0012345', 'Kushinagar Branch', 'Kushinagar', 'Kushinagar', 'Uttar Pradesh', '274302'],
    ],
    'HDFC' => [
        ['HDFC0000001', 'Gorakhpur Branch', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['HDFC0000002', 'Lucknow Hazratganj', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001'],
        ['HDFC0000003', 'Varanasi Branch', 'Varanasi', 'Varanasi', 'Uttar Pradesh', '221001'],
        ['HDFC0000004', 'Delhi Nehru Place', 'New Delhi', 'South Delhi', 'Delhi', '110019'],
        ['HDFC0000005', 'Mumbai Fort', 'Mumbai', 'Mumbai', 'Maharashtra', '400001'],
        ['HDFC0000006', 'Pune FC Road', 'Pune', 'Pune', 'Maharashtra', '411004'],
    ],
    'ICIC' => [
        ['ICIC0000001', 'Gorakhpur Branch', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['ICIC0000002', 'Lucknow Alambagh', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226005'],
        ['ICIC0000003', 'Varanasi Branch', 'Varanasi', 'Varanasi', 'Uttar Pradesh', '221001'],
        ['ICIC0000004', 'Delhi Connaught Place', 'New Delhi', 'Central Delhi', 'Delhi', '110001'],
        ['ICIC0000005', 'Mumbai Nariman Point', 'Mumbai', 'Mumbai', 'Maharashtra', '400021'],
    ],
    'PUNB' => [
        ['PUNB0001000', 'Gorakhpur Main', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['PUNB0002000', 'Lucknow Main', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001'],
        ['PUNB0003000', 'Varanasi Main', 'Varanasi', 'Varanasi', 'Uttar Pradesh', '221001'],
    ],
    'BARB' => [
        ['BARB0000001', 'Gorakhpur Main', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['BARB0000002', 'Lucknow Main', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001'],
        ['BARB0000003', 'Varanasi Main', 'Varanasi', 'Varanasi', 'Uttar Pradesh', '221001'],
        ['BARB0000004', 'Delhi Main', 'New Delhi', 'Central Delhi', 'Delhi', '110001'],
        ['BARB0000005', 'Mumbai Main', 'Mumbai', 'Mumbai', 'Maharashtra', '400001'],
    ],
    'UTIB' => [
        ['UTIB0000001', 'Gorakhpur Branch', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['UTIB0000002', 'Lucknow Branch', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001'],
        ['UTIB0000003', 'Delhi Saket', 'New Delhi', 'South Delhi', 'Delhi', '110017'],
    ],
    'CNRB' => [
        ['CNRB0000001', 'Gorakhpur Branch', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001'],
        ['CNRB0000002', 'Lucknow Branch', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001'],
    ],
    'UPI' => [
        ['UPIPAY0001', 'PhonePe/UPI', 'Online', 'Online', 'Virtual', '000000'],
        ['UPIPAY0002', 'Google Pay/Bhim', 'Online', 'Online', 'Virtual', '000000'],
        ['UPIPAY0003', 'Paytm/UPI', 'Online', 'Online', 'Virtual', '000000'],
        ['UPIPAY0004', 'Amazon Pay/UPI', 'Online', 'Online', 'Virtual', '000000'],
    ],
];

$totalBranches = 0;
foreach ($banks as $bank) {
    $db->execute("INSERT INTO banks (name, short_name) VALUES (?, ?)", $bank);
    $bankId = $db->lastInsertId();
    
    // Add branches if we have them
    $shortName = $bank[1];
    if (isset($bankBranches[$shortName])) {
        foreach ($bankBranches[$shortName] as $branch) {
            $db->execute(
                "INSERT INTO bank_branches (bank_id, ifsc, branch, city, district, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$bankId, $branch[0], $branch[1], $branch[2], $branch[3], $branch[4], $branch[5]]
            );
            $totalBranches++;
        }
    }
    
    echo "✅ Added Bank: {$bank[0]} ({$bank[1]})\n";
}

echo "\n✅ Seeded " . count($banks) . " banks with $totalBranches branches!\n";
