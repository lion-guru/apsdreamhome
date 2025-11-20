<?php

namespace Tests\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use mysqli;

abstract class Factory
{
    /**
     * The Faker instance
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * The model class this factory creates
     */
    protected $model;
    
    /**
     * The table name
     * @var string
     */
    protected $table;

    /**
     * Default attributes for the model
     */
    protected array $defaults = [];

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Define the model's default state
     */
    abstract protected function definition(): array;

    /**
     * Create a new model instance
     */
    public function make(array $attributes = []): array
    {
        return array_merge(
            $this->definition(),
            $attributes
        );
    }

    /**
     * Create a new model and persist it to the database
     */
    public function create(array $attributes = []): array
    {
        $attributes = $this->make($attributes);
        
        // Insert into database
        $db = $this->getMysqliConnection();
        $columns = implode(', ', array_map(function($col) {
            return "`$col`";
        }, array_keys($attributes)));
        $placeholders = implode(', ', array_fill(0, count($attributes), '?'));
        
        $stmt = $db->prepare("INSERT INTO `{$this->getTableName()}` ($columns) VALUES ($placeholders)");
        
        // Bind parameters
        $types = $this->getBindTypes($attributes);
        $values = array_values($attributes);
        $bindParams = [$types];
        
        // Create references for bind_param
        foreach ($values as $key => $value) {
            $bindParams[] = &$values[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        $stmt->execute();
        
        // Get the inserted ID
        $id = $stmt->insert_id;
        $stmt->close();
        
        // Return the created model with ID
        return array_merge(['id' => $id], $attributes);
    }

    /**
     * Create multiple model instances
     */
    public function times(int $count, array $attributes = []): array
    {
        $results = [];
        foreach (range(1, $count) as $i) {
            $results[] = $this->create($attributes);
        }
        return $results;
    }

    /**
     * Get the database connection
     * 
     * @return \mysqli The database connection
     */
    protected function getMysqliConnection(): \mysqli
    {
        global $testDbConnection;
        
        if (!isset($testDbConnection)) {
            $testDbConnection = new \mysqli(
                TEST_DB_HOST,
                TEST_DB_USER,
                TEST_DB_PASS,
                TEST_DB_NAME
            );
            
            if ($testDbConnection->connect_error) {
                throw new \RuntimeException("Database connection failed: " . $testDbConnection->connect_error);
            }
            
            $testDbConnection->set_charset('utf8mb4');
        }
        
        return $testDbConnection;
    }

    /**
     * Get the table name for the model
     */
    protected function getTableName(): string
    {
        if (isset($this->table)) {
            return $this->table;
        }
        
        // Convert model name to snake_case and pluralize
        $className = (new \ReflectionClass($this->model))->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    /**
     * Get the parameter types for binding
     */
    protected function getBindTypes(array $attributes): string
    {
        $types = '';
        
        foreach ($attributes as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        return $types;
    }
}
