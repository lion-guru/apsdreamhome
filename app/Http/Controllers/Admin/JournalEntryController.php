<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Accounting\JournalEntryService;
use App\Core\Database;
use App\Core\Middleware\TenantContext;
use Exception;

class JournalEntryController extends AdminController
{
    protected $db;
    protected $journalService;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database\Database::getInstance();
        $this->journalService = new JournalEntryService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $tid = TenantContext::getId();
            $where = $tid > 1 ? "WHERE je.tenant_id = ?" : "";
            $params = $tid > 1 ? [$tid] : [];

            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $fromDate = $_GET['from_date'] ?? '';
            $toDate = $_GET['to_date'] ?? '';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            $conditions = [];
            if ($tid > 1) $conditions[] = "je.tenant_id = $tid";
            if ($search) {
                $conditions[] = "(je.journal_number LIKE ? OR je.description LIKE ? OR je.reference_number LIKE ?)";
                $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
            }
            if ($status) {
                $conditions[] = "je.status = ?";
                $params[] = $status;
            }
            if ($type) {
                $conditions[] = "je.entry_type = ?";
                $params[] = $type;
            }
            if ($fromDate) {
                $conditions[] = "je.entry_date >= ?";
                $params[] = $fromDate;
            }
            if ($toDate) {
                $conditions[] = "je.entry_date <= ?";
                $params[] = $toDate;
            }

            $whereClause = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            // Count total
            $countSql = "SELECT COUNT(*) FROM journal_entries je $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Fetch entries
            $sql = "SELECT je.*, u.name as created_by_name 
                    FROM journal_entries je
                    LEFT JOIN users u ON u.id = je.created_by
                    $whereClause
                    ORDER BY je.entry_date DESC, je.id DESC
                    LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $entries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Journal Entries - APS Dream Home',
                'active_page' => 'journal_entries',
                'entries' => $entries,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type,
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ],
                'statuses' => ['draft', 'approved', 'rejected'],
                'types' => ['manual', 'system', 'adjustment', 'closing']
            ];

            return $this->render('admin/journal_entries/index', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load Journal Entries: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        $data = [
            'page_title' => 'Create Journal Entry',
            'active_page' => 'journal_entries',
            'accounts' => $this->getAccounts(),
            'entry_types' => ['manual', 'system', 'adjustment', 'closing'],
            'source_documents' => ['invoice', 'payment', 'expense', 'transfer', 'adjustment', 'opening'],
            'today' => date('Y-m-d')
        ];
        return $this->render('admin/journal_entries/create', $data);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            $lines = [];
            $lineCount = 0;
            foreach ($data['lines'] ?? [] as $line) {
                if (!empty($line['account_id'])) {
                    $debit = !empty($line['debit']) ? (float)$line['debit'] : 0;
                    $credit = !empty($line['credit']) ? (float)$line['credit'] : 0;
                    if ($debit > 0 || $credit > 0) {
                        $lines[] = [
                            'account_id' => (int)$line['account_id'],
                            'debit' => $debit,
                            'credit' => $credit,
                            'description' => $line['description'] ?? ''
                        ];
                        $lineCount++;
                    }
                }
            }

            if ($lineCount < 2) {
                return $this->jsonError('A journal entry must have at least 2 lines (one debit, one credit)', 400);
            }

            $jeData = [
                'journal_number' => $data['journal_number'] ?? '',
                'entry_date' => $data['entry_date'] ?? date('Y-m-d'),
                'narration' => $data['narration'] ?? '',
                'reference_type' => $data['reference_type'] ?? 'manual',
                'reference_id' => !empty($data['reference_id']) ? (int)$data['reference_id'] : null,
                'lines' => $lines
            ];

            $jeId = $this->journalService->postJournalEntry($jeData);

            $this->logActivity('journal_entry_created', [
                'je_id' => $jeId,
                'journal_number' => $jeData['journal_number'],
                'total_lines' => $lineCount
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Journal entry created successfully',
                'je_id' => $jeId,
                'redirect' => BASE_URL . '/admin/journal-entries/' . $jeId
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to create journal entry: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $tid = TenantContext::getId();
            $entry = $this->db->fetchOne(
                "SELECT je.*, u.name as created_by_name 
                 FROM journal_entries je
                 LEFT JOIN users u ON u.id = je.created_by
                 WHERE je.id = ?" . ($tid > 1 ? " AND je.tenant_id = ?" : ""),
                $tid > 1 ? [$id, $tid] : [$id]
            );

            if (!$entry) {
                $this->setFlash('error', 'Journal entry not found');
                return $this->redirect('/admin/journal-entries');
            }

            $lines = $this->db->fetchAll(
                "SELECT jel.*, coa.account_code, coa.account_name, coa.account_type
                 FROM journal_entry_lines jel
                 JOIN chart_of_accounts coa ON coa.id = jel.account_id
                 WHERE jel.journal_entry_id = ?
                 ORDER BY jel.line_order, jel.id",
                [$id]
            );

            $data = [
                'page_title' => 'Journal Entry #' . $entry['journal_number'],
                'active_page' => 'journal_entries',
                'entry' => $entry,
                'lines' => $lines
            ];
            return $this->render('admin/journal_entries/show', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load journal entry: ' . $e->getMessage());
            return $this->redirect('/admin/journal-entries');
        }
    }

    public function approve($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $tid = TenantContext::getId();
            $entry = $this->db->fetchOne(
                "SELECT * FROM journal_entries WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$id, $tid] : [$id]
            );
            if (!$entry) {
                return $this->jsonError('Journal entry not found', 404);
            }
            if ($entry['status'] !== 'draft') {
                return $this->jsonError('Only draft entries can be approved', 400);
            }

            $this->db->update('journal_entries', [
                'status' => 'approved',
                'approved_by' => $_SESSION['user_id'] ?? 1,
                'approval_date' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);

            $this->logActivity('journal_entry_approved', ['je_id' => $id]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Journal entry approved successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to approve: ' . $e->getMessage(), 500);
        }
    }

    public function reject($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $tid = TenantContext::getId();
            $entry = $this->db->fetchOne(
                "SELECT * FROM journal_entries WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$id, $tid] : [$id]
            );
            if (!$entry) {
                return $this->jsonError('Journal entry not found', 404);
            }
            if ($entry['status'] !== 'draft') {
                return $this->jsonError('Only draft entries can be rejected', 400);
            }

            $this->db->update('journal_entries', [
                'status' => 'rejected',
                'approved_by' => $_SESSION['user_id'] ?? 1,
                'approval_date' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);

            $this->logActivity('journal_entry_rejected', ['je_id' => $id]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Journal entry rejected successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to reject: ' . $e->getMessage(), 500);
        }
    }

    private function getAccounts()
    {
        $tid = TenantContext::getId();
        $sql = "SELECT id, account_code, account_name, account_type, account_category 
                FROM chart_of_accounts 
                WHERE is_active = 1";
        $params = [];
        if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
        $sql .= " ORDER BY account_type, account_code";
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}