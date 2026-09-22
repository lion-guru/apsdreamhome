<?php
require 'testing/unit/InputValidatorTest.php';
$t = new InputValidatorTest();
try {
    $t->testFluentMake();
    echo 'testFluentMake passed';
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
    echo "\nTrace: " . $e->getTraceAsString();
}