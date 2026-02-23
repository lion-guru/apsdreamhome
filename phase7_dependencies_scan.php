<?php
/**
 * APS Dream Home - Phase 7 Deep Scan: Dependencies Analysis
 * Comprehensive analysis of composer and npm dependencies for conflicts and security
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'dependencies_scan',
    'summary' => [],
    'issues' => [],
    'security_vulnerabilities' => [],
    'outdated_packages' => [],
    'recommendations' => []
];

echo "📦 Phase 7: Dependencies Deep Analysis\n";
echo "=====================================\n\n";

// Check Composer dependencies
echo "🐘 Analyzing Composer Dependencies\n";
echo "==================================\n";

$composerJsonPath = $projectRoot . '/composer.json';
$composerLockPath = $projectRoot . '/composer.lock';

if (file_exists($composerJsonPath)) {
    echo "✅ composer.json found\n";

    $composerJson = json_decode(file_get_contents($composerJsonPath), true);
    if ($composerJson === null) {
        $results['issues'][] = "Invalid composer.json syntax";
        echo "❌ Invalid composer.json syntax\n";
    } else {
        echo "✅ composer.json syntax valid\n";

        // Analyze dependencies
        $dependencies = isset($composerJson['require']) ? $composerJson['require'] : [];
        $devDependencies = isset($composerJson['require-dev']) ? $composerJson['require-dev'] : [];

        echo "📦 Runtime dependencies: " . count($dependencies) . "\n";
        echo "🔧 Dev dependencies: " . count($devDependencies) . "\n";

        // Check for Laravel/Lumen framework
        $hasLaravel = isset($dependencies['laravel/framework']);
        $hasLumen = isset($dependencies['laravel/lumen-framework']);

        if ($hasLaravel) {
            echo "✅ Laravel framework detected\n";
        } elseif ($hasLumen) {
            echo "✅ Lumen framework detected\n";
        } else {
            echo "ℹ️  No Laravel/Lumen framework detected (custom framework)\n";
        }

        // Check for potential security issues
        $securityIssues = [];
        foreach ($dependencies as $package => $version) {
            // Check for wildcard versions (security risk)
            if (strpos($version, '*') !== false) {
                $securityIssues[] = "Wildcard version constraint for {$package}: {$version}";
            }

            // Check for very old versions (might be vulnerable)
            if (preg_match('/^[<>=~^]*([0-9]+)\./', $version, $matches)) {
                $majorVersion = (int)$matches[1];
                if ($majorVersion < 2) {
                    $results['issues'][] = "Very old major version for {$package}: {$version}";
                }
            }
        }

        if (!empty($securityIssues)) {
            $results['security_vulnerabilities'] = array_merge($results['security_vulnerabilities'], $securityIssues);
        }
    }

    // Check composer.lock
    if (file_exists($composerLockPath)) {
        echo "✅ composer.lock found\n";

        $composerLock = json_decode(file_get_contents($composerLockPath), true);
        if ($composerLock === null) {
            $results['issues'][] = "Invalid composer.lock syntax";
            echo "❌ Invalid composer.lock syntax\n";
        } else {
            $lockedPackages = isset($composerLock['packages']) ? count($composerLock['packages']) : 0;
            echo "🔒 Locked packages: {$lockedPackages}\n";
        }
    } else {
        $results['issues'][] = "composer.lock not found - dependencies not locked";
        echo "⚠️  composer.lock not found\n";
    }

} else {
    $results['issues'][] = "composer.json not found";
    echo "❌ composer.json not found\n";
}

echo "\n";

// Check NPM dependencies
echo "📦 Analyzing NPM Dependencies\n";
echo "=============================\n";

$packageJsonPath = $projectRoot . '/package.json';
$packageLockPath = $projectRoot . '/package-lock.json';
$yarnLockPath = $projectRoot . '/yarn.lock';

if (file_exists($packageJsonPath)) {
    echo "✅ package.json found\n";

    $packageJson = json_decode(file_get_contents($packageJsonPath), true);
    if ($packageJson === null) {
        $results['issues'][] = "Invalid package.json syntax";
        echo "❌ Invalid package.json syntax\n";
    } else {
        echo "✅ package.json syntax valid\n";

        // Analyze dependencies
        $dependencies = isset($packageJson['dependencies']) ? $packageJson['dependencies'] : [];
        $devDependencies = isset($packageJson['devDependencies']) ? $packageJson['devDependencies'] : [];

        echo "📦 Runtime dependencies: " . count($dependencies) . "\n";
        echo "🔧 Dev dependencies: " . count($devDependencies) . "\n";

        // Check for scripts
        $scripts = isset($packageJson['scripts']) ? $packageJson['scripts'] : [];
        echo "📜 Scripts defined: " . count($scripts) . "\n";

        $importantScripts = ['dev', 'build', 'start', 'test'];
        $missingScripts = [];
        foreach ($importantScripts as $script) {
            if (!isset($scripts[$script])) {
                $missingScripts[] = $script;
            }
        }

        if (!empty($missingScripts)) {
            $results['issues'][] = "Missing important npm scripts: " . implode(', ', $missingScripts);
        }

        // Check for potential security issues
        $securityIssues = [];
        foreach ($dependencies as $package => $version) {
            // Check for wildcard versions
            if (strpos($version, '*') !== false || strpos($version, 'latest') !== false) {
                $securityIssues[] = "Wildcard version constraint for {$package}: {$version}";
            }
        }

        if (!empty($securityIssues)) {
            $results['security_vulnerabilities'] = array_merge($results['security_vulnerabilities'], $securityIssues);
        }
    }

    // Check lock files
    $hasLockFile = false;
    if (file_exists($packageLockPath)) {
        echo "✅ package-lock.json found\n";
        $hasLockFile = true;
    } elseif (file_exists($yarnLockPath)) {
        echo "✅ yarn.lock found\n";
        $hasLockFile = true;
    } else {
        $results['issues'][] = "No package lock file found (package-lock.json or yarn.lock)";
        echo "⚠️  No package lock file found\n";
    }

} else {
    echo "ℹ️  package.json not found (no Node.js dependencies)\n";
}

echo "\n";

// Check for vendor directory
echo "📁 Checking Vendor Directory\n";
echo "===========================\n";

$vendorDir = $projectRoot . '/vendor';
if (is_dir($vendorDir)) {
    $composerInstalled = is_dir($vendorDir . '/composer');
    if ($composerInstalled) {
        echo "✅ Composer dependencies installed\n";

        // Count installed packages
        $packageCount = count(glob($vendorDir . '/*', GLOB_ONLYDIR));
        echo "📦 Installed packages: {$packageCount}\n";
    } else {
        $results['issues'][] = "Vendor directory exists but composer packages not properly installed";
        echo "⚠️  Vendor directory incomplete\n";
    }
} else {
    $results['issues'][] = "Vendor directory not found - run 'composer install'";
    echo "❌ Vendor directory not found\n";
}

echo "\n";

// Check for node_modules
echo "📁 Checking Node Modules\n";
echo "========================\n";

$nodeModulesDir = $projectRoot . '/node_modules';
if (is_dir($nodeModulesDir)) {
    echo "✅ Node modules installed\n";

    // Count installed packages (rough estimate)
    $packageCount = count(glob($nodeModulesDir . '/*', GLOB_ONLYDIR));
    echo "📦 Installed packages: {$packageCount}\n";
} else {
    echo "ℹ️  Node modules not found (run 'npm install' if needed)\n";
}

echo "\n";

// Dependency conflict analysis
echo "🔍 Analyzing Potential Conflicts\n";
echo "===============================\n";

$conflicts = [];

// Check for mixed framework usage
if (isset($composerJson) && isset($packageJson)) {
    $hasLaravel = isset($composerJson['require']['laravel/framework']);
    $hasSymfony = isset($composerJson['require']['symfony/http-kernel']);

    if ($hasLaravel && $hasSymfony) {
        $conflicts[] = "Both Laravel and Symfony components detected - potential conflicts";
    }

    // Check for PHP/JS version conflicts
    $phpVersion = isset($composerJson['require']['php']) ? $composerJson['require']['php'] : null;
    $nodeVersion = isset($packageJson['engines']['node']) ? $packageJson['engines']['node'] : null;

    if ($phpVersion && $nodeVersion) {
        echo "📊 PHP requirement: {$phpVersion}\n";
        echo "📊 Node requirement: {$nodeVersion}\n";
    }
}

if (empty($conflicts)) {
    echo "✅ No obvious dependency conflicts detected\n";
} else {
    foreach ($conflicts as $conflict) {
        echo "⚠️  {$conflict}\n";
        $results['issues'][] = $conflict;
    }
}

echo "\n";

// Generate summary
echo "📊 Analysis Summary\n";
echo "==================\n";

$totalIssues = count($results['issues']);
$securityIssues = count($results['security_vulnerabilities']);

if ($totalIssues === 0 && $securityIssues === 0) {
    echo "🎉 Dependencies appear well-configured!\n";
} else {
    echo "⚠️  Found {$totalIssues} issues and {$securityIssues} security concerns\n";
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Run 'composer install' to install/update PHP dependencies\n";
echo "• Run 'npm install' to install/update Node.js dependencies\n";
echo "• Run 'composer update' periodically to get security updates\n";
echo "• Use 'composer audit' to check for security vulnerabilities\n";
echo "• Consider using 'npm audit' for JavaScript security checks\n";
echo "• Lock dependency versions in production\n";
echo "• Run Phase 8: Routing and middleware analysis\n";

$results['summary'] = [
    'composer_dependencies' => isset($composerJson['require']) ? count($composerJson['require']) : 0,
    'npm_dependencies' => isset($packageJson['dependencies']) ? count($packageJson['dependencies']) : 0,
    'total_issues' => $totalIssues,
    'security_vulnerabilities' => $securityIssues
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase7_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 7 Complete - Ready for Phase 8!\n";

?>
