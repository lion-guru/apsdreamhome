<?php

/**
 * Web Routes Configuration
 * 
 * This file returns an empty array as all routes are now defined in:
 * - routes/modern.php (Web routes with middleware)
 * - routes/api.php (API routes)
 * 
 * The Router checks for this file in handleLegacyFallback() when no modern route is found.
 * Returning empty ensures the modern routing system takes precedence.
 */

$webRoutes = [];
