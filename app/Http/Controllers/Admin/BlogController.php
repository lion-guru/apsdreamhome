<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class BlogController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
            $blogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $blogs = [];
        }
        $this->render("admin/blogs/index", ['page_title' => 'Blog Management', 'blogs' => $blogs]);
    }

    public function create()
    {
        $this->render("admin/blogs/create", ['page_title' => 'Create Blog Post']);
    }

    public function store()
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($title)));
        $slug = trim($slug, '-');
        try {
            $stmt = $this->db->prepare("INSERT INTO blog_posts (title, slug, content, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $slug, $content, $status, $this->tenantId()]);
            $_SESSION['success'] = 'Blog post created successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blogs');
    }

    public function edit($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $blog = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $blog = null;
        }
        if (!$blog) {
            $_SESSION['error'] = 'Post not found';
            $this->redirect('/admin/blogs');
            return;
        }
        $this->render("admin/blogs/edit", ['page_title' => 'Edit Blog Post', 'blog' => $blog]);
    }

    public function update($id)
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE blog_posts SET title = ?, content = ?, status = ? WHERE id = ? $tenantSql");
            $stmt->execute(array_merge([$title, $content, $status, $id], $tenantParams));
            $_SESSION['success'] = 'Blog post updated successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blogs');
    }

    public function destroy($id)
    {
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ? $tenantSql");
            $stmt->execute(array_merge([$id], $tenantParams));
            $_SESSION['success'] = 'Blog post deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blogs');
    }

    public function comments()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';

            $where = [];
            $params = [];

            if (!empty($status) && in_array($status, ['pending', 'approved', 'spam'])) {
                $where[] = "c.status = ?";
                $params[] = $status;
            }
            if (!empty($search)) {
                $where[] = "(c.author_name LIKE ? OR c.author_email LIKE ? OR c.comment LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM blog_comments c $whereSql");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;
            $totalPages = max(1, ceil($total / $perPage));

            $stmt = $this->db->prepare("
                SELECT c.*, p.title as post_title, u.name as admin_name
                FROM blog_comments c
                LEFT JOIN blog_posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                $whereSql
                ORDER BY c.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);
            $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $stats = $this->db->query("
                SELECT
                    COUNT(*) as total,
                    SUM(IF(status='pending',1,0)) as pending,
                    SUM(IF(status='approved',1,0)) as approved,
                    SUM(IF(status='spam',1,0)) as spam
                FROM blog_comments
            ")->fetch(\PDO::FETCH_ASSOC);

            $this->render('admin/blogs/comments', [
                'page_title' => 'Blog Comments',
                'comments' => $comments,
                'stats' => $stats,
                'total' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
                'status' => $status,
                'search' => $search,
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/admin/blogs');
        }
    }

    public function approveComment($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("UPDATE blog_comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Comment approved.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blogs/comments');
    }

    public function rejectComment($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("UPDATE blog_comments SET status = 'spam' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Comment marked as spam.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blogs/comments');
    }

    public function deleteComment($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("DELETE FROM blog_comments WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Comment deleted.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blogs/comments');
    }
}
