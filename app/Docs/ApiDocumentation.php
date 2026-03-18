<?php

namespace App\Docs;

use App\Core\Database;
use App\Core\App;
use Exception;

/**
 * API Documentation Generator
 * Generates comprehensive API documentation
 */
class ApiDocumentation
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = App::getInstance();
    }

    /**
     * Generate complete API documentation
     * @return array Documentation data
     */
    public function generateDocumentation()
    {
        try {
            $endpoints = $this->getApiEndpoints();
            $schemas = $this->getApiSchemas();
            $examples = $this->getApiExamples();

            return [
                'success' => true,
                'documentation' => [
                    'title' => 'APS Dream Home API Documentation',
                    'version' => '1.0.0',
                    'description' => 'Comprehensive RESTful API for APS Dream Home platform',
                    'base_url' => $this->config->get('app_url', 'http://localhost/apsdreamhome') . '/api',
                    'endpoints' => $endpoints,
                    'schemas' => $schemas,
                    'examples' => $examples,
                    'authentication' => [
                        'type' => 'API Key Authentication',
                        'description' => 'Include X-API-Key header in all requests',
                        'header_name' => 'X-API-Key',
                        'example' => 'X-API-Key: your-api-key-here'
                    ],
                    'response_formats' => [
                        'success' => [
                            'status' => 'success',
                            'data' => 'response_data'
                        ],
                        'error' => [
                            'status' => 'error',
                            'message' => 'error_message',
                            'code' => 'error_code'
                        ]
                    ],
                    'rate_limiting' => [
                        'requests_per_minute' => 60,
                        'requests_per_hour' => 1000,
                        'headers' => [
                            'X-RateLimit-Limit' => 'limit',
                            'X-RateLimit-Remaining' => 'remaining',
                            'X-RateLimit-Reset' => 'reset'
                        ]
                    ]
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to generate documentation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all API endpoints
     * @return array Endpoints data
     */
    private function getApiEndpoints()
    {
        return [
            // Authentication endpoints
            'auth' => [
                'POST /api/auth/login' => [
                    'description' => 'User authentication',
                    'parameters' => [
                        'email' => ['type' => 'string', 'required' => true, 'description' => 'User email address'],
                        'password' => ['type' => 'string', 'required' => true, 'description' => 'User password']
                    ],
                    'responses' => [
                        200 => ['description' => 'Authentication successful', 'schema' => 'AuthResponse'],
                        401 => ['description' => 'Authentication failed', 'schema' => 'ErrorResponse']
                    ]
                ],
                'POST /api/auth/register' => [
                    'description' => 'User registration',
                    'parameters' => [
                        'name' => ['type' => 'string', 'required' => true],
                        'email' => ['type' => 'string', 'required' => true],
                        'password' => ['type' => 'string', 'required' => true],
                        'phone' => ['type' => 'string', 'required' => false]
                    ],
                    'responses' => [
                        201 => ['description' => 'Registration successful', 'schema' => 'UserResponse'],
                        400 => ['description' => 'Validation error', 'schema' => 'ErrorResponse']
                    ]
                ]
            ],

            // Property endpoints
            'properties' => [
                'GET /api/properties' => [
                    'description' => 'Get all properties with optional filtering',
                    'parameters' => [
                        'page' => ['type' => 'integer', 'required' => false, 'description' => 'Page number for pagination'],
                        'limit' => ['type' => 'integer', 'required' => false, 'description' => 'Number of properties per page'],
                        'type' => ['type' => 'string', 'required' => false, 'description' => 'Property type filter'],
                        'status' => ['type' => 'string', 'required' => false, 'description' => 'Property status filter'],
                        'min_price' => ['type' => 'number', 'required' => false, 'description' => 'Minimum price filter'],
                        'max_price' => ['type' => 'number', 'required' => false, 'description' => 'Maximum price filter'],
                        'search' => ['type' => 'string', 'required' => false, 'description' => 'Search term for title/description/location']
                    ],
                    'responses' => [
                        200 => ['description' => 'Properties retrieved successfully', 'schema' => 'PropertyListResponse'],
                        400 => ['description' => 'Invalid parameters', 'schema' => 'ErrorResponse']
                    ]
                ],
                'GET /api/properties/{id}' => [
                    'description' => 'Get specific property by ID',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => 'Property ID']
                    ],
                    'responses' => [
                        200 => ['description' => 'Property retrieved successfully', 'schema' => 'PropertyResponse'],
                        404 => ['description' => 'Property not found', 'schema' => 'ErrorResponse']
                    ]
                ]
            ],

            // Banking endpoints
            'banking' => [
                'POST /api/banking/save' => [
                    'description' => 'Save banking details',
                    'parameters' => [
                        'account_number' => ['type' => 'string', 'required' => true],
                        'bank_name' => ['type' => 'string', 'required' => true],
                        'ifsc_code' => ['type' => 'string', 'required' => true],
                        'account_type' => ['type' => 'string', 'required' => true]
                    ],
                    'responses' => [
                        200 => ['description' => 'Banking details saved', 'schema' => 'BankingResponse'],
                        400 => ['description' => 'Validation error', 'schema' => 'ErrorResponse']
                    ]
                ]
            ],

            // Communication endpoints
            'communication' => [
                'POST /api/communication/send-email' => [
                    'description' => 'Send email',
                    'parameters' => [
                        'to' => ['type' => 'string', 'required' => true, 'description' => 'Recipient email address'],
                        'subject' => ['type' => 'string', 'required' => true, 'description' => 'Email subject'],
                        'message' => ['type' => 'string', 'required' => true, 'description' => 'Email message content']
                    ],
                    'responses' => [
                        200 => ['description' => 'Email sent successfully', 'schema' => 'CommunicationResponse'],
                        400 => ['description' => 'Validation error', 'schema' => 'ErrorResponse']
                    ]
                ],
                'POST /api/communication/send-whatsapp' => [
                    'description' => 'Send WhatsApp message',
                    'parameters' => [
                        'to' => ['type' => 'string', 'required' => true, 'description' => 'Recipient phone number'],
                        'message' => ['type' => 'string', 'required' => true, 'description' => 'WhatsApp message content'],
                        'type' => ['type' => 'string', 'required' => false, 'description' => 'Message type (text, template, interactive)']
                    ],
                    'responses' => [
                        200 => ['description' => 'WhatsApp message sent successfully', 'schema' => 'CommunicationResponse'],
                        400 => ['description' => 'Validation error', 'schema' => 'ErrorResponse']
                    ]
                ]
            ]
        ];
    }

    /**
     * Get API data schemas
     * @return array Schema definitions
     */
    private function getApiSchemas()
    {
        return [
            'AuthResponse' => [
                'type' => 'object',
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Authenticated user ID'],
                    'token' => ['type' => 'string', 'description' => 'Authentication token'],
                    'expires_at' => ['type' => 'string', 'format' => 'datetime', 'description' => 'Token expiration time']
                ]
            ],
            'UserResponse' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'User ID'],
                    'name' => ['type' => 'string', 'description' => 'User name'],
                    'email' => ['type' => 'string', 'format' => 'email', 'description' => 'User email'],
                    'phone' => ['type' => 'string', 'description' => 'User phone number'],
                    'role' => ['type' => 'string', 'description' => 'User role'],
                    'created_at' => ['type' => 'string', 'format' => 'datetime', 'description' => 'Account creation time']
                ]
            ],
            'PropertyResponse' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Property ID'],
                    'title' => ['type' => 'string', 'description' => 'Property title'],
                    'description' => ['type' => 'string', 'description' => 'Property description'],
                    'price' => ['type' => 'number', 'description' => 'Property price'],
                    'type' => ['type' => 'string', 'description' => 'Property type'],
                    'location' => ['type' => 'string', 'description' => 'Property location'],
                    'status' => ['type' => 'string', 'description' => 'Property status'],
                    'created_at' => ['type' => 'string', 'format' => 'datetime', 'description' => 'Property creation time'],
                    'images' => ['type' => 'array', 'description' => 'Property images array'],
                    'features' => ['type' => 'array', 'description' => 'Property features array']
                ]
            ],
            'PropertyListResponse' => [
                'type' => 'object',
                'properties' => [
                    'properties' => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Array of Property objects'],
                    'pagination' => ['type' => 'object', 'description' => 'Pagination information']
                ]
            ],
            'BankingResponse' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Banking details ID'],
                    'account_number' => ['type' => 'string', 'description' => 'Bank account number'],
                    'bank_name' => ['type' => 'string', 'description' => 'Bank name'],
                    'ifsc_code' => ['type' => 'string', 'description' => 'IFSC code'],
                    'account_type' => ['type' => 'string', 'description' => 'Account type'],
                    'created_at' => ['type' => 'string', 'format' => 'datetime', 'description' => 'Creation timestamp']
                ]
            ],
            'CommunicationResponse' => [
                'type' => 'object',
                'properties' => [
                    'message_id' => ['type' => 'string', 'description' => 'Unique message identifier'],
                    'status' => ['type' => 'string', 'description' => 'Message status'],
                    'sent_at' => ['type' => 'string', 'format' => 'datetime', 'description' => 'Message sent timestamp']
                ]
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Response status (error/success)'],
                    'message' => ['type' => 'string', 'description' => 'Response message'],
                    'code' => ['type' => 'integer', 'description' => 'HTTP status code']
                ]
            ]
        ];
    }

    /**
     * Get API usage examples
     * @return array Example requests and responses
     */
    private function getApiExamples()
    {
        return [
            'authentication' => [
                'login' => [
                    'request' => [
                        'method' => 'POST',
                        'url' => '/api/auth/login',
                        'headers' => ['Content-Type: application/json', 'X-API-Key: your-api-key-here'],
                        'body' => [
                            'email' => 'user@example.com',
                            'password' => 'userpassword123'
                        ]
                    ],
                    'response' => [
                        'status' => 200,
                        'body' => [
                            'status' => 'success',
                            'data' => [
                                'user_id' => 123,
                                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9c',
                                'expires_at' => '2024-12-31T23:59:59Z'
                            ]
                        ]
                    ]
                ]
            ],
            'properties' => [
                'list' => [
                    'request' => [
                        'method' => 'GET',
                        'url' => '/api/properties?page=1&limit=10&type=apartment',
                        'headers' => ['Content-Type: application/json', 'X-API-Key: your-api-key-here']
                    ],
                    'response' => [
                        'status' => 200,
                        'body' => [
                            'status' => 'success',
                            'data' => [
                                'properties' => [
                                    [
                                        'id' => 1,
                                        'title' => 'Luxury Apartment',
                                        'price' => 250000,
                                        'type' => 'apartment',
                                        'location' => 'Mumbai, India',
                                        'status' => 'active'
                                    ],
                                    [
                                        'id' => 2,
                                        'title' => 'Beach House',
                                        'price' => 180000,
                                        'type' => 'house',
                                        'location' => 'Goa, India',
                                        'status' => 'active'
                                    ]
                                ],
                                'pagination' => [
                                    'current_page' => 1,
                                    'per_page' => 10,
                                    'total' => 2,
                                    'total_pages' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
