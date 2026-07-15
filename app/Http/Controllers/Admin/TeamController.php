<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class TeamController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
        $members = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        $data = [
            'page_title' => 'Team Management',
            'members' => $members
        ];
        $this->render('admin/team/index', $data);
    }

    public function create()
    {
        $data = [
            'page_title' => 'Add Team Member',
            'member' => []
        ];
        $this->render('admin/team/form', $data);
    }

    public function store()
    {
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');

        if (empty($name) || empty($position)) {
            $_SESSION['error'] = 'Name and position are required.';
            header('Location: ' . BASE_URL . '/admin/team/create');
            exit;
        }

        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $facebook_url = trim($_POST['facebook_url'] ?? '');
        $instagram_url = trim($_POST['instagram_url'] ?? '');
        $expertise = trim($_POST['expertise'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $category = trim($_POST['category'] ?? 'department');
        $group_name = trim($_POST['group_name'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        $photo = '';
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../../assets/images/team/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $photo = 'team/' . time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $name) . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . basename($photo));
            }
        }

        $stmt = $this->db->prepare("INSERT INTO team_members (name, position, bio, photo, email, phone, linkedin, facebook_url, instagram_url, expertise, experience, category, group_name, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $position, $bio, $photo, $email, $phone, $linkedin, $facebook_url, $instagram_url, $expertise, $experience, $category, $group_name, $sortOrder, $status]);

        $_SESSION['success'] = 'Team member added successfully.';
        header('Location: ' . BASE_URL . '/admin/team');
        exit;
    }

    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$member) {
            $_SESSION['error'] = 'Team member not found.';
            header('Location: ' . BASE_URL . '/admin/team');
            exit;
        }

        $data = [
            'page_title' => 'Edit Team Member',
            'member' => $member
        ];
        $this->render('admin/team/form', $data);
    }

    public function update($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$member) {
            $_SESSION['error'] = 'Team member not found.';
            header('Location: ' . BASE_URL . '/admin/team');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');

        if (empty($name) || empty($position)) {
            $_SESSION['error'] = 'Name and position are required.';
            header('Location: ' . BASE_URL . '/admin/team/edit/' . $id);
            exit;
        }

        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $facebook_url = trim($_POST['facebook_url'] ?? '');
        $instagram_url = trim($_POST['instagram_url'] ?? '');
        $expertise = trim($_POST['expertise'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $category = trim($_POST['category'] ?? 'department');
        $group_name = trim($_POST['group_name'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $photo = $member['photo'];

        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../../assets/images/team/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                if ($photo && file_exists($uploadDir . basename($photo))) {
                    unlink($uploadDir . basename($photo));
                }
                $photo = 'team/' . time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $name) . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . basename($photo));
            }
        }

        $stmt = $this->db->prepare("UPDATE team_members SET name=?, position=?, bio=?, photo=?, email=?, phone=?, linkedin=?, facebook_url=?, instagram_url=?, expertise=?, experience=?, category=?, group_name=?, sort_order=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$name, $position, $bio, $photo, $email, $phone, $linkedin, $facebook_url, $instagram_url, $expertise, $experience, $category, $group_name, $sortOrder, $status, $id]);

        $_SESSION['success'] = 'Team member updated successfully.';
        header('Location: ' . BASE_URL . '/admin/team');
        exit;
    }

    public function destroy($id)
    {
        $stmt = $this->db->prepare("SELECT photo FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($member) {
            if ($member['photo']) {
                $path = __DIR__ . '/../../../../assets/images/' . $member['photo'];
                if (file_exists($path)) unlink($path);
            }
            $this->db->prepare("DELETE FROM team_members WHERE id = ?")->execute([$id]);
            $_SESSION['success'] = 'Team member deleted successfully.';
        }

        header('Location: ' . BASE_URL . '/admin/team');
        exit;
    }
}
