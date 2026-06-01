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
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';

            $where = ["b.plot_id IS NOT NULL"];
            $params = [];

            if (!empty($status)) {
                $where[] = "b.status = ?";
                $params[] = $status;
            }
            if (!empty($search)) {
                $where[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR p.plot_number LIKE ?)";
                $s = '%' . $search . '%';
                $params[] = $s; $params[] = $s; $params[] = $s;
            }
            $whereClause = 'WHERE ' . implode(' AND ', $where);

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings b LEFT JOIN users u ON b.customer_id = u.id LEFT JOIN plots p ON b.plot_id = p.id $whereClause");
            $countStmt->execute($params);
            $total = intval($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            $totalPages = max(1, ceil($total / $perPage));

            $stmt = $this->db->prepare("
                SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       p.plot_number, p.block, p.total_price, p.area_sqft,
                       c.name as colony_name
                FROM bookings b
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON b.colony_id = c.id
                $whereClause
                ORDER BY b.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $bookingIds = array_column($bookings, 'id');
            $agreementCounts = [];
            if (!empty($bookingIds)) {
                $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
                $agStmt = $this->db->prepare("SELECT entity_id, COUNT(*) as cnt, GROUP_CONCAT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(variables_data, '$.agreement_type')) SEPARATOR ',') as types FROM generated_documents WHERE entity_type = 'booking' AND entity_id IN ($placeholders) GROUP BY entity_id");
                $agStmt->execute($bookingIds);
                foreach ($agStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $agreementCounts[$row['entity_id']] = $row;
                }
            }

            return $this->render('admin/agreements/index', [
                'bookings' => $bookings,
                'agreement_counts' => $agreementCounts,
                'total' => $total,
                'filters' => ['status' => $status, 'search' => $search],
                'total_pages' => $totalPages,
                'current_page' => $page,
                'page_title' => 'Agreements - APS Dream Home',
                'active_page' => 'agreements',
            ]);
        } catch (Exception $e) {
            return $this->render('admin/agreements/index', [
                'bookings' => [], 'agreement_counts' => [], 'total' => 0,
                'filters' => ['status' => '', 'search' => ''],
                'total_pages' => 1, 'current_page' => 1,
                'error' => $e->getMessage(),
                'page_title' => 'Agreements - APS Dream Home',
            ]);
        }
    }

    public function generate($bookingId, $type)
    {
        try {
            $validTypes = ['allotment', 'sale_agreement', 'payment_plan'];
            if (!in_array($type, $validTypes)) {
                $this->setFlash('error', 'Invalid agreement type');
                $this->redirect('/admin/agreements');
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
        } catch (Exception $e) {
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
            }

            $fullPath = defined('APS_ROOT') ? APS_ROOT . $doc['file_path'] : (defined('APP_ROOT') ? APP_ROOT . $doc['file_path'] : __DIR__ . '/../../..' . $doc['file_path']);

            if (!file_exists($fullPath)) {
                $this->setFlash('error', 'File not found on server');
                $this->redirect('/admin/agreements');
            }

            $stmt = $this->db->prepare("UPDATE generated_documents SET download_count = download_count + 1 WHERE id = ?");
            $stmt->execute([$id]);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: private, max-age=0');
            readfile($fullPath);
            exit;
        } catch (Exception $e) {
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
            }

            $html = $this->agreementService->getHtmlPreview($bookingId, $type);
            $data = $this->agreementService->getBookingData($bookingId);

            return $this->render('admin/agreements/generate', [
                'booking' => $data,
                'agreement_type' => $type,
                'preview_html' => $html,
                'page_title' => 'Preview ' . ucwords(str_replace('_', ' ', $type)),
            ]);
        } catch (Exception $e) {
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
            }

            $bookingData = $this->agreementService->getBookingData($doc['entity_id']);
            $customerEmail = $bookingData['customer_email'] ?? '';
            $customerPhone = $bookingData['customer_phone'] ?? '';

            if (!empty($customerEmail)) {
                $subject = 'Your Agreement - ' . ($doc['title'] ?? 'APS Dream Home');
                $message = "Dear " . ($bookingData['customer_name'] ?? 'Customer') . ",\n\n";
                $message .= "Please find your agreement document attached.\n\n";
                $message .= "Document: " . $doc['title'] . "\n";
                $message .= "Document No: " . $doc['document_code'] . "\n\n";
                $message .= "You can download it from: " . (defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome') . "/admin/agreements/download/" . $id . "\n\n";
                $message .= "Thank you,\n" . ($this->company['company_name'] ?? 'APS Dream Home');

                try {
                    $stmt = $this->db->prepare("INSERT INTO email_queue (recipient_email, subject, body, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
                    $stmt->execute([$customerEmail, $subject, $message]);
                } catch (\Exception $e) {
                }
            }

            if (!empty($customerPhone)) {
                $smsText = "Dear " . ($bookingData['customer_name'] ?? 'Customer') . ", your " . $doc['title'] . " is ready. Doc No: " . $doc['document_code'] . " - APS Dream Home";
                try {
                    $stmt = $this->db->prepare("INSERT INTO sms_queue (recipient_phone, message, status, created_at) VALUES (?, ?, 'pending', NOW())");
                    $stmt->execute([$customerPhone, $smsText]);
                } catch (\Exception $e) {
                }
            }

            try {
                $this->notificationService->sendAgreementGenerated($doc['entity_id'], $doc['title'] ?? 'agreement');
            } catch (\Exception $e) {
                error_log("AgreementController: sendToCustomer notification failed: " . $e->getMessage());
            }

            $this->agreementService->markAsSent($id);

            $this->setFlash('success', 'Agreement sent to customer successfully');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error sending agreement: ' . $e->getMessage());
        }
        $this->redirect('/admin/agreements');
    }
}
