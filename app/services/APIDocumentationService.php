<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * API Documentation Service - Swagger/OpenAPI Generator
 * Auto-generates API documentation from routes and controllers
 */
class APIDocumentationService
{
    private $database;
    private $openApiVersion = '3.0.0';
    private $apiInfo = [];
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->apiInfo = [
            'title' => 'APS Dream Home API',
            'description' => 'Real Estate Management API - Properties, Leads, Bookings, MLM',
            'version' => '1.0.0',
            'contact' => [
                'name' => 'APS Dream Home',
                'email' => 'api@apsdreamhome.com',
                'url' => 'https://apsdreamhome.com'
            ],
            'license' => [
                'name' => 'Private',
                'url' => 'https://apsdreamhome.com/license'
            ]
        ];
    }
    
    /**
     * Generate complete OpenAPI specification
     */
    public function generateSpec(): array
    {
        $spec = [
            'openapi' => $this->openApiVersion,
            'info' => $this->apiInfo,
            'servers' => [
                ['url' => 'https://apsdreamhome.com/api', 'description' => 'Production Server'],
                ['url' => (rtrim(BASE_URL, '/')) . '/api', 'description' => 'Local Development']
            ],
            'paths' => $this->generatePaths(),
            'components' => [
                'schemas' => $this->generateSchemas(),
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ],
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key'
                    ]
                ]
            ],
            'security' => [
                ['BearerAuth' => []]
            ],
            'tags' => $this->generateTags()
        ];
        
        return $spec;
    }
    
    /**
     * Generate API paths from routes
     */
    private function generatePaths(): array
    {
        $paths = [];
        
        // Authentication
        $paths['/auth/login'] = [
            'post' => [
                'tags' => ['Authentication'],
                'summary' => 'User Login',
                'description' => 'Authenticate user and get access token',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email'],
                                    'password' => ['type' => 'string', 'format' => 'password']
                                ],
                                'required' => ['email', 'password']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Login successful',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/LoginResponse']
                            ]
                        ]
                    ],
                    '401' => ['description' => 'Invalid credentials']
                ]
            ]
        ];
        
        // Properties
        $paths['/properties'] = [
            'get' => [
                'tags' => ['Properties'],
                'summary' => 'List Properties',
                'description' => 'Get paginated list of properties',
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 20]],
                    ['name' => 'type', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['plot', 'house', 'flat', 'shop']]],
                    ['name' => 'location', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ['name' => 'min_price', 'in' => 'query', 'schema' => ['type' => 'number']],
                    ['name' => 'max_price', 'in' => 'query', 'schema' => ['type' => 'number']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'List of properties',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Property']
                                        ],
                                        'pagination' => ['$ref' => '#/components/schemas/Pagination']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'tags' => ['Properties'],
                'summary' => 'Create Property',
                'security' => [['BearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/PropertyInput']
                        ]
                    ]
                ],
                'responses' => [
                    '201' => ['description' => 'Property created'],
                    '401' => ['description' => 'Unauthorized'],
                    '422' => ['description' => 'Validation error']
                ]
            ]
        ];
        
        $paths['/properties/{id}'] = [
            'get' => [
                'tags' => ['Properties'],
                'summary' => 'Get Property Details',
                'parameters' => [
                    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Property details',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Property']
                            ]
                        ]
                    ],
                    '404' => ['description' => 'Property not found']
                ]
            ],
            'put' => [
                'tags' => ['Properties'],
                'summary' => 'Update Property',
                'security' => [['BearerAuth' => []]],
                'parameters' => [
                    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/PropertyInput']
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Property updated'],
                    '401' => ['description' => 'Unauthorized'],
                    '404' => ['description' => 'Property not found']
                ]
            ],
            'delete' => [
                'tags' => ['Properties'],
                'summary' => 'Delete Property',
                'security' => [['BearerAuth' => []]],
                'parameters' => [
                    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '204' => ['description' => 'Property deleted'],
                    '401' => ['description' => 'Unauthorized'],
                    '404' => ['description' => 'Property not found']
                ]
            ]
        ];
        
        // Leads
        $paths['/leads'] = [
            'get' => [
                'tags' => ['Leads'],
                'summary' => 'List Leads',
                'security' => [['BearerAuth' => []]],
                'parameters' => [
                    ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['new', 'contacted', 'qualified', 'converted', 'lost']]],
                    ['name' => 'source', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ['name' => 'assigned_to', 'in' => 'query', 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'List of leads',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Lead']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'tags' => ['Leads'],
                'summary' => 'Create Lead',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/LeadInput']
                        ]
                    ]
                ],
                'responses' => [
                    '201' => ['description' => 'Lead created successfully']
                ]
            ]
        ];
        
        // Bookings
        $paths['/bookings'] = [
            'get' => [
                'tags' => ['Bookings'],
                'summary' => 'List Bookings',
                'security' => [['BearerAuth' => []]],
                'responses' => [
                    '200' => ['description' => 'List of bookings']
                ]
            ],
            'post' => [
                'tags' => ['Bookings'],
                'summary' => 'Create Booking',
                'security' => [['BearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/BookingInput']
                        ]
                    ]
                ],
                'responses' => [
                    '201' => ['description' => 'Booking created']
                ]
            ]
        ];
        
        // Analytics
        $paths['/analytics/dashboard'] = [
            'get' => [
                'tags' => ['Analytics'],
                'summary' => 'Dashboard Analytics',
                'security' => [['BearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Dashboard statistics',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/DashboardStats']
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // MLM
        $paths['/mlm/network/{associateId}'] = [
            'get' => [
                'tags' => ['MLM'],
                'summary' => 'Get Network Tree',
                'security' => [['BearerAuth' => []]],
                'parameters' => [
                    ['name' => 'associateId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'levels', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 3]]
                ],
                'responses' => [
                    '200' => ['description' => 'Network tree data']
                ]
            ]
        ];
        
        return $paths;
    }
    
    /**
     * Generate schemas
     */
    private function generateSchemas(): array
    {
        return [
            'Property' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'readOnly' => true],
                    'title' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'type' => ['type' => 'string', 'enum' => ['plot', 'house', 'flat', 'shop', 'farmhouse']],
                    'price' => ['type' => 'number'],
                    'area' => ['type' => 'number'],
                    'location' => ['type' => 'string'],
                    'address' => ['type' => 'string'],
                    'amenities' => [
                        'type' => 'array',
                        'items' => ['type' => 'string']
                    ],
                    'images' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'url' => ['type' => 'string'],
                                'category' => ['type' => 'string']
                            ]
                        ]
                    ],
                    'status' => ['type' => 'string', 'enum' => ['available', 'booked', 'sold', 'hold']],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time']
                ],
                'required' => ['title', 'type', 'price', 'location']
            ],
            'PropertyInput' => [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string', 'maxLength' => 200],
                    'description' => ['type' => 'string'],
                    'type' => ['type' => 'string', 'enum' => ['plot', 'house', 'flat', 'shop', 'farmhouse']],
                    'price' => ['type' => 'number', 'minimum' => 0],
                    'area' => ['type' => 'number', 'minimum' => 0],
                    'location' => ['type' => 'string'],
                    'address' => ['type' => 'string'],
                    'amenities' => [
                        'type' => 'array',
                        'items' => ['type' => 'string']
                    ]
                ],
                'required' => ['title', 'type', 'price', 'location']
            ],
            'Lead' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'phone' => ['type' => 'string'],
                    'source' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'budget' => ['type' => 'number'],
                    'property_type' => ['type' => 'string'],
                    'location' => ['type' => 'string'],
                    'assigned_to' => ['type' => 'integer'],
                    'notes' => ['type' => 'string'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time']
                ]
            ],
            'LeadInput' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'phone' => ['type' => 'string'],
                    'source' => ['type' => 'string'],
                    'budget' => ['type' => 'number'],
                    'property_type' => ['type' => 'string'],
                    'location' => ['type' => 'string'],
                    'notes' => ['type' => 'string']
                ],
                'required' => ['name', 'phone']
            ],
            'BookingInput' => [
                'type' => 'object',
                'properties' => [
                    'property_id' => ['type' => 'integer'],
                    'customer_id' => ['type' => 'integer'],
                    'amount' => ['type' => 'number'],
                    'payment_method' => ['type' => 'string'],
                    'notes' => ['type' => 'string']
                ],
                'required' => ['property_id', 'customer_id', 'amount']
            ],
            'LoginResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'token' => ['type' => 'string'],
                    'user' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'role' => ['type' => 'string']
                        ]
                    ],
                    'expires_at' => ['type' => 'string', 'format' => 'date-time']
                ]
            ],
            'DashboardStats' => [
                'type' => 'object',
                'properties' => [
                    'total_properties' => ['type' => 'integer'],
                    'active_listings' => ['type' => 'integer'],
                    'total_leads' => ['type' => 'integer'],
                    'new_leads_today' => ['type' => 'integer'],
                    'total_bookings' => ['type' => 'integer'],
                    'bookings_this_month' => ['type' => 'integer'],
                    'total_revenue' => ['type' => 'number'],
                    'revenue_this_month' => ['type' => 'number'],
                    'conversion_rate' => ['type' => 'number']
                ]
            ],
            'Pagination' => [
                'type' => 'object',
                'properties' => [
                    'current_page' => ['type' => 'integer'],
                    'total_pages' => ['type' => 'integer'],
                    'per_page' => ['type' => 'integer'],
                    'total_items' => ['type' => 'integer'],
                    'has_next' => ['type' => 'boolean'],
                    'has_prev' => ['type' => 'boolean']
                ]
            ],
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'integer'],
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object']
                ]
            ]
        ];
    }
    
    /**
     * Generate tags
     */
    private function generateTags(): array
    {
        return [
            ['name' => 'Authentication', 'description' => 'User login and token management'],
            ['name' => 'Properties', 'description' => 'Property listings and management'],
            ['name' => 'Leads', 'description' => 'Lead management and tracking'],
            ['name' => 'Bookings', 'description' => 'Property bookings and reservations'],
            ['name' => 'users', 'description' => 'Customer management'],
            ['name' => 'users', 'description' => 'Sales associate management'],
            ['name' => 'MLM', 'description' => 'Multi-level marketing network'],
            ['name' => 'Payments', 'description' => 'Payment processing'],
            ['name' => 'Analytics', 'description' => 'Reports and analytics'],
            ['name' => 'Admin', 'description' => 'Administration endpoints']
        ];
    }
    
    /**
     * Export spec to JSON file
     */
    public function exportToFile(string $filepath): bool
    {
        $spec = $this->generateSpec();
        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($filepath, $json) !== false;
    }
    
    /**
     * Generate HTML documentation
     */
    public function generateHtmlDocs(): string
    {
        $spec = $this->generateSpec();
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>' . $spec['info']['title'] . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .endpoint { border-left: 4px solid #007bff; padding-left: 15px; margin: 20px 0; }
        .method-get { border-color: #28a745; }
        .method-post { border-color: #007bff; }
        .method-put { border-color: #ffc107; }
        .method-delete { border-color: #dc3545; }
        .schema { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1>' . $spec['info']['title'] . '</h1>
        <p class="lead">' . $spec['info']['description'] . '</p>
        <p>Version: <strong>' . $spec['info']['version'] . '</strong></p>
        
        <hr>
        
        <h2>Endpoints</h2>';
        
        foreach ($spec['paths'] as $path => $methods) {
            foreach ($methods as $method => $details) {
                $methodClass = 'method-' . $method;
                $html .= '
        <div class="endpoint ' . $methodClass . '">
            <h4><span class="badge bg-secondary">' . strtoupper($method) . '</span> ' . $path . '</h4>
            <p>' . ($details['summary'] ?? '') . '</p>
            <p>' . ($details['description'] ?? '') . '</p>';
                
                if (isset($details['parameters'])) {
                    $html .= '<h6>Parameters:</h6><ul>';
                    foreach ($details['parameters'] as $param) {
                        $html .= '<li><code>' . $param['name'] . '</code> (' . $param['in'] . ') - ' . ($param['schema']['type'] ?? 'string') . '</li>';
                    }
                    $html .= '</ul>';
                }
                
                $html .= '</div>';
            }
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        return $html;
    }
}
