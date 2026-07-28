<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Communication\NotificationService;
use Exception;

class RegistryController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';

            $where = [];
            $params = [];
            if (!empty($status)) {
                $where[] = "b.registry_status = ?";
                $params[] = $status;
            }
            if (!empty($search)) {
                $where[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR p.title LIKE ?)";
                $s = '%' . $search . '%';
                $params[] = $s; $params[] = $s; $params[] = $s;
            }
            $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings b 
                LEFT JOIN properties p ON b.property_id = p.id 
                LEFT JOIN users u ON b.customer_id = u.id 
                $whereClause");
            $countStmt->execute($params);
            $total = intval($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            $stmt = $this->db->prepare("SELECT b.*, p.title as property_title, p.location as property_location,
                u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                $whereClause
                ORDER BY b.created_at DESC");
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $statusCounts = $this->db->fetchAll("SELECT registry_status, COUNT(*) as count FROM bookings GROUP BY registry_status");
            $counts = ['not_started' => 0, 'documents_pending' => 0, 'stamp_duty_pending' => 0, 'appointment_scheduled' => 0, 'registered' => 0, 'mutation_pending' => 0, 'completed' => 0, 'cancelled' => 0];
            foreach ($statusCounts as $row) {
                $counts[$row['registry_status']] = intval($row['count']);
            }

            return $this->render('admin/registry/index', [
                'page_title' => 'Registry Management',
                'bookings' => $bookings,
                'total' => $total,
                'filters' => ['status' => $status, 'search' => $search],
                'status_counts' => $counts
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/registry/index', [
                'page_title' => 'Registry Management',
                'bookings' => [],
                'total' => 0,
                'filters' => ['status' => '', 'search' => ''],
                'status_counts' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT b.*, p.title as property_title, p.location as property_location,
                p.price as property_price, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                pl.plot_number, pl.area_sqft, c.name as colony_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots pl ON b.plot_id = pl.id
                LEFT JOIN colonies c ON pl.colony_id = c.id
                WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                $this->redirect('/admin/registry');
            }

            $activities = $this->db->prepare("SELECT * FROM registry_activity_log WHERE booking_id = ? ORDER BY created_at DESC");
            $activities->execute([$id]);
            $activities = $activities->fetchAll(\PDO::FETCH_ASSOC);

            return $this->render('admin/registry/show', [
                'page_title' => 'Registry - Booking #' . ($booking['booking_number'] ?? $id),
                'booking' => $booking,
                'activities' => $activities
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/registry');
        }
    }

    public function updateDocuments($id)
    {
        $this->requireAdmin();
        try {
            $status = $_POST['status'] ?? 'documents_pending';
            $notes = $_POST['notes'] ?? '';

            $tid = $this->tenantId();
            $stmt = $this->db->prepare("UPDATE bookings SET registry_status = ?, registry_notes = CONCAT(IFNULL(registry_notes,''), ?) WHERE id = ? AND tenant_id = ?");
            $note = "\n[" . date('Y-m-d H:i') . "] Documents " . ($status === 'documents_pending' ? 'marked pending' : 'collected') . ": " . $notes;
            $stmt->execute([$status, $note, $id, $tid]);

            $this->logRegistryActivity($id, 'documents_' . ($status === 'documents_pending' ? 'pending' : 'collected'), $notes);

            try {
                $this->notificationService->sendRegistryUpdate($id, $status);
            } catch (\Exception $e) {
                error_log("RegistryController: notification failed: " . $e->getMessage());
            }

            $this->setFlash('success', 'Documents status updated successfully');
            $this->redirect('/admin/registry/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/registry/show/' . $id);
        }
    }

    public function updateStampDuty($id)
    {
        $this->requireAdmin();
        try {
            $stampDuty = floatval($_POST['stamp_duty_amount'] ?? 0);
            $regFees = floatval($_POST['registration_fees'] ?? 0);
            $notes = $_POST['notes'] ?? '';

            $stmt = $this->db->prepare("UPDATE bookings SET stamp_duty_amount = ?, registration_fees = ?, registry_status = 'stamp_duty_pending', registry_notes = CONCAT(IFNULL(registry_notes,''), ?) WHERE id = ? AND tenant_id = ?");
            $note = "\n[" . date('Y-m-d H:i') . "] Stamp duty: ₹$stampDuty, Reg fees: ₹$regFees. " . $notes;
            $stmt->execute([$stampDuty, $regFees, $note, $id, $this->tenantId()]);

            $this->logRegistryActivity($id, 'stamp_duty_recorded', "Stamp Duty: ₹$stampDuty, Registration Fees: ₹$regFees. $notes");

            $this->setFlash('success', 'Stamp duty details recorded successfully');
            $this->redirect('/admin/registry/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/registry/show/' . $id);
        }
    }

    public function scheduleAppointment($id)
    {
        $this->requireAdmin();
        try {
            $appointmentDate = $_POST['appointment_date'] ?? '';
            $office = $_POST['sub_registrar_office'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($appointmentDate) || empty($office)) {
                $this->setFlash('error', 'Appointment date and office are required');
                $this->redirect('/admin/registry/show/' . $id);
            }

            $stmt = $this->db->prepare("UPDATE bookings SET appointment_date = ?, sub_registrar_office = ?, registry_status = 'appointment_scheduled', registry_notes = CONCAT(IFNULL(registry_notes,''), ?) WHERE id = ? AND tenant_id = ?");
            $note = "\n[" . date('Y-m-d H:i') . "] Appointment scheduled at $office on $appointmentDate. " . $notes;
            $stmt->execute([$appointmentDate, $office, $note, $id, $this->tenantId()]);

            $this->logRegistryActivity($id, 'appointment_scheduled', "Office: $office, Date: $appointmentDate. $notes");

            try {
                $this->notificationService->sendRegistryUpdate($id, 'appointment_scheduled');
            } catch (\Exception $e) {
                error_log("RegistryController: notification failed: " . $e->getMessage());
            }

            $this->setFlash('success', 'Appointment scheduled successfully');
            $this->redirect('/admin/registry/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/registry/show/' . $id);
        }
    }

    public function markRegistered($id)
    {
        $this->requireAdmin();
        try {
            $registryNumber = $_POST['registry_number'] ?? '';
            $registryDate = $_POST['registry_date'] ?? date('Y-m-d');
            $notes = $_POST['notes'] ?? '';

            if (empty($registryNumber)) {
                $this->setFlash('error', 'Registry number is required');
                $this->redirect('/admin/registry/show/' . $id);
            }

            $stmt = $this->db->prepare("UPDATE bookings SET registry_number = ?, registry_date = ?, registry_status = 'registered', registry_notes = CONCAT(IFNULL(registry_notes,''), ?) WHERE id = ? AND tenant_id = ?");
            $note = "\n[" . date('Y-m-d H:i') . "] Registered: #$registryNumber on $registryDate. " . $notes;
            $stmt->execute([$registryNumber, $registryDate, $note, $id, $this->tenantId()]);

            $this->logRegistryActivity($id, 'registered', "Registry #$registryNumber, Date: $registryDate. $notes");

            try {
                $this->notificationService->sendRegistryUpdate($id, 'registered');
            } catch (\Exception $e) {
                error_log("RegistryController: notification failed: " . $e->getMessage());
            }

            $this->setFlash('success', 'Property marked as registered successfully');
            $this->redirect('/admin/registry/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/registry/show/' . $id);
        }
    }

    public function updateMutation($id)
    {
        $this->requireAdmin();
        try {
            $mutationStatus = $_POST['mutation_status'] ?? 'pending';
            $mutationNumber = $_POST['mutation_number'] ?? '';
            $mutationDate = $_POST['mutation_date'] ?? null;
            $notes = $_POST['notes'] ?? '';

            $updates = [];
            $params = [];
            $updates[] = "mutation_status = ?";
            $params[] = $mutationStatus;
            if (!empty($mutationNumber)) {
                $updates[] = "mutation_number = ?";
                $params[] = $mutationNumber;
            }
            if (!empty($mutationDate)) {
                $updates[] = "mutation_date = ?";
                $params[] = $mutationDate;
            }
            if ($mutationStatus === 'completed') {
                $updates[] = "registry_status = 'completed'";
            }
            $updates[] = "registry_notes = CONCAT(IFNULL(registry_notes,''), ?)";
            $params[] = "\n[" . date('Y-m-d H:i') . "] Mutation: $mutationStatus" . (!empty($mutationNumber) ? " #$mutationNumber" : "") . ". $notes";
            $params[] = $id;
            $params[] = $this->tenantId();

            $stmt = $this->db->prepare("UPDATE bookings SET " . implode(', ', $updates) . " WHERE id = ? AND tenant_id = ?");
            $stmt->execute($params);

            $this->logRegistryActivity($id, 'mutation_' . $mutationStatus, "Mutation #$mutationNumber, Status: $mutationStatus. $notes");

            if ($mutationStatus === 'completed') {
                try {
                    $this->notificationService->sendRegistryUpdate($id, 'completed');
                } catch (\Exception $e) {
                    error_log("RegistryController: notification failed: " . $e->getMessage());
                }
            }

            $this->setFlash('success', 'Mutation status updated successfully');
            $this->redirect('/admin/registry/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/registry/show/' . $id);
        }
    }

    public function generateCertificate($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT b.*, p.title as property_title, p.location as property_location,
                p.price as property_price, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                pl.plot_number, pl.area_sqft, c.name as colony_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots pl ON b.plot_id = pl.id
                LEFT JOIN colonies c ON pl.colony_id = c.id
                WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                $this->redirect('/admin/registry');
            }

            require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';

            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            $pdf->SetCreator('APS Dream Home');
            $pdf->SetAuthor('APS Dream Home');
            $pdf->SetTitle('Registry Certificate - ' . ($booking['booking_number'] ?? $id));
            $pdf->SetSubject('Registry Completion Certificate');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pdf->AddPage();

            $html = $this->getCertificateHtml($booking);

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->Output('Registry_Certificate_' . ($booking['booking_number'] ?? $id) . '.pdf', 'D');
            exit;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error generating certificate: ' . $e->getMessage());
            $this->redirect('/admin/registry/show/' . $id);
        }
    }

    public function history($bookingId)
    {
        $this->requireAdmin();
        try {
            try {
                $activities = $this->db->prepare("SELECT * FROM registry_activity_log WHERE booking_id = ? ORDER BY created_at DESC");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $activities->execute([$bookingId]);
            $activities = $activities->fetchAll(\PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("SELECT b.booking_number, u.name as customer_name FROM bookings b LEFT JOIN users u ON b.customer_id = u.id WHERE b.id = ?");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $this->render('admin/registry/history', [
                'page_title' => 'Registry History - Booking #' . ($booking['booking_number'] ?? $bookingId),
                'activities' => $activities,
                'booking' => $booking,
                'booking_id' => $bookingId
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/registry/history', [
                'page_title' => 'Registry History',
                'activities' => [],
                'booking' => null,
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function logRegistryActivity($bookingId, $action, $details = '')
    {
        try {
            try {
                $tid = $this->tenantId();
                $stmt = $this->db->prepare("INSERT INTO registry_activity_log (booking_id, action, details, performed_by, created_at, tenant_id) VALUES (?, ?, ?, ?, NOW(), ?)");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([$bookingId, $action, $details, $_SESSION['admin_id'] ?? null, $tid]);
        } catch (\Exception $e) {
                    error_log("RegistryController.php: " . $e->getMessage());
        }
    }

    private function getCertificateHtml($booking)
    {
        $customerName = htmlspecialchars($booking['customer_name'] ?? '');
        $propertyTitle = htmlspecialchars($booking['property_title'] ?? '');
        $propertyLocation = htmlspecialchars($booking['property_location'] ?? '');
        $plotNumber = htmlspecialchars($booking['plot_number'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? '');
        $areaSqft = htmlspecialchars(number_format($booking['area_sqft'] ?? 0));
        $registryNumber = htmlspecialchars($booking['registry_number'] ?? '');
        $registryDate = htmlspecialchars(date('d F Y', strtotime($booking['registry_date'])));
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? '');
        $stampDuty = htmlspecialchars(number_format(floatval($booking['stamp_duty_amount'] ?? 0), 2));
        $regFees = htmlspecialchars(number_format(floatval($booking['registration_fees'] ?? 0), 2));
        $office = htmlspecialchars($booking['sub_registrar_office'] ?? '');
        $mutationNumber = htmlspecialchars($booking['mutation_number'] ?? '');
        $mutationDate = !empty($booking['mutation_date']) ? htmlspecialchars(date('d F Y', strtotime($booking['mutation_date']))) : 'N/A';

        return <<<HTML
<style>
    body { font-family: 'freeserif', 'serif'; }
    .certificate-wrapper { border: 3px double #1a5276; padding: 30px; margin: 10px; }
    h1 { text-align: center; color: #1a5276; font-size: 24pt; margin-bottom: 5px; }
    h2 { text-align: center; color: #2e86c1; font-size: 14pt; margin-top: 0; font-weight: normal; }
    .seal { text-align: center; font-size: 10pt; color: #666; margin: 15px 0; }
    .content { margin: 20px 0; }
    .content p { font-size: 11pt; line-height: 1.8; }
    .details-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .details-table th, .details-table td { border: 1px solid #999; padding: 8px 12px; text-align: left; font-size: 10pt; }
    .details-table th { background-color: #eaf2f8; width: 40%; }
    .footer { margin-top: 40px; text-align: center; font-size: 9pt; color: #888; border-top: 1px solid #ccc; padding-top: 10px; }
    .signatures { margin-top: 30px; }
    .signatures table { width: 100%; }
    .signatures td { width: 50%; text-align: center; padding-top: 40px; font-size: 10pt; }
    .signature-line { border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px; }
</style>
<div class="certificate-wrapper">
    <h1>REGISTRY COMPLETION CERTIFICATE</h1>
    <h2>APS Dream Home - Property Registry</h2>
    <div class="seal">&#9679; &#9679; &#9679; Government Registered &#9679; &#9679; &#9679;</div>

    <div class="content">
        <p>This is to certify that the registry of the property described below has been successfully completed and duly registered with the office of the Sub-Registrar.</p>

        <table class="details-table">
            <tr><th>Booking Number</th><td>$bookingNumber</td></tr>
            <tr><th>Property Title</th><td>$propertyTitle</td></tr>
            <tr><th>Location</th><td>$propertyLocation</td></tr>
            <tr><th>Colony</th><td>$colonyName</td></tr>
            <tr><th>Plot Number</th><td>$plotNumber</td></tr>
            <tr><th>Area</th><td>$areaSqft sq. ft.</td></tr>
            <tr><th>Buyer Name</th><td>$customerName</td></tr>
            <tr><th>Registry Number</th><td>$registryNumber</td></tr>
            <tr><th>Registry Date</th><td>$registryDate</td></tr>
            <tr><th>Sub-Registrar Office</th><td>$office</td></tr>
            <tr><th>Stamp Duty Paid</th><td>&#8377; $stampDuty</td></tr>
            <tr><th>Registration Fees</th><td>&#8377; $regFees</td></tr>
            <tr><th>Mutation Number</th><td>$mutationNumber</td></tr>
            <tr><th>Mutation Date</th><td>$mutationDate</td></tr>
        </table>

        <p style="margin-top:20px;">This certificate serves as conclusive proof of registration and may be produced as evidence in any court of law or before any government authority.</p>
    </div>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line">Authorized Signatory</div>
                    <div style="font-size:9pt; color:#666;">APS Dream Home</div>
                </td>
                <td>
                    <div class="signature-line">Buyer / Authorized Person</div>
                    <div style="font-size:9pt; color:#666;">$customerName</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This certificate is electronically generated and does not require a physical signature.<br>
        APS Dream Home &bull; Generated on: {$registryDate}
    </div>
</div>
HTML;
    }
}
