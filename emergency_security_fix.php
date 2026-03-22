<?php

echo "🚨 EMERGENCY SECURITY FIX - API KEYS COMPROMISED\n";
echo "===============================================\n\n";

// Check if API keys are working (they shouldn't be!)
$keys_to_check = [
    'AIzaSyCkVFFk4xU7cawmvg14HUEugmSrLt-aW5Y',
    'AIzaSyDfsQxz1ojlgOnlg4i_nFW7aUfYdQJTcxo'
];

foreach ($keys_to_check as $key) {
    echo "🔍 Checking key: " . substr($key, 0, 10) . "...\n";
    
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
    $data = ["contents" => [["parts" => [["text" => "test"]]]]];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ❌ KEY IS WORKING - IMMEDIATE REVOKE NEEDED!\n";
    } elseif ($http_code === 403) {
        echo "  ✅ KEY IS DISABLED - Good!\n";
    } else {
        echo "  ⚠️ Status Code: $http_code\n";
    }
    echo "\n";
}

echo "🔒 IMMEDIATE ACTIONS REQUIRED:\n";
echo "1. Go to https://aistudio.google.com\n";
echo "2. Delete BOTH API keys immediately\n";
echo "3. Generate NEW API key\n";
echo "4. Update .env file with NEW key only\n";
echo "5. NEVER share API keys publicly again\n\n";

echo "📧 SECURITY INCIDENT REPORT:\n";
echo "- API Keys exposed in chat messages\n";
echo "- Both keys need immediate revocation\n";
echo "- New secure implementation ready\n";
echo "- Environment variables protection enabled\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. REVOKE old keys (IMMEDIATE)\n";
echo "2. Generate NEW key\n";
echo "3. Update .env: GEMINI_API_KEY=new_key_here\n";
echo "4. Test with: php test_simple.php\n";
echo "5. Use secure ai_chat.html interface\n\n";

echo "✅ Secure AI system is ready for new API key\n";
?>
