<?php

namespace App\Services\Accounting;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Money Workflow Service
 *
 * Implements the full accounting / treasury / tax compliance stack:
 *   - Bank accounts (KYC + RERA escrow)
 *   - Daily cash book with auto-journal
 *   - Petty cash (topup + expense) with running balance
 *   - Cheque / DD register (issue / clear / bounce)
 *   - Bank reconciliation (statement vs book)
 *   - TDS register (auto-calc, deposit, Form 16A)
 *   - GST transactions (output / input + ITC reconciliation)
 *   - Demand letter templates ({{var}} substitution)
 *   - Cash flow forecast (inflow / outflow with probability)
 *   - Multi-level expense approval workflow
 *   - Vendor payments (TDS + GST aware)
 *   - Double-entry journal entries
 *   - Reports: Trial Balance, P&L, Balance Sheet, Cash Flow, 3-way Recon
 */
class MoneyWorkflowService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================================
    //  BANK ACCOUNTS MASTER
    // ============================================================

    public function createBankAccount(array $data): int
    {
        $payload = [
            'account_name'         => trim($data['account_name'] ?? ''),
            'account_number'       => trim($data['account_number'] ?? ''),
            'ifsc_code'            => strtoupper(trim($data['ifsc_code'] ?? '')),
            'bank_name'            => trim($data['bank_name'] ?? ''),
            'branch'               => $data['branch'] ?? null,
            'account_type'         => $data['account_type'] ?? 'current',
            'opening_balance'      => (float)($data['opening_balance'] ?? 0),
            'current_balance'      => (float)($data['opening_balance'] ?? 0),
            'is_escrow'            => !empty($data['is_escrow']) ? 1 : 0,
            'rera_project_id'      => !empty($data['rera_project_id']) ? (int)$data['rera_project_id'] : null,
            'gst_registered'       => !empty($data['gst_registered']) ? 1 : 0,
            'signatory_name'       => $data['signatory_name'] ?? null,
            'signatory_pan'        => strtoupper($data['signatory_pan'] ?? ''),
            'cancelled_cheque_path'=> $data['cancelled_cheque_path'] ?? null,
            'active'               => isset($data['active']) ? (int)$data['active'] : 1,
            'tenant_id'            => TenantContext::getId(),
        ];
        $this->db->insert('bank_accounts_master', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function listBankAccounts(bool $activeOnly = true): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM bank_accounts_master WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "") . ($activeOnly ? " AND active = 1" : "") . " ORDER BY account_name";
        return $this->db->fetchAll($sql, $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getBankBalance(int $bankAccountId, ?string $asOfDate = null): float
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        $bank = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$bankAccountId, $tid] : [$bankAccountId]);
        if (!$bank) {
            return 0.0;
        }

        // Inflows since as-of date are not yet "available" if the as-of date is in the past
        // but for simplicity we report the stored current_balance
        return (float)$bank['current_balance'];
    }

    // ============================================================
    //  CASH BOOK (auto-creates journal entry)
    // ============================================================

    public function recordCashTransaction(array $data): array
    {
        $type     = $data['transaction_type'] ?? 'receipt';
        $amount   = (float)($data['amount'] ?? 0);
        $bankId   = !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null;
        $party    = $data['party_name'] ?? null;
        $narr     = $data['narration'] ?? '';
        $txnDate  = $data['transaction_date'] ?? date('Y-m-d');
        $mode     = $data['payment_mode'] ?? 'cash';

        if ($amount <= 0) {
            throw new Exception('Amount must be > 0');
        }

        $this->db->beginTransaction();
        try {
            // Voucher number
            $voucherNumber = $this->generateVoucherNumber($type === 'receipt' ? 'receipt' : 'payment');

            // Insert cash book entry
            $cbId = (int)$this->db->insert('daily_cash_book', [
                'transaction_date' => $txnDate,
                'transaction_type' => $type,
                'reference_type'   => $data['reference_type'] ?? null,
                'reference_id'     => !empty($data['reference_id']) ? (int)$data['reference_id'] : null,
                'party_name'       => $party,
                'party_ledger'     => $data['party_ledger'] ?? null,
                'amount'           => $amount,
                'payment_mode'     => $mode,
                'narration'        => $narr,
                'voucher_number'   => $voucherNumber,
                'bank_account_id'  => $bankId,
                'recorded_by'      => $data['recorded_by'] ?? ($_SESSION['admin_id'] ?? null),
                'approved_by'      => $data['approved_by'] ?? null,
            'tenant_id' => TenantContext::getId(),
            ]);

            // Voucher log
            $this->db->insert('payment_voucher_log', [
                'voucher_type'   => $type === 'receipt' ? 'receipt' : 'payment',
                'voucher_number' => $voucherNumber,
                'voucher_date'   => $txnDate,
                'amount'         => $amount,
                'narration'      => $narr,
                'generated_for'  => 'cash_book',
                'reference_id'   => $cbId,
                'created_by'     => $data['recorded_by'] ?? ($_SESSION['admin_id'] ?? null),
            'tenant_id' => TenantContext::getId(),
            ]);

            // Auto-create journal entry: Dr/Cr pair on bank or cash
            $cashAccount = $this->getAccountByCode($mode === 'cash' ? '1210' : '1200'); // 1210 Cash in Hand / 1200 Bank
            $contraAccount = $type === 'receipt'
                ? $this->getAccountByCode('4100')   // Plot Sales Revenue
                : $this->getAccountByCode('5100');  // Cost of Land Sold / Expense

            if ($cashAccount && $contraAccount) {
                $lines = $type === 'receipt'
                    ? [
                        ['account_id' => $cashAccount['id'],     'debit' => $amount, 'credit' => 0, 'desc' => $narr ?: 'Cash/Bank receipt'],
                        ['account_id' => $contraAccount['id'],   'debit' => 0,       'credit' => $amount, 'desc' => $narr ?: 'Receipt from ' . $party],
                    ]
                    : [
                        ['account_id' => $contraAccount['id'],   'debit' => $amount, 'credit' => 0, 'desc' => $narr ?: 'Cash/Bank payment'],
                        ['account_id' => $cashAccount['id'],     'debit' => 0,       'credit' => $amount, 'desc' => $narr ?: 'Paid to ' . $party],
                    ];

                $this->postJournalEntry([
                    'entry_date'      => $txnDate,
                    'description'     => $narr ?: ucfirst($type) . ' via cash book',
                    'source_document' => $type === 'receipt' ? 'payment' : 'expense',
                    'source_id'       => $cbId,
                    'lines'           => $lines,
                    'auto_approve'    => true,
                    'created_by'      => $data['recorded_by'] ?? ($_SESSION['admin_id'] ?? null),
                ]);
            }

            // Update bank balance
            if ($bankId) {
                $delta = $type === 'receipt' ? $amount : -$amount;
                $this->db->execute(
                    "UPDATE bank_accounts_master SET current_balance = current_balance + ? WHERE id = ?",
                    [$delta, $bankId]
                );
            }

            $this->db->commit();

            return [
                'success'         => true,
                'cash_book_id'    => $cbId,
                'voucher_number'  => $voucherNumber,
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================================
    //  PETTY CASH
    // ============================================================

    public function topupPettyCash(float $amount, array $data = []): int
    {
        if ($amount <= 0) {
            throw new Exception('Topup amount must be > 0');
        }
        $lastBalance = $this->getPettyCashBalance();
        $newBalance  = $lastBalance + $amount;

        return (int)$this->db->insert('petty_cash', [
            'transaction_date' => $data['transaction_date'] ?? date('Y-m-d'),
            'transaction_type' => 'topup',
            'category'         => $data['category'] ?? 'misc',
            'amount'           => $amount,
            'receipt_number'   => $data['receipt_number'] ?? null,
            'narration'        => $data['narration'] ?? 'Petty cash topup',
            'balance_after'    => $newBalance,
            'custodian_id'     => $data['custodian_id'] ?? null,
            'approved_by'      => $data['approved_by'] ?? null,
        'tenant_id' => TenantContext::getId(),
        ]);
    }

    public function recordPettyExpense(array $data): int
    {
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new Exception('Expense amount must be > 0');
        }
        $lastBalance = $this->getPettyCashBalance();
        $newBalance  = $lastBalance - $amount;
        if ($newBalance < 0) {
            throw new Exception('Insufficient petty cash balance');
        }

        return (int)$this->db->insert('petty_cash', [
            'transaction_date' => $data['transaction_date'] ?? date('Y-m-d'),
            'transaction_type' => 'expense',
            'category'         => $data['category'] ?? 'misc',
            'amount'           => $amount,
            'receipt_number'   => $data['receipt_number'] ?? null,
            'narration'        => $data['narration'] ?? '',
            'balance_after'    => $newBalance,
            'custodian_id'     => $data['custodian_id'] ?? null,
            'approved_by'      => $data['approved_by'] ?? null,
        'tenant_id' => TenantContext::getId(),
        ]);
    }

    public function getPettyCashBalance(): float
    {
        $tid = TenantContext::getId();
        $row = $this->db->fetchOne("SELECT balance_after FROM petty_cash" . ($tid > 1 ? " WHERE tenant_id = ?" : "") . " ORDER BY id DESC LIMIT 1", $tid > 1 ? [$tid] : []);
        return (float)($row['balance_after'] ?? 0);
    }

    // ============================================================
    //  CHEQUE REGISTER
    // ============================================================

    public function issueCheque(array $data): int
    {
        return (int)$this->db->insert('cheque_register', [
            'bank_account_id'  => (int)$data['bank_account_id'],
            'cheque_number'    => trim($data['cheque_number']),
            'cheque_date'      => $data['cheque_date'],
            'amount'           => (float)$data['amount'],
            'payee_name'       => $data['payee_name'],
            'purpose'          => $data['purpose'] ?? null,
            'status'           => 'issued',
            'voucher_id'       => $data['voucher_id'] ?? null,
        'tenant_id' => TenantContext::getId(),
        ]);
    }

    public function markChequeCleared(int $id, string $date): bool
    {
        $this->db->execute(
            "UPDATE cheque_register SET status = 'cleared', clearance_date = ? WHERE id = ?",
            [$date, $id]
        );
        return true;
    }

    public function markChequeBounced(int $id, string $reason): bool
    {
        $this->db->execute(
            "UPDATE cheque_register SET status = 'bounced', bounce_reason = ? WHERE id = ?",
            [$reason, $id]
        );
        $this->db->insert('cheque_bounce_log', [
            'cheque_id'    => $id,
            'bounce_date'  => date('Y-m-d'),
            'bounce_reason'=> $reason,
            'recovery_status' => 'pending',
        'tenant_id' => TenantContext::getId(),
        ]);
        return true;
    }

    // ============================================================
    //  BANK RECONCILIATION
    // ============================================================

    public function startBankReconciliation(int $bankAccountId, array $data): int
    {
        return (int)$this->db->insert('bank_reconciliation', [
            'bank_account_id'   => $bankAccountId,
            'statement_date'    => $data['statement_date'],
            'statement_balance' => (float)$data['statement_balance'],
            'book_balance'      => (float)$data['book_balance'],
            'reconciled_by'     => $data['reconciled_by'] ?? null,
            'status'            => 'in_progress',
            'notes'             => $data['notes'] ?? null,
        'tenant_id' => TenantContext::getId(),
        ]);
    }

    public function reconcileItem(int $id, string $status): bool
    {
        $this->db->execute(
            "UPDATE bank_reconciliation_items SET status = ? WHERE id = ?",
            [$status, $id]
        );
        return true;
    }

    // ============================================================
    //  TDS REGISTER
    // ============================================================

    public function recordTDS(array $data): int
    {
        $gross   = (float)($data['gross_amount'] ?? 0);
        $rate    = (float)($data['tds_rate'] ?? 0);
        $tdsAmt  = round($gross * $rate / 100, 2);
        $surcharge = (float)($data['surcharge'] ?? 0);
        $cess      = (float)($data['cess'] ?? 0);

        $this->db->beginTransaction();
        try {
            $tdsId = (int)$this->db->insert('tds_register', [
                'tds_section'     => $data['tds_section'],
                'deductor_pan'    => strtoupper($data['deductor_pan'] ?? ''),
                'deductor_name'   => $data['deductor_name'] ?? null,
                'deductee_pan'    => strtoupper($data['deductee_pan']),
                'deductee_name'   => $data['deductee_name'],
                'transaction_date'=> $data['transaction_date'],
                'transaction_ref' => $data['transaction_ref'] ?? null,
                'gross_amount'    => $gross,
                'tds_rate'        => $rate,
                'tds_amount'      => $tdsAmt,
                'surcharge'       => $surcharge,
                'cess'            => $cess,
                'deposit_challan' => $data['deposit_challan'] ?? null,
                'bsr_code'        => $data['bsr_code'] ?? null,
                'deposit_date'    => $data['deposit_date'] ?? null,
                'return_period'   => $data['return_period'] ?? null,
                'status'          => $data['status'] ?? 'pending',
            'tenant_id' => TenantContext::getId(),
            ]);

            // Auto-post journal: Dr Expense / Cr TDS Payable
            $expenseAccount = $this->getAccountByCode($this->mapSectionToExpense($data['tds_section']));
            $tdsPayable     = $this->getAccountByCode('2110');
            if ($expenseAccount && $tdsPayable) {
                $this->postJournalEntry([
                    'entry_date'      => $data['transaction_date'],
                    'description'     => 'TDS u/s ' . $data['tds_section'] . ' on ' . ($data['transaction_ref'] ?? 'transaction'),
                    'source_document' => 'adjustment',
                    'source_id'       => $tdsId,
                    'lines'           => [
                        ['account_id' => $expenseAccount['id'], 'debit' => $gross, 'credit' => 0, 'desc' => 'Gross expense ' . $data['tds_section']],
                        ['account_id' => $tdsPayable['id'],     'debit' => 0, 'credit' => $tdsAmt + $surcharge + $cess, 'desc' => 'TDS payable u/s ' . $data['tds_section']],
                    ],
                    'auto_approve'    => true,
                    'created_by'      => $data['created_by'] ?? ($_SESSION['admin_id'] ?? null),
                ]);
            }

            $this->db->commit();
            return $tdsId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================================
    //  GST
    // ============================================================

    public function recordGST(array $data): int
    {
        $taxable = (float)($data['taxable_value'] ?? 0);
        $rate    = (float)($data['gst_rate'] ?? 0);
        $isInterState = !empty($data['igst_amount']) || (!empty($data['party_state']) && strtoupper($data['party_state']) !== strtoupper($data['our_state'] ?? ''));

        $igst = 0.0; $cgst = 0.0; $sgst = 0.0;
        if ($isInterState) {
            $igst = round($taxable * $rate / 100, 2);
        } else {
            $cgst = round($taxable * $rate / 200, 2);
            $sgst = round($taxable * $rate / 200, 2);
        }

        return (int)$this->db->insert('gst_transactions', [
            'transaction_date' => $data['transaction_date'],
            'transaction_type' => $data['transaction_type'] ?? 'output',
            'party_gstin'      => strtoupper($data['party_gstin'] ?? ''),
            'party_name'       => $data['party_name'],
            'invoice_number'   => $data['invoice_number'] ?? null,
            'invoice_date'     => $data['invoice_date'] ?? null,
            'taxable_value'    => $taxable,
            'cgst_amount'      => (float)($data['cgst_amount'] ?? $cgst),
            'sgst_amount'      => (float)($data['sgst_amount'] ?? $sgst),
            'igst_amount'      => (float)($data['igst_amount'] ?? $igst),
            'cess_amount'      => (float)($data['cess_amount'] ?? 0),
            'hsn_sac_code'     => $data['hsn_sac_code'] ?? null,
            'gst_rate'         => $rate,
            'return_period'    => $data['return_period'] ?? null,
            'itc_eligible'     => !empty($data['transaction_type']) && $data['transaction_type'] === 'input' ? 1 : 0,
            'itc_claimed'      => !empty($data['itc_claimed']) ? 1 : 0,
        'tenant_id' => TenantContext::getId(),
        ]);
    }

    // ============================================================
    //  DEMAND LETTER TEMPLATES
    // ============================================================

    public function generateDemandLetter(int $bookingId, string $type): array
    {
        $tid = TenantContext::getId();
        $booking = $this->db->fetchOne(
            "SELECT b.*, u.name AS customer_name, u.email, u.phone,
                    p.plot_number, p.total_price, p.area_sqft,
                    c.name AS colony_name
             FROM bookings b
             LEFT JOIN users u ON u.id = b.user_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
             LEFT JOIN plots p ON p.id = b.plot_id
             LEFT JOIN colonies c ON c.id = b.colony_id
             WHERE b.id = ?",
            $tid > 1 ? [$bookingId, $tid] : [$bookingId]
        );
        if (!$booking) {
            throw new Exception('Booking not found');
        }

        $tmpl = $this->db->fetchOne(
            "SELECT * FROM demand_letter_template
             WHERE template_type = ? AND is_active = 1
             ORDER BY id DESC LIMIT 1",
            [$type]
        );
        if (!$tmpl) {
            throw new Exception('No active template for type: ' . $type);
        }

        $vars = [
            'customer_name'   => $booking['customer_name'] ?? '',
            'email'           => $booking['email'] ?? '',
            'phone'           => $booking['phone'] ?? '',
            'booking_number'  => $booking['booking_number'] ?? '',
            'plot_number'     => $booking['plot_number'] ?? '',
            'colony_name'     => $booking['colony_name'] ?? '',
            'area_sqft'       => number_format((float)($booking['area_sqft'] ?? 0), 2),
            'total_price'     => number_format((float)($booking['total_price'] ?? 0), 2),
            'booking_date'    => $booking['booking_date'] ?? '',
            'amount_due'      => number_format((float)($booking['total_amount'] ?? 0), 2),
            'due_date'        => date('Y-m-d', strtotime('+7 days')),
        ];

        return [
            'subject'      => $this->replaceVars($tmpl['subject'], $vars),
            'body_html'    => $this->replaceVars($tmpl['body_html'] ?? '', $vars),
            'body_text'    => $this->replaceVars($tmpl['body_text'] ?? '', $vars),
            'sms_body'     => $this->replaceVars($tmpl['sms_body'] ?? '', $vars),
            'whatsapp_body'=> $this->replaceVars($tmpl['whatsapp_body'] ?? '', $vars),
            'booking'      => $booking,
            'template'     => $tmpl,
        ];
    }

    private function replaceVars(string $text, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $text = str_replace('{{' . $k . '}}', (string)$v, $text);
        }
        return $text;
    }

    // ============================================================
    //  CASH FLOW FORECAST
    // ============================================================

    public function forecastCashFlow(int $days = 30): array
    {
        $from = date('Y-m-d');
        $to   = date('Y-m-d', strtotime("+{$days} days"));

        $rows = $this->db->fetchAll(
            "SELECT *,
                    (expected_amount * probability_pct / 100) AS weighted_amount,
                    DATEDIFF(expected_date, CURDATE()) AS days_ahead
             FROM cash_flow_forecast
             WHERE expected_date BETWEEN ? AND ?
             ORDER BY expected_date ASC, type",
            [$from, $to]
        ) ?: [];

        $summary = [
            'inflow_total'     => 0.0,
            'outflow_total'    => 0.0,
            'weighted_inflow'  => 0.0,
            'weighted_outflow' => 0.0,
            'net'              => 0.0,
            'by_day'           => [],
        ];
        foreach ($rows as $r) {
            $weighted = (float)$r['weighted_amount'];
            if ($r['type'] === 'inflow') {
                $summary['inflow_total']    += (float)$r['expected_amount'];
                $summary['weighted_inflow'] += $weighted;
            } else {
                $summary['outflow_total']    += (float)$r['expected_amount'];
                $summary['weighted_outflow'] += $weighted;
            }
            $summary['by_day'][$r['expected_date']][] = $r;
        }
        $summary['net'] = $summary['weighted_inflow'] - $summary['weighted_outflow'];

        return [
            'summary' => $summary,
            'rows'    => $rows,
            'from'    => $from,
            'to'      => $to,
        ];
    }

    // ============================================================
    //  EXPENSE APPROVAL WORKFLOW
    // ============================================================

    public function approveExpense(int $approvalId, string $decision, string $remarks = ''): bool
    {
        $status = $decision === 'approve' ? 'approved' : 'rejected';
        $this->db->execute(
            "UPDATE expense_approvals
             SET status = ?, remarks = ?, approved_at = NOW()
             WHERE id = ?",
            [$status, $remarks, $approvalId]
        );

        // If approved, update the underlying expense record too
        $row = $this->db->fetchOne(
            "SELECT expense_id, expense_table, approval_level FROM expense_approvals WHERE id = ?",
            [$approvalId]
        );
        if ($row && $status === 'approved') {
            $this->db->execute(
                "UPDATE expenses SET status = 'approved' WHERE id = ?",
                [$row['expense_id']]
            );
        } elseif ($row && $status === 'rejected') {
            $this->db->execute(
                "UPDATE expenses SET status = 'rejected' WHERE id = ?",
                [$row['expense_id']]
            );
        }

        return true;
    }

    // ============================================================
    //  VENDOR MANAGEMENT + KYC
    // ============================================================

    /**
     * Create a new vendor with KYC fields and auto-detect TDS section.
     *
     * TDS auto-detection rules (194C — payments to contractors):
     *   individual     → 194C @ 1% (single-person contractor)
     *   company        → 194C @ 2% (corporate vendor)
     *   partnership    → 194C @ 2% (partnership firm)
     *   proprietorship → 194C @ 1% (sole-proprietor, treated like individual)
     *
     * Falls back to vendor_type mapping when entity_type is not set:
     *   contractor, transport → treated as individual (1%)
     *   supplier, service_provider, consultant, other → treated as company (2%)
     */
    public function createVendor(array $data): int
    {
        $entityType = $data['entity_type'] ?? null;
        if (!$entityType) {
            // Map legacy vendor_type to entity_type for TDS
            $vt = strtolower($data['vendor_type'] ?? 'other');
            $entityType = in_array($vt, ['contractor', 'transport']) ? 'individual' : 'company';
        }

        $tdsSection = $this->autoDetectTdsSection($entityType);
        $tdsRate    = $this->getTdsRateForSection($tdsSection, $entityType);

        $payload = [
            'vendor_name'       => trim($data['vendor_name'] ?? ''),
            'vendor_type'       => $data['vendor_type'] ?? 'other',
            'contact_person'    => $data['contact_person'] ?? null,
            'email'             => $data['email'] ?? null,
            'phone'             => $data['phone'] ?? null,
            'address'           => $data['address'] ?? null,
            'city'              => $data['city'] ?? null,
            'state'             => $data['state'] ?? null,
            'gst_number'        => strtoupper($data['gst_number'] ?? $data['gstin'] ?? ''),
            'gstin'             => strtoupper($data['gstin'] ?? $data['gst_number'] ?? ''),
            'pan_number'        => strtoupper($data['pan_number'] ?? ''),
            'entity_type'       => $entityType,
            'tds_section'       => $tdsSection,
            'is_tds_applicable' => isset($data['is_tds_applicable']) ? (int)$data['is_tds_applicable'] : 1,
            'kyc_status'        => $data['kyc_status'] ?? 'pending',
            'kyc_verified_at'   => null,
            'bank_name'         => $data['bank_name'] ?? null,
            'bank_account'      => $data['bank_account'] ?? null,
            'ifsc_code'         => strtoupper($data['ifsc_code'] ?? ''),
            'payment_terms'     => $data['payment_terms'] ?? '30_days',
            'contract_start'    => !empty($data['contract_start']) ? $data['contract_start'] : null,
            'contract_end'      => !empty($data['contract_end']) ? $data['contract_end'] : null,
            'status'            => $data['status'] ?? 'active',
            'notes'             => $data['notes'] ?? null,
            'created_by'        => $data['created_by'] ?? ($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null),
            'tenant_id'         => TenantContext::getId(),
        ];

        $this->db->insert('vendors', $payload);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Auto-detect TDS section based on entity_type.
     * All 194C rates per Income Tax Act:
     *   individual / proprietorship → 194C (1% for single-person entities)
     *   company / partnership       → 194C (2% for corporate/partnership entities)
     */
    public function autoDetectTdsSection(string $entityType): string
    {
        // All entity types fall under 194C; the rate differs by entity classification
        $validTypes = ['individual', 'company', 'partnership', 'proprietorship'];
        return in_array(strtolower($entityType), $validTypes) ? '194C' : '194C';
    }

    /**
     * Get the TDS rate for a section + entity_type combination.
     * 194C rates:
     *   individual / proprietorship → 1%
     *   company / partnership       → 2%
     */
    public function getTdsRateForSection(string $section, string $entityType): float
    {
        if ($section === '194C') {
            $entity = strtolower($entityType);
            return in_array($entity, ['individual', 'proprietorship']) ? 1.0 : 2.0;
        }

        // Other TDS sections with fixed rates
        $rates = [
            '194IA' => 1.0,
            '194IB' => 5.0,
            '194H'  => 5.0,
            '194I'  => 10.0,
            '194J'  => 10.0,
            '194M'  => 5.0,
            '194N'  => 2.0,
        ];
        return $rates[$section] ?? 0.0;
    }

    /**
     * Mark a vendor's KYC as verified.
     */
    public function verifyVendorKyc(int $vendorId): bool
    {
        $this->db->execute(
            "UPDATE vendors SET kyc_status = 'verified', kyc_verified_at = NOW() WHERE id = ?",
            [$vendorId]
        );
        return true;
    }

    /**
     * Mark a vendor's KYC as rejected.
     */
    public function rejectVendorKyc(int $vendorId, string $reason = ''): bool
    {
        $this->db->execute(
            "UPDATE vendors SET kyc_status = 'rejected', notes = CONCAT(COALESCE(notes,''), '\nKYC Rejected: ', ?) WHERE id = ?",
            [$reason, $vendorId]
        );
        return true;
    }

    /**
     * Get a single vendor by ID.
     */
    public function getVendor(int $id): ?array
    {
        $tid = TenantContext::getId();
        $row = $this->db->fetchOne("SELECT * FROM vendors WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$id, $tid] : [$id]);
        return $row ?: null;
    }

    /**
     * List vendors with optional filters.
     */
    public function listVendors(array $filters = []): array
    {
        $sql = "SELECT * FROM vendors WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        if (!empty($filters['kyc_status'])) {
            $sql .= " AND kyc_status = ?";
            $params[] = $filters['kyc_status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (vendor_name LIKE ? OR contact_person LIKE ? OR pan_number LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY created_at DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return $this->db->fetchAll($sql, $params) ?: [];
    }

    // ============================================================
    //  VENDOR PAYMENTS
    // ============================================================

    public function payVendor(array $data): int
    {
        $gross  = (float)($data['gross_amount'] ?? $data['amount'] ?? 0);
        $tds    = (float)($data['tds_amount'] ?? $data['tds_deducted'] ?? 0);
        $gst    = (float)($data['gst_amount'] ?? 0);
        $net    = $gross - $tds;

        $currency   = strtoupper(trim($data['currency'] ?? 'INR'));
        $fxRate     = (float)($data['exchange_rate'] ?? 1.0);
        $amountInr  = !empty($data['amount_inr'])
            ? (float)$data['amount_inr']
            : round($gross * $fxRate, 2);

        // Auto-fetch exchange rate for non-INR currencies if rate is missing or default
        if ($currency !== 'INR' && ($fxRate <= 0 || $fxRate == 1.0)) {
            try {
                $fxService = new \App\Services\ExchangeRateService();
                $rateResult = $fxService->getRate($currency, 'INR');
                if ($rateResult['success'] && isset($rateResult['rate'])) {
                    $fxRate    = (float)$rateResult['rate'];
                    $amountInr = round($gross * $fxRate, 2);
                }
            } catch (Exception $e) {
            // Keep manual rate on failure
            error_log($e->getMessage());
            }
        }

        $this->db->beginTransaction();
        try {
            $vpId = (int)$this->db->insert('vendor_payments', [
                'vendor_id'      => !empty($data['vendor_id']) ? (int)$data['vendor_id'] : null,
                'vendor_name'    => $data['vendor_name'] ?? '',
                'vendor_gstin'   => strtoupper($data['vendor_gstin'] ?? ''),
                'vendor_type'    => $data['vendor_type'] ?? null,
                'bill_id'        => !empty($data['bill_id']) ? (int)$data['bill_id'] : null,
                'bill_number'    => $data['bill_number'] ?? null,
                'bill_date'      => $data['bill_date'] ?? null,
                'gross_amount'   => $gross,
                'tds_section'    => $data['tds_section'] ?? null,
                'tds_amount'     => $tds,
                'gst_amount'     => $gst,
                'net_payable'    => $net,
                'paid_amount'    => (float)($data['paid_amount'] ?? $net),
                'currency'       => $currency,
                'exchange_rate'  => $fxRate,
                'amount_inr'     => $amountInr,
                'payment_date'   => $data['payment_date'] ?? date('Y-m-d'),
                'payment_mode'   => $data['payment_mode'] ?? 'rtgs',
                'bank_account_id'=> !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null,
                'transaction_ref'=> $data['transaction_ref'] ?? null,
                'status'         => $data['status'] ?? 'paid',
            'tenant_id' => TenantContext::getId(),
            ]);

            // Voucher
            $voucher = $this->generateVoucherNumber('payment');
            $this->db->insert('payment_voucher_log', [
                'voucher_type'   => 'payment',
                'voucher_number' => $voucher,
                'voucher_date'   => $data['payment_date'] ?? date('Y-m-d'),
                'amount'         => $net,
                'narration'      => 'Payment to ' . $data['vendor_name'] . ' - ' . ($data['bill_number'] ?? ''),
                'generated_for'  => 'vendor_payments',
                'reference_id'   => $vpId,
                'created_by'     => $data['created_by'] ?? ($_SESSION['admin_id'] ?? null),
            'tenant_id' => TenantContext::getId(),
            ]);

            // Journal: Dr Vendor / Cr Bank, with TDS + GST split
            $vendorAccount = $this->getAccountByCode('2100'); // Customer advances / liability group
            $bankAccount   = $this->getAccountByCode('1200');
            $tdsAccount    = $this->getAccountByCode('2110');
            $gstItc        = $this->getAccountByCode('2120');

            $lines = [];
            if ($vendorAccount) {
                $lines[] = ['account_id' => $vendorAccount['id'], 'debit' => $gross, 'credit' => 0, 'desc' => 'Vendor bill ' . ($data['bill_number'] ?? '')];
            }
            if ($bankAccount) {
                $lines[] = ['account_id' => $bankAccount['id'], 'debit' => 0, 'credit' => (float)($data['paid_amount'] ?? $net), 'desc' => 'Bank payment to vendor'];
            }
            if ($tds > 0 && $tdsAccount) {
                $lines[] = ['account_id' => $tdsAccount['id'], 'debit' => 0, 'credit' => $tds, 'desc' => 'TDS deducted u/s ' . ($data['tds_section'] ?? '-')];
            }
            if ($gst > 0 && $gstItc) {
                $lines[] = ['account_id' => $gstItc['id'], 'debit' => $gst, 'credit' => 0, 'desc' => 'GST ITC claimed'];
            }

            if (!empty($lines)) {
                $this->postJournalEntry([
                    'entry_date'      => $data['payment_date'] ?? date('Y-m-d'),
                    'description'     => 'Vendor payment - ' . $data['vendor_name'],
                    'source_document' => 'payment',
                    'source_id'       => $vpId,
                    'lines'           => $lines,
                    'auto_approve'    => true,
                    'created_by'      => $data['created_by'] ?? ($_SESSION['admin_id'] ?? null),
                ]);
            }

            $this->db->commit();
            return $vpId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================================
    //  TDS CERTIFICATES (Form 16A)
    // ============================================================

    public function issueTDSCertificate(int $deducteeUserId, string $fy, string $quarter): int
    {
        $tid = TenantContext::getId();
        $deductee = $this->db->fetchOne(
            "SELECT id, name, pan FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$deducteeUserId, $tid] : [$deducteeUserId]
        );
        if (!$deductee) {
            throw new Exception('Deductee not found');
        }

        // Sum TDS for the FY + quarter
        $quarterStart = match ($quarter) {
            'Q1' => $fy . '-04-01',
            'Q2' => $fy . '-07-01',
            'Q3' => $fy . '-10-01',
            'Q4' => $fy . '-01-01',
            default => throw new Exception('Invalid quarter'),
        };
        $quarterEnd = match ($quarter) {
            'Q1' => $fy . '-06-30',
            'Q2' => $fy . '-09-30',
            'Q3' => $fy . '-12-31',
            'Q4' => $fy . '-03-31',
        };

        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total_tds), 0) AS total
             FROM tds_register
             WHERE deductee_pan = ?
               AND transaction_date BETWEEN ? AND ?",
            [strtoupper($deductee['pan'] ?? ''), $quarterStart, $quarterEnd]
        );

        $certNumber = '16A-' . str_replace('-', '', $fy) . '-' . $quarter . '-' . substr(md5($deductee['pan'] . $fy . $quarter), 0, 6);

        return (int)$this->db->insert('tds_certificates_issued', [
            'deductee_user_id'   => $deducteeUserId,
            'deductee_name'      => $deductee['name'],
            'deductee_pan'       => strtoupper($deductee['pan'] ?? ''),
            'financial_year'     => $fy,
            'quarter'            => $quarter,
            'total_tds'          => (float)($row['total'] ?? 0),
            'certificate_number' => $certNumber,
            'issued_date'        => date('Y-m-d'),
        'tenant_id' => TenantContext::getId(),
        ]);
    }

    // ============================================================
    //  POST JOURNAL ENTRY (double-entry, Dr = Cr)
    // ============================================================

    public function postJournalEntry(array $data): int
    {
        $lines = $data['lines'] ?? [];
        $totalDr  = 0.0;
        $totalCr  = 0.0;
        foreach ($lines as $l) {
            $totalDr += (float)($l['debit']  ?? 0);
            $totalCr += (float)($l['credit'] ?? 0);
        }
        if (abs($totalDr - $totalCr) > 0.01) {
            throw new Exception("Journal entry unbalanced: Dr=$totalDr Cr=$totalCr (diff=" . ($totalDr - $totalCr) . ")");
        }
        if (empty($lines)) {
            throw new Exception('Journal entry has no lines');
        }

        $this->db->beginTransaction();
        try {
            $journalNumber = $data['journal_number'] ?? $this->generateJournalNumber();
            $entryId = (int)$this->db->insert('journal_entries', [
                'journal_number'  => $journalNumber,
                'entry_date'      => $data['entry_date'] ?? date('Y-m-d'),
                'reference_number'=> $data['reference_number'] ?? null,
                'description'     => $data['description'] ?? '',
                'total_debit'     => $totalDr,
                'total_credit'    => $totalCr,
                'entry_type'      => $data['entry_type'] ?? 'system',
                'source_document' => $data['source_document'] ?? null,
                'source_id'       => !empty($data['source_id']) ? (int)$data['source_id'] : null,
                'created_by'      => (int)($data['created_by'] ?? ($_SESSION['admin_id'] ?? 1)),
                'approved_by'     => !empty($data['auto_approve']) ? (int)($data['created_by'] ?? ($_SESSION['admin_id'] ?? 1)) : null,
                'approval_date'   => !empty($data['auto_approve']) ? date('Y-m-d H:i:s') : null,
                'status'          => !empty($data['auto_approve']) ? 'approved' : 'draft',
            'tenant_id' => TenantContext::getId(),
            ]);

            $order = 1;
            foreach ($lines as $l) {
                $this->db->insert('journal_entry_lines', [
                    'journal_entry_id' => $entryId,
                    'account_id'       => (int)$l['account_id'],
                    'debit_amount'     => (float)($l['debit']  ?? 0),
                    'credit_amount'    => (float)($l['credit'] ?? 0),
                    'description'      => $l['desc'] ?? null,
                    'line_order'       => $order++,
                'tenant_id' => TenantContext::getId(),
                ]);

                // Update account current balance
                $debit  = (float)($l['debit']  ?? 0);
                $credit = (float)($l['credit'] ?? 0);
                $this->db->execute(
                    "UPDATE chart_of_accounts
                     SET current_balance = current_balance + ? - ?
                     WHERE id = ?",
                    [$debit, $credit, (int)$l['account_id']]
                );
            }

            $this->db->commit();
            return $entryId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================================
    //  GENERAL LEDGER
    // ============================================================

    public function getLedger(int $accountId, string $fromDate, string $toDate): array
    {
        $account = $this->db->fetchOne("SELECT * FROM chart_of_accounts WHERE id = ?", [$accountId]);
        if (!$account) {
            return ['account' => null, 'lines' => [], 'opening' => 0.0, 'closing' => 0.0];
        }

        $opening = (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(debit_amount - credit_amount), 0)
             FROM journal_entry_lines jel
             JOIN journal_entries je ON je.id = jel.journal_entry_id
             WHERE jel.account_id = ? AND je.entry_date < ?",
            [$accountId, $fromDate]
        );

        $rows = $this->db->fetchAll(
            "SELECT je.entry_date, je.journal_number, je.description AS entry_desc,
                    jel.debit_amount, jel.credit_amount, jel.description AS line_desc
             FROM journal_entry_lines jel
             JOIN journal_entries je ON je.id = jel.journal_entry_id
             WHERE jel.account_id = ?
               AND je.entry_date BETWEEN ? AND ?
               AND je.status = 'approved'
             ORDER BY je.entry_date, je.id, jel.line_order",
            [$accountId, $fromDate, $toDate]
        ) ?: [];

        $running = $opening;
        foreach ($rows as &$r) {
            $running += (float)$r['debit_amount'] - (float)$r['credit_amount'];
            $r['running_balance'] = $running;
        }

        return [
            'account' => $account,
            'lines'   => $rows,
            'opening' => $opening,
            'closing' => $running,
        ];
    }

    // ============================================================
    //  TRIAL BALANCE
    // ============================================================

    public function getTrialBalance(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');

        $rows = $this->db->fetchAll(
            "SELECT a.id, a.account_code, a.account_name, a.account_type, a.account_category,
                    COALESCE(SUM(jel.debit_amount), 0)  AS total_debit,
                    COALESCE(SUM(jel.credit_amount), 0) AS total_credit,
                    a.opening_balance
             FROM chart_of_accounts a
             LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
             LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                AND je.entry_date <= ?
                AND je.status = 'approved'
             WHERE a.is_active = 1
             GROUP BY a.id
             ORDER BY a.account_code",
            [$asOfDate]
        ) ?: [];

        $totDr = 0.0; $totCr = 0.0;
        foreach ($rows as &$r) {
            $balance = (float)$r['opening_balance'] + (float)$r['total_debit'] - (float)$r['total_credit'];
            $r['balance']     = $balance;
            $r['balance_type'] = $balance >= 0 ? 'Dr' : 'Cr';
            $totDr += max($balance, 0);
            $totCr += max(-$balance, 0);
        }
        unset($r);

        return [
            'as_of_date' => $asOfDate,
            'rows'       => $rows,
            'total_dr'   => $totDr,
            'total_cr'   => $totCr,
        ];
    }

    // ============================================================
    //  PROFIT & LOSS
    // ============================================================

    public function getProfitLoss(string $fromDate, string $toDate): array
    {
        $rows = $this->db->fetchAll(
            "SELECT a.id, a.account_code, a.account_name, a.account_type,
                    COALESCE(SUM(jel.credit_amount), 0) AS total_credit,
                    COALESCE(SUM(jel.debit_amount), 0)  AS total_debit
             FROM chart_of_accounts a
             LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
             LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                AND je.entry_date BETWEEN ? AND ?
                AND je.status = 'approved'
             WHERE a.is_active = 1
               AND a.account_type IN ('income','expense')
             GROUP BY a.id
             ORDER BY a.account_type, a.account_code",
            [$fromDate, $toDate]
        ) ?: [];

        $income = 0.0; $expense = 0.0;
        $incomeRows = []; $expenseRows = [];
        foreach ($rows as $r) {
            if ($r['account_type'] === 'income') {
                $net = (float)$r['total_credit'] - (float)$r['total_debit'];
                $income += $net;
                $r['net'] = $net;
                $incomeRows[] = $r;
            } else {
                $net = (float)$r['total_debit'] - (float)$r['total_credit'];
                $expense += $net;
                $r['net'] = $net;
                $expenseRows[] = $r;
            }
        }

        return [
            'from'         => $fromDate,
            'to'           => $toDate,
            'income_rows'  => $incomeRows,
            'expense_rows' => $expenseRows,
            'total_income' => $income,
            'total_expense'=> $expense,
            'net_profit'   => $income - $expense,
        ];
    }

    // ============================================================
    //  BALANCE SHEET
    // ============================================================

    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');

        $rows = $this->db->fetchAll(
            "SELECT a.id, a.account_code, a.account_name, a.account_type, a.account_category,
                    a.opening_balance,
                    COALESCE(SUM(jel.debit_amount), 0)  AS total_debit,
                    COALESCE(SUM(jel.credit_amount), 0) AS total_credit
             FROM chart_of_accounts a
             LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
             LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                AND je.entry_date <= ?
                AND je.status = 'approved'
             WHERE a.is_active = 1
               AND a.account_type IN ('asset','liability','equity')
             GROUP BY a.id
             ORDER BY a.account_type, a.account_code",
            [$asOfDate]
        ) ?: [];

        $assets    = ['rows' => [], 'total' => 0.0];
        $liab      = ['rows' => [], 'total' => 0.0];
        $equity    = ['rows' => [], 'total' => 0.0];
        foreach ($rows as $r) {
            $balance = (float)$r['opening_balance'] + (float)$r['total_debit'] - (float)$r['total_credit'];
            $r['balance'] = $balance;
            if ($r['account_type'] === 'asset') {
                $assets['rows'][] = $r;
                $assets['total'] += $balance;
            } elseif ($r['account_type'] === 'liability') {
                $liab['rows'][] = $r;
                $liab['total']  += abs($balance);
            } else {
                $equity['rows'][] = $r;
                $equity['total'] += abs($balance);
            }
        }

        return [
            'as_of_date'      => $asOfDate,
            'assets'          => $assets,
            'liabilities'     => $liab,
            'equity'          => $equity,
            'total_liab_equity' => $liab['total'] + $equity['total'],
            'is_balanced'     => abs($assets['total'] - ($liab['total'] + $equity['total'])) < 1.0,
        ];
    }

    // ============================================================
    //  CASH FLOW STATEMENT
    // ============================================================

    public function getCashFlowStatement(string $fromDate, string $toDate): array
    {
        // Operating = receipts - payments to operating accounts (income, expense)
        // Investing = changes in fixed assets, land
        // Financing = changes in equity, loans

        $operating = $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN jel.credit_amount > 0 THEN jel.credit_amount ELSE 0 END), 0) AS inflow,
                COALESCE(SUM(CASE WHEN jel.debit_amount  > 0 THEN jel.debit_amount  ELSE 0 END), 0) AS outflow
             FROM journal_entry_lines jel
             JOIN journal_entries je ON je.id = jel.journal_entry_id
             JOIN chart_of_accounts a ON a.id = jel.account_id
             WHERE je.entry_date BETWEEN ? AND ?
               AND je.status = 'approved'
               AND a.account_type IN ('income','expense')",
            [$fromDate, $toDate]
        ) ?: ['inflow' => 0, 'outflow' => 0];

        $investing = $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(jel.credit_amount), 0) AS inflow,
                COALESCE(SUM(jel.debit_amount),  0) AS outflow
             FROM journal_entry_lines jel
             JOIN journal_entries je ON je.id = jel.journal_entry_id
             JOIN chart_of_accounts a ON a.id = jel.account_id
             WHERE je.entry_date BETWEEN ? AND ?
               AND je.status = 'approved'
               AND a.account_category = 'fixed_asset'",
            [$fromDate, $toDate]
        ) ?: ['inflow' => 0, 'outflow' => 0];

        $financing = $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(jel.credit_amount), 0) AS inflow,
                COALESCE(SUM(jel.debit_amount),  0) AS outflow
             FROM journal_entry_lines jel
             JOIN journal_entries je ON je.id = jel.journal_entry_id
             JOIN chart_of_accounts a ON a.id = jel.account_id
             WHERE je.entry_date BETWEEN ? AND ?
               AND je.status = 'approved'
               AND a.account_type IN ('liability','equity')
               AND a.account_category IN ('long_term_liability','owner_equity')",
            [$fromDate, $toDate]
        ) ?: ['inflow' => 0, 'outflow' => 0];

        $operatingNet = (float)$operating['inflow'] - (float)$operating['outflow'];
        $investingNet = (float)$investing['inflow'] - (float)$investing['outflow'];
        $financingNet = (float)$financing['inflow'] - (float)$financing['outflow'];

        return [
            'from' => $fromDate,
            'to'   => $toDate,
            'operating' => [
                'inflow'  => (float)$operating['inflow'],
                'outflow' => (float)$operating['outflow'],
                'net'     => $operatingNet,
            ],
            'investing' => [
                'inflow'  => (float)$investing['inflow'],
                'outflow' => (float)$investing['outflow'],
                'net'     => $investingNet,
            ],
            'financing' => [
                'inflow'  => (float)$financing['inflow'],
                'outflow' => (float)$financing['outflow'],
                'net'     => $financingNet,
            ],
            'net_change' => $operatingNet + $investingNet + $financingNet,
        ];
    }

    // ============================================================
    //  3-WAY RECONCILIATION  (bank, ledger, property-wise)
    // ============================================================

    public function threeWayReconciliation(int $trustAccountId, ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $bank = $this->db->fetchOne("SELECT * FROM bank_accounts_master WHERE id = ?", [$trustAccountId]);
        if (!$bank) {
            throw new Exception('Bank account not found');
        }

        // (1) Bank statement balance
        $bankBalance = (float)$bank['current_balance'];

        // (2) Ledger (book) balance from journal entries on this bank account
        $bankAcct = $this->db->fetchOne("SELECT id FROM chart_of_accounts WHERE account_code = '1200'");
        $bookBalance = 0.0;
        if ($bankAcct) {
            $bookBalance = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(jel.debit_amount - jel.credit_amount), 0)
                 FROM journal_entry_lines jel
                 JOIN journal_entries je ON je.id = jel.journal_entry_id
                 WHERE jel.account_id = ?
                   AND je.entry_date <= ?
                   AND je.status = 'approved'",
                [$bankAcct['id'], $asOfDate]
            );
        }

        // (3) Property-wise collection vs. expected
        $propertyRows = $this->db->fetchAll(
            "SELECT b.id AS booking_id, b.booking_number, b.total_amount,
                    c.name AS colony_name, p.plot_number,
                    COALESCE((SELECT SUM(amount) FROM payments WHERE booking_id = b.id AND status = 'completed'), 0) AS collected
             FROM bookings b
             LEFT JOIN plots p ON p.id = b.plot_id
             LEFT JOIN colonies c ON c.id = b.colony_id
             WHERE b.colony_id IS NOT NULL
             ORDER BY b.created_at DESC
             LIMIT 50"
        ) ?: [];

        $expected  = 0.0; $collected = 0.0; $pending = 0.0;
        foreach ($propertyRows as $p) {
            $expected  += (float)$p['total_amount'];
            $collected += (float)$p['collected'];
            $pending   += (float)$p['total_amount'] - (float)$p['collected'];
        }

        return [
            'as_of_date'     => $asOfDate,
            'bank'           => $bank,
            'bank_balance'   => $bankBalance,
            'ledger_balance' => $bookBalance,
            'difference'     => $bankBalance - $bookBalance,
            'property' => [
                'rows'         => $propertyRows,
                'expected'     => $expected,
                'collected'    => $collected,
                'pending'      => $pending,
                'is_reconciled'=> abs($bankBalance - $collected) < 1.0,
            ],
        ];
    }

    // ============================================================
    //  EMI PENALTY ENGINE
    //  18% flat per annum = 0.0493% per day
    //  5-day grace period after due_date
    // ============================================================

    public function applyDailyPenalties(): array
    {
        $result = [
            'success'       => true,
            'penalties_applied' => 0,
            'total_penalty'     => 0.0,
            'installments'      => [],
        ];

        try {
            $rows = $this->db->fetchAll(
                "SELECT bps.*, pb.plot_id, pb.booking_number, pb.booking_date,
                        DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
                 FROM booking_payment_schedules bps
                 LEFT JOIN plot_bookings pb ON pb.id = bps.booking_id
                 WHERE bps.status IN ('pending','overdue')
                   AND bps.due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)"
            ) ?: [];

            $advanceCache = [];

            foreach ($rows as $row) {
                $bookingId = (int)$row['booking_id'];

                // 1. Advance Payment Check
                if (!isset($advanceCache[$bookingId])) {
                    $totalPaid = (float)$this->db->fetchOne(
                        "SELECT COALESCE(SUM(paid_amount), 0) AS total FROM booking_payment_schedules WHERE booking_id = ?",
                        [$bookingId]
                    )['total'];

                    $totalScheduled = (float)$this->db->fetchOne(
                        "SELECT COALESCE(SUM(amount), 0) AS total FROM booking_payment_schedules WHERE booking_id = ? AND due_date <= CURDATE()",
                        [$bookingId]
                    )['total'];

                    $advanceCache[$bookingId] = ($totalPaid >= $totalScheduled);
                }

                if ($advanceCache[$bookingId]) {
                    // Skip completely: customer has paid in advance
                    continue;
                }

                // 2. 3-Year Interest Free Check
                $bookingDate = $row['booking_date'] ?? null;
                $isInterestFree = false;
                if ($bookingDate) {
                    $bDate = new \DateTime($bookingDate);
                    $dDate = new \DateTime($row['due_date']);
                    $threeYearsLimit = (clone $bDate)->modify('+3 years');
                    if ($dDate <= $threeYearsLimit) {
                        $isInterestFree = true;
                    }
                }

                // 3. Lose Interest-Free status if 3 consecutive bounces
                if ($isInterestFree) {
                    if ($this->hasThreeConsecutiveOverdueEMIs($bookingId)) {
                        $isInterestFree = false;
                    }
                }

                $daysOverdue  = (int)$row['days_overdue'];
                $installmentAmt = (float)$row['amount'];

                if ($isInterestFree) {
                    $newPenalty = 0.0;
                } else {
                    $penaltyRate  = 0.18;
                    $dailyRate    = $penaltyRate / 365;
                    $newPenalty   = round($installmentAmt * $dailyRate * $daysOverdue, 2);
                }

                $prevAccrued  = (float)($row['accrued_penalty'] ?? 0);
                $totalAccrued = $prevAccrued + $newPenalty;

                // Update accrued_penalty on the installment
                $this->db->execute(
                    "UPDATE booking_payment_schedules SET accrued_penalty = ?, status = 'overdue' WHERE id = ?",
                    [$totalAccrued, $row['id']]
                );

                if ($newPenalty > 0.0) {
                    // Log to penalty_audit
                    $this->db->insert('penalty_audit', [
                        'installment_id' => $row['id'],
                        'booking_id'     => $row['booking_id'],
                        'days_overdue'   => $daysOverdue,
                        'penalty_amount' => $newPenalty,
                        'total_accrued'  => $totalAccrued,
                    'tenant_id' => TenantContext::getId(),
                    ]);

                    $result['penalties_applied']++;
                    $result['total_penalty'] += $newPenalty;

                    // Notify customer of penalty
                    try {
                        $userId = $this->db->fetchOne(
                            "SELECT pb.user_id FROM plot_bookings pb WHERE pb.id = ?",
                            [$row['booking_id']]
                        );
                        if (!empty($userId['user_id'])) {
                            $notifSvc = new \App\Services\Communication\NotificationService();
                            $notifSvc->sendNotification((int)$userId['user_id'], 'in_app',
                                'EMI Penalty Applied',
                                'Installment #' . $row['installment_no'] . ' is ' . $daysOverdue . ' days overdue. Penalty of ₹' . number_format($newPenalty, 2) . ' applied (total: ₹' . number_format($totalAccrued, 2) . ').',
                                ['event_type' => 'payment', 'booking_id' => $row['booking_id'], 'action_url' => '/booking/confirmation/' . $row['booking_id']]
                            );
                        }
                    } catch (\Throwable $e) {
                        error_log('applyDailyPenalties notification failed: ' . $e->getMessage());
                    }
                }

                $result['installments'][] = [
                    'id'             => $row['id'],
                    'booking_id'     => $row['booking_id'],
                    'booking_number' => $row['booking_number'] ?? '',
                    'installment_no' => $row['installment_no'],
                    'due_date'       => $row['due_date'],
                    'days_overdue'   => $daysOverdue,
                    'amount'         => $installmentAmt,
                    'new_penalty'    => $newPenalty,
                    'total_accrued'  => $totalAccrued,
                ];
            }
        } catch (\Throwable $e) {
            error_log('applyDailyPenalties error: ' . $e->getMessage());
            $result['success'] = false;
            $result['error']   = $e->getMessage();
        }

        return $result;
    }

    public function getOverduePenaltySummary(): array
    {
        $summary = [
            'total_overdue_count'    => 0,
            'total_overdue_amount'   => 0.0,
            'total_accrued_penalties'=> 0.0,
            'worst_overdue_days'     => 0,
            'overdue_installments'   => [],
        ];

        try {
            $tid = TenantContext::getId();
            $rows = $this->db->fetchAll(
                "SELECT bps.*, pb.plot_id, pb.booking_number, pb.booking_date,
                        p.plot_number,
                        u.name AS customer_name,
                        DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
                 FROM booking_payment_schedules bps
                 LEFT JOIN plot_bookings pb ON pb.id = bps.booking_id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN users u ON u.id = pb.customer_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                 WHERE bps.status IN ('pending','overdue')
                   AND bps.due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                 ORDER BY bps.due_date ASC"
            ) ?: [];

            $advanceCache = [];

            foreach ($rows as $row) {
                $bookingId = (int)$row['booking_id'];

                // Advance Payment Check
                if (!isset($advanceCache[$bookingId])) {
                    $totalPaid = (float)$this->db->fetchOne(
                        "SELECT COALESCE(SUM(paid_amount), 0) AS total FROM booking_payment_schedules WHERE booking_id = ?",
                        [$bookingId]
                    )['total'];

                    $totalScheduled = (float)$this->db->fetchOne(
                        "SELECT COALESCE(SUM(amount), 0) AS total FROM booking_payment_schedules WHERE booking_id = ? AND due_date <= CURDATE()",
                        [$bookingId]
                    )['total'];

                    $advanceCache[$bookingId] = ($totalPaid >= $totalScheduled);
                }

                if ($advanceCache[$bookingId]) {
                    // Skip completely: customer has paid in advance
                    continue;
                }

                $days = (int)$row['days_overdue'];
                $summary['total_overdue_count']++;
                $summary['total_overdue_amount']   += (float)$row['amount'];
                $summary['total_accrued_penalties'] += (float)($row['accrued_penalty'] ?? 0);
                if ($days > $summary['worst_overdue_days']) {
                    $summary['worst_overdue_days'] = $days;
                }
                $summary['overdue_installments'][] = [
                    'id'             => $row['id'],
                    'booking_id'     => $row['booking_id'],
                    'booking_number' => $row['booking_number'] ?? '',
                    'installment_no' => $row['installment_no'],
                    'due_date'       => $row['due_date'],
                    'days_overdue'   => $days,
                    'amount'         => (float)$row['amount'],
                    'accrued_penalty'=> (float)($row['accrued_penalty'] ?? 0),
                    'plot_number'    => $row['plot_number'] ?? '',
                    'customer_name'  => $row['customer_name'] ?? '',
                ];
            }
        } catch (\Throwable $e) {
            error_log('getOverduePenaltySummary error: ' . $e->getMessage());
        }

        return $summary;
    }

    private function hasThreeConsecutiveOverdueEMIs(int $bookingId): bool
    {
        $installments = $this->db->fetchAll(
            "SELECT status, due_date FROM booking_payment_schedules 
             WHERE booking_id = ? 
             ORDER BY installment_no ASC",
            [$bookingId]
        ) ?: [];

        $consecutive = 0;
        $today = date('Y-m-d');
        foreach ($installments as $inst) {
            $isUnpaid = ($inst['status'] !== 'paid');
            $isPastDue = ($inst['due_date'] < $today);
            if ($isUnpaid && $isPastDue) {
                $consecutive++;
                if ($consecutive >= 3) {
                    return true;
                }
            } else {
                $consecutive = 0;
            }
        }
        return false;
    }

    // ============================================================
    //  ON-FIELD CASH COLLECTION & RECONCILIATION
    // ============================================================

    public function recordCollection(array $data): array
    {
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Amount must be greater than 0'];
        }

        $collectorId = (int)($data['collector_id'] ?? 0);
        if ($collectorId <= 0) {
            return ['success' => false, 'error' => 'Collector is required'];
        }

        $customerName = trim($data['customer_name'] ?? '');
        if ($customerName === '') {
            return ['success' => false, 'error' => 'Customer name is required'];
        }

        $collectionDate = $data['collection_date'] ?? date('Y-m-d');

        // Generate collection number: APS-CC-YYYYMMDD-NNNN
        $today = date('Ymd');
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM cash_collections WHERE DATE(created_at) = CURDATE()"
        );
        $collectionNumber = sprintf('APS-CC-%s-%04d', $today, $count + 1);

        $id = (int)$this->db->insert('cash_collections', [
            'booking_id'      => !empty($data['booking_id']) ? (int)$data['booking_id'] : null,
            'installment_id'  => !empty($data['installment_id']) ? (int)$data['installment_id'] : null,
            'collector_id'    => $collectorId,
            'customer_name'   => $customerName,
            'amount'          => $amount,
            'collection_date' => $collectionDate,
            'payment_method'  => $data['payment_method'] ?? 'cash',
            'reference_number'=> trim($data['reference_number'] ?? ''),
            'receipt_photo'   => $data['receipt_photo'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'status'          => 'submitted',
        'tenant_id' => TenantContext::getId(),
        ]);

        // If linked to a booking installment, update the EMI schedule paid_amount
        if (!empty($data['booking_id']) && !empty($data['installment_id'])) {
            try {
                $this->db->execute(
                    "UPDATE booking_payment_schedules
                     SET paid_amount = paid_amount + ?, payment_date = ?, payment_method = ?
                     WHERE id = ? AND paid_amount < emi_amount",
                    [$amount, $collectionDate, $data['payment_method'] ?? 'cash', (int)$data['installment_id']]
                );
            } catch (\Throwable $e) {
                error_log("[CashCollection] Failed to update installment: " . $e->getMessage());
            }
        }

        return ['success' => true, 'id' => $id, 'collection_number' => $collectionNumber];
    }

    public function getCollections(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT cc.*, u.name AS collector_name
                FROM cash_collections cc
                LEFT JOIN users u ON u.id = cc.collector_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                WHERE 1=1";
        $params = [];
        if ($tid > 1) $params[] = $tid;

        if (!empty($filters['status'])) {
            $sql .= " AND cc.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['collector_id'])) {
            $sql .= " AND cc.collector_id = ?";
            $params[] = (int)$filters['collector_id'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND cc.collection_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND cc.collection_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY cc.collection_date DESC, cc.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        } elseif (!isset($filters['limit'])) {
            $sql .= " LIMIT 200";
        }

        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getCollection(int $id): ?array
    {
        $tid = TenantContext::getId();
        $row = $this->db->fetchOne(
            "SELECT cc.*, u.name AS collector_name
             FROM cash_collections cc
             LEFT JOIN users u ON u.id = cc.collector_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
             WHERE cc.id = ?",
            $tid > 1 ? [$id, $tid] : [$id]
        );
        return $row ?: null;
    }

    public function verifyCollection(int $id, int $verifiedBy): bool
    {
        $this->db->execute(
            "UPDATE cash_collections SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE id = ? AND status = 'submitted'",
            [$verifiedBy, $id]
        );
        return true;
    }

    public function rejectCollection(int $id, int $rejectedBy, string $reason): bool
    {
        $this->db->execute(
            "UPDATE cash_collections SET status = 'rejected', verified_by = ?, verified_at = NOW(), rejection_reason = ? WHERE id = ? AND status = 'submitted'",
            [$rejectedBy, $reason, $id]
        );
        return true;
    }

    public function startReconciliation(int $collectorId, string $date): array
    {
        // Check for existing open session for this collector+date
        $existing = $this->db->fetchOne(
            "SELECT id FROM reconciliation_collections WHERE collector_id = ? AND session_date = ? AND status = 'open'",
            [$collectorId, $date]
        );
        if ($existing) {
            return ['success' => false, 'error' => 'Open reconciliation session already exists for this collector on this date'];
        }

        // Calculate totals from verified collections for this collector+date
        $totals = $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'submitted' THEN amount ELSE 0 END), 0) AS submitted,
                COALESCE(SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END), 0) AS verified,
                COALESCE(SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END), 0) AS rejected
             FROM cash_collections
             WHERE collector_id = ? AND collection_date = ?",
            [$collectorId, $date]
        );

        $submitted = (float)($totals['submitted'] ?? 0);
        $verified  = (float)($totals['verified'] ?? 0);
        $rejected  = (float)($totals['rejected'] ?? 0);
        $discrepancy = $submitted - $verified - $rejected;

        $id = (int)$this->db->insert('reconciliation_collections', [
            'session_date'      => $date,
            'collector_id'      => $collectorId,
            'total_submitted'   => $submitted,
            'total_verified'    => $verified,
            'total_rejected'    => $rejected,
            'discrepancy_amount'=> abs($discrepancy),
            'status'            => $discrepancy > 0.01 ? 'discrepancy' : 'open',
        'tenant_id' => TenantContext::getId(),
        ]);

        return ['success' => true, 'id' => $id, 'discrepancy' => $discrepancy];
    }

    public function closeReconciliation(int $sessionId, int $closedBy): bool
    {
        $this->db->execute(
            "UPDATE reconciliation_collections SET status = 'closed', closed_by = ?, closed_at = NOW() WHERE id = ?",
            [$closedBy, $sessionId]
        );
        return true;
    }

    public function getReconciliationSessions(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT rc.*, u.name AS collector_name
                FROM reconciliation_collections rc
                LEFT JOIN users u ON u.id = rc.collector_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                WHERE 1=1";
        $params = [];
        if ($tid > 1) $params[] = $tid;

        if (!empty($filters['status'])) {
            $sql .= " AND rc.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['collector_id'])) {
            $sql .= " AND rc.collector_id = ?";
            $params[] = (int)$filters['collector_id'];
        }

        $sql .= " ORDER BY rc.session_date DESC, rc.id DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getCollectionStats(): array
    {
        $stats = [
            'today_total'         => 0.0,
            'today_count'         => 0,
            'pending_verification'=> 0,
            'pending_amount'      => 0.0,
            'month_total'         => 0.0,
            'month_count'         => 0,
            'verified_total'      => 0.0,
            'rejected_total'      => 0.0,
        ];

        try {
            $row = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) AS cnt
                 FROM cash_collections WHERE collection_date = CURDATE()"
            );
            $stats['today_total'] = (float)($row['total'] ?? 0);
            $stats['today_count'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getCollectionStats error: ' . $e->getMessage()); }

        try {
            $row = $this->db->fetchOne(
                "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total
                 FROM cash_collections WHERE status = 'submitted'"
            );
            $stats['pending_verification'] = (int)($row['cnt'] ?? 0);
            $stats['pending_amount'] = (float)($row['total'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getCollectionStats error: ' . $e->getMessage()); }

        try {
            $monthFrom = date('Y-m-01');
            $monthTo = date('Y-m-t');
            $row = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) AS cnt
                 FROM cash_collections WHERE collection_date BETWEEN ? AND ?",
                [$monthFrom, $monthTo]
            );
            $stats['month_total'] = (float)($row['total'] ?? 0);
            $stats['month_count'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getCollectionStats error: ' . $e->getMessage()); }

        try {
            $row = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) AS total FROM cash_collections WHERE status = 'verified'"
            );
            $stats['verified_total'] = (float)($row['total'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getCollectionStats error: ' . $e->getMessage()); }

        try {
            $row = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) AS total FROM cash_collections WHERE status = 'rejected'"
            );
            $stats['rejected_total'] = (float)($row['total'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getCollectionStats error: ' . $e->getMessage()); }

        return $stats;
    }

    public function listCollectors(): array
    {
        $tid = TenantContext::getId();
        return $this->db->fetchAll(
            "SELECT u.id, u.name FROM users u
             WHERE u.role IN ('associate','agent','employee','admin')" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
             ORDER BY u.name",
            $tid > 1 ? [$tid] : []
        ) ?: [];
    }

    // ============================================================
    //  LEGAL / REGISTRY NOC PIPE
    // ============================================================

    public function checkRegistryEligibility(int $bookingId): array
    {
        $result = [
            'eligible'        => false,
            'reasons'         => [],
            'overdue_count'   => 0,
            'pending_amount'  => 0.0,
            'penalty_amount'  => 0.0,
            'booking'         => null,
        ];

        try {
            $tid = TenantContext::getId();
            $booking = $this->db->fetchOne(
                "SELECT pb.*, u.name AS customer_name, p.plot_number, c.name AS colony_name
                 FROM plot_bookings pb
                 LEFT JOIN users u ON u.id = pb.customer_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 WHERE pb.id = ?",
                $tid > 1 ? [$bookingId, $tid] : [$bookingId]
            );
            $result['booking'] = $booking;

            if (!$booking) {
                $result['reasons'][] = 'Booking not found.';
                return $result;
            }

            // 1. Overdue installments: status IN (pending, overdue) AND due_date < CURDATE()
            $overdue = $this->db->fetchOne(
                "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount - paid_amount), 0) AS pending
                 FROM booking_payment_schedules
                 WHERE booking_id = ?
                   AND status IN ('pending', 'overdue')
                   AND due_date < CURDATE()",
                [$bookingId]
            );
            $overdueCount = (int)($overdue['cnt'] ?? 0);
            $pendingAmt   = (float)($overdue['pending'] ?? 0);
            $result['overdue_count']  = $overdueCount;
            $result['pending_amount'] = $pendingAmt;

            if ($overdueCount > 0) {
                $result['reasons'][] = $overdueCount . ' overdue installment(s) totaling ₹' . number_format($pendingAmt, 2) . '.';
            }

            // 2. Accrued penalties > 0
            $penalty = $this->db->fetchOne(
                "SELECT COALESCE(SUM(accrued_penalty), 0) AS total
                 FROM booking_payment_schedules
                 WHERE booking_id = ? AND accrued_penalty > 0",
                [$bookingId]
            );
            $penaltyAmt = (float)($penalty['total'] ?? 0);
            $result['penalty_amount'] = $penaltyAmt;

            if ($penaltyAmt > 0) {
                $result['reasons'][] = 'Accrued penalties of ₹' . number_format($penaltyAmt, 2) . ' must be cleared.';
            }

            $result['eligible'] = empty($result['reasons']);
        } catch (\Throwable $e) {
            error_log('checkRegistryEligibility error: ' . $e->getMessage());
            $result['reasons'][] = 'System error: ' . $e->getMessage();
        }

        return $result;
    }

    public function generateNoc(int $bookingId, int $generatedBy): array
    {
        try {
            $tid = TenantContext::getId();
            $booking = $this->db->fetchOne(
                "SELECT pb.*, u.name AS customer_name, p.plot_number, c.name AS colony_name
                 FROM plot_bookings pb
                 LEFT JOIN users u ON u.id = pb.customer_id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 WHERE pb.id = ?",
                $tid > 1 ? [$bookingId, $tid] : [$bookingId]
            );
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            $eligibility = $this->checkRegistryEligibility($bookingId);
            if (!$eligibility['eligible']) {
                return [
                    'success' => false,
                    'error'   => 'Booking is not eligible for NOC generation.',
                    'reasons' => $eligibility['reasons'],
                ];
            }

            // NOC number: APS-NOC-YYYYMMDD-NNNN
            $today   = date('Ymd');
            $count   = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM daily_operations_log
                 WHERE log_type = 'other'
                   AND description LIKE 'NOC generated%'
                   AND log_date = CURDATE()"
            );
            $nocNumber = sprintf('APS-NOC-%s-%04d', $today, $count + 1);

            $nocId = (int)$this->db->insert('daily_operations_log', [
                'log_date'    => date('Y-m-d'),
                'log_type'    => 'other',
                'colony_id'   => $booking['colony_id'] ?? null,
                'plot_id'     => $booking['plot_id'] ?? null,
                'description' => 'NOC generated for ' . ($booking['customer_name'] ?? 'Customer') .
                                ' — Plot ' . ($booking['plot_number'] ?? '') .
                                ' (' . ($booking['colony_name'] ?? '') . ')',
                'party_name'  => $booking['customer_name'] ?? 'Customer',
                'party_type'  => 'customer',
                'priority'    => 'medium',
                'status'      => 'completed',
                'created_by'  => $generatedBy,
            'tenant_id' => TenantContext::getId(),
            ]);

            return [
                'success'    => true,
                'noc_id'     => $nocId,
                'noc_number' => $nocNumber,
                'booking'    => $booking,
                'generated_at' => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            error_log('generateNoc error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'System error: ' . $e->getMessage()];
        }
    }

    // ============================================================
    //  Helpers
    // ============================================================

    private function getAccountByCode(string $code): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM chart_of_accounts WHERE account_code = ? LIMIT 1", [$code]);
        return $row ?: null;
    }

    private function generateVoucherNumber(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $year   = date('Y');
        $count  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM payment_voucher_log WHERE voucher_type = ? AND YEAR(voucher_date) = ?",
            [$type, $year]
        );
        return sprintf('%s-%s-%06d', $prefix, $year, $count + 1);
    }

    private function generateJournalNumber(): string
    {
        $year = date('Y');
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM journal_entries WHERE YEAR(entry_date) = ?",
            [$year]
        );
        return sprintf('JV-%s-%06d', $year, $count + 1);
    }

    private function mapSectionToExpense(string $section): string
    {
        return match ($section) {
            '194C' => '5300', // Brokerage
            '194J' => '5500', // Professional / Salary
            '194I' => '5500',
            '194IA'=> '5600', // TDS on property transfer
            '194IB'=> '5600',
            default => '5500',
        };
    }

    // ============================================================
    //  MODULE 3 SPEC ALIASES
    //  Map spec-required method names to existing comprehensive impls.
    //  These provide the canonical public surface used by
    //  MoneyWorkflowController so the controller code stays clean.
    // ============================================================

    public function getBankAccounts(bool $activeOnly = true): array
    {
        return $this->listBankAccounts($activeOnly);
    }

    public function getBankAccount(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM bank_accounts_master WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public function recordTransaction(array $data): array
    {
        return $this->recordCashTransaction($data);
    }

    public function getDailyCashBook(string $fromDate, string $toDate, ?int $bankAccountId = null): array
    {
        $sql = "SELECT * FROM daily_cash_book WHERE transaction_date BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];
        if ($bankAccountId) {
            $sql .= " AND (bank_account_id = ? OR bank_account_id IS NULL)";
            $params[] = $bankAccountId;
        }
        $sql .= " ORDER BY transaction_date DESC, id DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getCashBookSummary(string $fromDate, string $toDate): array
    {
        $rows = $this->db->fetchAll(
            "SELECT transaction_type, SUM(amount) total FROM daily_cash_book
             WHERE transaction_date BETWEEN ? AND ? GROUP BY transaction_type",
            [$fromDate, $toDate]
        ) ?: [];
        $out = ['receipt' => 0.0, 'payment' => 0.0, 'contra' => 0.0, 'net' => 0.0];
        foreach ($rows as $r) {
            $out[$r['transaction_type']] = (float)$r['total'];
        }
        $out['net'] = $out['receipt'] - $out['payment'];
        return $out;
    }

    public function issueChequeWithVoucher(array $data): int
    {
        return $this->issueCheque($data);
    }

    public function markChequeStatus(int $id, string $status, string $reason = ''): bool
    {
        $status = strtolower($status);
        if ($status === 'cleared' || $status === 'realized') {
            return $this->markChequeCleared($id, date('Y-m-d'));
        }
        if ($status === 'bounced') {
            return $this->markChequeBounced($id, $reason);
        }
        return $this->db->execute(
            "UPDATE cheque_register SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        ) > 0;
    }

    public function getChequeRegister(array $filters = []): array
    {
        $sql = "SELECT c.*, b.account_name, b.bank_name FROM cheque_register c
                LEFT JOIN bank_accounts_master b ON c.bank_account_id = b.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['bank_account_id'])) {
            $sql .= " AND c.bank_account_id = ?";
            $params[] = (int)$filters['bank_account_id'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND c.cheque_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND c.cheque_date <= ?";
            $params[] = $filters['to_date'];
        }
        $sql .= " ORDER BY c.cheque_date DESC, c.id DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getChequeById(int $id): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT c.*, b.account_name, b.account_number, b.ifsc_code, b.bank_name, b.branch, b.signatory_name
             FROM cheque_register c
             LEFT JOIN bank_accounts_master b ON c.bank_account_id = b.id
             WHERE c.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    public function createReconciliation(int $bankAccountId, array $data): int
    {
        return $this->startBankReconciliation($bankAccountId, $data);
    }

    public function getReconciliationItems(int $reconciliationId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM bank_reconciliation_items WHERE reconciliation_id = ? ORDER BY id",
            [$reconciliationId]
        ) ?: [];
    }

    public function getReconciliations(?int $bankAccountId = null): array
    {
        $sql = "SELECT r.*, b.account_name, b.bank_name FROM bank_reconciliation r
                LEFT JOIN bank_accounts_master b ON r.bank_account_id = b.id
                WHERE 1=1";
        $params = [];
        if ($bankAccountId) {
            $sql .= " AND r.bank_account_id = ?";
            $params[] = $bankAccountId;
        }
        $sql .= " ORDER BY r.statement_date DESC, r.id DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function matchTransaction(int $itemId, string $status, ?int $cashBookId = null): bool
    {
        if ($cashBookId !== null) {
            $this->db->execute(
                "UPDATE bank_reconciliation_items SET status = ?, matched_cashbook_id = ? WHERE id = ?",
                [$status, $cashBookId, $itemId]
            );
        }
        return $this->reconcileItem($itemId, $status);
    }

    public function completeReconciliation(int $id): bool
    {
        return $this->db->execute(
            "UPDATE bank_reconciliation SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$id]
        ) > 0;
    }

    public function recordTdsProxy(array $data): int
    {
        // Translate spec field names -> recordTDS expected field names
        $translated = [
            'tds_section'      => $data['section_code'] ?? $data['tds_section'] ?? null,
            'deductor_pan'     => $data['deductor_pan'] ?? '',
            'deductor_name'    => $data['deductor_name'] ?? null,
            'deductee_pan'     => $data['deductee_pan'] ?? ($data['deductee_user_id'] ? 'PAN' . str_pad((string)$data['deductee_user_id'], 8, '0', STR_PAD_LEFT) : 'UNKNWN00'),
            'deductee_name'    => $data['deductee_name'] ?? null,
            'transaction_date' => $data['tds_date'] ?? $data['transaction_date'] ?? date('Y-m-d'),
            'transaction_ref'  => $data['transaction_ref'] ?? null,
            'gross_amount'     => (float)($data['gross_amount'] ?? 0),
            'tds_rate'         => (float)($data['tds_rate'] ?? 0),
            'surcharge'        => (float)($data['surcharge'] ?? 0),
            'cess'             => (float)($data['cess'] ?? 0),
            'deposit_challan'  => $data['deposit_challan'] ?? null,
            'bsr_code'         => $data['bsr_code'] ?? null,
            'deposit_date'     => $data['deposit_date'] ?? null,
            'return_period'    => $data['quarter'] ?? $data['return_period'] ?? null,
            'status'           => $data['status'] ?? 'pending',
            'created_by'       => $data['created_by'] ?? null,
        ];
        $tdsId = $this->recordTDS($translated);
        // Persist extra spec fields (financial_year, quarter, user_id) that recordTDS doesn't capture
        try {
            $fy = $data['financial_year'] ?? null;
            $quarter = $data['quarter'] ?? null;
            $userId = $data['deductee_user_id'] ?? null;
            if ($fy || $quarter || $userId) {
                $set = [];
                $params = [];
                if ($fy) { $set[] = 'financial_year = ?'; $params[] = $fy; }
                if ($quarter) { $set[] = 'quarter = ?'; $params[] = $quarter; }
                if ($userId) { $set[] = 'deductee_user_id = ?'; $params[] = (int)$userId; }
                if ($set) {
                    $params[] = $tdsId;
                    $this->db->execute('UPDATE tds_register SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
                }
            }
        } catch (Exception $e) { /* column may not exist - swallow */ error_log($e->getMessage()); }
        return $tdsId;
    }

    public function getTdsRegister(array $filters = []): array
    {
        $sql = "SELECT t.*, b.account_name FROM tds_register t
                LEFT JOIN bank_accounts_master b ON t.deposited_in_bank = b.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['fy'])) {
            $sql .= " AND t.financial_year = ?";
            $params[] = $filters['fy'];
        }
        if (!empty($filters['quarter'])) {
            $sql .= " AND t.quarter = ?";
            $params[] = $filters['quarter'];
        }
        if (!empty($filters['deductee_type'])) {
            $sql .= " AND t.deductee_type = ?";
            $params[] = $filters['deductee_type'];
        }
        $sql .= " ORDER BY t.tds_date DESC, t.id DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getTdsSummary(string $fy): array
    {
        $rows = $this->db->fetchAll(
            "SELECT section_code, SUM(tds_amount) total_tds, SUM(gross_amount) total_gross
             FROM tds_register WHERE financial_year = ? GROUP BY section_code",
            [$fy]
        ) ?: [];
        $bySection = [];
        $grand = 0.0;
        $gross = 0.0;
        foreach ($rows as $r) {
            $bySection[$r['section_code']] = [
                'total_tds' => (float)$r['total_tds'],
                'total_gross' => (float)$r['total_gross'],
            ];
            $grand += (float)$r['total_tds'];
            $gross += (float)$r['total_gross'];
        }
        return [
            'financial_year' => $fy,
            'by_section' => $bySection,
            'total_tds' => $grand,
            'total_gross' => $gross,
        ];
    }

    public function generateTdsCertificate(int $deducteeUserId, string $fy, string $quarter): int
    {
        return $this->issueTDSCertificate($deducteeUserId, $fy, $quarter);
    }

    public function getTdsCertificatesIssued(string $fy = ''): array
    {
        $sql = "SELECT * FROM tds_certificates_issued WHERE 1=1";
        $params = [];
        if ($fy !== '') {
            $sql .= " AND financial_year = ?";
            $params[] = $fy;
        }
        $sql .= " ORDER BY id DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function recordGstProxy(array $data): int
    {
        // Translate spec field names -> recordGST expected field names
        $taxable = (float)($data['taxable_amount'] ?? $data['taxable_value'] ?? 0);
        $rate    = (float)($data['gst_rate'] ?? 0);
        $cgst    = (float)($data['cgst'] ?? $data['cgst_amount'] ?? 0);
        $sgst    = (float)($data['sgst'] ?? $data['sgst_amount'] ?? 0);
        $igst    = (float)($data['igst'] ?? $data['igst_amount'] ?? 0);
        $partyGstin = $data['party_gstin'] ?? null;
        $partyName  = $data['party_name'] ?? 'Unknown';
        $isInterState = $igst > 0 || (($data['supply_type'] ?? '') === 'inter');
        if (!$cgst && !$sgst && !$igst) {
            if ($isInterState) { $igst = round($taxable * $rate / 100, 2); }
            else { $cgst = round($taxable * $rate / 200, 2); $sgst = round($taxable * $rate / 200, 2); }
        }
        $translated = [
            'transaction_date' => $data['transaction_date'] ?? date('Y-m-d'),
            'transaction_type' => $data['transaction_type'] ?? 'output',
            'party_gstin'      => $partyGstin,
            'party_name'       => $partyName,
            'invoice_number'   => $data['invoice_number'] ?? null,
            'invoice_date'     => $data['invoice_date'] ?? $data['transaction_date'] ?? null,
            'taxable_value'    => $taxable,
            'cgst_amount'      => $cgst,
            'sgst_amount'      => $sgst,
            'igst_amount'      => $igst,
            'cess_amount'      => (float)($data['cess'] ?? $data['cess_amount'] ?? 0),
            'hsn_sac_code'     => $data['hsn_sac_code'] ?? null,
            'gst_rate'         => $rate,
            'return_period'    => $data['quarter'] ?? $data['return_period'] ?? null,
        ];
        $gstId = $this->recordGST($translated);
        // Persist extra spec fields (financial_year) that recordGST doesn't capture
        try {
            $fy = $data['financial_year'] ?? null;
            if ($fy) {
                $this->db->execute('UPDATE gst_transactions SET financial_year = ? WHERE id = ?', [$fy, $gstId]);
            }
        } catch (Exception $e) { /* column may not exist - swallow */ error_log($e->getMessage()); }
        return $gstId;
    }

    public function getGstTransactions(array $filters = []): array
    {
        $sql = "SELECT * FROM gst_transactions WHERE 1=1";
        $params = [];
        if (!empty($filters['fy'])) {
            $sql .= " AND financial_year = ?";
            $params[] = $filters['fy'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND transaction_type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['month'])) {
            $sql .= " AND DATE_FORMAT(transaction_date, '%Y-%m') = ?";
            $params[] = $filters['month'];
        }
        $sql .= " ORDER BY transaction_date DESC, id DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getGstSummary(string $fy): array
    {
        $rows = $this->db->fetchAll(
            "SELECT transaction_type, SUM(cgst) total_cgst, SUM(sgst) total_sgst, SUM(igst) total_igst,
                    SUM(taxable_amount) total_taxable, SUM(total_tax) total_tax
             FROM gst_transactions WHERE financial_year = ? GROUP BY transaction_type",
            [$fy]
        ) ?: [];
        $out = [
            'output' => ['cgst' => 0, 'sgst' => 0, 'igst' => 0, 'taxable' => 0, 'tax' => 0],
            'input'  => ['cgst' => 0, 'sgst' => 0, 'igst' => 0, 'taxable' => 0, 'tax' => 0],
            'net_payable' => 0,
        ];
        foreach ($rows as $r) {
            $key = ($r['transaction_type'] === 'output') ? 'output' : 'input';
            $out[$key]['cgst'] += (float)$r['total_cgst'];
            $out[$key]['sgst'] += (float)$r['total_sgst'];
            $out[$key]['igst'] += (float)$r['total_igst'];
            $out[$key]['taxable'] += (float)$r['total_taxable'];
            $out[$key]['tax'] += (float)$r['total_tax'];
        }
        $out['net_payable'] = $out['output']['tax'] - $out['input']['tax'];
        return $out;
    }

    public function createDemandLetterTemplate(array $data): int
    {
        $this->db->insert('demand_letter_template', [
            'template_name' => $data['template_name'] ?? 'Untitled',
            'template_type' => $data['template_type'] ?? 'overdue_installment',
            'subject'       => $data['subject'] ?? '',
            'body_html'     => $data['body_html'] ?? '',
            'placeholders'  => $data['placeholders'] ?? null,
            'active'        => isset($data['active']) ? (int)$data['active'] : 1,
            'created_by'    => $data['created_by'] ?? ($_SESSION['admin_id'] ?? null),
        'tenant_id' => TenantContext::getId(),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getDemandLetterTemplates(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM demand_letter_template" . ($activeOnly ? " WHERE active = 1" : "") . " ORDER BY template_name";
        return $this->db->fetchAll($sql) ?: [];
    }

    public function getDemandLetterTemplate(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM demand_letter_template WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public function updateDemandLetterTemplate(int $id, array $data): bool
    {
        $payload = [];
        foreach (['template_name', 'template_type', 'subject', 'body_html', 'placeholders', 'active'] as $f) {
            if (array_key_exists($f, $data)) {
                $payload[$f] = $data[$f];
            }
        }
        if (empty($payload)) {
            return false;
        }
        $payload['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('demand_letter_template', $payload, 'id = ?', [$id]) > 0;
    }

    public function deleteDemandLetterTemplate(int $id): bool
    {
        return $this->db->execute("DELETE FROM demand_letter_template WHERE id = ?", [$id]) > 0;
    }

    public function generateForecast(int $days = 30): array
    {
        return $this->forecastCashFlow($days);
    }

    public function getCashForecasts(int $days = 30): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM cash_flow_forecast
             WHERE forecast_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY forecast_date",
            [$days]
        ) ?: [];
    }

    public function getActualVsForecast(string $fromDate, string $toDate): array
    {
        return $this->db->fetchAll(
            "SELECT forecast_date,
                    SUM(CASE WHEN direction='inflow'  THEN expected_amount ELSE 0 END) forecast_inflow,
                    SUM(CASE WHEN direction='outflow' THEN expected_amount ELSE 0 END) forecast_outflow
             FROM cash_flow_forecast
             WHERE forecast_date BETWEEN ? AND ?
             GROUP BY forecast_date ORDER BY forecast_date",
            [$fromDate, $toDate]
        ) ?: [];
    }

    public function submitExpense(array $data): int
    {
        $this->db->insert('expenses', [
            'expense_date'     => $data['expense_date'] ?? date('Y-m-d'),
            'category'         => $data['category'] ?? 'general',
            'description'      => $data['description'] ?? '',
            'amount'           => (float)($data['amount'] ?? 0),
            'associate_id'     => !empty($data['associate_id']) ? (int)$data['associate_id'] : null,
            'payment_mode'     => $data['payment_mode'] ?? 'cash',
            'proof_file'       => $data['proof_file'] ?? $data['supporting_doc'] ?? null,
            'status'           => $data['status'] ?? 'pending',
        'tenant_id' => TenantContext::getId(),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getExpenses(array $filters = []): array
    {
        $sql = "SELECT * FROM expenses WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        $sql .= " ORDER BY id DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getExpense(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM expense_approvals WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public function approveExpenseById(int $id, string $remarks = ''): bool
    {
        return $this->approveExpense($id, 'approved', $remarks);
    }

    public function rejectExpenseById(int $id, string $remarks = ''): bool
    {
        return $this->approveExpense($id, 'rejected', $remarks);
    }

    public function recordVendorPayment(array $data): int
    {
        return $this->payVendor($data);
    }

    public function getVendorPayments(array $filters = []): array
    {
        $sql = "SELECT * FROM vendor_payments WHERE 1=1";
        $params = [];
        if (!empty($filters['vendor_id'])) {
            $sql .= " AND vendor_id = ?";
            $params[] = (int)$filters['vendor_id'];
        }
        if (!empty($filters['vendor_type'])) {
            $sql .= " AND vendor_type = ?";
            $params[] = $filters['vendor_type'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND payment_date >= ?";
            $params[] = $filters['from_date'];
        }
        $sql .= " ORDER BY payment_date DESC, id DESC";
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getVendorOutstanding(): array
    {
        return $this->db->fetchAll(
            "SELECT vendor_id, vendor_name, vendor_type,
                    SUM(gross_amount) total_payable,
                    SUM(tds_amount) total_tds,
                    SUM(gst_amount) total_gst,
                    COUNT(*) bills
             FROM vendor_payments
             WHERE status IN ('pending','partial')
             GROUP BY vendor_id, vendor_name, vendor_type
             ORDER BY total_payable DESC"
        ) ?: [];
    }

    public function getVoucherLog(int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM payment_voucher_log ORDER BY id DESC LIMIT ?",
            [(int)$limit]
        ) ?: [];
    }

    public function getDashboardStats(): array
    {
        $totalBalance = 0.0;
        $escrowBalance = 0.0;
        try {
            $rows = $this->db->fetchAll("SELECT current_balance, is_escrow FROM bank_accounts_master WHERE active = 1") ?: [];
            foreach ($rows as $r) {
                $totalBalance += (float)$r['current_balance'];
                if ((int)$r['is_escrow'] === 1) {
                    $escrowBalance += (float)$r['current_balance'];
                }
            }
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        $petty = 0.0;
        try { $petty = $this->getPettyCashBalance(); } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        $chequesIssued = 0;
        $chequesPending = 0;
        $chequesBounced = 0;
        try {
            $row = $this->db->fetchOne("SELECT
                SUM(CASE WHEN status='issued' THEN 1 ELSE 0 END) issued,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
                SUM(CASE WHEN status='bounced' THEN 1 ELSE 0 END) bounced
                FROM cheque_register") ?: [];
            $chequesIssued = (int)($row['issued'] ?? 0);
            $chequesPending = (int)($row['pending'] ?? 0);
            $chequesBounced = (int)($row['bounced'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        $monthFrom = date('Y-m-01');
        $monthTo = date('Y-m-t');
        $receipts = 0.0;
        $payments = 0.0;
        try {
            $row = $this->db->fetchOne(
                "SELECT SUM(CASE WHEN transaction_type='receipt' THEN amount ELSE 0 END) r,
                        SUM(CASE WHEN transaction_type='payment' THEN amount ELSE 0 END) p
                 FROM daily_cash_book WHERE transaction_date BETWEEN ? AND ?",
                [$monthFrom, $monthTo]
            ) ?: [];
            $receipts = (float)($row['r'] ?? 0);
            $payments = (float)($row['p'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        $tdsThisQtr = 0.0;
        try {
            $row = $this->db->fetchOne("SELECT SUM(tds_amount) s FROM tds_register
                WHERE quarter = CONCAT('Q', QUARTER(CURDATE())) AND financial_year = CONCAT(YEAR(CURDATE())-1,'-',YEAR(CURDATE()))") ?: [];
            $tdsThisQtr = (float)($row['s'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        $gstNet = 0.0;
        try {
            $row = $this->db->fetchOne("SELECT
                SUM(CASE WHEN transaction_type='output' THEN total_tax ELSE 0 END) -
                SUM(CASE WHEN transaction_type='input'  THEN total_tax ELSE 0 END) net
                FROM gst_transactions
                WHERE financial_year = CONCAT(YEAR(CURDATE())-1,'-',YEAR(CURDATE()))") ?: [];
            $gstNet = (float)($row['net'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        $pendingExpenses = 0;
        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) c FROM expense_approvals WHERE status='pending'") ?: [];
            $pendingExpenses = (int)($row['c'] ?? 0);
        } catch (\Throwable $e) { error_log('MoneyWorkflowService::getDashboardStats error: ' . $e->getMessage()); }

        return [
            'total_bank_balance' => $totalBalance,
            'escrow_balance'     => $escrowBalance,
            'petty_cash'         => $petty,
            'cheques_issued'     => $chequesIssued,
            'cheques_pending'    => $chequesPending,
            'cheques_bounced'    => $chequesBounced,
            'cash_receipts_mtd'  => $receipts,
            'cash_payments_mtd'  => $payments,
            'cash_net_mtd'       => $receipts - $payments,
            'tds_quarter'        => $tdsThisQtr,
            'gst_net_payable'    => $gstNet,
            'pending_expenses'   => $pendingExpenses,
        ];
    }
}
