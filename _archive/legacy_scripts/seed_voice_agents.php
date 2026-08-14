<?php

$db = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome', 3307);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "=== Seeding Voice AI Agents and Scripts ===\n\n";

$r = $db->query("SELECT COUNT(*) as cnt FROM ai_calling_agents");
$row = $r->fetch_assoc();
echo "Current agents: {$row['cnt']}\n";

// Insert agents
$agent_sql = "INSERT IGNORE INTO ai_calling_agents (agent_id, agent_name, languages, max_concurrent_calls, daily_call_limit, status, current_calls, total_calls_made, successful_calls, avg_call_duration, conversion_rate)
VALUES
('AI_CALLER_004', 'Site Visit Booker', '[\"hi\",\"en\",\"hi-en\"]', 3, 50, 'active', 0, 0, 0, 0, 0.00),
('AI_CALLER_005', 'Property Consultant', '[\"hi\",\"en\",\"hi-en\"]', 3, 50, 'active', 0, 0, 0, 0, 0.00),
('AI_CALLER_006', 'Lead Nurturer', '[\"hi\",\"en\",\"hi-en\"]', 5, 80, 'active', 0, 0, 0, 0, 0.00)";

if ($db->query($agent_sql)) {
    echo "Agents inserted successfully.\n";
} else {
    echo "Error inserting agents: " . $db->error . "\n";
}

$r = $db->query("SELECT COUNT(*) as cnt FROM ai_call_scripts");
$row = $r->fetch_assoc();
echo "Current scripts: {$row['cnt']}\n";

// Build scripts using PHP arrays for proper JSON encoding
$scripts = [
    [
        'script_name' => 'Site Visit Booking',
        'script_code' => 'site_visit_booking',
        'description' => 'Script for booking property site visits with customer',
        'greeting_text' => 'Namaste! Main APS Dream Home ki taraf se baat kar rahi hoon. Kya main aapko property site visit ke liye schedule kar sakti hoon?',
        'introduction_text' => 'Hello! I am calling from APS Dream Home. I understand you are interested in visiting one of our properties. I can help you schedule a convenient time.',
        'property_pitch' => 'We have a beautiful property that matches your requirements. The site is well-connected with all major amenities nearby. I would love to show you around personally.',
        'questions_to_ask' => json_encode(["Which date works best for you?", "Would morning or evening be better?", "Do you have any specific questions about the property?"]),
        'objection_handling' => json_encode(["Too busy" => "I understand you are busy. A quick 30-minute visit is all you need. We can work around your schedule.", "Already seen" => "You may have seen photos online, but a physical visit gives you a real feel of the space and neighborhood."]),
        'closing_text' => 'Thank you for scheduling your visit. Our team will be ready to welcome you. Is there anything specific you would like us to prepare for your visit?',
        'voice_language' => 'hi-en',
        'voice_speed' => 1.0,
        'voice_tone' => 'professional',
        'is_active' => 1
    ],
    [
        'script_name' => 'Property Consultation',
        'script_code' => 'property_consultation',
        'description' => 'Script for answering property inquiries and providing details',
        'greeting_text' => 'Namaste! Main APS Dream Home se bol rahi hoon. Aapne hamari property mein interest dikhaya tha, kya main aapko kuch information de sakti hoon?',
        'introduction_text' => 'Good day! This is APS Dream Home. You recently showed interest in our properties, and I wanted to share some detailed information that might help you make a decision.',
        'property_pitch' => 'This property offers excellent value in a prime location. With top-notch amenities, modern construction quality, and flexible payment plans, it is an investment you should not miss.',
        'questions_to_ask' => json_encode(["What type of property are you looking for?", "What is your budget range?", "Do you have any preferred locations?", "Are you looking for immediate possession or future delivery?"]),
        'objection_handling' => json_encode(["Too expensive" => "We have various payment plans and options. Let me walk you through our flexible financing options including bank loans with minimal down payment.", "Compare with others" => "Our properties offer superior construction quality, better location, and more amenities compared to others in the same price range."]),
        'closing_text' => 'Thank you for your time! I will send you the property details on WhatsApp. Feel free to call us anytime for more information.',
        'voice_language' => 'hi-en',
        'voice_speed' => 1.0,
        'voice_tone' => 'friendly',
        'is_active' => 1
    ],
    [
        'script_name' => 'Lead Nurturing Follow-up',
        'script_code' => 'lead_nurturing',
        'description' => 'Script for follow-up calls to nurture and qualify leads',
        'greeting_text' => 'Namaste! APS Dream Home ki taraf se follow-up call hai. Pichli baar humne baat ki thi, kya aapke koi sawaal hai?',
        'introduction_text' => 'Hi! This is a follow-up call from APS Dream Home. We spoke earlier about your property requirements. I wanted to check if you have any new questions or if there is anything else I can help you with.',
        'property_pitch' => 'Since we last spoke, we have some new developments and offers that might interest you. We also have new inventory that matches your previously mentioned preferences.',
        'questions_to_ask' => json_encode(["Have you had a chance to discuss with your family?", "Is there anything holding you back from making a decision?", "Would you like to visit the property and see it for yourself?"]),
        'objection_handling' => json_encode(["Need more time" => "Of course, take your time. Can I send you some more information to help with your decision?", "Already bought" => "Congratulations! Would you like to know about our referral program? You can earn rewards by referring friends and family."]),
        'closing_text' => 'Thank you for your time! I will follow up with you again next week. If you need anything in between, feel free to reach out.',
        'voice_language' => 'hi-en',
        'voice_speed' => 1.0,
        'voice_tone' => 'friendly',
        'is_active' => 1
    ]
];

$stmt_sql = "INSERT IGNORE INTO ai_call_scripts (script_name, script_code, description, greeting_text, introduction_text, property_pitch, questions_to_ask, objection_handling, closing_text, voice_language, voice_speed, voice_tone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $db->prepare($stmt_sql);
if (!$stmt) {
    die("Prepare failed: " . $db->error);
}

foreach ($scripts as $s) {
    $stmt->bind_param("sssssssssssdi",
        $s['script_name'],
        $s['script_code'],
        $s['description'],
        $s['greeting_text'],
        $s['introduction_text'],
        $s['property_pitch'],
        $s['questions_to_ask'],
        $s['objection_handling'],
        $s['closing_text'],
        $s['voice_language'],
        $s['voice_speed'],
        $s['voice_tone'],
        $s['is_active']
    );
    if ($stmt->execute()) {
        echo "  Inserted: {$s['script_name']}\n";
    } else {
        echo "  Error inserting {$s['script_name']}: " . $stmt->error . "\n";
    }
}

echo "\n=== Verification ===\n";
$r = $db->query("SELECT id, agent_id, agent_name, status FROM ai_calling_agents ORDER BY id");
echo "\nai_calling_agents:\n";
while ($row = $r->fetch_assoc()) {
    echo "  ID:{$row['id']} agent_id:{$row['agent_id']} Name:{$row['agent_name']} Status:{$row['status']}\n";
}

$r = $db->query("SELECT id, script_code, script_name, voice_language, is_active FROM ai_call_scripts ORDER BY id");
echo "\nai_call_scripts:\n";
while ($row = $r->fetch_assoc()) {
    echo "  ID:{$row['id']} Code:{$row['script_code']} Name:{$row['script_name']} Lang:{$row['voice_language']} Active:{$row['is_active']}\n";
}

$db->close();
echo "\n=== Seed Complete ===\n";?>