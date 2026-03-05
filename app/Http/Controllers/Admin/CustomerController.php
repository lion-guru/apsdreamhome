<?php

namespace App\Http\Controllers\Admin;

use \Exception;

class CustomerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
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
            $customerModel = $this->model('Customer');
            $result = $customerModel->searchCustomers($search, $limit, $offset);

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

        $customerModel = $this->model('Customer');
        $customers = $customerModel->getAllCustomers($searchTerm);

        return $this->render('admin/customers/index', [
            'page_title' => ($this->mlSupport ? $this->mlSupport->translate('Customer Management') : 'Customer Management') . ' - ' . APP_NAME,
            'customers' => $customers,
            'searchTerm' => $searchTerm,
            'breadcrumbs' => [($this->mlSupport ? $this->mlSupport->translate("Customers") : 'Customers') => "admin/customers"]
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
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $data = $this->request->post();

        // Basic Validation
        if (empty($data['name'])) {
            $this->setFlash('error', $this->mlSupport->translate('Customer name is required.'));
            return $this->back();
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'name',
            'email',
            'mobile',
            'address',
            'city',
            'state',
            'pincode',
            'country_id',
            'status',
            'description'
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
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $data = $this->request->post();

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
            'name',
            'email',
            'mobile',
            'address',
            'city',
            'state',
            'pincode',
            'country_id',
            'status',
            'description'
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
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('admin/customers');
        }

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

    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->isCustomerLoggedIn()) {
            $this->redirect('/customer/dashboard');
        }

        $data = [
            'page_title' => 'Customer Login - APS Dream Home',
            'error' => $this->getFlash('login_error')
        ];

        $this->view('customers/login', $data);
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/login');
        }

        $email = Security::sanitize($_POST['email']) ?? '';
        $password = Security::sanitize($_POST['password']) ?? '';

        if (empty($email) || empty($password)) {
            $this->setFlash('login_error', 'Please enter both email and password.');
            $this->redirect('/customer/login');
        }

        $customer = $this->customerModel->authenticateCustomer($email, $password);

        if ($customer) {
            // Set session variables
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_role'] = 'customer';

            // Update last login
            $this->customerModel->updateCustomerProfile($customer['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            $this->redirect('/customer/dashboard');
        } else {
            $this->setFlash('login_error', 'Invalid email or password.');
            $this->redirect('/customer/login');
        }
    }

    public function logout()
    {
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_role']);

        $this->redirect('/customer/login');
    }

    public function properties()
    {
        $customerId = $_SESSION['customer_id'];

        // Get search filters
        $filters = [];
        if (!empty($_GET['property_type'])) {
            $filters['property_type'] = $_GET['property_type'];
        }
        if (!empty($_GET['city'])) {
            $filters['city'] = $_GET['city'];
        }
        if (!empty($_GET['state'])) {
            $filters['state'] = $_GET['state'];
        }
        if (!empty($_GET['min_price'])) {
            $filters['min_price'] = $_GET['min_price'];
        }
        if (!empty($_GET['max_price'])) {
            $filters['max_price'] = $_GET['max_price'];
        }
        if (!empty($_GET['bedrooms'])) {
            $filters['bedrooms'] = $_GET['bedrooms'];
        }
        if (!empty($_GET['bathrooms'])) {
            $filters['bathrooms'] = $_GET['bathrooms'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        // Search properties
        $properties = $this->customerModel->searchProperties($customerId, $filters);

        // Get property types for filter
        $propertyTypes = $this->getPropertyTypes();

        // Get locations for filter
        $locations = $this->getLocations();

        $data = [
            'properties' => $properties,
            'filters' => $filters,
            'property_types' => $propertyTypes,
            'locations' => $locations,
            'page_title' => 'Search Properties - APS Dream Home'
        ];

        $this->view('customers/properties', $data);
    }

    public function propertyDetails($propertyId)
    {
        $customerId = $_SESSION['customer_id'];

        // Get property details
        $property = $this->customerModel->getPropertyDetails($propertyId, $customerId);

        if (!$property) {
            $this->setFlash('error', 'Property not found or not available.');
            $this->redirect('/customer/properties');
        }

        // Get related properties
        $relatedProperties = $this->getRelatedProperties($propertyId, $property['property_type_id'], $property['city']);

        // Get property reviews
        $reviews = $this->getPropertyReviews($propertyId);

        $data = [
            'property' => $property,
            'related_properties' => $relatedProperties,
            'reviews' => $reviews,
            'page_title' => htmlspecialchars($property['title']) . ' - APS Dream Home'
        ];

        $this->view('customers/property_details', $data);
    }

    public function favorites()
    {
        $customerId = $_SESSION['customer_id'];

        // Get favorites with filters
        $filters = [];
        if (!empty($_GET['property_type'])) {
            $filters['property_type'] = $_GET['property_type'];
        }
        if (!empty($_GET['city'])) {
            $filters['city'] = $_GET['city'];
        }
        if (!empty($_GET['min_price'])) {
            $filters['min_price'] = $_GET['min_price'];
        }
        if (!empty($_GET['max_price'])) {
            $filters['max_price'] = $_GET['max_price'];
        }

        $favorites = $this->customerModel->getCustomerFavorites($customerId, $filters);

        // Get property types for filter
        $propertyTypes = $this->getPropertyTypes();

        // Get locations for filter
        $locations = $this->getLocations();

        $data = [
            'favorites' => $favorites,
            'filters' => $filters,
            'property_types' => $propertyTypes,
            'locations' => $locations,
            'page_title' => 'My Favorites - APS Dream Home'
        ];

        $this->view('customers/favorites', $data);
    }

    public function toggleFavorite($propertyId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/properties');
        }

        $customerId = $_SESSION['customer_id'];

        // Check if already favorited
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
            $this->setFlash('success', 'Property removed from favorites.');
        } else {
            $this->customerModel->addToFavorites($customerId, $propertyId);
            $this->setFlash('success', 'Property added to favorites.');
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/customer/properties');
    }

    public function bookings()
    {
        $customerId = $_SESSION['customer_id'];

        // Get bookings with filters
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['property_type'])) {
            $filters['property_type'] = $_GET['property_type'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $bookings = $this->customerModel->getCustomerBookings($customerId, $filters);

        // Get property types for filter
        $propertyTypes = $this->getPropertyTypes();

        $data = [
            'bookings' => $bookings,
            'filters' => $filters,
            'property_types' => $propertyTypes,
            'page_title' => 'My Bookings - APS Dream Home'
        ];

        $this->view('customers/bookings', $data);
    }

    public function payments()
    {
        $customerId = $_SESSION['customer_id'];

        // Get payments with filters
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['payment_method'])) {
            $filters['payment_method'] = $_GET['payment_method'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['min_amount'])) {
            $filters['min_amount'] = $_GET['min_amount'];
        }
        if (!empty($_GET['max_amount'])) {
            $filters['max_amount'] = $_GET['max_amount'];
        }

        $payments = $this->customerModel->getCustomerPayments($customerId, $filters);

        $data = [
            'payments' => $payments,
            'filters' => $filters,
            'page_title' => 'My Payments - APS Dream Home'
        ];

        $this->view('customers/payments', $data);
    }

    public function submitReview($propertyId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/property/' . $propertyId);
        }

        $customerId = $_SESSION['customer_id'];
        $rating = Security::sanitize($_POST['rating']) ?? 0;
        $reviewText = Security::sanitize($_POST['review_text']) ?? '';
        $anonymous = isset(Security::sanitize($_POST['anonymous'])) ? 1 : 0;

        if ($rating < 1 || $rating > 5) {
            $this->setFlash('error', 'Please provide a valid rating between 1 and 5.');
            $this->redirect('/customer/property/' . $propertyId);
        }

        $success = $this->customerModel->submitPropertyReview($customerId, $propertyId, [
            'rating' => $rating,
            'review_text' => $reviewText,
            'anonymous' => $anonymous
        ]);

        if ($success) {
            $this->setFlash('success', 'Thank you for your review! It will be published after approval.');
        } else {
            $this->setFlash('error', 'Failed to submit review. Please try again.');
        }

        $this->redirect('/customer/property/' . $propertyId);
    }

    public function profile()
    {
        $customerId = $_SESSION['customer_id'];
        $customer = $this->customerModel->getCustomerById($customerId);

        $data = [
            'customer' => $customer,
            'page_title' => 'My Profile - APS Dream Home'
        ];

        $this->view('customers/profile', $data);
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/profile');
        }

        $customerId = $_SESSION['customer_id'];

        $data = [
            'name' => Security::sanitize($_POST['name']) ?? '',
            'phone' => Security::sanitize($_POST['phone']) ?? '',
            'address' => Security::sanitize($_POST['address']) ?? '',
            'city' => Security::sanitize($_POST['city']) ?? '',
            'state' => Security::sanitize($_POST['state']) ?? '',
            'pincode' => Security::sanitize($_POST['pincode']) ?? '',
            'date_of_birth' => Security::sanitize($_POST['date_of_birth']) ?? '',
            'occupation' => Security::sanitize($_POST['occupation']) ?? '',
            'marital_status' => Security::sanitize($_POST['marital_status']) ?? '',
            'anniversary_date' => Security::sanitize($_POST['anniversary_date']) ?? '',
            'referral_source' => Security::sanitize($_POST['referral_source']) ?? ''
        ];

        $success = $this->customerModel->updateCustomerProfile($customerId, $data);

        if ($success) {
            $this->setFlash('success', 'Profile updated successfully.');
            $_SESSION['customer_name'] = $data['name'];
        } else {
            $this->setFlash('error', 'Failed to update profile. Please try again.');
        }

        $this->redirect('/customer/profile');
    }

    public function alerts()
    {
        $customerId = $_SESSION['customer_id'];

        // Get alerts with filters
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $alerts = $this->customerModel->getCustomerAlerts($customerId, $filters);

        // Get property types for creating new alerts
        $propertyTypes = $this->getPropertyTypes();

        // Get locations for creating new alerts
        $locations = $this->getLocations();

        $data = [
            'alerts' => $alerts,
            'filters' => $filters,
            'property_types' => $propertyTypes,
            'locations' => $locations,
            'page_title' => 'Property Alerts - APS Dream Home'
        ];

        $this->view('customers/alerts', $data);
    }

    public function createAlert()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/alerts');
        }

        $customerId = $_SESSION['customer_id'];

        $data = [
            'property_type_id' => Security::sanitize($_POST['property_type_id']) ?? null,
            'city' => Security::sanitize($_POST['city']) ?? null,
            'state' => Security::sanitize($_POST['state']) ?? null,
            'min_price' => Security::sanitize($_POST['min_price']) ?? null,
            'max_price' => Security::sanitize($_POST['max_price']) ?? null,
            'min_bedrooms' => Security::sanitize($_POST['min_bedrooms']) ?? null,
            'max_bedrooms' => Security::sanitize($_POST['max_bedrooms']) ?? null,
            'alert_type' => Security::sanitize($_POST['alert_type']) ?? 'email',
            'frequency' => Security::sanitize($_POST['frequency']) ?? 'daily'
        ];

        $success = $this->customerModel->createPropertyAlert($customerId, $data);

        if ($success) {
            $this->setFlash('success', 'Property alert created successfully.');
        } else {
            $this->setFlash('error', 'Failed to create alert. Please try again.');
        }

        $this->redirect('/customer/alerts');
    }

    public function emiCalculator()
    {
        $propertyId = $_GET['property_id'] ?? null;

        $data = [
            'property_id' => $propertyId,
            'page_title' => 'EMI Calculator - APS Dream Home'
        ];

        $this->view('customers/emi_calculator', $data);
    }

    public function calculateEMI()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/emi-calculator');
        }

        $loanAmount = Security::sanitize($_POST['loan_amount']) ?? 0;
        $interestRate = Security::sanitize($_POST['interest_rate']) ?? 0;
        $loanTenure = Security::sanitize($_POST['loan_tenure']) ?? 0;
        $propertyId = Security::sanitize($_POST['property_id']) ?? null;

        // EMI Calculation Formula: EMI = [P x R x (1+R)^N] / [(1+R)^N-1]
        // P = Principal loan amount
        // R = Monthly interest rate
        // N = Number of monthly installments

        $monthlyRate = $interestRate / (12 * 100);
        $numInstallments = $loanTenure * 12;

        if ($monthlyRate > 0) {
            $emi = ($loanAmount * $monthlyRate * pow(1 + $monthlyRate, $numInstallments)) /
                (pow(1 + $monthlyRate, $numInstallments) - 1);
        } else {
            $emi = $loanAmount / $numInstallments;
        }

        $totalPayment = $emi * $numInstallments;
        $totalInterest = $totalPayment - $loanAmount;

        $result = [
            'loan_amount' => $loanAmount,
            'interest_rate' => $interestRate,
            'loan_tenure' => $loanTenure,
            'monthly_emi' => round($emi, 2),
            'total_interest' => round($totalInterest, 2),
            'total_payment' => round($totalPayment, 2)
        ];

        // Save calculation if customer is logged in
        if ($this->isCustomerLoggedIn() && $propertyId) {
            $this->customerModel->saveEMICalculation($_SESSION['customer_id'], $propertyId, $result);
        }

        echo json_encode($result);
    }

    public function propertyViews()
    {
        $customerId = $_SESSION['customer_id'];

        $propertyViews = $this->customerModel->getPropertyViews($customerId);

        $data = [
            'property_views' => $propertyViews,
            'page_title' => 'Property Views History - APS Dream Home'
        ];

        $this->view('customers/property_views', $data);
    }

    public function emiHistory()
    {
        $customerId = $_SESSION['customer_id'];

        $emiHistory = $this->customerModel->getEMICalculatorHistory($customerId);

        $data = [
            'emi_history' => $emiHistory,
            'page_title' => 'EMI Calculator History - APS Dream Home'
        ];

        $this->view('customers/emi_history', $data);
    }

    public function associateBenefits()
    {
        $customerId = $_SESSION['customer_id'];

        // Get customer's potential associate benefits
        $benefits = $this->customerModel->getAssociateBenefits($customerId);

        $data = [
            'benefits' => $benefits,
            'page_title' => 'Associate Benefits - APS Dream Home'
        ];

        $this->view('customers/associate_benefits', $data);
    }

    public function associateInvitations()
    {
        $customerId = $_SESSION['customer_id'];

        // Get associate invitations
        $invitations = $this->customerModel->getAssociateInvitations($customerId);

        $data = [
            'invitations' => $invitations,
            'page_title' => 'Associate Invitations - APS Dream Home'
        ];

        $this->view('customers/associate_invitations', $data);
    }

    public function acceptInvitation($invitationId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/associate-invitations');
        }

        $customerId = $_SESSION['customer_id'];

        $result = $this->customerModel->acceptAssociateInvitation($invitationId);

        if ($result['success']) {
            $this->setFlash('success', $result['message'] . ' Your associate code: ' . $result['associate_code']);
            $_SESSION['customer_role'] = 'associate'; // Update session role
            $this->redirect('/associate/dashboard');
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/customer/associate-invitations');
        }
    }

    public function becomeAssociate()
    {
        $customerId = $_SESSION['customer_id'];

        // Get customer's benefits
        $benefits = $this->customerModel->getAssociateBenefits($customerId);

        // Get potential associates for admin reference
        $potentialAssociates = $this->customerModel->getPotentialAssociates(['limit' => 5]);

        $data = [
            'benefits' => $benefits,
            'potential_associates' => $potentialAssociates,
            'page_title' => 'Become an Associate - APS Dream Home'
        ];

        $this->view('customers/become_associate', $data);
    }

    public function sendInvitation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customer/become-associate');
        }

        $customerId = $_SESSION['customer_id'];
        $sponsorId = Security::sanitize($_POST['sponsor_id']) ?? null;
        $message = Security::sanitize($_POST['message']) ?? null;

        $result = $this->customerModel->sendAssociateInvitation($customerId, $sponsorId, $message);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/customer/become-associate');
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\Customer\CustomerController.php

function dashboard()
    {
        $customerId = $_SESSION['customer_id'];

        // Get customer details
        $customer = $this->customerModel->getCustomerById($customerId);

        if (!$customer) {
            $this->logout();
        }
function reviews()
    {
        $customerId = $_SESSION['customer_id'];

        // Get reviews with filters
        $filters = [];
        if (!empty($_GET['rating'])) {
            $filters['rating'] = $_GET['rating'];
        }
function isCustomerLoggedIn()
    {
        return isset($_SESSION['customer_id']);
    }
function middleware($middleware, array $options = [])
    {
        if ($middleware === 'customer.auth' && !$this->isCustomerLoggedIn()) {
            $this->redirect('/customer/login');
        }
function getPropertyTypes()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, icon FROM property_types WHERE status = 'active' ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
function getLocations()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT city, state, COUNT(*) as property_count
                FROM properties
                WHERE city IS NOT NULL AND city != '' AND status = 'available'
                GROUP BY city, state
                HAVING property_count > 0
                ORDER BY property_count DESC, state ASC, city ASC
                LIMIT 50
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
function getRelatedProperties($propertyId, $propertyTypeId, $city, $limit = 3)
    {
        try {
            $sql = "
                SELECT p.*, pt.name as property_type_name, pt.icon as property_type_icon,
                       u.name as agent_name, u.phone as agent_phone,
                       (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                       (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.id != :property_id
                  AND p.status = 'available'
                  AND (p.property_type_id = :property_type_id OR p.city = :city)
                ORDER BY RAND()
                LIMIT :limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'property_id' => $propertyId,
                'property_type_id' => $propertyTypeId,
                'city' => $city,
                'limit' => $limit
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
function getPropertyReviews($propertyId, $limit = 10)
    {
        try {
            $sql = "
                SELECT pr.*, u.name as customer_name, u.profile_image as customer_image
                FROM property_reviews pr
                LEFT JOIN users u ON pr.customer_id = u.id
                WHERE pr.property_id = :property_id AND pr.status = 'approved'
                ORDER BY pr.created_at DESC
                LIMIT :limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'property_id' => $propertyId,
                'limit' => $limit
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\CustomerController.php

function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'Please login to access this page');
            $this->redirect('/login');
        }
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 1017 lines. Consider optimizations:
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