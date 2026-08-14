<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Router;

/**
 * API documentation controller.
 *
 * Exposes:
 *   GET /api/docs         â€” Swagger UI
 *   GET /api/docs/spec    â€” Auto-generated OpenAPI 3.0 spec (JSON)
 *   GET /api/docs/list    â€” Lightweight route catalog
 *
 * Spec is generated at request time by introspecting the live Router,
 * so adding a new route to routes/api.php is the only step required
 * to surface it in the docs.
 */
class DocsController extends BaseController
{
    /** Methods we include in the OpenAPI spec. */
    private const METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

    /** Group â†’ tag map for nicer Swagger UI grouping. */
    private const GROUP_TAGS = [
        'auth'        => 'Authentication',
        'mobile'      => 'Mobile API',
        'properties'  => 'Properties',
        'leads'       => 'Leads & CRM',
        'campaigns'   => 'Marketing',
        'sms'         => 'Communications',
        'email'       => 'Communications',
        'whatsapp'    => 'Communications',
        'payment'     => 'Payments',
        'webhook'     => 'Webhooks',
        'twilio'      => 'Voice / Twilio',
        'razorpay'    => 'Payments',
        'storage'     => 'Storage',
        'ai'          => 'AI',
        'gemini'      => 'AI',
        'chatbot'     => 'AI',
        'search'      => 'Search',
        'mlm'         => 'MLM',
        'voice'       => 'Voice',
        'workflow'    => 'Workflow',
        'async'       => 'Async',
        'monitor'     => 'System',
        'work'        => 'System',
        'work-distribution' => 'HR / Work',
        'audit'       => 'System',
        'analytics'   => 'Analytics',
        'export'      => 'Export',
        'newsletter'  => 'Marketing',
        'cron'        => 'System',
        'feedback'    => 'Feedback',
        'support'     => 'Support',
        'inquiry'     => 'Leads & CRM',
        'subscribe'   => 'Marketing',
        'saving'      => 'Marketing',
        'share'       => 'Marketing',
        'social'      => 'Marketing',
        'voice-agent' => 'Voice / Twilio',
        'auth'        => 'Authentication',
        'login'       => 'Authentication',
        'register'    => 'Authentication',
        'logout'      => 'Authentication',
        'me'          => 'Authentication',
        'refresh'     => 'Authentication',
        'users'       => 'Users',
        'list'        => 'Properties',
        'detail'      => 'Properties',
        'add'         => 'Properties',
        'remove'      => 'Properties',
        'clear'       => 'Properties',
        'tracking'    => 'Properties',
        'leads'       => 'Leads & CRM',
        'sms'         => 'Communications',
    ];

    /**
     * GET /api/docs
     * Renders Swagger UI.
     */
    public function index()
    {
        $base = BASE_URL;
        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderSwaggerUi($base . '/api/docs/spec');
        exit;
    }

    /**
     * GET /api/docs/spec
     * Returns OpenAPI 3.0 JSON spec.
     */
    public function spec()
    {
        $spec = $this->buildSpec();
        header('Content-Type: application/json');
        echo json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * GET /api/docs/spec/{version}
     * Returns versioned OpenAPI 3.0 JSON spec (v1 or v2).
     */
    public function specVersion($version = 'v2')
    {
        $version = in_array($version, ['v1', 'v2']) ? $version : 'v2';
        $docService = new \App\Services\ApiDocService();
        $spec = $docService->generateSpec($version);
        header('Content-Type: application/json');
        echo json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * GET /api/docs/list
     * Lightweight route catalog (no schema, smaller payload).
     */
    public function list()
    {
        $list = [];
        foreach ($this->getApiRoutes() as $r) {
            $list[] = [
                'method'  => $r['method'],
                'path'    => $r['path'],
                'handler' => $r['handler'],
                'tag'     => $this->resolveTag($r['path']),
            ];
        }
        usort($list, fn($a, $b) => strcmp($a['path'] . $a['method'], $b['path'] . $b['method']));
        $this->jsonResponse(['success' => true, 'count' => count($list), 'routes' => $list]);
    }

    // ---- internals ----

    private function getRouter(): ?\Router
    {
        if (class_exists('\App\Core\Router')) {
            return new \App\Core\Router();
        }
        if (class_exists('\Router')) {
            return new \Router();
        }
        return null;
    }

    /**
     * Parse routes/api.php and routes/web.php directly to extract /api/* route
     * patterns, methods, and handler names. This works because the live
     * Router instance isn't reachable from a controller (it's a local in
     * public/index.php), and creating a fresh Router() gives an empty one.
     */
    private function getApiRoutes(): array
    {
        $routes = [];
        $files = [
            APS_ROOT . '/routes/api.php',
            APS_ROOT . '/routes/web.php',
        ];
        $methodMap = [
            'get' => 'GET',    'post' => 'POST',
            'put' => 'PUT',    'delete' => 'DELETE',
            'patch' => 'PATCH','any' => 'GET',
        ];
        $seen = [];
        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            $src = file_get_contents($file);
            // Strip block comments and line comments to avoid matching inside them
            $src = preg_replace('#/\*.*?\*/#s', '', $src);
            $src = preg_replace('#//[^\n]*#', '', $src);
            // Match: $router->METHOD('PATH', HANDLER)
            if (preg_match_all(
                '/\$router->(get|post|put|delete|patch|any)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^\)]+)\)/i',
                $src, $m, PREG_SET_ORDER
            )) {
                foreach ($m as $row) {
                    $call = strtolower($row[1]);
                    $path = $row[2];
                    $handler = trim($row[3]);
                    if (strpos($path, '/api') !== 0) continue;
                    $method = $methodMap[$call] ?? strtoupper($call);
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

    private function buildSpec(): array
    {
        $base = BASE_URL;
        $endpoints = [];
        foreach ($this->getApiRoutes() as $r) {
            $endpoints[] = $this->buildEndpoint($r['method'], $r['path'], $r['handler']);
        }
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title'       => 'APS Dream Home API',
                'description' => 'Auto-generated OpenAPI 3.0 specification. Source: routes/api.php and routes/web.php (filtered to /api/*).',
                'version'     => '1.0.0',
                'contact'     => ['name' => 'APS Dream Home Engineering', 'email' => 'dev@apsdreamhome.com'],
            ],
            'servers' => [
                ['url' => $base, 'description' => 'Current host'],
                ['url' => 'http://localhost/apsdreamhome', 'description' => 'Local XAMPP'],
            ],
            'tags' => array_values(array_unique(array_map(fn($e) => $e['tags'][0] ?? 'Other', $endpoints))),
            'paths' => $this->groupByPath($endpoints),
            'components' => $this->buildComponents(),
        ];
    }

    private function buildEndpoint(string $method, string $path, $handler): array
    {
        $tag = $this->resolveTag($path);
        $summary = $this->resolveSummary($method, $path);
        $params = $this->extractParams($path);
        $op = [
            'tags'        => [$tag],
            'summary'     => $summary,
            'description' => "Handler: `$handler`",
            'operationId' => $this->makeOperationId($method, $path),
            'responses'   => [
                '200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiResponse']]]],
                '401' => ['description' => 'Unauthenticated', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]]],
                '403' => ['description' => 'Forbidden (CSRF / auth)', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]]],
                '500' => ['description' => 'Server error', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiError']]]],
            ],
        ];
        if (!empty($params)) {
            $op['parameters'] = array_map(fn($p) => [
                'name' => $p, 'in' => 'path', 'required' => true,
                'schema' => ['type' => 'string', 'example' => '1'],
            ], $params);
        }
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $op['requestBody'] = [
                'required' => false,
                'content'  => [
                    'application/json' => ['schema' => ['type' => 'object', 'additionalProperties' => true]],
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
            if (strpos($path, '/' . $needle) !== false || strpos($path, $needle) !== false) {
                return $tag;
            }
        }
        // Fall back to the first non-empty path segment after /api/
        $parts = array_values(array_filter(explode('/', $path)));
        // remove 'api'
        array_shift($parts);
        return ucfirst($parts[0] ?? 'Other');
    }

    private function resolveSummary(string $method, string $path): string
    {
        $verbs = ['GET' => 'Retrieve', 'POST' => 'Create / trigger', 'PUT' => 'Update', 'DELETE' => 'Delete', 'PATCH' => 'Patch'];
        $last = basename(rtrim($path, '/'));
        $verb = $verbs[$method] ?? $method;
        if ($method === 'GET' && preg_match('/^\{[^}]+\}$/', $last)) {
            $verb = 'Retrieve single';
        }
        return "$verb $path";
    }

    private function buildComponents(): array
    {
        return [
            'schemas' => [
                'ApiResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'example' => true],
                        'data'    => ['type' => 'object', 'additionalProperties' => true],
                        'message' => ['type' => 'string', 'nullable' => true],
                    ],
                    'required' => ['success'],
                ],
                'ApiError' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'example' => false],
                        'error'   => ['type' => 'string', 'example' => 'Invalid CSRF token'],
                        'message' => ['type' => 'string'],
                    ],
                    'required' => ['success'],
                ],
            ],
            'securitySchemes' => [
                'sessionCookie' => [
                    'type' => 'apiKey', 'in' => 'cookie', 'name' => 'PHPSESSID',
                    'description' => 'PHP session cookie (set by /admin/login or /login).',
                ],
                'csrfToken' => [
                    'type' => 'apiKey', 'in' => 'header', 'name' => 'X-CSRF-Token',
                    'description' => 'CSRF token in either X-CSRF-Token header or `csrf_token` POST field.',
                ],
            ],
        ];
    }

    private function renderSwaggerUi(string $specUrl): string
    {
        $specUrlEsc = htmlspecialchars($specUrl, ENT_QUOTES);
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>APS Dream Home API Docs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.min.css">
    <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: "$specUrlEsc",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis],
                layout: 'BaseLayout',
                tryItOutEnabled: true,
            });
        };
    </script>
</body>
</html>
HTML;
    }
}
