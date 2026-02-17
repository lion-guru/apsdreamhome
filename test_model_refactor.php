<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers.php';

use App\Models\User;
use App\Core\Database;
use App\Core\Database as CoreDatabase;

echo "Testing Model Refactor...\n";

try {
    // 1. Test Database Singleton Consolidation
    echo "\n1. Testing Database instances...\n";
    $coreDb = CoreDatabase::getInstance();
    $modelDb = \App\Models\Database::getInstance();

    echo "CoreDatabase class: " . get_class($coreDb) . "\n";
    echo "ModelDatabase class: " . get_class($modelDb) . "\n";

    if ($coreDb === $modelDb) {
        echo "SUCCESS: Database instances are the same (Singleton works across classes)!\n";
    } else {
        echo "WARNING: Database instances are different objects.\n";
        // This is expected before I fix App\Models\Database
    }

    // 2. Test User Model (which extends App\Models\Model -> App\Core\Model)
    echo "\n2. Testing User Model...\n";

    // Test hydration (static method)
    $userData = [
        'id' => 999,
        'username' => 'test_user',
        'email' => 'test@example.com',
        'role' => 'user'
    ];

    $user = User::hydrate($userData);
    echo "Hydrated User ID: " . $user->id . "\n";
    echo "Hydrated Username: " . $user->username . "\n";

    if ($user instanceof App\Core\Model) {
        echo "SUCCESS: User is instance of App\Core\Model\n";
    } else {
        echo "FAILURE: User is NOT instance of App\Core\Model\n";
    }

    // Test fill (override in App\Models\Model)
    $user = new User();
    $user->fill(['id' => 1000, 'username' => 'filled_user']);
    echo "Filled User ID: " . $user->id . "\n"; // Should be set because we allowed 'id' in fill() override

    if ($user->id == 1000) {
        echo "SUCCESS: User ID filled correctly (backward compatibility)\n";
    } else {
        echo "FAILURE: User ID NOT filled\n";
    }

    echo "\nTests Completed.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
