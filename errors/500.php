<?php
/**
 * 500 Internal Server Error Page
 * Uses the unified error handler for consistent styling
 */

require_once __DIR__ . '/../app/core/ErrorHandler.php';

use App\Core\ErrorHandler;

ErrorHandler::handle500();