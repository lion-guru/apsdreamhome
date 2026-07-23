<?php
define('APP_ROOT', dirname(__DIR__));
define('APS_ROOT', dirname(__DIR__));
require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

$userIds = [];
$rows = $db->fetchAll("SELECT id FROM users LIMIT 200");
foreach ($rows as $r) { $userIds[] = (int)$r['id']; }

$firstNames = ['Amit','Rahul','Priya','Neha','Vikram','Sunita','Rajesh','Pooja','Suresh','Anita','Manoj','Kavita','Sanjay','Deepa','Arjun','Meera','Rohit','Sapna','Vijay','Rekha','Anil','Sunil','Pallavi','Nitin','Swati','Gaurav','Ritu','Karan','Shweta','Abhishek'];
$lastNames = ['Sharma','Verma','Gupta','Singh','Kumar','Patel','Yadav','Tiwari','Mishra','Chauhan','Reddy','Nair','Iyer','Joshi','Bansal','Aggarwal','Mehta','Trivedi','Pandey','Rathore'];
$cities = ['Delhi','Noida','Ghaziabad','Gurgaon','Greater Noida','Lucknow','Kanpur','Varanasi','Prayagraj','Agra','Meerut','Bareilly','Moradabad','Gorakhpur','Allahabad','Indore','Bhopal','Jaipur','Ludhiana','Chandigarh'];
$states = ['Uttar Pradesh','Delhi','Haryana','Madhya Pradesh','Rajasthan','Punjab'];
$sources = ['website','referral','facebook','instagram','whatsapp','google','office_walkin','telecaller','agent','campaign'];
$statuses = ['new','contacted','qualified','proposal','negotiation','nurture','closed_won','closed_lost'];
$interests = ['Residential Plot','Apartment 2BHK','Apartment 3BHK','Commercial Space','Villa','Land','Studio Apartment','Penthouse'];
$propertyTypes = ['plot','apartment','commercial','villa','land'];

$count = 0;
$target = 400;
$err = null;
for ($i = 1; $i <= $target; $i++) {
    $fn = $firstNames[array_rand($firstNames)];
    $ln = $lastNames[array_rand($lastNames)];
    $name = $fn . ' ' . $ln;
    $phone = '9' . rand(100000000, 999999999);
    $email = strtolower($fn) . $i . '@example.com';
    $city = $cities[array_rand($cities)];
    $state = $states[array_rand($states)];
    $status = $statuses[array_rand($statuses)];
    $source = $sources[array_rand($sources)];
    $interest = $interests[array_rand($interests)];
    $budget = rand(15, 250) * 100000; // 15L - 2.5Cr
    $score = rand(0, 100);
    $priority = $score >= 70 ? 'high' : ($score >= 40 ? 'medium' : 'low');
    $category = $score >= 70 ? 'hot' : ($score >= 40 ? 'warm' : 'cold');
    $created = date('Y-m-d H:i:s', strtotime('-' . rand(0, 400) . ' days'));
    $leadNumber = 'LD' . date('Ymd', strtotime($created)) . str_pad($i, 5, '0');

    try {
        $db->query(
            "INSERT INTO leads (lead_number, name, email, phone, city, state, pincode, source, status,
                assigned_to, created_by, property_interest, budget, budget_range, lead_score, conversion_probability,
                priority, lead_category, message, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $leadNumber, $name, $email, $phone, $city, $state, rand(110001, 859999), $source, $status,
                $userIds[array_rand($userIds)], 1, $interest, $budget, ($budget/100000).'L', $score, round($score,2),
                $priority, $category, 'Interested in ' . $interest . ' in ' . $city . '. Please share details.',
                $created, $created
            ]
        );
        $count++;
    } catch (\Throwable $e) {
        $err = $e->getMessage();
        echo "ROW $i FAILED: $err\n";
        break;
    }
}
echo "Seeded $count leads.\n";
