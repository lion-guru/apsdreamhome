<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Business\AssociateService;
use App\Services\Auth\AuthenticationService;

/**
 * Associate Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AssociateController extends AdminController
{
    private $associateService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->associateService = new AssociateService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new \App\Core\ViewRenderer();
    }

    /**
     * This controller validates CSRF manually (via AuthenticationService)
     * so skip the parent's automatic CSRF check on POST.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Show users list
     */
    private function buildRequest($routerParam = null): array
    {
        $params = [];
        if ($routerParam !== null && !is_array($routerParam)) {
            $params = ['id' => $routerParam];
        } elseif (is_array($routerParam) && isset($routerParam['params'])) {
            $params = $routerParam['params'];
        }
        return [
            'get' => $_GET,
            'post' => $_POST,
            'params' => $params,
            'server' => $_SERVER
        ];
    }

    public function index($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('view_associates')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $filters = [
            'status' => $request['get']['status'] ?? '',
            'search' => $request['get']['search'] ?? ''
        ];

        $result = $this->associateService->getAllAssociates($page, $limit, $filters);

        $data = [
            'title' => 'users - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'users' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'per_page' => $result['per_page'],
                'total' => $result['total']
            ],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('business/users/index', $data);
    }

    /**
     * Show associate details
     */
    public function show($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('view_associates')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Associate ID is required'];
            $this->redirect('/users');
            return;
        }

        $result = $this->associateService->getAssociateDetails($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/users');
            return;
        }

        $data = [
            'title' => 'Associate Details - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'associate' => $result['data']['associate'],
            'recent_sales' => $result['data']['recent_sales'],
            'metrics' => $result['data']['metrics'],
            'monthly_performance' => $result['data']['monthly_performance'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('business/users/show', $data);
    }

    /**
     * Show create associate form
     */
    public function create($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('create_associates')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Create Associate - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('business/users/create', $data);
    }

    /**
     * Store new associate
     */
    public function store($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('create_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'name' => trim($request['post']['name'] ?? ''),
            'email' => trim($request['post']['email'] ?? ''),
            'phone' => trim($request['post']['phone'] ?? ''),
            'address' => trim($request['post']['address'] ?? ''),
            'joining_date' => $request['post']['joining_date'] ?? date('Y-m-d'),
            'commission_rate' => floatval($request['post']['commission_rate'] ?? 0),
            'status' => $request['post']['status'] ?? 'active'
        ];

        $result = $this->associateService->createAssociate($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/users');
        } else {
            $_SESSION['errors'] = $result['errors'] ?? [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/users/create');
        }

        return $result;
    }

    /**
     * Show edit associate form
     */
    public function edit($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('edit_associates')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Associate ID is required'];
            $this->redirect('/users');
            return;
        }

        $result = $this->associateService->getAssociateDetails($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/users');
            return;
        }

        $data = [
            'title' => 'Edit Associate - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'associate' => $result['data']['associate'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('business/users/edit', $data);
    }

    /**
     * Update associate
     */
    public function update($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('edit_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Associate ID is required'
            ];
        }

        $data = [
            'name' => trim($request['post']['name'] ?? ''),
            'email' => trim($request['post']['email'] ?? ''),
            'phone' => trim($request['post']['phone'] ?? ''),
            'address' => trim($request['post']['address'] ?? ''),
            'commission_rate' => floatval($request['post']['commission_rate'] ?? 0),
            'status' => $request['post']['status'] ?? 'active'
        ];

        $result = $this->associateService->updateAssociate($id, $data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect("/users/$id");
        } else {
            $_SESSION['errors'] = $result['errors'] ?? [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect("/users/$id/edit");
        }

        return $result;
    }

    /**
     * Delete associate
     */
    public function destroy($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('delete_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Associate ID is required'
            ];
        }

        $result = $this->associateService->deleteAssociate($id);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/users');

        return $result;
    }

    /**
     * Update commission rate (AJAX)
     */
    public function updateCommissionRate($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('edit_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['post']['associate_id'] ?? null;
        $rate = floatval($request['post']['commission_rate'] ?? 0);

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Associate ID is required'
            ];
        }

        return $this->associateService->updateCommissionRate($id, $rate);
    }

    /**
     * Get performance report
     */
    public function performanceReport($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('view_reports')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'start_date' => $request['get']['start_date'] ?? date('Y-m-01'),
            'end_date' => $request['get']['end_date'] ?? date('Y-m-d')
        ];

        $result = $this->associateService->getPerformanceReport($filters);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/users');
            return;
        }

        $data = [
            'title' => 'Associate Performance Report - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'performance' => $result['data']['performance'],
            'summary' => $result['data']['summary'],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('business/users/performance', $data);
    }

    /**
     * Get top performers (AJAX)
     */
    public function getTopPerformers($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('view_reports')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $limit = intval($request['get']['limit'] ?? 10);
        $period = $request['get']['period'] ?? 'month';

        return $this->associateService->getTopPerformers($limit, $period);
    }

    /**
     * Export users (AJAX)
     */
    public function exportAssociates($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('export_data')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $format = $request['post']['format'] ?? 'csv';
        $filters = [
            'status' => $request['post']['status'] ?? ''
        ];

        return $this->associateService->exportAssociates($format, $filters);
    }

    /**
     * Search users (AJAX)
     */
    public function searchAssociates($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('view_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $query = trim($request['get']['q'] ?? '');
        $limit = intval($request['get']['limit'] ?? 20);

        if (empty($query)) {
            return [
                'success' => false,
                'message' => 'Search query is required'
            ];
        }

        try {
            $users = \App\Models\Associate::search($query, $limit);

            return [
                'success' => true,
                'data' => array_map(function ($associate) {
                    return $associate->toArray();
                }, $users)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Search failed'
            ];
        }
    }

    /**
     * Activate associate
     */
    public function activate($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('edit_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Associate ID is required'
            ];
        }

        $result = $this->associateService->updateAssociate($id, ['status' => 'active']);

        if ($result['success']) {
            $_SESSION['success'] = 'Associate activated successfully';
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/users');

        return $result;
    }

    /**
     * Deactivate associate
     */
    public function deactivate($request = null)
    {
        $request = $this->buildRequest($request);
        // Check authentication
        if (!$this->authService->isAuthenticated() || !$this->authService->hasPermission('edit_associates')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Associate ID is required'
            ];
        }

        $result = $this->associateService->updateAssociate($id, ['status' => 'inactive']);

        if ($result['success']) {
            $_SESSION['success'] = 'Associate deactivated successfully';
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/users');

        return $result;
    }

    /**
     * Redirect helper
     */
    public function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
