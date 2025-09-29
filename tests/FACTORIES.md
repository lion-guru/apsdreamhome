# Test Data Factories

This directory contains test data factories for generating realistic test data in your tests. The factories use the [Faker](https://fakerphp.github.io/) library to generate fake data.

## Available Factories

### UserFactory

Creates user instances with realistic test data.

**Example Usage:**

```php
// Create a single user
$user = create('App\\Models\\User');

// Create a user with specific attributes
$admin = create('App\\Models\\User', ['role' => 'admin']);

// Create multiple users
$users = create('App\\Models\\User', [], 5);

// Use factory methods
$agent = create('App\\Models\\User')->agent();
$admin = create('App\\Models\\User')->admin();
$userWithPassword = create('App\\Models\\User')->password('custom-password');
```

### PropertyFactory

Creates property instances with realistic test data.

**Example Usage:**

```php
// Create a single property
$property = create('App\\Models\\Property');

// Create a property with specific attributes
$apartment = create('App\\Models\\Property', ['type' => 'apartment']);

// Create multiple properties
$properties = create('App\\Models\\Property', [], 3);

// Use factory methods
$soldProperty = create('App\\Models\\Property')->status('sold');
$expensiveProperty = create('App\\Models\\Property')->priceRange(1000000, 5000000);
$locationProperty = create('App\\Models\\Property')
    ->location('New York', 'NY');
```

## Helper Functions

The following helper functions are available in your tests:

- `create(string $model, array $attributes = [], ?int $count = null)` - Create and persist model instances
- `make(string $model, array $attributes = [], ?int $count = null)` - Create model instances without persisting them
- `factory(string $model, int $count = 1)` - Get a factory instance for the given model

## Creating New Factories

1. Create a new factory class in the `tests/Factories` directory
2. Extend the base `Factory` class
3. Implement the `definition()` method to define default attributes
4. Add any additional helper methods for setting specific attributes

**Example:**

```php
<?php

namespace Tests\\Factories;

class PostFactory extends Factory
{
    protected string $model = 'App\\Models\\Post';
    
    protected function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'is_published' => true,
            'published_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];
    }
    
    public function unpublished(): self
    {
        $this->defaults['is_published'] = false;
        $this->defaults['published_at'] = null;
        return $this;
    }
}
```

## Running Tests

Run the test suite with:

```bash
./vendor/bin/phpunit
```

Or run specific test files:

```bash
./vendor/bin/phpunit tests/Unit/Factories/UserFactoryTest.php
```
