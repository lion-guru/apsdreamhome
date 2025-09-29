<?php
/**
 * Google OAuth Test Script
 * This file tests the Google OAuth configuration
 */

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/google_auth.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if Google API credentials are set
$client_id_set = defined('GOOGLE_CLIENT_ID') && !empty(GOOGLE_CLIENT_ID);
$client_secret_set = defined('GOOGLE_CLIENT_SECRET') && !empty(GOOGLE_CLIENT_SECRET);
$redirect_url_set = defined('GOOGLE_REDIRECT_URL') && !empty(GOOGLE_REDIRECT_URL);

// Get Google login URL
$google_login_url = getGoogleLoginUrl();

// Check if Google API client library is available
$google_api_available = class_exists('Google_Client');

// Output test results
echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Google OAuth Test</title>\n";
echo "    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <div class='container mt-5'>\n";
echo "        <div class='card'>\n";
echo "            <div class='card-header bg-primary text-white'>\n";
echo "                <h4 class='mb-0'>Google OAuth Configuration Test</h4>\n";
echo "            </div>\n";
echo "            <div class='card-body'>\n";
echo "                <h5>Configuration Status:</h5>\n";
echo "                <ul class='list-group mb-4'>\n";
echo "                    <li class='list-group-item d-flex justify-content-between align-items-center'>\n";
echo "                        Google Client ID\n";
echo "                        <span class='badge badge-" . ($client_id_set ? 'success' : 'danger') . "'>" . ($client_id_set ? 'Set' : 'Not Set') . "</span>\n";
echo "                    </li>\n";
echo "                    <li class='list-group-item d-flex justify-content-between align-items-center'>\n";
echo "                        Google Client Secret\n";
echo "                        <span class='badge badge-" . ($client_secret_set ? 'success' : 'danger') . "'>" . ($client_secret_set ? 'Set' : 'Not Set') . "</span>\n";
echo "                    </li>\n";
echo "                    <li class='list-group-item d-flex justify-content-between align-items-center'>\n";
echo "                        Google Redirect URL\n";
echo "                        <span class='badge badge-" . ($redirect_url_set ? 'success' : 'danger') . "'>" . ($redirect_url_set ? 'Set' : 'Not Set') . "</span>\n";
echo "                    </li>\n";
echo "                    <li class='list-group-item d-flex justify-content-between align-items-center'>\n";
echo "                        Google API Client Library\n";
echo "                        <span class='badge badge-" . ($google_api_available ? 'success' : 'danger') . "'>" . ($google_api_available ? 'Available' : 'Not Available') . "</span>\n";
echo "                    </li>\n";
echo "                </ul>\n";

if ($client_id_set && $client_secret_set && $redirect_url_set && $google_api_available) {
    echo "                <div class='alert alert-success'>\n";
    echo "                    <strong>Success!</strong> Your Google OAuth configuration appears to be correct.\n";
    echo "                </div>\n";
    echo "                <h5>Configuration Details:</h5>\n";
    echo "                <ul class='list-group mb-4'>\n";
    echo "                    <li class='list-group-item'>Redirect URL: " . htmlspecialchars(GOOGLE_REDIRECT_URL) . "</li>\n";
    echo "                </ul>\n";
    echo "                <div class='text-center'>\n";
    echo "                    <a href='" . htmlspecialchars($google_login_url) . "' class='btn btn-primary'>Test Google Login</a>\n";
    echo "                </div>\n";
} else {
    echo "                <div class='alert alert-danger'>\n";
    echo "                    <strong>Error!</strong> Your Google OAuth configuration is incomplete.\n";
    echo "                </div>\n";
    echo "                <h5>Next Steps:</h5>\n";
    echo "                <ol>\n";
    if (!$client_id_set || !$client_secret_set) {
        echo "                    <li>Create a Google Cloud project and obtain OAuth credentials</li>\n";
        echo "                    <li>Update the google_oauth_config.php file with your Client ID and Client Secret</li>\n";
    }
    if (!$google_api_available) {
        echo "                    <li>Install the Google API Client Library using Composer:</li>\n";
        echo "                    <pre><code>composer require google/apiclient:2.0</code></pre>\n";
    }
    echo "                    <li>Follow the <a href='google_auth_setup_guide.md' target='_blank'>Google OAuth Setup Guide</a> for detailed instructions</li>\n";
    echo "                </ol>\n";
}

echo "            </div>\n";
echo "        </div>\n";
echo "    </div>\n";
echo "</body>\n";
echo "</html>\n";