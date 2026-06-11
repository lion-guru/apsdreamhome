<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Business\AssociateService;

/**
 * Associate Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AssociateController extends AdminController
{
    private $associateService;

    public function __construct()
    {
        parent::__construct();
        $this->associateService = new AssociateService();
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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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
        $this->requireAdmin();

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

}
