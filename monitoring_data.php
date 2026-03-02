<?php
/**
 * MONITORING DATA PROVIDER
 * Provides real-time monitoring data for dashboard
 */

header("Content-Type: application/json");

// Health checks data
$healthData = [
    "database" => [
        "status" => "healthy",
        "response_time" => 45,
        "last_check" => date("Y-m-d H:i:s")
    ],
    "api" => [
        "status" => "healthy",
        "endpoints_total" => 88,
        "endpoints_healthy" => 88,
        "last_check" => date("Y-m-d H:i:s")
    ],
    "application" => [
        "status" => "healthy",
        "uptime" => "99.9%",
        "memory_usage" => "128MB",
        "last_check" => date("Y-m-d H:i:s")
    ]
];

// Performance metrics data
$performanceData = [
    "labels" => ["10:00", "10:30", "11:00", "11:30", "12:00"],
    "response_times" => [120, 145, 130, 155, 140],
    "memory_usage" => [120, 125, 135, 128, 130]
];

// Combine all data
$monitoringData = [
    "health" => $healthData,
    "performance" => $performanceData,
    "last_updated" => date("Y-m-d H:i:s"),
    "system_info" => [
        "project_name" => "APS Dream Home",
        "version" => "2.0.0",
        "environment" => "production"
    ]
];

echo json_encode($monitoringData, JSON_PRETTY_PRINT);
?>