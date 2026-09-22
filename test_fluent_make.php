<?php
require 'config/bootstrap.php';
require 'testing/unit/InputValidatorTest.php';
$t = new InputValidatorTest();
$t->testFluentMake();
echo 'Done';