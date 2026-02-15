<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AuthService;

function run_tests() {
    $authService = new AuthService();
    $results = [];

    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Test 1: Initial state
    $_SESSION = [];
    $results['isLoggedIn (initial)'] = $authService->isLoggedIn() === false;

    // Test 2: Login
    $_SESSION['auser'] = 'admin';
    $results['isLoggedIn (logged in)'] = $authService->isLoggedIn() === true;

    // Test 3: Admin check
    $_SESSION['role'] = 'admin';
    $results['isAdmin (true)'] = $authService->isAdmin() === true;

    // Test 4: Admin check (false)
    $_SESSION['role'] = 'user';
    $results['isAdmin (false)'] = $authService->isAdmin() === false;

    // Test 5: Logout
    $authService->logout();
    $results['logout (clears session)'] = empty($_SESSION);

    // Summary
    echo "--- AuthService Test Results ---\n";
    $all_passed = true;
    foreach ($results as $test => $passed) {
        echo ($passed ? "✅ [PASS]" : "❌ [FAIL]") . " $test\n";
        if (!$passed) $all_passed = false;
    }

    if ($all_passed) {
        echo "\nAll AuthService tests passed! 🎉\n";
    } else {
        echo "\nSome tests failed. ⚠️\n";
    }
}

run_tests();
