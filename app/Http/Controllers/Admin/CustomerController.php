<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use \Exception;
use App\Models\User\Customer;
use App\Core\Security;

class CustomerController extends AdminController
{
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = $this->model('Customer');
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'updateProfile', 'createAlert', 'toggleFavorite', 'sendInvitation', 'acceptInvitation']]);
    }

    /**
     * Search customers for Select2
     */
    public function search()
    {
        $search = $this->request->get('search', '');
        $page = (int)$this->request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $result = $this->customerModel->searchCustomers($search, $limit, $offset);

            return $this->jsonResponse([
                'items' => $result['items'],
                'more' => ($page * $limit) < $result['total']
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        $searchTerm = $this->request->get('search');
        $customers = $this->customerModel->getAllCustomers($searchTerm);

        return $this->render('admin/customers/index', [
            'page_title' => ($this->mlSupport ? $this->mlSupport->translate('Customer Management') : 'Customer Management') . ' - ' . APP_NAME,
            'customers' => $customers,
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return $this->render('admin/customers/create', [
            'page_title' => ($this->mlSupport ? $this->mlSupport->translate('Add Customer') : 'Add Customer') . ' - ' . APP_NAME
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('admin/customers');
        }

        $data = $_POST;

        // Basic Validation
        if (empty($data['name'])) {
            $this->setFlash('error', 'Customer name is required.');
            return $this->redirect('admin/customers/create');
        }

        $customerId = $this->customerModel->registerCustomer($data);

        if ($customerId) {
            $this->setFlash('success', 'Customer added successfully.');
            return $this->redirect('admin/customers');
        } else {
            $this->setFlash('error', 'Error adding customer.');
            return $this->redirect('admin/customers/create');
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $customer = $this->customerModel->getCustomerById($id);

        if (!$customer) {
            $this->setFlash('error', 'Customer not found.');
            return $this->redirect('admin/customers');
        }

        return $this->render('admin/customers/edit', [
            'page_title' => 'Edit Customer - ' . APP_NAME,
            'customer' => $customer
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('admin/customers');
        }

        $data = $_POST;
        $success = $this->customerModel->updateCustomer($id, $data);

        if ($success) {
            $this->setFlash('success', 'Customer updated successfully.');
            return $this->redirect('admin/customers');
        } else {
            $this->setFlash('error', 'Error updating customer.');
            return $this->redirect('admin/customers/edit/' . $id);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $customer = $this->customerModel->getWithUserInfo($id);

        if (!$customer) {
            $this->setFlash('error', 'Customer not found.');
            return $this->redirect('admin/customers');
        }

        return $this->render('admin/customers/show', [
            'page_title' => 'Customer Profile - ' . APP_NAME,
            'customer' => $customer
        ]);
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy($id)
    {
        if ($this->customerModel->deleteCustomer($id)) {
            $this->setFlash('success', 'Customer deleted successfully.');
        } else {
            $this->setFlash('error', 'Error deleting customer.');
        }

        return $this->redirect('admin/customers');
    }

    // Customer Portal Methods

    public function login()
    {
        if ($this->isCustomerLoggedIn()) {
            $this->redirect('/customer/dashboard');
        }

        $this->view('customers/login', [
            'page_title' => 'Customer Login',
            'error' => $this->getFlash('login_error')
        ]);
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/login');
        }

        $email = Security::sanitize($_POST['email']);
        $password = $_POST['password'];

        $customer = $this->customerModel->authenticateCustomer($email, $password);

        if ($customer) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_role'] = 'customer';
            
            $this->customerModel->updateCustomerProfile($customer['id'], ['last_login' => date('Y-m-d H:i:s')]);
            $this->redirect('/customer/dashboard');
        } else {
            $this->setFlash('login_error', 'Invalid email or password.');
            $this->redirect('/customer/login');
        }
    }

    public function logout()
    {
        unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_role']);
        $this->redirect('/customer/login');
    }

    public function dashboard()
    {
        if (!$this->isCustomerLoggedIn()) {
            $this->redirect('/customer/login');
        }

        $customerId = $_SESSION['customer_id'];
        $customer = $this->customerModel->getCustomerById($customerId);
        
        $stats = [
            'total_bookings' => $this->customerModel->countBookings($customerId),
            'total_payments' => $this->customerModel->sumPayments($customerId),
            'active_alerts' => $this->customerModel->countAlerts($customerId),
            'favorite_properties' => $this->customerModel->countFavorites($customerId)
        ];

        $this->view('customers/dashboard', [
            'customer' => $customer,
            'stats' => $stats,
            'page_title' => 'My Dashboard'
        ]);
    }

    public function properties()
    {
        if (!$this->isCustomerLoggedIn()) { $this->redirect('/customer/login'); }
        
        $filters = $_GET;
        $properties = $this->customerModel->searchProperties($_SESSION['customer_id'], $filters);
        $propertyTypes = $this->getPropertyTypes();
        $locations = $this->getLocations();

        $this->view('customers/properties', [
            'properties' => $properties,
            'filters' => $filters,
            'property_types' => $propertyTypes,
            'locations' => $locations,
            'page_title' => 'Search Properties'
        ]);
    }

    public function propertyDetails($propertyId)
    {
        if (!$this->isCustomerLoggedIn()) { $this->redirect('/customer/login'); }
        
        $property = $this->customerModel->getPropertyDetails($propertyId, $_SESSION['customer_id']);
        if (!$property) {
            $this->setFlash('error', 'Property not found.');
            $this->redirect('/customer/properties');
        }

        $this->view('customers/property_details', [
            'property' => $property,
            'page_title' => $property['title']
        ]);
    }

    public function toggleFavorite($propertyId)
    {
        if (!$this->isCustomerLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/properties');
        }

        $customerId = $_SESSION['customer_id'];
        $favorites = $this->customerModel->getCustomerFavorites($customerId);
        $isFavorited = false;
        foreach ($favorites as $favorite) {
            if ($favorite['id'] == $propertyId) {
                $isFavorited = true;
                break;
            }
        }

        if ($isFavorited) {
            $this->customerModel->removeFromFavorites($customerId, $propertyId);
            $this->setFlash('success', 'Removed from favorites.');
        } else {
            $this->customerModel->addToFavorites($customerId, $propertyId);
            $this->setFlash('success', 'Added to favorites.');
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/customer/properties');
    }

    public function bookings()
    {
        if (!$this->isCustomerLoggedIn()) { $this->redirect('/customer/login'); }
        
        $filters = $_GET;
        $bookings = $this->customerModel->getCustomerBookings($_SESSION['customer_id'], $filters);

        $this->view('customers/bookings', [
            'bookings' => $bookings,
            'filters' => $filters,
            'page_title' => 'My Bookings'
        ]);
    }

    public function payments()
    {
        if (!$this->isCustomerLoggedIn()) { $this->redirect('/customer/login'); }
        
        $filters = $_GET;
        $payments = $this->customerModel->getCustomerPayments($_SESSION['customer_id'], $filters);

        $this->view('customers/payments', [
            'payments' => $payments,
            'filters' => $filters,
            'page_title' => 'My Payments'
        ]);
    }

    public function alerts()
    {
        if (!$this->isCustomerLoggedIn()) { $this->redirect('/customer/login'); }
        
        $filters = $_GET;
        $alerts = $this->customerModel->getCustomerAlerts($_SESSION['customer_id'], $filters);

        $this->view('customers/alerts', [
            'alerts' => $alerts,
            'filters' => $filters,
            'page_title' => 'Property Alerts'
        ]);
    }

    public function createAlert()
    {
        if (!$this->isCustomerLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/alerts');
        }

        $data = $_POST;
        if ($this->customerModel->createPropertyAlert($_SESSION['customer_id'], $data)) {
            $this->setFlash('success', 'Alert created successfully.');
        } else {
            $this->setFlash('error', 'Failed to create alert.');
        }

        $this->redirect('/customer/alerts');
    }

    public function updateProfile()
    {
        if (!$this->isCustomerLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/profile');
        }

        $data = [
            'name' => Security::sanitize($_POST['name']),
            'phone' => Security::sanitize($_POST['phone']),
            'address' => Security::sanitize($_POST['address']),
            'city' => Security::sanitize($_POST['city']),
            'state' => Security::sanitize($_POST['state']),
            'pincode' => Security::sanitize($_POST['pincode'])
        ];

        if ($this->customerModel->updateCustomerProfile($_SESSION['customer_id'], $data)) {
            $this->setFlash('success', 'Profile updated successfully.');
            $_SESSION['customer_name'] = $data['name'];
        } else {
            $this->setFlash('error', 'Failed to update profile.');
        }

        $this->redirect('/customer/profile');
    }

    public function emiCalculator()
    {
        $this->view('customers/emi_calculator', [
            'page_title' => 'EMI Calculator',
            'property_id' => $_GET['property_id'] ?? null
        ]);
    }

    public function calculateEMI()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { return; }
        
        $loanAmount = (float)$_POST['loan_amount'];
        $interestRate = (float)$_POST['interest_rate'];
        $loanTenure = (int)$_POST['loan_tenure'];
        
        $monthlyRate = $interestRate / (12 * 100);
        $numInstallments = $loanTenure * 12;
        
        if ($monthlyRate > 0) {
            $emi = ($loanAmount * $monthlyRate * pow(1 + $monthlyRate, $numInstallments)) / (pow(1 + $monthlyRate, $numInstallments) - 1);
        } else {
            $emi = $loanAmount / $numInstallments;
        }

        $result = [
            'monthly_emi' => round($emi, 2),
            'total_payment' => round($emi * $numInstallments, 2),
            'total_interest' => round(($emi * $numInstallments) - $loanAmount, 2)
        ];

        echo json_encode($result);
    }

    // Helpers
    protected function getPropertyTypes()
    {
        return $this->db->fetchAll("SELECT id, name FROM property_types WHERE status = 'active'");
    }

    protected function getLocations()
    {
        return $this->db->fetchAll("SELECT DISTINCT city, state FROM properties WHERE status = 'available'");
    }

    protected function isCustomerLoggedIn()
    {
        return isset($_SESSION['customer_id']);
    }
}