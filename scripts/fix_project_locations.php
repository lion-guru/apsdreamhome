<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');

echo "🔄 Updating AI Knowledge Base with Correct Project Locations...\n\n";

// Update Braj Radha answers to show Deoria instead of Lucknow
$updates = [
    [
        'pattern' => 'braj radha nagri|braj radha enclave kaha hai',
        'answer' => "🏗️ **Braj Radha Enclave** - Premium Deoria Project\n\n📍 **Location:** Deoria, Uttar Pradesh\n🏠 **Type:** Residential Plots\n📐 **Plot Sizes:** 1500 - 4000 sq ft\n💰 **Price:** ₹4.5 Lakh se shuru\n\n✅ Near Highway\n✅ Peaceful Location\n✅ Green Environment\n✅ Modern Amenities\n\n📞 **Visit ke liye: +91 92771 21112**"
    ],
    [
        'pattern' => 'plot kaha kaha hai',
        'answer' => "📍 **Available Locations:**\n• Gorakhpur - Suryoday Heights, Raghunath City Center\n• Deoria - Braj Radha Enclave\n• Kushinagar - Buddh Bihar Colony\n\n📞 **Site visit: +91 92771 21112**"
    ]
];

foreach ($updates as $update) {
    $stmt = $pdo->prepare("UPDATE ai_knowledge_base SET answer = ? WHERE question_pattern = ?");
    $stmt->execute([$update['answer'], $update['pattern']]);
    echo "✅ Updated: " . $update['pattern'] . "\n";
}

echo "\n🎉 Knowledge base updated with correct locations!\n";
echo "📍 Projects: Gorakhpur, Deoria, Kushinagar\n";
