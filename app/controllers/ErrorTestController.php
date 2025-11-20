<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ErrorHandler;

/**
 * Test Controller for Error Pages
 * Tests the unified error handling system
 */
class ErrorTestController extends Controller
{
    /**
     * Test 404 error page
     */
    public function test404()
    {
        ErrorHandler::handle404();
    }
    
    /**
     * Test 500 error page
     */
    public function test500()
    {
        ErrorHandler::handle500();
    }
    
    /**
     * Test 403 error page
     */
    public function test403()
    {
        ErrorHandler::handle403();
    }
    
    /**
     * Test 401 error page
     */
    public function test401()
    {
        ErrorHandler::handle401();
    }
    
    /**
     * Test 400 error page
     */
    public function test400()
    {
        ErrorHandler::render(400);
    }
    
    /**
     * Test generic error rendering
     */
    public function testGeneric()
    {
        ErrorHandler::render(418, "I'm a teapot! This is a test of the generic error handler.");
    }
    
    /**
     * Test exception handling
     */
    public function testException()
    {
        throw new \Exception("This is a test exception to verify error handling works correctly.");
    }
}