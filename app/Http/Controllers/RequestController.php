<?php

namespace App\Http\Controllers;

use App\Services\RequestService;

/**
 * Custom Request Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class RequestController
{
    private $requestService;
    private $viewRenderer;

    public function __construct()
    {
        $this->requestService = new RequestService();
        $this->viewRenderer = new \App\Core\View();
    }

    /**
     * Initialize request processing system
     */
    public function initialize()
    {
        // Add built-in middleware
        $this->requestService->addSecurityMiddleware();
        $this->requestService->addRateLimitingMiddleware(100, 3600); // 100 requests per hour
        $this->requestService->addLoggingMiddleware();

        // Register default routes
        $this->registerDefaultRoutes();

        // Handle CORS
        $this->requestService->handleCors();

        // Process the request
        return $this->requestService->processRequest();
    }

    /**
     * Register default application routes
     */
    private function registerDefaultRoutes()
    {
        // Authentication routes
        $this->requestService->registerRoute('GET', '/login', [$this, 'showLogin']);
        $this->requestService->registerRoute('POST', '/login', [$this, 'processLogin'], ['auth']);
        $this->requestService->registerRoute('GET', '/register', [$this, 'showRegister']);
        $this->requestService->registerRoute('POST', '/register', [$this, 'processRegister'], ['auth']);
        $this->requestService->registerRoute('POST', '/logout', [$this, 'processLogout'], ['auth']);

        // Dashboard routes
        $this->requestService->registerRoute('GET', '/dashboard', [$this, 'showDashboard'], ['auth']);
        $this->requestService->registerRoute('GET', '/admin/dashboard', [$this, 'showAdminDashboard'], ['auth', 'admin']);

        // API routes
        $this->requestService->registerRoute('GET', '/api/health', [$this, 'apiHealth']);
        $this->requestService->registerRoute('GET', '/api/info', [$this, 'apiInfo']);
        $this->requestService->registerRoute('POST', '/api/echo', [$this, 'apiEcho']);

        // User management routes
        $this->requestService->registerRoute('GET', '/profile', [$this, 'showProfile'], ['auth']);
        $this->requestService->registerRoute('POST', '/profile', [$this, 'updateProfile'], ['auth']);

        // Error handling routes
        $this->requestService->registerRoute('GET', '/404', [$this, 'show404']);
        $this->requestService->registerRoute('GET', '/500', [$this, 'show500']);

        // Home route
        $this->requestService->registerRoute('GET', '/', [$this, 'showHome']);
    }

    /**
     * Show login page
     */
    public function showLogin($request)
    {
        // If already authenticated, redirect to dashboard
        $authService = new \App\Services\Auth\AuthenticationService();
        if ($authService->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Login - APS Dream Home',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ];

        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/login', $data);
    }

    /**
     * Process login
     */
    public function processLogin($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();

        $email = $request['post']['email'] ?? '';
        $password = $request['post']['password'] ?? '';
        $remember = isset($request['post']['remember']);

        $result = $authService->login($email, $password, $remember);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect($result['redirect']);
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $request['post'];
            $this->redirect('/login');
        }
    }

    /**
     * Show registration page
     */
    public function showRegister($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();
        if ($authService->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Register - APS Dream Home',
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? [],
            'roles' => ['user' => 'User', 'associate' => 'Associate']
        ];

        unset($_SESSION['errors'], $_SESSION['old']);

        return $this->viewRenderer->render('auth/register', $data);
    }

    /**
     * Process registration
     */
    public function processRegister($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();

        $userData = [
            'name' => $request['post']['name'] ?? '',
            'email' => $request['post']['email'] ?? '',
            'password' => $request['post']['password'] ?? '',
            'password_confirmation' => $request['post']['password_confirmation'] ?? '',
            'role' => $request['post']['role'] ?? 'user'
        ];

        if ($userData['password'] !== $userData['password_confirmation']) {
            $_SESSION['errors'] = ['Password confirmation does not match'];
            $_SESSION['old'] = $request['post'];
            $this->redirect('/register');
            return;
        }

        unset($userData['password_confirmation']);

        $result = $authService->register($userData);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/login');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old'] = $request['post'];
            $this->redirect('/register');
        }
    }

    /**
     * Process logout
     */
    public function processLogout($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();
        $result = $authService->logout();

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/login');
    }

    /**
     * Show dashboard
     */
    public function showDashboard($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            return;
        }

        $user = $authService->getCurrentUser();

        $data = [
            'title' => 'Dashboard - APS Dream Home',
            'user' => $user,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('dashboard/index', $data);
    }

    /**
     * Show admin dashboard
     */
    public function showAdminDashboard($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('admin_access')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $user = $authService->getCurrentUser();

        // Get system statistics
        $database = \App\Core\Database::getInstance();
        $stats = [
            'total_users' => $database->selectOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL")['count'],
            'active_sessions' => $database->selectOne("SELECT COUNT(DISTINCT user_id) as count FROM login_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)")['count'],
            'total_requests' => $database->selectOne("SELECT COUNT(*) as count FROM api_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")['count']
        ];

        $data = [
            'title' => 'Admin Dashboard - APS Dream Home',
            'user' => $user,
            'stats' => $stats,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('admin/dashboard', $data);
    }

    /**
     * API Health check
     */
    public function apiHealth($request)
    {
        $database = \App\Core\Database::getInstance();

        try {
            $database->query("SELECT 1");
            $databaseStatus = 'connected';
        } catch (Exception $e) {
            $databaseStatus = 'error';
        }

        return [
            'success' => true,
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $databaseStatus,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'uptime' => time() - ($_SERVER['REQUEST_TIME'] ?? time())
        ];
    }

    /**
     * API Info endpoint
     */
    public function apiInfo($request)
    {
        return [
            'success' => true,
            'application' => 'APS Dream Home',
            'version' => '1.0.0',
            'architecture' => 'Custom MVC',
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'request_info' => [
                'method' => $request['method'],
                'uri' => $request['uri'],
                'ip' => $request['ip'],
                'user_agent' => $request['user_agent']
            ]
        ];
    }

    /**
     * API Echo endpoint for testing
     */
    public function apiEcho($request)
    {
        return [
            'success' => true,
            'message' => 'Echo successful',
            'received_data' => [
                'method' => $request['method'],
                'get' => $request['get'],
                'post' => $request['post'],
                'json' => $request['json'] ?? null,
                'headers' => $request['headers']
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Show profile page
     */
    public function showProfile($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            return;
        }

        $user = $authService->getCurrentUser();

        $data = [
            'title' => 'Profile - APS Dream Home',
            'user' => $user,
            'errors' => $_SESSION['errors'] ?? [],
            'success' => $_SESSION['success'] ?? ''
        ];

        unset($_SESSION['errors'], $_SESSION['success']);

        return $this->viewRenderer->render('profile/index', $data);
    }

    /**
     * Update profile
     */
    public function updateProfile($request)
    {
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Authentication required'];
            $this->redirect('/login');
            return;
        }

        $user = $authService->getCurrentUser();
        $database = \App\Core\Database::getInstance();

        $updateData = [
            'name' => $request['post']['name'] ?? $user['name'],
            'email' => $request['post']['email'] ?? $user['email'],
            'phone' => $request['post']['phone'] ?? $user['phone'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validate email if changed
        if ($updateData['email'] !== $user['email']) {
            $existingUser = $database->selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$updateData['email'], $user['id']]);
            if ($existingUser) {
                $_SESSION['errors'] = ['Email already exists'];
                $_SESSION['old'] = $request['post'];
                $this->redirect('/profile');
                return;
            }
        }

        $result = $database->update('users', $updateData, 'id = ?', [$user['id']]);

        if ($result) {
            $_SESSION['success'] = 'Profile updated successfully';
        } else {
            $_SESSION['errors'] = ['Failed to update profile'];
        }

        $this->redirect('/profile');
    }

    /**
     * Show 404 page
     */
    public function show404($request)
    {
        http_response_code(404);

        $data = [
            'title' => 'Page Not Found - APS Dream Home',
            'requested_url' => $request['uri']
        ];

        return $this->viewRenderer->render('errors/404', $data);
    }

    /**
     * Show 500 page
     */
    public function show500($request)
    {
        http_response_code(500);

        $data = [
            'title' => 'Server Error - APS Dream Home',
            'error_id' => uniqid('error_')
        ];

        return $this->viewRenderer->render('errors/500', $data);
    }

    /**
     * Show home page
     */
    public function showHome($request)
    {
        $data = [
            'title' => 'Welcome to APS Dream Home',
            'features' => [
                'Farmer Management',
                'Security System',
                'Performance Optimization',
                'Event Management',
                'Custom MVC Architecture'
            ]
        ];

        return $this->viewRenderer->render('home/index', $data);
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }

    /**
     * Get request service instance
     */
    public function getRequestService()
    {
        return $this->requestService;
    }

    /**
     * Add custom route
     */
    public function addRoute($method, $path, $handler, $middleware = [])
    {
        return $this->requestService->registerRoute($method, $path, $handler, $middleware);
    }

    /**
     * Add custom middleware
     */
    public function addMiddleware($stage, $handler)
    {
        return $this->requestService->registerMiddleware($stage, $handler);
    }
}
