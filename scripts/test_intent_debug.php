<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\IntentDetector;
use App\Core\Database\Database;

$db = Database::getInstance();
$detector = new IntentDetector($db);

// Test with reflection to see what's matching
$message = 'Mujhe Gorakhpur mein 50 lakh ka plot chahiye';
$message = strtolower(trim($message));
$language = 'en';

echo "Message: $message\n";
echo "Language: $language\n\n";

// Check default patterns
$defaultIntents = [
    'buy_property' => [
        'en' => ['buy', 'purchase', 'want to buy', 'looking to buy', 'need a property', 'want a house', 'want flat', 'want plot', 'looking for'],
        'hi_lat' => ['khareedna', 'khareed', 'lena hai', 'ghar chahiye', 'plot chahiye', 'flat chahiye', 'lenge', 'chahiye mujhe', 'mujhe chahiye', 'mujhe plot', 'mujhe flat', 'mujhe ghar']
    ],
    'greeting' => [
        'en' => ['hello', 'hi', 'hey', 'namaste', 'good morning', 'good evening'],
        'hi_lat' => ['namaste', 'namaskar', 'hello', 'hi', 'hey', 'kaise ho', 'kya haal', 'kaise hain', 'kya bolte']
    ],
    'site_visit' => [
        'en' => ['site visit', 'visit property', 'see the property', 'schedule visit', 'book visit'],
        'hi_lat' => ['site visit', 'visit', 'dekhna hai', 'milne', 'aana chahta', 'book karo', 'schedule karo']
    ],
    'loan' => [
        'en' => ['loan', 'home loan', 'mortgage', 'finance', 'emi'],
        'hi_lat' => ['loan', 'home loan', 'karz', 'finance', 'emi', 'kist']
    ],
];

foreach ($defaultIntents as $intent => $langs) {
    $patterns = $langs[$language] ?? $langs['en'] ?? [];
    foreach ($patterns as $p) {
        if (stripos($message, strtolower($p)) !== false) {
            echo "DEFAULT MATCH: $intent -> '$p'\n";
        }
    }
    if ($language === 'en' && isset($langs['hi_lat'])) {
        foreach ($langs['hi_lat'] as $p) {
            if (stripos($message, strtolower($p)) !== false) {
                echo "HINGLISH MATCH: $intent -> '$p'\n";
            }
        }
    }
}

echo "\n--- DB Patterns ---\n";
// Check DB patterns
$stmt = $db->prepare("
    SELECT intent_name, pattern_text, pattern_type, weight
    FROM ai_intent_patterns
    WHERE is_active = 1 AND (language = ? OR language = 'en')
");
$stmt->execute([$language]);
$dbPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($dbPatterns as $p) {
    $pattern = strtolower($p['pattern_text']);
    $score = 0;
    if (stripos($message, $pattern) !== false) {
        $score = 0.7 * (float)$p['weight'];
        echo "DB MATCH: {$p['intent_name']} -> '{$p['pattern_text']}' (type: {$p['pattern_type']}, weight: {$p['weight']}, score: $score)\n";
    }
}