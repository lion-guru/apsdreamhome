<?php
// Check which failing routes are 404 vs 302 vs 500
$paths = [
    // Missing includes
    "/auto_orchestrator",
    "/agent_dashboard",
    // Auth-required (should be 302)
    "/user/edit-profile",
    "/associate/export/my-earnings",
    "/associate/export/active-team",
    "/associate/export/my-payouts",
    "/associate/export/downline",
    "/associate/export/new-directs",
    "/associate/export/plot-sales",
    "/associate/export/registry",
    "/employee/activities",
    "/employee/salary",
    "/employee/documents",
    "/payment",
    "/payment/initiate",
    "/payment/failure",
    "/payment/plans",
    "/payment/refund",
    "/payment/settings",
    // Admin routes
    "/admin/",
    "/admin/enterprise_dashboard",
    "/admin/dashboard/builder",
    "/admin/plots/create",
    "/admin/layout-manager",
    "/admin/ai-settings/export-usage-report",
    "/admin/godmode",
    "/admin/godmode/users",
    "/admin/godmode/system-health",
    // Unknown
    "/project_health_check",
    "/project_health",
    "/health",
    "/monitoring",
    "/api/dashboard/agent/network",
    "/api/dashboard/ceo/analytics",
    "/api/dashboard/ceo/team",
    "/api/dashboard/cfo/financial",
    "/api/dashboard/cfo/expenses",
    "/api/dashboard/builder/analytics",
    "/api/dashboard/builder/materials",
    "/api/fraud/dashboard",
];

echo "HTTP Status Code Check\n";
echo "======================\n\n";

foreach ($paths as $p) {
    $url = "http://localhost/apsdreamhome" . $p;
    $ctx = stream_context_create([
        "http" => [
            "timeout" => 5,
            "follow_location" => false,
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0\r\n"
        ]
    ]);
    $content = @file_get_contents($url, false, $ctx);
    $code = "???";
    if (isset($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match("/^HTTP\/\d\.\d\s+(\d+)/", $h, $m)) {
                $code = $m[1];
                break;
            }
        }
    }
    $label = match($code) {
        "200" => "OK",
        "302" => "REDIRECT (auth ok)",
        "301" => "MOVED",
        "404" => "*** 404 ***",
        "500" => "*** 500 ***",
        default => "UNKNOWN ($code)"
    };
    echo str_pad($code, 5) . " " . str_pad($label, 25) . " $p\n";
}

echo "\n--- DONE ---\n";
