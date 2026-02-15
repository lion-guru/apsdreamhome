<?php
namespace App\Models;

use App\Models\Model;

class Media extends Model {
    public static $table = 'media';
    private $uploadDir = 'uploads/media/';

    public function upload($file, $userId) {
        // Generate unique filename
        $extension = \pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Create upload directory if it doesn't exist
        if (!\file_exists($this->uploadDir)) {
            \mkdir($this->uploadDir, 0777, true);
        }

        // Move uploaded file
        if (\move_uploaded_file($file['tmp_name'], $filepath)) {
            return static::query()->insert([
                'filename' => $filename,
                'original_filename' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size'],
                'path' => $filepath,
                'uploaded_by' => $userId
            ]);
        }
        return false;
    }

    public function delete($id = null): bool {
        if ($id === null) {
            return parent::delete();
        }
        // Get file info before deleting
        $file = $this->getById($id);
        if ($file && \file_exists($file['path'])) {
            \unlink($file['path']);
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

    public function getAll($type = null, $limit = null, $offset = 0) {
        $query = static::query();

        if ($type) {
            $query->where('type', 'LIKE', $type . '%');
        }

        $query->orderBy('uploaded_at', 'DESC');

        if ($limit) {
            $query->limit($limit)->offset($offset);
        }

        return $query->get();
    }

    public function getByUser($userId, $type = null) {
        $query = static::query()
            ->where('uploaded_by', '=', $userId);

        if ($type) {
            $query->where('type', 'LIKE', $type . '%');
        }

        return $query->orderBy('uploaded_at', 'DESC')->get();
    }

    public function getTotalSize($userId = null) {
        $query = static::query();

        if ($userId) {
            $query->where('uploaded_by', '=', $userId);
        }

        $result = $query->select(['SUM(size) as total_size'])->first();
        return $result['total_size'] ?? 0;
    }

    public function search($query_str) {
        return static::query()
            ->where('filename', 'LIKE', '%' . $query_str . '%')
            ->orWhere('original_filename', 'LIKE', '%' . $query_str . '%')
            ->orWhere('type', 'LIKE', '%' . $query_str . '%')
            ->orderBy('uploaded_at', 'DESC')
            ->get();
    }
}
