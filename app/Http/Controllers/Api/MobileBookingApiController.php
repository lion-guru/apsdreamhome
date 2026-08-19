<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use App\Services\CacheService;
use App\Services\SyncService;
use PDO;
use App\Traits\TenantAwareTrait;

class MobileBookingApiController extends BaseController
{
    use TenantAwareTrait;
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function submitInquiry()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            [$validated, $errors] = $this->validateInput($input, [
                'property_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'message' => 'required|string|max:5000',
                'subject' => 'nullable|string|max:255',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $input = $validated;
            $inquiry_id = $this->createInquiry($input);
            if ($inquiry_id) {
                $emailNotification = new \EmailNotification();
                $emailNotification->sendInquiryNotification($inquiry_id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Inquiry submitted successfully',
                    'inquiry_id' => $inquiry_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to submit inquiry']);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Inquiry Submission API error');
        }
    }

    public function startSiteVisit()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $leadId = (int)($input['lead_id'] ?? 0);
            $agentId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$leadId || !$agentId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Lead ID and Agent ID are required']);
                return;
            }
            $visitId = $this->startSiteVisitSession($leadId, $agentId);
            echo json_encode(['success' => true, 'visit_id' => $visitId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Start Site Visit API error');
        }
    }

    public function updateSiteVisitLocation()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $visitId = (int)($input['visit_id'] ?? 0);
            $lat = isset($input['lat']) ? (float)$input['lat'] : null;
            $lng = isset($input['lng']) ? (float)$input['lng'] : null;
            if (!$visitId || $lat === null || $lng === null) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Visit ID, lat and lng are required']);
                return;
            }
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid coordinates']);
                return;
            }
            $this->updateVisitLocation($visitId, $lat, $lng);
            echo json_encode(['success' => true, 'message' => 'Location updated']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Update Location API error');
        }
    }

    public function completeSiteVisit()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $visitId = (int)($input['visit_id'] ?? 0);
            $outcome = \App\Core\Security::sanitize($input['outcome'] ?? 'completed');
            $notes = \App\Core\Security::sanitize($input['notes'] ?? '');
            if (!$visitId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Visit ID is required']);
                return;
            }
            $this->completeVisitSession($visitId, $outcome, $notes);
            echo json_encode(['success' => true, 'message' => 'Site visit marked as completed']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Complete Visit API error');
        }
    }

    public function getSiteVisitStatus()
    {
        $this->setCorsHeaders();
        try {
            $visitId = (int)($_GET['visit_id'] ?? 0);
            if (!$visitId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Visit ID is required']);
                return;
            }
            $status = $this->getVisitStatus($visitId);
            echo json_encode(['success' => true, 'data' => $status]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Visit Status API error');
        }
    }

    public function getAvailableSlots()
    {
        $this->setCorsHeaders();
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            $slots = $this->getAvailableVisitSlots($date);
            echo json_encode(['success' => true, 'data' => $slots]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Available Slots API error');
        }
    }

    public function bookSiteVisitApi()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            [$validated, $errors] = $this->validateInput($input, [
                'property_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'date' => 'required|string',
                'slot' => 'required|string',
                'name' => 'required|string',
                'phone' => 'required|string',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $bookingId = $this->createSiteVisitBooking($validated);
            echo json_encode(['success' => true, 'booking_id' => $bookingId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Book Site Visit API error');
        }
    }

    public function getMySiteVisits()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $visits = $this->getUserSiteVisits($userId);
            echo json_encode(['success' => true, 'data' => $visits]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'My Site Visits API error');
        }
    }

    public function cancelSiteVisitApi()
    {
        $this->setCorsHeaders();
        try {
            $visitId = (int)($_GET['visit_id'] ?? 0);
            if (!$visitId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Visit ID is required']);
                return;
            }
            $this->cancelVisitSession($visitId);
            echo json_encode(['success' => true, 'message' => 'Site visit cancelled']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Cancel Site Visit API error');
        }
    }

    public function rescheduleSiteVisitApi()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $visitId = (int)($input['visit_id'] ?? 0);
            $newDate = $input['date'] ?? null;
            $newSlot = $input['slot'] ?? null;
            if (!$visitId || !$newDate || !$newSlot) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Visit ID, date and slot are required']);
                return;
            }
            $newDate = \App\Core\Security::sanitize((string)$newDate);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid date format (YYYY-MM-DD)']);
                return;
            }
            $newSlot = \App\Core\Security::sanitize((string)$newSlot);
            $this->rescheduleVisitSession($visitId, $newDate, $newSlot);
            echo json_encode(['success' => true, 'message' => 'Site visit rescheduled']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Reschedule Site Visit API error');
        }
    }

    private function setCorsHeaders()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    private function handleApiError($exception, $context = 'API Error')
    {
        error_log($context . ': ' . $exception->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal server error',
            'message' => 'Internal server error',
            'context' => $context
        ]);
    }

    public function listBookings()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $bookings = $this->getBookingsData($userId);
            echo json_encode(['success' => true, 'data' => $bookings]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'List Bookings API error');
        }
    }

    public function bookingDetail($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $booking = $this->getBookingDetailData($userId, (int)$id);
            echo json_encode(['success' => true, 'data' => $booking]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Booking Detail API error');
        }
    }

    public function recordBookingPayment($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $amount = (float)($input['amount'] ?? 0);
            $paymentMethod = \App\Core\Security::sanitize($input['payment_method'] ?? 'cash');
            if ($amount <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid amount is required']);
                return;
            }
            $paymentId = $this->recordPayment($userId, (int)$id, $amount, $paymentMethod);
            echo json_encode(['success' => true, 'payment_id' => $paymentId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Record Payment API error');
        }
    }

    public function createBookingRequest()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            [$validated, $errors] = $this->validateInput($input, [
                'property_id' => 'required|integer',
                'booking_date' => 'nullable|string',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $bookingId = $this->createBooking($userId, $validated);
            echo json_encode(['success' => true, 'booking_id' => $bookingId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Create Booking API error');
        }
    }

    public function updateBooking($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $this->updateBookingRecord($userId, (int)$id, $input);
            echo json_encode(['success' => true, 'message' => 'Booking updated']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Update Booking API error');
        }
    }

    public function cancelBooking($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $this->cancelBookingRecord($userId, (int)$id);
            echo json_encode(['success' => true, 'message' => 'Booking cancelled']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Cancel Booking API error');
        }
    }

    private function getBookingsData($userId)
    {
        $stmt = $this->db->prepare("
            SELECT b.id, b.booking_date, b.amount, b.status, b.created_at,
                   p.title as property_title,
                   p.price as property_price
            FROM bookings b
            LEFT JOIN properties p ON b.property_id = p.id
            WHERE b.customer_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getBookingDetailData($userId, $bookingId)
    {
        $stmt = $this->db->prepare("
            SELECT b.*, p.title as property_title, p.price as property_price,
                   u.name as customer_name, u.email as customer_email
            FROM bookings b
            LEFT JOIN properties p ON b.property_id = p.id
            LEFT JOIN users u ON b.customer_id = u.id
            WHERE b.id = ? AND b.customer_id = ?
        ");
        $stmt->execute([$bookingId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function recordPayment($userId, $bookingId, $amount, $paymentMethod)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, 'completed', NOW())");
            $stmt->execute([$bookingId, $userId, $amount, $paymentMethod]);
            $paymentId = $this->db->lastInsertId();

            $stmt = $this->db->prepare("UPDATE bookings SET amount_paid = amount_paid + ?, status = CASE WHEN amount_paid >= amount THEN 'completed' ELSE 'partial' END WHERE id = ? AND customer_id = ?");
            $stmt->execute([$amount, $bookingId, $userId]);

            $this->db->commit();
            return $paymentId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function createBooking($userId, $data)
    {
        $stmt = $this->db->prepare("INSERT INTO bookings (customer_id, property_id, booking_date, amount, status, created_at) VALUES (?, ?, ?, 0, 'pending', NOW())");
        $stmt->execute([$userId, $data['property_id'], $data['booking_date'] ?? date('Y-m-d')]);
        return $this->db->lastInsertId();
    }

    private function updateBookingRecord($userId, $bookingId, $data)
    {
        $updates = [];
        $params = [];
        $allowed = ['booking_date', 'amount', 'status'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = \App\Core\Security::sanitize($data[$field]);
            }
        }
        if (!empty($updates)) {
            $params[] = $userId;
            $params[] = $bookingId;
            $stmt = $this->db->prepare("UPDATE bookings SET " . implode(', ', $updates) . " WHERE customer_id = ? AND id = ?");
            $stmt->execute($params);
        }
    }

    private function cancelBookingRecord($userId, $bookingId)
    {
        $stmt = $this->db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND customer_id = ?");
        $stmt->execute([$bookingId, $userId]);
    }
}
