<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use \Exception;

class CustomerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Search customers for Select2
     */
    public function search()
    {
        header('Content-Type: application/json');

        $request = $this->request();
        $search = $request->get('search', '');
        $page = (int)$request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $customerModel = $this->model('Customer');
            $result = $customerModel->searchCustomers($search, $limit, $offset);

            echo json_encode([
                'items' => $result['items'],
                'more' => ($page * $limit) < $result['total']
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        $request = $this->request();
        $searchTerm = $request->get('search');

        $customerModel = $this->model('Customer');
        $customers = $customerModel->getAllCustomers($searchTerm);

        return $this->render('admin/customers/index', [
            'page_title' => $this->mlSupport->translate('Customer Management') . ' - ' . $this->getConfig('app_name'),
            'customers' => $customers,
            'searchTerm' => $searchTerm,
            'breadcrumbs' => [$this->mlSupport->translate("Customers") => "admin/customers"]
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return $this->render('admin/customers/create', [
            'page_title' => $this->mlSupport->translate('Add Customer') . ' - ' . $this->getConfig('app_name'),
            'breadcrumbs' => [$this->mlSupport->translate("Customers") => "admin/customers", $this->mlSupport->translate("Add Customer") => ""]
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->post();

        // Basic Validation
        if (empty($data['name'])) {
            $this->setFlash('error', $this->mlSupport->translate('Customer name is required.'));
            return $this->back();
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'name', 'email', 'mobile', 'address', 'city', 'state', 'pincode',
            'country_id', 'status', 'description'
        ];

        $customerData = [];
        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $customerData[$field] = h($data[$field]);
            }
        }

        $customerModel = $this->model('Customer');
        if (!empty($customerData['email'])) {
            $existing = $customerModel->getCustomerByEmail($customerData['email']);
            if ($existing) {
                $this->setFlash('error', $this->mlSupport->translate('Email already registered to another customer.'));
                return $this->back();
            }
        }

        $customerId = $customerModel->registerCustomer($customerData);

        if ($customerId) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            // Log the activity
            $this->logActivity('Add Customer', 'Added customer: ' . h($customerData['name']) . ' (ID: ' . $customerId . ')');

            $this->setFlash('success', $this->mlSupport->translate('Customer added successfully.'));
            return $this->redirect('admin/customers');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Error adding customer. Please try again.'));
            return $this->back();
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $id = intval($id);
        $customerModel = $this->model('Customer');
        $customer = $customerModel->getCustomerById($id);

        if (!$customer) {
            $this->setFlash('error', $this->mlSupport->translate('Customer not found.'));
            return $this->redirect('admin/customers');
        }

        return $this->render('admin/customers/edit', [
            'page_title' => $this->mlSupport->translate('Edit Customer') . ' - ' . $this->getConfig('app_name'),
            'customer' => $customer,
            'breadcrumbs' => [
                $this->mlSupport->translate("Customers") => "admin/customers",
                $this->mlSupport->translate("Edit Customer") => ""
            ]
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->post();

        $customerModel = $this->model('Customer');
        $customer = $customerModel->getCustomerById($id);

        if (!$customer) {
            $this->setFlash('error', $this->mlSupport->translate('Customer not found.'));
            return $this->redirect('admin/customers');
        }

        // Basic Validation
        if (empty($data['name'])) {
            $this->setFlash('error', $this->mlSupport->translate('Customer name is required.'));
            return $this->back();
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'name', 'email', 'mobile', 'address', 'city', 'state', 'pincode',
            'country_id', 'status', 'description'
        ];

        $customerData = [];
        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $customerData[$field] = h($data[$field]);
            }
        }

        if (!empty($customerData['email']) && $customerData['email'] !== $customer['email']) {
            $existing = $customerModel->getCustomerByEmail($customerData['email']);
            if ($existing) {
                $this->setFlash('error', $this->mlSupport->translate('Email already registered to another customer.'));
                return $this->back();
            }
        }

        $success = $customerModel->updateCustomer($id, $customerData);

        if ($success) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            // Log the activity
            $this->logActivity('Update Customer', 'Updated customer: ' . h($customerData['name']) . ' (ID: ' . $id . ')');

            $this->setFlash('success', $this->mlSupport->translate('Customer updated successfully.'));
            return $this->redirect('admin/customers');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Error updating customer. Please try again.'));
            return $this->back();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $id = intval($id);
        $customerModel = $this->model('Customer');
        $customer = $customerModel->getWithUserInfo($id);

        if (!$customer) {
            $this->setFlash('error', $this->mlSupport->translate('Customer not found.'));
            return $this->redirect('admin/customers');
        }

        return $this->render('admin/customers/show', [
            'page_title' => $this->mlSupport->translate('Customer Profile') . ' - ' . $this->getConfig('app_name'),
            'customer' => $customer,
            'breadcrumbs' => [
                $this->mlSupport->translate("Customers") => "admin/customers",
                $this->mlSupport->translate("Profile") => ""
            ]
        ]);
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('admin/customers');
        }

        $customerModel = $this->model('Customer');
        $customer = $customerModel->getCustomerById($id);

        if (!$customer) {
            $this->setFlash('error', $this->mlSupport->translate('Customer not found.'));
            return $this->redirect('admin/customers');
        }

        if ($customerModel->deleteCustomer($id)) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Delete Customer', 'Deleted customer ID: ' . $id);
            $this->setFlash('success', $this->mlSupport->translate('Customer deleted successfully.'));
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Error deleting customer.'));
        }

        return $this->redirect('admin/customers');
    }
}
