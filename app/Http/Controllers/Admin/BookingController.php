<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Property;
use App\Services\NotificationService;
use Exception;

class BookingController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List all bookings
     */
    public function index()
    {
        $request = $this->request();
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'page' => (int)$request->get('page', 1),
            'per_page' => (int)$request->get('per_page', 10),
            'sort' => $request->get('sort', 'created_at'),
            'order' => $request->get('order', 'DESC')
        ];

        $bookings = Booking::getAdminBookings($filters);
        $total_bookings = Booking::getAdminTotalBookings($filters);
        
        // Handle total_bookings if it returns array/object (safety check)
        if (is_array($total_bookings)) {
             $total_bookings = isset($total_bookings[0]['count']) ? $total_bookings[0]['count'] : count($bookings);
        } elseif (is_object($total_bookings)) {
             $total_bookings = $total_bookings->count ?? 0;
        }

        return $this->render('admin/bookings/index', [
            'page_title' => $this->mlSupport->translate('Bookings Management') . ' - ' . $this->getConfig('app_name'),
            'bookings' => $bookings,
            'total_bookings' => $total_bookings,
            'filters' => $filters,
            'total_pages' => ceil($total_bookings / $filters['per_page'])
        ]);
    }

    /**
     * Show create booking form
     */
    public function create()
    {
        // Fetch properties (available)
        // Using direct query for simple list or use model method if available
        $properties = $this->db->fetchAll("SELECT id, title, price FROM properties WHERE status = 'available' ORDER BY title");

        // Fetch customers (users with role='customer')
        // Using direct query to be efficient
        $customers = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");

        return $this->render('admin/bookings/create', [
            'page_title' => $this->mlSupport->translate('Add New Booking') . ' - ' . $this->getConfig('app_name'),
            'properties' => $properties,
            'customers' => $customers
        ]);
    }

    /**
     * Store new booking
     */
    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('admin/bookings/create');
        }

        $request = $this->request();
        $data = $request->post();

        $property_id = intval($data['property_id'] ?? 0);
        $customer_id = intval($data['customer_id'] ?? 0);
        $booking_date = $data['booking_date'] ?? date('Y-m-d');
        $booking_amount = floatval($data['amount'] ?? 0);
        $status = $data['status'] ?? 'pending';
        $booking_number = 'BK-' . strtoupper(uniqid());

        if ($property_id <= 0 || $customer_id <= 0 || $booking_amount <= 0) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill in all required fields.'));
            return $this->redirect('admin/bookings/create');
        }

        // Fetch property price
        $property = Property::find($property_id);
        $total_amount = ($property && isset($property->price)) ? $property->price : $booking_amount;

        try {
            $booking = new Booking();
            // Booking model extends Model which handles fill/save
            $booking->fill([
                'property_id' => $property_id,
                'customer_id' => $customer_id,
                'booking_date' => $booking_date,
                'booking_amount' => $booking_amount,
                'total_amount' => $total_amount,
                'status' => $status,
                'booking_number' => $booking_number
            ]);
            
            if ($booking->save()) {
                $booking_id = $booking->id; 

                // Send notifications
                $this->sendNotifications($booking_id, $property_id, $customer_id, $booking_amount, $booking_date, $booking_number);

                $this->setFlash('success', $this->mlSupport->translate('Booking added successfully!'));
                return $this->redirect('admin/bookings');
            } else {
                throw new Exception("Failed to save booking.");
            }
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error adding booking: ') . $e->getMessage());
            return $this->redirect('admin/bookings/create');
        }
    }

    /**
     * Send booking notifications
     */
    private function sendNotifications($booking_id, $property_id, $customer_id, $amount, $date, $booking_number)
    {
        try {
            $notificationService = new NotificationService();

            // Fetch customer
            $customer = Customer::find($customer_id);

            if (!$customer) {
                return;
            }

            // Fetch property
            $property = Property::find($property_id);
            $property_title = ($property && isset($property->title)) ? $property->title : 'Property #' . $property_id;

            // Send to Customer
            $subject = ($this->mlSupport->translate('Booking Confirmation') ?? 'Booking Confirmation') . " - " . $booking_number;
            $body = "Dear " . ($customer->name ?? 'Customer') . ",<br><br>";
            $body .= "Your booking for property '<strong>" . htmlspecialchars($property_title) . "</strong>' has been confirmed.<br>";
            $body .= "Booking Number: <strong>" . htmlspecialchars($booking_number) . "</strong><br>";
            $body .= "Date: " . htmlspecialchars($date) . "<br>";
            $body .= "Amount: " . number_format($amount, 2) . "<br><br>";
            $body .= "Thank you for choosing APS Dream Home.";

            // Use email from customer object
            $customerEmail = $customer->email ?? ''; 
            
            if ($customerEmail) {
                $notificationService->sendEmail(
                    $customerEmail,
                    $subject,
                    $body,
                    'booking_confirmation',
                    $customer_id,
                    ['booking_id' => $booking_id, 'property_id' => $property_id]
                );
            }

            // Notify Admin
            $admin_subject = "New Booking Alert - " . $booking_number;
            $admin_body = "New booking received for property '<strong>" . htmlspecialchars($property_title) . "</strong>'.<br>";
            $admin_body .= "Booking Number: " . htmlspecialchars($booking_number) . "<br>";
            $admin_body .= "Customer: " . htmlspecialchars($customer->name ?? 'Unknown') . " (ID: $customer_id)<br>";
            $admin_body .= "Amount: " . number_format($amount, 2) . "<br>";
            $admin_body .= "Date: " . htmlspecialchars($date);

            $notificationService->notifyAdmin(
                $admin_subject,
                $admin_body,
                'new_booking_alert',
                ['booking_id' => $booking_id]
            );
        } catch (Exception $e) {
            // Log error but don't stop execution
            error_log("Notification Error: " . $e->getMessage());
        }
    }
}
