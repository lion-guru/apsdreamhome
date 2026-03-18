<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Services\AgentAssignmentService;
use App\Services\ReferralService;
use Exception;
use PDO;

/**
 * Enhanced PropertyController
 * Complete Property Buy/Sell workflow for all user types
 */
class PropertyController extends BaseController
{
    private PDO $db;
    private AgentAssignmentService $agentAssignmentService;
    private ReferralService $referralService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->agentAssignmentService = new AgentAssignmentService();
        $this->referralService = new ReferralService();
    }

    /**
     * Index method - List all properties with filters
     */
    public function index()
    {
        try {
            $filters = $this->getPropertyFilters();
            $properties = $this->getFilteredProperties($filters);
            
            $data = [
                'page_title' => 'Properties - APS Dream Home',
                'page_description' => 'Browse our extensive collection of residential and commercial properties',
                'properties' => $properties,
                'filters' => $filters,
                'property_types' => $this->getPropertyTypes(),
                'cities' => $this->getCities(),
                'price_ranges' => $this->getPriceRanges()
            ];

            $this->view('properties/index', $data);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Show property details with buy/sell options
     */
    public function show($id)
    {
        try {
            $property = $this->getPropertyById($id);
            
            if (!$property) {
                $this->redirect('/properties', ['error' => 'Property not found']);
                return;
            }

            // Get similar properties
            $similarProperties = $this->getSimilarProperties($property);
            
            // Get agent assignment if user is logged in
            $assignedAgent = null;
            if (isset($_SESSION['user_id'])) {
                $assignedAgent = $this->getUserAssignedAgent($_SESSION['user_id']);
            }

            $data = [
                'page_title' => $property['title'] . ' - APS Dream Home',
                'page_description' => substr($property['description'], 0, 160),
                'property' => $property,
                'similar_properties' => $similarProperties,
                'assigned_agent' => $assignedAgent,
                'can_buy' => $this->canUserBuyProperty($property),
                'can_sell' => $this->canUserSellProperty($property),
                'financing_options' => $this->getFinancingOptions($property),
                'documents_required' => $this->getRequiredDocuments($property)
            ];

            $this->view('properties/show', $data);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Buy property workflow
     */
    public function buy($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->redirect('/login?redirect=' . urlencode("/properties/{$id}/buy"));
                return;
            }

            $property = $this->getPropertyById($id);
            if (!$property) {
                $this->redirect('/properties', ['error' => 'Property not found']);
                return;
            }

            if (!$this->canUserBuyProperty($property)) {
                $this->redirect("/properties/{$id}", ['error' => 'You cannot buy this property']);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->processPurchase($property);
            }

            // Show buy form
            $data = [
                'page_title' => 'Buy Property - APS Dream Home',
                'property' => $property,
                'user' => $this->getCurrentUser(),
                'payment_options' => $this->getPaymentOptions(),
                'loan_options' => $this->getLoanOptions($property),
                'required_documents' => $this->getRequiredDocuments($property)
            ];

            $this->view('properties/buy', $data);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Sell property workflow
     */
    public function sell()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->redirect('/login?redirect=' . urlencode("/properties/sell"));
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->processSellProperty();
            }

            $data = [
                'page_title' => 'Sell Property - APS Dream Home',
                'user' => $this->getCurrentUser(),
                'property_types' => $this->getPropertyTypes(),
                'cities' => $this->getCities(),
                'amenities' => $this->getAmenities(),
                'sell_packages' => $this->getSellPackages()
            ];

            $this->view('properties/sell', $data);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Schedule property visit
     */
    public function scheduleVisit($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->redirect('/login?redirect=' . urlencode("/properties/{$id}/visit"));
                return;
            }

            $property = $this->getPropertyById($id);
            if (!$property) {
                $this->redirect('/properties', ['error' => 'Property not found']);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->processVisitScheduling($property);
            }

            $data = [
                'page_title' => 'Schedule Visit - APS Dream Home',
                'property' => $property,
                'user' => $this->getCurrentUser(),
                'available_slots' => $this->getAvailableVisitSlots($property),
                'assigned_agent' => $this->getUserAssignedAgent($_SESSION['user_id'])
            ];

            $this->view('properties/schedule-visit', $data);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Process property purchase
     */
    private function processPurchase(array $property): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $paymentMethod = $_POST['payment_method'] ?? '';
            $loanRequired = !empty($_POST['loan_required']);
            $downPayment = floatval($_POST['down_payment'] ?? 0);
            $installmentMonths = intval($_POST['installment_months'] ?? 0);

            // Validate input
            if (empty($paymentMethod)) {
                $this->redirect("/properties/{$property['id']}/buy", ['error' => 'Please select payment method']);
                return;
            }

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Create purchase record
                $purchaseId = $this->createPurchaseRecord([
                    'user_id' => $userId,
                    'property_id' => $property['id'],
                    'total_amount' => $property['price'],
                    'payment_method' => $paymentMethod,
                    'loan_required' => $loanRequired,
                    'down_payment' => $downPayment,
                    'installment_months' => $installmentMonths,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Process payment
                if ($downPayment > 0) {
                    $this->processPayment($purchaseId, $downPayment, 'down_payment');
                }

                // Create loan record if required
                if ($loanRequired) {
                    $this->createLoanRecord($purchaseId, $property['price'] - $downPayment, $installmentMonths);
                }

                // Update property status
                $this->updatePropertyStatus($property['id'], 'sold');

                // Create notification for admin
                $this->createNotification('admin', 'property_sold', "Property {$property['title']} has been sold");

                // Create notification for user
                $this->createNotification($userId, 'purchase_confirmed', "Your purchase of {$property['title']} has been confirmed");

                // Process commission if user was referred
                $this->processPurchaseCommission($userId, $property['id']);

                $this->db->commit();

                $this->redirect('/dashboard/purchases/' . $purchaseId, ['success' => 'Property purchased successfully!']);

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Purchase processing error: " . $e->getMessage());
            $this->redirect("/properties/{$property['id']}/buy", ['error' => 'Purchase failed. Please try again.']);
        }
    }

    /**
     * Process property selling
     */
    private function processSellProperty(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            
            $propertyData = [
                'user_id' => $userId,
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type' => $_POST['property_type'] ?? '',
                'city' => $_POST['city'] ?? '',
                'location' => $_POST['location'] ?? '',
                'price' => floatval($_POST['price'] ?? 0),
                'bedrooms' => intval($_POST['bedrooms'] ?? 0),
                'bathrooms' => intval($_POST['bathrooms'] ?? 0),
                'area' => $_POST['area'] ?? '',
                'amenities' => json_encode($_POST['amenities'] ?? []),
                'images' => json_encode($this->handlePropertyImages()),
                'status' => 'pending_approval',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Validate required fields
            if (empty($propertyData['title']) || empty($propertyData['price']) || $propertyData['price'] <= 0) {
                $this->redirect('/properties/sell', ['error' => 'Please fill all required fields']);
                return;
            }

            // Insert property
            $sql = "INSERT INTO properties (user_id, title, description, type, city, location, price, bedrooms, bathrooms, area, amenities, images, status, created_at)
                    VALUES (:user_id, :title, :description, :type, :city, :location, :price, :bedrooms, :bathrooms, :area, :amenities, :images, :status, :created_at)";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($propertyData);

            if ($success) {
                $propertyId = $this->db->lastInsertId();
                
                // Create notification for admin
                $this->createNotification('admin', 'property_submitted', "New property submitted for approval: {$propertyData['title']}");
                
                // Create notification for user
                $this->createNotification($userId, 'property_submitted', "Your property {$propertyData['title']} has been submitted for approval");

                $this->redirect('/dashboard/properties/' . $propertyId, ['success' => 'Property submitted for approval!']);
            } else {
                $this->redirect('/properties/sell', ['error' => 'Failed to submit property']);
            }

        } catch (Exception $e) {
            error_log("Property selling error: " . $e->getMessage());
            $this->redirect('/properties/sell', ['error' => 'Failed to submit property']);
        }
    }

    /**
     * Process visit scheduling
     */
    private function processVisitScheduling(array $property): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $visitDate = $_POST['visit_date'] ?? '';
            $visitTime = $_POST['visit_time'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($visitDate) || empty($visitTime)) {
                $this->redirect("/properties/{$property['id']}/visit", ['error' => 'Please select date and time']);
                return;
            }

            $visitData = [
                'user_id' => $userId,
                'property_id' => $property['id'],
                'visit_date' => $visitDate,
                'visit_time' => $visitTime,
                'notes' => $notes,
                'status' => 'scheduled',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sql = "INSERT INTO property_visits (user_id, property_id, visit_date, visit_time, notes, status, created_at)
                    VALUES (:user_id, :property_id, :visit_date, :visit_time, :notes, :status, :created_at)";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($visitData);

            if ($success) {
                // Create notification for assigned agent
                $assignedAgent = $this->getUserAssignedAgent($userId);
                if ($assignedAgent) {
                    $this->createNotification($assignedAgent['user_id'], 'property_visit', "Property visit scheduled for {$property['title']}");
                }

                // Create notification for admin
                $this->createNotification('admin', 'property_visit', "Property visit scheduled for {$property['title']}");

                // Create notification for user
                $this->createNotification($userId, 'visit_scheduled', "Your visit to {$property['title']} has been scheduled");

                $this->redirect("/properties/{$property['id']}", ['success' => 'Visit scheduled successfully!']);
            } else {
                $this->redirect("/properties/{$property['id']}/visit", ['error' => 'Failed to schedule visit']);
            }

        } catch (Exception $e) {
            error_log("Visit scheduling error: " . $e->getMessage());
            $this->redirect("/properties/{$property['id']}/visit", ['error' => 'Failed to schedule visit']);
        }
    }

    /**
     * Helper methods
     */
    private function getPropertyFilters(): array
    {
        return [
            'type' => $_GET['type'] ?? '',
            'city' => $_GET['city'] ?? '',
            'min_price' => intval($_GET['min_price'] ?? 0),
            'max_price' => intval($_GET['max_price'] ?? 0),
            'bedrooms' => intval($_GET['bedrooms'] ?? 0),
            'search' => $_GET['search'] ?? ''
        ];
    }

    private function getFilteredProperties(array $filters): array
    {
        $sql = "SELECT * FROM properties WHERE status = 'available'";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND city = :city";
            $params['city'] = $filters['city'];
        }

        if ($filters['min_price'] > 0) {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if ($filters['max_price'] > 0) {
            $sql .= " AND price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        if ($filters['bedrooms'] > 0) {
            $sql .= " AND bedrooms >= :bedrooms";
            $params['bedrooms'] = $filters['bedrooms'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE :search OR description LIKE :search OR location LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY featured DESC, created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function getPropertyById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private function getSimilarProperties(array $property): array
    {
        $sql = "SELECT * FROM properties 
                WHERE id != :id AND type = :type AND city = :city AND status = 'available'
                ORDER BY price ASC LIMIT 3";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $property['id'],
            'type' => $property['type'],
            'city' => $property['city']
        ]);
        return $stmt->fetchAll();
    }

    private function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    private function getUserAssignedAgent(int $userId): ?array
    {
        $sql = "SELECT a.*, u.name, u.email, u.phone 
                FROM agents a
                JOIN users u ON a.user_id = u.id
                JOIN customer_assignments ca ON a.user_id = ca.agent_id
                WHERE ca.customer_id = :user_id AND ca.status = 'active'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    private function canUserBuyProperty(array $property): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // Check if user already bought this property
        $stmt = $this->db->prepare("SELECT id FROM property_purchases WHERE user_id = :user_id AND property_id = :property_id LIMIT 1");
        $stmt->execute(['user_id' => $user['id'], 'property_id' => $property['id']]);
        
        return !$stmt->fetch();
    }

    private function canUserSellProperty(array $property): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        return $property['user_id'] == $user['id'];
    }

    private function getPropertyTypes(): array
    {
        return ['apartment', 'villa', 'house', 'plot', 'commercial', 'office', 'shop'];
    }

    private function getCities(): array
    {
        return ['Lucknow', 'Gorakhpur', 'Kanpur', 'Noida', 'Ghaziabad', 'Varanasi', 'Agra'];
    }

    private function getPriceRanges(): array
    {
        return [
            ['min' => 0, 'max' => 1000000, 'label' => 'Under ₹10L'],
            ['min' => 1000000, 'max' => 2500000, 'label' => '₹10L - ₹25L'],
            ['min' => 2500000, 'max' => 5000000, 'label' => '₹25L - ₹50L'],
            ['min' => 5000000, 'max' => 10000000, 'label' => '₹50L - ₹1Cr'],
            ['min' => 10000000, 'max' => 0, 'label' => 'Above ₹1Cr']
        ];
    }

    private function getAmenities(): array
    {
        return ['Parking', 'Swimming Pool', 'Gym', 'Security', 'Power Backup', 'Garden', 'Clubhouse', 'Lift'];
    }

    private function getPaymentOptions(): array
    {
        return ['full_payment', 'down_payment', 'installment', 'loan'];
    }

    private function getLoanOptions(array $property): array
    {
        return [
            ['months' => 12, 'interest_rate' => 8.5],
            ['months' => 24, 'interest_rate' => 9.0],
            ['months' => 36, 'interest_rate' => 9.5],
            ['months' => 60, 'interest_rate' => 10.0],
            ['months' => 120, 'interest_rate' => 10.5],
            ['months' => 240, 'interest_rate' => 11.0]
        ];
    }

    private function getFinancingOptions(array $property): array
    {
        return [
            'bank_loan' => [
                'name' => 'Bank Loan',
                'interest_rates' => '8.5% - 11%',
                'max_tenure' => '20 years',
                'processing_fee' => '1-2%'
            ],
            'home_loan' => [
                'name' => 'Home Loan',
                'interest_rates' => '8.0% - 10.5%',
                'max_tenure' => '30 years',
                'processing_fee' => '0.5-1%'
            ]
        ];
    }

    private function getRequiredDocuments(array $property): array
    {
        return [
            'identity_proof' => ['Aadhaar Card', 'PAN Card', 'Voter ID', 'Passport'],
            'address_proof' => ['Utility Bill', 'Rental Agreement', 'Bank Statement'],
            'income_proof' => ['Salary Slips', 'IT Returns', 'Bank Statements'],
            'property_documents' => ['Sale Deed', 'Title Deed', 'Encumbrance Certificate']
        ];
    }

    private function getSellPackages(): array
    {
        return [
            'basic' => ['price' => 5000, 'features' => ['Basic Listing', '30 Days', '1 Photo']],
            'standard' => ['price' => 10000, 'features' => ['Featured Listing', '60 Days', '5 Photos', 'Video']],
            'premium' => ['price' => 20000, 'features' => ['Premium Listing', '90 Days', 'Unlimited Photos', 'Video', 'Agent Support']]
        ];
    }

    private function getAvailableVisitSlots(array $property): array
    {
        $slots = [];
        $times = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
        
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            $slots[$date] = $times;
        }
        
        return $slots;
    }

    private function handlePropertyImages(): array
    {
        $images = [];
        
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$key];
                    $fileName = time() . '_' . $name;
                    $uploadPath = 'uploads/properties/' . $fileName;
                    
                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $images[] = $uploadPath;
                    }
                }
            }
        }
        
        return $images;
    }

    private function createPurchaseRecord(array $data): int
    {
        $sql = "INSERT INTO property_purchases (user_id, property_id, total_amount, payment_method, loan_required, down_payment, installment_months, status, created_at)
                VALUES (:user_id, :property_id, :total_amount, :payment_method, :loan_required, :down_payment, :installment_months, :status, :created_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->db->lastInsertId();
    }

    private function processPayment(int $purchaseId, float $amount, string $type): void
    {
        $sql = "INSERT INTO property_payments (purchase_id, amount, payment_type, status, created_at)
                VALUES (:purchase_id, :amount, :payment_type, 'pending', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['purchase_id' => $purchaseId, 'amount' => $amount, 'payment_type' => $type]);
    }

    private function createLoanRecord(int $purchaseId, float $amount, int $months): void
    {
        $sql = "INSERT INTO property_loans (purchase_id, principal_amount, tenure_months, interest_rate, status, created_at)
                VALUES (:purchase_id, :principal_amount, :tenure_months, 10.5, 'pending', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['purchase_id' => $purchaseId, 'principal_amount' => $amount, 'tenure_months' => $months]);
    }

    private function updatePropertyStatus(int $propertyId, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE properties SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $propertyId]);
    }

    private function createNotification($userId, string $type, string $message): void
    {
        $sql = "INSERT INTO notifications (user_id, type, title, message, created_at)
                VALUES (:user_id, :type, :type, :message, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'type' => $type, 'message' => $message]);
    }

    private function processPurchaseCommission(int $userId, int $propertyId): void
    {
        // Get user's referrer
        $stmt = $this->db->prepare("SELECT referrer_id FROM users WHERE id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();

        if ($user && $user['referrer_id']) {
            // Check if referrer is associate
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :referrer_id LIMIT 1");
            $stmt->execute(['referrer_id' => $user['referrer_id']]);
            $referrer = $stmt->fetch();

            if ($referrer && $referrer['role'] === 'associate') {
                // Create commission record
                $commissionAmount = 1000; // Fixed commission for property purchase
                
                $sql = "INSERT INTO mlm_commission_ledger 
                        (user_id, commission_type, amount, source_property_id, description, status, created_at)
                        VALUES (:user_id, 'property_purchase', :amount, :property_id, :description, 'pending', NOW())";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'user_id' => $user['referrer_id'],
                    'amount' => $commissionAmount,
                    'property_id' => $propertyId,
                    'description' => 'Commission from property purchase referral'
                ]);
            }
        }
    }

    private function handleError(Exception $e): void
    {
        error_log("Property Controller Error: " . $e->getMessage());
        $this->redirect('/properties', ['error' => 'An error occurred. Please try again.']);
    }
}
