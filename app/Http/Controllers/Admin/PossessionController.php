<?php

namespace App\Http\Controllers\Admin;

use App\Services\Communication\NotificationService;
use Exception;

class PossessionController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    public function dashboard()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(IF(possession_status='ready',1,0)) as ready_count,
                    SUM(IF(possession_status='scheduled',1,0)) as scheduled_count,
                    SUM(IF(possession_status='handed_over',1,0)) as handed_over_count,
                    SUM(IF(possession_status='delayed',1,0)) as delayed_count,
                    SUM(IF(possession_status='not_due',1,0)) as not_due_count
                FROM bookings WHERE registry_status IN ('registered','completed')
            ");
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

            $defectStmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_defects,
                    SUM(IF(status='open',1,0)) as open_defects,
                    SUM(IF(status='in_progress',1,0)) as in_progress_defects,
                    SUM(IF(status='resolved',1,0)) as resolved_defects,
                    SUM(IF(status='closed',1,0)) as closed_defects
                FROM defect_reports
            ");
            $defectStats = $defectStmt->fetch(\PDO::FETCH_ASSOC);

            $recent = $this->db->query("
                SELECT b.id, b.booking_number, b.possession_status, b.possession_date,
                       u.name as customer_name, p.title as property_title
                FROM bookings b
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN properties p ON b.property_id = p.id
                WHERE b.registry_status IN ('registered','completed')
                ORDER BY b.possession_date DESC, b.updated_at DESC
                LIMIT 10
            ")->fetchAll(\PDO::FETCH_ASSOC);

            return $this->render('admin/possession/index', [
                'page_title' => 'Possession Handover Dashboard',
                'stats' => $stats,
                'defect_stats' => $defectStats,
                'recent' => $recent
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/possession/index', [
                'page_title' => 'Possession Handover Dashboard',
                'stats' => [], 'defect_stats' => [], 'recent' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';

            $where = ["b.registry_status IN ('registered','completed')"];
            $params = [];
            if (!empty($status)) {
                $where[] = "b.possession_status = ?";
                $params[] = $status;
            }
            if (!empty($search)) {
                $where[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR p.title LIKE ?)";
                $s = '%' . $search . '%';
                $params[] = $s; $params[] = $s; $params[] = $s;
            }
            $whereClause = 'WHERE ' . implode(' AND ', $where);

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings b 
                LEFT JOIN properties p ON b.property_id = p.id 
                LEFT JOIN users u ON b.customer_id = u.id 
                $whereClause");
            $countStmt->execute($params);
            $total = intval($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            $stmt = $this->db->prepare("SELECT b.*, p.title as property_title, p.location as property_location,
                u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                pl.plot_number, c.name as colony_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots pl ON b.plot_id = pl.id
                LEFT JOIN colonies c ON pl.colony_id = c.id
                $whereClause
                ORDER BY b.possession_date DESC, b.updated_at DESC");
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $counts = $this->db->query("SELECT possession_status, COUNT(*) as count FROM bookings WHERE registry_status IN ('registered','completed') GROUP BY possession_status")->fetchAll(\PDO::FETCH_ASSOC);
            $statusCounts = ['not_due' => 0, 'ready' => 0, 'scheduled' => 0, 'handed_over' => 0, 'delayed' => 0];
            foreach ($counts as $row) {
                $statusCounts[$row['possession_status']] = intval($row['count']);
            }

            return $this->render('admin/possession/index', [
                'page_title' => 'Possession Handover',
                'bookings' => $bookings,
                'total' => $total,
                'filters' => ['status' => $status, 'search' => $search],
                'status_counts' => $statusCounts
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/possession/index', [
                'page_title' => 'Possession Handover',
                'bookings' => [], 'total' => 0,
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
                pl.plot_number, pl.area_sqft, c.name as colony_name,
                ha.name as handover_by_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots pl ON b.plot_id = pl.id
                LEFT JOIN colonies c ON pl.colony_id = c.id
                LEFT JOIN users ha ON b.handover_by = ha.id
                WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                $this->redirect('/admin/possession');
            }

            $checklist = $this->db->prepare("SELECT * FROM possession_checklist WHERE booking_id = ? ORDER BY is_completed ASC, id ASC");
            $checklist->execute([$id]);
            $checklist = $checklist->fetchAll(\PDO::FETCH_ASSOC);

            try {
                $defects = $this->db->prepare("SELECT d.*, u.name as reported_by_name, ru.name as resolved_by_name
                    FROM defect_reports d
                    LEFT JOIN users u ON d.reported_by = u.id
                    LEFT JOIN users ru ON d.resolved_by = ru.id
                    WHERE d.booking_id = ? ORDER BY d.created_at DESC");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $defects->execute([$id]);
            $defects = $defects->fetchAll(\PDO::FETCH_ASSOC);

            $pendingHandovers = $this->db->query("SELECT COUNT(*) as c FROM bookings WHERE registry_status IN ('registered','completed') AND possession_status IN ('ready','scheduled')")->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0;

            return $this->render('admin/possession/show', [
                'page_title' => 'Possession - Booking #' . ($booking['booking_number'] ?? $id),
                'booking' => $booking,
                'checklist' => $checklist,
                'defects' => $defects,
                'pending_handovers' => $pendingHandovers
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession');
        }
    }

    public function checklist($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT b.*, u.name as customer_name FROM bookings b LEFT JOIN users u ON b.customer_id = u.id WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                $this->redirect('/admin/possession');
            }

            $checklist = $this->db->prepare("SELECT * FROM possession_checklist WHERE booking_id = ? ORDER BY is_completed ASC, id ASC");
            $checklist->execute([$id]);
            $checklist = $checklist->fetchAll(\PDO::FETCH_ASSOC);

            return $this->render('admin/possession/checklist', [
                'page_title' => 'Handover Checklist - Booking #' . ($booking['booking_number'] ?? $id),
                'booking' => $booking,
                'checklist_items' => $checklist
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession');
        }
    }

    public function addChecklistItem($id)
    {
        $this->requireAdmin();
        try {
            $itemName = trim($_POST['item_name'] ?? '');
            if (empty($itemName)) {
                $this->setFlash('error', 'Checklist item name is required');
                $this->redirect('/admin/possession/checklist/' . $id);
            }

            $tid = $this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO possession_checklist (booking_id, item_name, tenant_id) VALUES (?, ?, ?)");
            $stmt->execute([$id, $itemName, $tid]);

            $this->setFlash('success', 'Checklist item added successfully');
            $this->redirect('/admin/possession/checklist/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession/checklist/' . $id);
        }
    }

    public function completeChecklistItem($id)
    {
        $this->requireAdmin();
        try {
            $itemId = intval($_POST['item_id'] ?? 0);
            $isCompleted = intval($_POST['is_completed'] ?? 0);
            $remarks = trim($_POST['remarks'] ?? '');

            if (!$itemId) {
                $this->setFlash('error', 'Invalid checklist item');
                $this->redirect('/admin/possession/checklist/' . $id);
            }

            $tid = $this->tenantId();
            if ($isCompleted) {
                $stmt = $this->db->prepare("UPDATE possession_checklist SET is_completed = 1, completed_by = ?, completed_at = NOW(), remarks = ? WHERE id = ? AND booking_id = ? AND tenant_id = ?");
                $stmt->execute([$_SESSION['admin_id'] ?? null, $remarks, $itemId, $id, $tid]);
            } else {
                $stmt = $this->db->prepare("UPDATE possession_checklist SET is_completed = 0, completed_by = NULL, completed_at = NULL, remarks = ? WHERE id = ? AND booking_id = ? AND tenant_id = ?");
                $stmt->execute([$remarks, $itemId, $id, $tid]);
            }

            $this->setFlash('success', 'Checklist item updated successfully');
            $this->redirect('/admin/possession/checklist/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession/checklist/' . $id);
        }
    }

    public function scheduleHandover($id)
    {
        $this->requireAdmin();
        try {
            $possessionDate = $_POST['possession_date'] ?? '';
            $notes = trim($_POST['handover_notes'] ?? '');

            if (empty($possessionDate)) {
                $this->setFlash('error', 'Possession date is required');
                $this->redirect('/admin/possession/show/' . $id);
            }

            $stmt = $this->db->prepare("UPDATE bookings SET possession_status = 'scheduled', possession_date = ?, handover_notes = CONCAT(IFNULL(handover_notes,''), ?) WHERE id = ? AND tenant_id = ?");
            $note = "\n[" . date('Y-m-d H:i') . "] Handover scheduled for " . $possessionDate . ". " . $notes;
            $stmt->execute([$possessionDate, $note, $id, $this->tenantId()]);

            $this->logPossessionActivity($id, 'scheduled', "Handover scheduled for " . $possessionDate . ". " . $notes);

            try {
                $this->notificationService->sendPossessionScheduled($id, $possessionDate);
            } catch (\Exception $e) {
                error_log("PossessionController: notification failed: " . $e->getMessage());
            }

            $this->setFlash('success', 'Handover scheduled successfully');
            $this->redirect('/admin/possession/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession/show/' . $id);
        }
    }

    public function markHandedOver($id)
    {
        $this->requireAdmin();
        try {
            $possessionDate = $_POST['possession_date'] ?? date('Y-m-d');
            $defectPeriod = intval($_POST['defect_liability_period'] ?? 365);
            $notes = trim($_POST['handover_notes'] ?? '');

            $letterNumber = 'POSS-' . date('Y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
            $defectEnd = date('Y-m-d', strtotime($possessionDate . ' + ' . $defectPeriod . ' days'));

            $stmt = $this->db->prepare("UPDATE bookings SET 
                possession_status = 'handed_over', 
                possession_date = ?, 
                possession_letter_number = ?,
                handover_notes = CONCAT(IFNULL(handover_notes,''), ?),
                handover_by = ?,
                defect_liability_period = ?,
                defect_liability_end_date = ?
                WHERE id = ? AND tenant_id = ?");
            $note = "\n[" . date('Y-m-d H:i') . "] Handed over on " . $possessionDate . ". Letter: " . $letterNumber . ". Defect liability: " . $defectPeriod . " days. " . $notes;
            $stmt->execute([$possessionDate, $letterNumber, $note, $_SESSION['admin_id'] ?? null, $defectPeriod, $defectEnd, $id, $this->tenantId()]);

            $this->logPossessionActivity($id, 'handed_over', "Handed over on $possessionDate. Letter #$letterNumber. Defect liability until $defectEnd. $notes");

            try {
                $this->notificationService->sendPossessionCompleted($id);
            } catch (\Exception $e) {
                error_log("PossessionController: notification failed: " . $e->getMessage());
            }

            $this->setFlash('success', 'Possession marked as handed over. Letter #' . $letterNumber . ' generated.');
            $this->redirect('/admin/possession/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession/show/' . $id);
        }
    }

    public function generateLetter($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT b.*, p.title as property_title, p.location as property_location,
                p.price as property_price, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                pl.plot_number, pl.area_sqft, pl.width, pl.length, c.name as colony_name,
                ha.name as handover_by_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots pl ON b.plot_id = pl.id
                LEFT JOIN colonies c ON pl.colony_id = c.id
                LEFT JOIN users ha ON b.handover_by = ha.id
                WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                $this->redirect('/admin/possession');
            }

            require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';

            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            $pdf->SetCreator('APS Dream Home');
            $pdf->SetAuthor('APS Dream Home');
            $pdf->SetTitle('Possession Letter - ' . ($booking['booking_number'] ?? $id));
            $pdf->SetSubject('Possession Handover Letter');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pdf->AddPage();

            $html = $this->getPossessionLetterHtml($booking);

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->Output('Possession_Letter_' . ($booking['booking_number'] ?? $id) . '.pdf', 'D');
            exit;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error generating letter: ' . $e->getMessage());
            $this->redirect('/admin/possession/show/' . $id);
        }
    }

    public function defectReports($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT b.*, u.name as customer_name FROM bookings b LEFT JOIN users u ON b.customer_id = u.id WHERE b.id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            try {
                $defects = $this->db->prepare("SELECT d.*, u.name as reported_by_name, ru.name as resolved_by_name
                    FROM defect_reports d
                    LEFT JOIN users u ON d.reported_by = u.id
                    LEFT JOIN users ru ON d.resolved_by = ru.id
                    WHERE d.booking_id = ? ORDER BY d.created_at DESC");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $defects->execute([$id]);
            $defects = $defects->fetchAll(\PDO::FETCH_ASSOC);

            return $this->render('admin/possession/show', [
                'page_title' => 'Defect Reports - Booking #' . ($booking['booking_number'] ?? $id),
                'booking' => $booking,
                'checklist' => [],
                'defects' => $defects,
                'pending_handovers' => 0,
                'focus_section' => 'defects'
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession');
        }
    }

    public function reportDefect($id)
    {
        $this->requireAdmin();
        try {
            $defectType = trim($_POST['defect_type'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';

            if (empty($description)) {
                $this->setFlash('error', 'Defect description is required');
                $this->redirect('/admin/possession/show/' . $id);
            }

            try {
                $tid = $this->tenantId();
                $stmt = $this->db->prepare("INSERT INTO defect_reports (booking_id, reported_by, defect_type, description, priority, tenant_id) VALUES (?, ?, ?, ?, ?, ?)");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$id, $_SESSION['admin_id'] ?? null, $defectType, $description, $priority, $tid]);

            $this->setFlash('success', 'Defect reported successfully');
            $this->redirect('/admin/possession/show/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession/show/' . $id);
        }
    }

    public function resolveDefect($defectId)
    {
        $this->requireAdmin();
        try {
            $resolutionNotes = trim($_POST['resolution_notes'] ?? '');

            if (empty($resolutionNotes)) {
                $this->setFlash('error', 'Resolution notes are required');
                $this->redirect('/admin/possession');
            }

            try {
                $stmt = $this->db->prepare("SELECT booking_id FROM defect_reports WHERE id = ?");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$defectId]);
            $defect = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$defect) {
                $this->setFlash('error', 'Defect not found');
                $this->redirect('/admin/possession');
            }

            $stmt = $this->db->prepare("UPDATE defect_reports SET status = 'resolved', resolution_notes = ?, resolved_by = ?, resolved_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$resolutionNotes, $_SESSION['admin_id'] ?? null, $defectId, $this->tenantId()]);

            $this->setFlash('success', 'Defect marked as resolved');
            $this->redirect('/admin/possession/show/' . $defect['booking_id']);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/admin/possession');
        }
    }

    private function logPossessionActivity($bookingId, $action, $details = '')
    {
        try {
            $this->db->query("INSERT INTO registry_activity_log (booking_id, action, details, performed_by, created_at, tenant_id) VALUES (?, ?, ?, ?, NOW(), ?)", [$bookingId, 'possession_' . $action, $details, $_SESSION['admin_id'] ?? null, $this->tenantId()]);
        } catch (\Exception $e) {
                    error_log("PossessionController.php: " . $e->getMessage());
        }
    }

    private function getPossessionLetterHtml($booking)
    {
        $customerName = htmlspecialchars($booking['customer_name'] ?? '');
        $customerAddress = htmlspecialchars($booking['customer_email'] ?? '') . ' / ' . htmlspecialchars($booking['customer_phone'] ?? '');
        $propertyTitle = htmlspecialchars($booking['property_title'] ?? '');
        $propertyLocation = htmlspecialchars($booking['property_location'] ?? '');
        $plotNumber = htmlspecialchars($booking['plot_number'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? '');
        $areaSqft = htmlspecialchars(number_format($booking['area_sqft'] ?? 0));
        $width = htmlspecialchars($booking['width'] ?? '--');
        $length = htmlspecialchars($booking['length'] ?? '--');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? '');
        $letterNumber = htmlspecialchars($booking['possession_letter_number'] ?? 'POSS-' . date('Y') . '-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT));
        $possessionDate = !empty($booking['possession_date']) ? htmlspecialchars(date('d F Y', strtotime($booking['possession_date']))) : date('d F Y');
        $handoverByName = htmlspecialchars($booking['handover_by_name'] ?? 'Authorized Signatory');
        $defectPeriod = intval($booking['defect_liability_period'] ?? 365);
        $defectEndDate = !empty($booking['defect_liability_end_date']) ? htmlspecialchars(date('d F Y', strtotime($booking['defect_liability_end_date']))) : date('d F Y', strtotime('+' . $defectPeriod . ' days'));
        $today = date('d F Y');
        $price = htmlspecialchars(number_format(floatval($booking['property_price'] ?? 0), 2));

        return <<<HTML
<style>
    body { font-family: 'freeserif', 'serif'; }
    .letter-wrapper { border: 2px solid #1a5276; padding: 30px; margin: 10px; }
    .header { text-align: center; border-bottom: 2px solid #1a5276; padding-bottom: 15px; margin-bottom: 20px; }
    .header h1 { color: #1a5276; font-size: 22pt; margin-bottom: 0; }
    .header h2 { color: #2e86c1; font-size: 13pt; margin-top: 5px; font-weight: normal; }
    .header .letter-no { font-size: 10pt; color: #666; margin-top: 5px; }
    .date-line { text-align: right; font-size: 11pt; margin-bottom: 20px; }
    .content { margin: 20px 0; }
    .content p { font-size: 11pt; line-height: 1.8; text-align: justify; }
    .details-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .details-table th, .details-table td { border: 1px solid #999; padding: 8px 12px; text-align: left; font-size: 10pt; }
    .details-table th { background-color: #eaf2f8; width: 40%; }
    .terms { margin: 20px 0; padding: 15px; background: #fdf2e9; border-left: 4px solid #e67e22; }
    .terms h4 { color: #e67e22; margin-top: 0; }
    .terms ul { margin-bottom: 0; font-size: 10pt; line-height: 1.8; }
    .signatures { margin-top: 40px; }
    .signatures table { width: 100%; }
    .signatures td { width: 50%; text-align: center; padding-top: 40px; font-size: 10pt; }
    .signature-line { border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px; }
    .footer { margin-top: 30px; text-align: center; font-size: 9pt; color: #888; border-top: 1px solid #ccc; padding-top: 10px; }
</style>
<div class="letter-wrapper">
    <div class="header">
        <h1>POSSESSION HANDOVER LETTER</h1>
        <h2>APS Dream Home - Property Possession Certificate</h2>
        <div class="letter-no">Letter No: <strong>{$letterNumber}</strong></div>
    </div>

    <div class="date-line">Date: {$today}</div>

    <div class="content">
        <p>To,</p>
        <p><strong>{$customerName}</strong><br>
        {$customerAddress}</p>

        <p><strong>Subject: Possession Handover of Property at {$colonyName}</strong></p>

        <p>Dear {$customerName},</p>

        <p>Congratulations! We are pleased to inform you that the possession of the property booked by you under <strong>Booking #{$bookingNumber}</strong> is hereby handed over to you with effect from <strong>{$possessionDate}</strong>.</p>

        <p>The property has been inspected and is found to be in complete accordance with the agreed specifications and approved layout plans. All amenities and utilities as per the agreement have been provided.</p>

        <table class="details-table">
            <tr><th>Booking Number</th><td>{$bookingNumber}</td></tr>
            <tr><th>Property Title</th><td>{$propertyTitle}</td></tr>
            <tr><th>Location</th><td>{$propertyLocation}</td></tr>
            <tr><th>Colony</th><td>{$colonyName}</td></tr>
            <tr><th>Plot Number</th><td>{$plotNumber}</td></tr>
            <tr><th>Plot Dimensions</th><td>{$width} ft x {$length} ft</td></tr>
            <tr><th>Area</th><td>{$areaSqft} sq. ft.</td></tr>
            <tr><th>Total Consideration</th><td>&#8377; {$price}</td></tr>
            <tr><th>Possession Date</th><td>{$possessionDate}</td></tr>
            <tr><th>Handover By</th><td>{$handoverByName}</td></tr>
            <tr><th>Defect Liability Period</th><td>{$defectPeriod} days (until {$defectEndDate})</td></tr>
        </table>

        <div class="terms">
            <h4>Terms &amp; Conditions</h4>
            <ul>
                <li>The allottee has inspected the property and is satisfied with its condition and specifications.</li>
                <li>Any structural or material defects reported within {$defectPeriod} days ({$defectEndDate}) from the date of possession will be rectified by the company.</li>
                <li>Defects caused by normal wear and tear, misuse, or unauthorized alterations are not covered.</li>
                <li>The allottee shall maintain the property in good condition and comply with all colony/housing society rules.</li>
                <li>Property tax and all other applicable charges from the date of possession are the responsibility of the allottee.</li>
                <li>This possession does not constitute transfer of ownership; the same shall be effected through a registered Sale Deed.</li>
            </ul>
        </div>

        <p>We thank you for choosing APS Dream Home as your trusted real estate partner and wish you a wonderful experience in your new property.</p>
    </div>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line">{$handoverByName}</div>
                    <div style="font-size:9pt; color:#666;">Authorized Signatory<br>APS Dream Home</div>
                </td>
                <td>
                    <div class="signature-line">{$customerName}</div>
                    <div style="font-size:9pt; color:#666;">Allottee / Buyer</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This letter is electronically generated and does not require a physical signature.<br>
        APS Dream Home &bull; Generated on: {$today} &bull; Letter #{$letterNumber}
    </div>
</div>
HTML;
    }
}
