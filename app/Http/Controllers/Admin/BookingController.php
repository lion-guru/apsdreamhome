<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Property;
use App\Core\Database;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\RequestService;
use App\Services\LoggingService;
use Exception;

class BookingController extends AdminController
{

    public function index()
    {
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $filters = [
                'search' => $_GET['search'] ?? '',
                'status' => $_GET['status'] ?? '',
                'customer_id' => $_GET['customer_id'] ?? '',
                'associate_id' => $_GET['associate_id'] ?? '',
                'sort' => $_GET['sort'] ?? 'b.created_at',
                'order' => $_GET['order'] ?? 'DESC'
            ];

            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR p.title LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
            }
            if (!empty($filters['status'])) {
                $where[] = "b.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['customer_id'])) {
                $where[] = "b.customer_id = ?";
                $params[] = $filters['customer_id'];
            }
            $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings b LEFT JOIN properties p ON b.property_id = p.id LEFT JOIN users u ON b.customer_id = u.id $whereClause");
            $countStmt->execute($params);
            $total = intval($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            $totalPages = max(1, ceil($total / $perPage));

            $stmt = $this->db->prepare(
                "SELECT b.*, p.title as property_title, p.location as property_location,
                        u.name as customer_name, u.email as customer_email,
                        a.name as associate_name, a.email as associate_email
                 FROM bookings b
                 LEFT JOIN properties p ON b.property_id = p.id
                 LEFT JOIN users u ON b.customer_id = u.id
                 LEFT JOIN users a ON b.associate_id = a.id
                 $whereClause
                 ORDER BY b.created_at DESC
                 LIMIT $perPage OFFSET $offset"
            );
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $users = $this->db->query("SELECT id, name FROM users WHERE role IN ('customer','agent') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $associates = $this->db->query("SELECT id, name FROM users WHERE role = 'associate' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            return $this->render('admin/bookings/index', [
                'bookings' => $bookings,
                'total' => $total,
                'filters' => $filters,
                'users' => $users,
                'associates' => $associates,
                'total_pages' => $totalPages,
                'current_page' => $page
            ]);
        } catch (Exception $e) {
            return $this->render('admin/bookings/index', [
                'bookings' => [], 'total' => 0, 'filters' => [],
                'users' => [], 'associates' => [],
                'total_pages' => 1, 'current_page' => 1,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        try {
            $users = $this->db->query("SELECT id, name, email, phone FROM users WHERE role IN ('customer','agent') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $properties = $this->db->query("SELECT id, title, location FROM properties WHERE status = 'active' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            return $this->render('admin/bookings/create', ['users' => $users, 'properties' => $properties]);
        } catch (Exception $e) {
            return $this->render('admin/bookings/create', ['users' => [], 'properties' => [], 'error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $data = $_POST;
            $stmt = $this->db->prepare(
                "INSERT INTO bookings (customer_id, property_id, visit_date, status, notes, created_at)
                 VALUES (?, ?, ?, 'pending', ?, NOW())"
            );
            $stmt->execute([$data['customer_id'], $data['property_id'], $data['visit_date'] ?? date('Y-m-d'), $data['notes'] ?? '']);
            $_SESSION['flash_message'] = 'Booking created successfully.';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('/admin/bookings');
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('/admin/bookings/create');
        }
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT b.*, p.title as property_title, p.location, p.price, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                 FROM bookings b
                 LEFT JOIN properties p ON b.property_id = p.id
                 LEFT JOIN users u ON b.customer_id = u.id
                 WHERE b.id = ?"
            );
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            $payments = [];
            $total_paid = 0;
            $commissions = [];
            $total_commission = 0;
            try {
                $pStmt = $this->db->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY created_at DESC");
                $pStmt->execute([$id]);
                $payments = $pStmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($payments as $pmt) {
                    if (($pmt['status'] ?? '') === 'completed') {
                        $total_paid += floatval($pmt['amount'] ?? 0);
                    }
                }
            } catch (\Exception $e) {}
            try {
                $cStmt = $this->db->prepare("SELECT * FROM commissions WHERE booking_id = ? ORDER BY created_at DESC");
                $cStmt->execute([$id]);
                $commissions = $cStmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($commissions as $cm) {
                    $total_commission += floatval($cm['amount'] ?? 0);
                }
            } catch (\Exception $e) {}

            return $this->render('admin/bookings/show', [
                'booking' => $booking,
                'payments' => $payments,
                'total_paid' => $total_paid,
                'commissions' => $commissions,
                'total_commission' => $total_commission
            ]);
        } catch (Exception $e) {
            return $this->render('admin/bookings/show', [
                'booking' => null,
                'payments' => [],
                'total_paid' => 0,
                'commissions' => [],
                'total_commission' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
            $users = $this->db->query("SELECT id, name, email, phone FROM users WHERE role IN ('customer','agent') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $properties = $this->db->query("SELECT id, title, location FROM properties WHERE status = 'active' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            return $this->render('admin/bookings/edit', ['booking' => $booking, 'users' => $users, 'properties' => $properties]);
        } catch (Exception $e) {
            return $this->render('admin/bookings/edit', ['booking' => null, 'users' => [], 'properties' => [], 'error' => $e->getMessage()]);
        }
    }

    public function update($id)
    {
        try {
            $data = $_POST;
            $stmt = $this->db->prepare(
                "UPDATE bookings SET customer_id = ?, property_id = ?, visit_date = ?, status = ?, notes = ? WHERE id = ?"
            );
            $stmt->execute([$data['customer_id'], $data['property_id'], $data['visit_date'] ?? date('Y-m-d'), $data['status'] ?? 'pending', $data['notes'] ?? '', $id]);
            $_SESSION['flash_message'] = 'Booking updated successfully.';
            $_SESSION['flash_type'] = 'success';
            // Hot-path: booking status/amount changes affect the admin dashboard KPI bundle.
            \App\Services\Cache\HotPathCacheService::invalidateAdminDashboard();
            $this->redirect('/admin/bookings');
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('/admin/bookings/' . $id . '/edit');
        }
    }

    public function destroy($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = 'Booking deleted successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        $this->redirect('/admin/bookings');
    }

    public function processPayment($id)
    {
        try {
            $data = $_POST;
            $stmt = $this->db->prepare(
                "INSERT INTO payments (booking_id, amount, payment_date, payment_method, transaction_id, status, notes)
                 VALUES (?, ?, NOW(), ?, ?, 'completed', ?)"
            );
            $stmt->execute([$id, $data['amount'] ?? 0, $data['payment_method'] ?? 'cash', $data['transaction_id'] ?? '', $data['notes'] ?? '']);
            $_SESSION['flash_message'] = 'Payment processed successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        $this->redirect('/admin/bookings/' . $id);
    }

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
