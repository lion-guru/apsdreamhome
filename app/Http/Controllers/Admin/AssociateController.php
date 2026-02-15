<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class AssociateController extends BaseController
{

    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        // Fetch associates with user details
        $sql = "SELECT a.*, u.name, u.email, u.phone, u.status as user_status,
                       s.company_name as sponsor_name,
                       (SELECT COUNT(*) FROM associates WHERE sponsor_id = a.user_id) as downline_count
                FROM associates a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN associates s_assoc ON a.sponsor_id = s_assoc.user_id
                LEFT JOIN users s ON s_assoc.user_id = s.id
                ORDER BY a.created_at DESC
                LIMIT $per_page OFFSET $offset";

        $associates = $this->db->fetchAll($sql);

        // Count total for pagination
        $count_sql = "SELECT COUNT(*) as total FROM associates";
        $total = $this->db->fetchOne($count_sql)['total'];

        $data = [
            'page_title' => 'Associates Management',
            'associates' => $associates,
            'total_pages' => ceil($total / $per_page),
            'current_page' => $page
        ];

        $this->view('admin/associates/index', $data);
    }

    public function create()
    {
        // Fetch potential sponsors
        $sponsors = $this->db->fetchAll("SELECT a.user_id as id, u.name 
                                         FROM associates a 
                                         JOIN users u ON a.user_id = u.id 
                                         WHERE u.status = 'active'");

        $data = [
            'page_title' => 'Add Associate',
            'sponsors' => $sponsors
        ];

        $this->view('admin/associates/create', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $sponsor_id = !empty($_POST['sponsor_id']) ? $_POST['sponsor_id'] : null;
            $commission_rate = $_POST['commission_rate'] ?? 0.00;

            // Validate
            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $this->setFlash('error', 'All fields are required.');
                $this->redirect('admin/associates/create');
                return;
            }

            // Check if email exists
            $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                $this->setFlash('error', 'Email already exists.');
                $this->redirect('admin/associates/create');
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
                $this->setFlash('success', 'Associate added successfully.');
                $this->redirect('admin/associates');
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->setFlash('error', 'Error creating associate: ' . $e->getMessage());
                $this->redirect('admin/associates/create');
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
            $this->setFlash('error', 'Associate not found.');
            $this->redirect('admin/associates');
            return;
        }

        // Fetch potential sponsors (excluding self)
        $sponsors = $this->db->fetchAll("SELECT a.user_id as id, u.name 
                                         FROM associates a 
                                         JOIN users u ON a.user_id = u.id 
                                         WHERE u.status = 'active' AND a.user_id != ?", [$associate['user_id']]);

        $data = [
            'page_title' => 'Edit Associate',
            'associate' => $associate,
            'sponsors' => $sponsors
        ];

        $this->view('admin/associates/edit', $data);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $this->setFlash('error', 'Associate not found.');
                $this->redirect('admin/associates');
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
                $this->setFlash('success', 'Associate updated successfully.');
                $this->redirect('admin/associates');
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->setFlash('error', 'Error updating associate: ' . $e->getMessage());
                $this->redirect('admin/associates/edit/' . $id);
            }
        }
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Fetch associate to get user_id
            $associate = $this->db->fetchOne("SELECT user_id FROM associates WHERE id = ?", [$id]);
            if (!$associate) {
                echo json_encode(['status' => 'error', 'message' => 'Associate not found']);
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
                $this->setFlash('success', 'Associate deleted successfully.');
                $this->redirect('admin/associates');
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->setFlash('error', 'Error deleting associate: ' . $e->getMessage());
                $this->redirect('admin/associates');
            }
        }
    }
}
