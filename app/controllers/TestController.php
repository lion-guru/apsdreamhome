<?php
/**
 * Test Controller for View Consolidation
 * Tests the modern view system integration
 */

use App\Core\View;

class TestController {
    private $view;
    
    public function __construct() {
        $this->view = new View();
    }
    
    /**
     * Test the consolidation page
     */
    public function consolidation() {
        return $this->view->render('test.consolidation');
    }
    
    /**
     * Test modern layout
     */
    public function layout() {
        $content = '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Modern Layout Test</h4>
                        </div>
                        <div class="card-body">
                            <p>This page demonstrates the modern layout system working correctly.</p>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Layout consolidation is working!
                            </div>
                            <div class="mt-4">
                                <a href="/auth/login" class="btn btn-primary me-2">Test Login</a>
                                <a href="/auth/register" class="btn btn-outline-primary me-2">Test Register</a>
                                <a href="/test/consolidation" class="btn btn-outline-secondary">Back to Consolidation</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        
        return $this->view->render('layouts.modern', ['content' => $content]);
    }
}