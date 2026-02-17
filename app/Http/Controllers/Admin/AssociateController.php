<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class AssociateController extends AdminController
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        // Fetch associates with user details
        $sql = "SELECT a.*, u.name, u.email, u.phone, u.status as user_status, u.created_at as joined_date,
                       (SELECT name FROM users WHERE id = a.sponsor_id) as sponsor_name,
                       (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.user_id) as downline_count
                FROM associates a
                JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT $per_page OFFSET $offset";

        $associates = $this->db->fetchAll($sql);

        // Count total for pagination
        $count_sql = "SELECT COUNT(*) as total FROM associates";
        $total = $this->db->fetchOne($count_sql)['total'];

        $data = [
            'page_title' => $this->mlSupport->translate('Associates Management'),
            'associates' => $associates,
            'total_pages' => ceil($total / $per_page),
            'current_page' => $page,
            'mlSupport' => $this->mlSupport
        ];

        $this->view('admin/associates/index', $data);
    }

    public function create()
    {
        // Fetch potential sponsors
        $sponsors = $this->db->fetchAll("SELECT u.id, u.name 
                                         FROM users u 
                                         JOIN associates a ON u.id = a.user_id 
                                         WHERE u.status = 'active'");

        $data = [
            'page_title' => $this->mlSupport->translate('Add Associate'),
            'sponsors' => $sponsors,
            'mlSupport' => $this->mlSupport
        ];

        $this->view('admin/associates/create', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
                $this->redirect('/admin/associates/create');
                return;
            }

            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $sponsor_id = !empty($_POST['sponsor_id']) ? $_POST['sponsor_id'] : null;
            $commission_rate = $_POST['commission_rate'] ?? 0.00;

            // Validate
            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $this->setFlash('error', $this->mlSupport->translate('All fields are required.'));
                $this->redirect('/admin/associates/create');
                return;
            }

            // Check if email exists
            $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                $this->setFlash('error', $this->mlSupport->translate('Email already exists.'));
                $this->redirect('/admin/associates/create');
                return;
            }

            try {
                $this->db->beginTransaction();

                // 1. Create User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $this->db->query(
                    "INSERT INTO users (name, email, phone, password, role_name, status, created_at) VALUES (?, ?, ?, ?, 'Associate', 'active', NOW())",
                    [$name, $email, $phone, $hashed_password]
                );
                $user_id = $this->db->lastInsertId();

                // 2. Create Associate Record
                $associate_code = 'ASC' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
                $this->db->query(
                    "INSERT INTO associates (user_id, associate_code, sponsor_id, commission_rate, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'active', NOW(), NOW())",
                    [$user_id, $associate_code, $sponsor_id, $commission_rate]
                );

                $this->db->commit();
                $this->setFlash('success', $this->mlSupport->translate('Associate created successfully.'));
                $this->redirect('/admin/associates');
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->setFlash('error', $this->mlSupport->translate('Error creating associate: ') . $e->getMessage());
                $this->redirect('/admin/associates/create');
            }
        }
    }

    public function edit($id)
    {
        // Fetch associate details
        $sql = "SELECT a.*, u.name, u.email, u.phone 
                FROM associates a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.id = ?";
        $associate = $this->db->fetchOne($sql, [$id]);

        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            $this->redirect('/admin/associates');
            return;
        }

        // Fetch potential sponsors (excluding self)
        $sponsors = $this->db->fetchAll("SELECT u.id, u.name 
                                         FROM users u 
                                         JOIN associates a ON u.id = a.user_id 
                                         WHERE u.status = 'active' AND a.user_id != ?", [$associate['user_id']]);

        $data = [
            'page_title' => $this->mlSupport->translate('Edit Associate'),
            'associate' => $associate,
            'sponsors' => $sponsors,
            'mlSupport' => $this->mlSupport
        ];

        $this->view('admin/associates/edit', $data);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
                $this->redirect('/admin/associates/edit/' . $id);
                return;
            }

            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $sponsor_id = !empty($_POST['sponsor_id']) ? $_POST['sponsor_id'] : null;
            $commission_rate = $_POST['commission_rate'] ?? 0.00;
            $status = $_POST['status'] ?? 'active';
            $password = $_POST['password'] ?? '';

            // Fetch associate to get user_id
            $associate = $this->db->fetchOne("SELECT user_id FROM associates WHERE id = ?", [$id]);
            if (!$associate) {
                $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
                $this->redirect('/admin/associates');
                return;
            }
            $user_id = $associate['user_id'];

            try {
                $this->db->beginTransaction();

                // 1. Update User
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->query(
                        "UPDATE users SET name = ?, email = ?, phone = ?, status = ?, password = ? WHERE id = ?",
                        [$name, $email, $phone, $status, $hashed_password, $user_id]
                    );
                } else {
                    $this->db->query(
                        "UPDATE users SET name = ?, email = ?, phone = ?, status = ? WHERE id = ?",
                        [$name, $email, $phone, $status, $user_id]
                    );
                }

                // 2. Update Associate
                $this->db->query(
                    "UPDATE associates SET sponsor_id = ?, commission_rate = ?, status = ?, updated_at = NOW() WHERE id = ?",
                    [$sponsor_id, $commission_rate, $status, $id]
                );

                $this->db->commit();
                $this->setFlash('success', $this->mlSupport->translate('Associate updated successfully.'));
                $this->redirect('/admin/associates');
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->setFlash('error', $this->mlSupport->translate('Error updating associate: ') . $e->getMessage());
                $this->redirect('/admin/associates/edit/' . $id);
            }
        }
    }

    public function show($id)
    {
        // Fetch associate details
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.status, u.created_at,
                       (SELECT name FROM users WHERE id = a.sponsor_id) as sponsor_name,
                       (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.user_id) as direct_downline_count,
                       (SELECT COALESCE(SUM(commission_amount), 0) FROM commissions WHERE associate_id = a.user_id AND status = 'paid') as total_earnings
                FROM associates a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = ?";
        $associate = $this->db->fetchOne($sql, [$id]);

        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            $this->redirect('/admin/associates');
            return;
        }

        // Fetch additional stats
        // This logic might need adjustment based on actual table structure for sales/team
        $stats = [
            'team' => [
                'total_team_members' => $this->getDownlineCount($associate['user_id'])
            ]
        ];

        // Fetch direct team members
        $team_sql = "SELECT a.*, u.name, u.email, u.phone, a.associate_code,
                            (SELECT COALESCE(SUM(commission_amount), 0) FROM commissions WHERE associate_id = a.user_id AND status = 'paid') as total_earnings,
                            (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.user_id) as total_sales
                     FROM associates a
                     JOIN users u ON a.user_id = u.id
                     WHERE a.sponsor_id = ?
                     ORDER BY a.created_at DESC";
        $team = $this->db->fetchAll($team_sql, [$associate['user_id']]);

        $data = [
            'page_title' => $this->mlSupport->translate('Associate Details'),
            'associate' => $associate,
            'stats' => $stats,
            'team' => $team,
            'mlSupport' => $this->mlSupport
        ];

        $this->view('admin/associates/show', $data);
    }

    public function commissions($id)
    {
        // Fetch associate details
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email, a.associate_code
                FROM associates a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = ?";
        $associate = $this->db->fetchOne($sql, [$id]);

        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            $this->redirect('/admin/associates');
            return;
        }

        // Fetch commissions
        $commissions_sql = "SELECT c.*, 
                                   p.title as property_title, 
                                   cust.name as customer_name
                            FROM commissions c
                            LEFT JOIN properties p ON c.property_id = p.id
                            LEFT JOIN customers cust ON c.customer_id = cust.id
                            WHERE c.associate_id = ?
                            ORDER BY c.created_at DESC";
        $commissions = $this->db->fetchAll($commissions_sql, [$associate['user_id']]);

        // Calculate summary
        $summary = [
            'total_commissions' => 0,
            'level_1_earnings' => 0,
            'level_2_earnings' => 0,
            'level_3_earnings' => 0
        ];

        foreach ($commissions as $comm) {
            $amount = (float)$comm['commission_amount'];
            $summary['total_commissions'] += $amount;
            if (isset($comm['level'])) {
                $level = $comm['level'];
                if (isset($summary["level_{$level}_earnings"])) {
                    $summary["level_{$level}_earnings"] += $amount;
                }
            }
        }

        $data = [
            'page_title' => $this->mlSupport->translate('Commission Report'),
            'associate' => $associate,
            'commissions' => $commissions,
            'summary' => $summary,
            'mlSupport' => $this->mlSupport
        ];

        $this->view('admin/associates/commissions', $data);
    }

    public function tree($id)
    {
        // Fetch associate details
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone, a.associate_code,
                       (SELECT COALESCE(SUM(commission_amount), 0) FROM commissions WHERE associate_id = a.user_id AND status = 'paid') as total_earnings,
                       (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.user_id) as downline_count
                FROM associates a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = ?";
        $associate = $this->db->fetchOne($sql, [$id]);

        if (!$associate) {
            $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
            $this->redirect('/admin/associates');
            return;
        }

        // Build tree
        // Note: For a real production system, fetching the entire tree might be expensive.
        // For now, we'll fetch a few levels deep or just direct reports.
        // Assuming getDownlineTree is a recursive function we implement or simulate.
        $tree = $this->getDownlineTree($associate['user_id']);

        // Since the view expects a specific structure for rendering (recursive renderTree function in view),
        // we'll pass the root's children.
        // Wait, the view uses a recursive renderTree function.
        // We need to pass the data in a format compatible with that.

        $data = [
            'page_title' => $this->mlSupport->translate('Associate Tree View'),
            'associate' => $associate,
            'tree' => $tree,
            'mlSupport' => $this->mlSupport
        ];

        $this->view('admin/associates/tree', $data);
    }

    private function getDownlineCount($userId)
    {
        // Simple recursive count or just direct reports?
        // Let's do direct reports for now to be safe, or a simple count query if table supports hierarchy paths.
        // If we want total downline, we need recursion.
        // Let's just count all descendants using a recursive CTE if supported, or PHP recursion.
        // For simplicity, let's just return direct reports count + sum of their downlines?
        // Let's stick to direct reports count for now to avoid performance issues, or maybe 1 level deep.

        // Actually, the view expects "Total Team Members".
        // Let's just count direct reports for now.
        $sql = "SELECT COUNT(*) as count FROM associates WHERE sponsor_id = ?";
        $result = $this->db->fetchOne($sql, [$userId]);
        return $result['count'];
    }

    private function getDownlineTree($sponsorId, $level = 1, $maxLevel = 3)
    {
        if ($level > $maxLevel) return [];

        $sql = "SELECT a.id, a.user_id, a.commission_rate, u.name as user_name, u.email as user_email, u.phone as user_phone,
                       (SELECT COALESCE(SUM(commission_amount), 0) FROM commissions WHERE associate_id = a.user_id) as total_commission,
                       (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.user_id) as direct_downline_count
                FROM associates a
                JOIN users u ON a.user_id = u.id
                WHERE a.sponsor_id = ?";
        $members = $this->db->fetchAll($sql, [$sponsorId]);

        foreach ($members as &$member) {
            $member['level'] = $level;
            $member['children'] = $this->getDownlineTree($member['user_id'], $level + 1, $maxLevel);
        }

        return $members;
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
                $this->redirect('/admin/associates');
                return;
            }

            // Fetch associate to get user_id
            $associate = $this->db->fetchOne("SELECT user_id FROM associates WHERE id = ?", [$id]);
            if (!$associate) {
                $this->setFlash('error', $this->mlSupport->translate('Associate not found.'));
                $this->redirect('/admin/associates');
                return;
            }
            $user_id = $associate['user_id'];

            try {
                $this->db->beginTransaction();

                // Delete associate record first
                $this->db->query("DELETE FROM associates WHERE id = ?", [$id]);

                // Delete user record
                $this->db->query("DELETE FROM users WHERE id = ?", [$user_id]);

                $this->db->commit();
                $this->setFlash('success', $this->mlSupport->translate('Associate deleted successfully.'));
                $this->redirect('/admin/associates');
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->setFlash('error', $this->mlSupport->translate('Error deleting associate: ') . $e->getMessage());
                $this->redirect('/admin/associates');
            }
        }
    }
}
