<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database;
use App\Core\Middleware\TenantContext;
use Exception;

class ChartOfAccountController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database\Database::getInstance();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $search = $_GET['search'] ?? '';
            $type = $_GET['type'] ?? '';
            $active = isset($_GET['active']) && $_GET['active'] !== '' ? (int)$_GET['active'] : '';

            $sql = "SELECT coa.*, 
                           (SELECT COUNT(*) FROM journal_entry_lines jel WHERE jel.account_id = coa.id) as usage_count
                    FROM chart_of_accounts coa";
            $conditions = [];
            $params = [];

            if ($search) {
                $conditions[] = "(coa.account_code LIKE ? OR coa.account_name LIKE ? OR coa.description LIKE ?)";
                $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
            }
            if ($type) {
                $conditions[] = "coa.account_type = ?";
                $params[] = $type;
            }
            if ($active !== '') {
                $conditions[] = "coa.is_active = ?";
                $params[] = $active;
            }
            if ($conditions) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            $sql .= " ORDER BY coa.account_type, coa.account_code";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $accounts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Chart of Accounts - APS Dream Home',
                'active_page' => 'chart_of_accounts',
                'accounts' => $accounts,
                'filters' => [
                    'search' => $search,
                    'type' => $type,
                    'active' => $active
                ],
                'types' => ['asset', 'liability', 'equity', 'income', 'expense']
            ];

            return $this->render('admin/chart_of_accounts/index', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load Chart of Accounts: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        $data = [
            'page_title' => 'Create Account - Chart of Accounts',
            'active_page' => 'chart_of_accounts',
            'types' => ['asset', 'liability', 'equity', 'income', 'expense'],
            'categories' => [
                'current_asset' => 'Current Asset',
                'fixed_asset' => 'Fixed Asset',
                'current_liability' => 'Current Liability',
                'long_term_liability' => 'Long Term Liability',
                'owner_equity' => 'Owner Equity',
                'revenue' => 'Revenue',
                'operating_expense' => 'Operating Expense',
                'non_operating_expense' => 'Non-Operating Expense'
            ],
            'accounts' => $this->getAccountsForParent()
        ];
        return $this->render('admin/chart_of_accounts/create', $data);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            $required = ['account_code', 'account_name', 'account_type', 'account_category'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Check duplicate account_code
            $existing = $this->db->fetchOne(
                "SELECT id FROM chart_of_accounts WHERE account_code = ?",
                [$data['account_code']]
            );
            if ($existing) {
                return $this->jsonError('Account code already exists', 400);
            }

            $insertData = [
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'account_type' => $data['account_type'],
                'account_category' => $data['account_category'],
                'parent_account_id' => !empty($data['parent_account_id']) ? (int)$data['parent_account_id'] : null,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'opening_balance' => !empty($data['opening_balance']) ? (float)$data['opening_balance'] : 0.00,
                'description' => $data['description'] ?? ''
            ];

            $this->db->insert('chart_of_accounts', $insertData);
            $accountId = (int)$this->db->lastInsertId();

            $this->logActivity('coa_created', [
                'account_id' => $accountId,
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name']
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Account created successfully',
                'account_id' => $accountId,
                'redirect' => BASE_URL . '/admin/chart-of-accounts'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to create account: ' . $e->getMessage(), 500);
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        try {
            $account = $this->db->fetchOne(
                "SELECT * FROM chart_of_accounts WHERE id = ?",
                [$id]
            );

            if (!$account) {
                $this->setFlash('error', 'Account not found');
                return $this->redirect('/admin/chart-of-accounts');
            }

            $data = [
                'page_title' => 'Edit Account - Chart of Accounts',
                'active_page' => 'chart_of_accounts',
                'account' => $account,
                'types' => ['asset', 'liability', 'equity', 'income', 'expense'],
                'categories' => [
                    'current_asset' => 'Current Asset',
                    'fixed_asset' => 'Fixed Asset',
                    'current_liability' => 'Current Liability',
                    'long_term_liability' => 'Long Term Liability',
                    'owner_equity' => 'Owner Equity',
                    'revenue' => 'Revenue',
                    'operating_expense' => 'Operating Expense',
                    'non_operating_expense' => 'Non-Operating Expense'
                ],
                'accounts' => $this->getAccountsForParent($id)
            ];
            return $this->render('admin/chart_of_accounts/edit', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load account: ' . $e->getMessage());
            return $this->redirect('/admin/chart-of-accounts');
        }
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            $account = $this->db->fetchOne(
                "SELECT * FROM chart_of_accounts WHERE id = ?",
                [$id]
            );
            if (!$account) {
                return $this->jsonError('Account not found', 404);
            }

            // Check duplicate account_code (excluding self)
            $existing = $this->db->fetchOne(
                "SELECT id FROM chart_of_accounts WHERE account_code = ? AND id != ?",
                [$data['account_code'], $id]
            );
            if ($existing) {
                return $this->jsonError('Account code already exists', 400);
            }

            // Prevent circular parent reference
            if (!empty($data['parent_account_id']) && (int)$data['parent_account_id'] === (int)$id) {
                return $this->jsonError('Account cannot be its own parent', 400);
            }

            $updateData = [
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'account_type' => $data['account_type'],
                'account_category' => $data['account_category'],
                'parent_account_id' => !empty($data['parent_account_id']) ? (int)$data['parent_account_id'] : null,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'opening_balance' => !empty($data['opening_balance']) ? (float)$data['opening_balance'] : 0.00,
                'description' => $data['description'] ?? ''
            ];

            $this->db->update('chart_of_accounts', $updateData, 'id = ?', [$id]);

            $this->logActivity('coa_updated', [
                'account_id' => $id,
                'account_code' => $data['account_code'],
                'changes' => $updateData
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Account updated successfully',
                'redirect' => BASE_URL . '/admin/chart-of-accounts'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to update account: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $account = $this->db->fetchOne(
                "SELECT * FROM chart_of_accounts WHERE id = ?",
                [$id]
            );
            if (!$account) {
                return $this->jsonError('Account not found', 404);
            }

            // Check if account has journal entry lines
            $usage = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM journal_entry_lines jel
                 JOIN journal_entries je ON je.id = jel.journal_entry_id
                 WHERE jel.account_id = ?",
                [$id]
            );
            if ($usage > 0) {
                return $this->jsonError('Cannot delete account with existing journal entries. Deactivate instead.', 400);
            }

            $this->db->delete('chart_of_accounts', 'id = ?', [$id]);

            $this->logActivity('coa_deleted', [
                'account_id' => $id,
                'account_code' => $account['account_code'],
                'account_name' => $account['account_name']
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to delete account: ' . $e->getMessage(), 500);
        }
    }

    private function getAccountsForParent($excludeId = null)
    {
        $sql = "SELECT id, account_code, account_name, account_type FROM chart_of_accounts WHERE is_active = 1";
        $params = [];
        if ($excludeId) { $sql .= " AND id != ?"; $params[] = $excludeId; }
        $sql .= " ORDER BY account_code";
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}