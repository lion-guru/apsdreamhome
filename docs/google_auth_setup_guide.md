# Google OAuth Setup Guide for APS Dream Homes

This guide will walk you through setting up Google OAuth authentication for your website.

## Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top of the page and click "New Project"
3. Enter a project name (e.g., "APS Dream Homes") and click "Create"
4. Once the project is created, select it from the project dropdown

## Step 2: Configure OAuth Consent Screen

1. In the Google Cloud Console, navigate to "APIs & Services" > "OAuth consent screen"
2. Select "External" as the user type (unless you have a Google Workspace account)
3. Click "Create"
4. Fill in the required information:
   - App name: "APS Dream Homes"
   - User support email: Your email address
   - Developer contact information: Your email address
5. Click "Save and Continue"
6. On the "Scopes" page, click "Add or Remove Scopes" and add the following scopes:
   - `./auth/userinfo.email`
   - `./auth/userinfo.profile`
7. Click "Save and Continue"
8. Review your app registration summary and click "Back to Dashboard"

## Step 3: Create OAuth Credentials

1. In the Google Cloud Console, navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "OAuth client ID"
3. Select "Web application" as the application type
4. Enter a name for your OAuth client (e.g., "APS Dream Homes Web Client")
5. Under "Authorized JavaScript origins", add your website's domain (for local development, add `http://localhost`)
6. Under "Authorized redirect URIs", add the callback URL:
   - For local development: `http://localhost/march2025apssite/google_callback.php`
   - For production: `https://yourdomain.com/march2025apssite/google_callback.php`
7. Click "Create"
8. A popup will appear with your client ID and client secret. Copy these values.

## Step 4: Update Configuration in google_auth.php

1. Open the `google_auth.php` file in your project
2. Replace the placeholder values with your actual Google API credentials:
   ```php
   define('GOOGLE_CLIENT_ID', 'your-client-id-here'); // Replace with your Client ID
   define('GOOGLE_CLIENT_SECRET', 'your-client-secret-here'); // Replace with your Client Secret
   define('GOOGLE_REDIRECT_URL', 'http://localhost/march2025apssite/google_callback.php'); // Update with your domain
   ```
3. For production, update the redirect URL to use your actual domain name

## Step 5: Test the Authentication

1. Make sure you have installed the Google API Client Library using Composer:
   ```
   composer require google/apiclient:2.0
   ```
2. Navigate to your login page
3. Click on the "Login with Google" button
4. You should be redirected to Google's authentication page
5. After authenticating, you should be redirected back to your website and logged in

## Troubleshooting

- If you encounter a "redirect_uri_mismatch" error, make sure the redirect URI in your Google Cloud Console matches exactly with the one in your code
- If you get a "invalid_client" error, double-check your client ID and client secret
- For local development, make sure you're using `http://localhost` instead of `127.0.0.1`

## Security Considerations

- Never commit your client secret to version control
- Consider using environment variables or a .env file to store sensitive credentials
- Implement CSRF protection for your authentication flow
- Always validate and sanitize user data before storing it in your database

For more information, refer to the [Google Identity documentation](https://developers.google.com/identity/protocols/oauth2/web-server).