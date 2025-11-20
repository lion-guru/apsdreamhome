<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Core\ApiController;
use App\Core\DatabaseManager;

/**
 * Sample API Controller for testing the enhanced routing system
 */
class TestApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Test basic API endpoint
     * GET /api/test
     */
    public function index()
    {
        return $this->json([
            'status' => 'success',
            'message' => 'API is working correctly',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }
    
    /**
     * Test API endpoint with parameters
     * GET /api/test/{id}
     */
    public function show($id)
    {
        return $this->json([
            'status' => 'success',
            'message' => 'Resource retrieved successfully',
            'data' => [
                'id' => $id,
                'name' => 'Test Resource ' . $id,
                'description' => 'This is a test resource with ID: ' . $id
            ]
        ]);
    }
    
    /**
     * Test API endpoint with database interaction
     * GET /api/test/users
     */
    public function users()
    {
        try {
            $stmt = $this->db->query("SELECT id, username, email, role, created_at FROM users LIMIT 10");
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->json([
                'status' => 'success',
                'message' => 'Users retrieved successfully',
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test POST endpoint
     * POST /api/test/create
     */
    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Name field is required'
            ], 400);
        }
        
        return $this->json([
            'status' => 'success',
            'message' => 'Resource created successfully',
            'data' => [
                'id' => rand(1000, 9999),
                'name' => $input['name'],
                'created_at' => date('Y-m-d H:i:s')
            ]
        ], 201);
    }
    
    /**
     * Test PUT endpoint
     * PUT /api/test/update/{id}
     */
    public function update($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid input data'
            ], 400);
        }
        
        return $this->json([
            'status' => 'success',
            'message' => 'Resource updated successfully',
            'data' => [
                'id' => $id,
                'updated_fields' => array_keys($input),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
    /**
     * Test DELETE endpoint
     * DELETE /api/test/delete/{id}
     */
    public function delete($id)
    {
        return $this->json([
            'status' => 'success',
            'message' => 'Resource deleted successfully',
            'data' => [
                'id' => $id,
                'deleted_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
    /**
     * Test middleware functionality
     * GET /api/test/protected
     */
    public function protected()
    {
        return $this->json([
            'status' => 'success',
            'message' => 'This is a protected endpoint',
            'user' => $_SESSION['user'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Test error handling
     * GET /api/test/error
     */
    public function error()
    {
        throw new \Exception('This is a test exception for error handling');
    }
    
    /**
     * Test rate limiting (if middleware is configured)
     * GET /api/test/rate-limited
     */
    public function rateLimited()
    {
        return $this->json([
            'status' => 'success',
            'message' => 'Rate limited endpoint accessed successfully',
            'requests_remaining' => $_SERVER['RATE_LIMIT_REMAINING'] ?? 'unknown',
            'reset_time' => $_SERVER['RATE_LIMIT_RESET'] ?? 'unknown'
        ]);
    }
}