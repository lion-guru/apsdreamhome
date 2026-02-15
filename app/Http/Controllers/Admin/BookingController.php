<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Exception;

class BookingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Use the Core Database wrapper
        $this->db = \App\Core\App::database();
        // Use legacy admin layout for consistency
        $this->layout = 'layouts/admin_legacy';

        if (!$this->isAdmin()) {
            $this->redirect('login');
        }
    }

    /**
     * List all bookings
     */
    public function index()
    {
        $this->data['page_title'] = 'Bookings Management';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10)
        ];

        // Construct query
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(b.id LIKE ? OR u.uname LIKE ? OR p.title LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['status'])) {
            $where[] = "b.status = ?";
            $params[] = $filters['status'];
        }

        $whereSql = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) FROM bookings b 
                     LEFT JOIN customers u ON b.customer_id = u.id 
                     LEFT JOIN properties p ON b.property_id = p.id 
                     WHERE $whereSql";
        $total_bookings = $this->db->fetchColumn($countSql, $params);

        // Fetch data
        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $sql = "SELECT b.*, u.name as customer_name, p.title as property_title 
                FROM bookings b 
                LEFT JOIN customers u ON b.customer_id = u.id 
                LEFT JOIN properties p ON b.property_id = p.id 
                WHERE $whereSql 
                ORDER BY b.created_at DESC 
                LIMIT {$filters['per_page']} OFFSET $offset";

        $bookings = $this->db->fetchAll($sql, $params);

        $this->data['bookings'] = $bookings;
        $this->data['total_bookings'] = $total_bookings;
        $this->data['filters'] = $filters;
        $this->data['total_pages'] = ceil($total_bookings / $filters['per_page']);

        $this->render('admin/bookings/index');
    }

    /**
     * Show create booking form
     */
    public function create()
    {
        $this->data['page_title'] = 'Add New Booking';

        // Fetch properties
        $this->data['properties'] = $this->db->fetchAll("SELECT id, title FROM properties WHERE status = 'available' ORDER BY title");

        // Fetch customers
        $this->data['customers'] = $this->db->fetchAll("SELECT uid, uname FROM user WHERE utype = 'customer' ORDER BY uname");

        $this->render('admin/bookings/create');
    }

    /**
     * Store new booking
     */
    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('admin/bookings/create');
            return;
        }

        $property_id = intval($_POST['property_id'] ?? 0);
        $customer_id = $_POST['customer_id'] ?? '';
        $booking_date = $_POST['booking_date'] ?? date('Y-m-d');
        $booking_amount = floatval($_POST['amount'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $booking_number = 'BK-' . strtoupper(uniqid());

        // Fetch property price for total amount (simplified logic, ideally should come from form or property table)
        $property = $this->db->fetchOne("SELECT price FROM properties WHERE id = ?", [$property_id]);
        $total_amount = $property['price'] ?? $booking_amount;

        if ($property_id <= 0 || empty($customer_id) || $booking_amount <= 0) {
            $this->setFlash('error', 'Please fill in all required fields.');
            $this->redirect('admin/bookings/create');
            return;
        }

        try {
            $this->db->execute("INSERT INTO bookings (property_id, customer_id, booking_date, booking_amount, total_amount, status, booking_number) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                $property_id,
                $customer_id,
                $booking_date,
                $booking_amount,
                $total_amount,
                $status,
                $booking_number
            ]);

            // TODO: Add notification logic (Customer & Admin)
            // $this->sendNotifications($property_id, $customer_id, $amount, $booking_date);

            $this->setFlash('success', 'Booking added successfully!');
            $this->redirect('admin/bookings');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error adding booking: ' . $e->getMessage());
            $this->redirect('admin/bookings/create');
        }
    }
}
