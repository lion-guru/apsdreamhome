<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Legal\RegistryEligibilityService;
use Exception;

class NocController extends AdminController
{
    /** @var RegistryEligibilityService */
    protected $eligibilityService;

    public function __construct()
    {
        parent::__construct();
        $this->eligibilityService = new RegistryEligibilityService();
    }

    /**
     * List all registry/NOC records.
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            $registries = $this->db->query(
                "SELECT r.*, pb.booking_number, u.name AS customer_name,
                        p.plot_number AS plot_number
                 FROM registries r
                 LEFT JOIN plot_bookings pb ON pb.id = r.booking_id
                 LEFT JOIN users u ON u.id = r.user_id
                 LEFT JOIN plots p ON p.id = r.plot_id
                 ORDER BY r.created_at DESC
                 LIMIT 100"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $registries = [];
        }
        try {
            $nocs = $this->db->query(
                "SELECT n.*, pb.booking_number, u.name AS customer_name,
                        p.plot_number AS plot_number
                 FROM noc_requests n
                 LEFT JOIN plot_bookings pb ON pb.id = n.booking_id
                 LEFT JOIN users u ON u.id = n.user_id
                 LEFT JOIN plots p ON p.id = n.plot_id
                 ORDER BY n.created_at DESC
                 LIMIT 100"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $nocs = [];
        }
        return $this->render('admin/legal/noc-index', [
            'page_title' => 'Registry & NOC Records',
            'registries' => $registries,
            'nocs' => $nocs,
        ]);
    }

    /**
     * Eligibility check page (form).
     */
    public function eligibility()
    {
        $this->requireAdmin();
        try {
            $bookings = $this->db->query(
                "SELECT pb.id, pb.booking_number, u.name AS customer_name,
                        p.plot_number AS plot_number
                 FROM plot_bookings pb
                 LEFT JOIN users u ON u.id = pb.customer_id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 WHERE pb.status NOT IN ('cancelled', 'transferred')
                 ORDER BY pb.id DESC
                 LIMIT 200"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $bookings = [];
        }
        return $this->render('admin/legal/noc-eligibility', [
            'page_title' => 'Registry / NOC Eligibility Check',
            'bookings' => $bookings,
            'result' => null,
            'booking_id' => 0,
        ]);
    }

    /**
     * POST: Run eligibility check for a specific booking.
     */
    public function check()
    {
        $this->requireAdmin();
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $checkType = $_POST['check_type'] ?? 'registry';

        if ($bookingId <= 0) {
            $this->setFlash('error', 'Please select a booking.');
            $this->redirect('/admin/legal/noc-eligibility');
        }

        if ($checkType === 'noc') {
            $result = $this->eligibilityService->checkNocEligibility($bookingId);
            $schedule = $this->eligibilityService->getPaymentSchedule($bookingId);
            $nocStatus = $this->eligibilityService->getNocStatus($bookingId);
        } else {
            $result = $this->eligibilityService->checkEligibility($bookingId);
            $schedule = $this->eligibilityService->getPaymentSchedule($bookingId);
            $nocStatus = null;
        }

        $registryStatus = $this->eligibilityService->getRegistryStatus($bookingId);

        try {
            $bookings = $this->db->query(
                "SELECT pb.id, pb.booking_number, u.name AS customer_name,
                        p.plot_number AS plot_number
                 FROM plot_bookings pb
                 LEFT JOIN users u ON u.id = pb.customer_id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 WHERE pb.status NOT IN ('cancelled', 'transferred')
                 ORDER BY pb.id DESC
                 LIMIT 200"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $bookings = [];
        }

        return $this->render('admin/legal/noc-eligibility', [
            'page_title' => 'Eligibility Check Result',
            'bookings' => $bookings,
            'booking_id' => $bookingId,
            'result' => $result,
            'schedule' => $schedule,
            'registry_status' => $registryStatus,
            'noc_status' => $nocStatus ?? null,
            'check_type' => $checkType,
        ]);
    }

    /**
     * Show registry detail.
     */
    public function showRegistry(int $id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare(
                "SELECT r.*, pb.booking_number, u.name AS customer_name,
                        p.plot_number AS plot_number, c.name AS colony_name,
                        a.name AS associate_name
                 FROM registries r
                 LEFT JOIN plot_bookings pb ON pb.id = r.booking_id
                 LEFT JOIN users u ON u.id = r.user_id
                 LEFT JOIN plots p ON p.id = r.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 LEFT JOIN users a ON a.id = r.associate_id
                 WHERE r.id = ?
                 LIMIT 1"
            );
            $stmt->execute([$id]);
            $registry = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $registry = null;
        }
        if (!$registry) {
            $this->setFlash('error', 'Registry record not found.');
            $this->redirect('/admin/legal/noc-index');
        }
        return $this->render('admin/legal/registry-show', [
            'page_title' => 'Registry #' . $id,
            'registry' => $registry,
        ]);
    }

    /**
     * Show NOC detail.
     */
    public function showNoc(int $id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare(
                "SELECT n.*, pb.booking_number, u.name AS customer_name,
                        p.plot_number AS plot_number, c.name AS colony_name,
                        req.name AS requested_by_name,
                        appr.name AS approved_by_name
                 FROM noc_requests n
                 LEFT JOIN plot_bookings pb ON pb.id = n.booking_id
                 LEFT JOIN users u ON u.id = n.user_id
                 LEFT JOIN plots p ON p.id = n.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 LEFT JOIN users req ON req.id = n.requested_by
                 LEFT JOIN users appr ON appr.id = n.approved_by
                 WHERE n.id = ?
                 LIMIT 1"
            );
            $stmt->execute([$id]);
            $noc = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $noc = null;
        }
        if (!$noc) {
            $this->setFlash('error', 'NOC record not found.');
            $this->redirect('/admin/legal/noc-index');
        }
        return $this->render('admin/legal/noc-show', [
            'page_title' => 'NOC #' . $id,
            'noc' => $noc,
        ]);
    }
}
