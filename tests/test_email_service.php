<?php

/**
 * Legacy Proxy for test_email_service.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Legacy\test_email_service as Legacytest_email_service;

class test_email_service extends Legacytest_email_service {}

