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
// DEBUG CODE REMOVED: 2026-02-25 07:31:16 CODE REMOVED: 2026-02-22 19:56:19 CODE REMOVED: 2026-02-22 19:56:19

echo "Using getAttribute: ";
var_dump($instance->getAttribute('wheres'));

echo "Using property_exists: ";
var_dump(property_exists($instance, 'wheres'));

echo "Direct access to declared property: ";
$reflection = new ReflectionClass($instance);
$wheresProperty = $reflection->getProperty('wheres');
$wheresProperty->setAccessible(true);
var_dump($wheresProperty->getValue($instance));