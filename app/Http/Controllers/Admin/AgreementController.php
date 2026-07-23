<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AgreementGenerationService;
use App\Services\Communication\NotificationService;
use Exception;

class AgreementController extends AdminController
{
    private $agreementService;
    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->agreementService = new AgreementGenerationService();
        $this->notificationService = new NotificationService();
    }

    public function index()
    {
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            $where = [];
            $params = [];

            if (!empty($type)) {
                $where[] = "a.agreement_type = ?";
                $params[] = $type;
            }
            if (!empty($status)) {
                $where[] = "a.status = ?";
                $params[] = $status;
            }
            if (!empty($search)) {
                $where[] = "(a.agreement_number LIKE ? OR a.party_a_name LIKE ? OR a.party_b_name LIKE ? OR p.plot_number LIKE ?)";
                $s = '%' . $search . '%';
                $params[] = $s;
                $params[] = $s;
                $params[] = $s;
                $params[] = $s;
            }
            if (!empty($dateFrom)) {
                $where[] = "a.agreement_date >= ?";
                $params[] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $where[] = "a.agreement_date <= ?";
                $params[] = $dateTo;
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM agreements a LEFT JOIN plots p ON a.plot_id = p.id $whereClause");
            $countStmt->execute($params);
            $total = intval($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            $totalPages = max(1, ceil($total / $perPage));

            $stmt = $this->db->prepare("
                SELECT a.*, 
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       c.name as colony_name,
                       b.booking_number
                FROM agreements a
                LEFT JOIN plots p ON a.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN plot_bookings b ON a.booking_id = b.id
                $whereClause
                ORDER BY a.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);
            $agreements = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stats = $this->getAgreementStats();

            return $this->render('admin/agreements/index', [
                'agreements' => $agreements,
                'total' => $total,
                'stats' => $stats,
                'filters' => ['type' => $type, 'status' => $status, 'search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo],
                'total_pages' => $totalPages,
                'current_page' => $page,
                'page_title' => 'Agreements - APS Dream Home',
                'active_page' => 'agreements',
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/agreements/index', [
                'agreements' => [], 'total' => 0, 'stats' => [],
                'filters' => ['type' => '', 'status' => '', 'search' => '', 'date_from' => '', 'date_to' => ''],
                'total_pages' => 1, 'current_page' => 1,
                'error' => $e->getMessage(),
                'page_title' => 'Agreements - APS Dream Home',
            ]);
        }
    }

    public function create()
    {
        $bookings = [];
        try {
            $stmt = $this->db->prepare("
                SELECT b.id, b.booking_number, b.total_amount, b.status,
                       u.name as customer_name, u.phone as customer_phone,
                       p.plot_number, c.name as colony_name
                FROM plot_bookings b
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE b.status IN ('token_paid','agreement_signed','emi_active','partially_paid')
                ORDER BY b.created_at DESC
            ");
            $stmt->execute();
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AgreementController::create - bookings query error: " . $e->getMessage());
        }

        return $this->render('admin/agreements/create', [
            'bookings' => $bookings,
            'page_title' => 'Create Agreement - APS Dream Home',
            'active_page' => 'agreements',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        try {
            $agreementType = $_POST['agreement_type'] ?? '';
            $bookingId = intval($_POST['booking_id'] ?? 0);
            $plotId = intval($_POST['plot_id'] ?? 0);
            $partyAName = trim($_POST['party_a_name'] ?? '');
            $partyAId = intval($_POST['party_a_id'] ?? 0) ?: null;
            $partyBName = trim($_POST['party_b_name'] ?? '');
            $partyBId = intval($_POST['party_b_id'] ?? 0) ?: null;
            $agreementDate = $_POST['agreement_date'] ?? date('Y-m-d');
            $registrationDate = !empty($_POST['registration_date']) ? $_POST['registration_date'] : null;
            $stampDuty = floatval($_POST['stamp_duty_amount'] ?? 0);
            $registrationFee = floatval($_POST['registration_fee'] ?? 0);
            $totalValue = floatval($_POST['total_value'] ?? 0);
            $validityDate = !empty($_POST['validity_date']) ? $_POST['validity_date'] : null;
            $notes = trim($_POST['notes'] ?? '');

            $validTypes = ['sale_deed', 'allotment', 'mortgage', 'lease', 'nda', 'joint_venture', 'other'];
            if (!in_array($agreementType, $validTypes)) {
                $this->json(['success' => false, 'error' => 'Invalid agreement type'], 400);
                return;
            }
            if (empty($partyAName) || empty($partyBName)) {
                $this->json(['success' => false, 'error' => 'Both party names are required'], 400);
                return;
            }

            if ($bookingId > 0 && $plotId === 0) {
                try {
                    $bst = $this->db->prepare("SELECT plot_id FROM plot_bookings WHERE id = ?");
                    $bst->execute([$bookingId]);
                    $brow = $bst->fetch(\PDO::FETCH_ASSOC);
                    if ($brow) $plotId = intval($brow['plot_id']);
                } catch (\Throwable $e) {}
            }

            $agreementNumber = $this->generateAgreementNumber($agreementType);

            $stmt = $this->db->prepare("
                INSERT INTO agreements 
                (agreement_number, agreement_type, booking_id, plot_id, party_a_name, party_a_id, party_b_name, party_b_id,
                 agreement_date, registration_date, stamp_duty_amount, registration_fee, total_value, 
                 validity_date, notes, status, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, NOW(), NOW())
            ");
            $stmt->execute([
                $agreementNumber, $agreementType, $bookingId ?: null, $plotId ?: null,
                $partyAName, $partyAId, $partyBName, $partyBId,
                $agreementDate, $registrationDate, $stampDuty, $registrationFee, $totalValue,
                $validityDate, $notes, $_SESSION['admin_id'] ?? null
            ]);

            $agreementId = $this->db->lastInsertId();

            $this->json(['success' => true, 'id' => $agreementId, 'agreement_number' => $agreementNumber, 'redirect' => BASE_URL . '/admin/agreements/' . $agreementId]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => 'Failed to create agreement: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       p.plot_number, p.block, p.sector, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       c.name as colony_name, c.description as colony_description,
                       b.booking_number, b.total_amount as booking_amount, b.status as booking_status,
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM agreements a
                LEFT JOIN plots p ON a.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN plot_bookings b ON a.booking_id = b.id
                LEFT JOIN users u ON b.customer_id = u.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $agreement = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$agreement) {
                $this->setFlash('error', 'Agreement not found');
                $this->redirect('/admin/agreements');
                return;
            }

            $relatedDocs = [];
            if ($agreement['booking_id']) {
                try {
                    $dstmt = $this->db->prepare("SELECT * FROM generated_documents WHERE entity_type = 'booking' AND entity_id = ? ORDER BY generated_at DESC");
                    $dstmt->execute([$agreement['booking_id']]);
                    $relatedDocs = $dstmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {}
            }

            return $this->render('admin/agreements/show', [
                'agreement' => $agreement,
                'related_docs' => $relatedDocs,
                'page_title' => 'Agreement #' . htmlspecialchars($agreement['agreement_number']),
                'active_page' => 'agreements',
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading agreement: ' . $e->getMessage());
            $this->redirect('/admin/agreements');
        }
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        try {
            $newStatus = $_POST['status'] ?? '';
            $validStatuses = ['draft', 'pending_signature', 'signed', 'registered', 'cancelled', 'expired'];
            if (!in_array($newStatus, $validStatuses)) {
                $this->json(['success' => false, 'error' => 'Invalid status'], 400);
                return;
            }

            $updates = ['status = ?', 'updated_at = NOW()'];
            $params = [$newStatus];

            if ($newStatus === 'signed') {
                $updates[] = 'registration_date = COALESCE(registration_date, CURDATE())';
            }
            if (!empty($_POST['stamp_duty_amount'])) {
                $updates[] = 'stamp_duty_amount = ?';
                $params[] = floatval($_POST['stamp_duty_amount']);
            }
            if (!empty($_POST['registration_fee'])) {
                $updates[] = 'registration_fee = ?';
                $params[] = floatval($_POST['registration_fee']);
            }
            if (!empty($_POST['notes'])) {
                $updates[] = 'notes = ?';
                $params[] = trim($_POST['notes']);
            }

            $params[] = $id;
            $setClause = implode(', ', $updates);
            $stmt = $this->db->prepare("UPDATE agreements SET $setClause WHERE id = ?");
            $stmt->execute($params);

            // Send status-based email notifications
            try {
                $agRow = $this->db->fetchOne("SELECT ag.*, pb.customer_id, pb.booking_number, p.plot_number, c.name as colony_name FROM agreements ag LEFT JOIN plot_bookings pb ON ag.booking_id = pb.id LEFT JOIN plots p ON ag.plot_id = p.id LEFT JOIN colonies c ON p.colony_id = c.id WHERE ag.id = ?", [(int)$id]);
                if (!empty($agRow['customer_id'])) {
                    $emailSvc = new \App\Services\EmailTemplateService();
                    $bookingData = [
                        'booking_number' => $agRow['booking_number'] ?? '',
                        'plot_number' => $agRow['plot_number'] ?? '',
                        'colony_name' => $agRow['colony_name'] ?? '',
                        'agreement_number' => $agRow['agreement_number'] ?? '',
                        'total_value' => $agRow['total_value'] ?? 0,
                    ];
                    if ($newStatus === 'pending_signature') {
                        $emailSvc->sendAgreementPending((int)$agRow['customer_id'], $bookingData);
                    } elseif ($newStatus === 'signed') {
                        $emailSvc->sendAgreementSigned((int)$agRow['customer_id'], $bookingData);
                    }
                }
            } catch (\Throwable $e) {
                error_log("[AgreementController] update email failed: " . $e->getMessage());
            }

            $this->json(['success' => true, 'message' => 'Agreement updated to ' . ucfirst(str_replace('_', ' ', $newStatus))]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    public function generate($id, $type = null)
    {
        if ($type === null && is_numeric($id)) {
            $type = $_GET['type'] ?? 'allotment';
        }

        try {
            $bookingId = $id;
            $validTypes = ['allotment', 'sale_agreement', 'payment_plan'];
            if (!in_array($type, $validTypes)) {
                $this->setFlash('error', 'Invalid agreement type');
                $this->redirect('/admin/agreements');
                return;
            }

            $docId = null;
            switch ($type) {
                case 'allotment':
                    $docId = $this->agreementService->generateAllotmentLetter($bookingId);
                    break;
                case 'sale_agreement':
                    $docId = $this->agreementService->generateSaleAgreement($bookingId);
                    break;
                case 'payment_plan':
                    $docId = $this->agreementService->generatePaymentPlan($bookingId);
                    break;
            }

            if ($docId) {
                try {
                    $this->notificationService->sendAgreementGenerated($bookingId, $type);
                } catch (\Exception $e) {
                    error_log("AgreementController: notification failed: " . $e->getMessage());
                }
                $this->setFlash('success', ucwords(str_replace('_', ' ', $type)) . ' generated successfully');
                $this->redirect('/admin/agreements/download/' . $docId);
            } else {
                $this->setFlash('error', 'Failed to generate agreement');
                $this->redirect('/admin/agreements');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error generating agreement: ' . $e->getMessage());
            $this->redirect('/admin/agreements');
        }
    }

    public function download($id)
    {
        try {
            $doc = $this->agreementService->getDocumentById($id);
            if (!$doc || empty($doc['file_path'])) {
                $this->setFlash('error', 'Document not found');
                $this->redirect('/admin/agreements');
                return;
            }

            $fullPath = defined('APS_ROOT') ? APS_ROOT . $doc['file_path'] : (defined('APP_ROOT') ? APP_ROOT . $doc['file_path'] : __DIR__ . '/../../..' . $doc['file_path']);

            if (!file_exists($fullPath)) {
                $this->setFlash('error', 'File not found on server');
                $this->redirect('/admin/agreements');
                return;
            }

            $stmt = $this->db->prepare("UPDATE generated_documents SET download_count = download_count + 1 WHERE id = ?");
            $stmt->execute([$id]);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: private, max-age=0');
            readfile($fullPath);
            exit;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error downloading document: ' . $e->getMessage());
            $this->redirect('/admin/agreements');
        }
    }

    public function preview($bookingId, $type)
    {
        try {
            $validTypes = ['allotment', 'sale_agreement', 'payment_plan'];
            if (!in_array($type, $validTypes)) {
                $this->setFlash('error', 'Invalid agreement type');
                $this->redirect('/admin/agreements');
                return;
            }

            $html = $this->agreementService->getHtmlPreview($bookingId, $type);
            $data = $this->agreementService->getBookingData($bookingId);

            return $this->render('admin/agreements/generate', [
                'booking' => $data,
                'agreement_type' => $type,
                'preview_html' => $html,
                'page_title' => 'Preview ' . ucwords(str_replace('_', ' ', $type)),
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error previewing agreement: ' . $e->getMessage());
            $this->redirect('/admin/agreements');
        }
    }

    public function sendToCustomer($id)
    {
        try {
            $doc = $this->agreementService->getDocumentById($id);
            if (!$doc) {
                $this->setFlash('error', 'Document not found');
                $this->redirect('/admin/agreements');
                return;
            }

            $bookingData = $this->agreementService->getBookingData($doc['entity_id']);
            $customerEmail = $bookingData['customer_email'] ?? '';
            $customerPhone = $bookingData['customer_phone'] ?? '';

            if (!empty($customerEmail)) {
                // Send via EmailTemplateService (branded HTML email)
                try {
                    $emailSvc = new \App\Services\EmailTemplateService();
                    $emailSvc->sendAgreementPending(intval($bookingData['customer_id'] ?? 0), [
                        'booking_number' => $doc['entity_id'] ?? '',
                        'plot_number' => $bookingData['plot_number'] ?? '',
                        'colony_name' => $bookingData['colony_name'] ?? '',
                        'agreement_number' => $doc['document_code'] ?? '',
                        'total_value' => $bookingData['total_value'] ?? 0,
                    ]);
                } catch (\Throwable $e) {
                    error_log("[AgreementController] sendToCustomer email fallback: " . $e->getMessage());
                    // Fallback: queue plain email
                    $subject = 'Your Agreement - ' . ($doc['title'] ?? 'APS Dream Home');
                    $message = "Dear " . ($bookingData['customer_name'] ?? 'Customer') . ",\n\n";
                    $message .= "Please find your agreement document attached.\n\n";
                    $message .= "Document: " . $doc['title'] . "\n";
                    $message .= "Document No: " . $doc['document_code'] . "\n\n";
                    $message .= "You can download it from: " . (BASE_URL) . "/admin/agreements/download/" . $id . "\n\n";
                    $message .= "Thank you,\n" . ($this->company['company_name'] ?? 'APS Dream Home');
                    try {
                        $stmt2 = $this->db->prepare("INSERT INTO email_queue (recipient_email, subject, body, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
                        $stmt2->execute([$customerEmail, $subject, $message]);
                    } catch (\Exception $e2) {
                        error_log("AgreementController: email_queue fallback failed: " . $e2->getMessage());
                    }
                }
            }

            if (!empty($customerPhone)) {
                $smsText = "Dear " . ($bookingData['customer_name'] ?? 'Customer') . ", your " . $doc['title'] . " is ready. Doc No: " . $doc['document_code'] . " - APS Dream Home";
                try {
                    $stmt = $this->db->prepare("INSERT INTO sms_queue (recipient_phone, message, status, created_at) VALUES (?, ?, 'pending', NOW())");
                    $stmt->execute([$customerPhone, $smsText]);
                } catch (\Exception $e) {
                    error_log("AgreementController.php: " . $e->getMessage());
                }
            }

            try {
                $this->notificationService->sendAgreementGenerated($doc['entity_id'], $doc['title'] ?? 'agreement');
            } catch (\Exception $e) {
                error_log("AgreementController: sendToCustomer notification failed: " . $e->getMessage());
            }

            $this->agreementService->markAsSent($id);

            $this->setFlash('success', 'Agreement sent to customer successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error sending agreement: ' . $e->getMessage());
        }
        $this->redirect('/admin/agreements');
    }

    private function generateAgreementNumber(string $type): string
    {
        $typeMap = [
            'sale_deed' => 'SD',
            'allotment' => 'AL',
            'mortgage' => 'MG',
            'lease' => 'LS',
            'nda' => 'ND',
            'joint_venture' => 'JV',
            'other' => 'OT',
        ];
        $prefix = $typeMap[$type] ?? 'AG';
        $year = date('Y');

        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM agreements WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $count = intval($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0) + 1;

        return "APS/$prefix/$year/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function getAgreementStats(): array
    {
        $stats = ['total' => 0, 'draft' => 0, 'pending_signature' => 0, 'signed' => 0, 'registered' => 0, 'cancelled' => 0, 'expired' => 0];
        try {
            $stmt = $this->db->query("SELECT status, COUNT(*) as cnt FROM agreements GROUP BY status");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $stats[$row['status']] = intval($row['cnt']);
                $stats['total'] += intval($row['cnt']);
            }
        } catch (\Throwable $e) {}
        return $stats;
    }
}
