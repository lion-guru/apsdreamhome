<?php
/**
 * Generate Hindi voice prompts for EMI reminders and IVR
 * Uses Google TTS (free) to create audio files
 * 
 * Run: php cron/generate_voice_prompts.php
 */

set_time_limit(120);
date_default_timezone_set('Asia/Kolkata');

$soundDir = __DIR__ . '/../storage/voice_prompts';
if (!is_dir($soundDir)) mkdir($soundDir, 0755, true);

$prompts = [
    // IVR Greetings
    'greeting_hello' => 'Namaskar! APS Dream Home mein aapka swagat hai. Hum Gorakhpur mein plots aur properties provide karte hain.',
    'greeting_morning' => 'Suprabhat! APS Dream Home se baat ho rahi hai. Kya aap property ke baare mein jaanna chahte hain?',
    'greeting_evening' => 'Namaskar! APS Dream Home se call ho rahi hai. Kya aapko kisi property ke baare mein jaankari chahiye?',

    // EMI Reminder - Overdue
    'emi_overdue_intro' => 'Namaskar! APS Dream Home se call ho rahi hai. Aapka EMI payment overdue hai. Kripya jald se jald payment karein.',
    'emi_overdue_amount' => 'Aapki {amount} rupaye ki EMI baaki hai. Iska due date {due_date} tha.',
    'emi_overdue_action' => 'Payment karne ke liye aap hamare office aa sakte hain ya online transfer kar sakte hain.',
    'emi_overdue_warning' => 'Lagatar 3 EMI na bhugtane par aapki booking cancel ho sakti hai. Kripya turant payment karein.',

    // EMI Reminder - Due Today
    'emi_today_intro' => 'Namaskar! APS Dream Home se call ho rahi hai. Aaj aapki EMI due hai.',
    'emi_today_amount' => 'Aapki {amount} rupaye ki EMI aaj pay karni hai.',
    'emi_today_action' => 'Aap hamare Raghunath Nagri office mein cash ya online payment kar sakte hain.',
    'emi_today_thank' => 'Payment karne ke baad receipt zaroor lein. Dhanyavaad!',

    // EMI Reminder - Upcoming
    'emi_upcoming_intro' => 'Namaskar! APS Dream Home se friendly reminder hai.',
    'emi_upcoming_amount' => 'Aapki agla EMI {amount} rupaye ka {days} din mein due hai.',
    'emi_upcoming_date' => 'Due date hai {due_date}.',
    'emi_upcoming_prep' => 'Kripya payment ki taiyari kar lein. Aap online ya offline dono tarah se pay kar sakte hain.',

    // Property Introduction
    'property_intro' => 'APS Dream Home mein hum residential plots offer karte hain Gorakhpur ke prime locations mein.',
    'property_price' => 'Humari starting price hai paanch lakh rupaye se. EMI suvidha bhi uplabdh hai.',
    'property_location' => 'Humare colonies hain Suryoday, Braj Radha Nagri, Raghunath Nagri, aur Budh Bihar. Sab locations Gorakhpur mein hain.',
    'property_visit' => 'Aap free site visit ke liye aa sakte hain. Hamara office hai Raghunath Nagri, Gorakhpur.',

    // Site Visit
    'visit_offer' => 'Kya aap hamari property dekhna chahenge? Hum aapke liye free site visit arrange kar sakte hain.',
    'visit_schedule' => 'Bataiye kaun sa din aur samay aapke liye convenient hoga?',
    'visit_address' => 'Hamara office address hai APS Dream Home, Raghunath Nagri, Gorakhpur, Uttar Pradesh.',

    // Booking
    'booking_info' => 'Plot book karne ke liye sirf 21,000 rupaye ki booking amount lagegi.',
    'booking_process' => 'Booking ke liye aapko apna Aadhaar card, PAN card aur passport size photo chahiye.',
    'booking_emi' => 'Humare paas 12 se 60 mahine tak ki EMI options hain. Bank se 80% tak loan bhi mil sakta hai.',

    // Goodbye
    'goodbye' => 'APS Dream Home dhanyavaad! Aapka din shubh ho. Jab bhi zaroorat ho, humse contact karein.',
    'goodbye_thanks' => 'Baatsheet ke liye dhanyavaad. Hamari team aapse jald hi milegi.',
    'goodbye_later' => 'Koi baat nhi. Jab bhi aapka mann kare, humse baat karein. Dhanyavaad!',

    // Fallback
    'repeat' => 'Maafi chahunga, samajh nahi aaya. Kya aap phir se bol sakte hain?',
    'hold' => 'Kripya ek minute intezaar karein. Main aapki madad karta hoon.',
    'transfer' => 'Ek second, main aapko hamari team se connect karta hoon.',
];

$generated = 0;
$failed = 0;

echo "=== Voice Prompt Generator ===\n";
echo "Generating " . count($prompts) . " prompts...\n\n";

foreach ($prompts as $name => $text) {
    $file = $soundDir . '/' . $name . '.mp3';
    
    // Skip if already exists
    if (file_exists($file) && filesize($file) > 100) {
        echo "[SKIP] {$name} (already exists)\n";
        $generated++;
        continue;
    }
    
    // Generate via Google TTS
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=hi-IN&client=tw-ob&q=" . urlencode($text);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    
    $audio = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    curl_close($ch);
    
    if ($httpCode === 200 && $audio && strlen($audio) > 500) {
        file_put_contents($file, $audio);
        echo "[OK] {$name} (" . round($size / 1024, 1) . " KB)\n";
        $generated++;
    } else {
        echo "[FAIL] {$name} (HTTP {$httpCode})\n";
        $failed++;
    }
    
    // Rate limit: 0.5s between requests
    usleep(500000);
}

// Generate placeholder sound files for Asterisk
$asteriskSoundDir = __DIR__ . '/../storage/voice_prompts/asterisk';
if (!is_dir($asteriskSoundDir)) mkdir($asteriskSoundDir, 0755, true);

$asteriskPrompts = [
    'hello_hi' => 'greeting_hello',
    'booking_info' => 'booking_info',
    'connecting_sales' => 'transfer',
    'emi_reminder_hi' => 'emi_overdue_intro',
    'pay_now_info' => 'emi_overdue_action',
    'goodbye_hi' => 'goodbye',
    'repeat' => 'repeat',
    'fallback' => 'repeat',
];

foreach ($asteriskPrompts as $asteriskName => $sourceName) {
    $source = $soundDir . '/' . $sourceName . '.mp3';
    $dest = $asteriskSoundDir . '/' . $asteriskName . '.gsm';
    
    if (file_exists($source) && !file_exists($dest)) {
        exec("sox {$source} -r 8000 -c 1 {$dest} 2>/dev/null || cp {$source} {$dest}");
        echo "[COPY] {$asteriskName}.gsm\n";
    }
}

echo "\n=== Summary ===\n";
echo "Generated: {$generated}\n";
echo "Failed: {$failed}\n";
echo "Output: {$soundDir}\n";
echo "Asterisk sounds: {$asteriskSoundDir}\n";?>