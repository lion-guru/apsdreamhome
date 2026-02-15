<?php
require_once 'app/core/Model.php';
require_once 'app/core/UnifiedModel.php';

use App\Core\Model;

class TestModel extends Model {
    protected static $table = 'test_table';
}

echo "=== Testing magic get behavior ===\n";

$instance = new TestModel();
echo "Direct property access: ";
var_dump($instance->wheres);

echo "Using getAttribute: ";
var_dump($instance->getAttribute('wheres'));

echo "Using property_exists: ";
var_dump(property_exists($instance, 'wheres'));

echo "Direct access to declared property: ";
$reflection = new ReflectionClass($instance);
$wheresProperty = $reflection->getProperty('wheres');
$wheresProperty->setAccessible(true);
var_dump($wheresProperty->getValue($instance));