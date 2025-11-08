<?php
// Placeholder for common-functions.php
// TODO: Restore the original contents of this file.

// --- Google OAuth Login URL Generator ---
function getGoogleLoginUrl($redirectUri = null) {
    // Load config (assumes google_oauth_config.php defines constants)
    if (!defined('GOOGLE_CLIENT_ID')) {
        require_once __DIR__ . '/../google_oauth_config.php';
    }
    $clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
    $redirectUri = $redirectUri ?: (defined('GOOGLE_REDIRECT_URL') ? GOOGLE_REDIRECT_URL : 'http://localhost/apsdreamhome/google_callback.php');
    $scope = 'email profile';
    $state = bin2hex(random_bytes(8)); // Optional: for CSRF protection

    $params = [
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => $scope,
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    return $url;
}

// --- Google OAuth Login URL Generator for Associates ---
function getAssociateGoogleLoginUrl($redirectUri = null) {
    if (!defined('GOOGLE_CLIENT_ID')) {
        require_once __DIR__ . '/../google_oauth_config.php';
    }
    $clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
    // Default to a special associate redirect if not provided
    $defaultAssociateRedirect = (defined('GOOGLE_REDIRECT_URL') ? dirname(GOOGLE_REDIRECT_URL) . '/associate_google_callback.php' : 'http://localhost/apsdreamhome/associate_google_callback.php');
    $redirectUri = $redirectUri ?: $defaultAssociateRedirect;
    $scope = 'email profile';
    $state = bin2hex(random_bytes(8));
    $params = [
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => $scope,
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    return $url;
}
