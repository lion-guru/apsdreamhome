<?php
namespace Admin\Models;

class Page {
    private $db;
    private $table = 'pages';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (title, slug, content, layout, meta_description, meta_keywords, status, created_by) 
                VALUES (:title, :slug, :content, :layout, :meta_description, :meta_keywords, :status, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $this->createSlug($data['title']),
            ':content' => $data['content'],
            ':layout' => $data['layout'] ?? 'default',
            ':meta_description' => $data['meta_description'] ?? '',
            ':meta_keywords' => $data['meta_keywords'] ?? '',
            ':status' => $data['status'] ?? 'draft',
            ':created_by' => $data['user_id']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                title = :title,
                content = :content,
                layout = :layout,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                status = :status,
                updated_by = :updated_by
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':layout' => $data['layout'],
            ':meta_description' => $data['meta_description'],
            ':meta_keywords' => $data['meta_keywords'],
            ':status' => $data['status'],
            ':updated_by' => $data['user_id'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAll($status = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($status) {
            $sql .= " WHERE status = :status";
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        if ($status) {
            $stmt->execute([':status' => $status]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function createSlug($title) {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    public function createBackup($pageId, $content, $userId) {
        $sql = "INSERT INTO content_backups (page_id, content, created_by) 
                VALUES (:page_id, :content, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':page_id' => $pageId,
            ':content' => $content,
            ':created_by' => $userId
        ]);
    }

    public function getBackups($pageId) {
        $sql = "SELECT * FROM content_backups WHERE page_id = :page_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':page_id' => $pageId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
