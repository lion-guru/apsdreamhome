<?php
namespace App\Models;

use App\Models\Model;

class Component extends Model {
    public static $table = 'components';

    public static function create(array $attributes = []) {
        return static::query()->insert([
            'name' => $attributes['name'],
            'type' => $attributes['type'],
            'content' => $attributes['content'],
            'is_active' => $attributes['is_active'] ?? true,
            'created_by' => $attributes['user_id'] ?? null
        ]);
    }

    public function update($id, $data) {
        return static::query()
            ->where('id', '=', $id)
            ->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'content' => $data['content'],
                'is_active' => $data['is_active']
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

    public function getAll($type = null, $activeOnly = true) {
        $query = static::query();

        if ($type) {
            $query->where('type', '=', $type);
        }

        if ($activeOnly) {
            $query->where('is_active', '=', true);
        }

        return $query->orderBy('name', 'ASC')->get();
    }

    public function getTypes() {
        return static::query()
            ->select(['DISTINCT type'])
            ->orderBy('type', 'ASC')
            ->get();
    }

    public function toggleActive($id) {
        return static::query()
            ->getConnection()
            ->execute("UPDATE " . static::$table . " SET is_active = NOT is_active WHERE id = ?", [$id]);
    }

    public function search($query_str) {
        return static::query()
            ->where('name', 'LIKE', '%' . $query_str . '%')
            ->orWhere('type', 'LIKE', '%' . $query_str . '%')
            ->orWhere('content', 'LIKE', '%' . $query_str . '%')
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function duplicate($id) {
        $component = $this->getById($id);
        if (!$component) return false;

        $component['name'] = $component['name'] . ' (Copy)';
        $userId = $component['created_by'] ?? null; // Preserve or set user_id

        return $this->create([
            'name' => $component['name'],
            'type' => $component['type'],
            'content' => $component['content'],
            'is_active' => $component['is_active'],
            'user_id' => $userId
        ]);
    }
}
