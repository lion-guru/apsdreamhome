<?php
/**
 * Firebase Configuration for APS Dream Homes
 * 
 * Get these from Firebase Console > Project Settings > General > Your apps > Web app
 * For server-side admin SDK, also need service account key from:
 * Firebase Console > Project Settings > Service Accounts > Generate new private key
 */

return [
    // Client-side config (safe to expose in frontend)
    'client' => [
        'apiKey' => env('FIREBASE_API_KEY', ''),
        'authDomain' => env('FIREBASE_AUTH_DOMAIN', ''),
        'projectId' => env('FIREBASE_PROJECT_ID', ''),
        'storageBucket' => env('FIREBASE_STORAGE_BUCKET', ''),
        'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID', ''),
        'appId' => env('FIREBASE_APP_ID', ''),
        'measurementId' => env('FIREBASE_MEASUREMENT_ID', ''),
    ],
    
    // Admin SDK config (server-side only - NEVER expose to frontend)
    'admin' => [
        'project_id' => env('FIREBASE_PROJECT_ID', ''),
        'client_email' => env('FIREBASE_CLIENT_EMAIL', ''),
        'private_key' => str_replace('\\n', "\n", env('FIREBASE_PRIVATE_KEY', '')),
    ],
    
    // Database paths
    'paths' => [
        'bookings' => 'artifacts/{appId}/public/data/bookings',
        'colonies' => 'artifacts/{appId}/public/data/colonies',
    ],
];

// env() helper is globally available from app/helpers.php