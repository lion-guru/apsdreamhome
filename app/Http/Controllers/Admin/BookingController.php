<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use \Exception;

class BookingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'updateStatus']]);
    }

    /**
     * Display a listing of bookings.
     */
    public function index()
    {
        $request = $this->request();
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        $sql = "SELECT b.*, p.title as property_title,
                       COALESCE(u.uname, c.name) as customer_name,
                       COALESCE(u.uemail, c.email) as customer_email,
                       COALESCE(u.uphone, c.phone) as customer_phone
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN customers c ON b.customer_id = c.id
                LEFT JOIN user u ON c.user_id = u.uid
                WHERE 1=1";

        $params = [];
        if ($status !== 'all') {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (u.uname LIKE ? OR u.uemail LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR p.title LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $bookings = $this->db->fetchAll($sql, $params);

        return $this->render('admin/bookings/index', [
            'page_title' => $this->mlSupport->translate('Booking Management') . ' - ' . $this->getConfig('app_name'),
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
            'breadcrumbs' => [$this->mlSupport->translate("Bookings") => "admin/bookings"]
        ]);
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        $properties = $this->db->fetchAll("SELECT id, title FROM properties WHERE status = 'available' ORDER BY title ASC");
        $customers = $this->db->fetchAll("SELECT c.id, COALESCE(u.uname, c.name) as name FROM customers c LEFT JOIN user u ON c.user_id = u.uid ORDER BY name ASC");

        return $this->render('admin/bookings/create', [
            'page_title' => $this->mlSupport->translate('Add Booking') . ' - ' . $this->getConfig('app_name'),
            'properties' => $properties,
            'customers' => $customers,
            'breadcrumbs' => [
                $this->mlSupport->translate("Bookings") => "admin/bookings",
                $this->mlSupport->translate("Add Booking") => ""
            ]
        ]);
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->post();

        // Validation
        if (empty($data['customer_id']) || empty($data['property_id']) || empty($data['visit_date'])) {
            $this->setFlash('error', $this->mlSupport->translate('Required fields are missing.'));
            return $this->back();
        }

        try {
            // XSS Protection - Sanitize input
            $customerId = strip_tags($data['customer_id']);
            $propertyId = (int)$data['property_id'];
            $bookingType = strip_tags($data['booking_type'] ?? 'site_visit');
            $visitDate = strip_tags($data['visit_date']);
            $visitTime = !empty($data['visit_time']) ? strip_tags($data['visit_time']) : null;
            $budgetRange = !empty($data['budget_range']) ? strip_tags($data['budget_range']) : null;
            $specialRequirements = !empty($data['special_requirements']) ? strip_tags($data['special_requirements']) : null;
            $status = strip_tags($data['status'] ?? 'pending');

            $sql = "INSERT INTO bookings (customer_id, property_id, booking_type, visit_date, visit_time, budget_range, special_requirements, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $this->db->execute($sql, [
                $customerId,
                $propertyId,
                $bookingType,
                $visitDate,
                $visitTime,
                $budgetRange,
                $specialRequirements,
                $status
            ]);

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Add Booking', 'Added booking for customer ' . $customerId . ' for property ' . $propertyId);
            $this->setFlash('success', $this->mlSupport->translate('Booking added successfully.'));
            return $this->redirect('admin/bookings');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error adding booking: ') . $e->getMessage());
            return $this->back();
        }
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit($id)
    {
        $id = (int)$id;
        $booking = $this->db->fetch("SELECT * FROM bookings WHERE id = ?", [$id]);

        if (!$booking) {
            $this->setFlash('error', $this->mlSupport->translate('Booking not found.'));
            return $this->redirect('admin/bookings');
        }

        $properties = $this->db->fetchAll("SELECT id, title FROM properties ORDER BY title ASC");
        $customers = $this->db->fetchAll("SELECT c.id, COALESCE(u.uname, c.name) as name FROM customers c LEFT JOIN user u ON c.user_id = u.uid ORDER BY name ASC");

        return $this->render('admin/bookings/edit', [
            'page_title' => $this->mlSupport->translate('Edit Booking') . ' - ' . $this->getConfig('app_name'),
            'booking' => $booking,
            'properties' => $properties,
            'customers' => $customers,
            'breadcrumbs' => [
                $this->mlSupport->translate("Bookings") => "admin/bookings",
                $this->mlSupport->translate("Edit Booking") => ""
            ]
        ]);
    }

    /**
     * Update the specified booking in storage.
     */
    public function update($id)
    {
        $id = (int)$id;
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->post();

        try {
            // XSS Protection - Sanitize input
            $customerId = strip_tags($data['customer_id']);
            $propertyId = (int)$data['property_id'];
            $bookingType = strip_tags($data['booking_type']);
            $visitDate = strip_tags($data['visit_date']);
            $visitTime = !empty($data['visit_time']) ? strip_tags($data['visit_time']) : null;
            $budgetRange = !empty($data['budget_range']) ? strip_tags($data['budget_range']) : null;
            $specialRequirements = !empty($data['special_requirements']) ? strip_tags($data['special_requirements']) : null;
            $status = strip_tags($data['status']);

            $sql = "UPDATE bookings SET
                    customer_id = ?,
                    property_id = ?,
                    booking_type = ?,
                    visit_date = ?,
                    visit_time = ?,
                    budget_range = ?,
                    special_requirements = ?,
                    status = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $this->db->execute($sql, [
                $customerId,
                $propertyId,
                $bookingType,
                $visitDate,
                $visitTime,
                $budgetRange,
                $specialRequirements,
                $status,
                $id
            ]);

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Update Booking', 'Updated booking ID: ' . $id);
            $this->setFlash('success', $this->mlSupport->translate('Booking updated successfully.'));
            return $this->redirect('admin/bookings');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error updating booking: ') . $e->getMessage());
            return $this->back();
        }
    }

    /**
     * Update booking status.
     */
    public function updateStatus($id)
    {
        $id = (int)$id;
        if (!$this->validateCsrfToken()) {
            return $this->jsonError($this->mlSupport->translate('Security validation failed.'));
        }

        $status = $this->request()->post('status');
        if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
            return $this->jsonError($this->mlSupport->translate('Invalid status.'));
        }

        try {
            $this->db->execute("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Update Booking Status', "Updated booking ID: $id status to $status");
            return $this->jsonSuccess(null, $this->mlSupport->translate('Status updated successfully.'));
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy($id)
    {
        $id = (int)$id;
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        try {
            $this->db->execute("DELETE FROM bookings WHERE id = ?", [$id]);

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Delete Booking', 'Deleted booking ID: ' . $id);
            $this->setFlash('success', $this->mlSupport->translate('Booking deleted successfully.'));
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error deleting booking: ') . $e->getMessage());
        }

        return $this->redirect('admin/bookings');
    }
}
