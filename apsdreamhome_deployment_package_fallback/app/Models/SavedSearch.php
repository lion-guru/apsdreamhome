<?php

namespace App\Models;

use App\Core\UnifiedModel;

class SavedSearch extends UnifiedModel {
    public static $table = 'saved_searches';
    protected array $fillable = ['user_id', 'name', 'filters', 'created_at'];

    public function getByUserId($userId) {
        return static::query()
            ->from(static::$table)
            ->where('user_id', '=', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function deleteForUser($id, $userId) {
        return static::query()
            ->from(static::$table)
            ->where('id', '=', $id)
            ->where('user_id', '=', $userId)
            ->delete();
    }
}
