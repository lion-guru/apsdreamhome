<?php
/**
 * Simple API Wrapper - Database Independent
 */

header("Content-Type: application/json");

function getStaticStats() {
    return [
        "success" => true,
        "stats" => [
            "mcp_keys" => ["total" => 4, "active" => 4],
            "user_keys" => ["total" => 2, "active" => 2],
            "total_keys" => 6,
            "active_keys" => 6
        ]
    ];
}

function getStaticMcpKeys() {
    return [
        "success" => true,
        "keys" => [
            [
                "key_name" => "GOOGLE_MAPS_API_KEY",
                "service_name" => "Google Maps",
                "key_type" => "api_key",
                "is_active" => 1,
                "created_at" => date("Y-m-d H:i:s")
            ],
            [
                "key_name" => "RECAPTCHA_SITE_KEY",
                "service_name" => "Google reCAPTCHA",
                "key_type" => "api_key",
                "is_active" => 1,
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]
    ];
}

function getStaticUserKeys() {
    return [
        "success" => true,
        "keys" => [
            [
                "api_key" => "aps_test_key_123",
                "name" => "Test API Key",
                "user_id" => 1,
                "status" => "active",
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]
    ];
}

function getStaticSystemStats() {
    return [
        "success" => true,
        "memory" => 25,
        "cpu" => 30,
        "storage" => 45,
        "timestamp" => date("Y-m-d H:i:s")
    ];
}

$action = $_GET["action"] ?? "";

switch ($action) {
    case "stats":
        echo json_encode(getStaticStats());
        break;
    case "mcp_keys":
        echo json_encode(getStaticMcpKeys());
        break;
    case "user_keys":
        echo json_encode(getStaticUserKeys());
        break;
    case "system_stats":
        echo json_encode(getStaticSystemStats());
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?>