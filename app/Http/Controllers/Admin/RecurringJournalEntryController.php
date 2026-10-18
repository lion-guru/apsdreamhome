<?php
/**
 * RecurringJournalEntryController — Recurring Journal Entry management (Gap 27).
 *
 * Template CRUD + manual execute + cron-style process-due.
 * Routes: /admin/recurring-journal-entries (index/create/store/show/execute/{id}/process-due/delete/{id})
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\RecurringJournalEntryService;
use Exception;

class RecurringJournalEntryController extends AdminController
{
    protected $rjeService;

    public function __construct()
    {
        parent::__construct();
        $this->rjeService = new RecurringJournalEntryService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? 'active';
            $rjes = $this->rjeService->getAll($status);
            $stats = $this->rjeService->getStats();
            return $this->render('admin/recurring_journal_entries/index', [
                'page_title' => 'Recurring Journal Entries - APS Dream Home',
                'active_page' => 'recurring_journal_entries',
                'rjes' => $rjes,
                'stats' => $stats,
                'filters' => ['status' => $status],
                'statuses' => ['active', 'inactive'],
                'frequencies' => ['weekly', 'monthly', 'quarterly', 'yearly'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load recurring JEs: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        try {
            $accounts = $this->db->fetchAll(
                "SELECT id, account_code, account_name, account_type FROM chart_of_accounts WHERE is_active = 1 ORDER BY account_code"
            ) ?: [];
            return $this->render('admin/recurring_journal_entries/create', [
                'page_title' => 'Create Recurring Journal Entry',
                'active_page' => 'recurring_journal_entries',
                'accounts' => $accounts,
                'today' => date('Y-m-d'),
                'frequencies' => ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'],
                'entryTypes' => ['manual' => 'Manual', 'system' => 'System', 'adjustment' => 'Adjustment', 'closing' => 'Closing'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/recurring-journal-entries');
        }
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            if (empty($_POST['template_name'])) {
                return $this->jsonError('Template name is required', 400);
            }
            $rjeId = $this->rjeService->create($_POST);
            $this->logActivity('recurring_je_created', ['rje_id' => $rjeId, 'name' => $_POST['template_name']]);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Recurring journal entry created',
                'rje_id' => $rjeId,
                'redirect' => BASE_URL . '/admin/recurring-journal-entries/' . $rjeId,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $rje = $this->rjeService->getById((int)$id);
            if (!$rje) {
                $this->setFlash('error', 'Recurring journal entry not found');
                return $this->redirect('/admin/recurring-journal-entries');
            }
            return $this->render('admin/recurring_journal_entries/show', [
                'page_title' => 'Recurring JE: ' . $rje['template_name'],
                'active_page' => 'recurring_journal_entries',
                'rje' => $rje,
                'lines' => $rje['lines'] ?? [],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/recurring-journal-entries');
        }
    }

    public function execute($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $jeId = $this->rjeService->execute((int)$id);
            $this->logActivity('recurring_je_executed', ['rje_id' => $id, 'je_id' => $jeId]);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Journal entry posted',
                'journal_entry_id' => $jeId,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function processDue()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $result = $this->rjeService->processDue();
            $this->logActivity('recurring_je_process_due', $result);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Processed ' . $result['processed'] . ' recurring JE(s)',
                'result' => $result,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $ok = $this->rjeService->delete((int)$id);
            if (!$ok) {
                return $this->jsonError('Recurring JE not found', 404);
            }
            $this->logActivity('recurring_je_deleted', ['rje_id' => $id]);
            return $this->jsonResponse(['success' => true, 'message' => 'Recurring JE deleted']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}
