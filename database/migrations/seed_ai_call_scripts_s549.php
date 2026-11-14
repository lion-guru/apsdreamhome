<?php
/**
 * Migration: Seed the ai_call_scripts table used by the dynamic TwiML path
 * (generateTwiMLFromScript) (Session 549).
 *
 * Before this migration ai_call_scripts was empty, so every AI outbound call
 * fell back to the static greeting in TwilioVoiceService. These two bilingual
 * (hi-en) scripts cover the two scheduled call types in use today:
 *   - property_introduction    (property enquiry follow-up)
 *   - site_visit_confirmation (site visit scheduling/reminder)
 *
 * Idempotent: skips any script_code that already exists.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$scripts = [
    [
        'code'    => 'property_introduction',
        'name'    => 'Property Introduction (hi-en)',
        'body'    => "GREETING: Namaste {customer_name} ji! Main APS Dream Home se bol raha hoon. Hum Gorakhpur mein premium township plots provide karte hain.\n"
                   . "PROMPT: Kripya option choose karein. Dial 1 site visit book karne ke liye. Dial 2 price aur EMI details ke liye. Dial 3 agent se baat karne ke liye.\n"
                   . "CLOSE: Thank you. Hamara agent aapse jald hi sampark karega. Goodbye.",
    ],
    [
        'code'    => 'site_visit_confirmation',
        'name'    => 'Site Visit Confirmation (hi-en)',
        'body'    => "GREETING: Namaste {customer_name} ji! Main APS Dream Home se bol raha hoon. Aapki site visit confirm karne ke liye call kar raha hoon.\n"
                   . "PROMPT: Kripya option choose karein. Dial 1 appointment confirm karne ke liye. Dial 2 reschedule karne ke liye. Dial 3 agent se baat karne ke liye.\n"
                   . "CLOSE: Thank you. Agar aapka appointment confirm hua hai to hamara agent aapko address aur directions ke saath SMS bhejega. Goodbye.",
    ],
];

$check = $pdo->prepare('SELECT COUNT(*) FROM ai_call_scripts WHERE script_code = ? AND tenant_id = 1');
$insert = $pdo->prepare(
    'INSERT INTO ai_call_scripts
        (script_code, script_name, script_body, is_active, tenant_id)
     VALUES (?, ?, ?, 1, 1)'
);

$added = 0;
foreach ($scripts as $s) {
    $check->execute([$s['code']]);
    if ((int)$check->fetchColumn() > 0) {
        echo "SKIP {$s['code']} - already exists\n";
        continue;
    }
    $insert->execute([$s['code'], $s['name'], $s['body']]);
    $added++;
    echo "SEEDED {$s['code']}\n";
}

echo "Done. Scripts inserted: {$added}\n";
