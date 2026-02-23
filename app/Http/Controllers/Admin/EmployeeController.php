<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Performance;

class EmployeeController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'offboard']]);
    }

    /**
     * Display a listing of employees
     */
    public function index()
    {
        $filters = [
            'search' => $this->request->get('search', ''),
            'department' => $this->request->get('department', '')
        ];

        $employees = $this->model('Employee')->getAllEmployees($filters);

        return $this->render('admin/employees/index', [
            'employees' => $employees,
            'filters' => $filters,
            'page_title' => $this->mlSupport->translate('Employee Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $employeeModel = $this->model('Employee');
        $roles = $employeeModel->getRoles();
        $departments = $employeeModel->getDepartments();

        return $this->render('admin/employees/create', [
            'roles' => $roles,
            'departments' => $departments,
            'page_title' => $this->mlSupport->translate('Add New Employee') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store a newly created employee in storage
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $data = $this->request->all();

        // Basic Validation
        if (empty($data['name']) || empty($data['email'])) {
            $this->setFlash('error', $this->mlSupport->translate('Name and Email are required.'));
            return $this->back();
        }

        if (!\filter_var($data['email'], \FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid email address.'));
            return $this->back();
        }

        // Sanitize data
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = h($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        $data = $sanitizedData;

        $employeeModel = $this->model('Employee');
        // Check if email already exists
        $existing = $employeeModel->getEmployeeByEmail($data['email']);
        if ($existing) {
            $this->setFlash('error', $this->mlSupport->translate('Email already registered.'));
            return $this->back();
        }

        try {
            // Map form fields to model fields if necessary
            // The view likely sends 'role_id' and 'department_id' if using select dropdowns correctly
            // If view sends 'role' instead of 'role_id', we need to map it.
            // Assuming view is updated or sends correct IDs.

            $employeeId = $employeeModel->createEmployee($data);

            if ($employeeId) {
                $this->logActivity('Add Employee', 'Added employee: ' . h($data['name']));

                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee added successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error adding employee. Please try again.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Show the form for editing an employee
     */
    public function edit($id)
    {
        $id = intval($id);
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeById($id);
        $roles = $employeeModel->getRoles();
        $departments = $employeeModel->getDepartments();

        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('admin/employees');
        }

        return $this->render('admin/employees/edit', [
            'employee' => $employee,
            'roles' => $roles,
            'departments' => $departments,
            'page_title' => $this->mlSupport->translate('Edit Employee') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update the specified employee in storage
     */
    public function update($id)
    {
        $id = intval($id);

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $data = $this->request->all();
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeById($id);

        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('admin/employees');
        }

        // Basic Validation
        if (empty($data['name'])) {
            $this->setFlash('error', $this->mlSupport->translate('Name is required.'));
            return $this->back();
        }

        // Sanitize data
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = h($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        $data = $sanitizedData;

        try {
            $success = $employeeModel->updateEmployee($id, $data);

            if ($success) {
                $this->logActivity('Update Employee', 'Updated employee: ' . h($data['name']));

                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee updated successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error updating employee.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Remove the specified employee from storage
     */
    public function destroy($id)
    {
        $id = intval($id);

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->back();
        }

        try {
            $employeeModel = $this->model('Employee');
            $success = $employeeModel->deleteEmployee($id);

            if ($success) {
                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->logActivity('Delete Employee', 'Soft deleted employee ID: ' . $id);
                $this->setFlash('success', $this->mlSupport->translate('Employee deleted successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error deleting employee.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Offboard an employee (deactivate and revoke roles)
     */
    public function offboard($id)
    {
        $id = intval($id);

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->back();
        }

        try {
            $employeeModel = $this->model('Employee');
            $employee = $employeeModel->getEmployeeById($id);

            if (!$employee) {
                $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
                return $this->redirect('admin/employees');
            }

            $success = $employeeModel->offboardEmployee($id);

            if ($success) {
                $this->logActivity('Offboard Employee', 'Offboarded employee: ' . ($employee['name'] ?? $id));
                $this->sendOffboardNotification($employee);

                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee offboarded successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error offboarding employee.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error offboarding employee') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Display employee performance reviews
     */
    public function performance()
    {
        $employeeId = $this->request->get('employee_id');
        $reviewType = $this->request->get('type', 'all');
        $status = $this->request->get('status', 'all');

        $performanceModel = new Performance();

        $filters = [];
        if ($reviewType !== 'all') $filters['review_type'] = $reviewType;
        if ($status !== 'all') $filters['status'] = $status;

        $reviews = [];
        $employees = [];

        if ($employeeId) {
            $reviews = $performanceModel->getEmployeeReviews($employeeId);
            $employee = $this->model('Employee')->getEmployeeById($employeeId);
            $employees = $employee ? [$employee] : [];
        } else {
            $employees = $this->model('Employee')->getAllEmployees();
        }

        return $this->render('admin/employees/performance', [
            'reviews' => $reviews,
            'employees' => $employees,
            'selected_employee' => $employeeId,
            'filters' => [
                'type' => $reviewType,
                'status' => $status
            ],
            'page_title' => $this->mlSupport->translate('Employee Performance') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Create new performance review
     */
    public function createPerformanceReview()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $performanceModel = new Performance();
            $result = $performanceModel->createReview([
                'employee_id' => $data['employee_id'],
                'reviewer_id' => $this->request->session('auth')['id'],
                'review_period_start' => $data['review_period_start'],
                'review_period_end' => $data['review_period_end'],
                'review_type' => $data['review_type']
            ]);

            if ($result['success']) {
                $this->setFlash('success', 'Performance review created successfully');
            } else {
                $this->setFlash('error', $result['message']);
            }

            return $this->redirect('/admin/employees/performance');
        }

        $employees = $this->model('Employee')->getAllEmployees();

        return $this->render('admin/employees/create_performance_review', [
            'employees' => $employees,
            'page_title' => $this->mlSupport->translate('Create Performance Review') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Edit performance review
     */
    public function editPerformanceReview($reviewId)
    {
        $performanceModel = new Performance();
        $review = $performanceModel->find($reviewId);

        if (!$review) {
            $this->setFlash('error', 'Performance review not found');
            return $this->redirect('/admin/employees/performance');
        }

        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $result = $performanceModel->updateReview($reviewId, [
                'overall_rating' => $data['overall_rating'],
                'performance_level' => $data['performance_level'],
                'goals_achievement' => $data['goals_achievement'],
                'strengths' => $data['strengths'],
                'areas_for_improvement' => $data['areas_for_improvement'],
                'development_plan' => $data['development_plan'],
                'reviewer_comments' => $data['reviewer_comments'],
                'status' => $data['status'],
                'review_date' => $data['review_date'],
                'next_review_date' => $data['next_review_date']
            ]);

            if ($result['success']) {
                $this->setFlash('success', 'Performance review updated successfully');
            } else {
                $this->setFlash('error', $result['message']);
            }

            return $this->redirect('/admin/employees/performance');
        }

        $employee = $this->model('Employee')->getEmployeeById($review['employee_id']);
        $feedback = $performanceModel->getReviewFeedback($reviewId);

        return $this->render('admin/employees/edit_performance_review', [
            'review' => $review,
            'employee' => $employee,
            'feedback' => $feedback,
            'page_title' => $this->mlSupport->translate('Edit Performance Review') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Display KPIs management
     */
    public function kpis()
    {
        $performanceModel = new Performance();
        $kpis = $performanceModel->query("SELECT * FROM kpis WHERE is_active = 1 ORDER BY category, name")->fetchAll();

        return $this->render('admin/employees/kpis', [
            'kpis' => $kpis,
            'page_title' => $this->mlSupport->translate('KPI Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Set employee KPIs
     */
    public function setEmployeeKPIs()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $performanceModel = new Performance();
            $result = $performanceModel->setEmployeeKPIs(
                $data['employee_id'],
                $data['kpis'],
                $data['period_start'],
                $data['period_end']
            );

            if ($result['success']) {
                $this->setFlash('success', 'Employee KPIs set successfully');
            } else {
                $this->setFlash('error', $result['message']);
            }

            return $this->redirect('/admin/employees/kpis');
        }

        $employees = $this->model('Employee')->getAllEmployees();
        $performanceModel = new Performance();
        $availableKPIs = $performanceModel->query("SELECT * FROM kpis WHERE is_active = 1 ORDER BY category, name")->fetchAll();

        return $this->render('admin/employees/set_employee_kpis', [
            'employees' => $employees,
            'available_kpis' => $availableKPIs,
            'page_title' => $this->mlSupport->translate('Set Employee KPIs') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Display performance goals
     */
    public function goals()
    {
        $employeeId = $this->request->get('employee_id');
        $status = $this->request->get('status', 'all');

        $performanceModel = new Performance();

        $goals = [];
        $employees = [];

        if ($employeeId) {
            $goals = $performanceModel->getEmployeeGoals($employeeId);
            $employee = $this->model('Employee')->getEmployeeById($employeeId);
            $employees = $employee ? [$employee] : [];
        } else {
            $employees = $this->model('Employee')->getAllEmployees();
            // Get all goals if no specific employee selected
            $goals = $performanceModel->query(
                "SELECT pg.*, e.name as employee_name, a.auser as assigned_by_name
                 FROM performance_goals pg
                 LEFT JOIN employees e ON pg.employee_id = e.id
                 LEFT JOIN admin a ON pg.assigned_by = a.aid
                 ORDER BY pg.created_at DESC LIMIT 50"
            )->fetchAll();
        }

        return $this->render('admin/employees/goals', [
            'goals' => $goals,
            'employees' => $employees,
            'selected_employee' => $employeeId,
            'status_filter' => $status,
            'page_title' => $this->mlSupport->translate('Performance Goals') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Create performance goal
     */
    public function createGoal()
    {
        if ($this->request->isMethod('post')) {
            $data = $this->request->all();

            $performanceModel = new Performance();
            $result = $performanceModel->createGoal([
                'employee_id' => $data['employee_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'target_date' => $data['target_date'],
                'assigned_by' => $this->request->session('auth')['id']
            ]);

            if ($result['success']) {
                $this->setFlash('success', 'Performance goal created successfully');
            } else {
                $this->setFlash('error', $result['message']);
            }

            return $this->redirect('/admin/employees/goals');
        }

        $employees = $this->model('Employee')->getAllEmployees();

        return $this->render('admin/employees/create_goal', [
            'employees' => $employees,
            'page_title' => $this->mlSupport->translate('Create Performance Goal') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Performance analytics dashboard
     */
    public function performanceAnalytics()
    {
        $performanceModel = new Performance();

        // Get overall analytics
        $analytics = $performanceModel->getPerformanceAnalytics();

        // Get recent reviews
        $recentReviews = $performanceModel->query(
            "SELECT pr.*, e.name as employee_name
             FROM performance_reviews pr
             LEFT JOIN employees e ON pr.employee_id = e.id
             WHERE pr.status = 'completed'
             ORDER BY pr.review_date DESC LIMIT 10"
        )->fetchAll();

        // Get top performers
        $topPerformers = $performanceModel->query(
            "SELECT e.name, AVG(pr.overall_rating) as avg_rating, COUNT(*) as review_count
             FROM performance_reviews pr
             LEFT JOIN employees e ON pr.employee_id = e.id
             WHERE pr.status = 'completed' AND pr.overall_rating >= 4.0
             GROUP BY pr.employee_id
             ORDER BY avg_rating DESC LIMIT 10"
        )->fetchAll();

        return $this->render('admin/employees/performance_analytics', [
            'analytics' => $analytics,
            'recent_reviews' => $recentReviews,
            'top_performers' => $topPerformers,
            'page_title' => $this->mlSupport->translate('Performance Analytics') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    protected function sendOffboardNotification($employee)
    {
        try {
            // Include legacy notification system if available
            if (file_exists(ABSPATH . '/includes/notification_manager.php')) {
                require_once ABSPATH . '/includes/notification_manager.php';
                require_once ABSPATH . '/includes/email_service.php';

                $nm = new \NotificationManager(null, new \EmailService());

                // Notify Admin
                $nm->send([
                    'user_id' => 1, // Admin ID
                    'template' => 'EMPLOYEE_OFFBOARDED',
                    'data' => [
                        'employee_name' => $employee['name'],
                        'admin_name' => $this->request()->session('auth')['username'] ?? 'Admin'
                    ],
                    'channels' => ['db']
                ]);
            }
        } catch (\Exception $e) {
            // Log notification error but don't fail the offboarding process
            logger()->error("Failed to send offboard notification: " . $e->getMessage());
        }
    }
}
