<?php
namespace App\Controllers;

class BaseController {
    /**
     * Send a 404 Not Found response
     */
    protected function notFound() {
        http_response_code(404);
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        } else {
            include __DIR__ . '/../views/errors/404.php';
        }
        exit;
    }

    /**
     * Require user to be logged in
     */
    protected function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            } else {
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                header('Location: /login');
                exit;
            }
        }
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Send a 403 Forbidden response
     */
    protected function forbidden() {
        http_response_code(403);
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
        } else {
            include __DIR__ . '/../views/errors/403.php';
        }
        exit;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Render a view with data
     */
    protected function render($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = __DIR__ . "/../views/" . $view . ".php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: " . $view);
        }
        
        // Get the view content
        $content = ob_get_clean();
        
        // Include layout if exists
        $layoutFile = __DIR__ . "/../views/layouts/main.php";
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
}
