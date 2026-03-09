<?php

namespace App\Models;

use App\Models\Model;

/**
 * Media Library Model
 * Handles media library data operations
 */
class MediaLibrary extends Model
{
    protected static $table = 'media_library';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'original_name',
        'filename',
        'title',
        'description',
        'category',
        'mime_type',
        'file_size',
        'upload_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get media by category
     */
    public static function getByCategory(string $category): array
    {
        return self::where('category', $category)
                   ->orderBy('upload_date', 'desc')
                   ->get();
    }

    /**
     * Get images only
     */
    public static function getImages(): array
    {
        return self::where('mime_type', 'LIKE', 'image/%')
                   ->orderBy('upload_date', 'desc')
                   ->get();
    }

    /**
     * Get documents only
     */
    public static function getDocuments(): array
    {
        return self::where('mime_type', 'LIKE', 'application/%')
                   ->orderBy('upload_date', 'desc')
                   ->get();
    }

    /**
     * Get media by type (image/document)
     */
    public static function getByType(string $type): array
    {
        $mimePattern = $type === 'image' ? 'image/%' : 'application/%';
        return self::where('mime_type', 'LIKE', $mimePattern)
                   ->orderBy('upload_date', 'desc')
                   ->get();
    }

    /**
     * Search media by title or description
     */
    public static function search(string $term): array
    {
        return self::where('title', 'LIKE', "%{$term}%")
                   ->orWhere('description', 'LIKE', "%{$term}%")
                   ->orderBy('upload_date', 'desc')
                   ->get();
    }

    /**
     * Get recent uploads
     */
    public static function getRecent(int $limit = 10): array
    {
        return self::orderBy('upload_date', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get media statistics
     */
    public static function getStats(): array
    {
        $stats = [];

        // Total files
        $stats['total_files'] = self::count();

        // Total images
        $stats['total_images'] = self::where('mime_type', 'LIKE', 'image/%')->count();

        // Total documents
        $stats['total_documents'] = self::where('mime_type', 'LIKE', 'application/%')->count();

        // Files by category
        $categoryStats = self::raw("
            SELECT category, COUNT(*) as count 
            FROM " . static::$table . " 
            GROUP BY category 
            ORDER BY count DESC
        ");

        $stats['by_category'] = [];
        foreach ($categoryStats as $stat) {
            $stats['by_category'][$stat['category']] = $stat['count'];
        }

        // Storage usage
        $storageInfo = self::raw("SELECT SUM(file_size) as total_size FROM " . static::$table);
        $stats['total_size'] = $storageInfo[0]['total_size'] ?? 0;

        return $stats;
    }

    /**
     * Get file URL
     */
    public function getUrl(): string
    {
        return BASE_URL . 'uploads/media/' . $this->filename;
    }

    /**
     * Get file path
     */
    public function getPath(): string
    {
        return __DIR__ . '/../../../../uploads/media/' . $this->filename;
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Check if file is a document
     */
    public function isDocument(): bool
    {
        return strpos($this->mime_type, 'application/') === 0;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete media file
     */
    public function deleteMedia(): bool
    {
        try {
            // Delete physical file
            $filePath = $this->getPath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete database record
            return $this->delete();

        } catch (\Exception $e) {
            return false;
        }
    }
}
