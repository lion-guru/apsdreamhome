<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;

class GstComplianceService
{
    use TenantAwareTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function calculateGst(array $params): array
    {
        $taxableValue = (float)($params['taxable_value'] ?? 0);
        $gstRate = (float)($params['gst_rate'] ?? 0);
        $supplyType = $params['supply_type'] ?? 'intra_state';

        $cgst = 0;
        $sgst = 0;
        $igst = 0;

        if ($supplyType === 'inter_state') {
            $igst = $taxableValue * ($gstRate / 100);
        } else {
            $cgst = $taxableValue * ($gstRate / 200);
            $sgst = $taxableValue * ($gstRate / 200);
        }

        $cess = 0;
        if (!empty($params['cess_rate'])) {
            $cess = $taxableValue * ((float)$params['cess_rate'] / 100);
        }

        return [
            'taxable_value' => round($taxableValue, 2),
            'cgst_rate' => $supplyType === 'inter_state' ? 0 : round($gstRate / 2, 2),
            'sgst_rate' => $supplyType === 'inter_state' ? 0 : round($gstRate / 2, 2),
            'igst_rate' => $supplyType === 'inter_state' ? $gstRate : 0,
            'cess_rate' => (float)($params['cess_rate'] ?? 0),
            'cgst_amount' => round($cgst, 2),
            'sgst_amount' => round($sgst, 2),
            'igst_amount' => round($igst, 2),
            'cess_amount' => round($cess, 2),
            'total_tax' => round($cgst + $sgst + $igst + $cess, 2),
            'total_amount' => round($taxableValue + $cgst + $sgst + $igst + $cess, 2),
        ];
    }

    public function getHsnCode(string $hsnCode): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM gst_hsn_codes WHERE hsn_code = ? AND tenant_id = ? AND is_active = 1", [$hsnCode, $this->tenantId()]);
        return $row ?: null;
    }

    public function searchHsnCodes(string $query, int $limit = 20): array
    {
        return $this->db->fetchAll("
            SELECT * FROM gst_hsn_codes 
            WHERE tenant_id = ? AND is_active = 1 
            AND (hsn_code LIKE ? OR description LIKE ?)
            ORDER BY hsn_code LIMIT ?
        ", [$this->tenantId(), "%$query%", "%$query%", $limit]);
    }

    public function createInvoice(array $data): int
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $totals = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'cess' => 0];
        foreach ($items as $item) {
            $calc = $this->calculateGst([
                'taxable_value' => ($item['rate'] * $item['quantity']) - ($item['discount_amount'] ?? 0),
                'gst_rate' => $item['gst_rate'],
                'supply_type' => $data['supply_type'],
                'cess_rate' => $item['cess_rate'] ?? 0,
            ]);
            $totals['taxable'] += $calc['taxable_value'];
            $totals['cgst'] += $calc['cgst_amount'];
            $totals['sgst'] += $calc['sgst_amount'];
            $totals['igst'] += $calc['igst_amount'];
            $totals['cess'] += $calc['cess_amount'];
        }

        $data['taxable_value'] = $totals['taxable'];
        $data['cgst_amount'] = $totals['cgst'];
        $data['sgst_amount'] = $totals['sgst'];
        $data['igst_amount'] = $totals['igst'];
        $data['cess_amount'] = $totals['cess'];
        $data['total_tax'] = $totals['cgst'] + $totals['sgst'] + $totals['igst'] + $totals['cess'];
        $data['total_amount'] = $totals['taxable'] + $data['total_tax'];
        $data['tenant_id'] = $this->tenantId();
        $data['created_by'] = $data['created_by'] ?? ($_SESSION['admin_id'] ?? 1);

        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $values = array_values($data);

        $this->db->execute(
            "INSERT INTO gst_invoices (" . implode(',', $columns) . ") VALUES ($placeholders)",
            $values
        );
        $invoiceId = (int)$this->db->lastInsertId();

        foreach ($items as $index => $item) {
            $taxable = ($item['rate'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $calc = $this->calculateGst([
                'taxable_value' => $taxable,
                'gst_rate' => $item['gst_rate'],
                'supply_type' => $data['supply_type'],
                'cess_rate' => $item['cess_rate'] ?? 0,
            ]);

            $this->db->execute("
                INSERT INTO gst_invoice_items 
                (tenant_id, invoice_id, line_number, hsn_code, description, uom, quantity, rate, taxable_value, 
                discount_amount, gst_rate, cgst_rate, sgst_rate, igst_rate, cess_rate,
                cgst_amount, sgst_amount, igst_amount, cess_amount, total_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $this->tenantId(), $invoiceId, $index + 1,
                $item['hsn_code'], $item['description'], $item['uom'] ?? 'PCS',
                $item['quantity'], $item['rate'], $taxable, $item['discount_amount'] ?? 0,
                $item['gst_rate'], $calc['cgst_rate'], $calc['sgst_rate'], $calc['igst_rate'], $calc['cess_rate'],
                $calc['cgst_amount'], $calc['sgst_amount'], $calc['igst_amount'], $calc['cess_amount'],
                $calc['total_amount']
            ]);
        }

        return $invoiceId;
    }

    public function getInvoice(int $id): ?array
    {
        $invoice = $this->db->fetchOne("SELECT * FROM gst_invoices WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        if (!$invoice) return null;

        $invoice['items'] = $this->db->fetchAll("
            SELECT * FROM gst_invoice_items WHERE invoice_id = ? AND tenant_id = ? ORDER BY line_number
        ", [$id, $this->tenantId()]);

        return $invoice;
    }

    public function validateInvoiceForEInvoicing(int $invoiceId): array
    {
        $invoice = $this->getInvoice($invoiceId);
        if (!$invoice) return ['valid' => false, 'errors' => ['Invoice not found']];

        $errors = [];
        if ($invoice['invoice_type'] !== 'B2B') $errors[] = 'E-invoicing only applicable for B2B invoices';
        if (empty($invoice['customer_gstin'])) $errors[] = 'Customer GSTIN is mandatory';
        if (strlen($invoice['customer_gstin']) !== 15) $errors[] = 'Invalid GSTIN format';
        if ($invoice['total_amount'] < 200) $errors[] = 'Invoice value must be at least ₹200 for e-invoicing';
        if (empty($invoice['hsn_code']) && empty($invoice['items'][0]['hsn_code'])) $errors[] = 'HSN code is mandatory';

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    public function generateIrn(int $invoiceId): ?string
    {
        $validation = $this->validateInvoiceForEInvoicing($invoiceId);
        if (!$validation['valid']) return null;

        $invoice = $this->getInvoice($invoiceId);
        $irn = 'IRN' . date('Ymd') . str_pad($invoiceId, 8, '0', STR_PAD_LEFT);

        $this->db->execute("
            UPDATE gst_invoices SET 
                irn = ?, irn_date = NOW(), einvoice_status = 'generated',
                einvoice_error = NULL
            WHERE id = ?
        ", [$irn, $invoiceId]);

        $this->logEinvoiceAttempt($invoiceId, 'success', $irn);
        return $irn;
    }

    private function logEinvoiceAttempt(int $invoiceId, string $status, ?string $irn = null, ?string $error = null): void
    {
        $invoice = $this->getInvoice($invoiceId);
        $this->db->execute("
            INSERT INTO gst_einvoicing_log (tenant_id, invoice_id, request_payload, response_payload, irn, status, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [
            $this->tenantId(),
            $invoiceId,
            json_encode(['invoice' => $invoice]),
            json_encode(['irn' => $irn, 'status' => $status]),
            $irn,
            $status,
            $error
        ]);
    }

    public function prepareGstr1Data(string $period): array
    {
        $invoices = $this->db->fetchAll("
            SELECT * FROM gst_invoices 
            WHERE tenant_id = ? AND DATE_FORMAT(invoice_date, '%Y-%m') = ? 
            AND status IN ('validated', 'draft')
            ORDER BY invoice_date
        ", [$this->tenantId(), $period]);

        $summary = [
            'B2B' => [], 'B2C' => [], 'B2CL' => [], 'EXP' => [], 'SEZ' => [], 'DE' => [],
            'CDNR' => [], 'CDNUR' => [], 'HSN' => [], 'NIL' => [], 'ADV' => [], 'ADJ' => []
        ];

        foreach ($invoices as $inv) {
            $section = $inv['invoice_type'];
            if (!isset($summary[$section])) $section = 'B2B';

            $summary[$section][] = [
                'invoice_id' => $inv['id'],
                'customer_gstin' => $inv['customer_gstin'],
                'invoice_number' => $inv['invoice_number'],
                'invoice_date' => $inv['invoice_date'],
                'invoice_value' => $inv['total_amount'],
                'taxable_value' => $inv['taxable_value'],
                'cgst_amount' => $inv['cgst_amount'],
                'sgst_amount' => $inv['sgst_amount'],
                'igst_amount' => $inv['igst_amount'],
                'cess_amount' => $inv['cess_amount'],
                'place_of_supply' => $inv['place_of_supply'],
                'supply_type' => $inv['supply_type'],
                'reverse_charge' => $inv['reverse_charge'],
            ];
        }

        $hsnSummary = $this->db->fetchAll("
            SELECT gii.hsn_code, gii.uom, SUM(gii.quantity) as total_qty,
                   SUM(gii.taxable_value) as taxable_value,
                   SUM(gii.cgst_amount) as cgst,
                   SUM(gii.sgst_amount) as sgst,
                   SUM(gii.igst_amount) as igst,
                   SUM(gii.cess_amount) as cess
            FROM gst_invoice_items gii
            JOIN gst_invoices gi ON gi.id = gii.invoice_id
            WHERE gii.tenant_id = ? AND DATE_FORMAT(gi.invoice_date, '%Y-%m') = ?
            GROUP BY gii.hsn_code, gii.uom
        ", [$this->tenantId(), $period]);

        $summary['HSN'] = $hsnSummary;
        return $summary;
    }

    public function saveGstr1Summary(string $period, array $data): void
    {
        foreach ($data as $section => $records) {
            if (!is_array($records)) continue;
            foreach ($records as $rec) {
                $this->db->execute("
                    INSERT INTO gst_gstr1_summary 
                    (tenant_id, return_period, section, invoice_id, customer_gstin, invoice_number, invoice_date,
                     invoice_value, taxable_value, cgst_amount, sgst_amount, igst_amount, cess_amount,
                     place_of_supply, supply_type, reverse_charge, hsn_code, uqc, total_quantity)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    invoice_value = VALUES(invoice_value), taxable_value = VALUES(taxable_value),
                    cgst_amount = VALUES(cgst_amount), sgst_amount = VALUES(sgst_amount),
                    igst_amount = VALUES(igst_amount), cess_amount = VALUES(cess_amount)
                ", [
                    $this->tenantId(), $period, $section,
                    $rec['invoice_id'] ?? null, $rec['customer_gstin'] ?? null, $rec['invoice_number'] ?? null,
                    $rec['invoice_date'] ?? null, $rec['invoice_value'] ?? 0, $rec['taxable_value'] ?? 0,
                    $rec['cgst_amount'] ?? 0, $rec['sgst_amount'] ?? 0, $rec['igst_amount'] ?? 0, $rec['cess_amount'] ?? 0,
                    $rec['place_of_supply'] ?? null, $rec['supply_type'] ?? null, $rec['reverse_charge'] ?? 0,
                    $rec['hsn_code'] ?? null, $rec['uqc'] ?? null, $rec['total_quantity'] ?? 0
                ]);
            }
        }
    }

    public function prepareGstr3bData(string $period): array
    {
        $outward = $this->db->fetchOne("
            SELECT 
                SUM(taxable_value) as taxable, SUM(igst_amount) as igst,
                SUM(cgst_amount) as cgst, SUM(sgst_amount) as sgst,
                SUM(cess_amount) as cess
            FROM gst_invoices 
            WHERE tenant_id = ? AND DATE_FORMAT(invoice_date, '%Y-%m') = ?
            AND status IN ('validated', 'draft')
        ", [$this->tenantId(), $period]);

        $inward = ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0];
        $itcAvailable = ['igst' => 0, 'cgst' => 0, 'sgst' => 0];
        
        // Try to fetch from purchase invoices table if it exists
        try {
            $tableExists = $this->db->fetchOne("SHOW TABLES LIKE 'gst_purchase_invoices'");
            if ($tableExists) {
                $inward = $this->db->fetchOne("
                    SELECT SUM(taxable_value) as taxable, SUM(igst_amount) as igst,
                           SUM(cgst_amount) as cgst, SUM(sgst_amount) as sgst
                    FROM gst_purchase_invoices 
                    WHERE tenant_id = ? AND DATE_FORMAT(invoice_date, '%Y-%m') = ?
                ", [$this->tenantId(), $period]) ?? $inward;

                $itcAvailable = $this->db->fetchOne("
                    SELECT SUM(igst_amount) as igst, SUM(cgst_amount) as cgst, SUM(sgst_amount) as sgst
                    FROM gst_purchase_invoices 
                    WHERE tenant_id = ? AND DATE_FORMAT(invoice_date, '%Y-%m') = ?
                    AND itc_eligible = 1
                ", [$this->tenantId(), $period]) ?? $itcAvailable;
            }
        } catch (\Throwable $e) {
            // Table doesn't exist, use defaults
        }

        $outwardTax = ($outward['igst'] ?? 0) + ($outward['cgst'] ?? 0) + ($outward['sgst'] ?? 0) + ($outward['cess'] ?? 0);
        $itcClaimed = ($itcAvailable['igst'] ?? 0) + ($itcAvailable['cgst'] ?? 0) + ($itcAvailable['sgst'] ?? 0);
        $netLiability = max(0, $outwardTax - $itcClaimed);

        return [
            '3.1' => ['taxable' => $outward['taxable'] ?? 0, 'igst' => $outward['igst'] ?? 0, 'cgst' => $outward['cgst'] ?? 0, 'sgst' => $outward['sgst'] ?? 0, 'cess' => $outward['cess'] ?? 0],
            '3.2' => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0, 'cess' => 0],
            '4'   => ['igst' => $itcAvailable['igst'] ?? 0, 'cgst' => $itcAvailable['cgst'] ?? 0, 'sgst' => $itcAvailable['sgst'] ?? 0],
            '5'   => ['tax_liability' => $netLiability, 'tax_paid_cash' => 0, 'tax_paid_itc' => $itcClaimed, 'interest' => 0, 'late_fee' => 0],
        ];
    }

    public function saveGstr3bSummary(string $period, array $data): void
    {
        foreach ($data as $section => $values) {
            $this->db->execute("
                INSERT INTO gst_gstr3b_summary 
                (tenant_id, return_period, table_section, taxable_value, igst_amount, cgst_amount, sgst_amount, cess_amount,
                 itc_claimed, itc_reversed, net_itc, tax_liability, tax_paid_cash, tax_paid_itc, interest, late_fee, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'computed')
                ON DUPLICATE KEY UPDATE
                taxable_value = VALUES(taxable_value), igst_amount = VALUES(igst_amount),
                cgst_amount = VALUES(cgst_amount), sgst_amount = VALUES(sgst_amount),
                cess_amount = VALUES(cess_amount), itc_claimed = VALUES(itc_claimed),
                itc_reversed = VALUES(itc_reversed), net_itc = VALUES(net_itc),
                tax_liability = VALUES(tax_liability), tax_paid_cash = VALUES(tax_paid_cash),
                tax_paid_itc = VALUES(tax_paid_itc), interest = VALUES(interest),
                late_fee = VALUES(late_fee), status = 'computed'
            ", [
                $this->tenantId(), $period, $section,
                $values['taxable'] ?? 0, $values['igst'] ?? 0, $values['cgst'] ?? 0, $values['sgst'] ?? 0, $values['cess'] ?? 0,
                $values['itc_claimed'] ?? 0, $values['itc_reversed'] ?? 0, $values['net_itc'] ?? 0,
                $values['tax_liability'] ?? 0, $values['tax_paid_cash'] ?? 0, $values['tax_paid_itc'] ?? 0,
                $values['interest'] ?? 0, $values['late_fee'] ?? 0
            ]);
        }
    }

    public function getHsnCodes(int $limit = 100, int $offset = 0): array
    {
        return $this->db->fetchAll("
            SELECT * FROM gst_hsn_codes WHERE tenant_id = ? ORDER BY hsn_code LIMIT ? OFFSET ?
        ", [$this->tenantId(), $limit, $offset]);
    }

    public function createHsnCode(array $data): int
    {
        $data['tenant_id'] = $this->tenantId();
        $data['created_by'] = $data['created_by'] ?? ($_SESSION['admin_id'] ?? 1);
        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $values = array_values($data);
        $this->db->execute("INSERT INTO gst_hsn_codes (" . implode(',', $columns) . ") VALUES ($placeholders)", $values);
        return (int)$this->db->lastInsertId();
    }

    public function updateHsnCode(int $id, array $data): bool
    {
        $set = []; $vals = [];
        foreach ($data as $k => $v) {
            if (in_array($k, ['id', 'tenant_id', 'created_at', 'created_by'])) continue;
            $set[] = "$k = ?"; $vals[] = $v;
        }
        if (!$set) return false;
        $vals[] = $id; $vals[] = $this->tenantId();
        $this->db->execute("UPDATE gst_hsn_codes SET " . implode(',', $set) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?", $vals);
        return true;
    }

    public function getInvoiceList(array $filters = []): array
    {
        $where = ["gi.tenant_id = ?"]; $params = [$this->tenantId()];
        if (!empty($filters['from_date'])) { $where[] = "gi.invoice_date >= ?"; $params[] = $filters['from_date']; }
        if (!empty($filters['to_date'])) { $where[] = "gi.invoice_date <= ?"; $params[] = $filters['to_date']; }
        if (!empty($filters['type'])) { $where[] = "gi.invoice_type = ?"; $params[] = $filters['type']; }
        if (!empty($filters['status'])) { $where[] = "gi.status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['gstin'])) { $where[] = "gi.customer_gstin = ?"; $params[] = $filters['gstin']; }
        if (!empty($filters['irn_status'])) { $where[] = "gi.einvoice_status = ?"; $params[] = $filters['irn_status']; }

        return $this->db->fetchAll("
            SELECT gi.* FROM gst_invoices gi
            WHERE " . implode(' AND ', $where) . "
            ORDER BY gi.invoice_date DESC, gi.id DESC
            LIMIT 100
        ", $params);
    }
}