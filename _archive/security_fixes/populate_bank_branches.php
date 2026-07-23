<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Insert major bank branches in UP area
$branches = [
    // SBI Gorakhpur
    ['SBIN0000144', 'State Bank of India', 'Gorakhpur Main Branch', 'Bank Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2333233', '273002002', 'SBININBB'],
    ['SBIN0000145', 'State Bank of India', 'Gorakhpur Medical College', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', '0551-2581234', '273002003', 'SBININBB'],
    ['SBIN0000146', 'State Bank of India', 'Gorakhpur University', 'University Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273009', '0551-2280123', '273002004', 'SBININBB'],
    ['SBIN0000147', 'State Bank of India', 'Gorakhpur Cantt', 'Cantt Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2334567', '273002005', 'SBININBB'],
    ['SBIN0000148', 'State Bank of India', 'Gorakhpur GIDA', 'GIDA Industrial Area, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2681234', '273002006', 'SBININBB'],
    ['SBIN0000149', 'State Bank of India', 'Gorakhpur Sahjanwa', 'Sahjanwa, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2685678', '273002007', 'SBININBB'],
    ['SBIN0000150', 'State Bank of India', 'Gorakhpur Pipraich', 'Pipraich, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273152', '0551-2781234', '273002008', 'SBININBB'],
    
    // SBI Lucknow
    ['SBIN0000137', 'State Bank of India', 'Lucknow Main Branch', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2233445', '226002002', 'SBININBB'],
    ['SBIN0000138', 'State Bank of India', 'Lucknow Gomti Nagar', 'Vibhuti Khand, Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2301234', '226002003', 'SBININBB'],
    ['SBIN0000139', 'State Bank of India', 'Lucknow Aliganj', 'Kapoorthala Complex, Aliganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226024', '0522-2321234', '226002004', 'SBININBB'],
    ['SBIN0000140', 'State Bank of India', 'Lucknow Indira Nagar', 'Indira Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226016', '0522-2341234', '226002005', 'SBININBB'],
    ['SBIN0000141', 'State Bank of India', 'Lucknow Alambagh', 'Alambagh, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226005', '0522-2351234', '226002006', 'SBININBB'],
    ['SBIN0000142', 'State Bank of India', 'Lucknow Mahanagar', 'Mahanagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226006', '0522-2361234', '226002007', 'SBININBB'],
    ['SBIN0000143', 'State Bank of India', 'Lucknow Nirala Nagar', 'Nirala Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226020', '0522-2371234', '226002008', 'SBININBB'],
    
    // PNB Gorakhpur
    ['PUNB0000123', 'Punjab National Bank', 'Gorakhpur Main', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2331122', '273024002', 'PUNBINBB'],
    ['PUNB0000124', 'Punjab National Bank', 'Gorakhpur University', 'University Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273009', '0551-2281122', '273024003', 'PUNBINBB'],
    ['PUNB0000125', 'Punjab National Bank', 'Gorakhpur Gida', 'GIDA Area, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2681122', '273024004', 'PUNBINBB'],
    
    // PNB Lucknow
    ['PUNB0000126', 'Punjab National Bank', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2231122', '226024002', 'PUNBINBB'],
    ['PUNB0000127', 'Punjab National Bank', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2301122', '226024003', 'PUNBINBB'],
    ['PUNB0000128', 'Punjab National Bank', 'Lucknow Aliganj', 'Aliganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226024', '0522-2321122', '226024004', 'PUNBINBB'],
    
    // HDFC Gorakhpur
    ['HDFC0000456', 'HDFC Bank', 'Gorakhpur Main', 'Park Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2332233', '273240002', 'HDFCINBB'],
    ['HDFC0000457', 'HDFC Bank', 'Gorakhpur Medical College', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', '0551-2582233', '273240003', 'HDFCINBB'],
    ['HDFC0000458', 'HDFC Bank', 'Gorakhpur Gida', 'GIDA, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2682233', '273240004', 'HDFCINBB'],
    
    // HDFC Lucknow
    ['HDFC0000459', 'HDFC Bank', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2232233', '226240002', 'HDFCINBB'],
    ['HDFC0000460', 'HDFC Bank', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2302233', '226240003', 'HDFCINBB'],
    ['HDFC0000461', 'HDFC Bank', 'Lucknow Aliganj', 'Aliganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226024', '0522-2322233', '226240004', 'HDFCINBB'],
    
    // ICICI Gorakhpur
    ['ICIC0000789', 'ICICI Bank', 'Gorakhpur Main', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2333344', '273229002', 'ICICINBB'],
    ['ICIC0000790', 'ICICI Bank', 'Gorakhpur Gida', 'GIDA, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2683344', '273229003', 'ICICINBB'],
    
    // ICICI Lucknow
    ['ICIC0000791', 'ICICI Bank', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2233344', '226229002', 'ICICINBB'],
    ['ICIC0000792', 'ICICI Bank', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2303344', '226229003', 'ICICINBB'],
    ['ICIC0000793', 'ICICI Bank', 'Lucknow Aliganj', 'Aliganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226024', '0522-2323344', '226229004', 'ICICINBB'],
    
    // Bank of Baroda Gorakhpur
    ['BARB0GORAKH', 'Bank of Baroda', 'Gorakhpur Main', 'Bank Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2334455', '273012002', 'BARBINBB'],
    ['BARB0GORAKH2', 'Bank of Baroda', 'Gorakhpur University', 'University Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273009', '0551-2284455', '273012003', 'BARBINBB'],
    
    // Bank of Baroda Lucknow
    ['BARB0LUCKNO', 'Bank of Baroda', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2234455', '226012002', 'BARBINBB'],
    ['BARB0LUCKNO2', 'Bank of Baroda', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2304455', '226012003', 'BARBINBB'],
    
    // Canara Bank Gorakhpur
    ['CNRB0000123', 'Canara Bank', 'Gorakhpur Main', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2335566', '273015002', 'CNRBINBB'],
    ['CNRB0000124', 'Canara Bank', 'Gorakhpur Medical College', 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273013', '0551-2585566', '273015003', 'CNRBINBB'],
    
    // Canara Bank Lucknow
    ['CNRB0000125', 'Canara Bank', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2235566', '226015002', 'CNRBINBB'],
    ['CNRB0000126', 'Canara Bank', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2305566', '226015003', 'CNRBINBB'],
    
    // Union Bank Gorakhpur
    ['UBIN0000456', 'Union Bank of India', 'Gorakhpur Main', 'Park Road, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2336677', '273026002', 'UBININBB'],
    ['UBIN0000457', 'Union Bank of India', 'Gorakhpur Gida', 'GIDA, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2686677', '273026003', 'UBININBB'],
    
    // Union Bank Lucknow
    ['UBIN0000458', 'Union Bank of India', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2236677', '226026002', 'UBININBB'],
    ['UBIN0000459', 'Union Bank of India', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2306677', '226026003', 'UBININBB'],
    
    // Axis Bank Gorakhpur
    ['UTIB0000123', 'Axis Bank', 'Gorakhpur Main', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', '0551-2337788', '273211002', 'AXISINBB'],
    ['UTIB0000124', 'Axis Bank', 'Gorakhpur Gida', 'GIDA, Gorakhpur', 'Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273209', '0551-2687788', '273211003', 'AXISINBB'],
    
    // Axis Bank Lucknow
    ['UTIB0000125', 'Axis Bank', 'Lucknow Hazratganj', 'Hazratganj, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226001', '0522-2237788', '226211002', 'AXISINBB'],
    ['UTIB0000126', 'Axis Bank', 'Lucknow Gomti Nagar', 'Gomti Nagar, Lucknow', 'Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', '0522-2307788', '226211003', 'AXISINBB'],
];

$stmt = $pdo->prepare("
    INSERT INTO bank_branches (ifsc_code, bank_name, branch_name, address, city, district, state, pincode, contact, micr_code, swift_code, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE 
        bank_name = VALUES(bank_name),
        branch_name = VALUES(branch_name),
        address = VALUES(address),
        city = VALUES(city),
        district = VALUES(district),
        state = VALUES(state),
        pincode = VALUES(pincode),
        contact = VALUES(contact),
        micr_code = VALUES(micr_code),
        swift_code = VALUES(swift_code),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($branches as $branch) {
    try {
        $stmt->execute($branch);
        $inserted++;
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "Inserted/Updated $inserted bank branches\n";

// Verify
$count = $pdo->query("SELECT COUNT(*) FROM bank_branches")->fetchColumn();
echo "Total branches in table: $count\n";