<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Business\AssociateService;

class AssociateController extends AdminController
{
    private $associateService;

    public function __construct()
    {
        parent::__construct();
        $this->associateService = new AssociateService();
    }

    public function index()
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => trim($_GET['q'] ?? ''),
        ];

        $result = $this->associateService->getAllAssociates($page, $limit, $filters);

        $this->render('admin/business/associates/index', [
            'page_title' => 'Associates - Business',
            'associates' => $result['data'] ?? [],
            'pagination' => [
                'total' => $result['total'] ?? 0,
                'per_page' => $result['per_page'] ?? $limit,
                'current_page' => $result['current_page'] ?? $page,
                'last_page' => $result['last_page'] ?? 1,
            ],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $result = $this->associateService->getAssociateDetails($id);

        if (!$result['success']) {
            $_SESSION['error'] = $result['message'];
            $this->redirect('/admin/business/associates');
            return;
        }

        $this->render('admin/business/associates/show', [
            'page_title' => 'Associate #' . $id,
            'associate' => $result['data']['associate'] ?? [],
            'recent_sales' => $result['data']['recent_sales'] ?? [],
            'metrics' => $result['data']['metrics'] ?? [],
            'monthly_performance' => $result['data']['monthly_performance'] ?? [],
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function create()
    {
        $this->requireAdmin();

        $this->render('admin/business/associates/create', [
            'page_title' => 'Create Associate',
            'old' => $_SESSION['old_input'] ?? [],
            'errors' => $_SESSION['errors'] ?? [],
        ]);
        unset($_SESSION['old_input'], $_SESSION['errors']);
    }

    public function store()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/business/associates/create');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'joining_date' => $_POST['joining_date'] ?? date('Y-m-d'),
            'commission_rate' => (float)($_POST['commission_rate'] ?? 0),
            'status' => $_POST['status'] ?? 'active',
        ];

        $result = $this->associateService->createAssociate($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/admin/business/associates');
        } else {
            $_SESSION['errors'] = $result['errors'] ?? [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/admin/business/associates/create');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $result = $this->associateService->getAssociateDetails($id);

        if (!$result['success']) {
            $_SESSION['error'] = $result['message'];
            $this->redirect('/admin/business/associates');
            return;
        }

        $this->render('admin/business/associates/edit', [
            'page_title' => 'Edit Associate #' . $id,
            'associate' => $result['data']['associate'] ?? [],
            'errors' => $_SESSION['errors'] ?? [],
        ]);
        unset($_SESSION['errors']);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/business/associates/edit/{$id}");
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'commission_rate' => (float)($_POST['commission_rate'] ?? 0),
            'status' => $_POST['status'] ?? 'active',
        ];

        $result = $this->associateService->updateAssociate($id, $data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect("/admin/business/associates/show/{$id}");
        } else {
            $_SESSION['errors'] = $result['errors'] ?? [$result['message']];
            $this->redirect("/admin/business/associates/edit/{$id}");
        }
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $result = $this->associateService->deleteAssociate($id);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/admin/business/associates');
    }

    public function updateCommissionRate()
    {
        $this->requireAdmin();

        $id = (int)($_POST['associate_id'] ?? 0);
        $rate = (float)($_POST['commission_rate'] ?? 0);

        if (!$id) {
            return $this->jsonResponse(['success' => false, 'message' => 'Associate ID is required']);
        }

        $result = $this->associateService->updateCommissionRate($id, $rate);
        return $this->jsonResponse($result);
    }

    public function performanceReport()
    {
        $this->requireAdmin();

        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
        ];

        $result = $this->associateService->getPerformanceReport($filters);
        return $this->jsonResponse($result);
    }

    public function getTopPerformers()
    {
        $this->requireAdmin();

        $limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
        $period = in_array($_GET['period'] ?? 'month', ['month', 'quarter', 'year']) ? $_GET['period'] : 'month';

        $result = $this->associateService->getTopPerformers($limit, $period);
        return $this->jsonResponse($result);
    }

    public function exportAssociates()
    {
        $this->requireAdmin();

        $format = $_POST['format'] ?? 'csv';
        $filters = ['status' => $_POST['status'] ?? ''];

        $result = $this->associateService->exportAssociates($format, $filters);
        return $this->jsonResponse($result);
    }

    public function searchAssociates()
    {
        $this->requireAdmin();

        $query = trim($_GET['q'] ?? '');
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));

        if (empty($query)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Search query is required']);
        }

        $result = $this->associateService->getAllAssociates(1, $limit, ['search' => $query]);
        return $this->jsonResponse(['success' => true, 'data' => $result['data'] ?? []]);
    }

    public function activate($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $result = $this->associateService->updateAssociate($id, ['status' => 'active']);

        if ($result['success']) {
            $_SESSION['success'] = 'Associate activated successfully';
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/admin/business/associates');
    }

    public function deactivate($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $result = $this->associateService->updateAssociate($id, ['status' => 'inactive']);

        if ($result['success']) {
            $_SESSION['success'] = 'Associate deactivated successfully';
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/admin/business/associates');
    }
}
