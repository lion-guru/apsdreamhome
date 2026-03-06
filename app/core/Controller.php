<?php

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Auth;
use App\Core\Session\Session;
use PDO;

/**
 * Base Controller
 * 
 * All controllers should extend this base controller
 */
#[\AllowDynamicProperties]
class Controller
{
    /**
     * The request instance
     *
     * @var Request
     */
    protected $request;

    /**
     * The response instance
     *
     * @var Response
     */
    protected $response;

    /**
     * The view instance
     *
     * @var View
     */
    protected $view;

    /**
     * The auth instance
     *
     * @var Auth
     */
    protected $auth;

    /**
     * The database connection
     *
     * @var Database
     */
    protected $db;

    /**
     * The session manager
     *
     * @var SessionManager
     */
    protected $session;
    
    /**
     * The data array for views
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * The layout template
     *
     * @var string
     */
    protected $layout = 'base';

    /**
     * The middleware stack
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The logger instance
     *
     * @var \App\Services\SystemLogger
     */
    protected $logger;

    /**
     * The application instance
     *
     * @var App
     */
    protected $app;

    /**
     * Register middleware for the controller
     *
     * @param string $middleware The middleware name
     * @param array $options Options (only, except)
     * @return void
     */
    protected function middleware($middleware, array $options = [])
    {
        $this->middleware[] = [
            'middleware' => $middleware,
            'options' => $options
        ];
    }

    /**
     * Controller constructor
     */
    public function __construct()
    {
        // Initialize components for custom router system
        $this->request = new \App\Core\Http\Request();
        $this->response = new \App\Core\Http\Response();
        $this->view = new View();
        $this->session = new \App\Core\Session\Session();
        $this->db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
        $this->auth = null; // Initialize auth if needed
        $this->app = null; // Not using App class
    }

    /**
     * Get the middleware for the controller
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Load a model
     *
     * @param string $model The model name (without namespace)
     * @return \App\Models\Model
     */
    public function model($model)
    {
        $model = 'App\\Models\\' . $model;
        return new $model();
    }

    /**
     * Redirect to a different page
     *
     * @param string $url The URL to redirect to
     * @param int $statusCode The HTTP status code (default: 302)
     * @return Response
     */
    public function redirect($url, $statusCode = 302)
    {
        return Response::redirect($url, $statusCode);
    }

    /**
     * Redirect to a named route
     *
     * @param string $name The route name
     * @param array $params Route parameters
     * @param int $statusCode The HTTP status code (default: 302)
     * @return Response
     */
    public function redirectToRoute($name, $params = [], $statusCode = 302)
    {
        $url = $this->app->router->url($name, $params);
        return $this->redirect($url, $statusCode);
    }

    /**
     * Redirect back to the previous page
     *
     * @param int $statusCode The HTTP status code (default: 302)
     * @param string $fallback The fallback URL if no referrer is set
     * @return Response
     */
    public function back($statusCode = 302, $fallback = '/')
    {
        $url = $this->request->headers->get('Referer', $fallback);
        return $this->redirect($url, $statusCode);
    }

    /**
     * Render a view
     *
     * @param string $view The view name
     * @param array $data The data to pass to the view
     * @param string|null $layout The layout to use (optional)
     * @return string
     */
    public function view($view, $data = [], $layout = null)
    {
        if ($layout !== null) {
            $this->view->layout($layout);
        }

        // Add flash messages to all views (if session is available)
        if ($this->session && method_exists($this->session, 'getFlashBag')) {
            $data['flash'] = $this->session->getFlashBag()->all();
        } else {
            $data['flash'] = [];
        }

        // Add auth and user to all views
        $data['auth'] = $this->auth;
        $data['user'] = $this->auth && method_exists($this->auth, 'user') ? $this->auth->user() : null;

        return $this->view->render($view, $data);
    }

    /**
     * Return a JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode The HTTP status code (default: 200)
     * @param array $headers Additional headers
     * @return Response
     */
    public function json($data, $statusCode = 200, $headers = [])
    {
        return Response::json($data, $statusCode, $headers);
    }

    /**
     * Return a 404 Not Found response
     *
     * @param string $message The error message
     * @return Response
     */
    public function notFound($message = 'Not Found')
    {
        return Response::error($message, 404);
    }

    /**
     * Return a 403 Forbidden response
     *
     * @param string $message The error message
     * @return Response
     */
    public function forbidden($message = 'Forbidden')
    {
        return Response::error($message, 403);
    }

    /**
     * Return a 401 Unauthorized response
     *
     * @param string $message The error message
     * @return Response
     */
    public function unauthorized($message = 'Unauthorized')
    {
        return Response::error($message, 401);
    }

    /**
     * Check if user is authenticated
     *
     * @throws \RuntimeException If user is not authenticated
     * @return void
     */
    public function requireLogin()
    {
        if (!$this->auth || !$this->auth->check()) {
            if ($this->session && method_exists($this->session, 'getFlashBag')) {
                $this->session->getFlashBag()->add('error', 'Please login to continue');
            }
            Response::redirect('/login')->send();
            exit;
        }
    }

    /**
     * Check if user has a specific role
     *
     * @param string|array $roles The role(s) to check
     * @throws \RuntimeException If user doesn't have the required role
     * @return void
     */
    public function requireRole($roles)
    {
        $this->requireLogin();

        if (!$this->auth->hasRole($roles)) {
            $this->forbidden()->send();
            exit;
        }
    }

    /**
     * Check if user is admin
     *
     * @throws \RuntimeException If user is not an admin
     * @return void
     */
    public function requireAdmin()
    {
        $this->requireRole('admin');
    }

    /**
     * Get the authenticated user
     *
     * @return \App\Models\User|null
     */
    public function user()
    {
        return $this->auth->user();
    }

    /**
     * Validate request data
     *
     * @param array $data The data to validate
     * @param array $rules The validation rules
     * @param array $messages Custom error messages
     * @return bool|Response True if validation passes, Response if validation fails
     */
    public function validate($data, $rules, $messages = [])
    {
        $validator = new Validator($data, $rules, $messages);

        if ($validator->fails()) {
            if ($this->request->expectsJson()) {
                return $this->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // For non-JSON requests, redirect back with errors
            $this->session->getFlashBag()->add('errors', $validator->errors());
            $this->session->getFlashBag()->add('old', $this->request->all());

            return $this->back()->withInput();
        }

        return true;
    }

    /**
     * Check if the current request is an AJAX request
     *
     * @return bool
     */
    protected function isAjax()
    {
        return $this->request->isAjax();
    }

    /**
     * Check if the current request is a JSON request
     *
     * @return bool
     */
    protected function isJson()
    {
        return $this->request->isJson();
    }

    /**
     * Get the current user ID
     *
     * @return int|null
     */
    protected function getUserId()
    {
        return $this->auth->id();
    }

    /**
     * Get the current user's role
     *
     * @return string|null
     */
    protected function getUserRole()
    {
        $user = $this->auth->user();
        return $user ? $user->role : null;
    }

    /**
     * Render a view with data and layout
     *
     * @param string $view The view name
     * @param array $data The data to pass to the view
     * @param string|null $layout The layout to use (optional)
     * @return void
     */
    public function render($view, $data = [], $layout = null)
    {
        if ($layout !== null) {
            $this->view->layout($layout);
        }

        // Add flash messages to all views
        $data['flash'] = $this->session->getFlashBag()->all();

        // Add auth and user to all views
        $data['auth'] = $this->auth;
        $data['user'] = $this->auth ? $this->auth->user() : null;

        echo $this->view->render($view, $data);
    }

    /**
     * Magic method to handle undefined method calls
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }

    public function index()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Get user role from session
        $role = $this->session->get('admin_role', 'admin');

        // Set page data
        $this->data['page_title'] = ucfirst($role) . ' Dashboard - APS Dream Home';
        $this->data['user_role'] = $role;

        // Get common dashboard statistics
        $this->data['stats'] = $this->getDashboardStats();

        // Role-specific data loading
        $this->loadRoleSpecificData($role);

        // Get quick actions
        $this->data['quickActions'] = $this->getQuickActions();

        // Get recent activities
        $this->data['recent_activities'] = $this->getRecentActivities();

        // Get system status
        $this->data['system_status'] = $this->getSystemStatus();

        // Get AI agents status
        $this->data['ai_agents_status'] = $this->getAIAgentsStatus();

        // Render the appropriate dashboard view based on role
        // If a specific role view exists, use it, otherwise use default
        $viewPath = "admin/dashboards/{$role}";
        if (!file_exists(APP_PATH . "/views/{$viewPath}.php")) {
            $viewPath = 'admin/dashboards/default';
        }

        $this->render($viewPath);
    }

    public function logout()
    {
        // Clear admin session
        session_unset();
        session_destroy();
        
        // Start fresh session to avoid issues
        session_start();
        
        $this->redirect('/admin/login');
    }

    public function properties()
    {
        // Set page data
        $this->data['page_title'] = 'Properties Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Properties', 'url' => $this->getBaseUrl() . 'admin/properties']
        ];

        // Get filter parameters
        $filters = [
            'search' => $this->request->get('search', ''),
            'status' => $this->request->get('status', ''),
            'type' => $this->request->get('type', ''),
            'featured' => $this->request->get('featured', ''),
            'page' => (int)$this->request->get('page', 1),
            'per_page' => (int)$this->request->get('per_page', 10),
            'sort' => $this->request->get('sort', 'created_at'),
            'order' => $this->request->get('order', 'DESC')
        ];

        // Get properties data
        $this->data['properties'] = Property::getAdminProperties($filters);
        $this->data['total_properties'] = Property::getAdminTotalProperties($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_properties'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_properties']);

        // Render the properties page
        $this->render('admin/properties/index');
    }

    public function users()
    {
        // Set page data
        $this->data['page_title'] = 'Users Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Users', 'url' => $this->getBaseUrl() . 'admin/users']
        ];

        // Get filter parameters
        $filters = [
            'search' => $this->request->get('search', ''),
            'role' => $this->request->get('role', ''),
            'status' => $this->request->get('status', ''),
            'page' => (int)$this->request->get('page', 1),
            'per_page' => (int)$this->request->get('per_page', 10),
            'sort' => $this->request->get('sort', 'created_at'),
            'order' => $this->request->get('order', 'DESC')
        ];

        // Get users data
        $this->data['users'] = \App\Models\User::getAdminUsers($filters);
        $this->data['total_users'] = \App\Models\User::getAdminTotalUsers($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_users'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_users']);

        // Render the users page
        $this->render('admin/users/index');
    }

    public function associates()
    {
        $this->data['page_title'] = 'Associates Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Associates', 'url' => $this->getBaseUrl() . 'admin/associates']
        ];

        // Get filter parameters
        $filters = [
            'search' => $this->request->get('search', ''),
            'status' => $this->request->get('status', ''),
            'page' => (int)$this->request->get('page', 1),
            'per_page' => (int)$this->request->get('per_page', 10),
            'sort' => $this->request->get('sort', 'created_at'),
            'order' => $this->request->get('order', 'DESC')
        ];

        // Get associates data
        $this->data['associates'] = \App\Models\Associate::getAdminAssociates($filters);
        $this->data['total_associates'] = \App\Models\Associate::getAdminTotalAssociates($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_associates'] / $filters['per_page']);

        $this->render('admin/associates/index');
    }

    public function customers()
    {
        $this->data['page_title'] = 'Customers Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Customers', 'url' => $this->getBaseUrl() . 'admin/customers']
        ];

        // Get filter parameters
        $filters = [
            'search' => $this->request->get('search', ''),
            'status' => $this->request->get('status', ''),
            'page' => (int)$this->request->get('page', 1),
            'per_page' => (int)$this->request->get('per_page', 10),
            'sort' => $this->request->get('sort', 'created_at'),
            'order' => $this->request->get('order', 'DESC')
        ];

        // Get customers data
        $this->data['customers'] = \App\Models\Customer::getAdminCustomers($filters);
        $this->data['total_customers'] = \App\Models\Customer::getAdminTotalCustomers($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_customers'] / $filters['per_page']);

        $this->render('admin/customers/index');
    }

    public function bookings()
    {
        $this->data['page_title'] = 'Bookings Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Bookings', 'url' => $this->getBaseUrl() . 'admin/bookings']
        ];

        // Get filter parameters
        $filters = [
            'search' => $this->request->get('search', ''),
            'status' => $this->request->get('status', ''),
            'page' => (int)$this->request->get('page', 1),
            'per_page' => (int)$this->request->get('per_page', 10),
            'sort' => $this->request->get('sort', 'created_at'),
            'order' => $this->request->get('order', 'DESC')
        ];

        // Get bookings data
        $this->data['bookings'] = \App\Models\Booking::getAdminBookings($filters);
        $this->data['total_bookings'] = \App\Models\Booking::getAdminTotalBookings($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_bookings'] / $filters['per_page']);

        $this->render('admin/bookings/index');
    }

    public function employees()
    {
        $this->data['page_title'] = 'Employees Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Employees', 'url' => $this->getBaseUrl() . 'admin/employees']
        ];

        // Get filter parameters
        $filters = [
            'search' => $this->request->get('search', ''),
            'status' => $this->request->get('status', ''),
            'page' => (int)$this->request->get('page', 1),
            'per_page' => (int)$this->request->get('per_page', 10),
            'sort' => $this->request->get('sort', 'created_at'),
            'order' => $this->request->get('order', 'DESC')
        ];

        // Get employees data
        $this->data['employees'] = \App\Models\Employee::getAdminEmployees($filters);
        $this->data['total_employees'] = \App\Models\Employee::getAdminTotalEmployees($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_employees'] / $filters['per_page']);

        $this->render('admin/employees/index');
    }

    public function settings()
    {
        // Set page data
        $this->data['page_title'] = 'Settings - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Settings', 'url' => $this->getBaseUrl() . 'admin/settings']
        ];

        // Get current settings
        $this->data['settings'] = $this->getSystemSettings();

        // Check for success/error messages
        $this->data['success'] = $this->request->get('success', '');
        $this->data['error'] = $this->request->get('error', '');

        // Render the settings page
        $this->render('admin/settings/index');
    }

    public function about()
    {
        $this->data['page_title'] = 'About Us - ' . APP_NAME;

        // Fetch about items
        try {
            $aboutObjects = About::all();
            $this->data['about_items'] = array_map(fn($item) => $item->toArray(), $aboutObjects);
        } catch (Exception $e) {
            $this->data['error'] = "Error loading about content: " . $e->getMessage();
            $this->data['about_items'] = [];
        }

        $this->render('admin/about/index');
    }

    public function aboutCreate()
    {
        $this->data['page_title'] = 'Add About Content - ' . APP_NAME;
        $this->render('admin/about/create');
    }

    public function aboutStore()
    {
        if ($this->request->method() === 'POST') {
            try {
                // Validate CSRF
                if (!$this->validateCsrfToken()) {
                    throw new Exception("Invalid CSRF token");
                }

                $title = trim($this->request->post('title', ''));
                $content = trim($this->request->post('content', ''));

                if (empty($title) || empty($content)) {
                    throw new Exception("Title and Content are required");
                }

                $data = [
                    'title' => $title,
                    'content' => $content,
                    'image' => ''
                ];

                // Handle Image Upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $uploadDir = "upload/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $data['image'] = $fileName;
                    }
                }

                $about = new About($data);
                if ($about->save()) {
                    $this->redirect('admin/about?msg=' . urlencode('Content added successfully'));
                    return;
                } else {
                    throw new Exception("Failed to save content");
                }
            } catch (Exception $e) {
                $this->data['error'] = "Error adding content: " . $e->getMessage();
                $this->data['page_title'] = 'Add About Content - ' . APP_NAME;
                $this->render('admin/aboutadd');
            }
        }
    }

    public function aboutEdit($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $about = About::with(['query'])->find($id);
            if (!$about) {
                $this->redirect('admin/about?error=' . urlencode('Content not found'));
                return;
            }

            $this->data['page_title'] = 'Edit About Content - ' . APP_NAME;
            $this->data['about_data'] = $about->toArray();
            $this->layout = 'layouts/admin';
            $this->render('admin/about/edit');
        } catch (Exception $e) {
            $this->redirect('admin/about?error=' . urlencode('Error loading content: ' . $e->getMessage()));
        }
    }

    public function aboutUpdate($id)
    {
        if ($this->request->method() === 'POST') {
            try {
                // Validate CSRF
                if (!$this->validateCsrfToken()) {
                    throw new Exception("Invalid CSRF token");
                }

                $about = About::find($id);
                if (!$about) {
                    throw new Exception("Content not found");
                }

                $title = trim($this->request->post('title', ''));
                $content = trim($this->request->post('content', ''));

                if (empty($title) || empty($content)) {
                    throw new Exception("Title and Content are required");
                }

                $about->title = $title;
                $about->content = $content;

                // Handle Image Upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $uploadDir = "upload/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        // Delete old image
                        if ($about->image && file_exists($uploadDir . $about->image)) {
                            unlink($uploadDir . $about->image);
                        }
                        $about->image = $fileName;
                    }
                }

                if ($about->save()) {
                    $this->redirect('admin/about?msg=' . urlencode('Content updated successfully'));
                } else {
                    throw new Exception("Failed to update content");
                }
            } catch (Exception $e) {
                $this->redirect("admin/about/edit/{$id}?error=" . urlencode("Error updating content: " . $e->getMessage()));
            }
        }
    }

    public function aboutDelete($id)
    {
        if ($this->request->method() === 'POST') {
            try {
                // Validate CSRF
                if (!$this->validateCsrfToken()) {
                    throw new Exception("Invalid CSRF token");
                }

                $about = About::find($id);
                if ($about) {
                    if ($about->image && file_exists('upload/' . $about->image)) {
                        unlink('upload/' . $about->image);
                    }

                    if ($about->delete()) {
                        $this->redirect('admin/about?msg=' . urlencode('Content deleted successfully'));
                    } else {
                        throw new Exception("Failed to delete from database");
                    }
                } else {
                    throw new Exception("Content not found");
                }
            } catch (Exception $e) {
                $this->redirect('admin/about?error=' . urlencode('Error deleting content: ' . $e->getMessage()));
            }
        }
    }

    public function contact()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Contact Us - ' . APP_NAME;
        $this->render('admin/contactview');
    }

    public function crmDashboard()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'CRM Dashboard - ' . APP_NAME;
        $this->render('admin/advanced_crm_dashboard');
    }

    public function aiHub()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'AI Control Center - ' . APP_NAME;
        $this->render('admin/ai_hub');
    }

    public function aiAgentDashboard()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'AI Agent Performance - ' . APP_NAME;
        $this->render('admin/ai_agent_dashboard');
    }

    public function aiLeadScoring()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'AI Lead Scoring - ' . APP_NAME;
        $this->render('admin/ai_lead_scoring');
    }

    public function superadminDashboard()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Superadmin Dashboard - ' . APP_NAME;
        $this->render('admin/superadmin_dashboard');
    }

    public function whatsappSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'WhatsApp Automation - ' . APP_NAME;
        $this->render('admin/whatsapp_automation');
    }

    public function siteSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Site Configuration - ' . APP_NAME;
        $this->render('admin/header_footer_settings');
    }

    public function apiSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'API Management - ' . APP_NAME;
        $this->render('admin/api_key_manager');
    }

    public function backupSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Backup Manager - ' . APP_NAME;
        $this->render('admin/backup_manager');
    }

    public function auditLogs()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Audit Logs - ' . APP_NAME;
        $this->render('admin/audit_access_log_view');
    }

    public function kisaanList()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Land Records - ' . APP_NAME;
        $this->render('admin/view_kisaan');
    }

    public function kisaanAdd()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Add Land Details - ' . APP_NAME;
        $this->render('admin/kissan');
    }

    public function mlmReports()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'MLM Reports - ' . APP_NAME;
        $this->render('admin/professional_mlm_reports');
    }

    public function mlmSettings()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'MLM Settings - ' . APP_NAME;
        $this->render('admin/professional_mlm_settings');
    }

    public function mlmPayouts()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Payouts - ' . APP_NAME;
        $this->render('admin/payouts');
    }

    public function mlmCommissions()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Commission Reports - ' . APP_NAME;
        $this->render('admin/professional_mlm_reports'); // Reusing same view as reports if specialized one not found
    }

    public function createProperty()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Create Property - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Properties', 'url' => $this->getBaseUrl() . 'admin/properties'],
            ['title' => 'Create', 'url' => $this->getBaseUrl() . 'admin/properties/create']
        ];

        // Get property types and agents for form
        $this->data['property_types'] = $this->getPropertyTypes();
        $this->data['agents'] = $this->getActiveAgents();

        // Render the create property form
        $this->render('admin/create_property');
    }

    public function invoices()
    {
        $filters = [
            'status' => $this->request->get('status', ''),
            'client_type' => $this->request->get('client_type', ''),
            'date_from' => $this->request->get('date_from', ''),
            'date_to' => $this->request->get('date_to', ''),
            'search' => $this->request->get('search', '')
        ];

        $invoiceModel = new Invoice();
        $invoices = $invoiceModel->getInvoices($filters);

        return $this->render('admin/invoices/index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'page_title' => $this->mlSupport->translate('Invoice Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function createInvoice()
    {
        // Get clients for dropdown
        $customers = $this->model('Customer')->getAllCustomers();
        $associates = $this->model('Associate')->getAllAssociates();

        return $this->render('admin/invoices/create', [
            'customers' => $customers,
            'associates' => $associates,
            'page_title' => $this->mlSupport->translate('Create Invoice') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function storeInvoice()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();
            $items = json_decode($data['items'], true);

            if (!$items || empty($items)) {
                $this->setFlash('error', 'At least one item is required');
                return $this->redirect('/admin/invoices/create');
            }

            $invoiceModel = new Invoice();
            $result = $invoiceModel->createInvoice([
                'client_id' => $data['client_id'] ?? null,
                'client_type' => $data['client_type'] ?? 'customer',
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'] ?? null,
                'client_phone' => $data['client_phone'] ?? null,
                'client_address' => $data['client_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'due_date' => $data['due_date'],
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'currency' => $data['currency'] ?? 'INR',
                'generated_by' => $this->request->session('auth')['id']
            ], $items);

            if ($result['success']) {
                $this->setFlash('success', 'Invoice created successfully');
                return $this->redirect('/admin/invoices');
            } else {
                $this->setFlash('error', $result['message']);
                return $this->redirect('/admin/invoices/create');
            }
        }

        return $this->redirect('/admin/invoices/create');
    }

    public function showInvoice($invoiceId)
    {
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->getInvoiceDetails($invoiceId);

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            return $this->redirect('/admin/invoices');
        }

        return $this->render('admin/invoices/show', [
            'invoice' => $invoice,
            'page_title' => 'Invoice ' . $invoice['invoice_number'] . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function generateInvoicePDF($invoiceId)
    {
        $invoiceModel = new Invoice();
        $html = $invoiceModel->generateInvoiceHTML($invoiceId);

        if (!$html) {
            $this->setFlash('error', 'Invoice not found');
            return $this->redirect('/admin/invoices');
        }

        // Set headers for PDF download
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="invoice_' . $invoiceId . '.html"');

        echo $html;
        exit;
    }

    public function sendInvoice()
    {
        if ($this->request->isMethod('post')) {
            $invoiceId = $this->request->post('invoice_id');

            $invoiceModel = new Invoice();
            $result = $invoiceModel->sendInvoice($invoiceId);

            if ($result['success']) {
                $this->setFlash('success', $result['message']);
            } else {
                $this->setFlash('error', $result['message']);
            }
        }

        return $this->redirect('/admin/invoices');
    }

    public function recordPayment()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $invoiceModel = new Invoice();
            $result = $invoiceModel->recordPayment($data['invoice_id'], [
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'received_by' => $this->request->session('auth')['id']
            ]);

            if ($result['success']) {
                $this->setFlash('success', $result['message']);
            } else {
                $this->setFlash('error', $result['message']);
            }
        }

        return $this->redirect('/admin/invoices');
    }

    public function sendPaymentReminder()
    {
        if ($this->request->isMethod('post')) {
            $invoiceId = $this->request->post('invoice_id');

            $invoiceModel = new Invoice();
            $result = $invoiceModel->sendPaymentReminder($invoiceId);

            if ($result['success']) {
                $this->setFlash('success', $result['message']);
            } else {
                $this->setFlash('error', $result['message']);
            }
        }

        return $this->redirect('/admin/invoices');
    }

    public function getOverdueInvoices()
    {
        $invoiceModel = new Invoice();
        $overdueInvoices = $invoiceModel->getOverdueInvoices();

        return $this->render('admin/invoices/overdue', [
            'overdue_invoices' => $overdueInvoices,
            'page_title' => $this->mlSupport->translate('Overdue Invoices') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function invoiceAnalytics()
    {
        $invoiceModel = new Invoice();

        // Get various statistics
        $totalInvoices = count($invoiceModel->getInvoices());
        $paidInvoices = count($invoiceModel->getInvoices(['status' => 'paid']));
        $pendingInvoices = count($invoiceModel->getInvoices(['status' => 'sent']));
        $overdueInvoices = count($invoiceModel->getOverdueInvoices());

        // Calculate total amounts
        $allInvoices = $invoiceModel->getInvoices();
        $totalAmount = array_sum(array_column($allInvoices, 'total_amount'));
        $paidAmount = array_sum(array_column(array_filter($allInvoices, function($inv) {
            return $inv['status'] === 'paid';
        }), 'total_amount'));

        $analytics = [
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'pending_invoices' => $pendingInvoices,
            'overdue_invoices' => $overdueInvoices,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $totalAmount - $paidAmount,
            'payment_rate' => $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0
        ];

        return $this->render('admin/invoices/analytics', [
            'analytics' => $analytics,
            'page_title' => $this->mlSupport->translate('Invoice Analytics') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function gstSettings()
    {
        $taxModel = new Tax();
        $gstSettings = $taxModel->getGstSettings();

        return $this->render('admin/tax/gst_settings', [
            'gst_settings' => $gstSettings,
            'page_title' => $this->mlSupport->translate('GST Settings') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function updateGstSettings()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $taxModel = new Tax();
            $result = $taxModel->updateGstSettings([
                'gstin' => $data['gstin'],
                'business_name' => $data['business_name'],
                'business_address' => $data['business_address'],
                'state_code' => $data['state_code'],
                'state_name' => $data['state_name'],
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'gst_type' => $data['gst_type'],
                'registration_date' => $data['registration_date'],
                'threshold_limit' => $data['threshold_limit']
            ]);

            if ($result['success']) {
                $this->setFlash('success', 'GST settings updated successfully');
            } else {
                $this->setFlash('error', $result['message']);
            }
        }

        return $this->redirect('/admin/tax/gst-settings');
    }

    public function gstr1Report()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $taxModel = new Tax();
        $gstr1Data = $taxModel->generateGSTR1($fromDate, $toDate);

        return $this->render('admin/tax/gstr1_report', [
            'gstr1_data' => $gstr1Data,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('GSTR-1 Report') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function gstr3bReport()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $taxModel = new Tax();
        $gstr3bData = $taxModel->generateGSTR3B($fromDate, $toDate);

        return $this->render('admin/tax/gstr3b_report', [
            'gstr3b_data' => $gstr3bData,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('GSTR-3B Report') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function exportGstr1Json()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $taxModel = new Tax();
        $jsonContent = $taxModel->exportGSTR1Json($fromDate, $toDate);

        // Set headers for JSON download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="gstr1_' . date('Ym', strtotime($fromDate)) . '.json"');
        header('Content-Length: ' . strlen($jsonContent));

        echo $jsonContent;
        exit;
    }

    public function hsnSummary()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $taxModel = new Tax();
        $hsnSummary = $taxModel->getHsnWiseSummary($fromDate, $toDate);

        return $this->render('admin/tax/hsn_summary', [
            'hsn_summary' => $hsnSummary,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('HSN/SAC Summary') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function taxLedger()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));
        $ledgerType = $this->request->get('ledger_type', 'all');

        $taxModel = new Tax();
        $ledgerSummary = $taxModel->getTaxLedgerSummary($fromDate, $toDate);

        // Filter by ledger type if specified
        if ($ledgerType !== 'all') {
            $ledgerSummary = array_filter($ledgerSummary, function($ledger) use ($ledgerType) {
                return $ledger['ledger_type'] === $ledgerType;
            });
        }

        return $this->render('admin/tax/tax_ledger', [
            'ledger_summary' => $ledgerSummary,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'ledger_type' => $ledgerType,
            'page_title' => $this->mlSupport->translate('Tax Ledger') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function taxReconciliation()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $taxModel = new Tax();
        $reconciliation = $taxModel->generateTaxReconciliation($fromDate, $toDate);

        return $this->render('admin/tax/tax_reconciliation', [
            'reconciliation' => $reconciliation,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('Tax Reconciliation') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function tdsReport()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $taxModel = new Tax();
        $tdsReport = $taxModel->generateTDSReport($fromDate, $toDate);

        return $this->render('admin/tax/tds_report', [
            'tds_report' => $tdsReport,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('TDS Report') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function gstReturns()
    {
        $status = $this->request->get('status', 'all');
        $returnType = $this->request->get('return_type', 'all');

        // Get GST returns from database
        $db = $this->model('Database');
        $query = "SELECT * FROM gst_returns WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if ($returnType !== 'all') {
            $query .= " AND return_type = ?";
            $params[] = $returnType;
        }

        $query .= " ORDER BY created_at DESC";

        $gstReturns = $db->query($query, $params)->fetchAll();

        return $this->render('admin/tax/gst_returns', [
            'gst_returns' => $gstReturns,
            'status_filter' => $status,
            'return_type_filter' => $returnType,
            'page_title' => $this->mlSupport->translate('GST Returns') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function taxComplianceDashboard()
    {
        $taxModel = new Tax();
        $gstSettings = $taxModel->getGstSettings();

        // Get current month data
        $currentMonth = date('Y-m');
        $fromDate = date('Y-m-01');
        $toDate = date('Y-m-t');

        $monthlyReport = $taxModel->generateGSTR3B($fromDate, $toDate);

        // Get compliance status
        $compliance = [
            'gstr1_filed' => false, // Would check gst_returns table
            'gstr3b_filed' => false,
            'tds_deducted' => 0,
            'tds_deposited' => 0,
            'pending_returns' => 0,
            'overdue_returns' => 0
        ];

        return $this->render('admin/tax/compliance_dashboard', [
            'gst_settings' => $gstSettings,
            'monthly_report' => $monthlyReport,
            'compliance' => $compliance,
            'current_month' => $currentMonth,
            'page_title' => $this->mlSupport->translate('Tax Compliance Dashboard') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function profitLossReport()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $financialModel = new FinancialReports();
        $pnlData = $financialModel->generateProfitLoss($fromDate, $toDate);

        return $this->render('admin/finance/profit_loss', [
            'pnl_data' => $pnlData,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('Profit & Loss Report') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function balanceSheet()
    {
        $asOfDate = $this->request->get('as_of_date', date('Y-m-d'));

        $financialModel = new FinancialReports();
        $balanceSheet = $financialModel->generateBalanceSheet($asOfDate);

        return $this->render('admin/finance/balance_sheet', [
            'balance_sheet' => $balanceSheet,
            'as_of_date' => $asOfDate,
            'page_title' => $this->mlSupport->translate('Balance Sheet') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function cashFlowStatement()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $financialModel = new FinancialReports();
        $cashFlow = $financialModel->generateCashFlow($fromDate, $toDate);

        return $this->render('admin/finance/cash_flow', [
            'cash_flow' => $cashFlow,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('Cash Flow Statement') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function trialBalance()
    {
        $asOfDate = $this->request->get('as_of_date', date('Y-m-d'));

        $financialModel = new FinancialReports();
        $trialBalance = $financialModel->getTrialBalance($asOfDate);

        return $this->render('admin/finance/trial_balance', [
            'trial_balance' => $trialBalance,
            'as_of_date' => $asOfDate,
            'page_title' => $this->mlSupport->translate('Trial Balance') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function chartOfAccounts()
    {
        $financialModel = new FinancialReports();
        $accounts = $financialModel->getChartOfAccounts();

        // Group by account type
        $groupedAccounts = [];
        foreach ($accounts as $account) {
            $type = $account['account_type'];
            if (!isset($groupedAccounts[$type])) {
                $groupedAccounts[$type] = [];
            }
            $groupedAccounts[$type][] = $account;
        }

        return $this->render('admin/finance/chart_of_accounts', [
            'grouped_accounts' => $groupedAccounts,
            'page_title' => $this->mlSupport->translate('Chart of Accounts') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function journalEntries()
    {
        $status = $this->request->get('status', 'all');
        $fromDate = $this->request->get('from_date', '');
        $toDate = $this->request->get('to_date', '');

        $query = "SELECT je.*, a.auser as posted_by_name
                  FROM journal_entries je
                  LEFT JOIN admin a ON je.posted_by = a.aid
                  WHERE 1=1";

        $params = [];

        if ($status !== 'all') {
            $query .= " AND je.status = ?";
            $params[] = $status;
        }

        if ($fromDate) {
            $query .= " AND je.entry_date >= ?";
            $params[] = $fromDate;
        }

        if ($toDate) {
            $query .= " AND je.entry_date <= ?";
            $params[] = $toDate;
        }

        $query .= " ORDER BY je.created_at DESC LIMIT 50";

        $db = $this->model('Database');
        $journalEntries = $db->query($query, $params)->fetchAll();

        return $this->render('admin/finance/journal_entries', [
            'journal_entries' => $journalEntries,
            'status_filter' => $status,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('Journal Entries') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function createJournalEntry()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();
            $lines = json_decode($data['lines'], true);

            if (!$lines || empty($lines)) {
                $this->setFlash('error', 'At least one journal line is required');
                return $this->redirect('/admin/finance/journal-entries/create');
            }

            $financialModel = new FinancialReports();
            $result = $financialModel->createJournalEntry([
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'reference_type' => $data['reference_type'] ?? 'journal',
                'reference_id' => $data['reference_id'] ?? null,
                'status' => $data['status'] ?? 'draft'
            ], $lines);

            if ($result['success']) {
                $this->setFlash('success', 'Journal entry created successfully');
                return $this->redirect('/admin/finance/journal-entries');
            } else {
                $this->setFlash('error', $result['message']);
                return $this->redirect('/admin/finance/journal-entries/create');
            }
        }

        $financialModel = new FinancialReports();
        $accounts = $financialModel->getChartOfAccounts();

        return $this->render('admin/finance/create_journal_entry', [
            'accounts' => $accounts,
            'page_title' => $this->mlSupport->translate('Create Journal Entry') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function postJournalEntry()
    {
        if ($this->request->isMethod('post')) {
            $entryId = $this->request->post('entry_id');

            $financialModel = new FinancialReports();
            $result = $financialModel->postJournalEntry($entryId);

            if ($result['success']) {
                $this->setFlash('success', $result['message']);
            } else {
                $this->setFlash('error', $result['message']);
            }
        }

        return $this->redirect('/admin/finance/journal-entries');
    }

    public function financialDashboard()
    {
        $financialModel = new FinancialReports();

        // Current month P&L summary
        $currentMonthPL = $financialModel->generateProfitLoss(date('Y-m-01'), date('Y-m-t'));

        // Current assets and liabilities summary
        $balanceSheet = $financialModel->generateBalanceSheet(date('Y-m-d'));

        // Recent journal entries
        $db = $this->model('Database');
        $recentEntries = $db->query(
            "SELECT je.*, a.auser as posted_by_name
             FROM journal_entries je
             LEFT JOIN admin a ON je.posted_by = a.aid
             ORDER BY je.created_at DESC LIMIT 5"
        )->fetchAll();

        // Account balances summary
        $accountBalances = $db->query(
            "SELECT account_type, COUNT(*) as count, SUM(current_balance) as total_balance
             FROM chart_of_accounts
             WHERE is_active = 1
             GROUP BY account_type"
        )->fetchAll();

        return $this->render('admin/finance/dashboard', [
            'current_month_pl' => $currentMonthPL,
            'balance_sheet' => $balanceSheet,
            'recent_entries' => $recentEntries,
            'account_balances' => $accountBalances,
            'page_title' => $this->mlSupport->translate('Financial Dashboard') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function budgets()
    {
        $filters = [
            'period_type' => $this->request->get('period_type', ''),
            'is_active' => $this->request->get('is_active', '1'),
            'year' => $this->request->get('year', date('Y'))
        ];

        $budgetModel = new Budget();
        $budgets = $budgetModel->getBudgets($filters);

        return $this->render('admin/budget/index', [
            'budgets' => $budgets,
            'filters' => $filters,
            'page_title' => $this->mlSupport->translate('Budget Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function createBudget()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();
            $budgetItems = json_decode($data['budget_items'], true);

            if (!$budgetItems || empty($budgetItems)) {
                $this->setFlash('error', 'At least one budget item is required');
                return $this->redirect('/admin/budget/create');
            }

            $budgetModel = new Budget();
            $result = $budgetModel->createBudget([
                'budget_name' => $data['budget_name'],
                'period_type' => $data['period_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $data['is_active'] ?? 1,
                'created_by' => $this->request->session('auth')['id']
            ], $budgetItems);

            if ($result['success']) {
                $this->setFlash('success', 'Budget created successfully');
                return $this->redirect('/admin/budget');
            } else {
                $this->setFlash('error', $result['message']);
                return $this->redirect('/admin/budget/create');
            }
        }

        $financialModel = new FinancialReports();
        $accounts = $financialModel->getChartOfAccounts();

        return $this->render('admin/budget/create', [
            'accounts' => $accounts,
            'page_title' => $this->mlSupport->translate('Create Budget') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function showBudget($budgetId)
    {
        $budgetModel = new Budget();
        $budget = $budgetModel->getBudgetDetails($budgetId);

        if (!$budget) {
            $this->setFlash('error', 'Budget not found');
            return $this->redirect('/admin/budget');
        }

        return $this->render('admin/budget/show', [
            'budget' => $budget,
            'page_title' => $budget['budget_name'] . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function budgetVarianceReport($budgetId)
    {
        $fromDate = $this->request->get('from_date', '');
        $toDate = $this->request->get('to_date', '');

        $budgetModel = new Budget();
        $report = $budgetModel->generateBudgetVarianceReport($budgetId, $fromDate, $toDate);

        if (!$report['budget']) {
            $this->setFlash('error', 'Budget not found');
            return $this->redirect('/admin/budget');
        }

        return $this->render('admin/budget/variance_report', [
            'report' => $report,
            'budget_id' => $budgetId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => 'Budget Variance Report - ' . $this->getConfig('app_name')
        ]);
    }

    public function createBudgetFromHistory()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $budgetModel = new Budget();
            $result = $budgetModel->createBudgetFromHistory(
                $data['budget_name'],
                $data['start_date'],
                $data['end_date'],
                $data['adjustment_percentage'] ?? 0
            );

            if ($result['success']) {
                $this->setFlash('success', 'Budget created from historical data successfully');
                return $this->redirect('/admin/budget');
            } else {
                $this->setFlash('error', $result['message']);
                return $this->redirect('/admin/budget/create-from-history');
            }
        }

        return $this->render('admin/budget/create_from_history', [
            'page_title' => $this->mlSupport->translate('Create Budget from History') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function budgetForecast()
    {
        $months = (int)$this->request->get('months', 12);
        $startDate = $this->request->get('start_date', date('Y-m-01'));

        $budgetModel = new Budget();
        $forecast = $budgetModel->generateBudgetForecast($startDate, $months);

        return $this->render('admin/budget/forecast', [
            'forecast' => $forecast,
            'months' => $months,
            'start_date' => $startDate,
            'page_title' => $this->mlSupport->translate('Budget Forecast') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function budgetAlerts()
    {
        $budgetModel = new Budget();
        $alerts = $budgetModel->getBudgetAlerts();

        return $this->render('admin/budget/alerts', [
            'alerts' => $alerts,
            'page_title' => $this->mlSupport->translate('Budget Alerts') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function budgetUtilization()
    {
        $fromDate = $this->request->get('from_date', date('Y-m-01'));
        $toDate = $this->request->get('to_date', date('Y-m-t'));

        $budgetModel = new Budget();
        $utilization = $budgetModel->getBudgetUtilizationReport($fromDate, $toDate);

        return $this->render('admin/budget/utilization', [
            'utilization' => $utilization,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page_title' => $this->mlSupport->translate('Budget Utilization') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    public function budgetComparison()
    {
        $budgetIds = $this->request->get('budget_ids', '');
        $budgetIdsArray = !empty($budgetIds) ? explode(',', $budgetIds) : [];

        $budgetModel = new Budget();
        $budgets = [];

        if (!empty($budgetIdsArray)) {
            foreach ($budgetIdsArray as $budgetId) {
                $budget = $budgetModel->getBudgetDetails((int)$budgetId);
                if ($budget) {
                    $budgets[] = $budget;
                }
            }
        }

        // Get all active budgets for selection
        $allBudgets = $budgetModel->getBudgets(['is_active' => 1], 100);

        return $this->render('admin/budget/comparison', [
            'budgets' => $budgets,
            'all_budgets' => $allBudgets,
            'selected_budget_ids' => $budgetIds,
            'page_title' => $this->mlSupport->translate('Budget Comparison') . ' - ' . $this->getConfig('app_name')
        ]);
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 1908 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//