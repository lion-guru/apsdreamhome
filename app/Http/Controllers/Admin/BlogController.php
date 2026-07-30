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
}
