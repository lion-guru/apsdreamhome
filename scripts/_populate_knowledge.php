<?php
/**
 * Populate ai_knowledge_base with live data from properties, plots, colonies
 * Run once: php scripts/_populate_knowledge.php
 */
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "=== Populating Knowledge Base ===\n\n";

// 1. Add colony-specific knowledge
$colonies = $db->fetchAll("SELECT id, name, is_active FROM colonies");
echo "Colonies found: " . count($colonies) . "\n";

foreach ($colonies as $colony) {
    $plotStats = $db->fetch(
        "SELECT COUNT(*) as total, SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as available,
                MIN(area_sqft) as min_area, MAX(area_sqft) as max_area
         FROM plots WHERE colony_id = ?",
        [$colony['id']]
    );

    $name = $colony['name'];
    $total = $plotStats['total'] ?? 0;
    $available = $plotStats['available'] ?? 0;
    $minArea = $plotStats['min_area'] ?? 0;
    $maxArea = $plotStats['max_area'] ?? 0;

    // Insert Q&A pairs
    $entries = [
        ['projects', "$name mein kitne plot hain", "{$name} mein total $total plots hain, jismein $available available hain. Area: {$minArea}-{$maxArea} sqft."],
        ['projects', "$name kaha hai", "{$name} Gorakhpur, Uttar Pradesh mein hai. Premium colony with all modern amenities."],
        ['pricing', "$name ka price kya hai", "{$name} mein plots ₹5.5 Lakh se ₹15 Lakh tak available hain. Size: {$minArea}-{$maxArea} sqft."],
        ['amenities', "$name mein kya kya hai", "{$name} mein hain: 24/7 security, paved roads, water supply, electricity, parks, green spaces."],
    ];

    foreach ($entries as $e) {
        try {
            $db->execute(
                "INSERT INTO ai_knowledge_base (category, question_pattern, answer, usage_count, effectiveness_score, created_at, updated_at)
                 VALUES (?, ?, ?, 0, 0.00, NOW(), NOW())",
                $e
            );
            echo "  Added: {$e[1]}\n";
        } catch (\Exception $ex) {
            // Duplicate - skip
        }
    }
}

// 2. Add property-type knowledge
$propTypes = $db->fetchAll("SELECT type, COUNT(*) as cnt, MIN(price) as min_price, MAX(price) as max_price FROM properties WHERE status='active' GROUP BY type");
foreach ($propTypes as $pt) {
    $minP = number_format($pt['min_price'] / 100000, 1);
    $maxP = number_format($pt['max_price'] / 100000, 1);
    $db->execute(
        "INSERT INTO ai_knowledge_base (category, question_pattern, answer, usage_count, effectiveness_score, created_at, updated_at)
         VALUES (?, ?, ?, 0, 0.00, NOW(), NOW())",
        ['pricing', "{$pt['type']} ka price kitna hai", ucfirst($pt['type']) . " available hain ₹{$minP} Lakh se ₹{$maxP} Lakh tak. Total {$pt['cnt']} available."]
    );
    echo "  Added: {$pt['type']} pricing\n";
}

// 3. Add service knowledge
$services = [
    ['services', 'home loan kaise milega', 'Home loan available at 8.5% interest rate. Partners: SBI, HDFC, ICICI, Bank of Baroda. Apply at /financial-services or call +91 92771 21112.'],
    ['services', 'commission kaise milega', 'APS Dream Home mein commission milta hai: Direct Sale 5-20%, Level 1 Referral 7%, Level 2 Referral 5%. Associate banein aur earning shuru karein!'],
    ['services', 'site visit kaise karein', 'Site visit ke liye call karein +91 92771 21112 ya WhatsApp karein. Mon-Sat 9AM-6PM available. Free transportation bhi milta hai!'],
    ['contact', 'phone number kya hai', 'Call us: +91 92771 21112\nWhatsApp: +91 92771 21112\nEmail: info@apsdreamhome.com\nOffice: Raghunath Nagri, Gorakhpur'],
    ['projects', 'aps dream home kya hai', 'APS Dream Home premium real estate company hai Gorakhpur, UP mein. 4 colonies: Suryoday, Raghunath, Braj Radha, Budh Bihar. Plots from ₹5.5L.'],
];

foreach ($services as $s) {
    try {
        $db->execute(
            "INSERT INTO ai_knowledge_base (category, question_pattern, answer, usage_count, effectiveness_score, created_at, updated_at)
             VALUES (?, ?, ?, 0, 0.00, NOW(), NOW())",
            $s
        );
        echo "  Added: {$s[1]}\n";
    } catch (\Exception $ex) {}
}

echo "\n=== Done ===\n";
$total = $db->fetch("SELECT COUNT(*) as cnt FROM ai_knowledge_base")['cnt'];
echo "Total knowledge base entries: $total\n";
