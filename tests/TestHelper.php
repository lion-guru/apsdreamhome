<?php

use Tests\Factories\Factory;
use Tests\Factories\PropertyFactory;
use Tests\Factories\UserFactory;

// Ensure we have the Faker autoloader
if (!class_exists('Faker\Factory')) {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    } else {
        throw new RuntimeException('Faker library not found. Run `composer require fakerphp/faker`');
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('factory')) {
    /**
     * Get a factory instance for the given model
     */
    function factory(string $model, int $count = 1): Factory
    {
        $factoryClass = "Tests\\Factories\\" . class_basename($model) . 'Factory';
        
        if (!class_exists($factoryClass)) {
            throw new InvalidArgumentException("Factory for model {$model} not found");
        }
        
        return new $factoryClass();
    }
}

if (!function_exists('create')) {
    /**
     * Create a new model instance and persist it to the database
     */
    function create(string $model, array $attributes = [], ?int $count = null)
    {
        $factory = factory($model);
        
        if ($count !== null) {
            return $factory->times($count, $attributes);
        }
        
        return $factory->create($attributes);
    }
}

if (!function_exists('make')) {
    /**
     * Create a new model instance without persisting it
     */
    function make(string $model, array $attributes = [], ?int $count = null)
    {
        $factory = factory($model);
        
        if ($count !== null) {
            return array_map(
                fn() => $factory->make($attributes),
                range(1, $count)
            );
        }
        
        return $factory->make($attributes);
    }
}

// Include the factory classes
require_once __DIR__ . '/Factories/Factory.php';
require_once __DIR__ . '/Factories/PropertyFactory.php';
require_once __DIR__ . '/Factories/UserFactory.php';
