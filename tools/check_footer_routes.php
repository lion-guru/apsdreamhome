<?php
$testUrls = [
    '/about', '/services', '/privacy', '/resell',
    '/projects', '/company/projects',
    '/financial-services', '/legal/terms-conditions',
    '/legal/services', '/privacy-policy', '/terms'
];

$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n",
        'follow_location' => 0,
        'timeout' => 10
    ]
];
$context = stream_context_create($opts);

foreach ($testUrls as $url) {
    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    $headers = @get_headers($fullUrl);
    $httpCode = $headers ? (int)explode(' ', $headers[0])[1] : 0;
    $has500 = $httpCode === 500;
    $has404 = $httpCode === 404;
    $has200 = $httpCode === 200;
    $has302 = $httpCode === 302;
    $status = $has200 ? 'OK 200' : ($has302 ? 'REDIRECT 302' : ($has500 ? 'FAIL 500' : ($has404 ? 'FAIL 404' : "FAIL $httpCode")));
    echo "  $url → $status\n";
}
