<?php
/**
 * OpenAPI Spec Generator — Phase 8: OpenAPI Auto-Generation
 *
 * Uses zircote/swagger-php v7 to scan controller docblocks for OpenAPI annotations
 * and generate openapi.json. Falls back to manual spec if no annotations found.
 *
 * Run via: php scripts/generate_openapi.php
 * Output: docs/openapi.json
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use OpenApi\Generator;

$scanDirs = [
    __DIR__ . '/../app/Http/Controllers',
    __DIR__ . '/../app/Services',
    __DIR__ . '/../app/OpenApi',
];

try {
    echo "Scanning for OpenAPI annotations...\n";
    $generator = new Generator();
    $openapi = $generator->generate($scanDirs);
    $output = $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    $outputDir = __DIR__ . '/../docs';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $outputFile = $outputDir . '/openapi.json';
    
    // Parse the generated spec first
    $spec = json_decode($output, true);
    $pathCount = count($spec['paths'] ?? []);
    
    // If annotation scanning finds 0 paths, preserve existing manual spec
    if ($pathCount === 0) {
        if (file_exists($outputFile)) {
            $existing = file_get_contents($outputFile);
            $existingSpec = json_decode($existing, true);
            if (count($existingSpec['paths'] ?? []) > 0) {
                echo "⚠️  No annotations found. Preserving existing manual spec (" . number_format(strlen($existing)) . " bytes, " . count($existingSpec['paths']) . " paths)\n";
                exit(0);
            }
        }
    }
    
    file_put_contents($outputFile, $output);

    echo "✅ OpenAPI spec generated: $outputFile\n";
    echo "   Size: " . number_format(strlen($output)) . " bytes\n";
    echo "   Paths: $pathCount\n";
    echo "   Schemas: " . count($spec['components']['schemas'] ?? []) . "\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
