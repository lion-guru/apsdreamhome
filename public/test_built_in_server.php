\u003c?php
// Test script to verify error pages work with PHP built-in server

$testUrl = 'http://localhost:8000/test/error/404';

echo "Testing error page via PHP built-in server: $testUrl\n\n";

$command = "curl -s -o NUL -w \"%{http_code}\" $testUrl";
$httpCode = (int) shell_exec($command);

if ($httpCode === 200) {
    echo "Success! HTTP Status Code: $httpCode\n";
    // Optionally, you can fetch the content if needed for further checks
    // $response = file_get_contents($testUrl);
    // echo "Response preview (first 500 chars):\n";
    // echo substr($response, 0, 500) . "...\n";
} else {
    echo "Failed to fetch URL. HTTP Status Code: $httpCode\n";
}
