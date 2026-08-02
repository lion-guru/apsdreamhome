<?php

namespace App\Services;

/**
 * Auto-generates OpenAPI 3.0 specs by parsing routes/api.php + routes/web.php.
 * Used by both the admin UI page and the JSON spec endpoint.
 */
class ApiDocService
{
    private const GROUP_TAGS = [
        'auth'             => 'Authentication',
        'login'            => 'Authentication',
        'logout'           => 'Authentication',
        'register'         => 'Authentication',
        'me'               => 'Authentication',
        'refresh'          => 'Authentication',
        'mobile'           => 'Mobile API',
        'properties'       => 'Properties',
        'bookings'         => 'Bookings',
        'leads'            => 'Leads & CRM',
        'inquiry'          => 'Leads & CRM',
        'campaigns'        => 'Marketing',
        'newsletter'       => 'Marketing',
        'subscribe'        => 'Marketing',
        'saving'           => 'Marketing',
        'sms'              => 'Communications',
        'email'            => 'Communications',
        'whatsapp'         => 'Communications',
        'payment'          => 'Payments',
        'webhook'          => 'Webhooks',
        'twilio'           => 'Voice / Twilio',
        'razorpay'         => 'Payments',
        'storage'          => 'Storage',
        'ai'               => 'AI',
        'gemini'           => 'AI',
        'chatbot'          => 'AI',
        'assistant'        => 'AI',
        'search'           => 'Search',
        'mlm'              => 'MLM',
        'voice'            => 'Voice',
        'voice-agent'      => 'Voice / Twilio',
        'workflow'         => 'Workflow',
        'async'            => 'Async Tasks',
        'monitor'          => 'System',
        'work'             => 'System',
        'work-distribution'=> 'HR / Work',
        'audit'            => 'System',
        'analytics'        => 'Analytics',
        'export'           => 'Export',
        'notification'     => 'Notifications',
        'referral'         => 'Referrals',
        'profile'          => 'User Profile',
        'dashboard'        => 'Dashboard',
        'location'         => 'Locations',
        'locations'        => 'Locations',
        'valuation'        => 'AI Valuation',
        'sharing'          => 'Sharing',
        'doc'              => 'Documentation',
        'docs'             => 'Documentation',
        'push'             => 'Push Notifications',
    ];

    private const METHOD_MAP = [
        'get'    => 'GET',
        'post'   => 'POST',
        'put'    => 'PUT',
        'delete' => 'DELETE',
        'patch'  => 'PATCH',
        'any'    => 'GET',
    ];

    /**
     * Generate the complete OpenAPI 3.0 spec array.
     */
    public function generateSpec(string $version = 'v2'): array
    {
        $routes = $this->parseRoutes();
        $endpoints = [];
        foreach ($routes as $r) {
            $endpoints[] = $this->buildEndpoint($r['method'], $r['path'], $r['handler']);
        }

        return [
            'openapi' => '3.0.3',
            'info'    => [
                'title'       => 'APS Dream Home API',
                'description' => "Auto-generated OpenAPI 3.0 spec ({$version}). Parsed from routes/api.php and routes/web.php. " .
                                 "All /api/* routes are included. Authentication: session cookie (admin) or Bearer token (mobile).",
                'version'     => $version === 'v2' ? '2.0.0' : '1.0.0',
                'contact'     => [
                    'name'  => 'APS Dream Home Engineering',
                    'email' => 'dev@apsdreamhome.com',
                    'url'   => 'https://apsdreamhome.com',
                ],
            ],
            'servers' => [
                ['url' => BASE_URL, 'description' => 'Current host'],
                ['url' => 'http://localhost/apsdreamhome', 'description' => 'Local XAMPP'],
            ],
            'tags'     => $this->collectTags($endpoints),
            'paths'    => $this->groupByPath($endpoints),
            'components' => [
                'schemas' => [
                    'ApiResponse' => [
                        'type'       => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data'    => ['type' => 'object', 'additionalProperties' => true],
                            'message' => ['type' => 'string', 'nullable' => true],
                        ],
                        'required' => ['success'],
                    ],
                    'ApiError' => [
                        'type'       => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => false],
                            'error'   => ['type' => 'string', 'example' => 'Invalid request'],
                            'message' => ['type' => 'string'],
                        ],
                        'required' => ['success'],
                    ],
                    'PaginatedResponse' => [
                        'type'       => 'object',
                        'properties' => [
                            'success'    => ['type' => 'boolean', 'example' => true],
                            'data'       => ['type' => 'array', 'items' => ['type' => 'object']],
                            'total'      => ['type' => 'integer', 'example' => 42],
                            'page'       => ['type' => 'integer', 'example' => 1],
                            'per_page'   => ['type' => 'integer', 'example' => 20],
                        ],
                    ],
                ],
                'securitySchemes' => [
                    'sessionCookie' => [
                        'type'        => 'apiKey',
                        'in'          => 'cookie',
                        'name'        => 'PHPSESSID',
                        'description' => 'PHP session cookie (admin panel login).',
                    ],
                    'bearerAuth' => [
                        'type'        => 'http',
                        'scheme'      => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'JWT token from /api/mobile/auth/login.',
                    ],
                    'csrfToken' => [
                        'type'        => 'apiKey',
                        'in'          => 'header',
                        'name'        => 'X-CSRF-Token',
                        'description' => 'CSRF token (admin POST requests).',
                    ],
                ],
            ],
        ];
    }

    /**
     * Return a structured list of all endpoints grouped by category.
     */
    public function getEndpoints(): array
    {
        $routes = $this->parseRoutes();
        $groups = [];
        foreach ($routes as $r) {
            $tag = $this->resolveTag($r['path']);
            if (!isset($groups[$tag])) {
                $groups[$tag] = [];
            }
            $groups[$tag][] = [
                'method'       => $r['method'],
                'path'         => $r['path'],
                'handler'      => $r['handler'],
                'auth_required' => $this->requiresAuth($r['path'], $r['handler']),
            ];
        }
        ksort($groups);
        return $groups;
    }

    /**
     * Export spec as JSON string.
     */
    public function exportSpec(string $version = 'v2'): string
    {
        return json_encode($this->generateSpec($version), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Parse routes/api.php + routes/web.php and extract /api/* routes.
     */
    private function parseRoutes(): array
    {
        $files = [
            APS_ROOT . '/routes/api.php',
            APS_ROOT . '/routes/web.php',
        ];
        $routes = [];
        $seen   = [];

        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            $src = file_get_contents($file);
            // Strip comments to avoid false matches
            $src = preg_replace('#/\*.*?\*/#s', '', $src);
            $src = preg_replace('#//[^\n]*#', '', $src);

            if (preg_match_all(
                '/\$router->(get|post|put|delete|patch|any)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^\)]+)\)/i',
                $src, $m, PREG_SET_ORDER
            )) {
                foreach ($m as $row) {
                    $call    = strtolower($row[1]);
                    $path    = $row[2];
                    $handler = trim($row[3]);
                    if (strpos($path, '/api') !== 0) continue;
                    $method = self::METHOD_MAP[$call] ?? strtoupper($call);

                    if ($call === 'any') {
                        foreach (['GET', 'POST'] as $mm) {
                            $key = $mm . ' ' . $path;
                            if (isset($seen[$key])) continue;
                            $seen[$key] = true;
                            $routes[] = ['method' => $mm, 'path' => $path, 'handler' => $handler];
                        }
                    } else {
                        $key = $method . ' ' . $path;
                        if (isset($seen[$key])) continue;
                        $seen[$key] = true;
                        $routes[] = ['method' => $method, 'path' => $path, 'handler' => $handler];
                    }
                }
            }
        }
        return $routes;
    }

    private function buildEndpoint(string $method, string $path, string $handler): array
    {
        $tag    = $this->resolveTag($path);
        $params = $this->extractParams($path);
        $op     = [
            'tags'        => [$tag],
            'summary'     => $this->resolveSummary($method, $path),
            'description' => "Handler: `{$handler}`",
            'operationId' => $this->makeOperationId($method, $path),
            'responses'   => [
                '200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiResponse']]]],
                '401' => ['description' => 'Unauthenticated'],
                '403' => ['description' => 'Forbidden'],
                '500' => ['description' => 'Server error'],
            ],
        ];

        if ($this->requiresAuth($path, $handler)) {
            $op['security'] = [['bearerAuth' => []], ['sessionCookie' => []]];
        }

        if (!empty($params)) {
            $op['parameters'] = array_map(fn($p) => [
                'name'     => $p,
                'in'       => 'path',
                'required' => true,
                'schema'   => ['type' => 'string', 'example' => '1'],
            ], $params);
        }

        // Query parameters for common GET list endpoints
        if ($method === 'GET' && !str_contains($path, '{')) {
            $last = basename(rtrim($path, '/'));
            if (in_array($last, ['properties', 'leads', 'users', 'bookings', 'inquiries', 'bookings', 'tasks', 'payouts'])) {
                $op['parameters'][] = [
                    ['name' => 'page',  'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 20]],
                ];
                $op['parameters'] = array_merge(...$op['parameters']);
            }
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $op['requestBody'] = [
                'required' => false,
                'content'  => [
                    'application/json'               => ['schema' => ['type' => 'object', 'additionalProperties' => true]],
                    'application/x-www-form-urlencoded' => ['schema' => ['type' => 'object', 'additionalProperties' => true]],
                ],
            ];
        }

        return ['method' => $method, 'path' => $path, 'tags' => [$tag], 'op' => $op];
    }

    private function groupByPath(array $endpoints): array
    {
        $out = [];
        foreach ($endpoints as $e) {
            $out[$e['path']][$e['method']] = $e['op'];
        }
        return $out;
    }

    private function collectTags(array $endpoints): array
    {
        $tags = [];
        foreach ($endpoints as $e) {
            foreach ($e['tags'] as $t) {
                $tags[$t] = $t;
            }
        }
        return array_values(array_unique($tags));
    }

    private function extractParams(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $m);
        return $m[1] ?? [];
    }

    private function makeOperationId(string $method, string $path): string
    {
        $id = strtolower($method) . '_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $path);
        return trim($id, '_');
    }

    private function resolveTag(string $path): string
    {
        foreach (self::GROUP_TAGS as $needle => $tag) {
            if (str_contains($path, '/' . $needle) || str_contains($path, $needle)) {
                return $tag;
            }
        }
        $parts = array_values(array_filter(explode('/', $path)));
        array_shift($parts); // remove 'api'
        $first = $parts[0] ?? 'Other';
        // Remove version prefix
        if (preg_match('/^v\d+$/', $first)) {
            $first = $parts[1] ?? 'Other';
        }
        return ucfirst($first);
    }

    private function resolveSummary(string $method, string $path): string
    {
        $verbs = [
            'GET'    => 'Retrieve',
            'POST'   => 'Create / trigger',
            'PUT'    => 'Update',
            'DELETE' => 'Delete',
            'PATCH'  => 'Patch',
        ];
        $last = basename(rtrim($path, '/'));
        $verb = $verbs[$method] ?? $method;

        if ($method === 'GET' && preg_match('/^\{[^}]+\}$/', $last)) {
            $verb = 'Retrieve single';
        }
        return "{$verb} " . $this->humanizePath($path);
    }

    private function humanizePath(string $path): string
    {
        // Remove /api prefix and version for display
        $p = preg_replace('#^/api(/v\d+)?#', '', $path);
        $p = preg_replace('#/\{[^}]+\}#', '', $p);
        $p = trim($p, '/');
        $parts = array_filter(explode('/', $p));
        return $parts ? implode(' / ', array_map(fn($s) => ucfirst($s), $parts)) : 'Root';
    }

    private function requiresAuth(string $path, string $handler): bool
    {
        // Public endpoints that don't need auth
        $public = ['/api/health', '/api/properties', '/api/contact', '/api/newsletter',
                    '/api/property-inquiry', '/api/locations', '/api/payment/methods',
                    '/api/docs', '/api/gemini/test', '/api/gemini/status',
                    '/api/monitor/health'];
        foreach ($public as $p) {
            if ($path === $p || str_starts_with($path, $p)) return false;
        }
        // Check for middleware indicator in handler
        if (str_contains($handler, 'ApiAuthMiddleware') || str_contains($handler, 'middleware')) return true;
        // Mobile and auth endpoints usually need auth
        if (str_contains($path, '/mobile/') || str_contains($path, '/v2/mobile/')) return true;
        return false;
    }
}
