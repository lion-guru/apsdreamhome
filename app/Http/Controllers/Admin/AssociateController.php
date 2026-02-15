<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class AssociateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List all associates
     */
    public function index()
    {
        $sql = "
            SELECT a.*, u.uname as name, u.uemail as email, u.uphone as phone,
                   s.uname as sponsor_name,
                   (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.id) as downline_count
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            LEFT JOIN associates sa ON a.sponsor_id = sa.id
            LEFT JOIN user s ON sa.user_id = s.uid
            ORDER BY a.created_at DESC
        ";
        $associates = $this->db->fetchAll($sql);

        return $this->render('admin/associates/index', [
            'associates' => $associates,
            'page_title' => $this->mlSupport->translate('Associates Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show create associate form
     */
    public function create()
    {
        // Fetch potential sponsors (all active associates)
        $sponsors = $this->db->fetchAll("
            SELECT a.id, u.uname as name
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            WHERE a.status = 'active'
        ");

        return $this->render('admin/associates/create', [
            'sponsors' => $sponsors,
            'page_title' => $this->mlSupport->translate('Add New Associate') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store new associate
     */
    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->all();

        // Sanitize data
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = h($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        $data = $sanitizedData;

        // Validation
        $errors = [];
        if (empty($data['name'])) $errors[] = $this->mlSupport->translate('Name is required');
        if (empty($data['email'])) $errors[] = $this->mlSupport->translate('Email is required');
        if (empty($data['phone'])) $errors[] = $this->mlSupport->translate('Phone is required');
        if (empty($data['password'])) $errors[] = $this->mlSupport->translate('Password is required');

        if (!empty($errors)) {
            $this->setFlash('error', \implode(', ', $errors));
            return $this->back();
        }

        try {
            $this->db->beginTransaction();

            // 1. Create User entry
            $passwordHash = \password_hash($data['password'], \PASSWORD_DEFAULT);
            $userId = $this->db->insert('user', [
                'uname' => $data['name'],
                'uemail' => $data['email'],
                'uphone' => $data['phone'],
                'upass' => $passwordHash,
                'utype' => 'associate',
                'role' => 'associate',
                'status' => 'active',
                'join_date' => \date('Y-m-d H:i:s')
            ]);

            // 2. Create Associate entry
            $associateCode = 'AS' . \str_pad($userId, 5, '0', \STR_PAD_LEFT);
            $sponsor_id = !empty($data['sponsor_id']) ? (int)$data['sponsor_id'] : null;
            $commission_rate = !empty($data['commission_rate']) ? (float)$data['commission_rate'] : 0.00;

            $associateId = $this->db->insert('associates', [
                'user_id' => $userId,
                'sponsor_id' => $sponsor_id,
                'associate_code' => $associateCode,
                'commission_rate' => $commission_rate,
                'status' => 'active',
                'created_at' => \date('Y-m-d H:i:s'),
                'updated_at' => \date('Y-m-d H:i:s')
            ]);

            $this->db->commit();

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Create Associate', "Created associate: " . h($data['name']) . " (ID: $associateId)");

            $this->setFlash('success', $this->mlSupport->translate('Associate created successfully.'));
            return $this->redirect('/admin/associates');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', $this->mlSupport->translate('Error creating associate: ') . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Show edit associate form
     */
    public function edit($id)
    {
        $id = intval($id);
        $associate = $this->model('Associate')->getAssociateById($id);
        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            return $this->redirect('/admin/associates');
        }

        $sponsors = $this->db->fetchAll("
            SELECT a.id, u.uname as name
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            WHERE a.status = 'active' AND a.id != ?
        ", [$id]);

        return $this->render('admin/associates/edit', [
            'associate' => $associate,
            'sponsors' => $sponsors,
            'page_title' => $this->mlSupport->translate('Edit Associate') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update associate
     */
    public function update($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->all();

        try {
            $this->db->beginTransaction();

            $associate = $this->model('Associate')->getAssociateById($id);
            if (!$associate) {
                $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
                return $this->redirect('/admin/associates');
            }

            // 1. Update User entry
            $this->db->update('user', [
                'uname' => $data['name'],
                'uemail' => $data['email'],
                'uphone' => $data['phone']
            ], ['uid' => $associate['user_id']]);

            // Update password if provided
            if (!empty($data['password'])) {
                $passwordHash = \password_hash($data['password'], \PASSWORD_DEFAULT);
                $this->db->update('user', ['upass' => $passwordHash], ['uid' => $associate['user_id']]);
            }

            // 2. Update Associate entry
            $sponsor_id = !empty($data['sponsor_id']) ? (int)$data['sponsor_id'] : null;
            $commission_rate = !empty($data['commission_rate']) ? (float)$data['commission_rate'] : 0.00;

            $this->db->update('associates', [
                'sponsor_id' => $sponsor_id,
                'commission_rate' => $commission_rate,
                'status' => $data['status'],
                'updated_at' => \date('Y-m-d H:i:s')
            ], ['id' => $id]);

            $this->db->commit();

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Update Associate', "Updated associate: " . h($data['name']) . " (ID: $id)");

            $this->setFlash('success', $this->mlSupport->translate('Associate updated successfully.'));
            return $this->redirect('/admin/associates');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', $this->mlSupport->translate('Error updating associate: ') . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Delete associate
     */
    public function destroy($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('/admin/associates');
        }

        try {
            $this->db->beginTransaction();

            $associate = $this->model('Associate')->getAssociateById($id);
            if (!$associate) {
                $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
                return $this->redirect('/admin/associates');
            }

            // Check if has downline
            $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM associates WHERE sponsor_id = ?", [$id]);
            $downlineCount = $result ? $result['cnt'] : 0;

            if ($downlineCount > 0) {
                $this->setFlash('error', $this->mlSupport->translate('Cannot delete associate with downline members.'));
                return $this->redirect('/admin/associates');
            }

            // Delete associate and user
            $this->db->delete('associates', ['id' => $id]);
            $this->db->delete('user', ['uid' => $associate['user_id']]);

            $this->db->commit();

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->logActivity('Delete Associate', "Deleted associate ID: $id");

            $this->setFlash('success', $this->mlSupport->translate('Associate deleted successfully.'));
            return $this->redirect('/admin/associates');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', $this->mlSupport->translate('Error deleting associate: ') . h($e->getMessage()));
            return $this->redirect('/admin/associates');
        }
    }

    /**
     * Show associate details
     */
    public function show($id)
    {
        $id = intval($id);
        $associateModel = $this->model('Associate');
        $associate = $associateModel->getAssociateById($id);
        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            return $this->redirect('/admin/associates');
        }

        $stats = $associateModel->getBusinessStats($id);
        $team = $associateModel->getTeamMembers($id);

        return $this->render('admin/associates/show', [
            'associate' => $associate,
            'stats' => $stats,
            'team' => $team,
            'page_title' => $this->mlSupport->translate('Associate Details') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show associate commissions report
     */
    public function commissions($id)
    {
        $id = intval($id);
        $associateModel = $this->model('Associate');
        $associate = $associateModel->getAssociateById($id);
        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            return $this->redirect('/admin/associates');
        }

        $commissions = $associateModel->getAssociateEarnings($id);
        $summary = $associateModel->getCommissionSummary($id);

        return $this->render('admin/associates/commissions', [
            'associate' => $associate,
            'commissions' => $commissions,
            'summary' => $summary,
            'page_title' => $this->mlSupport->translate('Commission Report') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show associate tree view
     */
    public function tree($id = null)
    {
        $id = $id ? intval($id) : null;
        $associateModel = $this->model('Associate');
        if (!$id) {
            // Find a root associate or use the first one
            $root = $this->db->fetch("SELECT id FROM associates ORDER BY id ASC LIMIT 1");
            $id = $root ? $root['id'] : 0;
        }

        if (!$id) {
            $this->setFlash('error', $this->mlSupport->translate('No associates found to show tree.'));
            return $this->redirect('/admin/associates');
        }

        $associate = $associateModel->getAssociateById($id);
        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            return $this->redirect('/admin/associates');
        }

        $hierarchy = $associateModel->getDownlineHierarchy($id);

        return $this->render('admin/associates/tree', [
            'associate' => $associate,
            'hierarchy' => $hierarchy,
            'page_title' => $this->mlSupport->translate('Associate Tree View') . ' - ' . $this->getConfig('app_name')
        ]);
    }
}
