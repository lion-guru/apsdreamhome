<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Knowledge Base Controller
 * Handles knowledge base article management for admin panel
 */
class KnowledgeBaseController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show all knowledge base articles
     */
    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM knowledge_base ORDER BY created_at DESC");
            $articles = $stmt->fetchAll();
        } catch (\Exception $e) {
            $articles = [];
        }

        $this->render('admin/knowledge-base/index', [
            'page_title' => 'Knowledge Base',
            'page_description' => 'Manage knowledge base articles',
            'articles' => $articles
        ]);
    }

    /**
     * Show create article form
     */
    public function create()
    {
        $this->render('admin/knowledge-base/create', [
            'page_title' => 'Add Knowledge Base Article',
            'page_description' => 'Add a new knowledge base article'
        ]);
    }

    /**
     * Store new article
     */
    public function store()
    {
        $data = [
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'category' => $_POST['category'] ?? 'Getting Started',
            'status' => $_POST['status'] ?? 'draft',
            'views' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $sql = "INSERT INTO knowledge_base (title, content, category, status, views, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['content'],
                $data['category'],
                $data['status'],
                $data['views'],
                $data['created_at']
            ]);

            $_SESSION['success'] = 'Article added successfully!';
            header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error adding article: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/knowledge-base/create');
            exit;
        }
    }

    /**
     * Show article details
     */
    public function show($id)
    {
        try {
            try {
                // Increment view count
                [$tw, $tp] = $this->tenantWhere();
                $stmt = $this->db->prepare("UPDATE knowledge_base SET views = views + 1 WHERE id = ?" . $tw);
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([$id, ...$tp]);

            $stmt = $this->db->prepare("SELECT * FROM knowledge_base WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch();

            if (!$article) {
                $_SESSION['error'] = 'Article not found';
                header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading article';
            header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
            exit;
        }

        $this->render('admin/knowledge-base/show', [
            'page_title' => 'Article Details',
            'page_description' => 'View knowledge base article',
            'article' => $article
        ]);
    }

    /**
     * Edit article
     */
    public function edit($id)
    {
        try {
            try {
                $stmt = $this->db->prepare("SELECT * FROM knowledge_base WHERE id = ?");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([$id]);
            $article = $stmt->fetch();

            if (!$article) {
                $_SESSION['error'] = 'Article not found';
                header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading article';
            header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
            exit;
        }

        $this->render('admin/knowledge-base/edit', [
            'page_title' => 'Edit Article',
            'page_description' => 'Edit knowledge base article',
            'article' => $article
        ]);
    }

    /**
     * Update article
     */
    public function update($id)
    {
        $data = [
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'category' => $_POST['category'] ?? 'Getting Started',
            'status' => $_POST['status'] ?? 'draft'
        ];

        try {
            $sql = "UPDATE knowledge_base 
                    SET title = ?, content = ?, category = ?, status = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['content'],
                $data['category'],
                $data['status'],
                $id
            ]);

            $_SESSION['success'] = 'Article updated successfully!';
            header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error updating article: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/knowledge-base/' . $id . '/edit');
            exit;
        }
    }

    /**
     * Delete article
     */
    public function delete($id)
    {
        try {
            try {
                [$tw, $tp] = $this->tenantWhere();
                $stmt = $this->db->prepare("DELETE FROM knowledge_base WHERE id = ?" . $tw);
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([$id, ...$tp]);

            $_SESSION['success'] = 'Article deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error deleting article: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/knowledge-base-new');
        exit;
    }
}
