<?php

/**
 * API Documentation Generator
 * Automatically generates comprehensive API documentation
 */
class ApiDocumentation
{
    private $apiEndpoints = [];
    private $baseUrl = '';

    public function __construct()
    {
        $this->baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome/';
        $this->loadApiEndpoints();
    }

    /**
     * Load all API endpoints from routing configuration
     */
    private function loadApiEndpoints(): void
    {
        // Load from Router.php routes
        $routerFile = __DIR__ . '/../core/Router.php';

        if (file_exists($routerFile)) {
            $content = file_get_contents($routerFile);

            // Extract API routes (lines containing 'api/')
            preg_match_all('/\'api\/[^\'"]+\'\s*=>\s*\[([^\]]+)\]/', $content, $matches);

            foreach ($matches[0] as $index => $routeDefinition) {
                $route = $matches[0][$index];
                $controllerInfo = $matches[1][$index];

                // Extract route path and controller info
                if (preg_match('/\'(api\/[^\'"]+)\'/', $route, $pathMatch)) {
                    $path = $pathMatch[1];
                    $endpoint = $this->parseControllerInfo($controllerInfo);

                    $this->apiEndpoints[$path] = [
                        'path' => $path,
                        'method' => $endpoint['method'] ?? 'GET',
                        'controller' => $endpoint['controller'] ?? 'Unknown',
                        'action' => $endpoint['action'] ?? 'unknown',
                        'description' => $this->generateDescription($path),
                        'parameters' => $this->extractParameters($path),
                        'responses' => $this->getResponseExamples($path)
                    ];
                }
            }
        }

        // Load from API directory files
        $this->loadApiFiles();
    }

    /**
     * Load API endpoints from actual API files
     */
    private function loadApiFiles(): void
    {
        $apiDir = __DIR__ . '/../../api/';

        if (is_dir($apiDir)) {
            $files = scandir($apiDir);

            foreach ($files as $file) {
                if (strpos($file, '.php') !== false && $file !== 'index.php') {
                    $endpoint = str_replace('.php', '', $file);

                    if (!isset($this->apiEndpoints[$endpoint])) {
                        $this->apiEndpoints[$endpoint] = [
                            'path' => $endpoint,
                            'method' => 'GET',
                            'controller' => 'ApiController',
                            'action' => $endpoint,
                            'description' => $this->generateDescription($endpoint),
                            'parameters' => [],
                            'responses' => []
                        ];
                    }
                }
            }
        }
    }

    /**
     * Parse controller information from route definition
     */
    private function parseControllerInfo(string $controllerInfo): array
    {
        $info = [];

        if (preg_match('/\'controller\'\s*=>\s*\'([^\'"]+)\'/', $controllerInfo, $match)) {
            $info['controller'] = $match[1];
        }

        if (preg_match('/\'action\'\s*=>\s*\'([^\'"]+)\'/', $controllerInfo, $match)) {
            $info['action'] = $match[1];
        }

        return $info;
    }

    /**
     * Generate description for API endpoint
     */
    private function generateDescription(string $path): string
    {
        $descriptions = [
            'properties' => 'Retrieve list of properties',
            'property' => 'Get detailed information about a specific property',
            'inquiry/submit' => 'Submit a property inquiry',
            'favorites' => 'Manage user favorites',
            'search' => 'Search properties based on criteria',
            'leads' => 'Manage leads and inquiries',
            'users' => 'User management operations',
            'auth' => 'Authentication operations',
            'analytics' => 'Analytics and reporting data',
            'notifications' => 'Notification management',
            'book' => 'Booking and appointment management',
            'schedule_visit' => 'Schedule property visits',
            'testimonials' => 'Testimonial management'
        ];

        $pathParts = explode('/', $path);
        $mainPath = $pathParts[0];

        return $descriptions[$mainPath] ?? 'API endpoint for ' . str_replace('_', ' ', $path);
    }

    /**
     * Extract parameters from API path
     */
    private function extractParameters(string $path): array
    {
        $parameters = [];

        // Common parameter patterns
        if (strpos($path, 'property') !== false) {
            $parameters[] = [
                'name' => 'id',
                'type' => 'integer',
                'required' => true,
                'description' => 'Property ID'
            ];
        }

        if (strpos($path, 'search') !== false) {
            $parameters[] = [
                'name' => 'q',
                'type' => 'string',
                'required' => false,
                'description' => 'Search query'
            ];
        }

        return $parameters;
    }

    /**
     * Get response examples for API endpoints
     */
    private function getResponseExamples(string $path): array
    {
        $examples = [
            'properties' => [
                'success' => [
                    'status' => 'success',
                    'data' => [
                        [
                            'id' => 1,
                            'title' => 'Sample Property',
                            'price' => 50000,
                            'location' => 'Gorakhpur'
                        ]
                    ]
                ]
            ],
            'property' => [
                'success' => [
                    'status' => 'success',
                    'data' => [
                        'id' => 1,
                        'title' => 'Sample Property',
                        'description' => 'Property description',
                        'price' => 50000,
                        'images' => ['image1.jpg', 'image2.jpg']
                    ]
                ]
            ]
        ];

        return $examples[$path] ?? [];
    }

    /**
     * Generate OpenAPI/Swagger documentation
     */
    public function generateOpenAPI(): array
    {
        $openapi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'APS Dream Home API',
                'version' => '1.0.0',
                'description' => 'Comprehensive API for APS Dream Home real estate platform'
            ],
            'servers' => [
                [
                    'url' => $this->baseUrl,
                    'description' => 'Development server'
                ]
            ],
            'paths' => []
        ];

        foreach ($this->apiEndpoints as $path => $endpoint) {
            $fullPath = '/api/' . $path;

            $openapi['paths'][$fullPath] = [
                strtolower($endpoint['method']) => [
                    'summary' => $endpoint['description'],
                    'parameters' => array_map(function($param) {
                        return [
                            'name' => $param['name'],
                            'in' => 'query',
                            'required' => $param['required'],
                            'schema' => [
                                'type' => $param['type']
                            ],
                            'description' => $param['description']
                        ];
                    }, $endpoint['parameters']),
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                            'content' => [
                                'application/json' => [
                                    'example' => $endpoint['responses']['success'] ?? []
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $openapi;
    }

    /**
     * Generate HTML documentation
     */
    public function generateHTML(): string
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home API Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .endpoint { border: 1px solid #ddd; margin: 20px 0; padding: 20px; border-radius: 5px; }
        .method { font-weight: bold; color: #007bff; }
        .path { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .description { margin: 10px 0; }
        .parameters { margin: 15px 0; }
        .param { margin: 5px 0; padding: 5px; background: #f8f9fa; border-left: 3px solid #007bff; }
        .response { background: #e9ecef; padding: 10px; margin: 10px 0; border-radius: 3px; font-family: monospace; font-size: 12px; }
        h1, h2 { color: #333; }
        .tag { background: #28a745; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <h1><span class="tag">API</span> APS Dream Home API Documentation</h1>
    <p>Comprehensive API documentation for the APS Dream Home real estate platform.</p>
    <p><strong>Base URL:</strong> <code>' . $this->baseUrl . '</code></p>';

        foreach ($this->apiEndpoints as $path => $endpoint) {
            $html .= '
    <div class="endpoint">
        <h3><span class="method">' . strtoupper($endpoint['method']) . '</span> <span class="path">/api/' . $path . '</span></h3>
        <div class="description">' . htmlspecialchars($endpoint['description']) . '</div>

        <div class="parameters">
            <strong>Parameters:</strong>';

            if (empty($endpoint['parameters'])) {
                $html .= '<br><em>None required</em>';
            } else {
                foreach ($endpoint['parameters'] as $param) {
                    $required = $param['required'] ? '<span class="tag">REQUIRED</span>' : '<span class="tag" style="background: #ffc107; color: black;">OPTIONAL</span>';
                    $html .= '
            <div class="param">
                <strong>' . $param['name'] . '</strong> (' . $param['type'] . ') ' . $required . '<br>
                ' . htmlspecialchars($param['description']) . '
            </div>';
                }
            }

            $html .= '
        </div>';

            if (!empty($endpoint['responses'])) {
                $html .= '
        <div class="parameters">
            <strong>Response Example:</strong>
            <div class="response">' . htmlspecialchars(json_encode($endpoint['responses']['success'] ?? [], JSON_PRETTY_PRINT)) . '</div>
        </div>';
            }

            $html .= '
    </div>';
        }

        $html .= '
</body>
</html>';

        return $html;
    }

    /**
     * Save documentation to files
     */
    public function saveDocumentation(): void
    {
        // Create docs/api directory if it doesn't exist
        $docsDir = __DIR__ . '/../../docs/api/';
        if (!is_dir($docsDir)) {
            mkdir($docsDir, 0755, true);
        }

        // Save OpenAPI spec
        file_put_contents(
            $docsDir . 'openapi.json',
            json_encode($this->generateOpenAPI(), JSON_PRETTY_PRINT)
        );

        // Save HTML documentation
        file_put_contents(
            $docsDir . 'index.html',
            $this->generateHTML()
        );

        // Save Markdown documentation
        file_put_contents(
            $docsDir . 'README.md',
            $this->generateMarkdown()
        );
    }

    /**
     * Generate Markdown documentation
     */
    private function generateMarkdown(): string
    {
        $markdown = "# APS Dream Home API Documentation\n\n";
        $markdown .= "Comprehensive API documentation for the APS Dream Home real estate platform.\n\n";
        $markdown .= "**Base URL:** `{$this->baseUrl}`\n\n";

        foreach ($this->apiEndpoints as $path => $endpoint) {
            $markdown .= "## " . strtoupper($endpoint['method']) . " `/api/{$path}`\n\n";
            $markdown .= "{$endpoint['description']}\n\n";

            if (!empty($endpoint['parameters'])) {
                $markdown .= "### Parameters\n\n";
                foreach ($endpoint['parameters'] as $param) {
                    $required = $param['required'] ? ' (Required)' : ' (Optional)';
                    $markdown .= "- `{$param['name']}` ({$param['type']})$required - {$param['description']}\n";
                }
                $markdown .= "\n";
            }

            if (!empty($endpoint['responses'])) {
                $markdown .= "### Response Example\n\n";
                $markdown .= "```json\n" . json_encode($endpoint['responses']['success'] ?? [], JSON_PRETTY_PRINT) . "\n```\n\n";
            }

            $markdown .= "---\n\n";
        }

        return $markdown;
    }

    /**
     * Get all API endpoints
     */
    public function getEndpoints(): array
    {
        return $this->apiEndpoints;
    }

    /**
     * Get API statistics
     */
    public function getStats(): array
    {
        return [
            'total_endpoints' => count($this->apiEndpoints),
            'methods' => array_count_values(array_column($this->apiEndpoints, 'method')),
            'categories' => $this->categorizeEndpoints()
        ];
    }

    /**
     * Categorize endpoints by functionality
     */
    private function categorizeEndpoints(): array
    {
        $categories = [];

        foreach ($this->apiEndpoints as $path => $endpoint) {
            $category = $this->determineCategory($path);

            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
            $categories[$category]++;
        }

        return $categories;
    }

    /**
     * Determine category for endpoint
     */
    private function determineCategory(string $path): string
    {
        if (strpos($path, 'property') !== false) return 'Properties';
        if (strpos($path, 'auth') !== false) return 'Authentication';
        if (strpos($path, 'user') !== false) return 'Users';
        if (strpos($path, 'lead') !== false) return 'Leads';
        if (strpos($path, 'search') !== false) return 'Search';
        if (strpos($path, 'analytics') !== false) return 'Analytics';
        if (strpos($path, 'notification') !== false) return 'Notifications';

        return 'General';
    }
}

// Global function to generate API documentation
function generate_api_docs(): ApiDocumentation
{
    $apiDocs = new ApiDocumentation();
    $apiDocs->saveDocumentation();

    return $apiDocs;
}

?>
