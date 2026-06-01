<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
class BlogController extends AdminController {
    public function __construct() { parent::__construct(); }

    public function index() {
        try {
            $stmt = $this->db->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { $posts = []; }
        $this->render("admin/blog/index", ['page_title' => 'Blog Management', 'posts' => $posts]);
    }

    public function create() {
        $this->render("admin/blog/create", ['page_title' => 'Create Blog Post']);
    }

    public function store() {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($title)));
        $slug = trim($slug, '-');
        try {
            $stmt = $this->db->prepare("INSERT INTO blog_posts (title, slug, content, status, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $slug, $content, $status]);
            $_SESSION['success'] = 'Blog post created successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blog');
    }

    public function edit($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { $post = null; }
        if (!$post) { $_SESSION['error'] = 'Post not found'; $this->redirect('/admin/blog'); return; }
        $this->render("admin/blog/edit", ['page_title' => 'Edit Blog Post', 'post' => $post]);
    }

    public function update($id) {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        try {
            $stmt = $this->db->prepare("UPDATE blog_posts SET title = ?, content = ?, status = ? WHERE id = ?");
            $stmt->execute([$title, $content, $status, $id]);
            $_SESSION['success'] = 'Blog post updated successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blog');
    }

    public function destroy($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Blog post deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/blog');
    }
}
