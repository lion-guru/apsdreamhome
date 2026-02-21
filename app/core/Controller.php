<?php

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Auth;
use App\Core\Database\Database;
use App\Core\Session\SessionManager;

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
        // Get the base path from APP_ROOT constant or use default
        $basePath = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
        $app = App::getInstance($basePath);

        $this->request = $app->request();
        $this->response = $app->response();
        $this->view = new View();
        $this->session = $app->session();

        // Debug auth
        if (!isset($app->auth)) {
            // echo "DEBUG: App auth is not set in Controller\n";
        }

        $this->auth = $app->auth ?? null;
        $this->db = $app->db();
        $this->session = $app->session();
        $this->app = $app;
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

        // Add flash messages to all views
        $data['flash'] = $this->session->getFlashBag()->all();

        // Add auth and user to all views
        $data['auth'] = $this->auth;
        $data['user'] = $this->auth ? $this->auth->user() : null;

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
        if (!$this->auth->check()) {
            $this->session->getFlashBag()->add('error', 'Please login to access this page');
            $this->redirect('/login')->send();
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
}
