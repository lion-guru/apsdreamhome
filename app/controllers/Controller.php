<?php

namespace App\Controllers;

use App\Services\AuthService;

abstract class Controller
{
    protected AuthService $auth;
    protected array $data = [];
    protected string $layout = 'base';

    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->auth = new AuthService();
        // Ensure CSRF token is initialized
        $this->getCsrfToken();
    }

    /**
     * Set flash message
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
    }

    /**
     * Get flash message
     */
    protected function getFlash(string $type): ?string
    {
        if (isset($_SESSION[$type])) {
            $message = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $message;
        }
        return null;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send a 404 Not Found response
     */
    protected function notFound(): void
    {
        http_response_code(404);
        if ($this->isAjaxRequest()) {
            $this->json(['error' => 'Not Found'], 404);
        } else {
            $this->view('errors/404', [], 404);
        }
        exit;
    }

    /**
     * Require user to be logged in
     */
    protected function requireLogin(): void
    {
        if (!$this->auth->isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => 'Authentication required'], 401);
            } else {
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                header('Location: /login');
                exit;
            }
        }
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return $this->auth->isLoggedIn();
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->auth->isAdmin();
    }

    /**
     * Require user to be an admin
     */
    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => 'Admin access required'], 403);
            } else {
                $this->forbidden();
            }
        }
    }

    /**
     * Send a 403 Forbidden response
     */
    protected function forbidden(): void
    {
        http_response_code(403);
        if ($this->isAjaxRequest()) {
            $this->json(['error' => 'Forbidden'], 403);
        } else {
            $this->view('errors/403', [], 403);
        }
        exit;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit();
    }

    /**
     * Render a view template
     */
    protected function view(string $view, array $data = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);

        // Extract data for use in view
        extract($data);

        // Determine layout based on view path
        $layout = $this->layout;
        if (strpos($view, 'admin/') === 0) {
            $layout = 'admin';
        }

        // Build view path
        $viewPath = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }

        // Include layout if not already included
        if (!isset($content)) {
            $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require_once $layoutPath;
            } else {
                // Fallback to direct view rendering
                require_once $viewPath;
            }
        }
    }

    /**
     * CSRF token utilities
     */
    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken(?string $token): bool
    {
        return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
