<?php

/**
 * Demand Letter Controller
 * Generates, lists, streams and dispatches demand notices for installment dues
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class DemandLetterController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance()->getPdo();
    }

    /**
     * List demand letters with filters and summary stats.
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;

            $filters = [
                'status'    => trim((string)($_GET['status'] ?? '')),
                'search'    => trim((string)($_GET['search'] ?? '')),
                'date_from' => trim((string)($_GET['date_from'] ?? '')),
                'date_to'   => trim((string)($_GET['date_to'] ?? '')),
            ];

            $where = ['dl.tenant_id = ?'];
            $params = [$this->tenantId()];

            if ($filters['status'] !== '') {
                $where[] = 'dl.status = ?';
                $params[] = $filters['status'];
            }
            if ($filters['search'] !== '') {
                $where[] = '(dl.letter_number LIKE ? OR b.booking_number LIKE ? OR COALESCE(cu.name, bu.name) LIKE ?)';
                $like = '%' . $filters['search'] . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            if ($filters['date_from'] !== '') {
                $where[] = 'dl.generated_date >= ?';
                $params[] = $filters['date_from'];
            }
            if ($filters['date_to'] !== '') {
                $where[] = 'dl.generated_date <= ?';
                $params[] = $filters['date_to'];
            }

            $whereSql = implode(' AND ', $where);

            $countSql = "SELECT COUNT(*) FROM booking_demand_letters dl
                         JOIN bookings b ON b.id = dl.booking_id
                         LEFT JOIN users cu ON cu.id = b.customer_id
                         LEFT JOIN users bu ON bu.id = b.user_id
                         WHERE {$whereSql}";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            $totalPages = max(1, (int)ceil($total / $perPage));

            $sql = "SELECT dl.*, b.booking_number,
                           COALESCE(cu.name, bu.name) AS customer_name,
                           COALESCE(cu.phone, bu.phone) AS customer_phone,
                           COALESCE(cu.email, bu.email) AS customer_email
                    FROM booking_demand_letters dl
                    JOIN bookings b ON b.id = dl.booking_id
                    LEFT JOIN users cu ON cu.id = b.customer_id
                    LEFT JOIN users bu ON bu.id = b.user_id
                    WHERE {$whereSql}
                    ORDER BY dl.generated_date DESC, dl.id DESC
                    LIMIT {$perPage} OFFSET {$offset}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $letters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stats = [
                'total'        => 0,
                'sent'         => 0,
                'paid'         => 0,
                'overdue'      => 0,
                'amount'       => 0,
                'total_amount' => 0,
            ];
            $statsSql = "SELECT
                            COUNT(*) AS total,
                            SUM(dl.status = 'sent') AS sent,
                            SUM(dl.status = 'paid') AS paid,
                            SUM(dl.status = 'overdue') AS overdue,
                            COALESCE(SUM(dl.amount), 0) AS amount,
                            COALESCE(SUM(CASE WHEN dl.status <> 'paid' THEN dl.amount ELSE 0 END), 0) AS total_amount
                         FROM booking_demand_letters dl
                         WHERE dl.tenant_id = ?";
            $stmt = $this->db->prepare($statsSql);
            $stmt->execute([$this->tenantId()]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $stats['total'] = (int)$row['total'];
                $stats['sent'] = (int)$row['sent'];
                $stats['paid'] = (int)$row['paid'];
                $stats['overdue'] = (int)$row['overdue'];
                $stats['amount'] = (float)$row['amount'];
                $stats['total_amount'] = (float)$row['total_amount'];
            }

            $pagination = [
                'current_page' => $page,
                'total_pages'  => $totalPages,
                'per_page'     => $perPage,
                'total'        => $total,
            ];

            return $this->render('admin.finance.demand_letters.index', [
                'letters'    => $letters,
                'stats'      => $stats,
                'filters'    => $filters,
                'pagination' => $pagination,
            ]);
        } catch (\Exception $e) {
            error_log('DemandLetterController::index error: ' . $e->getMessage());
            $this->setFlash('error', 'Unable to load demand letters.');
            $this->redirect('/admin/finance/dashboard');
        }
    }

    /**
     * Show the demand-letter generation form for a payment installment,
     * or render the booking picker when no installment is supplied.
     */
    public function generate($installmentId = null)
    {
        $this->requireAdmin();
        $installmentId = (int)($installmentId ?? 0);

        if ($installmentId <= 0 && $this->isPost()) {
            $installmentId = (int)($_POST['installment_id'] ?? 0);
        }

        if ($installmentId <= 0) {
            if (!$this->isPost()) {
                return $this->render('admin.finance.demand_letters.generate', [
                    'bookings' => $this->fetchBookingsWithInstallments(),
                ]);
            }
            $this->setFlash('error', 'Please select a booking and installment to generate the demand letter.');
            $this->redirect('/admin/finance/demand-letters/create');
        }

        try {
            $installment = $this->fetchInstallment($installmentId);
            if (!$installment) {
                $this->setFlash('error', 'Installment not found.');
                $this->redirect('/admin/finance/dashboard');
            }

            $bookingId = (int)$installment['booking_id'];
            $booking = $this->fetchBooking($bookingId);
            if (!$booking) {
                $this->setFlash('error', 'Booking not found.');
                $this->redirect('/admin/finance/dashboard');
            }

            if ($this->isPost()) {
                $this->validateCsrfOrFail();
                $letterNumber = $this->buildLetterNumber($booking['booking_number'] ?? '', $installment['installment_no']);

                $exists = true;
                $counter = 0;
                while ($exists) {
                    $candidate = $counter > 0 ? $letterNumber . '-' . $counter : $letterNumber;
                    $stmt = $this->db->prepare(
                        "SELECT COUNT(*) FROM booking_demand_letters WHERE letter_number = ? AND tenant_id = ?"
                    );
                    $stmt->execute([$candidate, $this->tenantId()]);
                    if ((int)$stmt->fetchColumn() === 0) {
                        $letterNumber = $candidate;
                        $exists = false;
                    } else {
                        $counter++;
                    }
                }

                $pdfService = new \App\Services\PDF\AgreementPDFService($this->db instanceof \PDO ? $this->db : null);
                $result = $pdfService->generateDemandLetter($installmentId);

                $pdfPath = null;
                if (!empty($result['success']) && !empty($result['pdf_path'])) {
                    $pdfPath = $result['pdf_path'];
                }

                $stmt = $this->db->prepare(
                    "INSERT INTO booking_demand_letters
                        (tenant_id, booking_id, installment_id, letter_number, generated_date,
                         due_date, amount, status, pdf_path, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'drafted', ?, NOW(), NOW())"
                );
                $stmt->execute([
                    $this->tenantId(),
                    $bookingId,
                    $installmentId,
                    $letterNumber,
                    date('Y-m-d'),
                    $installment['due_date'] ?? date('Y-m-d'),
                    $installment['amount'] ?? 0,
                    $pdfPath,
                ]);

                $letterId = (int)$this->db->lastInsertId();

                if ($pdfPath) {
                    $stmt = $this->db->prepare(
                        "UPDATE booking_payment_schedules SET demand_letter_pdf = ? WHERE id = ? AND tenant_id = ?"
                    );
                    $stmt->execute([$pdfPath, $installmentId, $this->tenantId()]);
                }

                $this->setFlash('success', 'Demand letter ' . $letterNumber . ' generated successfully.');
                $this->redirect('/admin/finance/demand-letters/' . $letterId . '/pdf');
            }

            return $this->render('admin.finance.demand_letters.generate', [
                'installment' => $installment,
                'booking'     => $booking,
                'customer'   => $this->fetchBookingCustomer($booking),
                'bookings'   => [],
            ]);
        } catch (\Exception $e) {
            error_log('DemandLetterController::generate error: ' . $e->getMessage());
            $this->setFlash('error', 'Demand letter generation failed.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        }
    }

    /**
     * Stream the generated demand-letter PDF.
     */
    public function pdf($id = null)
    {
        $this->requireAdmin();
        $id = (int)($id ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Invalid demand letter ID.');
            $this->redirect('/admin/finance/demand-letters');
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM booking_demand_letters WHERE id = ? AND tenant_id = ?"
            );
            $stmt->execute([$id, $this->tenantId()]);
            $letter = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$letter) {
                $this->setFlash('error', 'Demand letter not found.');
                $this->redirect('/admin/finance/demand-letters');
            }

            if (!$this->letterPdfExists($letter)) {
                $this->setFlash('error', 'Demand letter PDF not found.');
                $this->redirect('/admin/finance/demand-letters');
            }

            $pdfPath = $letter['pdf_path'];
            $filename = $letter['letter_number'] . '.pdf';

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($pdfPath));
            header('Cache-Control: no-cache, must-revalidate');
            readfile($pdfPath);
            exit;
        } catch (\Exception $e) {
            error_log('DemandLetterController::pdf error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to load demand letter PDF.');
            $this->redirect('/admin/finance/demand-letters');
        }
    }

    /**
     * Send a demand letter via WhatsApp; updates status/sent meta on success.
     */
    public function sendWhatsApp($id = null)
    {
        $this->requireAdmin();
        $id = (int)($id ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Invalid demand letter ID.');
            $this->redirect('/admin/finance/demand-letters');
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT dl.*, b.booking_number,
                        COALESCE(cu.name, bu.name) AS customer_name,
                        COALESCE(cu.phone, bu.phone) AS customer_phone
                 FROM booking_demand_letters dl
                 JOIN bookings b ON b.id = dl.booking_id
                 LEFT JOIN users cu ON cu.id = b.customer_id
                 LEFT JOIN users bu ON bu.id = b.user_id
                 WHERE dl.id = ? AND dl.tenant_id = ?
                 LIMIT 1"
            );
            $stmt->execute([$id, $this->tenantId()]);
            $letter = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$letter) {
                $this->setFlash('error', 'Demand letter not found.');
                $this->redirect('/admin/finance/demand-letters');
            }

            $phone = preg_replace('/[^0-9]/', '', $letter['customer_phone'] ?? '');
            if ($phone === '') {
                $this->setFlash('error', 'Customer phone number not available for WhatsApp delivery.');
                $this->redirect('/admin/finance/demand-letters?status=' . urlencode($letter['status']));
            }

            $message = "Dear " . ($letter['customer_name'] ?? 'Customer') . ","
                . " your demand letter (" . $letter['letter_number'] . ") for booking "
                . $letter['booking_number'] . " amounting to Rs. " . number_format((float)$letter['amount'], 2) . " is ready."
                . " Kindly make the payment before the due date " . $letter['due_date'] . ". - APS Dream Home";

            $delivered = false;
            try {
                $whatsapp = new \App\Services\Communication\WhatsAppWebService();
                $response = method_exists($whatsapp, 'sendMessage')
                    ? $whatsapp->sendMessage($phone, $message)
                    : null;
                if (is_array($response) && !empty($response['success'])) {
                    $delivered = true;
                } elseif (is_object($response) && !empty($response->success)) {
                    $delivered = true;
                } elseif ($response === true) {
                    $delivered = true;
                }
            } catch (\Exception $wa) {
                error_log('DemandLetterController::sendWhatsApp provider error: ' . $wa->getMessage());
            }

            $newStatus = $delivered ? 'sent' : ($letter['status'] ?: 'drafted');
            $stmt = $this->db->prepare(
                "UPDATE booking_demand_letters
                 SET status = ?, sent_via = 'whatsapp', sent_to_email = ?, sent_at = NOW(), updated_at = NOW()
                 WHERE id = ? AND tenant_id = ?"
            );
            $stmt->execute([$newStatus, $phone, $id, $this->tenantId()]);

            if ($delivered) {
                $this->setFlash('success', 'Demand letter sent via WhatsApp to ' . $phone . '.');
            } else {
                $this->setFlash('error', 'WhatsApp delivery failed; letter kept as ' . $newStatus . '. Please try again.');
            }
            $this->redirect('/admin/finance/demand-letters?status=' . urlencode($newStatus));
        } catch (\Exception $e) {
            error_log('DemandLetterController::sendWhatsApp error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to send demand letter via WhatsApp.');
            $this->redirect('/admin/finance/demand-letters');
        }
    }

    private function isPost(): bool
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper((string)$_SERVER['REQUEST_METHOD']) === 'POST';
    }

    private function buildLetterNumber(string $bookingNumber, $installmentNo): string
    {
        $booking = $bookingNumber !== '' ? $bookingNumber : 'NA';
        $inst = (int)$installmentNo;
        return 'DL-' . $booking . '-' . ($inst > 0 ? $inst : '0') . '-' . date('Ymd');
    }

    private function fetchInstallment(int $installmentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM booking_payment_schedules
             WHERE id = ? AND tenant_id = ?"
        );
        $stmt->execute([$installmentId, $this->tenantId()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchBooking(int $bookingId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*,
                    COALESCE(cu.name, bu.name) AS customer_name,
                    COALESCE(cu.phone, bu.phone) AS customer_phone,
                    COALESCE(cu.email, bu.email) AS customer_email
             FROM bookings b
             LEFT JOIN users cu ON cu.id = b.customer_id
             LEFT JOIN users bu ON bu.id = b.user_id
             WHERE b.id = ? AND b.tenant_id = ?
             LIMIT 1"
        );
        $stmt->execute([$bookingId, $this->tenantId()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchBookingCustomer(array $booking): array
    {
        return [
            'name'  => $booking['customer_name'] ?? '',
            'phone' => $booking['customer_phone'] ?? $booking['phone'] ?? '',
            'email' => $booking['customer_email'] ?? $booking['email'] ?? '',
        ];
    }

    private function letterPdfExists(array $letter): bool
    {
        $path = $letter['pdf_path'] ?? '';
        if ($path !== '' && file_exists($path)) {
            return true;
        }
        return false;
    }
}