<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\VisitService;
use App\Traits\TenantAwareTrait;

/**
 * Public visit scheduling - book site visits
 */
class VisitController extends BaseController
{
    use TenantAwareTrait;
    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new VisitService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function book()
    {
        $propertyId = (int)($_GET['property_id'] ?? $_POST['property_id'] ?? 0);
        $property = null;
        if ($propertyId && $this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE id = ?");
                $stmt->execute([$propertyId]);
                $property = $stmt->fetch();
            } catch (\Throwable $e) {
                $property = null;
            }
        }
        $slots = $this->service ? $this->service->getAvailableSlots(date('Y-m-d'), date('Y-m-d', strtotime('+14 days')), $propertyId ?: null) : [];
        return $this->render('pages.visits.book', [
            'page_title' => 'Schedule Site Visit',
            'page_heading' => 'Book a Property Visit',
            'property' => $property,
            'slots' => $slots,
            'property_id' => $propertyId,
            'logged_in' => !empty($_SESSION['user_id'])
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect(BASE_URL . '/visit/book');
        }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $propertyId = (int)($_POST['property_id'] ?? 0);
        $visitDate = $_POST['visit_date'] ?? '';
        $visitTime = $_POST['visit_time'] ?? '';
        $visitType = $_POST['visit_type'] ?? 'site_visit';
        $notes = trim($_POST['notes'] ?? '');

        $errors = [];
        if (!$name) $errors[] = 'Name is required';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (!$phone) $errors[] = 'Phone is required';
        if (!$propertyId) $errors[] = 'Property is required';
        if (!$visitDate) $errors[] = 'Date is required';
        if (!$visitTime) $errors[] = 'Time slot is required';

        if ($errors) {
            $_SESSION['visit_errors'] = $errors;
            $_SESSION['visit_form'] = $_POST;
            return $this->redirect(BASE_URL . '/visit/book?property_id=' . $propertyId);
        }
        $result = $this->service ? $this->service->bookVisit([
            'customer_id' => $_SESSION['user_id'] ?? null,
            'property_id' => $propertyId,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'visit_date' => $visitDate,
            'visit_time' => $visitTime,
            'visit_type' => $visitType,
            'notes' => $notes
        ]) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            $_SESSION['visit_success'] = $result;
            return $this->redirect(BASE_URL . '/visit/confirm?id=' . $result['visit_id']);
        }
        $_SESSION['visit_errors'] = [$result['error']];
        return $this->redirect(BASE_URL . '/visit/book?property_id=' . $propertyId);
    }

    public function confirm()
    {
        $id = (int)($_GET['id'] ?? 0);
        $visit = $this->service ? $this->service->getById($id) : null;
        if (!$visit) {
            $this->setFlash('error', 'Visit not found');
            return $this->redirect(BASE_URL . '/');
        }
        return $this->render('pages.visits.confirm', [
            'page_title' => 'Visit Booked',
            'page_heading' => 'Visit Confirmed',
            'visit' => $visit
        ]);
    }

    public function myVisits()
    {
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            return $this->redirect(BASE_URL . '/login');
        }
        $visits = $this->service ? $this->service->getByUser((int)$userId) : [];
        return $this->render('pages.visits.my_visits', [
            'page_title' => 'My Visits',
            'page_heading' => 'My Property Visits',
            'visits' => $visits
        ]);
    }

    public function cancel()
    {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $reason = $_POST['reason'] ?? 'Cancelled by customer';
        if ($this->service && $id) {
            $this->service->cancel($id, $reason);
            $this->setFlash('success', 'Visit cancelled');
        }
        return $this->redirect(BASE_URL . '/visit/my-visits');
    }
}
