<?php

namespace App\Services\Accounting;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use Exception;

class InvoiceService
{
    use ServiceTenantTrait;
    protected $db;

    public function __construct($pdo = null)
    {
        if ($pdo instanceof \PDO) {
            $this->db = $pdo;
        } else {
            $this->db = Database::getInstance();
            if (method_exists($this->db, 'getPdo')) {
                $this->db = $this->db->getPdo();
            }
        }
    }

    private function pdo()
    {
        if ($this->db instanceof \PDO) {
            return $this->db;
        }
        $inst = Database::getInstance();
        if (method_exists($inst, 'getPdo')) {
            return $inst->getPdo();
        }
        if (method_exists($inst, 'getConnection')) {
            return $inst->getConnection();
        }
        return $this->db;
    }

    public function generateInvoiceNumber(): string
    {
        $date = date('Ymd');
        $prefix = 'APS-INV-' . $date . '-';
        $pdo = $this->pdo();
        $stmt = $pdo->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE ?" . $this->tenantSql() . " ORDER BY id DESC LIMIT 1");
        $params = [$prefix . '%'];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($last) {
            $lastNum = (int)substr($last['invoice_number'], -4);
            $next = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }
        return $prefix . $next;
    }

    public function calculateGst(float $amount, string $placeOfSupply, string $companyState = 'Uttar Pradesh'): array
    {
        $rate = 18.0;
        $companyState = strtolower(trim($companyState));
        $pos = strtolower(trim($placeOfSupply));

        if ($pos === $companyState || $pos === '' || $pos === 'up' || $pos === 'uttar pradesh') {
            $cgst = round($amount * ($rate / 2) / 100, 2);
            $sgst = round($amount * ($rate / 2) / 100, 2);
            $igst = 0;
            $gstType = 'cgst_sgst';
        } else {
            $cgst = 0;
            $sgst = 0;
            $igst = round($amount * $rate / 100, 2);
            $gstType = 'igst';
        }

        return [
            'gst_type' => $gstType,
            'gst_rate' => $rate,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => $igst,
            'tax_amount' => $cgst + $sgst + $igst,
        ];
    }

    public function getCompanyDetails(): array
    {
        $pdo = $this->pdo();
        $details = [
            'company_name' => 'APS Dream Home',
            'gstin' => '09AAACN1234F1Z5',
            'pan' => 'AAACN1234F',
            'address' => 'Gorakhpur, Uttar Pradesh, India',
            'phone' => '+91 92771 21112',
            'email' => 'info@apsdreamhome.com',
            'state' => 'Uttar Pradesh',
            'state_code' => '09',
            'bank_name' => '',
            'bank_account' => '',
            'bank_ifsc' => '',
            'bank_branch' => '',
        ];

        try {
            $row = $pdo->query("SELECT * FROM company_credentials LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $details['gstin'] = $row['gstin'] ?? $details['gstin'];
                $details['pan'] = $row['pan'] ?? $details['pan'];
                $details['company_name'] = $row['company_name'] ?? $details['company_name'];
                $details['address'] = $row['address'] ?? $details['address'];
                $details['phone'] = $row['phone'] ?? $details['phone'];
                $details['email'] = $row['email'] ?? $details['email'];
            }
        } catch (Exception $e) {
        // graceful fallback
        error_log($e->getMessage());
        }

        try {
            $rows = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('company_name','company_address','company_phone','company_email','company_state','gst_number','pan_number','bank_name','bank_account_number','bank_ifsc','bank_branch')")->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                foreach ($rows as $r) {
                    $k = $r['key'];
                    $v = $r['value'];
                    if ($k === 'company_name' && $v) $details['company_name'] = $v;
                    if ($k === 'company_address' && $v) $details['address'] = $v;
                    if ($k === 'company_phone' && $v) $details['phone'] = $v;
                    if ($k === 'company_email' && $v) $details['email'] = $v;
                    if ($k === 'company_state' && $v) $details['state'] = $v;
                    if ($k === 'gst_number' && $v) $details['gstin'] = $v;
                    if ($k === 'pan_number' && $v) $details['pan'] = $v;
                    if ($k === 'bank_name' && $v) $details['bank_name'] = $v;
                    if ($k === 'bank_account_number' && $v) $details['bank_account'] = $v;
                    if ($k === 'bank_ifsc' && $v) $details['bank_ifsc'] = $v;
                    if ($k === 'bank_branch' && $v) $details['bank_branch'] = $v;
                }
            }
        } catch (Exception $e) {
        // graceful fallback
        error_log($e->getMessage());
        }

        return $details;
    }

    public function createInvoice(array $data): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $invoiceNumber = $this->generateInvoiceNumber();

            $subtotal = (float)($data['subtotal'] ?? 0);
            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $taxableAmount = $subtotal - $discountAmount;
            if ($taxableAmount < 0) $taxableAmount = 0;

            $placeOfSupply = $data['place_of_supply'] ?? 'Uttar Pradesh';
            $gst = $this->calculateGst($taxableAmount, $placeOfSupply);

            $totalAmount = $taxableAmount + $gst['tax_amount'];

            $insertColumns = [
                'invoice_number', 'invoice_date', 'due_date',
                'client_id', 'client_type', 'client_name', 'client_email', 'client_phone', 'client_address',
                'billing_address', 'shipping_address',
                'subtotal', 'tax_amount', 'gst_type', 'gst_rate', 'cgst_amount', 'sgst_amount', 'igst_amount',
                'gstin', 'hsn_code', 'place_of_supply', 'e_invoice_number', 'e_way_bill',
                'discount_amount', 'total_amount', 'currency',
                'status', 'payment_terms', 'notes', 'template_id', 'generated_by',
                'booking_id'
            ];
            $insertValues = [
                $invoiceNumber,
                date('Y-m-d'),
                $data['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                $data['client_id'] ?? null,
                $data['client_type'] ?? 'customer',
                $data['client_name'] ?? '',
                $data['client_email'] ?? '',
                $data['client_phone'] ?? '',
                $data['client_address'] ?? '',
                $data['billing_address'] ?? '',
                $data['shipping_address'] ?? '',
                $subtotal,
                $gst['tax_amount'],
                $gst['gst_type'],
                $gst['gst_rate'],
                $gst['cgst_amount'],
                $gst['sgst_amount'],
                $gst['igst_amount'],
                $data['gstin'] ?? '',
                $data['hsn_code'] ?? '',
                $placeOfSupply,
                $data['e_invoice_number'] ?? null,
                $data['e_way_bill'] ?? null,
                $discountAmount,
                $totalAmount,
                $data['currency'] ?? 'INR',
                $data['status'] ?? 'draft',
                $data['payment_terms'] ?? '',
                $data['notes'] ?? '',
                $data['template_id'] ?? null,
                $data['generated_by'] ?? ($_SESSION['admin_id'] ?? null),
                $data['booking_id'] ?? null,
            ];
            if ($this->tenantId() > 1) {
                $insertColumns[] = 'tenant_id';
                $insertValues[] = $this->tenantId();
            }
            $cols = implode(', ', $insertColumns);
            $phs = implode(', ', array_fill(0, count($insertValues), '?'));
            $stmt = $pdo->prepare("INSERT INTO invoices ($cols) VALUES ($phs)");
            $stmt->execute($insertValues);
            $invoiceId = (int)$pdo->lastInsertId();

            if (!empty($data['items']) && is_array($data['items'])) {
                $sortOrder = 0;
                foreach ($data['items'] as $item) {
                    $this->addLineItem($invoiceId, array_merge($item, ['sort_order' => ++$sortOrder]));
                }
            }

            $pdo->commit();
            return $invoiceId;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function createFromBooking(int $bookingId): int
    {
        $pdo = $this->pdo();

        $tid = TenantContext::getId();
        $booking = $pdo->prepare("SELECT pb.*, p.plot_no, p.colony_id, p.area_sqft, p.total_price AS plot_price,
                u.name AS client_name, u.email AS client_email, u.phone AS client_phone
            FROM plot_bookings pb
            LEFT JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN users u ON pb.customer_id = u.id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
            WHERE pb.id = ?");
        $stmt = $booking;
        $stmt->execute($tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Booking #{$bookingId} not found.");
        }

        $items = [];
        $plotPrice = (float)($row['plot_price'] ?? $row['total_plot_value'] ?? 0);

        $items[] = [
            'item_type' => 'property',
            'item_name' => 'Plot ' . ($row['plot_no'] ?? ''),
            'item_description' => 'Colony #' . ($row['colony_id'] ?? '') . ' | ' . ($row['area_sqft'] ?? 0) . ' sqft',
            'quantity' => 1,
            'unit_price' => $plotPrice,
            'discount_percent' => 0,
            'tax_percent' => 18,
        ];

        return $this->createInvoice([
            'client_id' => $row['customer_id'] ?? null,
            'client_type' => 'customer',
            'client_name' => $row['client_name'] ?? '',
            'client_email' => $row['client_email'] ?? '',
            'client_phone' => $row['client_phone'] ?? '',
            'client_address' => '',
            'place_of_supply' => 'Uttar Pradesh',
            'items' => $items,
            'booking_id' => $bookingId,
            'status' => 'draft',
            'notes' => "Auto-generated from Booking #" . ($row['booking_number'] ?? $bookingId),
        ]);
    }

    public function getInvoice(int $id): ?array
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?" . $this->tenantSql());
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$invoice) {
            return null;
        }

        $itemStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order ASC, id ASC");
        $itemStmt->execute([$id]);
        $invoice['items'] = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $invoice;
    }

    public function listInvoices(array $filters = []): array
    {
        $pdo = $this->pdo();
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "i.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(i.invoice_number LIKE ? OR i.client_name LIKE ? OR i.client_email LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "i.invoice_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "i.invoice_date <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : 'WHERE 1=1';
        $whereClause .= $this->tenantSql();
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) FROM invoices i {$whereClause}";
        $countStmt = $pdo->prepare($countSql);
        $countParams = $params;
        if ($this->tenantId() > 1) $countParams[] = $this->tenantId();
        $countStmt->execute($countParams);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT i.* FROM invoices i {$whereClause} ORDER BY i.created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        $stmt = $pdo->prepare($sql);
        $execParams = $params;
        if ($this->tenantId() > 1) $execParams[] = $this->tenantId();
        $stmt->execute($execParams);
        $invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'invoices' => $invoices,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    public function addLineItem(int $invoiceId, array $item): int
    {
        $pdo = $this->pdo();
        $quantity = (float)($item['quantity'] ?? 1);
        $unitPrice = (float)($item['unit_price'] ?? 0);
        $discountPercent = (float)($item['discount_percent'] ?? 0);
        $taxPercent = (float)($item['tax_percent'] ?? 18);

        $discountAmount = round($unitPrice * $quantity * $discountPercent / 100, 2);
        $lineTotalBeforeTax = ($unitPrice * $quantity) - $discountAmount;
        $taxAmount = round($lineTotalBeforeTax * $taxPercent / 100, 2);
        $lineTotal = $lineTotalBeforeTax + $taxAmount;

        $insertColumns = ['invoice_id', 'item_type', 'item_name', 'item_description',
            'quantity', 'unit_price', 'discount_percent', 'discount_amount',
            'tax_percent', 'tax_amount', 'line_total', 'sort_order'];
        $insertValues = [$invoiceId, $item['item_type'] ?? 'service', $item['item_name'] ?? '', $item['item_description'] ?? '',
            $quantity, $unitPrice, $discountPercent, $discountAmount,
            $taxPercent, $taxAmount, $lineTotal, $item['sort_order'] ?? 0];
        if ($this->tenantId() > 1) {
            $insertColumns[] = 'tenant_id';
            $insertValues[] = $this->tenantId();
        }
        $cols = implode(', ', $insertColumns);
        $phs = implode(', ', array_fill(0, count($insertValues), '?'));
        $stmt = $pdo->prepare("INSERT INTO invoice_items ($cols) VALUES ($phs)");

        $stmt->execute($insertValues);

        return (int)$pdo->lastInsertId();
    }

    public function recalculateInvoice(int $invoiceId): void
    {
        $pdo = $this->pdo();

        $itemStmt = $pdo->prepare("SELECT SUM(line_total) AS items_total, SUM(discount_amount) AS item_discounts, SUM(tax_amount) AS item_taxes FROM invoice_items WHERE invoice_id = ?");
        $itemStmt->execute([$invoiceId]);
        $totals = $itemStmt->fetch(\PDO::FETCH_ASSOC);

        $subtotal = (float)($totals['items_total'] ?? 0);
        $taxAmount = (float)($totals['item_taxes'] ?? 0);
        $totalAmount = $subtotal;

        $invStmt = $pdo->prepare("SELECT discount_amount FROM invoices WHERE id = ?" . $this->tenantSql());
        $params = [$invoiceId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $invStmt->execute($params);
        $inv = $invStmt->fetch(\PDO::FETCH_ASSOC);
        $invDiscount = (float)($inv['discount_amount'] ?? 0);

        $totalAmount = $subtotal;

        $sql = "UPDATE invoices SET subtotal = ?, tax_amount = ?, total_amount = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql();
        $params = [$subtotal, $taxAmount, $totalAmount, $invoiceId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $pdo->prepare($sql)->execute($params);
    }

    public function updateStatus(int $invoiceId, string $status): bool
    {
        $pdo = $this->pdo();
        $allowed = ['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $sql = "UPDATE invoices SET status = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql();
        $params = [$status, $invoiceId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function markAsPaid(int $invoiceId): bool
    {
        $pdo = $this->pdo();
        $sql = "UPDATE invoices SET status = 'paid', paid_at = NOW(), updated_at = NOW() WHERE id = ?" . $this->tenantSql();
        $params = [$invoiceId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function markAsSent(int $invoiceId): bool
    {
        $pdo = $this->pdo();
        $sql = "UPDATE invoices SET status = 'sent', sent_at = NOW(), updated_at = NOW() WHERE id = ?" . $this->tenantSql();
        $params = [$invoiceId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function deleteInvoice(int $invoiceId): bool
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $sql = "DELETE FROM invoice_items WHERE invoice_id = ? AND invoice_id IN (SELECT id FROM invoices" . $this->tenantSql() . ")";
            $params = [$invoiceId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $pdo->prepare($sql)->execute($params);
            $sql = "DELETE FROM invoices WHERE id = ?" . $this->tenantSql();
            $params = [$invoiceId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getStats(): array
    {
        $pdo = $this->pdo();
        $stats = [
            'total_count' => 0,
            'total_amount' => 0,
            'paid_count' => 0,
            'paid_amount' => 0,
            'pending_count' => 0,
            'pending_amount' => 0,
            'overdue_count' => 0,
            'overdue_amount' => 0,
            'draft_count' => 0,
            'draft_amount' => 0,
        ];

        try {
            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
            $stmt = $pdo->prepare("SELECT
                COUNT(*) AS total_count,
                COALESCE(SUM(total_amount), 0) AS total_amount,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) AS paid_amount,
                SUM(CASE WHEN status IN ('sent','viewed') THEN 1 ELSE 0 END) AS pending_count,
                COALESCE(SUM(CASE WHEN status IN ('sent','viewed') THEN total_amount ELSE 0 END), 0) AS pending_amount,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) AS overdue_count,
                COALESCE(SUM(CASE WHEN status = 'overdue' THEN total_amount ELSE 0 END), 0) AS overdue_amount,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_count,
                COALESCE(SUM(CASE WHEN status = 'draft' THEN total_amount ELSE 0 END), 0) AS draft_amount
            FROM invoices" . $tenantWhere);
            $stmt->execute($tid > 1 ? [$tid] : []);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                $stats = array_merge($stats, $row);
                foreach (['total_amount', 'paid_amount', 'pending_amount', 'overdue_amount', 'draft_amount'] as $k) {
                    $stats[$k] = (float)$stats[$k];
                }
                foreach (['total_count', 'paid_count', 'pending_count', 'overdue_count', 'draft_count'] as $k) {
                    $stats[$k] = (int)$stats[$k];
                }
            }
        } catch (Exception $e) {
        // graceful fallback to zeros
        error_log($e->getMessage());
        }

        return $stats;
    }

    public function getUsers(): array
    {
        $pdo = $this->pdo();
        try {
            $tid = TenantContext::getId();
            $sql = "SELECT id, name, email, phone, role FROM users WHERE role IN ('customer','associate','agent','employee')";
            $params = [];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $sql .= " ORDER BY name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getBookings(): array
    {
        $pdo = $this->pdo();
        try {
            $tid = TenantContext::getId();
            $sql = "SELECT pb.id, pb.booking_number, pb.total_plot_value, pb.status,
                    p.plot_no, u.name AS client_name
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN users u ON pb.customer_id = u.id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                ORDER BY pb.created_at DESC LIMIT 200";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($tid > 1 ? [$tid] : []);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
