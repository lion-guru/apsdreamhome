<?php

/**
 * Legacy Proxy for AIDreamHome
 * Bridges the namespaced App\Services\Legacy\AIDreamHome to the global namespace
 */

require_once __DIR__ . '/../app/config/env.php';
require_once __DIR__ . '/../app/services/Legacy/AIIntegration.php';

// Alias the class for any legacy code expecting AIDreamHome
if (!class_exists('AIDreamHome')) {
    class_alias('App\Services\Legacy\AIDreamHome', 'AIDreamHome');
}

// Bridge the utility functions to global namespace
if (!function_exists('generateAIPropertyDescription')) {
    function generateAIPropertyDescription($property_data)
    {
        return \App\Services\Legacy\generateAIPropertyDescription($property_data);
    }
}

if (!function_exists('getAIPropertyValuation')) {
    function getAIPropertyValuation($property_data)
    {
        return \App\Services\Legacy\getAIPropertyValuation($property_data);
    }
}

if (!function_exists('getAIChatbotResponse')) {
    function getAIChatbotResponse($user_query, $context = [])
    {
        return \App\Services\Legacy\getAIChatbotResponse($user_query, $context);
    }
}

if (!function_exists('getAIPropertyRecommendations')) {
    function getAIPropertyRecommendations($user_preferences)
    {
        return \App\Services\Legacy\getAIPropertyRecommendations($user_preferences);
    }
}
