<?php
/**
 * APS Dream Home - API Documentation
 * 
 * This page provides comprehensive documentation for the APS Dream Home API,
 * including authentication, endpoints, and usage examples.
 */

// Set page title and include header
$pageTitle = 'API Documentation | APS Dream Home';
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background-color: var(--secondary);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .sidebar {
            position: sticky;
            top: 1rem;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }
        .endpoint {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-right: 0.75rem;
        }
        .method.get { background-color: #61affe; }
        .method.post { background-color: #49cc90; }
        .method.put { background-color: #fca130; }
        .method.delete { background-color: #f93e3e; }
        .badge-required {
            background-color: var(--danger);
            font-size: 0.7rem;
            vertical-align: middle;
        }
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            border-left: 4px solid var(--primary);
        }
        .nav-link {
            color: var(--secondary);
            font-weight: 500;
        }
        .nav-link.active {
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-home me-2"></i> APS Dream Home API</h1>
                    <p class="lead mb-0">Comprehensive documentation for integrating with our platform</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-light text-dark">v1.0.0</span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-book me-2"></i> Documentation
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="#introduction" class="list-group-item list-group-item-action">
                                <i class="fas fa-info-circle me-2"></i> Introduction
                            </a>
                            <a href="#authentication" class="list-group-item list-group-item-action active">
                                <i class="fas fa-key me-2"></i> Authentication
                            </a>
                            <a href="#rate-limiting" class="list-group-item list-group-item-action">
                                <i class="fas fa-tachometer-alt me-2"></i> Rate Limiting
                            </a>
                            <a href="#properties" class="list-group-item list-group-item-action">
                                <i class="fas fa-home me-2"></i> Properties
                            </a>
                            <a href="#leads" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-plus me-2"></i> Leads
                            </a>
                            <a href="#visits" class="list-group-item list-group-item-action">
                                <i class="far fa-calendar-alt me-2"></i> Visits
                            </a>
                            <a href="#customers" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i> Customers
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Introduction -->
                <section id="introduction" class="mb-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h4 mb-0">Introduction</h2>
                        </div>
                        <div class="card-body">
                            <p>Welcome to the APS Dream Home API documentation. This API allows you to integrate with our real estate management system and build custom applications.</p>
                            
                            <h5>Base URL</h5>
                            <p>All API requests should be made to the following base URL:</p>
                            <pre><code><?php echo $baseUrl; ?>/api/</code></pre>
                            
                            <h5>Response Format</h5>
                            <p>All API responses are returned in JSON format with the following structure:</p>
                            <pre><code>{
    "status": "success|error",
    "message": "Descriptive message",
    "data": {
        // Response data
    },
    "meta": {
        // Pagination and metadata
    }
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Authentication -->
                <section id="authentication" class="mb-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h4 mb-0">Authentication</h2>
                        </div>
                        <div class="card-body">
                            <p>All API requests require authentication using an API key. You can generate API keys from the admin panel.</p>
                            
                            <h5>Using Your API Key</h5>
                            <p>Include your API key in the <code>X-API-Key</code> header with each request:</p>
                            <pre><code>GET /api/endpoint
X-API-Key: your_api_key_here</code></pre>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Never expose your API key in client-side code or public repositories.
                            </div>
                            
                            <h5>Permissions</h5>
                            <p>API keys can be configured with different permission levels:</p>
                            <ul>
                                <li><strong>Read-only:</strong> Can only make GET requests</li>
                                <li><strong>Read/Write:</strong> Can make GET, POST, and PUT requests</li>
                                <li><strong>Admin:</strong> Full access to all endpoints</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Rate Limiting -->
                <section id="rate-limiting" class="mb-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h4 mb-0">Rate Limiting</h2>
                        </div>
                        <div class="card-body">
                            <p>To ensure fair usage, the API is rate limited. The default rate limits are:</p>
                            <ul>
                                <li><strong>Free Tier:</strong> 100 requests per hour</li>
                                <li><strong>Standard Tier:</strong> 1,000 requests per hour</li>
                                <li><strong>Enterprise Tier:</strong> 10,000+ requests per hour</li>
                            </ul>
                            
                            <h5>Rate Limit Headers</h5>
                            <p>The following headers are included in rate-limited responses:</p>
                            <ul>
                                <li><code>X-RateLimit-Limit</code>: Maximum number of requests allowed</li>
                                <li><code>X-RateLimit-Remaining</code>: Remaining number of requests</li>
                                <li><code>X-RateLimit-Reset</code>: Timestamp when the limit resets</li>
                            </ul>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tip:</strong> Implement exponential backoff when you hit rate limits to avoid being blocked.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Properties -->
                <section id="properties" class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Properties</h2>
                        <a href="#top" class="btn btn-sm btn-outline-primary">Back to top</a>
                    </div>
                    
                    <!-- List Properties -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method get">GET</span>
                            <h3 class="h5 mb-0">/properties</h3>
                        </div>
                        <p>Retrieve a list of properties with optional filtering and pagination.</p>
                        
                        <h5>Query Parameters</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>status</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Filter by status (available, sold, under_contract)</td>
                                    </tr>
                                    <tr>
                                        <td><code>min_price</code></td>
                                        <td>number</td>
                                        <td>No</td>
                                        <td>Minimum price</td>
                                    </tr>
                                    <tr>
                                        <td><code>max_price</code></td>
                                        <td>number</td>
                                        <td>No</td>
                                        <td>Maximum price</td>
                                    </tr>
                                    <tr>
                                        <td><code>bedrooms</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Number of bedrooms</td>
                                    </tr>
                                    <tr>
                                        <td><code>page</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Page number for pagination (default: 1)</td>
                                    </tr>
                                    <tr>
                                        <td><code>limit</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Number of items per page (default: 10, max: 100)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X GET \
  '<?php echo $baseUrl; ?>/api/properties?status=available&min_price=100000&max_price=500000&bedrooms=3' \
  -H 'X-API-Key: your_api_key_here'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "Modern 3-Bedroom Apartment",
      "description": "Beautiful apartment in the city center...",
      "price": 350000,
      "bedrooms": 3,
      "bathrooms": 2,
      "area": 1500,
      "address": "123 Main St, City",
      "status": "available",
      "created_at": "2023-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "total_pages": 1
  }
}</code></pre>
                    </div>
                    
                    <!-- Get Property -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method get">GET</span>
                            <h3 class="h5 mb-0">/properties/{id}</h3>
                        </div>
                        <p>Retrieve detailed information about a specific property.</p>
                        
                        <h5>URL Parameters</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>id</code></td>
                                        <td>integer</td>
                                        <td>Yes</td>
                                        <td>Property ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X GET \
  '<?php echo $baseUrl; ?>/api/properties/1' \
  -H 'X-API-Key: your_api_key_here'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Modern 3-Bedroom Apartment",
    "description": "Beautiful apartment in the city center...",
    "price": 350000,
    "bedrooms": 3,
    "bathrooms": 2,
    "area": 1500,
    "property_type": "Apartment",
    "status": "available",
    "address": "123 Main St, City",
    "features": ["Balcony", "Parking", "Gym", "Swimming Pool"],
    "images": [
      "/uploads/properties/1/image1.jpg",
      "/uploads/properties/1/image2.jpg"
    ],
    "created_at": "2023-01-15T10:30:00Z",
    "updated_at": "2023-01-15T10:30:00Z"
  }
}</code></pre>
                    </div>
                    
                    <!-- Create Property -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method post">POST</span>
                            <h3 class="h5 mb-0">/properties</h3>
                        </div>
                        <p>Create a new property listing.</p>
                        
                        <h5>Request Body</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>title</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Property title</td>
                                    </tr>
                                    <tr>
                                        <td><code>description</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Detailed description</td>
                                    </tr>
                                    <tr>
                                        <td><code>price</code></td>
                                        <td>number</td>
                                        <td>Yes</td>
                                        <td>Asking price</td>
                                    </tr>
                                    <tr>
                                        <td><code>bedrooms</code></td>
                                        <td>integer</td>
                                        <td>Yes</td>
                                        <td>Number of bedrooms</td>
                                    </tr>
                                    <tr>
                                        <td><code>bathrooms</code></td>
                                        <td>number</td>
                                        <td>Yes</td>
                                        <td>Number of bathrooms</td>
                                    </tr>
                                    <tr>
                                        <td><code>area</code></td>
                                        <td>number</td>
                                        <td>Yes</td>
                                        <td>Area in square feet</td>
                                    </tr>
                                    <tr>
                                        <td><code>property_type</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Type of property (e.g., Apartment, House, Villa)</td>
                                    </tr>
                                    <tr>
                                        <td><code>status</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Current status (default: available)</td>
                                    </tr>
                                    <tr>
                                        <td><code>address</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Full address</td>
                                    </tr>
                                    <tr>
                                        <td><code>features</code></td>
                                        <td>array</td>
                                        <td>No</td>
                                        <td>Array of property features</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X POST \
  '<?php echo $baseUrl; ?>/api/properties' \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "title": "Luxury Penthouse with City View",
    "description": "Stunning penthouse with panoramic city views...",
    "price": 750000,
    "bedrooms": 3,
    "bathrooms": 2.5,
    "area": 2200,
    "property_type": "Penthouse",
    "status": "available",
    "address": "456 High Street, Downtown",
    "features": ["Balcony", "Parking", "Gym", "Pool"]
  }'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "message": "Property created successfully",
  "data": {
    "id": 2,
    "title": "Luxury Penthouse with City View",
    "description": "Stunning penthouse with panoramic city views...",
    "price": 750000,
    "bedrooms": 3,
    "bathrooms": 2.5,
    "area": 2200,
    "property_type": "Penthouse",
    "status": "available",
    "address": "456 High Street, Downtown",
    "features": ["Balcony", "Parking", "Gym", "Pool"],
    "created_at": "2023-05-18T10:45:30Z",
    "updated_at": "2023-05-18T10:45:30Z"
  }
}</code></pre>
                    </div>
                </section>

                <!-- Leads -->
                <section id="leads" class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Leads</h2>
                        <a href="#top" class="btn btn-sm btn-outline-primary">Back to top</a>
                    </div>
                    
                    <!-- List Leads -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method get">GET</span>
                            <h3 class="h5 mb-0">/leads</h3>
                        </div>
                        <p>Retrieve a list of leads with optional filtering and pagination.</p>
                        
                        <h5>Query Parameters</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>status</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Filter by status (new, contacted, qualified, converted, lost)</td>
                                    </tr>
                                    <tr>
                                        <td><code>source</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Filter by lead source (website, referral, social_media, etc.)</td>
                                    </tr>
                                    <tr>
                                        <td><code>property_id</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Filter leads by property</td>
                                    </tr>
                                    <tr>
                                        <td><code>start_date</code></td>
                                        <td>date</td>
                                        <td>No</td>
                                        <td>Filter leads created after this date (YYYY-MM-DD)</td>
                                    </tr>
                                    <tr>
                                        <td><code>end_date</code></td>
                                        <td>date</td>
                                        <td>No</td>
                                        <td>Filter leads created before this date (YYYY-MM-DD)</td>
                                    </tr>
                                    <tr>
                                        <td><code>page</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Page number for pagination (default: 1)</td>
                                    </tr>
                                    <tr>
                                        <td><code>limit</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Number of items per page (default: 10, max: 100)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X GET \
  '<?php echo $baseUrl; ?>/api/leads?status=new&source=website&start_date=2023-01-01' \
  -H 'X-API-Key: your_api_key_here'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "data": [
    {
      "id": 101,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "source": "website",
      "status": "new",
      "property_id": 1,
      "property_title": "Modern 3-Bedroom Apartment",
      "notes": "Interested in properties with a garden",
      "created_at": "2023-05-10T14:30:00Z"
    }
  ],
  "meta": {
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "total_pages": 1
  }
}</code></pre>
                    </div>
                    
                    <!-- Create Lead -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method post">POST</span>
                            <h3 class="h5 mb-0">/leads</h3>
                        </div>
                        <p>Create a new lead from a potential customer inquiry.</p>
                        
                        <h5>Request Body</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>name</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Lead's full name</td>
                                    </tr>
                                    <tr>
                                        <td><code>email</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Lead's email address</td>
                                    </tr>
                                    <tr>
                                        <td><code>phone</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Lead's phone number</td>
                                    </tr>
                                    <tr>
                                        <td><code>source</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Source of the lead (default: website)</td>
                                    </tr>
                                    <tr>
                                        <td><code>property_id</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Property ID if related to a specific property</td>
                                    </tr>
                                    <tr>
                                        <td><code>notes</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Additional notes about the lead</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X POST \
  '<?php echo $baseUrl; ?>/api/leads' \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1987654321",
    "source": "website",
    "property_id": 1,
    "notes": "Interested in 3-bedroom properties"
  }'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "message": "Lead created successfully",
  "data": {
    "id": 102,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1987654321",
    "source": "website",
    "status": "new",
    "property_id": 1,
    "property_title": "Modern 3-Bedroom Apartment",
    "notes": "Interested in 3-bedroom properties",
    "created_at": "2023-05-18T12:30:00Z",
    "updated_at": "2023-05-18T12:30:00Z"
  }
}</code></pre>
                    </div>
                </section>

                <!-- Visits -->
                <section id="visits" class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Property Visits</h2>
                        <a href="#top" class="btn btn-sm btn-outline-primary">Back to top</a>
                    </div>
                    
                    <!-- Schedule Visit -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method post">POST</span>
                            <h3 class="h5 mb-0">/visits</h3>
                        </div>
                        <p>Schedule a new property visit.</p>
                        
                        <h5>Request Body</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>property_id</code></td>
                                        <td>integer</td>
                                        <td>Yes</td>
                                        <td>ID of the property to visit</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer_name</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Name of the visitor</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer_email</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Email of the visitor</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer_phone</code></td>
                                        <td>string</td>
                                        <td>Yes</td>
                                        <td>Phone number of the visitor</td>
                                    </tr>
                                    <tr>
                                        <td><code>visit_date</code></td>
                                        <td>date</td>
                                        <td>Yes</td>
                                        <td>Date of the visit (YYYY-MM-DD)</td>
                                    </tr>
                                    <tr>
                                        <td><code>visit_time</code></td>
                                        <td>time</td>
                                        <td>Yes</td>
                                        <td>Time of the visit (HH:MM:SS)</td>
                                    </tr>
                                    <tr>
                                        <td><code>notes</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Additional notes about the visit</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X POST \
  '<?php echo $baseUrl; ?>/api/visits' \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "property_id": 1,
    "customer_name": "Alex Johnson",
    "customer_email": "alex@example.com",
    "customer_phone": "+15551234567",
    "visit_date": "2023-06-15",
    "visit_time": "14:30:00",
    "notes": "Will be coming with spouse"
  }'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "message": "Visit scheduled successfully",
  "data": {
    "id": 45,
    "property_id": 1,
    "property_title": "Modern 3-Bedroom Apartment",
    "customer_name": "Alex Johnson",
    "customer_email": "alex@example.com",
    "customer_phone": "+15551234567",
    "visit_date": "2023-06-15",
    "visit_time": "14:30:00",
    "status": "scheduled",
    "notes": "Will be coming with spouse",
    "confirmation_code": "VST-7X9Y2Z",
    "created_at": "2023-05-18T13:15:00Z"
  }
}</code></pre>
                    </div>
                    
                    <!-- Get Visit Details -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method get">GET</span>
                            <h3 class="h5 mb-0">/visits/{id}</h3>
                        </div>
                        <p>Retrieve details of a specific visit.</p>
                        
                        <h5>URL Parameters</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>id</code></td>
                                        <td>integer</td>
                                        <td>Yes</td>
                                        <td>Visit ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X GET \
  '<?php echo $baseUrl; ?>/api/visits/45' \
  -H 'X-API-Key: your_api_key_here'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "data": {
    "id": 45,
    "property_id": 1,
    "property_title": "Modern 3-Bedroom Apartment",
    "property_address": "123 Main St, City",
    "customer_name": "Alex Johnson",
    "customer_email": "alex@example.com",
    "customer_phone": "+15551234567",
    "visit_date": "2023-06-15",
    "visit_time": "14:30:00",
    "status": "scheduled",
    "notes": "Will be coming with spouse",
    "confirmation_code": "VST-7X9Y2Z",
    "feedback": null,
    "rating": null,
    "agent_id": 5,
    "agent_name": "Sarah Wilson",
    "created_at": "2023-05-18T13:15:00Z",
    "updated_at": "2023-05-18T13:15:00Z"
  }
}</code></pre>
                    </div>
                </section>

                <!-- Customers -->
                <section id="customers" class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Customers</h2>
                        <a href="#top" class="btn btn-sm btn-outline-primary">Back to top</a>
                    </div>
                    
                    <!-- Get Customer -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method get">GET</span>
                            <h3 class="h5 mb-0">/customers/{id}</h3>
                        </div>
                        <p>Retrieve detailed information about a specific customer.</p>
                        
                        <h5>URL Parameters</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>id</code></td>
                                        <td>integer</td>
                                        <td>Yes</td>
                                        <td>Customer ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X GET \
  '<?php echo $baseUrl; ?>/api/customers/23' \
  -H 'X-API-Key: your_api_key_here'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "data": {
    "id": 23,
    "first_name": "Michael",
    "last_name": "Brown",
    "email": "michael@example.com",
    "phone": "+15559876543",
    "address": "789 Oak Avenue, Townsville",
    "city": "Townsville",
    "state": "State",
    "postal_code": "12345",
    "country": "Country",
    "date_of_birth": "1985-07-15",
    "occupation": "Software Engineer",
    "annual_income": 125000,
    "credit_score": 780,
    "preferred_contact_method": "email",
    "budget_min": 250000,
    "budget_max": 500000,
    "preferred_property_types": ["House", "Townhouse"],
    "preferred_locations": ["Downtown", "Suburb"],
    "must_have_features": ["Garage", "Garden", "3+ Bedrooms"],
    "lead_source": "referral",
    "lead_status": "qualified",
    "assigned_agent_id": 5,
    "assigned_agent_name": "Sarah Wilson",
    "total_interactions": 8,
    "last_interaction": "2023-05-15T11:30:00Z",
    "total_properties_viewed": 5,
    "favorite_properties": [1, 7, 12],
    "notes": [
      {
        "date": "2023-05-15T11:30:00Z",
        "content": "Interested in properties with a home office",
        "agent": "Sarah Wilson"
      }
    ],
    "created_at": "2023-01-10T09:15:00Z",
    "updated_at": "2023-05-15T11:30:00Z"
  }
}</code></pre>
                    </div>
                    
                    <!-- Update Customer -->
                    <div class="endpoint mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="method put">PUT</span>
                            <h3 class="h5 mb-0">/customers/{id}</h3>
                        </div>
                        <p>Update an existing customer's information.</p>
                        
                        <h5>URL Parameters</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>id</code></td>
                                        <td>integer</td>
                                        <td>Yes</td>
                                        <td>Customer ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Request Body</h5>
                        <p>Include only the fields you want to update.</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>email</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>New email address</td>
                                    </tr>
                                    <tr>
                                        <td><code>phone</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>New phone number</td>
                                    </tr>
                                    <tr>
                                        <td><code>address</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>New address</td>
                                    </tr>
                                    <tr>
                                        <td><code>budget_min</code></td>
                                        <td>number</td>
                                        <td>No</td>
                                        <td>Minimum budget</td>
                                    </tr>
                                    <tr>
                                        <td><code>budget_max</code></td>
                                        <td>number</td>
                                        <td>No</td>
                                        <td>Maximum budget</td>
                                    </tr>
                                    <tr>
                                        <td><code>preferred_property_types</code></td>
                                        <td>array</td>
                                        <td>No</td>
                                        <td>Array of preferred property types</td>
                                    </tr>
                                    <tr>
                                        <td><code>preferred_locations</code></td>
                                        <td>array</td>
                                        <td>No</td>
                                        <td>Array of preferred locations</td>
                                    </tr>
                                    <tr>
                                        <td><code>must_have_features</code></td>
                                        <td>array</td>
                                        <td>No</td>
                                        <td>Array of must-have features</td>
                                    </tr>
                                    <tr>
                                        <td><code>lead_status</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>New lead status (new, contacted, qualified, converted, lost)</td>
                                    </tr>
                                    <tr>
                                        <td><code>assigned_agent_id</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>ID of the agent to assign</td>
                                    </tr>
                                    <tr>
                                        <td><code>note</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Add a new note to the customer's record</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Example Request</h5>
                        <pre><code>curl -X PUT \
  '<?php echo $baseUrl; ?>/api/customers/23' \
  -H 'Content-Type: application/json' \
  -H 'X-API-Key: your_api_key_here' \
  -d '{
    "phone": "+15559876544",
    "budget_min": 300000,
    "budget_max": 550000,
    "preferred_locations": ["Downtown", "Riverside", "Suburb"],
    "must_have_features": ["Garage", "Garden", "Home Office", "3+ Bedrooms"],
    "note": "Customer is now looking for a home office space due to remote work requirements."
  }'</code></pre>
                        
                        <h5>Example Response</h5>
                        <pre><code>{
  "status": "success",
  "message": "Customer updated successfully",
  "data": {
    "id": 23,
    "first_name": "Michael",
    "last_name": "Brown",
    "email": "michael@example.com",
    "phone": "+15559876544",
    "address": "789 Oak Avenue, Townsville",
    "budget_min": 300000,
    "budget_max": 550000,
    "preferred_property_types": ["House", "Townhouse"],
    "preferred_locations": ["Downtown", "Riverside", "Suburb"],
    "must_have_features": ["Garage", "Garden", "Home Office", "3+ Bedrooms"],
    "lead_status": "qualified",
    "assigned_agent_id": 5,
    "notes": [
      {
        "date": "2023-05-18T14:30:00Z",
        "content": "Customer is now looking for a home office space due to remote work requirements.",
        "agent": "API Update"
      },
      {
        "date": "2023-05-15T11:30:00Z",
        "content": "Interested in properties with a home office",
        "agent": "Sarah Wilson"
      }
    ],
    "updated_at": "2023-05-18T14:30:00Z"
  }
}</code></pre>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>APS Dream Home API</h5>
                    <p class="mb-0">Version 1.0.0</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Update active nav link on scroll
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.list-group-item');
        
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>