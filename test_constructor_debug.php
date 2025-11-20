<?php

// Test constructor debugging
require_once 'config/bootstrap.php';

// Create a simple test model with constructor override
class TestModel extends App\Core\Model {
    protected static $table = 'test_table';
    
    public function __construct(array $attributes = []) {
        echo "TestModel constructor called\n";
        parent::__construct($attributes);
        echo "After parent constructor, wheres: " . json_encode($this->wheres) . "\n";
    }
}

try {
    echo "=== Constructor debugging ===\n";
    
    echo "Creating TestModel instance...\n";
    $instance = new TestModel();
    echo "After instance creation, wheres: " . json_encode($instance->wheres) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}