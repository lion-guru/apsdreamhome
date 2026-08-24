<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

/**
 * Property Allocation Controller - Plot Allocation Management
 * Manages plot status, allocation to users, availability calendar
 */
class PropertyAllocationController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        parent::__construct();
    }

    /**
     * Display all property allocations
     * Route: /admin/property-allocations
     */
    public function index()
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get all property allocations with customer and property details
            $sql = "SELECT pa.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                    p.title as property_title, p.location as property_location, p.plot_number, p.area_sqft,
                    CASE pa.status
                        WHEN 'pending' THEN 'Pending'
                        WHEN 'confirmed' THEN 'Confirmed'
                        WHEN 'cancelled' THEN 'Cancelled'
                        WHEN 'transferred' THEN 'Transferred'
                        ELSE pa.status
                    END as status_label
                    FROM property_allocations pa
                    LEFT JOIN users c ON pa.customer_id = c.id
                    LEFT JOIN properties p ON pa.property_id = p.id
                    ORDER BY pa.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $allocations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get allocation statistics
            $stats = $this->getAllocationStatistics();
            
            $data = [
                'page_title' => 'Property Allocation Management',
                'allocations' => $allocations,
                'stats' => $stats
            ];
            
            return $this->render('admin.property-allocations.index', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.property-allocations.index', [
                'page_title' => 'Property Allocation Management',
                'allocations' => [],
                'stats' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display create allocation form
     * Route: /admin/property-allocations/create
     */
    public function create()
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get users for dropdown
            $users = $conn->query("SELECT id, name, email, phone FROM users ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get available properties for dropdown
            $properties = $conn->query("SELECT id, title, location, plot_number, area_sqft, price 
                                        FROM properties 
                                        WHERE status = 'available' 
                                        ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Create Property Allocation',
                'users' => $users,
                'properties' => $properties
            ];
            
            return $this->render('admin.property-allocations.create', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.property-allocations.create', [
                'page_title' => 'Create Property Allocation',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new property allocation
     * Route: /admin/property-allocations/store
     */
    public function store()
    {
        try {
            $conn = $this->db->getConnection();
            
            $customerId = $_POST['customer_id'] ?? 0;
            $propertyId = $_POST['property_id'] ?? 0;
            $bookingAmount = $_POST['booking_amount'] ?? 0;
            $totalPrice = $_POST['total_price'] ?? 0;
            $installmentPlan = $_POST['installment_plan'] ?? 'full_payment';
            $installmentMonths = $_POST['installment_months'] ?? 0;
            $notes = $_POST['notes'] ?? '';
            
            // Generate allocation number
            $allocationNumber = 'PA-' . date('Ymd-His');
            
            $tid = $this->tenantId();
            $sql = "INSERT INTO property_allocations 
                    (user_id, property_id, notes, status, tenant_id, created_at)
                    VALUES (?, ?, ?, 'pending', ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $customerId, $propertyId, $notes, $tid
            ]);

            // Update property status to booked
            $conn->prepare("UPDATE properties SET status = 'booked' WHERE id = ? AND tenant_id = ?")->execute([$propertyId, $tid]);
            
            redirect('/admin/property-allocations?success=Property allocated successfully');
            exit;
            
        } catch (\Exception $e) {
            redirect('/admin/property-allocations/create?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Display single allocation details
     * Route: /admin/property-allocations/{id}
     */
    public function show($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT pa.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                    c.address as customer_address,
                    p.title as property_title, p.location as property_location, p.plot_number, 
                    p.area_sqft, p.price as property_price,
                    CASE pa.status
                        WHEN 'pending' THEN 'Pending'
                        WHEN 'confirmed' THEN 'Confirmed'
                        WHEN 'cancelled' THEN 'Cancelled'
                        WHEN 'transferred' THEN 'Transferred'
                        ELSE pa.status
                    END as status_label
                    FROM property_allocations pa
                    LEFT JOIN users c ON pa.customer_id = c.id
                    LEFT JOIN properties p ON pa.property_id = p.id
                    WHERE pa.id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $allocation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$allocation) {
                redirect('/admin/property-allocations?error=Allocation not found');
                exit;
            }
            
            // Get payment history for this allocation
            $payments = $conn->prepare("SELECT * FROM payments WHERE property_allocation_id = ? ORDER BY payment_date DESC");
            $payments->execute([$id]);
            $paymentHistory = $payments->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'page_title' => 'Property Allocation Details - ' . $allocation['allocation_number'],
                'allocation' => $allocation,
                'payment_history' => $paymentHistory
            ];
            
            return $this->render('admin.property-allocations.show', $data);
            
        } catch (\Exception $e) {
            redirect('/admin/property-allocations?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Confirm property allocation
     * Route: /admin/property-allocations/{id}/confirm
     */
    public function confirm($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            $tid = $this->tenantId();
            // Update allocation status to confirmed
            $conn->prepare("UPDATE property_allocations SET status = 'confirmed' WHERE id = ? AND tenant_id = ?")->execute([$id, $tid]);

            // Update property status to sold
            $sql = "UPDATE properties p 
                    INNER JOIN property_allocations pa ON p.id = pa.property_id 
                    SET p.status = 'sold' 
                    WHERE pa.id = ? AND pa.tenant_id = ?";
            $conn->prepare($sql)->execute([$id, $tid]);
            
            redirect('/admin/property-allocations?success=Property allocation confirmed');
            exit;
            
        } catch (\Exception $e) {
            redirect('/admin/property-allocations?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Cancel property allocation
     * Route: /admin/property-allocations/{id}/cancel
     */
    public function cancel($id)
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get property_id before cancelling
            $propertySql = "SELECT property_id FROM property_allocations WHERE id = ?";
            $propertyStmt = $conn->prepare($propertySql);
            $propertyStmt->execute([$id]);
            $property = $propertyStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($property) {
                $tid = $this->tenantId();
                // Update allocation status to cancelled
                $conn->prepare("UPDATE property_allocations SET status = 'cancelled' WHERE id = ? AND tenant_id = ?")->execute([$id, $tid]);

                // Update property status back to available
                $conn->prepare("UPDATE properties SET status = 'available' WHERE id = ? AND tenant_id = ?")->execute([$property['property_id'], $tid]);
            }
            
            redirect('/admin/property-allocations?success=Property allocation cancelled');
            exit;
            
        } catch (\Exception $e) {
            redirect('/admin/property-allocations?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Display availability calendar
     * Route: /admin/property-allocations/calendar
     */
    public function calendar()
    {
        try {
            $conn = $this->db->getConnection();
            
            // Get all properties with their status
            $properties = $conn->query("SELECT id, title, location, plot_number, area_sqft, price, status 
                                       FROM properties 
                                       ORDER BY location, plot_number")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get properties by status
            $available = array_filter($properties, function($p) { return $p['status'] === 'available'; });
            $booked = array_filter($properties, function($p) { return $p['status'] === 'booked'; });
            $sold = array_filter($properties, function($p) { return $p['status'] === 'sold'; });
            $blocked = array_filter($properties, function($p) { return $p['status'] === 'blocked'; });
            
            $data = [
                'page_title' => 'Property Availability Calendar',
                'all_properties' => $properties,
                'available' => $available,
                'booked' => $booked,
                'sold' => $sold,
                'blocked' => $blocked
            ];
            
            return $this->render('admin.property-allocations.calendar', $data);
            
        } catch (\Exception $e) {
            return $this->render('admin.property-allocations.calendar', [
                'page_title' => 'Property Availability Calendar',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get allocation statistics
     */
    private function getAllocationStatistics()
    {
        try {
            $conn = $this->db->getConnection();
            
            $stats = [
                'total_allocations' => $conn->query("SELECT COUNT(*) FROM property_allocations")->fetchColumn(),
                'confirmed_allocations' => $conn->query("SELECT COUNT(*) FROM property_allocations WHERE status = 'confirmed'")->fetchColumn(),
                'pending_allocations' => $conn->query("SELECT COUNT(*) FROM property_allocations WHERE status = 'pending'")->fetchColumn(),
                'cancelled_allocations' => $conn->query("SELECT COUNT(*) FROM property_allocations WHERE status = 'cancelled'")->fetchColumn(),
                'total_booking_amount' => $conn->query("SELECT COALESCE(SUM(booking_amount), 0) FROM property_allocations")->fetchColumn(),
                'confirmed_amount' => $conn->query("SELECT COALESCE(SUM(booking_amount), 0) FROM property_allocations WHERE status = 'confirmed'")->fetchColumn(),
                'available_properties' => $conn->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn(),
                'booked_properties' => $conn->query("SELECT COUNT(*) FROM properties WHERE status = 'booked'")->fetchColumn(),
                'sold_properties' => $conn->query("SELECT COUNT(*) FROM properties WHERE status = 'sold'")->fetchColumn()
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            return [];
        }
    }
}
