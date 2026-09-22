<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\PayoutBatchService;
use App\Traits\TenantAwareTrait;

/**
 * Mobile API — Associate Bank Bulk Payout Engine (read + export).
 *
 * Staff-only (BaseController::ADMIN_ROLES). List/detail reuse the
 * tenant-aware PayoutBatchService; the CSV export mirrors
 * Admin\PayoutBatchController::exportBankCsv against the live schema
 * (user_bank_accounts → entry snapshots, payout_entries.net_amount,
 * APS-COMM-{batch}-{entry} references).
 */
class PayoutBatchApiController extends BaseController
{
    use TenantAwareTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function staffUser(): ?array
    {
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if ($userId <= 0) return null;
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id, name, role, status FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('PayoutBatchApiController::staffUser: ' . $e->getMessage());
            return null;
        }
        if (!$user || ($user['status'] ?? '') !== 'active') return null;
        if (!in_array(($user['role'] ?? ''), self::ADMIN_ROLES, true)) return null;
        return $user;
    }

    /**
     * GET /api/v2/mobile/payout-batches
     */
    public function index()
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $service = new PayoutBatchService();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $result = $service->getBatches((string)($_GET['status'] ?? ''), (string)($_GET['type'] ?? ''), $page, 20);
            $this->jsonResponse([
                'success' => true,
                'data' => $result['items'],
                'stats' => $service->getStats(),
                'pagination' => ['total' => $result['total'], 'page' => $result['page'], 'total_pages' => $result['total_pages']],
            ]);
        } catch (\Throwable $e) {
            error_log('PayoutBatchApiController::index: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * GET /api/v2/mobile/payout-batches/{id}
     */
    public function detail($id)
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $service = new PayoutBatchService();
            $batch = $service->getBatch((int)$id);
            if (!$batch) {
                return $this->jsonError('Batch not found', 404);
            }
            $entries = $service->getBatchEntries((int)$id, max(1, (int)($_GET['page'] ?? 1)), 50);
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'batch' => $batch,
                    'entries' => $entries['items'],
                    'pagination' => ['total' => $entries['total'], 'page' => $entries['page'], 'total_pages' => $entries['total_pages']],
                    'export_url' => (defined('BASE_URL') ? BASE_URL : '') . '/api/v2/mobile/payout-batches/' . (int)$id . '/export-bank-csv?format=generic',
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('PayoutBatchApiController::detail: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * GET /api/v2/mobile/payout-batches/{id}/export-bank-csv?format=generic|icici|hdfc|sbi
     * Streams the Corporate NetBanking bulk-upload CSV.
     */
    public function exportCsv($id)
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $id = (int)$id;
            $format = strtolower(trim($_GET['format'] ?? 'generic'));
            if (!in_array($format, ['generic', 'icici', 'hdfc', 'sbi'], true)) $format = 'generic';

            $service = new PayoutBatchService();
            $batch = $service->getBatch($id);
            if (!$batch) {
                return $this->jsonError('Batch not found', 404);
            }

            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = (int)$this->tenantId();
            $tWhere = $tid > 1 ? ' AND pe.tenant_id = ?' : '';
            $stmt = $pdo->prepare(
                "SELECT pe.*, u.name AS user_name,
                        COALESCE(uba.account_holder_name, pe.beneficiary_name, u.name, '') AS bene_name,
                        COALESCE(uba.account_number, pe.beneficiary_account, '') AS account_no,
                        COALESCE(uba.ifsc_code, pe.beneficiary_ifsc, '') AS ifsc_code
                 FROM payout_entries pe
                 LEFT JOIN users u ON u.id = pe.beneficiary_user_id
                 LEFT JOIN user_bank_accounts uba
                       ON uba.user_id = pe.beneficiary_user_id AND uba.is_primary = 1
                 WHERE pe.batch_id = ? AND pe.status != 'cancelled'{$tWhere}
                 ORDER BY pe.id ASC"
            );
            $stmt->execute($tid > 1 ? [$id, $tid] : [$id]);
            $entries = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            try {
                $persistRef = $pdo->prepare("UPDATE payout_entries SET payment_reference = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            } catch (\Throwable $e) {
                error_log('PayoutBatchApiController::exportCsv ref prepare: ' . $e->getMessage());
                $persistRef = null;
            }

            $rows = [];
            $sr = 0;
            foreach ($entries as $e) {
                $entryId = (int)$e['id'];
                $net = round((float)($e['net_amount'] ?? 0), 2);
                $ref = trim((string)($e['payment_reference'] ?? ''));
                if ($ref === '') {
                    $ref = 'APS-COMM-' . $id . '-' . $entryId;
                    if ($persistRef) {
                        try {
                            $persistRef->execute($tid > 1 ? [$ref, $entryId, $tid] : [$ref, $entryId]);
                        } catch (\Throwable $ex) {
                            error_log('PayoutBatchApiController::exportCsv persist ref: ' . $ex->getMessage());
                        }
                    }
                }
                $sr++;
                $rows[] = [
                    'sr'      => $sr,
                    'name'    => trim((string)($e['bene_name'] ?? '')),
                    'account' => trim((string)($e['account_no'] ?? '')),
                    'ifsc'    => strtoupper(trim((string)($e['ifsc_code'] ?? ''))),
                    'amount'  => $net,
                    'type'    => $net >= 200000 ? 'RTGS' : 'NEFT',
                    'ref'     => $ref,
                    'remarks' => 'APS Commission ' . ($batch['batch_name'] ?? ('Batch ' . $id)),
                ];
            }

            if ($format === 'sbi') {
                $header = ['Payment Type', 'Beneficiary Account No', 'Beneficiary IFSC', 'Amount', 'Beneficiary Name', 'Customer Reference', 'Narration'];
                $csvRows = [$header];
                foreach ($rows as $r) {
                    $csvRows[] = [$r['type'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['name'], $r['ref'], $r['remarks']];
                }
            } elseif ($format === 'icici') {
                $header = ['Beneficiary Name', 'Account Number', 'IFSC Code', 'Amount', 'Payment Type(NEFT/RTGS/IMPS)', 'Customer Reference', 'Remarks'];
                $csvRows = [$header];
                foreach ($rows as $r) {
                    $csvRows[] = [$r['name'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['type'], $r['ref'], $r['remarks']];
                }
            } elseif ($format === 'hdfc') {
                $header = ['Sr No', 'Beneficiary Name', 'Beneficiary Account No', 'IFSC', 'Amount', 'Payment Type', 'Reference No', 'Narration'];
                $csvRows = [$header];
                foreach ($rows as $r) {
                    $csvRows[] = [$r['sr'], $r['name'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['type'], $r['ref'], $r['remarks']];
                }
            } else {
                $header = ['SrNo', 'BeneficiaryName', 'AccountNo', 'IFSC', 'Amount', 'PaymentType', 'Remarks'];
                $csvRows = [$header];
                foreach ($rows as $r) {
                    $csvRows[] = [$r['sr'], $r['name'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['type'], $r['ref'] . ' | ' . $r['remarks']];
                }
            }

            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="payout_batch_' . $id . '_' . $format . '_' . date('Y-m-d_His') . '.csv"');
            $fp = fopen('php://output', 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($csvRows as $csvRow) {
                fputcsv($fp, $csvRow);
            }
            fclose($fp);
            exit;
        } catch (\Throwable $e) {
            error_log('PayoutBatchApiController::exportCsv: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }
}
