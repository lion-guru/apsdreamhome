<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

use \App\Traits\ServiceTenantTrait;

class BankImportService
{
    use \App\Traits\ServiceTenantTrait;

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

    private function getPdo(): \PDO
    {
        return $this->db;
    }

    /**
     * Parse CSV file and import bank transactions.
     * Expected CSV columns: Date, Description, Debit, Credit, Balance, Cheque/Ref No
     */
    public function importCsv(string $filePath, int $importedBy = null): array
    {
        try {
            $pdo = $this->getPdo();

            if (!file_exists($filePath)) {
                return ['success' => false, 'error' => 'File not found'];
            }

            $originalFilename = basename($filePath);
            $importDate = date('Y-m-d');

            // Create import record
            $tid = $this->tenantId();
            $tidCol = $tid > 1 ? ", tenant_id" : "";
            $tidPlaceholder = $tid > 1 ? ", ?" : "";
            $tidParam = $tid > 1 ? [$tid] : [];
            $stmt = $pdo->prepare("
                INSERT INTO bank_statement_imports (filename, original_filename, import_date, status, imported_by{$tidCol})
                VALUES (?, ?, ?, 'processing', ?{$tidPlaceholder})
            ");
            $stmt->execute(array_merge([$originalFilename, $originalFilename, $importDate, $importedBy], $tidParam));
            $importId = (int)$pdo->lastInsertId();

            // Open CSV
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                $this->updateImportStatus($importId, 'failed', 'Cannot open file');
                return ['success' => false, 'error' => 'Cannot open file'];
            }

            // Read header row
            $header = fgetcsv($handle);
            if ($header === false) {
                fclose($handle);
                $this->updateImportStatus($importId, 'failed', 'Empty CSV file');
                return ['success' => false, 'error' => 'Empty CSV file'];
            }

            // Detect column mapping
            $map = $this->detectColumnMapping($header);

            $totalRows = 0;
            $inserted = 0;

            $tid = $this->tenantId();
            $tidCol = $tid > 1 ? ", tenant_id" : "";
            $tidPlaceholder = $tid > 1 ? ", ?" : "";
            $tidParam = $tid > 1 ? [$tid] : [];
            $stmtInsert = $pdo->prepare("
                INSERT INTO bank_transactions
                    (import_id, bank_account_id, transaction_date, value_date, description, debit, credit, balance, cheque_number, reference_number{$tidCol})
                VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?{$tidPlaceholder})
            ");

            while (($row = fgetcsv($handle)) !== false) {
                $totalRows++;
                $txnDate = $this->parseDate($row[$map['date']] ?? '');
                $description = trim($row[$map['description']] ?? '');
                $debit = (float)($row[$map['debit']] ?? 0);
                $credit = (float)($row[$map['credit']] ?? 0);
                $balance = (float)($row[$map['balance']] ?? 0);
                $chequeRef = trim($row[$map['cheque']] ?? '');
                $reference = trim($row[$map['reference']] ?? '');

                if (empty($txnDate) || empty($description)) {
                    continue;
                }

                $stmtInsert->execute([
                    $importId,
                    $txnDate,
                    null,
                    $description,
                    $debit,
                    $credit,
                    $balance,
                    $chequeRef ?: null,
                    $reference ?: null,
                    ...$tidParam,
                ]);
                $inserted++;
            }

            fclose($handle);

            // Update import record
            $this->updateImportStatus($importId, 'completed', null, $totalRows, 0, $inserted);

            return [
                'success'    => true,
                'import_id'  => $importId,
                'total_rows' => $totalRows,
                'imported'   => $inserted,
            ];
        } catch (Exception $e) {
            error_log('BankImportService::importCsv error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Detect CSV column mapping from header row
     */
    private function detectColumnMapping(array $header): array
    {
        $map = ['date' => 0, 'description' => 1, 'debit' => 2, 'credit' => 3, 'balance' => 4, 'cheque' => 5, 'reference' => 6];

        foreach ($header as $i => $col) {
            $lower = strtolower(trim($col));
            if (in_array($lower, ['date', 'transaction date', 'txn date', 'value date', 'trans date'])) {
                $map['date'] = $i;
            } elseif (in_array($lower, ['description', 'narration', 'particulars', 'details', 'memo'])) {
                $map['description'] = $i;
            } elseif (in_array($lower, ['debit', 'dr', 'debit amount', 'withdrawal', 'withdrawals'])) {
                $map['debit'] = $i;
            } elseif (in_array($lower, ['credit', 'cr', 'credit amount', 'deposit', 'deposits'])) {
                $map['credit'] = $i;
            } elseif (in_array($lower, ['balance', 'closing balance', 'running balance', 'avail bal'])) {
                $map['balance'] = $i;
            } elseif (in_array($lower, ['cheque', 'cheque no', 'cheque number', 'cheque_no'])) {
                $map['cheque'] = $i;
            } elseif (in_array($lower, ['reference', 'ref', 'ref no', 'reference number', 'utr', 'txn id'])) {
                $map['reference'] = $i;
            }
        }

        return $map;
    }

    /**
     * Parse various date formats into Y-m-d
     */
    private function parseDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr)) {
            return null;
        }

        // Try common formats
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'm/d/Y', 'Y/m/d', 'd-M-Y', 'd M Y', 'Y-m-d H:i:s'];
        foreach ($formats as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $dateStr);
            if ($d !== false && $d->format('Y-m-d') !== '1970-01-01') {
                return $d->format('Y-m-d');
            }
        }

        // Try strtotime as last resort
        $ts = strtotime($dateStr);
        if ($ts !== false && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function updateImportStatus(int $importId, string $status, ?string $error = null, ?int $total = null, ?int $matched = null, ?int $unmatched = null): void
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $sets = ['status = ?'];
            $params = [$status];

            if ($error !== null) {
                $sets[] = 'error_message = ?';
                $params[] = $error;
            }
            if ($total !== null) {
                $sets[] = 'total_rows = ?';
                $params[] = $total;
            }
            if ($matched !== null) {
                $sets[] = 'matched_rows = ?';
                $params[] = $matched;
            }
            if ($unmatched !== null) {
                $sets[] = 'unmatched_rows = ?';
                $params[] = $unmatched;
            }

            $params[] = $importId;
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            if ($tid > 1) $params[] = $tid;
            $pdo->prepare("UPDATE bank_statement_imports SET " . implode(', ', $sets) . " WHERE id = ?{$tidSql}")->execute($params);
        } catch (Exception $e) {
            error_log('BankImportService::updateImportStatus error: ' . $e->getMessage());
        }
    }

    /**
     * Auto-match bank transactions with internal transactions.
     * Match criteria: same amount, date within 3 days.
     */
    public function autoMatch(int $importId): array
    {
        try {
            $pdo = $this->getPdo();

            // Get unmatched bank transactions
            $stmt = $pdo->prepare("
                SELECT * FROM bank_transactions
                WHERE import_id = ? AND matched = 0
                ORDER BY transaction_date ASC
            ");
            $stmt->execute([$importId]);
            $bankTxns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $matched = 0;
            $unmatched = 0;

            foreach ($bankTxns as $bt) {
                $amount = max((float)$bt['debit'], (float)$bt['credit']);
                if ($amount <= 0) {
                    $unmatched++;
                    continue;
                }

                // Determine if debit or credit
                $isDebit = (float)$bt['debit'] > 0;

                // Search payment_transactions
                $internal = $this->findMatchingInternal($amount, $bt['transaction_date'], $isDebit);

                if ($internal) {
                    $upStmt = $pdo->prepare("
                        UPDATE bank_transactions
                        SET matched = 1, matched_transaction_id = ?, matched_at = NOW()
                        WHERE id = ?
                    ");
                    $upStmt->execute([$internal['id'], $bt['id']]);
                    $matched++;
                } else {
                    $unmatched++;
                }
            }

            // Update import stats
            $totalMatched = $this->countMatched($importId);
            $totalUnmatched = $this->countUnmatched($importId);
            $this->updateImportStatus($importId, 'completed', null, null, $totalMatched, $totalUnmatched);

            return ['success' => true, 'matched' => $matched, 'unmatched' => $unmatched];
        } catch (Exception $e) {
            error_log('BankImportService::autoMatch error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function findMatchingInternal(float $amount, string $bankDate, bool $isDebit): ?array
    {
        try {
            $pdo = $this->getPdo();

            // Search payment_transactions (our internal records)
            $stmt = $pdo->prepare("
                SELECT id, amount, transaction_date, type
                FROM payment_transactions
                WHERE ABS(amount - ?) < 0.01
                  AND ABS(DATEDIFF(transaction_date, ?)) <= 3
                LIMIT 1
            ");
            $stmt->execute([$amount, $bankDate]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            }

            // Also try daily_cash_book
            $stmt2 = $pdo->prepare("
                SELECT id, amount, transaction_date, type
                FROM daily_cash_book
                WHERE ABS(amount - ?) < 0.01
                  AND ABS(DATEDIFF(transaction_date, ?)) <= 3
                LIMIT 1
            ");
            $stmt2->execute([$amount, $bankDate]);
            return $stmt2->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function countMatched(int $importId): int
    {
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bank_transactions WHERE import_id = ? AND matched = 1");
            $stmt->execute([$importId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function countUnmatched(int $importId): int
    {
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bank_transactions WHERE import_id = ? AND matched = 0");
            $stmt->execute([$importId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getImports(): array
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " WHERE i.tenant_id = ?" : "";
            $params = $tid > 1 ? [$tid] : [];
            $stmt = $pdo->prepare("
                SELECT i.*,
                       b.account_name, b.bank_name
                FROM bank_statement_imports i
                LEFT JOIN bank_accounts_master b ON b.id = i.bank_account_id
                {$tidSql}
                ORDER BY i.created_at DESC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getImport(int $id): ?array
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND i.tenant_id = ?" : "";
            $params = [$id];
            if ($tid > 1) $params[] = $tid;
            $stmt = $pdo->prepare("
                SELECT i.*,
                       b.account_name, b.bank_name
                FROM bank_statement_imports i
                LEFT JOIN bank_accounts_master b ON b.id = i.bank_account_id
                WHERE i.id = ?{$tidSql}
            ");
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getImportTransactions(int $importId, ?string $matched = null): array
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $sql = "SELECT * FROM bank_transactions WHERE import_id = ?{$tidSql}";
            $params = [$importId];

            if ($matched === '1') {
                $sql .= " AND matched = 1{$tidSql}";
                if ($tid > 1) $params[] = $tid;
            } elseif ($matched === '0') {
                $sql .= " AND matched = 0{$tidSql}";
                if ($tid > 1) $params[] = $tid;
            }

            $sql .= " ORDER BY transaction_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getUnmatchedTransactions(?int $importId = null): array
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND bt.tenant_id = ?" : "";
            $sql = "SELECT bt.*, bsi.original_filename
                    FROM bank_transactions bt
                    JOIN bank_statement_imports bsi ON bsi.id = bt.import_id
                    WHERE bt.matched = 0
                    {$tidSql}";
            $params = $tid > 1 ? [$tid] : [];

            if ($importId) {
                $sql .= " AND bt.import_id = ?";
                $params[] = $importId;
            }

            $sql .= " ORDER BY bt.transaction_date DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function manualMatch(int $bankTxnId, int $internalTxnId): bool
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $pdo->prepare("
                UPDATE bank_transactions
                SET matched = 1, matched_transaction_id = ?, matched_at = NOW()
                WHERE id = ? AND matched = 0{$tidSql}
            ");
            $stmt->execute(array_merge([$internalTxnId, $bankTxnId], $tid > 1 ? [$tid] : []));

            if ($stmt->rowCount() > 0) {
                // Update import stats
                $bt = $this->getBankTransaction($bankTxnId);
                if ($bt) {
                    $totalMatched = $this->countMatched($bt['import_id']);
                    $totalUnmatched = $this->countUnmatched($bt['import_id']);
                    $this->updateImportStatus($bt['import_id'], 'completed', null, null, $totalMatched, $totalUnmatched);
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log('BankImportService::manualMatch error: ' . $e->getMessage());
            return false;
        }
    }

    public function unmatch(int $bankTxnId): bool
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $bt = $this->getBankTransaction($bankTxnId);
            if (!$bt) {
                return false;
            }

            $stmt = $pdo->prepare("
                UPDATE bank_transactions
                SET matched = 0, matched_transaction_id = NULL, matched_at = NULL
                WHERE id = ?{$tidSql}
            ");
            $stmt->execute(array_merge([$bankTxnId], $tid > 1 ? [$tid] : []));

            $totalMatched = $this->countMatched($bt['import_id']);
            $totalUnmatched = $this->countUnmatched($bt['import_id']);
            $this->updateImportStatus($bt['import_id'], 'completed', null, null, $totalMatched, $totalUnmatched);

            return true;
        } catch (Exception $e) {
            error_log('BankImportService::unmatch error: ' . $e->getMessage());
            return false;
        }
    }

    private function getBankTransaction(int $id): ?array
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $pdo->prepare("SELECT * FROM bank_transactions WHERE id = ?{$tidSql}");
            $stmt->execute($tid > 1 ? [$id, $tid] : [$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function deleteImport(int $importId): bool
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $pdo->prepare("DELETE FROM bank_statement_imports WHERE id = ?{$tidSql}");
            $stmt->execute($tid > 1 ? [$importId, $tid] : [$importId]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log('BankImportService::deleteImport error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate reconciliation summary
     */
    public function getReconciliationSummary(int $importId): array
    {
        try {
            $pdo = $this->getPdo();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";

            $stmt = $pdo->prepare("
                SELECT
                    COALESCE(SUM(credit), 0) AS total_credits,
                    COALESCE(SUM(debit), 0) AS total_debits,
                    COUNT(*) AS total_transactions,
                    SUM(CASE WHEN matched = 1 THEN 1 ELSE 0 END) AS matched_count,
                    SUM(CASE WHEN matched = 0 THEN 1 ELSE 0 END) AS unmatched_count,
                    SUM(CASE WHEN matched = 1 THEN COALESCE(credit, 0) + COALESCE(debit, 0) ELSE 0 END) AS matched_amount,
                    SUM(CASE WHEN matched = 0 THEN COALESCE(credit, 0) + COALESCE(debit, 0) ELSE 0 END) AS unmatched_amount
                FROM bank_transactions
                WHERE import_id = ?{$tidSql}
            ");
            $stmt->execute([$importId, ...($tid > 1 ? [$tid] : [])]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            $total = (int)($row['total_transactions'] ?? 0);
            $matchedCount = (int)($row['matched_count'] ?? 0);
            $matchRate = $total > 0 ? round(($matchedCount / $total) * 100, 1) : 0;

            return [
                'total_credits'    => (float)($row['total_credits'] ?? 0),
                'total_debits'     => (float)($row['total_debits'] ?? 0),
                'total_transactions' => $total,
                'matched_count'    => $matchedCount,
                'unmatched_count'  => (int)($row['unmatched_count'] ?? 0),
                'matched_amount'   => (float)($row['matched_amount'] ?? 0),
                'unmatched_amount' => (float)($row['unmatched_amount'] ?? 0),
                'match_rate'       => $matchRate,
            ];
        } catch (Exception $e) {
            return [
                'total_credits' => 0, 'total_debits' => 0, 'total_transactions' => 0,
                'matched_count' => 0, 'unmatched_count' => 0,
                'matched_amount' => 0, 'unmatched_amount' => 0, 'match_rate' => 0,
            ];
        }
    }

    /**
     * Get bank accounts for dropdown
     */
    public function getBankAccounts(): array
    {
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->query("SELECT id, account_name, bank_name FROM bank_accounts_master WHERE active = 1 ORDER BY account_name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search internal transactions for manual matching
     */
    public function searchInternalTransactions(string $query, float $amount = 0): array
    {
        try {
            $pdo = $this->getPdo();
            $results = [];

            // Search payment_transactions
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $sql = "SELECT id, amount, transaction_date, type, description
                    FROM payment_transactions
                    WHERE 1=1{$tidSql}";
            $params = [];
            if ($tid > 1) $params[] = $tid;

            if ($amount > 0) {
                $sql .= " AND ABS(amount - ?) < 0.01";
                $params[] = $amount;
            }
            if (!empty($query)) {
                $sql .= " AND (description LIKE ? OR type LIKE ?)";
                $params[] = "%{$query}%";
                $params[] = "%{$query}%";
            }
            $sql .= " ORDER BY transaction_date DESC LIMIT 20";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }
}
