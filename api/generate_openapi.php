<?php
/**
 * APS Dream Home - Complete OpenAPI 3.0 Specification
 *
 * This file contains the complete API documentation in OpenAPI 3.0 format
 * for the APS Dream Home real estate platform.
 */

$openApiSpec = [
    'openapi' => '3.0.3',
    'info' => [
        'title' => 'APS Dream Home API',
        'description' => 'Comprehensive API for the APS Dream Home real estate management platform. This API provides endpoints for property management, user authentication, bookings, commissions, and more.',
        'version' => '1.0.0',
        'contact' => [
            'name' => 'APS Dream Home Support',
            'email' => 'support@apsdreamhome.com',
            'url' => 'https://apsdreamhome.com/support'
        ],
        'license' => [
            'name' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT'
        ]
    ],
    'servers' => [
        [
            'url' => 'https://api.apsdreamhome.com/v1',
            'description' => 'Production server'
        ],
        [
            'url' => 'http://localhost/apsdreamhomefinal/api',
            'description' => 'Development server'
        ]
    ],
    'security' => [
        [
            'ApiKeyAuth' => []
        ],
        [
            'BearerAuth' => []
        ]
    ],
    'components' => [
        'securitySchemes' => [
            'ApiKeyAuth' => [
                'type' => 'apiKey',
                'in' => 'header',
                'name' => 'X-API-Key'
            ],
            'BearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT'
            ]
        ],
        'schemas' => [
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => false
                    ],
                    'error' => [
                        'type' => 'string',
                        'example' => 'Invalid API key'
                    ],
                    'code' => [
                        'type' => 'integer',
                        'example' => 401
                    ]
                ]
            ],
            'Property' => [
                'type' => 'object',
                'properties' => [
                    'pid' => [
                        'type' => 'integer',
                        'description' => 'Property ID'
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Property title'
                    ],
                    'pcontent' => [
                        'type' => 'string',
                        'description' => 'Property description'
                    ],
                    'type' => [
                        'type' => 'string',
                        'enum' => ['house', 'apartment', 'commercial', 'plot'],
                        'description' => 'Property type'
                    ],
                    'bhk' => [
                        'type' => 'string',
                        'description' => 'BHK configuration'
                    ],
                    'bedroom' => [
                        'type' => 'integer',
                        'description' => 'Number of bedrooms'
                    ],
                    'bathroom' => [
                        'type' => 'integer',
                        'description' => 'Number of bathrooms'
                    ],
                    'balcony' => [
                        'type' => 'integer',
                        'description' => 'Number of balconies'
                    ],
                    'price' => [
                        'type' => 'number',
                        'description' => 'Property price'
                    ],
                    'location' => [
                        'type' => 'string',
                        'description' => 'Property location'
                    ],
                    'city' => [
                        'type' => 'string',
                        'description' => 'City name'
                    ],
                    'state' => [
                        'type' => 'string',
                        'description' => 'State name'
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['available', 'sold', 'rented'],
                        'description' => 'Property status'
                    ],
                    'pimage' => [
                        'type' => 'string',
                        'description' => 'Main property image URL'
                    ],
                    'isFeatured' => [
                        'type' => 'integer',
                        'description' => 'Featured property flag'
                    ],
                    'date' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => 'Property creation date'
                    ]
                ]
            ],
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'User ID'
                    ],
                    'name' => [
                        'type' => 'string',
                        'description' => 'User name'
                    ],
                    'email' => [
                        'type' => 'string',
                        'format' => 'email',
                        'description' => 'User email'
                    ],
                    'phone' => [
                        'type' => 'string',
                        'description' => 'User phone number'
                    ],
                    'utype' => [
                        'type' => 'string',
                        'enum' => ['user', 'agent', 'builder', 'admin'],
                        'description' => 'User type'
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'inactive'],
                        'description' => 'User status'
                    ],
                    'created_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => 'Account creation date'
                    ],
                    'last_login' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => 'Last login date'
                    ]
                ]
            ],
            'Booking' => [
                'type' => 'object',
                'properties' => [
                    'booking_id' => [
                        'type' => 'integer',
                        'description' => 'Booking ID'
                    ],
                    'customer_name' => [
                        'type' => 'string',
                        'description' => 'Customer name'
                    ],
                    'customer_email' => [
                        'type' => 'string',
                        'format' => 'email',
                        'description' => 'Customer email'
                    ],
                    'customer_phone' => [
                        'type' => 'string',
                        'description' => 'Customer phone'
                    ],
                    'property_id' => [
                        'type' => 'integer',
                        'description' => 'Property ID'
                    ],
                    'amount' => [
                        'type' => 'number',
                        'description' => 'Booking amount'
                    ],
                    'paid_amount' => [
                        'type' => 'number',
                        'description' => 'Paid amount'
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'confirmed', 'cancelled', 'completed'],
                        'description' => 'Booking status'
                    ],
                    'payment_status' => [
                        'type' => 'string',
                        'enum' => ['unpaid', 'partial', 'paid'],
                        'description' => 'Payment status'
                    ],
                    'booking_date' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => 'Booking date'
                    ]
                ]
            ],
            'Associate' => [
                'type' => 'object',
                'properties' => [
                    'associate_id' => [
                        'type' => 'integer',
                        'description' => 'Associate ID'
                    ],
                    'uid' => [
                        'type' => 'string',
                        'description' => 'Associate unique ID'
                    ],
                    'user_id' => [
                        'type' => 'integer',
                        'description' => 'User ID'
                    ],
                    'referral_code' => [
                        'type' => 'string',
                        'description' => 'Referral code'
                    ],
                    'level' => [
                        'type' => 'integer',
                        'description' => 'Associate level'
                    ],
                    'total_business' => [
                        'type' => 'number',
                        'description' => 'Total business amount'
                    ],
                    'current_month_business' => [
                        'type' => 'number',
                        'description' => 'Current month business'
                    ],
                    'team_business' => [
                        'type' => 'number',
                        'description' => 'Team business amount'
                    ],
                    'created_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                        'description' => 'Creation date'
                    ]
                ]
            ]
        ],
        'parameters' => [
            'PropertyId' => [
                'name' => 'id',
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'integer'
                ],
                'description' => 'Property ID'
            ],
            'UserId' => [
                'name' => 'id',
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'integer'
                ],
                'description' => 'User ID'
            ],
            'Page' => [
                'name' => 'page',
                'in' => 'query',
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'default' => 1
                ],
                'description' => 'Page number for pagination'
            ],
            'Limit' => [
                'name' => 'limit',
                'in' => 'query',
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default' => 10
                ],
                'description' => 'Number of items per page'
            ],
            'ApiKey' => [
                'name' => 'X-API-Key',
                'in' => 'header',
                'required' => true,
                'schema' => [
                    'type' => 'string'
                ],
                'description' => 'API key for authentication'
            ]
        ]
    ],
    'paths' => [
        '/properties' => [
            'get' => [
                'summary' => 'Get properties list',
                'description' => 'Retrieve a paginated list of properties with optional filtering',
                'security' => [
                    ['ApiKeyAuth' => []]
                ],
                'parameters' => [
                    ['$ref' => '#/components/parameters/Page'],
                    ['$ref' => '#/components/parameters/Limit'],
                    [
                        'name' => 'type',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['house', 'apartment', 'commercial', 'plot']
                        ],
                        'description' => 'Filter by property type'
                    ],
                    [
                        'name' => 'city',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string'
                        ],
                        'description' => 'Filter by city'
                    ],
                    [
                        'name' => 'min_price',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'number'
                        ],
                        'description' => 'Minimum price filter'
                    ],
                    [
                        'name' => 'max_price',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'number'
                        ],
                        'description' => 'Maximum price filter'
                    ],
                    [
                        'name' => 'featured',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'boolean'
                        ],
                        'description' => 'Show only featured properties'
                    ],
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['available', 'sold', 'rented']
                        ],
                        'description' => 'Filter by property status'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => [
                                            'type' => 'boolean',
                                            'example' => true
                                        ],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => [
                                                '$ref' => '#/components/schemas/Property'
                                            ]
                                        ],
                                        'pagination' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'current_page' => ['type' => 'integer'],
                                                'total_pages' => ['type' => 'integer'],
                                                'total_items' => ['type' => 'integer'],
                                                'items_per_page' => ['type' => 'integer']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Bad request',
                        '$ref' => '#/components/schemas/Error'
                    ],
                    '401' => [
                        'description' => 'Unauthorized',
                        '$ref' => '#/components/schemas/Error'
                    ]
                ]
            ],
            'post' => [
                'summary' => 'Create new property',
                'description' => 'Create a new property listing',
                'security' => [
                    ['BearerAuth' => []]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Property'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Property created successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => ['$ref' => '#/components/schemas/Property'],
                                        'message' => ['type' => 'string', 'example' => 'Property created successfully']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => ['$ref' => '#/components/schemas/Error'],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ]
        ],
        '/properties/{id}' => [
            'get' => [
                'summary' => 'Get property details',
                'description' => 'Retrieve detailed information about a specific property',
                'parameters' => [
                    ['$ref' => '#/components/parameters/PropertyId']
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Property details',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => ['$ref' => '#/components/schemas/Property']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '404' => ['$ref' => '#/components/schemas/Error']
                ]
            ],
            'put' => [
                'summary' => 'Update property',
                'description' => 'Update an existing property',
                'security' => [
                    ['BearerAuth' => []]
                ],
                'parameters' => [
                    ['$ref' => '#/components/parameters/PropertyId']
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Property'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Property updated successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => ['$ref' => '#/components/schemas/Property'],
                                        'message' => ['type' => 'string', 'example' => 'Property updated successfully']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '404' => ['$ref' => '#/components/schemas/Error'],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ],
            'delete' => [
                'summary' => 'Delete property',
                'description' => 'Delete a property listing',
                'security' => [
                    ['BearerAuth' => []]
                ],
                'parameters' => [
                    ['$ref' => '#/components/parameters/PropertyId']
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Property deleted successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'message' => ['type' => 'string', 'example' => 'Property deleted successfully']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '404' => ['$ref' => '#/components/schemas/Error'],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ]
        ],
        '/auth/login' => [
            'post' => [
                'summary' => 'User login',
                'description' => 'Authenticate user and return access token',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email', 'password'],
                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'description' => 'User email'
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'description' => 'User password'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Login successful',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'token' => ['type' => 'string', 'description' => 'JWT token'],
                                                'user' => ['$ref' => '#/components/schemas/User'],
                                                'expires_at' => ['type' => 'string', 'format' => 'date-time']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ]
        ],
        '/auth/register' => [
            'post' => [
                'summary' => 'User registration',
                'description' => 'Register a new user account',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'email', 'password', 'phone'],
                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'description' => 'Full name'
                                    ],
                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'description' => 'Email address'
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'description' => 'Password'
                                    ],
                                    'phone' => [
                                        'type' => 'string',
                                        'description' => 'Phone number'
                                    ],
                                    'utype' => [
                                        'type' => 'string',
                                        'enum' => ['user', 'agent', 'builder'],
                                        'description' => 'User type'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Registration successful',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => ['$ref' => '#/components/schemas/User'],
                                        'message' => ['type' => 'string', 'example' => 'Registration successful']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => ['$ref' => '#/components/schemas/Error']
                ]
            ]
        ],
        '/bookings' => [
            'get' => [
                'summary' => 'Get bookings',
                'description' => 'Retrieve bookings with optional filtering',
                'security' => [
                    ['BearerAuth' => []]
                ],
                'parameters' => [
                    ['$ref' => '#/components/parameters/Page'],
                    ['$ref' => '#/components/parameters/Limit'],
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['pending', 'confirmed', 'cancelled', 'completed']
                        ],
                        'description' => 'Filter by booking status'
                    ],
                    [
                        'name' => 'property_id',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'integer'
                        ],
                        'description' => 'Filter by property ID'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Bookings retrieved successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Booking']
                                        ],
                                        'pagination' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'current_page' => ['type' => 'integer'],
                                                'total_pages' => ['type' => 'integer'],
                                                'total_items' => ['type' => 'integer'],
                                                'items_per_page' => ['type' => 'integer']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ],
            'post' => [
                'summary' => 'Create booking',
                'description' => 'Create a new property booking',
                'security' => [
                    ['BearerAuth' => []]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['property_id', 'customer_name', 'customer_email', 'customer_phone'],
                                'properties' => [
                                    'property_id' => [
                                        'type' => 'integer',
                                        'description' => 'Property ID'
                                    ],
                                    'customer_name' => [
                                        'type' => 'string',
                                        'description' => 'Customer name'
                                    ],
                                    'customer_email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'description' => 'Customer email'
                                    ],
                                    'customer_phone' => [
                                        'type' => 'string',
                                        'description' => 'Customer phone'
                                    ],
                                    'amount' => [
                                        'type' => 'number',
                                        'description' => 'Booking amount'
                                    ],
                                    'notes' => [
                                        'type' => 'string',
                                        'description' => 'Additional notes'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Booking created successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => ['$ref' => '#/components/schemas/Booking'],
                                        'message' => ['type' => 'string', 'example' => 'Booking created successfully']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => ['$ref' => '#/components/schemas/Error'],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ]
        ],
        '/agents' => [
            'get' => [
                'summary' => 'Get agents list',
                'description' => 'Retrieve list of real estate agents',
                'parameters' => [
                    ['$ref' => '#/components/parameters/Page'],
                    ['$ref' => '#/components/parameters/Limit'],
                    [
                        'name' => 'level',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'integer',
                            'minimum' => 1
                        ],
                        'description' => 'Filter by agent level'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Agents retrieved successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Associate']
                                        ],
                                        'pagination' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'current_page' => ['type' => 'integer'],
                                                'total_pages' => ['type' => 'integer'],
                                                'total_items' => ['type' => 'integer'],
                                                'items_per_page' => ['type' => 'integer']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '/analytics/properties' => [
            'get' => [
                'summary' => 'Get property analytics',
                'description' => 'Retrieve property performance analytics',
                'security' => [
                    ['BearerAuth' => []]
                ],
                'parameters' => [
                    [
                        'name' => 'start_date',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date'
                        ],
                        'description' => 'Start date for analytics'
                    ],
                    [
                        'name' => 'end_date',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date'
                        ],
                        'description' => 'End date for analytics'
                    ],
                    [
                        'name' => 'group_by',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['day', 'week', 'month', 'year']
                        ],
                        'description' => 'Group results by time period'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Analytics retrieved successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean', 'example' => true],
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'total_properties' => ['type' => 'integer'],
                                                'available_properties' => ['type' => 'integer'],
                                                'sold_properties' => ['type' => 'integer'],
                                                'featured_properties' => ['type' => 'integer'],
                                                'average_price' => ['type' => 'number'],
                                                'price_range' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'min' => ['type' => 'number'],
                                                        'max' => ['type' => 'number']
                                                    ]
                                                ],
                                                'top_cities' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'city' => ['type' => 'string'],
                                                            'count' => ['type' => 'integer']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['$ref' => '#/components/schemas/Error']
                ]
            ]
        ]
    ]
];

// Save the OpenAPI specification
file_put_contents(__DIR__ . '/openapi_complete.json', json_encode($openApiSpec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Complete OpenAPI 3.0 specification generated successfully!\n";
echo "File saved to: " . __DIR__ . "/openapi_complete.json\n";
?>
