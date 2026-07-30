<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Booking;
use App\Models\Property;
use App\Core\Database;
use App\Services\LoggingService;
use Exception;

class BookingController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $filters = [
                'search'       => $_GET['search'] ?? '',
                'status'       => $_GET['status'] ?? '',
                'colony_id'    => $_GET['colony_id'] ?? '',
                'associate_id' => $_GET['associate_id'] ?? '',
                'sort'         => $_GET['sort'] ?? 'b.created_at',
                'order'        => $_GET['order'] ?? 'DESC'
            ];

            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR pl.plot_number LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
            }
            if (!empty($filters['status'])) {
                $where[] = "b.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['colony_id'])) {
                $where[] = "pl.colony_id = ?";
                $params[] = $filters['colony_id'];
            }
            if (!empty($filters['associate_id'])) {
                $where[] = "b.associate_id = ?";
                $params[] = $filters['associate_id'];
            }
            $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $countSql = "SELECT COUNT(DISTINCT b.id) as total FROM plot_bookings b LEFT JOIN plots pl ON b.plot_id = pl.id LEFT JOIN users u ON b.customer_id = u.id $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = intval($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            $totalPages = max(1, ceil($total / $perPage));

            $validSorts = ['b.created_at', 'b.booking_number', 'b.total_plot_value', 'b.status'];
            $sortCol = in_array($filters['sort'], $validSorts) ? $filters['sort'] : 'b.created_at';
            $sortDir = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';

            $stmt = $this->db->prepare(
                "SELECT b.id, b.booking_number, b.status, b.total_plot_value, b.booking_amount,
                        b.booking_date, b.created_at,
                        pl.plot_number, pl.colony_id,
                        c.name as colony_name,
                        u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                        a.name as associate_name
                 FROM plot_bookings b
                 LEFT JOIN plots pl ON b.plot_id = pl.id
                 LEFT JOIN colonies c ON pl.colony_id = c.id
                 LEFT JOIN users u ON b.customer_id = u.id
                 LEFT JOIN users a ON b.associate_id = a.id
                 $whereClause
                 ORDER BY $sortCol $sortDir
                 LIMIT $perPage OFFSET $offset"
            );
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            [$tidSql, $tidParams] = $this->tenantWhere();
            $allAssociates = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('associate','agent'){$tidSql} ORDER BY name", $tidParams) ?: [];
            $colonies = $this->db->query("SELECT id, name FROM colonies WHERE is_active=1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $statuses = ['token_paid','agreement_signed','emi_active','partially_paid','fully_paid','cancelled','transferred','registration_done'];

            return $this->render('admin/bookings/index', [
                'bookings'     => $bookings,
                'total'        => $total,
                'filters'      => $filters,
                'associates'   => $allAssociates,
                'colonies'     => $colonies,
                'statuses'     => $statuses,
                'total_pages'  => $totalPages,
                'current_page' => $page
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/bookings/index', [
                'bookings' => [], 'total' => 0, 'filters' => [],
                'associates' => [], 'colonies' => [], 'statuses' => [],
                'total_pages' => 1, 'current_page' => 1,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role IN ('customer','agent'){$tidSql} ORDER BY name", $tidParams) ?: [];
            $properties = $this->db->query("SELECT id, title, location FROM properties WHERE status = 'active' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            return $this->render('admin/bookings/create', ['users' => $users, 'properties' => $properties]);
        } catch (\Exception $e) {
            return $this->render('admin/bookings/create', ['users' => [], 'properties' => [], 'error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $data = $_POST;
            $stmt = $this->db->prepare(
                "INSERT INTO plot_bookings (customer_id, plot_id, booking_amount, total_plot_value, booking_date, status, notes, created_at)
                 VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())"
            );
            $stmt->execute([
                $data['customer_id'],
                $data['plot_id'],
                $data['booking_amount'] ?? $data['total_plot_value'] ?? 0,
                $data['total_plot_value'] ?? 0,
                $data['booking_date'] ?? date('Y-m-d'),
                $data['notes'] ?? ''
            ]);
            $_SESSION['success'] = 'Booking created successfully.';
            $this->redirect('/admin/bookings');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/admin/bookings/create');
        }
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT b.*, pl.plot_number, pl.area_sqft, pl.price_per_sqft,
                        c.name as colony_name,
                        u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                        a.name as associate_name
                 FROM plot_bookings b
                 LEFT JOIN plots pl ON b.plot_id = pl.id
                 LEFT JOIN colonies c ON pl.colony_id = c.id
                 LEFT JOIN users u ON b.customer_id = u.id
                 LEFT JOIN users a ON b.associate_id = a.id
                 WHERE b.id = ?"
            );
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            $payments = [];
            $total_paid = 0;
            $commissions = [];
            $total_commission = 0;
            try {
                $pStmt = $this->db->prepare("SELECT * FROM booking_payment_schedules WHERE booking_id = ? ORDER BY due_date ASC");
                $pStmt->execute([$id]);
                $payments = $pStmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($payments as $pmt) {
                    if (($pmt['paid_date'] ?? '') !== '') {
                        $total_paid += floatval($pmt['paid_amount'] ?? $pmt['amount'] ?? 0);
                    }
                }
            } catch (\Exception $e) { error_log('BookingController::show payment error: ' . $e->getMessage()); }
            try {
                $cStmt = $this->db->prepare("SELECT mcl.*, u.name as associate_name FROM mlm_commission_ledger mcl LEFT JOIN users u ON mcl.associate_id = u.id WHERE mcl.booking_id = ? ORDER BY mcl.created_at DESC");
                $cStmt->execute([$id]);
                $commissions = $cStmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($commissions as $cm) {
                    $total_commission += floatval($cm['amount'] ?? 0);
                }
            } catch (\Exception $e) { error_log('BookingController::show commission error: ' . $e->getMessage()); }

            return $this->render('admin/bookings/show', [
                'booking'          => $booking,
                'payments'         => $payments,
                'total_paid'       => $total_paid,
                'commissions'      => $commissions,
                'total_commission' => $total_commission
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/bookings/show', [
                'booking' => null, 'payments' => [], 'total_paid' => 0,
                'commissions' => [], 'total_commission' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT b.*, pl.plot_number, pl.colony_id, u.name as customer_name FROM plot_bookings b LEFT JOIN plots pl ON b.plot_id = pl.id LEFT JOIN users u ON b.customer_id = u.id WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
            [$tidSql, $tidParams] = $this->tenantWhere();
            $customers = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role IN ('customer','user'){$tidSql} ORDER BY name", $tidParams) ?: [];
            $plots = $this->db->query("SELECT pl.id, pl.plot_number, c.name as colony_name FROM plots pl LEFT JOIN colonies c ON pl.colony_id = c.id WHERE pl.status IN ('available','booked') ORDER BY c.name, pl.plot_number")->fetchAll(\PDO::FETCH_ASSOC);
            return $this->render('admin/bookings/edit', ['booking' => $booking, 'customers' => $customers, 'plots' => $plots]);
        } catch (\Exception $e) {
            return $this->render('admin/bookings/edit', ['booking' => null, 'customers' => [], 'plots' => [], 'error' => $e->getMessage()]);
        }
    }

    public function update($id)
    {
        try {
            $data = $_POST;
            $stmt = $this->db->prepare(
                "UPDATE plot_bookings SET customer_id = ?, plot_id = ?, booking_amount = ?, total_plot_value = ?, status = ?, notes = ? WHERE id = ?"
            );
            $stmt->execute([
                $data['customer_id'],
                $data['plot_id'],
                $data['booking_amount'] ?? 0,
                $data['total_plot_value'] ?? 0,
                $data['status'] ?? 'pending',
                $data['notes'] ?? '',
                $id
            ]);
            $_SESSION['success'] = 'Booking updated successfully.';
            // Hot-path: booking status/amount changes affect the admin dashboard KPI bundle.
            \App\Services\Cache\HotPathCacheService::invalidateAdminDashboard();
            $this->redirect('/admin/bookings');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/admin/bookings/' . $id . '/edit');
        }
    }

    public function destroy($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM plot_bookings WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Booking deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
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
            $_SESSION['success'] = 'Payment processed successfully.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
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
        } catch (\Exception $e) {
            return $this->jsonErrorLocal($e->getMessage(), 500);
        }
    }

    public function myBookings()
    {
        try {
            $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
            if (!$userId) {
                return $this->jsonErrorLocal('Unauthorized', 401);
            }
            $sql = "SELECT b.*, pl.plot_number, pl.area_sqft, c.name as colony_name 
                    FROM plot_bookings b
                    JOIN plots pl ON b.plot_id = pl.id
                    JOIN colonies c ON pl.colony_id = c.id
                    WHERE b.customer_id = ?
                    ORDER BY b.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonSuccess($bookings);
        } catch (\Exception $e) {
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
