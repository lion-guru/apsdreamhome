<?php
/**
 * Script to generate API documentation from PHP annotations
 */

require_once __DIR__ . '/vendor/autoload.php';

// Scan directories for PHP files with annotations
$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/includes/Controllers',
    __DIR__ . '/includes/Models',
]);

// Merge with base configuration
$baseConfig = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/public/api-docs/swagger.yaml');
$generatedConfig = json_decode($openapi->toJson(), true);

// Merge configurations
$mergedConfig = array_merge_recursive($baseConfig, $generatedConfig);

// Save the merged OpenAPI specification
file_put_contents(
    __DIR__ . '/public/api-docs/openapi.json',
    json_encode($mergedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo "API documentation generated successfully!\n";
echo "View the documentation at: http://localhost/apsdreamhomefinal/api/public/api-docs/index.html\n";
