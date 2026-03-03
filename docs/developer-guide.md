# APS Dream Home Developer Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Architecture](#architecture)
3. [Getting Started](#getting-started)
4. [API Reference](#api-reference)
5. [Database Schema](#database-schema)
6. [Authentication](#authentication)
7. [Error Handling](#error-handling)
8. [Best Practices](#best-practices)
9. [Testing](#testing)
10. [Deployment](#deployment)

## Introduction

### Overview
APS Dream Home is a comprehensive real estate platform built with modern web technologies. This guide provides developers with the information needed to integrate with our platform, build applications, and contribute to the project.

### Technology Stack
- **Backend**: PHP 8.2+ with Laravel Framework
- **Frontend**: Vue.js 3, React, and modern JavaScript
- **Database**: MySQL 8.0 with Redis caching
- **Search**: Elasticsearch 8.x
- **Queue**: Redis with Laravel Queue
- **File Storage**: AWS S3
- **Monitoring**: Prometheus, Grafana, New Relic

### Key Features
- AI-powered property recommendations
- Advanced search and filtering
- Real-time notifications
- Mobile-responsive design
- Multi-language support
- RESTful API
- WebSocket connections

## Architecture

### System Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   API Gateway   │    │   Backend       │
│   (Vue.js)      │◄──►│   (Nginx)       │◄──►│   (Laravel)     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │   Services      │
                       │   (Microservices)│
                       └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │   Data Layer    │
                       │ (MySQL, Redis)  │
                       └─────────────────┘
```

### MVC Structure
- **Models**: Data models and business logic
- **Views**: Frontend templates and components
- **Controllers**: Request handling and response formatting
- **Services**: Business logic and external integrations
- **Middleware**: Request filtering and authentication
- **Repositories**: Data access layer

### Microservices Architecture
- **User Service**: User management and authentication
- **Property Service**: Property listings and management
- **Search Service**: Advanced search and indexing
- **Analytics Service**: Data analysis and reporting
- **Notification Service**: Email, SMS, and push notifications
- **Payment Service**: Payment processing and management

## Getting Started

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- MySQL 8.0
- Redis 7.x
- Elasticsearch 8.x

### Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/apsdreamhome/apsdreamhome.git
   cd apsdreamhome
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Environment setup:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Database setup:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. Start development servers:
   ```bash
   php artisan serve
   npm run dev
   ```

### Configuration
Edit `.env` file with your settings:
```env
APP_NAME="APS Dream Home"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## API Reference

### Authentication
All API endpoints require authentication using JWT tokens:
```bash
# Login
POST /api/v2.0/users/login
{
    "email": "user@example.com",
    "password": "password"
}

# Response
{
    "success": true,
    "data": {
        "token": "jwt-token-here",
        "user": {...},
        "expires_at": "2026-03-04T12:00:00Z"
    }
}
```

### Rate Limiting
- **Standard**: 1000 requests per hour
- **Premium**: 5000 requests per hour
- **Enterprise**: 10000 requests per hour

### Response Format
All API responses follow this format:
```json
{
    "success": true,
    "data": {...},
    "message": "Success",
    "timestamp": "2026-03-03T12:00:00Z"
}
```

### Error Responses
```json
{
    "success": false,
    "error": {
        "code": 422,
        "message": "Validation failed",
        "details": {...}
    },
    "timestamp": "2026-03-03T12:00:00Z"
}
```

## Database Schema

### Core Tables
- **users**: User accounts and profiles
- **properties**: Property listings and details
- **property_images**: Property photos and media
- **property_features**: Property features and amenities
- **locations**: Geographic locations and neighborhoods
- **search_history**: User search queries
- **favorites**: User favorite properties
- **notifications**: System notifications
- **analytics**: Usage analytics and metrics

### Relationships
```php
// User Model
public function properties()
{
    return $this->hasMany(Property::class);
}

public function favorites()
{
    return $this->belongsToMany(Property::class, 'favorites');
}

// Property Model
public function user()
{
    return $this->belongsTo(User::class);
}

public function images()
{
    return $this->hasMany(PropertyImage::class);
}

public function features()
{
    return $this->belongsToMany(Feature::class, 'property_features');
}
```

### Database Migrations
```php
// Create properties table
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->string('location');
    $table->string('property_type');
    $table->integer('bedrooms');
    $table->integer('bathrooms');
    $table->integer('size');
    $table->enum('status', ['active', 'inactive', 'sold', 'rented']);
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});
```

## Authentication

### JWT Authentication
```php
// Generate token
$token = JWTAuth::fromUser($user);

// Verify token
$user = JWTAuth::authenticate($token);

// Middleware
Route::middleware(['auth:api'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
});
```

### OAuth Integration
```php
// Google OAuth
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Facebook OAuth
Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
```

### Two-Factor Authentication
```php
// Enable 2FA
$user->enableTwoFactorAuthentication();

// Verify 2FA
if ($user->verifyTwoFactorCode($code)) {
    // Authentication successful
}
```

## Error Handling

### Exception Handling
```php
// Custom exception
class PropertyNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 404,
                'message' => 'Property not found'
            ]
        ], 404);
    }
}

// Global exception handler
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        }
        
        return parent::render($request, $exception);
    }
}
```

### Validation
```php
// Request validation
class PropertyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'property_type' => 'required|in:apartment,house,villa,condo,commercial'
        ];
    }
}
```

## Best Practices

### Code Style
- Follow PSR-12 coding standards
- Use type hints and return types
- Write comprehensive unit tests
- Document all public methods
- Use meaningful variable names

### Performance Optimization
- Use database indexing
- Implement caching strategies
- Optimize database queries
- Use lazy loading for relationships
- Implement pagination for large datasets

### Security
- Validate all user inputs
- Use prepared statements
- Implement rate limiting
- Use HTTPS for all communications
- Store sensitive data securely

### API Design
- Use RESTful principles
- Implement proper HTTP status codes
- Use consistent response formats
- Provide comprehensive error messages
- Include API versioning

## Testing

### Unit Testing
```php
// Example unit test
class PropertyTest extends TestCase
{
    public function test_can_create_property()
    {
        $property = Property::factory()->create();
        
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => $property->title
        ]);
    }
}
```

### Feature Testing
```php
// Example feature test
class PropertyApiTest extends TestCase
{
    public function test_can_get_properties()
    {
        $response = $this->get('/api/v2.0/properties');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success' => true,
                    'data' => [
                        'properties' => [
                            '*' => [
                                'id', 'title', 'price', 'location'
                            ]
                        ]
                    ]
                ]);
    }
}
```

### Browser Testing
```php
// Example browser test
class PropertySearchTest extends DuskTestCase
{
    public function test_can_search_properties()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/properties')
                    ->type('#search', 'apartment')
                    ->click('#search-button')
                    ->assertSee('Search Results');
        });
    }
}
```

## Deployment

### Environment Configuration
```bash
# Production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://apsdreamhome.com

# Staging
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.apsdreamhome.com

# Development
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Docker Deployment
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

### CI/CD Pipeline
```yaml
# GitHub Actions
name: Deploy to Production
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to production
        run: |
          docker build -t apsdreamhome:latest .
          docker push apsdreamhome:latest
          kubectl apply -f k8s/
```

### Monitoring
```php
// Performance monitoring
class PerformanceMonitor
{
    public function trackRequest($request, $response)
    {
        $metrics = [
            'response_time' => microtime(true) - LARAVEL_START,
            'memory_usage' => memory_get_usage(),
            'status_code' => $response->getStatusCode()
        ];
        
        $this->sendMetrics($metrics);
    }
}
```

## Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests
5. Submit a pull request
6. Code review and merge

### Code Review Guidelines
- Follow coding standards
- Include tests for new features
- Update documentation
- Ensure backward compatibility
- Performance testing

### Issue Reporting
- Use GitHub issues for bug reports
- Provide detailed reproduction steps
- Include environment details
- Add screenshots if applicable

## Support

### Developer Support
- **Documentation**: [docs.apsdreamhome.com](https://docs.apsdreamhome.com)
- **API Reference**: [api.apsdreamhome.com/docs](https://api.apsdreamhome.com/docs)
- **Community Forum**: [forum.apsdreamhome.com](https://forum.apsdreamhome.com)
- **Email**: developers@apsdreamhome.com

### Resources
- **SDKs**: Available for PHP, JavaScript, Python
- **Code Examples**: GitHub repository
- **Tutorials**: Video and written guides
- **Webinars**: Live training sessions

---

*Last updated: March 2026*
*Version: 2.0.0*
