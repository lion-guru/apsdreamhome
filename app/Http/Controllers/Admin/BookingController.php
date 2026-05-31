<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Property;
use App\Core\Database;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\AuthenticationService;
use App\Services\RequestService;
use App\Services\LoggingService;
use Exception;

class BookingController extends AdminController
{

    public function availability($propertyId)
    {
        try {
            if (!\is_numeric($propertyId)) {
                return $this->jsonErrorLocal('Invalid property ID', 400);
            }

            $sql = "SELECT visit_date FROM visit_availability WHERE property_id = ? AND visit_date >= CURDATE()";
            $stmt = $this->db->prepare("SELECT visit_date FROM visit_availability WHERE property_id = ? AND visit_date >= CURDATE()");
            $stmt->execute([$propertyId]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $availableDates = \array_column($result, 'visit_date');

            return $this->jsonSuccess($availableDates);
        } catch (Exception $e) {
            return $this->jsonErrorLocal($e->getMessage(), 500);
        }
    }

    public function myBookings()
    {
        try {
            $user = $this->auth->user();
            $sql = "SELECT b.*, p.title as property_title, p.location 
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ?
                    ORDER BY b.visit_date DESC";
            $stmt = $this->db->prepare(
                "SELECT b.*, p.title as property_title, p.location 
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ?
                    ORDER BY b.visit_date DESC"
            );
            $stmt->execute([$user->id]);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonSuccess($bookings);
        } catch (Exception $e) {
            return $this->jsonErrorLocal($e->getMessage(), 500);
        }
    }

    // Helper methods for CSRF validation and JSON responses
    protected function validateCsrfTokenLocal(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $token === ($_SESSION['csrf_token'] ?? '');
    }

    private function jsonErrorLocal(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    private function jsonSuccess($data): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}
