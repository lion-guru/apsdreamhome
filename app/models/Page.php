<?php
namespace App\Models;

use App\Models\Model;

class Page extends Model {
    public static $table = 'pages';

    public static function create(array $data) {
        $instance = new static();
        return static::query()->insert([
            'title' => $data['title'],
            'slug' => $instance->createSlug($data['title']),
            'content' => $data['content'],
            'layout' => $data['layout'] ?? 'default',
            'meta_description' => $data['meta_description'] ?? '',
            'meta_keywords' => $data['meta_keywords'] ?? '',
            'status' => $data['status'] ?? 'draft',
            'created_by' => $data['user_id']
        ]);
    }

    public function update($id, $data) {
        return static::query()
            ->where('id', '=', $id)
            ->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'layout' => $data['layout'],
                'meta_description' => $data['meta_description'],
                'meta_keywords' => $data['meta_keywords'],
                'status' => $data['status'],
                'updated_by' => $data['user_id']
            ]);
    }

    public function delete($id = null): bool {
        if ($id === null) {
            return parent::delete();
        }
        return static::query()
            ->where('id', '=', $id)
            ->delete() > 0;
    }

    public function getById($id) {
        return static::query()
            ->where('id', '=', $id)
            ->first();
    }

    public function getBySlug($slug) {
        return static::query()
            ->where('slug', '=', $slug)
            ->first();
    }

    public function getAll($status = null) {
        $query = static::query();
        if ($status) {
            $query->where('status', '=', $status);
        }
        return $query->orderBy('created_at', 'DESC')->get();
    }

    private function createSlug($title) {
        $slug = \strtolower($title);
        $slug = \preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = \preg_replace('/-+/', '-', $slug);
        $slug = \trim($slug, '-');
        return $slug;
    }

    public function createBackup($pageId, $content, $userId) {
        return static::query()
            ->from('content_backups')
            ->insert([
                'page_id' => $pageId,
                'content' => $content,
                'created_by' => $userId
            ]);
    }

    public function getBackups($pageId) {
        return static::query()
            ->from('content_backups')
            ->where('page_id', '=', $pageId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
